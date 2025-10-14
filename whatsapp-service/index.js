const express = require('express');
const bodyParser = require('body-parser');
const cors = require('cors');
const { createBot, createProvider, createFlow } = require('@bot-whatsapp/bot');
const BaileysProvider = require('@bot-whatsapp/provider-baileys');
const MockAdapter = require('@bot-whatsapp/database');
require('dotenv').config();

const app = express();
const PORT = process.env.PORT || 3001;

// Middleware
app.use(cors());
app.use(bodyParser.json());
app.use(bodyParser.urlencoded({ extended: true }));

// Variable global para el provider de WhatsApp
let whatsappProvider = null;
let botStatus = 'initializing';

// Función para normalizar número de teléfono
function normalizePhoneNumber(phone) {
    // Eliminar espacios, guiones y paréntesis
    let normalized = phone.replace(/[\s\-\(\)]/g, '');
    
    // Si empieza con +52, eliminar el +
    if (normalized.startsWith('+52')) {
        normalized = normalized.substring(1);
    }
    
    // Si no empieza con 52, agregarlo
    if (!normalized.startsWith('52')) {
        normalized = '52' + normalized;
    }
    
    // Formato para Baileys: número@s.whatsapp.net
    return normalized + '@s.whatsapp.net';
}

// Flow principal del bot (vacío, solo para inicializar)
const flowPrincipal = createFlow([]);

// Inicializar el bot de WhatsApp
async function initWhatsAppBot() {
    try {
        console.log('🚀 Inicializando bot de WhatsApp...');
        
        const adapterDB = new MockAdapter();
        const adapterProvider = createProvider(BaileysProvider, {
            phoneNumber: process.env.WHATSAPP_NUMBER || '524492106893'
        });
        
        whatsappProvider = adapterProvider;
        
        await createBot({
            flow: flowPrincipal,
            provider: adapterProvider,
            database: adapterDB,
        });
        
        botStatus = 'ready';
        console.log('✅ Bot de WhatsApp iniciado correctamente');
        console.log('📱 Número configurado:', process.env.WHATSAPP_NUMBER);
        
    } catch (error) {
        botStatus = 'error';
        console.error('❌ Error al iniciar bot de WhatsApp:', error);
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
                status: botStatus
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
        await whatsappProvider.sendText(normalizedPhone, message);
        
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
        phoneNumber: process.env.WHATSAPP_NUMBER || '524492106893'
    });
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
        
        const testCode = Math.floor(100000 + Math.random() * 900000).toString();
        
        const normalizedPhone = normalizePhoneNumber(phone);
        const message = `🧪 *Mensaje de Prueba*\n\n` +
                       `Este es un mensaje de prueba.\n` +
                       `Código de ejemplo: *${testCode}*\n\n` +
                       `_Sistema de Verificación - Congreso UAA_`;
        
        await whatsappProvider.sendText(normalizedPhone, message);
        
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
});

// Iniciar bot de WhatsApp
initWhatsAppBot();

// Manejo de errores no capturados
process.on('unhandledRejection', (error) => {
    console.error('❌ Error no manejado:', error);
});
