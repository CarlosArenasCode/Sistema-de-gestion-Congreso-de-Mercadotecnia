document.addEventListener('DOMContentLoaded', () => {
    const qrContainer = document.getElementById('qr-code-container');
    const errorContainer = document.getElementById('qr-error');

    // Función para cargar el QR
    function cargarQR() {
        fetch('../php/qr_usuario.php')  // Petición al backend para obtener el QR
            .then(response => response.json())
            .then(data => {
                if (data.qr_code_data) {
                    // Si se genera el código QR, mostrarlo
                    const img = document.createElement('img');
                    img.src = `https://api.qrserver.com/v1/create-qr-code/?data=${encodeURIComponent(data.qr_code_data)}&size=250x250`;
                    qrContainer.innerHTML = '';  // Limpiar el contenedor
                    qrContainer.appendChild(img);
                    errorContainer.style.display = 'none';  // Ocultar mensaje de error
                } else {
                    throw new Error(data.error || 'No se encontró el QR');
                }
            })
            .catch(error => {
                qrContainer.style.display = 'none';  // Ocultar el contenedor del QR si hay error
                errorContainer.style.display = 'block';  // Mostrar el mensaje de error
                errorContainer.textContent = 'Error al cargar el QR: ' + error.message;
                if (error.message === 'Usuario no logueado') {
                    window.location.href = 'login.html';  // Redirigir a la página de login si no está logueado
                }
            });
    }

    cargarQR();  // Cargar el QR al principio
    setInterval(cargarQR, 60000);  // Actualizar el QR cada 60 segundos

    // Opcional: Mostrar un contador regresivo para la actualización del QR
    const refreshTime = document.getElementById('refresh-time');
    let segundos = 60;
    setInterval(() => {
        segundos--;
        if (segundos <= 0) {
            segundos = 60;
        }
        refreshTime.textContent = segundos;
    }, 1000);
});
