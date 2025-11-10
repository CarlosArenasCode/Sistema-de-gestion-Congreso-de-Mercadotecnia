# 🚀 ACCESO RÁPIDO - URLs Actualizadas

## 🔧 PROBLEMA RESUELTO
El puerto **8080** estaba siendo usado por **Oracle TNSLSNR** instalado en Windows.  
**Solución**: Cambiado a puerto **8081**

---

## 📱 URLs para Compartir con tu Compañero

### ✅ USAR ESTAS URLs (Puerto 8081):

```
Aplicación Principal:
http://10.13.208.45:8081

Registro de Usuario:
http://10.13.208.45:8081/Front-end/registro_usuario.html

Login:
http://10.13.208.45:8081/Front-end/login.html

Panel Admin:
http://10.13.208.45:8081/Front-end/admin_dashboard.html

WhatsApp QR:
http://10.13.208.45:3001
```

---

## ⚡ Pasos Rápidos

### 1️⃣ Configurar Firewall (REQUIERE ADMIN)

Abrir PowerShell como **Administrador** y ejecutar:

```powershell
cd "C:\xampp\htdocs\Proyecto\Sistema-de-gestion-Congreso-de-Mercadotecnia"
.\configurar-firewall.ps1
```

O manualmente:
```powershell
netsh advfirewall firewall add rule name="Docker Web Puerto 8081" dir=in action=allow protocol=TCP localport=8081
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

- ✅ Docker corriendo en puerto **8081**
- ✅ WhatsApp en puerto **3001**
- ✅ Oracle DB en puerto **1521**
- ✅ IP: **10.13.208.45**
- ⏳ Firewall: Ejecutar script como admin

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
