
document.addEventListener('DOMContentLoaded', () => {
    const certificatesListDiv = document.getElementById('certificates-list');

    function cargarConstancias() {
        certificatesListDiv.innerHTML = '<p>Cargando tus constancias...</p>';

        fetch('../php/constancias_usuario.php')
            .then(response => {
                if (!response.ok) {
                    throw new Error('No se pudo obtener la información. Por favor, inicia sesión de nuevo.');
                }
                return response.json();
            })
            .then(data => {
                if (data.error) {
                    throw new Error(data.error);
                }
                renderConstancias(data);
            })
            .catch(error => {
                certificatesListDiv.innerHTML = `<p class="error-message">${error.message}</p>`;
            });
    }

    function renderConstancias(constancias) {
        certificatesListDiv.innerHTML = '';
        if (constancias.length === 0) {
            certificatesListDiv.innerHTML = '<p>No estás inscrito en eventos que generen constancias.</p>';
            return;
        }

        constancias.forEach(constancia => {
            const item = document.createElement('div');
            item.className = 'certificate-item';

            let statusClass = 'status-incomplete';
            let buttonHtml = '<button class="button download-button" disabled>No disponible</button>';

            switch (constancia.estado) {
                case 'Disponible':
                    statusClass = 'status-available';
                    buttonHtml = `<a href="${constancia.url_descarga}" class="button download-button" download>Descargar PDF</a>`;
                    break;
                case 'Pendiente de Emisión':
                    statusClass = 'status-pending';
                    buttonHtml = '<button class="button download-button" disabled>Pendiente</button>';
                    break;
                case 'Asistencia Incompleta':
                default:
                    statusClass = 'status-incomplete';
                    break;
            }

            item.innerHTML = `
                <h4>${constancia.nombre_evento}</h4>
                <p>Estado: <span class="${statusClass}">${constancia.estado}</span></p>
                <div class="item-actions">${buttonHtml}</div>
            `;
            certificatesListDiv.appendChild(item);
        });
    }

    cargarConstancias();
});