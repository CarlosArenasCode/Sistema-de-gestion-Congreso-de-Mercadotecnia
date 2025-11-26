/**
 * WebSocket Client para Asistencia en Tiempo Real
 * Sistema de Gestión - Congreso de Mercadotecnia
 * 
 * Este módulo maneja la conexión WebSocket con el servidor
 * para recibir actualizaciones en tiempo real de asistencias,
 * inscripciones y notificaciones.
 */

class AttendanceWebSocket {
    constructor(options = {}) {
        this.serverUrl = options.serverUrl || 'http://localhost:3001';
        this.socket = null;
        this.connected = false;
        this.reconnectAttempts = 0;
        this.maxReconnectAttempts = options.maxReconnectAttempts || 5;
        this.reconnectDelay = options.reconnectDelay || 3000;
        this.eventHandlers = {};
        this.debug = options.debug || false;
        
        this.log('Inicializando cliente WebSocket...');
    }

    /**
     * Conectar al servidor WebSocket
     */
    connect() {
        if (this.connected) {
            this.log('Ya está conectado al servidor');
            return;
        }

        try {
            this.log(`Conectando a ${this.serverUrl}...`);
            
            // Cargar socket.io-client desde CDN
            if (typeof io === 'undefined') {
                console.error('❌ Socket.IO client no está cargado. Asegúrate de incluir el script CDN.');
                return;
            }

            this.socket = io(this.serverUrl, {
                transports: ['websocket', 'polling'],
                reconnection: true,
                reconnectionDelay: this.reconnectDelay,
                reconnectionAttempts: this.maxReconnectAttempts
            });

            this.setupEventListeners();
            
        } catch (error) {
            console.error('❌ Error al conectar WebSocket:', error);
            this.handleReconnect();
        }
    }

    /**
     * Configurar listeners de eventos del socket
     */
    setupEventListeners() {
        // Conexión establecida
        this.socket.on('connect', () => {
            this.connected = true;
            this.reconnectAttempts = 0;
            this.log('✅ Conectado al servidor WebSocket');
            this.showNotification('Conectado a notificaciones en tiempo real', 'success');
            this.trigger('connected');
        });

        // Confirmación de conexión
        this.socket.on('connection:established', (data) => {
            this.log('Conexión establecida:', data);
        });

        // Desconexión
        this.socket.on('disconnect', (reason) => {
            this.connected = false;
            this.log(`⚠️ Desconectado: ${reason}`);
            this.showNotification('Desconectado del servidor', 'warning');
            this.trigger('disconnected', { reason });
            
            if (reason === 'io server disconnect') {
                // El servidor forzó la desconexión, reconectar manualmente
                this.handleReconnect();
            }
        });

        // Error de conexión
        this.socket.on('connect_error', (error) => {
            console.error('❌ Error de conexión:', error);
            this.handleReconnect();
        });

        // ============================================
        // EVENTOS DE ASISTENCIA
        // ============================================

        // Nueva asistencia registrada (broadcast a todos)
        this.socket.on('attendance:registered', (data) => {
            this.log('📝 Nueva asistencia registrada:', data);
            this.trigger('attendance:registered', data);
            this.showAttendanceNotification(data);
        });

        // Confirmación de asistencia (para el usuario específico)
        this.socket.on('attendance:confirmed', (data) => {
            this.log('✅ Tu asistencia fue confirmada:', data);
            this.trigger('attendance:confirmed', data);
            this.showNotification(data.message || 'Asistencia confirmada', 'success');
        });

        // Actualización para evento específico
        this.socket.on('attendance:event:update', (data) => {
            this.log('📊 Actualización de evento:', data);
            this.trigger('attendance:event:update', data);
        });

        // Actualización para administradores
        this.socket.on('attendance:admin:update', (data) => {
            this.log('👤 Actualización admin:', data);
            this.trigger('attendance:admin:update', data);
            this.updateAdminStats(data.stats);
        });

        // ============================================
        // EVENTOS DE ESTADÍSTICAS
        // ============================================

        this.socket.on('stats:update', (stats) => {
            this.log('📊 Estadísticas actualizadas:', stats);
            this.trigger('stats:update', stats);
        });

        // ============================================
        // CONFIRMACIONES DE UNIÓN A SALAS
        // ============================================

        this.socket.on('joined:event', (data) => {
            this.log(`✅ Unido al evento ${data.eventId}`);
            this.trigger('joined:event', data);
        });

        this.socket.on('joined:admin', (data) => {
            this.log('✅ Unido a sala de administradores');
            this.trigger('joined:admin', data);
        });

        this.socket.on('joined:user', (data) => {
            this.log(`✅ Unido como usuario ${data.userId}`);
            this.trigger('joined:user', data);
        });
    }

    /**
     * Unirse a la sala de un evento específico
     */
    joinEvent(eventId) {
        if (!this.connected) {
            this.log('⚠️ No conectado. Esperando conexión...');
            this.on('connected', () => this.joinEvent(eventId));
            return;
        }
        this.log(`Uniéndose al evento ${eventId}...`);
        this.socket.emit('join:event', eventId);
    }

    /**
     * Unirse a la sala de administradores
     */
    joinAdmin() {
        if (!this.connected) {
            this.log('⚠️ No conectado. Esperando conexión...');
            this.on('connected', () => this.joinAdmin());
            return;
        }
        this.log('Uniéndose a sala de administradores...');
        this.socket.emit('join:admin');
    }

    /**
     * Unirse a la sala de un usuario específico
     */
    joinUser(userId) {
        if (!this.connected) {
            this.log('⚠️ No conectado. Esperando conexión...');
            this.on('connected', () => this.joinUser(userId));
            return;
        }
        this.log(`Uniéndose como usuario ${userId}...`);
        this.socket.emit('join:user', userId);
    }

    /**
     * Solicitar estadísticas actuales
     */
    requestStats() {
        if (!this.connected) {
            this.log('⚠️ No conectado');
            return;
        }
        this.socket.emit('request:stats');
    }

    /**
     * Registrar un manejador de eventos
     */
    on(eventName, handler) {
        if (!this.eventHandlers[eventName]) {
            this.eventHandlers[eventName] = [];
        }
        this.eventHandlers[eventName].push(handler);
    }

    /**
     * Desregistrar un manejador de eventos
     */
    off(eventName, handler) {
        if (!this.eventHandlers[eventName]) return;
        
        if (handler) {
            this.eventHandlers[eventName] = this.eventHandlers[eventName].filter(h => h !== handler);
        } else {
            delete this.eventHandlers[eventName];
        }
    }

    /**
     * Disparar un evento personalizado
     */
    trigger(eventName, data) {
        if (!this.eventHandlers[eventName]) return;
        
        this.eventHandlers[eventName].forEach(handler => {
            try {
                handler(data);
            } catch (error) {
                console.error(`Error en handler de ${eventName}:`, error);
            }
        });
    }

    /**
     * Manejar reconexión
     */
    handleReconnect() {
        if (this.reconnectAttempts >= this.maxReconnectAttempts) {
            this.log('❌ Máximo de intentos de reconexión alcanzado');
            this.showNotification('No se pudo conectar al servidor. Recarga la página.', 'error');
            return;
        }

        this.reconnectAttempts++;
        this.log(`Reintentando conexión (${this.reconnectAttempts}/${this.maxReconnectAttempts})...`);
        
        setTimeout(() => {
            this.connect();
        }, this.reconnectDelay);
    }

    /**
     * Desconectar del servidor
     */
    disconnect() {
        if (this.socket) {
            this.socket.disconnect();
            this.connected = false;
            this.log('Desconectado del servidor');
        }
    }

    /**
     * Mostrar notificación de asistencia
     */
    showAttendanceNotification(data) {
        const message = `${data.nombre_completo} (${data.matricula}) registró asistencia en ${data.nombre_evento}`;
        
        // Intentar usar el sistema de notificaciones nativo del navegador
        if ('Notification' in window && Notification.permission === 'granted') {
            new Notification('Nueva Asistencia Registrada', {
                body: message,
                icon: '/Logos/UAA_LOGO.png',
                badge: '/Logos/UAA_LOGO.png'
            });
        }
        
        // Mostrar notificación visual en la página
        this.showNotification(message, 'info', 5000);
    }

    /**
     * Actualizar estadísticas de administrador
     */
    updateAdminStats(stats) {
        // Actualizar elementos del DOM si existen
        const totalAttendanceEl = document.querySelector('[data-stat="totalAttendance"]');
        const activeEventsEl = document.querySelector('[data-stat="activeEvents"]');
        const connectedClientsEl = document.querySelector('[data-stat="connectedClients"]');

        if (totalAttendanceEl) totalAttendanceEl.textContent = stats.totalAttendance || 0;
        if (activeEventsEl) activeEventsEl.textContent = stats.activeEvents || 0;
        if (connectedClientsEl) connectedClientsEl.textContent = stats.connectedClients || 0;
    }

    /**
     * Mostrar notificación visual
     */
    showNotification(message, type = 'info', duration = 3000) {
        // Buscar contenedor de notificaciones o crearlo
        let container = document.getElementById('websocket-notifications');
        
        if (!container) {
            container = document.createElement('div');
            container.id = 'websocket-notifications';
            container.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 10000;
                max-width: 400px;
            `;
            document.body.appendChild(container);
        }

        // Crear notificación
        const notification = document.createElement('div');
        notification.className = `websocket-notification websocket-${type}`;
        notification.style.cssText = `
            background: ${this.getNotificationColor(type)};
            color: white;
            padding: 15px 20px;
            margin-bottom: 10px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            animation: slideInRight 0.3s ease-out;
            font-family: 'Segoe UI', sans-serif;
            font-size: 14px;
            line-height: 1.4;
        `;
        notification.textContent = message;

        container.appendChild(notification);

        // Auto-eliminar después del duration
        setTimeout(() => {
            notification.style.animation = 'slideOutRight 0.3s ease-out';
            setTimeout(() => notification.remove(), 300);
        }, duration);
    }

    /**
     * Obtener color según tipo de notificación
     */
    getNotificationColor(type) {
        const colors = {
            success: '#28a745',
            error: '#dc3545',
            warning: '#ffc107',
            info: '#17a2b8'
        };
        return colors[type] || colors.info;
    }

    /**
     * Log con prefijo
     */
    log(...args) {
        if (this.debug) {
            console.log('[WebSocket]', ...args);
        }
    }

    /**
     * Verificar si está conectado
     */
    isConnected() {
        return this.connected;
    }
}

// Añadir estilos para animaciones
const style = document.createElement('style');
style.textContent = `
    @keyframes slideInRight {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    @keyframes slideOutRight {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(400px);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);

// Exportar para uso global
window.AttendanceWebSocket = AttendanceWebSocket;
