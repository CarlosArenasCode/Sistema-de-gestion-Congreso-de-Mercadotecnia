# 🌐 Configuración de Acceso desde Otra Computadora

Esta guía te permitirá acceder a la aplicación Docker desde otra computadora en la misma red.

## 📍 Información de tu Computadora (A)

- **IP Principal**: `10.13.208.45`
- **Puerto Web**: `8081` (cambiado de 8080 por conflicto con Oracle local)
- **Puerto WhatsApp**: `3001`
- **Puerto Oracle DB**: `1521`
- **Puerto Oracle EM**: `5500`

**Todos los servicios Docker están expuestos para acceso de red**

## 🔧 Paso 1: Configurar Firewall de Windows (REQUIERE ADMIN)

### Opción A: Usando PowerShell como Administrador

1. **Abrir PowerShell como Administrador**:
   - Clic derecho en el botón de Windows
   - Seleccionar "Windows PowerShell (Admin)" o "Terminal (Admin)"

2. **Ejecutar estos comandos**:

```powershell
# Permitir puerto 8081 (Aplicación Web) - CAMBIADO DE 8080 POR CONFLICTO CON ORACLE
netsh advfirewall firewall add rule name="Docker Web Puerto 8081" dir=in action=allow protocol=TCP localport=8081

# Permitir puerto 3001 (WhatsApp Service)
netsh advfirewall firewall add rule name="Docker WhatsApp Puerto 3001" dir=in action=allow protocol=TCP localport=3001

# Permitir puerto 1521 (Oracle Database) - OPCIONAL
netsh advfirewall firewall add rule name="Docker Oracle Puerto 1521" dir=in action=allow protocol=TCP localport=1521

# Permitir puerto 5500 (Oracle Enterprise Manager) - OPCIONAL
netsh advfirewall firewall add rule name="Docker Oracle EM Puerto 5500" dir=in action=allow protocol=TCP localport=5500

# Verificar reglas creadas
netsh advfirewall firewall show rule name="Docker Web Puerto 8081"
```

### Opción B: Usando la Interfaz Gráfica de Windows

1. **Abrir Firewall de Windows**:
   - Presiona `Win + R`
   - Escribe: `wf.msc`
   - Presiona Enter

2. **Crear Nueva Regla**:
   - Clic en "Reglas de entrada" (panel izquierdo)
   - Clic en "Nueva regla..." (panel derecho)
   - Seleccionar: **Puerto** → Siguiente
   - Protocolo: **TCP**
   - Puerto local específico: **8080**
   - Acción: **Permitir la conexión**
   - Perfil: Marcar **Dominio, Privado y Público**
   - Nombre: **Docker Web Puerto 8080**
   - Finalizar

3. **Repetir para Puerto 3001** (WhatsApp Service):
   - Mismo proceso pero con puerto **3001**
   - Nombre: **Docker WhatsApp Puerto 3001**

## ⚠️ Nota Importante sobre el Puerto 8081

El puerto fue cambiado de **8080** a **8081** porque Oracle Database instalado localmente en Windows usa el puerto 8080 (Oracle XML DB/TNSLSNR), lo que causaba conflictos de acceso.

## 🖥️ Paso 2: Acceso desde la Computadora B (Compañero)

### Requisitos:
- Ambas computadoras deben estar en la **misma red** (mismo WiFi o red local)

### URLs para Acceder:

1. **Aplicación Principal**:
   ```
   http://10.13.208.45:8081
   ```

2. **Página de Inicio/Bienvenida**:
   ```
   http://10.13.208.45:8081/welcome.html
   ```

3. **Registro de Usuario**:
   ```
   http://10.13.208.45:8081/Front-end/registro_usuario.html
   ```

4. **Login**:
   ```
   http://10.13.208.45:8081/Front-end/login.html
   ```

5. **Panel de Administración**:
   ```
   http://10.13.208.45:8081/Front-end/admin_dashboard.html
   ```

6. **WhatsApp QR (para escanear código)**:
   ```
   http://10.13.208.45:3001
   ```

7. **Oracle Database (para SQL Developer, DBeaver, etc.)**:
   ```
   Host: 10.13.208.45
   Puerto: 1521
   Servicio: FREEPDB1
   Usuario: congreso_user
   Password: congreso_pass
   ```

8. **Oracle Enterprise Manager (interfaz web de admin)**:
   ```
   https://10.13.208.45:5500/em
   Usuario: sys as sysdba
   Password: OraclePass123!
   ```

## ✅ Paso 3: Verificar Conexión

### Desde tu Computadora (A):

```powershell
# Verificar que Docker esté escuchando
docker-compose ps

# Verificar puertos abiertos
netstat -an | findstr "8081"
netstat -an | findstr "3001"
```

### Desde la Computadora B (Compañero):

1. **Abrir navegador** (Chrome, Firefox, Edge)

2. **Probar conectividad**:
   ```
   http://10.13.208.45:8081
   ```

3. **Si NO funciona**, hacer ping para verificar conectividad:
   ```cmd
   ping 10.13.208.45
   ```

## 🔍 Troubleshooting (Solución de Problemas)

### Problema: "No se puede acceder al sitio"

#### Solución 1: Verificar Firewall
```powershell
# Ver reglas de firewall
netsh advfirewall firewall show rule name=all | findstr "8081"
```

#### Solución 2: Verificar Docker está corriendo
```powershell
docker-compose ps
```

Todos los servicios deben mostrar "Up" y "healthy" (oracle_db).

#### Solución 3: Verificar IP no ha cambiado
```powershell
ipconfig | findstr "IPv4"
```

Si la IP cambió, actualiza las URLs con la nueva IP.

#### Solución 4: Desactivar temporalmente el Firewall (SOLO PARA PRUEBA)
```powershell
# COMO ADMINISTRADOR - Solo para probar
netsh advfirewall set allprofiles state off

# IMPORTANTE: Volver a activarlo después
netsh advfirewall set allprofiles state on
```

### Problema: "La página carga pero no puedo registrarme"

- **Verificar logs**:
  ```powershell
  docker-compose logs -f web
  ```

- **Verificar base de datos**:
  ```powershell
  docker-compose logs oracle_db
  ```

### Problema: "No recibo el código de WhatsApp"

1. **Verificar servicio WhatsApp**:
   ```
   http://10.13.208.45:3001
   ```

2. **Escanear QR code** si muestra "disconnected"

3. **Ver logs**:
   ```powershell
   docker-compose logs -f whatsapp
   ```

## 📱 Configuración de IP Estática (Opcional pero Recomendado)

Para evitar que la IP cambie y tengas que actualizar las URLs:

1. **Abrir Configuración de Red**:
   - Panel de Control → Red e Internet → Centro de redes y recursos compartidos
   - Clic en tu conexión activa
   - Propiedades → Protocolo de Internet versión 4 (TCP/IPv4)

2. **Configurar IP Estática**:
   - Seleccionar: "Usar la siguiente dirección IP"
   - IP: `10.13.208.45` (tu IP actual)
   - Máscara de subred: `255.255.255.0` (normalmente)
   - Puerta de enlace: (la IP de tu router, ej: `10.13.208.1`)
   - DNS: `8.8.8.8` (Google DNS)

## 🎯 Resumen Rápido

### En Computadora A (tuya):
1. ✅ Abrir PowerShell como **Administrador**
2. ✅ Ejecutar: 
   ```powershell
   netsh advfirewall firewall add rule name="Docker Web Puerto 8080" dir=in action=allow protocol=TCP localport=8080
   ```
3. ✅ Verificar Docker corriendo: `docker-compose ps`

### En Computadora B (compañero):
1. ✅ Conectarse a la misma red WiFi/LAN
2. ✅ Abrir navegador
3. ✅ Ir a: `http://10.13.208.45:8080`

## 📞 Información de Contacto en Caso de Problemas

Si encuentras problemas, verifica:
- ✅ Ambas computadoras en la misma red
- ✅ Firewall configurado correctamente
- ✅ Docker corriendo (`docker-compose ps`)
- ✅ No hay antivirus bloqueando conexiones

---

**Fecha de configuración**: 10 de Noviembre, 2025  
**IP Configurada**: 10.13.208.45  
**Puertos**: 8080 (Web), 3001 (WhatsApp), 1521 (Oracle)
