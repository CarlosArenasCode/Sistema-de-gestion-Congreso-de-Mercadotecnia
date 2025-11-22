const express = require('express');
const bodyParser = require('body-parser');
const cors = require('cors');
const { Client, LocalAuth } = require('whatsapp-web.js');
const qrcode = require('qrcode-terminal');
const http = require('http');
const { Server } = require('socket.io');
require('dotenv').config();

const app = express();
const server = http.createServer(app);
const io = new Server(server, {
    cors: {
        origin: "*", // En producción, especificar dominios permitidos
        methods: ["GET", "POST"]
    }
});

const PORT = process.env.PORT || 3001;

// Middleware
app.use(cors());
app.use(bodyParser.json());
app.use(bodyParser.urlencoded({ extended: true }));

// Variable global para el cliente de WhatsApp
let whatsappClient = null;
let botStatus = 'initializing';
let qrCode = null;

// Estadísticas en tiempo real
let realtimeStats = {
    totalAttendance: 0,
    activeEvents: 0,
    connectedClients: 0,
    lastUpdate: new Date().toISOString()
};

// Función para normalizar número de teléfono
function normalizePhoneNumber(phone) {
    if (!phone) return '';

    // Quitar todo lo que no sea dígito
    let digits = String(phone).replace(/\D/g, '');

    // Manejar prefijo internacional con 00 (p.ej. 001234...)
    if (digits.startsWith('00')) {
        digits = digits.replace(/^00/, '');
    }

    // Casos comunes:
    // - 10 dígitos: asumimos número local (p.ej. 4492106893) -> prefix 52
    // - 11 dígitos que empiezan con '1' (p.ej. 14492106893): añadimos 52 delante -> 5214492106893
    // - Empieza con 52 (con o sin 1): se acepta tal cual

    if (digits.length === 10) {
        // número local: agregar código país 52 (sin el '1' por defecto)
        return '52' + digits + '@c.us';
    }

    if (digits.length === 11 && digits.startsWith('1')) {
        // usuario envió '1' + 10 dígitos -> convertir a 52 1 xxxxxxxxxx
        return '52' + digits + '@c.us';
    }

    if (digits.startsWith('52')) {
        return digits + '@c.us';
    }

    // Si viene algo distinto pero con más de 10 dígitos, intentar tomar último bloque de 10
    if (digits.length > 10) {
        // Si el usuario envió por ejemplo '521449...' o similares ya se habría capturado arriba.
        // Como fallback agregamos 52 delante si no existe.
        return '52' + digits + '@c.us';
    }

    // Fallback final: prefijar 52
    return '52' + digits + '@c.us';
}

// Inicializar el cliente de WhatsApp
async function initWhatsAppClient() {
    try {
        console.log('🚀 Inicializando cliente de WhatsApp...');
        
        whatsappClient = new Client({
            authStrategy: new LocalAuth({
                dataPath: '/app/.wwebjs_auth'
            }),
            puppeteer: {
                headless: true,
                args: [
                    '--no-sandbox',
                    '--disable-setuid-sandbox',
                    '--disable-dev-shm-usage',
                    '--disable-accelerated-2d-canvas',
                    '--no-first-run',
                    '--no-zygote',
                    '--disable-gpu'
                ]
            }
        });

        // Evento: Código QR generado
        whatsappClient.on('qr', (qr) => {
            console.log('📱 Código QR generado. Escanea con WhatsApp:');
            qrcode.generate(qr, { small: true });
            qrCode = qr;
            botStatus = 'qr_ready';
        });

        // Evento: Cliente listo
        whatsappClient.on('ready', () => {
            botStatus = 'ready';
            qrCode = null;
            console.log('✅ Cliente de WhatsApp listo');
            console.log('📱 Número configurado:', process.env.WHATSAPP_NUMBER);
        });

        // Evento: Cliente autenticado
        whatsappClient.on('authenticated', () => {
            console.log('🔐 Cliente autenticado correctamente');
            botStatus = 'authenticated';
        });

        // Evento: Fallo de autenticación
        whatsappClient.on('auth_failure', (msg) => {
            console.error('❌ Fallo de autenticación:', msg);
            botStatus = 'auth_failure';
        });

        // Evento: Cliente desconectado
        whatsappClient.on('disconnected', (reason) => {
            console.log('⚠️ Cliente desconectado:', reason);
            botStatus = 'disconnected';
        });

        // Inicializar cliente
        await whatsappClient.initialize();
        
    } catch (error) {
        botStatus = 'error';
        console.error('❌ Error al iniciar cliente de WhatsApp:', error);
    }
}

// Endpoint para enviar código de verificación
app.post('/send-verification-code', async (req, res) => {
    try {
        const { phone, code, name } = req.body;
        
        if (!phone || !code) {
            return res.status(400).json({
                success: false,
                error: 'Se requiere teléfono y código'
            });
        }
        
        if (botStatus !== 'ready') {
            return res.status(503).json({
                success: false,
                error: 'El servicio de WhatsApp no está listo',
                status: botStatus,
                qrAvailable: botStatus === 'qr_ready'
            });
        }
        
        // Normalizar el número de teléfono
        const normalizedPhone = normalizePhoneNumber(phone);
        console.log(`📤 Enviando código de verificación a: ${normalizedPhone}`);
        
        // Mensaje de verificación
        const userName = name ? name : 'Usuario';
        const message = `🔐 *Código de Verificación*\n\n` +
                       `Hola ${userName},\n\n` +
                       `Tu código de verificación es:\n\n` +
                       `*${code}*\n\n` +
                       `Este código es válido por 15 minutos.\n\n` +
                       `⚠️ No compartas este código con nadie.\n\n` +
                       `_Congreso de Mercadotecnia UAA_`;
        
        // Enviar mensaje
        await whatsappClient.sendMessage(normalizedPhone, message);
        
        console.log(`✅ Código de verificación enviado a ${phone} (${normalizedPhone})`);
        
        res.json({
            success: true,
            message: 'Código enviado correctamente',
            phone: phone,
            normalized: normalizedPhone
        });
        
    } catch (error) {
        console.error('❌ Error al enviar código:', error);
        res.status(500).json({
            success: false,
            error: 'Error al enviar el código',
            details: error.message
        });
    }
});

// ============================================
// WEBSOCKET - SOCKET.IO CONFIGURATION
// ============================================

io.on('connection', (socket) => {
    console.log(`🔌 Cliente conectado: ${socket.id}`);
    realtimeStats.connectedClients++;
    
    // Enviar estado actual al conectarse
    socket.emit('connection:established', {
        socketId: socket.id,
        timestamp: new Date().toISOString(),
        message: 'Conectado al servidor de asistencia en tiempo real'
    });

    // Unirse a una sala de evento específico
    socket.on('join:event', (eventId) => {
        socket.join(`event_${eventId}`);
        console.log(`📍 Cliente ${socket.id} se unió al evento ${eventId}`);
        socket.emit('joined:event', { eventId, timestamp: new Date().toISOString() });
    });

    // Unirse a la sala de administradores
    socket.on('join:admin', () => {
        socket.join('admins');
        console.log(`👤 Admin ${socket.id} conectado`);
        socket.emit('joined:admin', { 
            stats: realtimeStats,
            timestamp: new Date().toISOString() 
        });
    });

    // Unirse a la sala de un usuario específico
    socket.on('join:user', (userId) => {
        socket.join(`user_${userId}`);
        console.log(`👤 Usuario ${userId} conectado (${socket.id})`);
        socket.emit('joined:user', { userId, timestamp: new Date().toISOString() });
    });

    // Solicitar estadísticas actuales
    socket.on('request:stats', () => {
        socket.emit('stats:update', realtimeStats);
    });

    // Desconexión
    socket.on('disconnect', () => {
        console.log(`🔌 Cliente desconectado: ${socket.id}`);
        realtimeStats.connectedClients--;
    });
});

// ============================================
// ENDPOINT PARA NOTIFICAR ASISTENCIA (llamado desde PHP)
// ============================================

app.post('/notify-attendance', (req, res) => {
    try {
        const { 
            id_usuario, 
            id_evento, 
            nombre_completo, 
            matricula, 
            nombre_evento,
            tipo_registro, // 'entrada' o 'salida'
            timestamp 
        } = req.body;

        if (!id_usuario || !id_evento || !nombre_completo) {
            return res.status(400).json({
                success: false,
                error: 'Faltan datos requeridos'
            });
        }

        const attendanceData = {
            id_usuario,
            id_evento,
            nombre_completo,
            matricula,
            nombre_evento,
            tipo_registro: tipo_registro || 'entrada',
            timestamp: timestamp || new Date().toISOString()
        };

        // Actualizar estadísticas
        realtimeStats.totalAttendance++;
        realtimeStats.lastUpdate = new Date().toISOString();

        // Emitir a todos los clientes conectados
        io.emit('attendance:registered', attendanceData);

        // Emitir al evento específico
        io.to(`event_${id_evento}`).emit('attendance:event:update', attendanceData);

        // Emitir al usuario específico
        io.to(`user_${id_usuario}`).emit('attendance:confirmed', {
            ...attendanceData,
            message: `Tu asistencia ha sido registrada en ${nombre_evento}`
        });

        // Emitir a administradores con estadísticas actualizadas
        io.to('admins').emit('attendance:admin:update', {
            ...attendanceData,
            stats: realtimeStats
        });

        console.log(`✅ Asistencia notificada vía WebSocket: ${nombre_completo} (${matricula}) - ${nombre_evento}`);

        res.json({
            success: true,
            message: 'Notificación enviada vía WebSocket',
            connectedClients: realtimeStats.connectedClients,
            data: attendanceData
        });

    } catch (error) {
        console.error('❌ Error al notificar asistencia:', error);
        res.status(500).json({
            success: false,
            error: error.message
        });
    }
});

// Endpoint para obtener estadísticas
app.get('/stats', (req, res) => {
    res.json({
        success: true,
        stats: realtimeStats,
        connectedClients: io.engine.clientsCount
    });
});

// ============================================
// ENDPOINTS DE WHATSAPP (EXISTENTES)
// ============================================

// Endpoint para verificar el estado del servicio
app.get('/health', (req, res) => {
    res.json({
        status: botStatus,
        authenticated: botStatus === 'ready' || botStatus === 'authenticated',
        service: 'whatsapp-verification',
        timestamp: new Date().toISOString(),
        phoneNumber: process.env.WHATSAPP_NUMBER || '524492106893',
        qrAvailable: botStatus === 'qr_ready',
        websocket: {
            enabled: true,
            connectedClients: realtimeStats.connectedClients
        }
    });
});

// Página principal para mostrar el QR
app.get('/', (req, res) => {
    const html = `
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WhatsApp - Congreso UAA</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 40px;
            max-width: 600px;
            width: 100%;
            text-align: center;
        }
        h1 { color: #333; margin-bottom: 10px; font-size: 28px; }
        .subtitle { color: #666; margin-bottom: 30px; font-size: 14px; }
        .status {
            padding: 15px 25px;
            border-radius: 10px;
            margin: 20px 0;
            font-weight: 600;
            font-size: 16px;
        }
        .status.ready { background: #d4edda; color: #155724; border: 2px solid #28a745; }
        .status.qr { background: #fff3cd; color: #856404; border: 2px solid #ffc107; }
        .status.loading { background: #d1ecf1; color: #0c5460; border: 2px solid #17a2b8; }
        .status.error { background: #f8d7da; color: #721c24; border: 2px solid #dc3545; }
        #qrcode {
            margin: 30px auto;
            padding: 20px;
            background: white;
            border-radius: 15px;
            display: inline-block;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .instructions {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
            text-align: left;
        }
        .instructions h3 { color: #495057; margin-bottom: 15px; font-size: 18px; }
        .instructions ol { margin-left: 20px; color: #6c757d; line-height: 1.8; }
        .instructions li { margin: 8px 0; }
        .refresh-btn {
            background: #667eea;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 20px;
            transition: all 0.3s;
        }
        .refresh-btn:hover { background: #5568d3; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(102,126,234,0.4); }
        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 20px auto;
        }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        /* Formulario de prueba */
        .test-form { margin-top: 20px; text-align: left; }
        .test-form input[type="text"] { width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; margin-bottom: 10px; font-size: 16px; }
        .test-form label { display: inline-flex; align-items: center; gap: 8px; margin-right: 10px; color: #495057; }
        .test-form .send-btn { background: #28a745; color: white; border: none; padding: 10px 18px; border-radius: 8px; cursor: pointer; font-weight: 700; }
        .test-form .send-btn[disabled] { opacity: 0.6; cursor: not-allowed; }
        .test-result { margin-top: 12px; font-size: 14px; }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.1/build/qrcode.min.js"></script>
</head>
<body>
    <div class="container">
        <h1>📱 WhatsApp Service</h1>
        <p class="subtitle">Sistema de Verificación - Congreso UAA</p>
        
        <div id="statusContainer"></div>
        <div id="qrcode"></div>
        
        <div class="instructions">
            <h3>📋 Cómo conectar WhatsApp:</h3>
            <ol>
                <li>Abre <strong>WhatsApp</strong> en tu teléfono</li>
                <li>Ve a <strong>Configuración</strong> (los 3 puntos arriba)</li>
                <li>Toca <strong>"Dispositivos vinculados"</strong></li>
                <li>Toca <strong>"Vincular un dispositivo"</strong></li>
                <li>Escanea el código QR que aparece arriba</li>
            </ol>
        </div>
        
        <div class="test-form" id="testFormContainer">
            <h3>📨 Enviar mensaje de prueba</h3>
            <p>Ingresa el número telefónico (10 dígitos). Selecciona el código de país en el desplegable; el prefijo se añadirá automáticamente (para México <strong>+52</strong> se añadirá también el <strong>1</strong> automáticamente).</p>
            <div style="display:flex; gap:10px; margin-bottom:10px;">
                <select id="countryCode" style="width:40%; padding:10px; border-radius:8px; border:1px solid #ddd; font-size:16px;">
                    <option value="52" selected>+52 (Mexico)</option>
                    <option value="1">+1 (USA/Canadá)</option>
                    <option value="34">+34 (España)</option>
                    <option value="44">+44 (Reino Unido)</option>
                    <option value="51">+51 (Perú)</option>
                    <option value="57">+57 (Colombia)</option>
                    <option value="58">+58 (Venezuela)</option>
                    <option value="598">+598 (Uruguay)</option>
                    <option value="">Otro</option>
                </select>
                <input type="text" id="testPhone" placeholder="4492106893" maxlength="20" style="flex:1; padding:10px; border-radius:8px; border:1px solid #ddd; font-size:16px;" />
            </div>
            <div>
                <button class="send-btn" id="sendTestBtn" onclick="sendTest()">Enviar prueba</button>
                <button class="refresh-btn" onclick="location.reload()">🔄 Actualizar Estado</button>
            </div>
            <div id="testResult" class="test-result"></div>
        </div>
    </div>
    
    <script>
        async function checkStatus() {
            try {
                const response = await fetch('/health');
                const data = await response.json();
                const statusContainer = document.getElementById('statusContainer');
                // Mostrar/ocultar formulario de prueba según estado
                updateTestFormVisibility(data.status);
                const qrcodeDiv = document.getElementById('qrcode');
                
                if (data.status === 'ready' || data.status === 'authenticated') {
                    statusContainer.innerHTML = '<div class="status ready">✅ WhatsApp Conectado</div>';
                    qrcodeDiv.innerHTML = '<p style="color: #28a745; font-weight: 600;">🎉 El servicio está listo para enviar mensajes</p>';
                } else if (data.status === 'qr_ready') {
                    statusContainer.innerHTML = '<div class="status qr">⏳ Escanea el código QR para conectar</div>';
                    // Obtener y mostrar el QR
                    const qrResponse = await fetch('/qr');
                    const qrData = await qrResponse.json();
                    if (qrData.success && qrData.qr) {
                        qrcodeDiv.innerHTML = '';
                        QRCode.toCanvas(qrData.qr, { width: 300, margin: 2 }, (err, canvas) => {
                            if (!err) qrcodeDiv.appendChild(canvas);
                        });
                    }
                } else if (data.status === 'initializing') {
                    statusContainer.innerHTML = '<div class="status loading">🔄 Inicializando servicio...</div>';
                    qrcodeDiv.innerHTML = '<div class="spinner"></div><p style="color: #666; margin-top: 15px;">Espera unos segundos...</p>';
                    setTimeout(checkStatus, 3000);
                } else {
                    statusContainer.innerHTML = '<div class="status error">⚠️ Estado: ' + data.status + '</div>';
                    qrcodeDiv.innerHTML = '<p style="color: #dc3545;">Intenta recargar la página</p>';
                }
            } catch (error) {
                document.getElementById('statusContainer').innerHTML = '<div class="status error">❌ Error de conexión</div>';
                document.getElementById('qrcode').innerHTML = '<p style="color: #dc3545;">No se pudo conectar al servicio</p>';
            }
        }
        
        // Verificar estado al cargar
        checkStatus();
        
        // Auto-refresh cada 10 segundos si está inicializando
        setInterval(() => {
            const status = document.querySelector('.status');
            if (status && status.classList.contains('loading')) {
                checkStatus();
            }
        }, 10000);

        // Función para enviar prueba desde la UI
        async function sendTest() {
            const phoneInput = document.getElementById('testPhone');
                const countryCodeSelect = document.getElementById('countryCode');
                const resultDiv = document.getElementById('testResult');
                const sendBtn = document.getElementById('sendTestBtn');

                let raw = (phoneInput.value || '').trim();
                // Extraer solo dígitos del número ingresado
                const digits = raw.replace(/\D/g, '');
                if (!digits || digits.length !== 10) {
                    resultDiv.innerHTML = '<span style="color:#dc3545;">Ingresa un número válido de 10 dígitos (solo números).</span>';
                    return;
                }

                const countryCode = (countryCodeSelect && countryCodeSelect.value) ? countryCodeSelect.value : '52';
                let phone;
                // Para México (+52) se añade el '1' automáticamente entre el código y el número
                if (countryCode === '52') {
                    phone = '+52 1 ' + digits;
                } else if (countryCode === '') {
                    // Si eligieron 'Otro', enviamos solo el número tal cual (asumiremos que será normalizado en el servidor)
                    phone = digits;
                } else {
                    phone = '+' + countryCode + ' ' + digits;
                }

            // Deshabilitar botón mientras se envía
            sendBtn.disabled = true;
            resultDiv.innerHTML = 'Enviando...';

            try {
                const resp = await fetch('/test-send', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ phone })
                });
                const data = await resp.json();
                if (data && data.success) {
                    resultDiv.innerHTML = '<span style="color:#155724;">✅ ' + (data.message || 'Mensaje enviado') + '</span>';
                } else {
                    resultDiv.innerHTML = '<span style="color:#dc3545;">❌ ' + (data.error || data.message || 'Error al enviar') + '</span>';
                }
            } catch (err) {
                resultDiv.innerHTML = '<span style="color:#dc3545;">❌ Error de conexión: ' + err.message + '</span>';
            } finally {
                sendBtn.disabled = false;
            }
        }

        // Mostrar u ocultar el formulario de prueba según estado
        function updateTestFormVisibility(status) {
            const container = document.getElementById('testFormContainer');
            if (!container) return;
            if (status === 'ready' || status === 'authenticated') {
                container.style.display = 'block';
            } else {
                container.style.display = 'none';
            }
        }
    </script>
</body>
</html>
    `;
    res.send(html);
});

// Endpoint para obtener el código QR (API)
app.get('/qr', (req, res) => {
    if (qrCode && botStatus === 'qr_ready') {
        res.json({
            success: true,
            qr: qrCode,
            message: 'Escanea este código QR con WhatsApp'
        });
    } else if (botStatus === 'ready') {
        res.json({
            success: false,
            message: 'Ya estás autenticado, no necesitas QR'
        });
    } else {
        res.json({
            success: false,
            message: 'Código QR no disponible',
            status: botStatus
        });
    }
});

// Endpoint para probar el envío
app.post('/test-send', async (req, res) => {
    try {
        const { phone } = req.body;
        
        if (!phone) {
            return res.status(400).json({
                success: false,
                error: 'Se requiere número de teléfono'
            });
        }
        
        if (botStatus !== 'ready') {
            return res.status(503).json({
                success: false,
                error: 'El servicio no está listo',
                status: botStatus
            });
        }
        
        const testCode = Math.floor(100000 + Math.random() * 900000).toString();
        
        const normalizedPhone = normalizePhoneNumber(phone);
        console.log(`📤 Enviando mensaje de prueba a: ${normalizedPhone}`);
        
        const message = `🧪 *Mensaje de Prueba*\n\n` +
                       `Este es un mensaje de prueba.\n` +
                       `Código de ejemplo: *${testCode}*\n\n` +
                       `_Sistema de Verificación - Congreso UAA_`;
        
        await whatsappClient.sendMessage(normalizedPhone, message);
        console.log(`✅ Mensaje de prueba enviado a ${phone} (${normalizedPhone})`);
        
        res.json({
            success: true,
            message: 'Mensaje de prueba enviado',
            phone: phone,
            normalized: normalizedPhone,
            testCode: testCode
        });
        
    } catch (error) {
        console.error('❌ Error en prueba:', error);
        res.status(500).json({
            success: false,
            error: error.message
        });
    }
});

// Iniciar servidor
server.listen(PORT, () => {
    console.log(`🌐 Servidor corriendo en http://localhost:${PORT}`);
    console.log(`🔌 WebSocket Server activo`);
    console.log(`📡 Endpoints disponibles:`);
    console.log(`   - POST /send-verification-code (WhatsApp)`);
    console.log(`   - POST /notify-attendance (WebSocket)`);
    console.log(`   - POST /test-send`);
    console.log(`   - GET  /health`);
    console.log(`   - GET  /stats`);
    console.log(`   - GET  /qr`);
    console.log(`   - WS   / (Socket.IO)`);
});

// Iniciar cliente de WhatsApp
initWhatsAppClient();

// Manejo de errores no capturados
process.on('unhandledRejection', (error) => {
    console.error('❌ Error no manejado:', error);
});

// Manejo de cierre graceful
process.on('SIGINT', async () => {
    console.log('\n⏹️ Cerrando servidor...');
    if (whatsappClient) {
        await whatsappClient.destroy();
    }
    process.exit(0);
});

process.on('SIGTERM', async () => {
    console.log('\n⏹️ Cerrando servidor...');
    if (whatsappClient) {
        await whatsappClient.destroy();
    }
    process.exit(0);
});
