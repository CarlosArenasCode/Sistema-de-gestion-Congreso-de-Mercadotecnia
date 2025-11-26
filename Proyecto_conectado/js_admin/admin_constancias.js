// js_admin/admin_constancias.js
document.addEventListener('DOMContentLoaded', () => {
    const filtroEventoSelect = document.getElementById('filtro-evento');
    const constanciasTableBody = document.querySelector('.constancias-table tbody');
    const alertPlaceholder = document.getElementById('alert-placeholder');

    function showAlert(message, type = 'success') {
        const alertClass = type === 'success' ? 'message success' : 'error-message';
        alertPlaceholder.innerHTML = `<div class="${alertClass}" role="alert">${message}</div>`;
        setTimeout(() => alertPlaceholder.innerHTML = '', 5000);
    }

    async function cargarFiltroEventos() {
        try {
            console.log('Cargando eventos para constancias...');
            const response = await fetch('../php_admin/constancias_controller.php?action=get_eventos_filtro');
            console.log('Response status:', response.status);
            
            const text = await response.text();
            console.log('Response text:', text);
            
            const data = JSON.parse(text);
            console.log('Datos parseados:', data);
            
            if (data.success && data.eventos) {
                console.log('Eventos encontrados:', data.eventos.length);
                data.eventos.forEach(evento => {
                    const option = document.createElement('option');
                    option.value = evento.id_evento;
                    option.textContent = evento.nombre_evento;
                    filtroEventoSelect.appendChild(option);
                    console.log('Evento agregado:', evento.nombre_evento);
                });
            } else {
                console.warn('No hay eventos o la respuesta no fue exitosa:', data);
            }
        } catch (error) {
            console.error('Error cargando eventos:', error);
            showAlert('Error cargando eventos: ' + error.message, 'error');
        }
    }

    async function cargarElegibles() {
        const eventoId = filtroEventoSelect.value;
        if (!eventoId) {
            constanciasTableBody.innerHTML = '<tr><td colspan="5" style="text-align: center;">Seleccione un evento para ver los participantes.</td></tr>';
            return;
        }

        constanciasTableBody.innerHTML = '<tr><td colspan="5" style="text-align: center;">Cargando...</td></tr>';

        try {
            const response = await fetch(`../php_admin/constancias_controller.php?action=get_elegibles&id_evento=${eventoId}`);
            const data = await response.json();
            if (data.success) {
                renderTabla(data.usuarios, eventoId);
            } else {
                throw new Error(data.error || 'No se pudieron cargar los datos.');
            }
        } catch (error) {
            constanciasTableBody.innerHTML = `<tr><td colspan="5" class="error-message">${error.message}</td></tr>`;
        }
    }

    function renderTabla(usuarios, eventoId) {
        constanciasTableBody.innerHTML = '';
        if (usuarios.length === 0) {
            constanciasTableBody.innerHTML = '<tr><td colspan="5" style="text-align: center;">No hay usuarios inscritos en este evento.</td></tr>';
            return;
        }

        usuarios.forEach(user => {
            const tr = document.createElement('tr');
            const elegibleClass = user.elegible ? 'si' : 'no';
            const elegibleText = user.elegible ? 'Sí' : 'No';

            let actionsHtml = '';
            if (user.elegible) {
                if (user.emitida) {
                    actionsHtml = `
                        <a href="../${user.ruta_archivo_pdf}" class="button small secondary" target="_blank">Ver PDF</a>
                        <button class="button small cancel-button btn-regenerar" data-user-id="${user.id_usuario}" data-event-id="${eventoId}">Regenerar</button>
                    `;
                } else {
                    actionsHtml = `<button class="button small register-button btn-generar" data-user-id="${user.id_usuario}" data-event-id="${eventoId}">Generar</button>`;
                }
            } else {
                actionsHtml = `<button class="button small" disabled>No elegible</button>`;
            }

            tr.innerHTML = `
                <td>${user.nombre_completo}</td>
                <td><span class="estado-cumplimiento ${elegibleClass}">${elegibleText}</span></td>
                <td>${user.emitida ? '<span class="status-available">Emitida</span>' : '<span class="status-pending">No emitida</span>'}</td>
                <td class="actions">${actionsHtml}</td>
            `;
            constanciasTableBody.appendChild(tr);
        });
    }

    filtroEventoSelect.addEventListener('change', cargarElegibles);

    constanciasTableBody.addEventListener('click', async (e) => {
        const target = e.target;
        if (target.matches('.btn-generar, .btn-regenerar')) {
            const userId = target.dataset.userId;
            const eventId = target.dataset.eventId;
            const actionText = target.matches('.btn-generar') ? 'generar' : 'regenerar';
            
            if (!confirm(`¿Seguro que deseas ${actionText} la constancia para este usuario?`)) return;

            target.disabled = true;
            target.textContent = 'Procesando...';

            const formData = new FormData();
            formData.append('action', 'generar_una_constancia');
            formData.append('id_usuario', userId);
            formData.append('id_evento', eventId);
            
            try {
                const response = await fetch('../php_admin/constancias_controller.php', { method: 'POST', body: formData });
                const result = await response.json();
                
                if (result.success) {
                    showAlert(result.message, 'success');
                    cargarElegibles(); // Recargar la tabla
                } else {
                    throw new Error(result.message || 'Error desconocido');
                }
            } catch (error) {
                showAlert(`Error al ${actionText}: ${error.message}`, 'error');
                target.disabled = false;
                target.textContent = actionText.charAt(0).toUpperCase() + actionText.slice(1);
            }
        }
    });

    cargarFiltroEventos();
});