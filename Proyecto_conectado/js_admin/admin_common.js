/**
 * JavaScript común para todas las páginas de administrador
 * Carga el nombre del administrador en el menú
 */

document.addEventListener('DOMContentLoaded', async () => {
    try {
        const response = await fetch('../php_admin/dashboard_controller.php');
        if (!response.ok) {
            return; // Si falla, no hacer nada
        }
        
        const data = await response.json();
        
        if (data.success && data.admin_nombre) {
            // Actualizar todos los elementos con clase user-menu span
            const adminNameElements = document.querySelectorAll('.user-menu span');
            adminNameElements.forEach(el => {
                el.textContent = `Bienvenido, ${data.admin_nombre}`;
            });
        }
    } catch (error) {
        console.error('Error al cargar nombre del admin:', error);
        // Silenciosamente fallar sin afectar la página
    }
});
