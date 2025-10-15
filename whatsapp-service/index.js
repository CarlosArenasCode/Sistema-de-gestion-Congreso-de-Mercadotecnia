const express = require('express');
const bodyParser = require('body-parser');
const cors = require('cors');
const { Client, LocalAuth } = require('whatsapp-web.js');
const qrcode = require('qrcode-terminal');
require('dotenv').config();

const app = express();
const PORT = process.env.PORT || 3001;

// Middleware
app.use(cors());
app.use(bodyParser.json());
app.use(bodyParser.urlencoded({ extended: true }));

// Variable global para el cliente de WhatsApp
let whatsappClient = null;
let botStatus = 'initializing';
let qrCode = null;

// Función para normalizar número de teléfono
function normalizePhoneNumber(phone) {
    // Eliminar espacios, guiones y paréntesis
    let normalized = phone.replace(/[\s\-\(\)]/g, '');
    
    // Si empieza con +, eliminar el +
    if (normalized.startsWith('+')) {
        normalized = normalized.substring(1);
    }
    
    // Si no empieza con 52, agregarlo
    if (!normalized.startsWith('52')) {
        normalized = '52' + normalized;
    }
    
    // Formato para WhatsApp Web: número@c.us
    return normalized + '@c.us';
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
        
        console.log(`✅ Código enviado a ${phone} (${normalizedPhone})`);
        
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

// Endpoint para verificar el estado del servicio
app.get('/health', (req, res) => {
    res.json({
        status: botStatus,
        service: 'whatsapp-verification',
        timestamp: new Date().toISOString(),
        phoneNumber: process.env.WHATSAPP_NUMBER || '524492106893',
        qrAvailable: botStatus === 'qr_ready'
    });
});

// Endpoint para obtener el código QR
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
        const message = `🧪 *Mensaje de Prueba*\n\n` +
                       `Este es un mensaje de prueba.\n` +
                       `Código de ejemplo: *${testCode}*\n\n` +
                       `_Sistema de Verificación - Congreso UAA_`;
        
        await whatsappClient.sendMessage(normalizedPhone, message);
        
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
app.listen(PORT, () => {
    console.log(`🌐 Servidor corriendo en http://localhost:${PORT}`);
    console.log(`📡 Endpoints disponibles:`);
    console.log(`   - POST /send-verification-code`);
    console.log(`   - POST /test-send`);
    console.log(`   - GET  /health`);
    console.log(`   - GET  /qr`);
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
