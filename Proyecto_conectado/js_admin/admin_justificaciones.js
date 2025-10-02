document.addEventListener('DOMContentLoaded', () => {
    const filterEstadoSelect = document.getElementById('filter-estado');
    const searchInput = document.getElementById('search-input');
    const tablaJustificacionesBody = document.querySelector('.justification-table tbody');
    const loadingIndicator = document.getElementById('loading-indicator');
    const noResultsIndicator = document.getElementById('no-results-indicator');
    const detalleSeccion = document.getElementById('detalle-justificacion');
    
    // Elementos del detalle
    const detalleIdSpan = document.getElementById('detalle-id');
    const detalleUsuarioNombreSpan = document.getElementById('detalle-usuario-nombre');
    const detalleUsuarioIdSpan = document.getElementById('detalle-usuario-id');
    const detalleEventoNombreSpan = document.getElementById('detalle-evento-nombre');
    const detalleMotivoDiv = document.getElementById('detalle-motivo');
    const detalleAdjuntoSpan = document.getElementById('detalle-adjunto');
    const detalleEstadoSpan = document.getElementById('detalle-estado');
    const aprobarButton = detalleSeccion.querySelector('.register-button');
    const rechazarButton = detalleSeccion.querySelector('.logout-button');
    const cerrarDetalleButton = detalleSeccion.querySelector('.secondary');

    let searchTimeout;
    let currentJustificacionId = null;

    function cargarJustificaciones() {
        loadingIndicator.style.display = 'block';
        noResultsIndicator.style.display = 'none';
        tablaJustificacionesBody.innerHTML = '';
        
        const estado = filterEstadoSelect.value;
        const searchTerm = searchInput.value;

        const params = new URLSearchParams({
            action: 'get_list',
            estado: estado,
            search: searchTerm
        });

        fetch(`../php_admin/justificaciones_controller.php?${params.toString()}`)
            .then(response => response.json())
            .then(data => {
                loadingIndicator.style.display = 'none';
                if (data.error) throw new Error(data.error);
                renderTablaJustificaciones(data.justificaciones);
            })
            .catch(error => {
                loadingIndicator.style.display = 'none';
                noResultsIndicator.style.display = 'block';
                console.error("Error:", error);
            });
    }
    
    function renderTablaJustificaciones(justificaciones) {
        tablaJustificacionesBody.innerHTML = '';
        if (!justificaciones || justificaciones.length === 0) {
            noResultsIndicator.style.display = 'block';
            return;
        }

        justificaciones.forEach(j => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${j.id_justificacion}</td>
                <td>${escapeHtml(j.nombre_usuario)}</td>
                <td>${escapeHtml(j.nombre_evento)}</td>
                <td>${new Date(j.fecha_falta + 'T00:00:00').toLocaleDateString('es-ES')}</td>
                <td>${new Date(j.fecha_solicitud).toLocaleString('es-ES')}</td>
                <td><span class="status-${j.estado.toLowerCase()}">${escapeHtml(j.estado)}</span></td>
                <td class="actions">
                    <button class="button secondary small btn-revisar" data-id="${j.id_justificacion}">Revisar</button>
                </td>
            `;
            tablaJustificacionesBody.appendChild(tr);
        });
    }

    tablaJustificacionesBody.addEventListener('click', (e) => {
        if (e.target.classList.contains('btn-revisar')) {
            verDetalle(e.target.dataset.id);
        }
    });

    async function verDetalle(id) {
        currentJustificacionId = id;
        const response = await fetch(`../php_admin/justificaciones_controller.php?action=get_detail&id_justificacion=${id}`);
        const detalle = await response.json();
        
        detalleIdSpan.textContent = detalle.id_justificacion;
        detalleUsuarioNombreSpan.textContent = escapeHtml(detalle.nombre_usuario);
        detalleUsuarioIdSpan.textContent = detalle.id_usuario;
        detalleEventoNombreSpan.textContent = escapeHtml(detalle.nombre_evento);
        detalleMotivoDiv.textContent = escapeHtml(detalle.motivo);
        detalleEstadoSpan.innerHTML = `<span class="status-${detalle.estado.toLowerCase()}">${escapeHtml(detalle.estado)}</span>`;

        if (detalle.archivo_adjunto_ruta) {
            // Asumiendo que la ruta guardada es relativa a la carpeta php/
            const urlAdjunto = `../php/${detalle.archivo_adjunto_ruta}`;
            detalleAdjuntoSpan.innerHTML = `<a href="${urlAdjunto}" target="_blank" class="button small secondary">Ver Archivo</a>`;
        } else {
            detalleAdjuntoSpan.textContent = "No adjuntado";
        }
        
        aprobarButton.style.display = detalle.estado === 'PENDIENTE' ? 'inline-block' : 'none';
        rechazarButton.style.display = detalle.estado === 'PENDIENTE' ? 'inline-block' : 'none';

        detalleSeccion.style.display = 'block';
        detalleSeccion.scrollIntoView({ behavior: 'smooth' });
    }

    cerrarDetalleButton.addEventListener('click', () => {
        detalleSeccion.style.display = 'none';
        currentJustificacionId = null;
    });

    async function actualizarEstado(nuevoEstado) {
        if (!confirm(`¿Seguro que deseas ${nuevoEstado.toLowerCase()} esta justificación?`)) return;

        const response = await fetch(`../php_admin/justificaciones_controller.php`, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                action: 'update_status',
                id_justificacion: currentJustificacionId,
                nuevo_estado: nuevoEstado
            })
        });
        const result = await response.json();
        alert(result.message || result.error);
        if(result.success) {
            cerrarDetalle();
            cargarJustificaciones();
        }
    }

    aprobarButton.addEventListener('click', () => actualizarEstado('APROBADA'));
    rechazarButton.addEventListener('click', () => actualizarEstado('RECHAZADA'));

    filterEstadoSelect.addEventListener('change', cargarJustificaciones);
    searchInput.addEventListener('input', () => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(cargarJustificaciones, 300);
    });

    function escapeHtml(text) {
        return text ? String(text).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;") : '';
    }

    cargarJustificaciones();
});