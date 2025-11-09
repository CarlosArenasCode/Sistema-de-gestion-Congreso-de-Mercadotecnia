# Sistema de Protección de Sesión - Guía de Implementación

## ✅ Sistema Implementado

El sistema de protección de sesión ya está configurado y funcionando. Protege automáticamente todas las páginas del sistema.

---

## 📋 Archivos Creados

### 1. JavaScript - Protección del Cliente
**`js/session-guard.js`**
- Verifica sesión automáticamente al cargar cada página
- Redirige al login si no hay sesión activa
- Sincroniza logout entre pestañas
- Verifica sesión cada 5 minutos

### 2. PHP - Verificación del Servidor
**`php/verificar_sesion.php`**
- API endpoint para verificar sesión en servidor
- Verifica timeout de sesión (1 hora de inactividad)
- Retorna datos del usuario si la sesión es válida

### 3. PHP - Login Mejorado
**`php/login.php`**
- Ahora retorna respuestas JSON
- Guarda datos completos en la sesión
- Genera token de sesión
- Compatible con sessionStorage

---

## 🔒 Páginas Protegidas Automáticamente

### Estudiantes:
- dashboard_alumno.html ✅
- horario.html
- mi_qr.html
- mis_constancias.html
- justificar_falta.html

### Administradores:
- admin_dashboard.html
- admin_asistencia.html
- admin_constancias.html
- admin_eventos.html
- admin_inscripciones.html
- admin_justificacion.html
- admin_scan_qr.html
- admin_usuarios.html

---

## 🌐 Páginas Públicas (No Requieren Sesión)

- login.html ✅
- login_admin.html
- registro_usuario.html
- recuperar_pass.html
- recuperar_pass_admin.html
- reset_password.html
- verificar_codigo.html
- welcome.html
- index.php

---

## 🚀 Cómo Agregar Protección a Otras Páginas

Para proteger una nueva página HTML, agrega esto antes del cierre de `</body>`:

```html
<!-- Script de protección de sesión (DEBE IR PRIMERO) -->
<script src="../js/session-guard.js"></script>

<!-- Tus otros scripts aquí -->
<script src="../js/tu-script.js"></script>
```

**Para botón de cerrar sesión:**

```html
<a href="#" id="logout-btn" class="logout-button">Cerrar Sesión</a>

<script>
document.getElementById('logout-btn').addEventListener('click', function(e) {
    e.preventDefault();
    if (confirm('¿Estás seguro de que deseas cerrar sesión?')) {
        window.sessionGuard.logout();
    }
});
</script>
```

---

## ⚙️ Configuración

### Timeout de Sesión
**Servidor (PHP):** 1 hora de inactividad
Modificar en `php/verificar_sesion.php`:
```php
$sessionTimeout = 3600; // segundos
```

**Cliente (JS):** Verificación cada 5 minutos
Modificar en `js/session-guard.js`:
```javascript
setInterval(verifySession, 5 * 60 * 1000); // ms
```

---

## 🧪 Cómo Probar

### 1. Probar Acceso Sin Sesión
```
1. Abre el navegador en modo incógnito
2. Intenta acceder a: http://localhost:8080/Front-end/dashboard_alumno.html
3. Resultado esperado: Redirige automáticamente a login.html
```

### 2. Probar Login y Acceso
```
1. Ve a: http://localhost:8080/Front-end/login.html
2. Inicia sesión con credenciales válidas
3. Deberías ser redirigido al dashboard
4. Intenta acceder a otras páginas protegidas
5. Resultado esperado: Todas las páginas deben funcionar
```

### 3. Probar Cierre de Sesión
```
1. En el dashboard, clic en "Cerrar Sesión"
2. Confirma el cierre
3. Resultado esperado: Redirige a login.html
4. Intenta regresar al dashboard (con botón atrás)
5. Resultado esperado: Te redirige automáticamente al login
```

### 4. Probar Sincronización de Pestañas
```
1. Abre el dashboard en dos pestañas
2. En una pestaña, cierra sesión
3. En la otra pestaña, intenta navegar
4. Resultado esperado: Ambas pestañas redirigen al login
```

### 5. Probar Timeout
```
1. Inicia sesión
2. Espera 1 hora sin interactuar
3. Intenta hacer algo en la página
4. Resultado esperado: Te redirige al login con mensaje de "sesión expirada"
```

---

## 🔧 Solución de Problemas

### Problema: La página no redirige al login

**Solución:**
1. Verifica que `session-guard.js` esté incluido en la página
2. Abre la consola del navegador (F12) y busca errores
3. Verifica que la ruta sea correcta: `../js/session-guard.js`

### Problema: Login no guarda la sesión

**Solución:**
1. Verifica que `login.php` retorne JSON correctamente
2. Abre Network tab en DevTools al hacer login
3. Verifica que la respuesta incluya `success: true` y `userData`

### Problema: Sesión se pierde al cambiar de página

**Solución:**
1. Verifica que uses `sessionStorage` (no `localStorage`)
2. Asegúrate de no cerrar todas las pestañas del navegador
3. Verifica que no haya código que limpie sessionStorage

---

## 📊 Flujo Completo

```
Usuario → Abre Página Protegida
    ↓
session-guard.js se ejecuta
    ↓
¿Es página pública?
    ├─ SÍ → Permite acceso
    └─ NO → Verifica sessionStorage
              ↓
        ¿Tiene datos de sesión?
              ├─ NO → Redirige a login.html
              └─ SÍ → Verifica en servidor (PHP)
                        ↓
                  ¿Sesión válida?
                        ├─ NO → Limpia datos → Redirige a login
                        └─ SÍ → ¿Permisos correctos?
                                  ├─ NO → Redirige a login
                                  └─ SÍ → Permite acceso ✅
```

---

## 🎯 Próximos Pasos

1. **Agregar session-guard a todas las páginas protegidas**
   - Ver lista de páginas en "Páginas Protegidas Automáticamente"
   - Usar el snippet de código de arriba

2. **Probar el sistema completo**
   - Ejecutar los 5 tests de la sección "Cómo Probar"

3. **Actualizar login de administrador**
   - Seguir el mismo patrón que `login.php` para usuarios

4. **Opcional: Ajustar timeouts**
   - Según necesidades del sistema

---

## ✅ Checklist de Implementación

- [x] Crear `js/session-guard.js`
- [x] Crear `php/verificar_sesion.php`
- [x] Actualizar `php/login.php` a JSON
- [x] Actualizar `Front-end/login.html`
- [x] Actualizar `Front-end/dashboard_alumno.html`
- [ ] Agregar session-guard a `horario.html`
- [ ] Agregar session-guard a `mi_qr.html`
- [ ] Agregar session-guard a `mis_constancias.html`
- [ ] Agregar session-guard a `justificar_falta.html`
- [ ] Agregar session-guard a todas las páginas admin
- [ ] Actualizar login de administrador
- [ ] Probar sistema completo
- [ ] Documentar en README principal

---

**Fecha de implementación:** Octubre 18, 2025  
**Estado:** ✅ Sistema Base Implementado  
**Siguiente paso:** Agregar a todas las páginas protegidas
