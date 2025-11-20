# 🚀 ACCESO RÁPIDO - URLs Actualizadas

## 🔧 PROBLEMA RESUELTO
El puerto **8080** estaba siendo usado por **Oracle TNSLSNR** instalado en Windows.  
**Solución**: Cambiado a puerto **8081**

---

## 📱 URLs para Compartir con tu Compañero

### ✅ Servicio Web - Aplicación Principal (Puerto 8081):

```
Página de Bienvenida:
http://10.13.208.45:8081/welcome.html

Aplicación Principal:
http://10.13.208.45:8081

Registro de Usuario:
http://10.13.208.45:8081/Front-end/registro_usuario.html

Login:
http://10.13.208.45:8081/Front-end/login.html

Verificar Código:
http://10.13.208.45:8081/Front-end/verificar_codigo.html

Panel Admin:
http://10.13.208.45:8081/Front-end/admin_dashboard.html

Gestión de Eventos:
http://10.13.208.45:8081/Front-end/gestion_eventos.html

Inscripciones:
http://10.13.208.45:8081/Front-end/inscripciones.html
```

### ✅ Servicio WhatsApp (Puerto 3001):

```
WhatsApp QR Code (escanear con teléfono):
http://10.13.208.45:3001

Estado del Servicio:
http://10.13.208.45:3001/health

Formulario de Prueba:
http://10.13.208.45:3001/test
```

### ✅ Oracle Database (Puerto 1521):

**Para conexiones SQL desde herramientas externas (SQL Developer, DBeaver, etc.):**

```
Host: 10.13.208.45
Puerto: 1521
Servicio: FREEPDB1
Usuario: congreso_user
Password: congreso_pass

String de conexión:
10.13.208.45:1521/FREEPDB1
```

### ✅ Oracle Enterprise Manager (Puerto 5500):

**Interfaz web de administración de Oracle:**

```
https://10.13.208.45:5500/em
```

**Credenciales:**
- Usuario: `sys as sysdba` o `PDBADMIN`
- Password: `OraclePass123!`

---

## ⚡ Pasos Rápidos

### 1️⃣ Configurar Firewall (REQUIERE ADMIN)

Abrir PowerShell como **Administrador** y ejecutar:

```powershell
cd "C:\xampp\htdocs\Proyecto\Sistema-de-gestion-Congreso-de-Mercadotecnia"
.\configurar-firewall.ps1
```

Este script configurará automáticamente:
- ✅ Puerto **8081** - Aplicación Web
- ✅ Puerto **3001** - WhatsApp Service  
- ✅ Puerto **1521** - Oracle Database
- ✅ Puerto **5500** - Oracle Enterprise Manager (opcional)

O manualmente:
```powershell
netsh advfirewall firewall add rule name="Docker Web Puerto 8081" dir=in action=allow protocol=TCP localport=8081
netsh advfirewall firewall add rule name="Docker WhatsApp Puerto 3001" dir=in action=allow protocol=TCP localport=3001
netsh advfirewall firewall add rule name="Docker Oracle Puerto 1521" dir=in action=allow protocol=TCP localport=1521
netsh advfirewall firewall add rule name="Docker Oracle EM Puerto 5500" dir=in action=allow protocol=TCP localport=5500
```

### 2️⃣ Verificar Docker

```powershell
docker-compose ps
```

Todos los servicios deben mostrar "Up".

### 3️⃣ Compartir IP y Puerto

Dile a tu compañero que acceda a:
```
http://10.13.208.45:8081
```

**Requisito**: Ambos en la misma red WiFi/LAN

---

## ✅ Estado Actual

- ✅ **Web** corriendo en puerto **8081**
- ✅ **WhatsApp** en puerto **3001**
- ✅ **Oracle DB** en puerto **1521**
- ✅ **Oracle EM** en puerto **5500**
- ✅ IP: **10.13.208.45**
- ⏳ Firewall: Ejecutar script como admin

**Todos los servicios son accesibles desde la red local**

---

## 🔍 Troubleshooting

### No funciona desde otra PC:
1. Verifica firewall: Ejecuta `configurar-firewall.ps1` como admin
2. Ping a la IP: `ping 10.13.208.45`
3. Misma red: Ambas PCs en mismo WiFi

### Funciona en localhost pero no en IP:
- ✅ RESUELTO: Cambiado puerto a 8081

---

**Última actualización**: 10 Nov 2025  
**Puerto**: 8081 (ACTUALIZADO)
