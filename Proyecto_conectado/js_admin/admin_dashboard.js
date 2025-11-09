document.addEventListener('DOMContentLoaded', () => {
    const statsContainer = document.querySelector('.stats-container');

    async function cargarEstadisticas() {
        try {
            const response = await fetch('../php_admin/dashboard_controller.php');
            if (!response.ok) {
                throw new Error('No se pudo cargar la información del dashboard.');
            }
            const data = await response.json();

            if (data.success) {
                const stats = data.stats;
                statsContainer.innerHTML = `
                    <p><strong>Usuarios Registrados:</strong> ${stats.usuarios_registrados}</p>
                    <p><strong>Eventos Programados:</strong> ${stats.eventos_programados}</p>
                    <p><strong>Justificaciones Pendientes:</strong> <span style="color: #dc3545; font-weight: bold;">${stats.justificaciones_pendientes}</span></p>
                `;
                
                // Actualizar nombre del admin en el menú
                if (data.admin_nombre) {
                    const adminNameElements = document.querySelectorAll('.user-menu span');
                    adminNameElements.forEach(el => {
                        el.textContent = `Bienvenido, ${data.admin_nombre}`;
                    });
                }
            }
        } catch (error) {
            statsContainer.innerHTML = '<p>No se pudieron cargar las estadísticas.</p>';
        }
    }

    cargarEstadisticas();
});