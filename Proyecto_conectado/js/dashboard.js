document.addEventListener('DOMContentLoaded', function () {
    fetch('../php/usuario.php')
        .then(response => response.json())
        .then(data => {
            if (data.nombre) {
                // Solo actualizar el ID 'user-name' si existe
                const userNameElement = document.getElementById('user-name');
                if (userNameElement) {
                    userNameElement.textContent = data.nombre;
                }

                
                const userNameMainElement = document.getElementById('user-name-main');
                if (userNameMainElement) {
                    userNameMainElement.textContent = data.nombre;
                }

            } else {
                window.location.href = 'login.html'; // Redirigir si no hay sesión activa
            }
        })
        .catch(error => {
            console.error('Error al obtener nombre del usuario:', error);
            window.location.href = 'login.html'; // Redirigir al login en caso de error
        });
});
