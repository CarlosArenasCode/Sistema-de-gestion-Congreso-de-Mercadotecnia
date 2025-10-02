document.addEventListener("DOMContentLoaded", () => {
    // Cargar eventos en el select
    fetch("../php/ver_evento.php")
        .then(response => response.json())
        .then(data => {
            const select = document.getElementById("evento");
            select.innerHTML = '<option value="">Selecciona el evento...</option>';
            data.forEach(evento => {
                const option = document.createElement("option");
                option.value = evento.id_evento;
                option.textContent = `${evento.nombre_evento} - ${evento.fecha}`;
                select.appendChild(option);
            });
        })
        .catch(error => {
            console.error("Error al cargar eventos:", error);
            const select = document.getElementById("evento");
            select.innerHTML = '<option value="">Error al cargar eventos</option>';
        });

    // Cargar justificaciones del usuario
    fetch("../php/ver_justificaciones.php")
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                console.error(data.error);
                return;
            }

            const tabla = document.getElementById("tabla-justificaciones").getElementsByTagName('tbody')[0];
            tabla.innerHTML = ''; // Limpiar la tabla antes de agregar nuevos datos

            data.forEach(justificacion => {
                const row = tabla.insertRow();
                
                const cellEvento = row.insertCell(0);
                const cellFechaFalta = row.insertCell(1);
                const cellMotivo = row.insertCell(2);
                const cellEstado = row.insertCell(3);
                const cellFechaRevision = row.insertCell(4);

                cellEvento.textContent = justificacion.nombre_evento;
                cellFechaFalta.textContent = justificacion.fecha_falta;
                cellMotivo.textContent = justificacion.motivo;
                cellEstado.textContent = justificacion.estado;
                cellFechaRevision.textContent = justificacion.fecha_revision ? justificacion.fecha_revision : 'Pendiente';
            });
        })
        .catch(error => {
            console.error("Error al cargar justificaciones:", error);
        });
});
