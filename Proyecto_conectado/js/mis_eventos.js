document.addEventListener('DOMContentLoaded', function() {
    const eventListDiv = document.getElementById('event-list');
    const userNameSpan = document.getElementById('user-name'); // Para el nombre en la barra
    const userNameMainSpan = document.getElementById('user-name-main'); // Para el H2

    
    const storedUserName = localStorage.getItem('userName'); 
    if (storedUserName) {
        if(userNameSpan) userNameSpan.textContent = storedUserName;
        if(userNameMainSpan) userNameMainSpan.textContent = storedUserName;
    } else {
        if(userNameSpan) userNameSpan.textContent = "Usuario";
        if(userNameMainSpan) userNameMainSpan.textContent = "Usuario";
    }

    // --- Cargar Mis Próximos Eventos ---
    fetch('../php/eventos_inscrito.php') 
        .then(response => {
            if (!response.ok) {
                throw new Error('La respuesta de la red no fue correcta');
            }
            return response.json();
        })
        .then(data => {
            if (data.error) {
                console.error('Error del servidor:', data.error);
                eventListDiv.innerHTML = '<p>Hubo un error al cargar tus eventos. Por favor, intenta más tarde.</p>';
            } else if (data.length === 0) {
                eventListDiv.innerHTML = '<p>No tienes eventos próximos registrados.</p>';
            } else {
                eventListDiv.innerHTML = ''; // Limpiar el mensaje predeterminado
                data.forEach(evento => {
                    const eventElement = document.createElement('div');
                    eventElement.classList.add('event-item');

                    // Formatear la fecha (DD/MM/AAAA)
                    const fecha = new Date(evento.fecha_inicio + 'T00:00:00'); // Asegura que sea local
                    const formattedDate = fecha.toLocaleDateString('es-ES', {
                        day: '2-digit',
                        month: '2-digit',
                        year: 'numeric'
                    });

                    // Formatear la hora (HH:MM)
                    const formattedTime = evento.hora_inicio.substring(0, 5);

                    eventElement.innerHTML = `
                        <h4>${evento.nombre_evento}</h4>
                        <p><strong>Fecha:</strong> ${formattedDate}</p>
                        <p><strong>Hora:</strong> ${formattedTime}</p>
                        <p><strong>Lugar:</strong> ${evento.lugar || 'Por definir'}</p>
                        ${evento.ponente ? `<p><strong>Ponente:</strong> ${evento.ponente}</p>` : ''}
                        <hr>
                    `;
                    eventListDiv.appendChild(eventElement);
                });
            }
        })
        .catch(error => {
            console.error('Error al obtener los eventos:', error);
            eventListDiv.innerHTML = '<p>No se pudieron cargar tus eventos. Revisa tu conexión o intenta más tarde.</p>';
        });
});