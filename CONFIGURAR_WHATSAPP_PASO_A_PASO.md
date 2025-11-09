# 📱 Guía Paso a Paso: Configurar WhatsApp Business API con Twilio

## 🎯 Objetivo
Enviar códigos de verificación por **WhatsApp** (no SMS) usando tu número: **+52 449 210 6893**

---

## ⏱️ Tiempo estimado: 3-5 días (por aprobaciones)

**Importante:** WhatsApp requiere aprobaciones que pueden tomar tiempo. ¡Paciencia! 😊

---

## 📋 FASE 1: Preparación (15 minutos)

### Requisitos que necesitas:
- [ ] Cuenta de Twilio (gratis)
- [ ] Número de teléfono: **+52 449 210 6893**
- [ ] Correo electrónico de negocio
- [ ] Cuenta de Facebook Business (te ayudo a crearla)

### Paso 1.1: Crear cuenta en Twilio

1. **Ve a:** https://www.twilio.com/try-twilio
2. **Regístrate con:**
   - Tu email
   - Contraseña segura
3. **Verifica tu email**
4. **Verifica tu número de teléfono** (+52 449 210 6893)
5. **Recibirás $15 USD en créditos gratuitos**

### Paso 1.2: Acceder al Dashboard

1. **Ve a:** https://console.twilio.com/
2. **Guarda estas credenciales** (las necesitarás después):
   ```
   Account SID: ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
   Auth Token: (haz clic en "Show" para verlo)
   ```
3. **Anótalas en un lugar seguro**

---

## 🏢 FASE 2: Configurar Facebook Business Manager (30 minutos)

WhatsApp Business API requiere una cuenta de Facebook Business Manager.

### Paso 2.1: Crear Facebook Business Manager

1. **Ve a:** https://business.facebook.com/
2. **Haz clic en "Create account"**
3. **Completa la información:**
   - Nombre del negocio: "Congreso de Mercadotecnia UAA"
   - Tu nombre
   - Email de negocio
4. **Verifica tu negocio:**
   - Sube documento oficial (puede ser credencial, comprobante)
   - O conecta página de Facebook del negocio

### Paso 2.2: Crear cuenta de WhatsApp Business

1. En Facebook Business Manager, ve a:
   - **Business Settings** → **Accounts** → **WhatsApp Accounts**
2. Haz clic en **"Add"** → **"Create a new WhatsApp Business Account"**
3. **Información requerida:**
   - Nombre para mostrar: "Congreso MKT UAA"
   - Categoría: "Education" o "Event Planning"
   - Descripción: "Sistema de verificación para Congreso de Mercadotecnia"

---

## 📞 FASE 3: Conectar WhatsApp con Twilio (20 minutos)

### Paso 3.1: Ir al WhatsApp Sender en Twilio

1. **Ve a:** https://console.twilio.com/us1/develop/sms/senders/whatsapp-senders
2. **Haz clic en:** "Create new WhatsApp Sender"
3. **Selecciona:** "Use my own WhatsApp Business Account"

### Paso 3.2: Conectar tu cuenta de Facebook

1. **Haz clic en:** "Connect to Facebook Business Manager"
2. **Inicia sesión** con tu cuenta de Facebook
3. **Autoriza a Twilio** a acceder a tu Business Manager
4. **Selecciona** tu WhatsApp Business Account creado en Fase 2

### Paso 3.3: Registrar tu número de teléfono

**IMPORTANTE:** Este es el paso crítico para usar tu número **+52 449 210 6893**

1. **En Twilio, selecciona:** "Add phone number to WhatsApp Business Account"
2. **Ingresa tu número:** +52 449 210 6893
3. **Método de verificación:** 
   - Opción A: **SMS** (recibirás un código por SMS)
   - Opción B: **Llamada de voz** (recibirás una llamada con el código)
4. **Ingresa el código de verificación** que recibas
5. **Espera la confirmación:** "Phone number verified successfully"

### Paso 3.4: Configurar perfil de WhatsApp Business

1. **Completa el perfil:**
   - Nombre: "Congreso MKT UAA"
   - Foto de perfil: Logo del congreso
   - Descripción: "Verificación para Congreso de Mercadotecnia"
   - Categoría: "Education"
   - Dirección: Universidad Autónoma de Aguascalientes
   - Email: (tu email de contacto)
   - Sitio web: (si aplica)

---

## 📝 FASE 4: Crear Plantillas de Mensajes (30 minutos + 24-48h aprobación)

WhatsApp requiere que todos los mensajes sean pre-aprobados mediante plantillas.

### Paso 4.1: Crear plantilla de código de verificación

1. **Ve a:** https://console.twilio.com/us1/develop/sms/content-editor
2. **Haz clic en:** "Create new Content Template"
3. **Configuración de la plantilla:**

```
Template Name: codigo_verificacion
Language: Spanish (es_MX)
Category: AUTHENTICATION

Header: None

Body:
Hola {{1}},

Tu código de verificación para el Congreso de Mercadotecnia es:

🔐 {{2}}

Este código expira en 15 minutos.
No compartas este código con nadie.

Si no solicitaste este código, ignora este mensaje.

Footer:
Congreso MKT - UAA

Buttons: None
```

4. **Variables explicadas:**
   - `{{1}}` = Nombre del usuario
   - `{{2}}` = Código de 6 dígitos

### Paso 4.2: Enviar para aprobación

1. **Haz clic en:** "Submit for approval"
2. **Espera:** 24-48 horas (a veces más rápido)
3. **Recibirás email** cuando sea aprobado o rechazado

### ⚠️ Consejos para aprobación rápida:
- ✅ Usa lenguaje claro y profesional
- ✅ No uses URLs acortadas
- ✅ No prometas premios o sorteos
- ✅ Indica claramente el propósito del mensaje
- ❌ Evita emojis excesivos
- ❌ No uses lenguaje marketing agresivo

---

## 💻 FASE 5: Configurar el Código PHP (10 minutos)

Una vez que tu plantilla sea aprobada, necesitas actualizar el código.

### Paso 5.1: Actualizar configuración

Edita: `Proyecto_conectado/php/verificacion_config.php`

```php
<?php
// Número emisor (tu WhatsApp Business registrado)
define('TELEFONO_EMISOR', '+5244921068393'); // Ya está correcto

// CAMBIAR A false para usar WhatsApp real
define('SMS_MODE_DESARROLLO', false); // ⚠️ CAMBIAR DESPUÉS DE APROBACIÓN

// Credenciales de Twilio (del dashboard)
define('TWILIO_ACCOUNT_SID', 'ACxxxxxxxxxxxxxxxx'); // ⚠️ Pega tu Account SID
define('TWILIO_AUTH_TOKEN', 'tu_auth_token_aqui'); // ⚠️ Pega tu Auth Token

// Configuración específica de WhatsApp
define('USE_WHATSAPP', true); // ⚠️ NUEVO: Activar WhatsApp en lugar de SMS
define('WHATSAPP_TEMPLATE_SID', 'HXxxxxxxxxxxxxxxxxxx'); // ⚠️ SID de tu plantilla aprobada
?>
```

### Paso 5.2: Actualizar servicio de SMS para WhatsApp

Voy a crear un nuevo archivo para manejar WhatsApp específicamente.

---

## 🧪 FASE 6: Testing con WhatsApp Sandbox (Mientras esperas aprobación)

Puedes empezar a probar AHORA mismo con el Sandbox de Twilio:

### Paso 6.1: Activar Sandbox

1. **Ve a:** https://console.twilio.com/us1/develop/sms/try-it-out/whatsapp-learn
2. **Verás un número de WhatsApp de Twilio** (ej: +1 415 523 8886)
3. **Verás un código único** (ej: "join example-word")

### Paso 6.2: Unirte al Sandbox desde tu WhatsApp

1. **Abre WhatsApp** en tu teléfono
2. **Agrega el número de Twilio** a tus contactos
3. **Envía el mensaje que te indica** (ej: "join example-word")
4. **Recibirás confirmación:** "You are now connected to the Twilio Sandbox"

### Paso 6.3: Configurar Sandbox en el código

```php
// Para testing con Sandbox (temporal)
define('TELEFONO_EMISOR', '+14155238886'); // Número del Sandbox
define('USE_WHATSAPP_SANDBOX', true); // Solo para desarrollo
```

### Paso 6.4: Probar

1. Registra un usuario con TU número (+52 449 210 6893)
2. Deberías recibir el código por WhatsApp
3. ✅ Si funciona, tu configuración es correcta

---

## 🔄 FASE 7: Migrar de Sandbox a Producción

Una vez que tu plantilla sea aprobada y tu número verificado:

### Paso 7.1: Actualizar configuración

```php
<?php
define('TELEFONO_EMISOR', '+5244921068393'); // Tu número real
define('USE_WHATSAPP_SANDBOX', false); // Desactivar sandbox
define('USE_WHATSAPP', true); // Activar WhatsApp producción
define('SMS_MODE_DESARROLLO', false); // Desactivar modo desarrollo
define('WHATSAPP_TEMPLATE_SID', 'HXxxxxx'); // SID de plantilla aprobada
?>
```

### Paso 7.2: Reiniciar Docker

```powershell
docker compose restart
```

### Paso 7.3: Probar con usuario real

1. Registra un nuevo usuario
2. El código debe llegar por WhatsApp
3. ✅ ¡Listo!

---

## 📊 Checklist de Progreso

### Completar en orden:

- [ ] **Día 1:** Crear cuenta Twilio
- [ ] **Día 1:** Crear Facebook Business Manager
- [ ] **Día 1:** Conectar WhatsApp con Twilio
- [ ] **Día 1:** Verificar número +52 449 210 6893
- [ ] **Día 1:** Crear plantilla de mensaje
- [ ] **Día 1:** Enviar plantilla para aprobación
- [ ] **Día 1:** Probar con Sandbox (opcional pero recomendado)
- [ ] **Día 2-4:** Esperar aprobación de plantilla ⏳
- [ ] **Día 5:** Configurar código PHP con plantilla aprobada
- [ ] **Día 5:** Activar modo producción
- [ ] **Día 5:** Probar con usuarios reales
- [ ] **Día 5:** ✅ ¡Sistema funcionando con WhatsApp!

---

## 💰 Costos

### Créditos gratuitos:
- **$15 USD** al crear cuenta (suficiente para ~1,500 mensajes)

### Costos después de créditos:
- **WhatsApp en México:** ~$0.005 USD por mensaje
- **Muy económico:** 1,000 mensajes = ~$5 USD

---

## ❓ Preguntas Frecuentes

### ¿Puedo usar mi número personal de WhatsApp?
❌ No. Necesitas un número dedicado para WhatsApp Business API.
Tu número +52 449 210 6893 debe ser convertido a WhatsApp Business.
**Advertencia:** No podrás usar WhatsApp normal en ese número después.

### ¿Perderé mis chats de WhatsApp?
✅ Puedes hacer backup antes de convertir.
⚠️ Considera usar un número dedicado solo para el sistema.

### ¿Puedo usar otro número?
✅ Sí, si prefieres no usar tu número personal, puedes:
1. Comprar un número nuevo en Twilio (~$1-2 USD/mes)
2. O usar un número diferente que tengas disponible

### ¿Cuánto tarda la aprobación de plantillas?
⏱️ Típicamente 24-48 horas
🚀 A veces más rápido (2-6 horas)
⏳ Puede tardar hasta 7 días en casos excepcionales

### ¿Qué hago si rechazan mi plantilla?
1. Lee el motivo del rechazo en el email
2. Ajusta el texto según las recomendaciones
3. Vuelve a enviar para aprobación
4. Contacta soporte de Twilio si tienes dudas

---

## 🆘 Soporte

### Si necesitas ayuda:

**Twilio Support:**
- Email: help@twilio.com
- Docs: https://www.twilio.com/docs/whatsapp
- Phone: Disponible en dashboard

**Meta/Facebook Support:**
- WhatsApp Business: https://business.whatsapp.com/support

**Yo (tu asistente):**
- Estoy aquí para ayudarte en cada paso 😊
- Solo dime en qué fase estás

---

## 🎯 Próximos Pasos AHORA MISMO

### 1. Crear cuenta Twilio (5 min)
👉 https://www.twilio.com/try-twilio

### 2. Crear Facebook Business Manager (10 min)
👉 https://business.facebook.com/

### 3. ¿Quieres probar con Sandbox primero?
👉 Te recomiendo esto para verificar que todo funciona

---

## 📞 ¿Listo para empezar?

Dime en qué paso estás o si tienes alguna duda. Te voy a ayudar en todo el proceso.

**¿Ya tienes cuenta de Twilio?** → Vamos a Fase 2
**¿Necesitas crear cuenta?** → Empecemos por Fase 1
**¿Prefieres probar Sandbox primero?** → Te guío paso a paso

¡Vamos a configurar tu WhatsApp! 🚀
