document.addEventListener('DOMContentLoaded', function() {
    const eventosContainer = document.getElementById('eventos-container');
    const formEvento = document.getElementById('form-evento');
    const formEventoTituloDisplay = document.getElementById('form-evento-titulo-display');
    const eventoIdInput = document.getElementById('id_evento');
    const btnMostrarFormulario = document.getElementById('btn-mostrar-formulario');
    const crearEventoFormSection = document.getElementById('crear-evento-form-section');
    const btnCancelarEdicion = document.getElementById('btn-cancelar-edicion');
    const btnLimpiarForm = document.getElementById('btn-limpiar-form');
    const loadingIndicatorEventos = document.getElementById('loading-indicator-eventos');
    const alertPlaceholder = document.getElementById('alert-placeholder');

    const API_URL = '../php_admin/eventos_controller.php';

    function showAlert(message, type = 'success') {
        const alertClass = type === 'success' ? 'message success' : 'error-message';
        alertPlaceholder.innerHTML = `<div class="${alertClass}" role="alert">${message}</div>`;
        setTimeout(() => {
            alertPlaceholder.innerHTML = '';
        }, 5000);
    }

    function cargarEventos() {
        loadingIndicatorEventos.style.display = 'block';
        eventosContainer.innerHTML = '';

        fetch(`${API_URL}?action=get_eventos`)
            .then(response => response.json())
            .then(data => {
                loadingIndicatorEventos.style.display = 'none';
                if (data.success && data.eventos) {
                    if (data.eventos.length === 0) {
                        eventosContainer.innerHTML = '<p>No hay eventos para mostrar.</p>';
                        return;
                    }
                    renderEventos(data.eventos);
                } else {
                    showAlert(data.message || 'Error al cargar los eventos.', 'error');
                }
            })
            .catch(error => {
                loadingIndicatorEventos.style.display = 'none';
                showAlert('Error de conexión al cargar los eventos.', 'error');
            });
    }

    function renderEventos(eventos) {
        eventosContainer.innerHTML = '';
        eventos.forEach(evento => {
            const cupoInfo = evento.cupo_maximo ? `${evento.cupo_actual || 0} / ${evento.cupo_maximo}` : 'Ilimitado';
            const constanciaInfo = evento.genera_constancia == 1 ? `Sí (${evento.tipo_evento}, ${evento.horas_para_constancia}h)` : 'No';

            const card = document.createElement('div');
            card.className = 'evento-card';
            card.innerHTML = `
                <h4>${escapeHtml(evento.nombre_evento)}</h4>
                <p><strong>Ponente:</strong> ${escapeHtml(evento.ponente) || 'N/A'}</p>
                <p><strong>Fecha:</strong> ${formatDate(evento.fecha_inicio)} ${evento.hora_inicio.substring(0,5)}</p>
                <p><strong>Lugar:</strong> ${escapeHtml(evento.lugar) || 'N/A'}</p>
                <p><strong>Cupo:</strong> ${cupoInfo}</p>
                <p><strong>Constancia:</strong> ${constanciaInfo}</p>
                <div class="evento-actions">
                    <button class="button small secondary button-edit" data-id="${evento.id_evento}">Editar</button>
                    <button class="button small logout-button button-delete" data-id="${evento.id_evento}">Eliminar</button>
                </div>
            `;
            eventosContainer.appendChild(card);
        });
    }

    function escapeHtml(text) {
        return text ? text.toString().replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;") : '';
    }

    function formatDate(dateString) {
        if (!dateString) return 'N/A';
        const date = new Date(dateString + 'T00:00:00');
        return date.toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric' });
    }

    function limpiarFormulario() {
        formEvento.reset();
        eventoIdInput.value = '';
        formEventoTituloDisplay.textContent = 'Crear Nuevo Evento';
        btnCancelarEdicion.style.display = 'none';
        formEvento.querySelector('button[type="submit"]').textContent = 'Guardar Evento';
    }

    btnMostrarFormulario.addEventListener('click', () => {
        limpiarFormulario();
        crearEventoFormSection.style.display = 'block';
        btnMostrarFormulario.style.display = 'none';
        formEvento.scrollIntoView({ behavior: 'smooth' });
    });

    btnCancelarEdicion.addEventListener('click', () => {
        limpiarFormulario();
        crearEventoFormSection.style.display = 'none';
        btnMostrarFormulario.style.display = 'inline-block';
    });
    
    btnLimpiarForm.addEventListener('click', limpiarFormulario);

    formEvento.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(formEvento);

        fetch(API_URL, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert(data.message, 'success');
                limpiarFormulario();
                crearEventoFormSection.style.display = 'none';
                btnMostrarFormulario.style.display = 'inline-block';
                cargarEventos();
            } else {
                showAlert(data.message || 'Error al guardar el evento.', 'error');
            }
        })
        .catch(() => showAlert('Error de conexión al guardar el evento.', 'error'));
    });

    eventosContainer.addEventListener('click', function(e) {
        const target = e.target;
        if (target.classList.contains('button-edit')) {
            const eventoId = target.dataset.id;
            fetch(`${API_URL}?action=get_evento_detalle&id_evento=${eventoId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.evento) {
                        const evento = data.evento;
                        eventoIdInput.value = evento.id_evento;
                        document.getElementById('nombre_evento').value = evento.nombre_evento;
                        document.getElementById('ponente').value = evento.ponente || '';
                        document.getElementById('fecha_inicio').value = evento.fecha_inicio;
                        document.getElementById('hora_inicio').value = evento.hora_inicio;
                        document.getElementById('fecha_fin').value = evento.fecha_fin || '';
                        document.getElementById('hora_fin').value = evento.hora_fin || '';
                        document.getElementById('lugar').value = evento.lugar || '';
                        document.getElementById('cupo_maximo').value = evento.cupo_maximo || '';
                        document.getElementById('descripcion').value = evento.descripcion || '';
                        document.getElementById('genera_constancia').value = evento.genera_constancia;
                        // --- ACTUALIZAR CAMPOS NUEVOS ---
                        document.getElementById('tipo_evento').value = evento.tipo_evento || 'conferencia';
                        document.getElementById('horas_para_constancia').value = evento.horas_para_constancia || '1.0';

                        formEventoTituloDisplay.textContent = 'Editar Evento';
                        formEvento.querySelector('button[type="submit"]').textContent = 'Actualizar Evento';
                        crearEventoFormSection.style.display = 'block';
                        btnMostrarFormulario.style.display = 'none';
                        btnCancelarEdicion.style.display = 'inline-block';
                        crearEventoFormSection.scrollIntoView({ behavior: 'smooth' });
                    } else {
                        showAlert(data.message || 'Error al cargar datos del evento.', 'error');
                    }
                })
                .catch(() => showAlert('Error de conexión al cargar datos del evento.', 'error'));
        }

        if (target.classList.contains('button-delete')) {
            if (confirm('¿Estás seguro de que deseas eliminar este evento? Esta acción no se puede deshacer.')) {
                const eventoId = target.dataset.id;
                const formData = new FormData();
                formData.append('action', 'delete_evento');
                formData.append('id_evento', eventoId);

                fetch(API_URL, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert(data.message, 'success');
                        cargarEventos();
                    } else {
                        showAlert(data.message || 'Error al eliminar el evento.', 'error');
                    }
                })
                .catch(() => showAlert('Error de conexión al eliminar el evento.', 'error'));
            }
        }
    });

    cargarEventos();
});