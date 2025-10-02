document.addEventListener('DOMContentLoaded', function() {
    const usuariosTableBody = document.getElementById('usuarios-table-body');
    const searchUsuarioInput = document.getElementById('search-usuario');
    const formUsuario = document.getElementById('form-usuario');
    const formUsuarioTituloDisplay = document.getElementById('form-usuario-titulo-display');
    const idUsuarioHiddenInput = document.getElementById('id_usuario_hidden');
    const btnMostrarFormUsuario = document.getElementById('btn-mostrar-form-usuario');
    const crearUsuarioFormSection = document.getElementById('crear-usuario-form-section');
    const btnCancelarEdicionUsuario = document.getElementById('btn-cancelar-edicion-usuario');
    const btnLimpiarFormUsuario = document.getElementById('btn-limpiar-form-usuario');
    const loadingIndicatorUsuarios = document.getElementById('loading-indicator-usuarios');
    const noResultsIndicatorUsuarios = document.getElementById('no-results-indicator-usuarios');
    const alertPlaceholderUser = document.getElementById('alert-placeholder-user');

    const API_URL = '../php_admin/usuarios_controller.php';

    function showAlertUser(message, type = 'success') {
        alertPlaceholderUser.innerHTML = `<div class="alert alert-${type}" role="alert">${message}</div>`;
        setTimeout(() => {
            alertPlaceholderUser.innerHTML = '';
        }, 5000);
    }

    function cargarUsuarios(searchTerm = '') {
        loadingIndicatorUsuarios.style.display = 'block';
        noResultsIndicatorUsuarios.style.display = 'none';
        usuariosTableBody.innerHTML = '';

        fetch(`${API_URL}?action=get_usuarios&search=${encodeURIComponent(searchTerm)}`)
            .then(response => response.json())
            .then(data => {
                loadingIndicatorUsuarios.style.display = 'none';
                if (data.success && data.usuarios) {
                    if (data.usuarios.length === 0) {
                        noResultsIndicatorUsuarios.style.display = 'block';
                    } else {
                        renderUsuarios(data.usuarios);
                    }
                } else {
                    usuariosTableBody.innerHTML = `<tr><td colspan="7" class="text-center text-danger">Error al cargar usuarios: ${data.message || 'Desconocido'}</td></tr>`;
                    console.error('Error al cargar usuarios:', data.message);
                }
            })
            .catch(error => {
                loadingIndicatorUsuarios.style.display = 'none';
                usuariosTableBody.innerHTML = `<tr><td colspan="7" class="text-center text-danger">Error de conexión al cargar usuarios.</td></tr>`;
                console.error('Fetch error usuarios:', error);
            });
    }

    function renderUsuarios(usuarios) {
        usuarios.forEach(usuario => {
            const tr = document.createElement('tr');
            tr.dataset.id_usuario = usuario.id_usuario;
            tr.innerHTML = `
                <td>${escapeHtml(usuario.id_usuario)}</td>
                <td>${escapeHtml(usuario.matricula) || '-'}</td>
                <td>${escapeHtml(usuario.nombre_completo)}</td>
                <td>${escapeHtml(usuario.email)}</td>
                <td>${escapeHtml(usuario.semestre) || '-'}</td>
                <td>${escapeHtml(usuario.fecha_registro_formateada) || '-'}</td>
                <td class="actions">
                    <button class="button-edit-usuario button small primary" data-id="${usuario.id_usuario}">Editar</button>
                    <button class="button-delete-usuario button small danger" data-id="${usuario.id_usuario}">Eliminar</button>
                </td>
            `;
            usuariosTableBody.appendChild(tr);
        });
    }
    
    function escapeHtml(unsafe) {
        if (unsafe === null || typeof unsafe === 'undefined') return '';
        return unsafe
             .toString()
             .replace(/&/g, "&amp;")
             .replace(/</g, "&lt;")
             .replace(/>/g, "&gt;")
             .replace(/"/g, "&quot;")
             .replace(/'/g, "&#039;");
    }

    function limpiarFormularioUsuario() {
        formUsuario.reset();
        idUsuarioHiddenInput.value = '';
        formUsuarioTituloDisplay.textContent = 'Añadir Nuevo Usuario';
        btnCancelarEdicionUsuario.style.display = 'none';
        formUsuario.querySelector('button[type="submit"]').textContent = 'Guardar Usuario';
        document.getElementById('password').placeholder = ''; // Quitar placeholder específico de edición
    }

    btnLimpiarFormUsuario.addEventListener('click', limpiarFormularioUsuario);

    btnMostrarFormUsuario.addEventListener('click', () => {
        limpiarFormularioUsuario();
        crearUsuarioFormSection.style.display = 'block';
        btnMostrarFormUsuario.style.display = 'none';
        crearUsuarioFormSection.scrollIntoView({ behavior: 'smooth' });
    });

    btnCancelarEdicionUsuario.addEventListener('click', () => {
        limpiarFormularioUsuario();
        crearUsuarioFormSection.style.display = 'none';
        btnMostrarFormUsuario.style.display = 'inline-block';
    });

    formUsuario.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(formUsuario);
        // 'action' se puede añadir aquí o el PHP puede inferirlo
        // formData.append('action', 'save_usuario'); 

        fetch(API_URL, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlertUser(data.message, 'success');
                limpiarFormularioUsuario();
                crearUsuarioFormSection.style.display = 'none';
                btnMostrarFormUsuario.style.display = 'inline-block';
                cargarUsuarios(searchUsuarioInput.value); // Recargar con el filtro actual
            } else {
                showAlertUser(data.message || 'Error al guardar el usuario.', 'error');
            }
        })
        .catch(error => {
            showAlertUser('Error de conexión al guardar el usuario.', 'error');
            console.error('Error en fetch guardar usuario:', error);
        });
    });

    usuariosTableBody.addEventListener('click', function(e) {
        const target = e.target;
        const userId = target.dataset.id;

        if (target.classList.contains('button-edit-usuario')) {
            fetch(`${API_URL}?action=get_usuario_detalle&id_usuario=${userId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.usuario) {
                        const u = data.usuario;
                        idUsuarioHiddenInput.value = u.id_usuario;
                        document.getElementById('matricula').value = u.matricula || '';
                        document.getElementById('nombre_completo').value = u.nombre_completo || '';
                        document.getElementById('email').value = u.email || '';
                        document.getElementById('semestre').value = u.semestre || '';
                        // document.getElementById('qr_code_data').value = u.qr_code_data || ''; // Si tienes este campo
                        document.getElementById('password').value = ''; // Limpiar campo de contraseña
                        document.getElementById('password').placeholder = 'Dejar en blanco para no cambiar';
                        
                        formUsuarioTituloDisplay.textContent = 'Editar Usuario';
                        formUsuario.querySelector('button[type="submit"]').textContent = 'Actualizar Usuario';
                        crearUsuarioFormSection.style.display = 'block';
                        btnMostrarFormUsuario.style.display = 'none';
                        btnCancelarEdicionUsuario.style.display = 'inline-block';
                        crearUsuarioFormSection.scrollIntoView({ behavior: 'smooth' });
                    } else {
                        showAlertUser(data.message || 'Error al cargar datos del usuario.', 'error');
                    }
                })
                .catch(error => {
                    showAlertUser('Error de conexión al cargar datos del usuario.', 'error');
                    console.error('Error fetch edit usuario:', error);
                });
        } else if (target.classList.contains('button-delete-usuario')) {
            if (confirm(`¿Estás seguro de que deseas eliminar al usuario ID ${userId}? Esta acción no se puede deshacer.`)) {
                const formData = new FormData();
                formData.append('action', 'delete_usuario');
                formData.append('id_usuario', userId);

                fetch(API_URL, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlertUser(data.message, 'success');
                        cargarUsuarios(searchUsuarioInput.value); // Recargar con filtro actual
                    } else {
                        showAlertUser(data.message || 'Error al eliminar el usuario.', 'error');
                    }
                })
                .catch(error => {
                    showAlertUser('Error de conexión al eliminar el usuario.', 'error');
                    console.error('Error fetch delete usuario:', error);
                });
            }
        }
    });

    let searchTimeout;
    searchUsuarioInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            cargarUsuarios(this.value);
        }, 300); // Espera 300ms después de que el usuario deja de escribir
    });

    // Carga inicial de usuarios
    cargarUsuarios();
});