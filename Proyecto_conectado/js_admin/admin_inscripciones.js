document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('search-input');
    const tablaInscripcionesBody = document.getElementById('tabla-inscripciones-body');
    const loadingIndicator = document.getElementById('loading-indicator');
    const noResultsIndicator = document.getElementById('no-results-indicator');
    let searchTimeout;

    function cargarInscripciones(searchTerm = '') {
        loadingIndicator.style.display = 'block';
        noResultsIndicator.style.display = 'none';
        tablaInscripcionesBody.innerHTML = '';

        const url = `../php_admin/ver_inscripciones.php?search=${encodeURIComponent(searchTerm)}`;

        fetch(url)
            .then(response => response.json())
            .then(data => {
                loadingIndicator.style.display = 'none';
                if (data.error) throw new Error(data.error);
                renderTablaInscripciones(data.inscripciones);
            })
            .catch(error => {
                loadingIndicator.style.display = 'none';
                noResultsIndicator.textContent = 'Error al cargar los datos.';
                noResultsIndicator.style.display = 'block';
                console.error('Error al cargar inscripciones:', error);
            });
    }

    function renderTablaInscripciones(inscripciones) {
        tablaInscripcionesBody.innerHTML = '';
        if (!inscripciones || inscripciones.length === 0) {
            noResultsIndicator.style.display = 'block';
            return;
        }

        inscripciones.forEach(inscripcion => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${inscripcion.id_usuario}</td>
                <td>${escapeHtml(inscripcion.nombre_usuario)}</td>
                <td>${escapeHtml(inscripcion.nombre_evento)}</td>
                <td>${new Date(inscripcion.fecha_inscripcion).toLocaleString('es-ES')}</td>
                <td>${escapeHtml(inscripcion.estado)}</td>
            `;
            tablaInscripcionesBody.appendChild(tr);
        });
    }

    function escapeHtml(text) {
        return text ? String(text).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;") : '';
    }

    searchInput.addEventListener('input', () => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            cargarInscripciones(searchInput.value);
        }, 300);
    });

    cargarInscripciones();
});