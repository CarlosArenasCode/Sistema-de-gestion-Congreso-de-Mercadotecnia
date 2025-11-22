# 🌐 Mapa de Servicios - Acceso de Red

## 📊 Resumen de Puertos Expuestos

```
┌─────────────────────────────────────────────────────────────┐
│                   IP: 10.13.208.45                          │
└─────────────────────────────────────────────────────────────┘
                              │
        ┌─────────────────────┼─────────────────────┐
        │                     │                     │
        ▼                     ▼                     ▼
   ┌─────────┐          ┌─────────┐          ┌─────────┐
   │  :8081  │          │  :3001  │          │  :1521  │
   │   WEB   │          │WhatsApp │          │ Oracle  │
   └─────────┘          └─────────┘          │   DB    │
                                              └─────────┘
                                                   │
                                              ┌─────────┐
                                              │  :5500  │
                                              │Oracle EM│
                                              └─────────┘
```

---

## 🎯 Servicios Disponibles

### 1️⃣ **Aplicación Web** - Puerto 8081
**Descripción**: Sistema de Gestión del Congreso de Mercadotecnia  
**Tecnología**: PHP 8.2 + Apache 2.4 + Oracle Extensions  
**URLs**:
- Inicio: `http://10.13.208.45:8081`
- Registro: `http://10.13.208.45:8081/Front-end/registro_usuario.html`
- Login: `http://10.13.208.45:8081/Front-end/login.html`
- Admin: `http://10.13.208.45:8081/Front-end/admin_dashboard.html`

---

### 2️⃣ **WhatsApp Service** - Puerto 3001
**Descripción**: Servicio de envío de códigos de verificación por WhatsApp  
**Tecnología**: Node.js + whatsapp-web.js  
**URLs**:
- QR Code: `http://10.13.208.45:3001`
- Health Check: `http://10.13.208.45:3001/health`
- Test Form: `http://10.13.208.45:3001/test`

**Estado requerido**: Debe estar "authenticated" (escanear QR)

---

### 3️⃣ **Oracle Database** - Puerto 1521
**Descripción**: Base de datos Oracle 23ai Free  
**Tecnología**: Oracle Database 23ai Free (Pluggable Database)  
**Conexión**:
```
Host: 10.13.208.45
Puerto: 1521
Servicio/SID: FREEPDB1
Usuario Aplicación: congreso_user
Password: congreso_pass
```

**Herramientas compatibles**:
- SQL Developer
- DBeaver
- SQL*Plus
- Toad for Oracle
- DataGrip

**String de conexión**:
```
jdbc:oracle:thin:@10.13.208.45:1521/FREEPDB1
```

**Usuario Administrador**:
- Usuario: `sys as sysdba` / `PDBADMIN`
- Password: `OraclePass123!`

---

### 4️⃣ **Oracle Enterprise Manager** - Puerto 5500
**Descripción**: Interfaz web de administración de Oracle Database  
**Tecnología**: Oracle EM Express  
**URL**: `https://10.13.208.45:5500/em`

**Credenciales**:
- Usuario: `sys as sysdba`
- Password: `OraclePass123!`

⚠️ **Nota**: Usa HTTPS y puede mostrar advertencia de certificado autofirmado (normal)

---

## 🔥 Firewall - Puertos a Abrir

Para permitir acceso desde otras computadoras en la red, ejecutar como **Administrador**:

```powershell
# Web Application
netsh advfirewall firewall add rule name="Docker Web Puerto 8081" dir=in action=allow protocol=TCP localport=8081

# WhatsApp Service
netsh advfirewall firewall add rule name="Docker WhatsApp Puerto 3001" dir=in action=allow protocol=TCP localport=3001

# Oracle Database
netsh advfirewall firewall add rule name="Docker Oracle Puerto 1521" dir=in action=allow protocol=TCP localport=1521

# Oracle Enterprise Manager
netsh advfirewall firewall add rule name="Docker Oracle EM Puerto 5500" dir=in action=allow protocol=TCP localport=5500
```

O ejecutar el script automático:
```powershell
.\configurar-firewall.ps1
```

---

## ✅ Verificación de Servicios

### Desde el Host (tu computadora):

```powershell
# Verificar contenedores corriendo
docker-compose ps

# Verificar puertos escuchando
netstat -an | findstr "8081 3001 1521 5500"

# Probar acceso web
curl http://localhost:8081
curl http://localhost:3001/health

# Probar Oracle
docker-compose exec oracle_db sqlplus congreso_user/congreso_pass@FREEPDB1
```

### Desde otra computadora (Computadora B):

```bash
# Ping para verificar conectividad
ping 10.13.208.45

# Probar puerto web
curl http://10.13.208.45:8081

# Probar WhatsApp
curl http://10.13.208.45:3001/health
```

---

## 🎓 Casos de Uso

### **Estudiante registrándose desde otra PC**:
1. Abrir: `http://10.13.208.45:8081/Front-end/registro_usuario.html`
2. Llenar formulario de registro
3. Recibir código por email y WhatsApp
4. Verificar con código recibido

### **Administrador gestionando eventos**:
1. Login: `http://10.13.208.45:8081/Front-end/login.html`
2. Panel admin: `http://10.13.208.45:8081/Front-end/admin_dashboard.html`
3. Gestionar eventos, inscripciones, asistencias

### **DBA conectándose a Oracle desde SQL Developer**:
1. Crear nueva conexión
2. Host: `10.13.208.45`
3. Puerto: `1521`
4. Servicio: `FREEPDB1`
5. Ejecutar consultas, revisar esquema

### **Desarrollador monitoreando WhatsApp**:
1. Abrir: `http://10.13.208.45:3001`
2. Verificar estado de autenticación
3. Escanear QR si es necesario
4. Probar envío desde: `http://10.13.208.45:3001/test`

---

## 📱 Requisitos de Red

✅ **Ambas computadoras en la misma red local**
- Mismo WiFi / Ethernet
- Misma subred (ej: 10.13.208.x)

✅ **Firewall configurado correctamente**
- Reglas de entrada permitidas para puertos 8081, 3001, 1521, 5500

✅ **Docker corriendo**
- Todos los contenedores "Up" y "healthy"

✅ **IP estática o conocida**
- Mejor configurar IP estática en Windows
- O ejecutar `ipconfig` antes de compartir

---

## 🔧 Troubleshooting

### No puedo acceder desde otra PC:
1. ✅ Verificar que estén en la misma red
2. ✅ Hacer ping a la IP: `ping 10.13.208.45`
3. ✅ Verificar firewall (ejecutar script)
4. ✅ Confirmar Docker corriendo: `docker-compose ps`

### Oracle no acepta conexiones:
1. ✅ Esperar a que esté "healthy": `docker-compose ps`
2. ✅ Verificar puerto 1521 abierto en firewall
3. ✅ Usar servicio `FREEPDB1` (no XE ni ORCL)

### WhatsApp desconectado:
1. ✅ Abrir: `http://10.13.208.45:3001`
2. ✅ Escanear QR con WhatsApp del teléfono
3. ✅ Esperar status "authenticated"

---

**Actualizado**: 10 de Noviembre, 2025  
**Servicios totales**: 4 (Web, WhatsApp, Oracle DB, Oracle EM)  
**Puertos expuestos**: 8081, 3001, 1521, 5500
