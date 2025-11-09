# 📱 CONFIGURAR SERVICIO DE WHATSAPP

## 🎯 **Conectar WhatsApp al Sistema**

El servicio de WhatsApp usa **WhatsApp Web** para enviar mensajes. Necesitas vincular un número de WhatsApp al sistema.

---

## ✅ **Paso 1: Verificar que el servicio esté corriendo**

```powershell
docker ps | findstr whatsapp
```

Deberías ver: `congreso_whatsapp` con estado `Up`

---

## ✅ **Paso 2: Opción A - Ver QR en el navegador (MÁS FÁCIL)**

1. Abre tu navegador en: **http://localhost:3001**

2. Verás un código QR en la pantalla

3. En tu teléfono:
   - Abre **WhatsApp**
   - Ve a **Configuración** (los 3 puntos arriba)
   - Toca **"Dispositivos vinculados"**
   - Toca **"Vincular un dispositivo"**
   - Escanea el código QR que aparece en tu navegador

4. Una vez escaneado, verás un mensaje: **"✅ WhatsApp conectado!"**

---

## ✅ **Paso 2: Opción B - Ver QR en la terminal**

```powershell
docker logs congreso_whatsapp --tail 50
```

Verás un código QR en formato ASCII. Escanéalo con WhatsApp como en la opción A.

---

## 📝 **Verificar conexión**

Una vez conectado, puedes verificar el estado:

```powershell
docker logs congreso_whatsapp --tail 20
```

Deberías ver algo como:
```
✅ WhatsApp conectado exitosamente
📱 Número: +52XXXXXXXXXX
```

---

## 🔄 **Reconectar si se desconecta**

Si el servicio se desconecta (por reiniciar Docker, etc.):

1. Reinicia el contenedor de WhatsApp:
   ```powershell
   docker restart congreso_whatsapp
   ```

2. Espera 30 segundos

3. Abre de nuevo: **http://localhost:3001**

4. Escanea el nuevo código QR

---

## 🧪 **Probar envío de mensaje**

Una vez conectado, puedes probar enviando un mensaje de prueba:

```
http://localhost:8080/php/test_whatsapp.php?telefono=5214491234567&mensaje=Prueba
```

Reemplaza el número con un WhatsApp válido (formato: 521 + 10 dígitos).

---

## ⚠️ **Importante:**

### **¿Qué número debo usar?**

- **Opción 1 (Recomendado):** Un número de WhatsApp dedicado para el sistema
  - Puede ser un número secundario
  - Crea una cuenta de WhatsApp Business para mejor control

- **Opción 2:** Tu número personal
  - Funcionará, pero mezclarás mensajes personales con los del sistema
  - No recomendado para producción

### **¿Por cuánto tiempo queda conectado?**

- La sesión de WhatsApp Web puede durar varias semanas
- Se guarda en el volumen Docker: `whatsapp_sessions`
- Si reinicias Docker con `-v` (eliminar volúmenes), tendrás que reconectar

### **¿Puedo usar el número en mi teléfono al mismo tiempo?**

- ✅ **SÍ**, puedes usar WhatsApp normalmente en tu teléfono
- El sistema solo envía mensajes automáticos
- No recibirás mensajes en el sistema, solo los envías

---

## 🔧 **Solución de Problemas**

### **No aparece el código QR**

```powershell
# Ver logs completos
docker logs congreso_whatsapp

# Reiniciar el servicio
docker restart congreso_whatsapp

# Esperar 30 segundos y volver a ver logs
docker logs congreso_whatsapp --tail 50
```

### **"QR Code expired" o expiró**

- El QR expira después de 1-2 minutos
- Reinicia el servicio para generar uno nuevo:
  ```powershell
  docker restart congreso_whatsapp
  ```

### **Se desconecta constantemente**

- Verifica que el volumen `whatsapp_sessions` esté persistiendo
- No uses el teléfono para cerrar sesión en "Dispositivos vinculados"
- Verifica que el contenedor no se esté reiniciando:
  ```powershell
  docker logs congreso_whatsapp | findstr "error"
  ```

---

## 📊 **Estado del Servicio**

### **Ver si está conectado:**

```powershell
docker exec congreso_whatsapp node -e "console.log('Servicio activo')"
```

### **Ver logs en tiempo real:**

```powershell
docker logs -f congreso_whatsapp
```

---

## 🎯 **URLs Útiles:**

- **Ver QR**: http://localhost:3001
- **Logs**: `docker logs congreso_whatsapp`
- **Reiniciar**: `docker restart congreso_whatsapp`

---

## ✅ **Verificación Final**

Cuando todo esté configurado, al registrar un usuario nuevo:

1. ✅ Deberías recibir un **EMAIL** con el código de verificación
2. ✅ Deberías recibir un **SMS/WhatsApp** con el código de verificación

Si solo recibes uno de los dos, revisa la configuración del servicio faltante.

---

**¿Listo?** Empieza por **http://localhost:3001** para ver el QR de WhatsApp. 📱
