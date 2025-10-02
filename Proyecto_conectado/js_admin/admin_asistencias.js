document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search-input');
    const attendanceTableBody = document.getElementById('attendance-table-body');
    const loadingIndicator = document.getElementById('loading-indicator');
    const noResultsIndicator = document.getElementById('no-results-indicator');
    const exportCsvButton = document.getElementById('export-csv-button');

    const API_URL = '../php_admin/reporte_asistencia_controller.php';
    let searchTimeout;

    function cargarAsistencias(searchTerm = '') {
        loadingIndicator.style.display = 'block';
        noResultsIndicator.style.display = 'none';
        attendanceTableBody.innerHTML = '';

        const queryParams = `action=get_asistencias&search=${encodeURIComponent(searchTerm)}`;

        fetch(`${API_URL}?${queryParams}`)
            .then(response => response.json())
            .then(data => {
                loadingIndicator.style.display = 'none';
                if (data.success && data.asistencias) {
                    renderAsistencias(data.asistencias);
                } else {
                    noResultsIndicator.textContent = `Error: ${data.error || 'No se pudieron cargar los datos.'}`;
                    noResultsIndicator.style.display = 'block';
                }
            })
            .catch(error => {
                loadingIndicator.style.display = 'none';
                noResultsIndicator.textContent = 'Error de conexión.';
                noResultsIndicator.style.display = 'block';
                console.error('Error en fetch:', error);
            });
    }

    function renderAsistencias(asistencias) {
        if (asistencias.length === 0) {
            noResultsIndicator.style.display = 'block';
            return;
        }

        asistencias.forEach(asistencia => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${asistencia.id_usuario || '-'}</td>
                <td>${asistencia.nombre_usuario || 'Usuario no encontrado'}</td>
                <td>${asistencia.nombre_evento || 'Evento no encontrado'}</td>
                <td>${asistencia.fecha || '-'}</td>
                <td>${asistencia.hora_entrada || '-'}</td>
                <td>${asistencia.hora_salida || '-'}</td>
                <td>${asistencia.duracion_formateada || '-'}</td>
            `;
            attendanceTableBody.appendChild(tr);
        });
    }

    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            cargarAsistencias(this.value);
        }, 300);
    });
    
    exportCsvButton.addEventListener('click', () => {
        const searchTerm = searchInput.value;
        const exportUrl = `${API_URL}?action=export_asistencias_csv&search=${encodeURIComponent(searchTerm)}`;
        window.open(exportUrl, '_blank');
    });

    cargarAsistencias();
});