/**
 * Session Guard - Sistema de Protección de Sesión
 * Verifica si el usuario tiene una sesión activa
 * Si no la tiene, redirige al login correspondiente
 */

(function() {
    'use strict';

    // Configuración
    const config = {
        loginUrl: '/Front-end/login.html',
        adminLoginUrl: '/Front-end/login_admin.html',
        sessionCheckUrl: '/php/verificar_sesion.php',
        // Páginas que NO requieren autenticación
        publicPages: [
            '/Front-end/login.html',
            '/Front-end/login_admin.html',
            '/Front-end/registro_usuario.html',
            '/Front-end/recuperar_pass.html',
            '/Front-end/recuperar_pass_admin.html',
            '/Front-end/reset_password.html',
            '/Front-end/verificar_codigo.html',
            '/index.php',
            '/welcome.html'
        ]
    };

    /**
     * Obtiene la ruta actual de la página
     */
    function getCurrentPath() {
        return window.location.pathname;
    }

    /**
     * Verifica si la página actual es pública
     */
    function isPublicPage() {
        const currentPath = getCurrentPath();
        return config.publicPages.some(page => currentPath.includes(page));
    }

    /**
     * Determina si la página es del área de administración
     */
    function isAdminPage() {
        const currentPath = getCurrentPath();
        return currentPath.includes('admin_') || currentPath.includes('login_admin');
    }

    /**
     * Obtiene el tipo de usuario de la sesión actual
     */
    function getUserTypeFromSession() {
        try {
            const sessionData = sessionStorage.getItem('userData');
            if (sessionData) {
                const userData = JSON.parse(sessionData);
                return userData.rol || userData.tipo;
            }
        } catch (e) {
            console.error('Error al leer datos de sesión:', e);
        }
        return null;
    }

    /**
     * Verifica la sesión en el servidor
     */
    async function checkServerSession() {
        try {
            const response = await fetch(config.sessionCheckUrl, {
                method: 'GET',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json'
                }
            });

            if (!response.ok) {
                return { loggedIn: false };
            }

            const data = await response.json();
            return data;

        } catch (error) {
            console.error('Error al verificar sesión:', error);
            return { loggedIn: false };
        }
    }

    /**
     * Verifica si hay datos de sesión en sessionStorage
     */
    function hasSessionStorage() {
        const userData = sessionStorage.getItem('userData');
        const token = sessionStorage.getItem('token');
        return !!(userData && token);
    }

    /**
     * Limpia todos los datos de sesión
     */
    function clearSession() {
        sessionStorage.clear();
        localStorage.clear();
    }

    /**
     * Redirige al login apropiado
     */
    function redirectToLogin() {
        const isAdmin = isAdminPage();
        const loginUrl = isAdmin ? config.adminLoginUrl : config.loginUrl;
        
        // Guardar la URL actual para redirección después del login
        sessionStorage.setItem('redirectAfterLogin', window.location.pathname);
        
        console.log('Sesión no activa. Redirigiendo a:', loginUrl);
        window.location.href = loginUrl;
    }

    /**
     * Verifica si el usuario tiene permisos para la página actual
     */
    function checkPagePermissions(userType) {
        const currentPath = getCurrentPath();
        
        // Si es página de admin y el usuario no es admin
        if (currentPath.includes('admin_') && userType !== 'admin') {
            console.warn('Usuario sin permisos de admin intentando acceder a:', currentPath);
            redirectToLogin();
            return false;
        }
        
        return true;
    }

    /**
     * Realiza la verificación completa de sesión
     */
    async function verifySession() {
        // Si es una página pública, no verificar sesión
        if (isPublicPage()) {
            console.log('Página pública, no se requiere verificación de sesión');
            return;
        }

        // SOLO verificar sesión en el servidor (ignorar sessionStorage completamente)
        const sessionData = await checkServerSession();
        
        if (!sessionData.loggedIn) {
            console.warn('Sesión no válida en el servidor');
            redirectToLogin();
            return;
        }

        // Verificar permisos de la página
        const userType = sessionData.rol || sessionData.tipo;
        if (userType && !checkPagePermissions(userType)) {
            return;
        }

        console.log('Sesión verificada correctamente - Usuario:', sessionData.user?.nombre || 'N/A');
    }

    /**
     * Maneja el evento de cierre de ventana
     */
    function handleWindowClose() {
        // Nota: Por seguridad, limpiamos sessionStorage al cerrar la ventana
        // Si quieres mantener la sesión entre pestañas, comenta la siguiente línea
        window.addEventListener('beforeunload', function() {
            // Opcional: Descomentar si quieres que se cierre sesión al cerrar ventana
            // sessionStorage.clear();
        });
    }

    /**
     * Monitorea cambios en el sessionStorage desde otras pestañas
     */
    function monitorSessionChanges() {
        window.addEventListener('storage', function(e) {
            if (e.key === 'userLoggedOut' && e.newValue === 'true') {
                console.log('Sesión cerrada desde otra pestaña');
                clearSession();
                redirectToLogin();
            }
        });
    }

    /**
     * Inicializa el sistema de protección de sesión
     */
    function initialize() {
        console.log('Session Guard inicializado');
        
        // Verificar sesión al cargar la página
        verifySession();
        
        // Configurar monitoreo de sesión
        handleWindowClose();
        monitorSessionChanges();
        
        // Verificar sesión periódicamente (cada 5 minutos)
        setInterval(verifySession, 5 * 60 * 1000);
    }

    // Ejecutar cuando el DOM esté listo
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initialize);
    } else {
        initialize();
    }

    // Exponer función para logout manual
    window.sessionGuard = {
        logout: function() {
            // Solo redirigir al login, la sesión PHP se destruye en el servidor
            window.location.href = config.loginUrl;
        },
        checkSession: verifySession
    };

})();
