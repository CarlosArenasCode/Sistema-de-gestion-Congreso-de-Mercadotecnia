document.addEventListener('DOMContentLoaded', () => {
    const filterDaySelect = document.getElementById('filter-day');
    const listaEventosContainer = document.getElementById('lista-eventos');
    let allEvents = []; // To store all fetched events

    function formatDate(dateString) {
        const date = new Date(dateString);
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    async function loadEventsAndPopulateFilter() {
        try {
            const response = await fetch('../php/ver_evento.php');
            if (!response.ok) {
                if (response.status === 401) {
                    alert('Sesión expirada o no autenticado. Por favor, inicie sesión de nuevo.');
                    listaEventosContainer.innerHTML = '<p class="error-message">Debes iniciar sesión para ver los eventos.</p>';
                    return;
                }
                throw new Error(`Error ${response.status}: ${await response.text()}`);
            }
            allEvents = await response.json();

            if (allEvents.error) {
                listaEventosContainer.innerHTML = `<p class="error-message">Error: ${allEvents.error}</p>`;
                return;
            }

            populateFilter(allEvents);
            displayFilteredEvents();

        } catch (error) {
            console.error('Error al cargar eventos:', error);
            listaEventosContainer.innerHTML = `<p class="error-message">No se pudieron cargar los eventos. Intente más tarde. ${error.message}</p>`;
        }
    }

    function populateFilter(events) {
        filterDaySelect.innerHTML = '<option value="all">Todos los días</option>';
        const uniqueDates = [...new Set(events.map(event => formatDate(event.fecha_inicio)))].sort();

        uniqueDates.forEach((date, index) => {
            const option = document.createElement('option');
            option.value = date;
            const displayDate = new Date(date + 'T00:00:00');
            option.textContent = `Día ${index + 1} (${displayDate.toLocaleDateString('es-ES', { year: 'numeric', month: 'long', day: 'numeric' })})`;
            filterDaySelect.appendChild(option);
        });
    }

    function displayFilteredEvents() {
        const selectedDay = filterDaySelect.value;
        let eventsToDisplay = allEvents;

        if (selectedDay !== "all") {
            eventsToDisplay = allEvents.filter(event => formatDate(event.fecha_inicio) === selectedDay);
        }
        renderEvents(eventsToDisplay);
    }

    function renderEvents(eventsToDisplay) {
        listaEventosContainer.innerHTML = '';

        if (eventsToDisplay.length === 0) {
            listaEventosContainer.innerHTML = '<p>No hay eventos disponibles para el filtro seleccionado.</p>';
            return;
        }

        eventsToDisplay.forEach(event => {
            const eventCard = document.createElement('div');
            eventCard.className = 'event-card';

            const cuposDisponibles = event.cupo_maximo - event.cupo_actual;
            const fechaInicio = new Date(event.fecha_inicio + 'T00:00:00');
            const fechaFin = new Date(event.fecha_fin + 'T00:00:00');

            const fechaInicioFormatted = fechaInicio.toLocaleDateString('es-ES', {
                weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
            });

            const fechaFinFormatted = fechaFin.toLocaleDateString('es-ES', {
                weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
            });

            const horaInicioFormatted = event.hora_inicio.substring(0, 5);
            const horaFinFormatted = event.hora_fin.substring(0, 5);

            let buttonHtml = '';
            // Convertir a número para asegurar comparación correcta
            const isInscrito = parseInt(event.is_inscrito) === 1;
            
            if (isInscrito) {
                buttonHtml = `<button class="button cancel-button" data-event-id="${event.id_evento}">Cancelar Inscripción</button>`;
            } else if (event.cupo_actual >= event.cupo_maximo) {
                buttonHtml = `<button class="button disabled-button" disabled>Cupo Lleno</button>`;
            } else {
                buttonHtml = `<button class="button inscribe-button" data-event-id="${event.id_evento}">Inscribirse</button>`;
            }

            const fechaHtml = (fechaInicio.getTime() === fechaFin.getTime())
                ? `<p><strong>Fecha:</strong> ${fechaInicioFormatted}</p>`
                : `<p><strong>Fecha de inicio:</strong> ${fechaInicioFormatted}</p>
                   <p><strong>Fecha de fin:</strong> ${fechaFinFormatted}</p>`;

            eventCard.innerHTML = `
                <h3>${event.nombre_evento}</h3>
                ${fechaHtml}
                <p><strong>Horario:</strong> ${horaInicioFormatted} - ${horaFinFormatted}</p>
                <p><strong>Lugar:</strong> ${event.lugar || 'No especificado'}</p>
                <p><strong>Ponente:</strong> ${event.ponente || 'No especificado'}</p>
                <p><strong>Descripción:</strong> ${event.descripcion || 'Sin descripción.'}</p>
                <p><strong>Cupos disponibles:</strong> ${cuposDisponibles > 0 ? cuposDisponibles : 0} / ${event.cupo_maximo}</p>
                <p><strong>Genera constancia:</strong> ${event.genera_constancia ? 'Sí' : 'No'}</p>
                ${buttonHtml}
            `;
            listaEventosContainer.appendChild(eventCard);
        });

        // Listeners para botones
        document.querySelectorAll('.inscribe-button').forEach(button => {
            button.addEventListener('click', () => handleInscriptionAction(button.dataset.eventId, 'inscribir'));
        });
        document.querySelectorAll('.cancel-button').forEach(button => {
            button.addEventListener('click', () => handleInscriptionAction(button.dataset.eventId, 'cancelar'));
        });
    }

    async function handleInscriptionAction(eventId, actionType) {
        const url = actionType === 'inscribir' ? '../php/inscribir_evento.php' : '../php/cancelar_inscripcion.php';
        const confirmationMessage = actionType === 'inscribir'
            ? '¿Confirmas tu inscripción a este evento?'
            : '¿Estás seguro de que deseas cancelar tu inscripción a este evento?';

        if (!confirm(confirmationMessage)) return;

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id_evento: eventId }),
            });

            const result = await response.json();

            if (!response.ok) {
                alert(`Error: ${result.error || 'Ocurrió un problema.'}`);
                if (response.status === 401) window.location.href = 'login.html';
                return;
            }

            if (result.success) {
                alert(result.message);
                loadEventsAndPopulateFilter();
            } else {
                alert(`Error: ${result.error || 'No se pudo completar la acción.'}`);
            }
        } catch (error) {
            console.error(`Error en ${actionType}:`, error);
            alert(`No se pudo ${actionType === 'inscribir' ? 'inscribir' : 'cancelar la inscripción'}. Intente más tarde.`);
        }
    }

    loadEventsAndPopulateFilter();
    filterDaySelect.addEventListener('change', displayFilteredEvents);
});
