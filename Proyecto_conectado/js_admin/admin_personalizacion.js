// admin_personalizacion.js - Gestión de personalización del sitio
document.addEventListener('DOMContentLoaded', function() {
    // Referencias a elementos
    const formColores = document.getElementById('form-colores');
    const formAgregarImagen = document.getElementById('form-agregar-imagen');
    const btnResetColores = document.getElementById('btn-reset-colores');
    const btnPreviewColores = document.getElementById('btn-preview-colores');
    const btnOrdenarImagenes = document.getElementById('btn-ordenar-imagenes');
    const successMessage = document.getElementById('success-message');
    const errorMessage = document.getElementById('error-message');
    const imagenesLista = document.getElementById('imagenes-lista');
    const miniCarruselTrack = document.getElementById('mini-carrusel-track');
    const previewColores = document.getElementById('preview-colores');
    
    // Radio buttons para tipo de imagen
    const radioUrl = document.getElementById('tipo-url');
    const radioArchivo = document.getElementById('tipo-archivo');
    const groupUrl = document.getElementById('group-url');
    const groupArchivo = document.getElementById('group-archivo');

    // Sincronizar color picker con input de texto
    const colorInputs = [
        'color_primario', 'color_secundario', 'color_header',
        'color_nav', 'color_nav_hover', 'color_footer', 'color_carrusel_fondo'
    ];

    colorInputs.forEach(name => {
        const colorPicker = document.getElementById(name);
        const textInput = document.getElementById(name + '_text');
        
        if (colorPicker && textInput) {
            colorPicker.addEventListener('input', (e) => {
                textInput.value = e.target.value.toUpperCase();
                updatePreview();
            });
            
            textInput.addEventListener('input', (e) => {
                const value = e.target.value;
                if (/^#[0-9A-Fa-f]{6}$/.test(value)) {
                    colorPicker.value = value;
                    updatePreview();
                }
            });
        }
    });

    // Cambiar entre URL y Archivo
    radioUrl.addEventListener('change', () => {
        groupUrl.style.display = 'block';
        groupArchivo.style.display = 'none';
        document.getElementById('url_imagen').required = true;
        document.getElementById('archivo_imagen').required = false;
    });

    radioArchivo.addEventListener('change', () => {
        groupUrl.style.display = 'none';
        groupArchivo.style.display = 'block';
        document.getElementById('url_imagen').required = false;
        document.getElementById('archivo_imagen').required = true;
    });

    // Cargar datos iniciales
    cargarColores();
    cargarImagenes();

    // FORMULARIO DE COLORES
    formColores.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const colores = {};
        colorInputs.forEach(name => {
            colores[name] = document.getElementById(name).value;
        });

        try {
            const response = await fetch('../php_admin/personalizacion_controller.php?action=save_colores', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(colores)
            });

            const data = await response.json();
            
            if (data.success) {
                showSuccess('Colores guardados exitosamente. Los cambios se aplicarán en todas las páginas.');
                // Aplicar cambios inmediatamente
                aplicarColores(colores);
            } else {
                showError(data.message || 'Error al guardar los colores');
            }
        } catch (error) {
            showError('Error de conexión: ' + error.message);
        }
    });

    // RESTABLECER COLORES
    btnResetColores.addEventListener('click', async () => {
        if (!confirm('¿Estás seguro de restablecer todos los colores a los valores por defecto?')) {
            return;
        }

        try {
            const response = await fetch('../php_admin/personalizacion_controller.php?action=reset_colores', {
                method: 'POST'
            });

            const data = await response.json();
            
            if (data.success) {
                showSuccess('Colores restablecidos a valores por defecto');
                cargarColores();
            } else {
                showError(data.message || 'Error al restablecer los colores');
            }
        } catch (error) {
            showError('Error de conexión: ' + error.message);
        }
    });

    // VISTA PREVIA DE COLORES
    btnPreviewColores.addEventListener('click', () => {
        previewColores.style.display = previewColores.style.display === 'none' ? 'block' : 'none';
        btnPreviewColores.textContent = previewColores.style.display === 'none' ? '👁️ Vista Previa' : '❌ Ocultar Vista Previa';
    });

    // FORMULARIO AGREGAR IMAGEN
    formAgregarImagen.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const tipoFuente = document.querySelector('input[name="tipo_fuente"]:checked').value;
        
        if (tipoFuente === 'url') {
            // Enviar URL
            const data = {
                url_imagen: document.getElementById('url_imagen').value,
                alt_texto: document.getElementById('alt_texto').value
            };

            try {
                const response = await fetch('../php_admin/personalizacion_controller.php?action=add_imagen', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });

                const result = await response.json();
                
                if (result.success) {
                    showSuccess('Imagen agregada exitosamente');
                    formAgregarImagen.reset();
                    cargarImagenes();
                } else {
                    showError(result.message || 'Error al agregar la imagen');
                }
            } catch (error) {
                showError('Error de conexión: ' + error.message);
            }
        } else {
            // Subir archivo
            const formData = new FormData();
            formData.append('archivo_imagen', document.getElementById('archivo_imagen').files[0]);
            formData.append('alt_texto', document.getElementById('alt_texto').value);

            try {
                const response = await fetch('../php_admin/personalizacion_controller.php?action=add_imagen', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();
                
                if (result.success) {
                    showSuccess('Imagen subida y agregada exitosamente');
                    formAgregarImagen.reset();
                    cargarImagenes();
                } else {
                    showError(result.message || 'Error al subir la imagen');
                }
            } catch (error) {
                showError('Error de conexión: ' + error.message);
            }
        }
    });

    // GUARDAR ORDEN DE IMÁGENES
    btnOrdenarImagenes.addEventListener('click', async () => {
        const items = document.querySelectorAll('.imagen-item');
        const imagenes = Array.from(items).map(item => parseInt(item.dataset.id));

        try {
            const response = await fetch('../php_admin/personalizacion_controller.php?action=update_orden', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ imagenes })
            });

            const data = await response.json();
            
            if (data.success) {
                showSuccess('Orden de imágenes guardado exitosamente');
            } else {
                showError(data.message || 'Error al guardar el orden');
            }
        } catch (error) {
            showError('Error de conexión: ' + error.message);
        }
    });

    // FUNCIONES AUXILIARES

    async function cargarColores() {
        try {
            const response = await fetch('../php_admin/personalizacion_controller.php?action=get_colores');
            const data = await response.json();
            
            if (data.success) {
                const colores = data.colores;
                colorInputs.forEach(name => {
                    if (colores[name]) {
                        document.getElementById(name).value = colores[name];
                        document.getElementById(name + '_text').value = colores[name];
                    }
                });
                aplicarColores(colores);
            }
        } catch (error) {
            console.error('Error al cargar colores:', error);
        }
    }

    async function cargarImagenes() {
        try {
            const response = await fetch('../php_admin/personalizacion_controller.php?action=get_imagenes');
            const data = await response.json();
            
            if (data.success) {
                renderImagenes(data.imagenes);
                renderMiniCarrusel(data.imagenes);
            }
        } catch (error) {
            console.error('Error al cargar imágenes:', error);
            imagenesLista.innerHTML = '<p class="error-message">Error al cargar las imágenes</p>';
        }
    }

    function renderImagenes(imagenes) {
        if (imagenes.length === 0) {
            imagenesLista.innerHTML = '<p style="text-align: center; color: #666;">No hay imágenes en el carrusel. Agrega la primera imagen abajo.</p>';
            return;
        }

        imagenesLista.innerHTML = imagenes.map((img, index) => `
            <div class="imagen-item" data-id="${img.id}" draggable="true">
                <span class="drag-handle" title="Arrastrar para reordenar">☰</span>
                <img src="${img.url_imagen}" alt="${img.alt_texto}" class="imagen-preview" onerror="this.src='https://via.placeholder.com/100x60?text=Error'">
                <div class="imagen-info">
                    <input type="text" value="${img.alt_texto}" data-id="${img.id}" class="alt-input" placeholder="Texto alternativo">
                    <small style="color: #666;">
                        ${img.tipo_fuente === 'archivo' ? '📁 Archivo local' : '🌐 URL externa'} | 
                        Orden: ${img.orden}
                    </small>
                </div>
                <div class="imagen-actions">
                    <button class="button btn-icon secondary btn-update-alt" data-id="${img.id}" title="Actualizar texto">💾</button>
                    <button class="button btn-icon" style="background-color: #dc3545;" data-id="${img.id}" class="btn-delete" title="Eliminar">🗑️</button>
                </div>
            </div>
        `).join('');

        // Event listeners para actualizar y eliminar
        document.querySelectorAll('.btn-update-alt').forEach(btn => {
            btn.addEventListener('click', () => actualizarAltTexto(btn.dataset.id));
        });

        document.querySelectorAll('.btn-delete').forEach(btn => {
            btn.addEventListener('click', () => eliminarImagen(btn.dataset.id));
        });

        // Drag and drop para reordenar
        enableDragAndDrop();
    }

    function renderMiniCarrusel(imagenes) {
        if (imagenes.length === 0) {
            miniCarruselTrack.innerHTML = '<p style="color: white; text-align: center;">No hay imágenes</p>';
            return;
        }

        // Duplicar imágenes para efecto de bucle
        const imagenesHTML = imagenes.map(img => 
            `<img src="${img.url_imagen}" alt="${img.alt_texto}" onerror="this.style.display='none'">`
        ).join('');
        
        miniCarruselTrack.innerHTML = imagenesHTML + imagenesHTML;
    }

    async function actualizarAltTexto(id) {
        const input = document.querySelector(`.alt-input[data-id="${id}"]`);
        const nuevoAlt = input.value;

        try {
            const response = await fetch('../php_admin/personalizacion_controller.php?action=update_imagen', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: parseInt(id), alt_texto: nuevoAlt, activo: 1 })
            });

            const data = await response.json();
            
            if (data.success) {
                showSuccess('Texto alternativo actualizado');
            } else {
                showError(data.message || 'Error al actualizar');
            }
        } catch (error) {
            showError('Error de conexión: ' + error.message);
        }
    }

    async function eliminarImagen(id) {
        if (!confirm('¿Estás seguro de eliminar esta imagen del carrusel?')) {
            return;
        }

        try {
            const response = await fetch('../php_admin/personalizacion_controller.php?action=delete_imagen', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: parseInt(id) })
            });

            const data = await response.json();
            
            if (data.success) {
                showSuccess('Imagen eliminada exitosamente');
                cargarImagenes();
            } else {
                showError(data.message || 'Error al eliminar la imagen');
            }
        } catch (error) {
            showError('Error de conexión: ' + error.message);
        }
    }

    function aplicarColores(colores) {
        const root = document.documentElement;
        Object.keys(colores).forEach(key => {
            root.style.setProperty('--' + key.replace(/_/g, '-'), colores[key]);
        });
    }

    function updatePreview() {
        const colores = {};
        colorInputs.forEach(name => {
            colores[name] = document.getElementById(name).value;
        });
        aplicarColores(colores);
    }

    function enableDragAndDrop() {
        const items = document.querySelectorAll('.imagen-item');
        let draggedElement = null;

        items.forEach(item => {
            item.addEventListener('dragstart', function(e) {
                draggedElement = this;
                this.style.opacity = '0.5';
            });

            item.addEventListener('dragend', function(e) {
                this.style.opacity = '1';
            });

            item.addEventListener('dragover', function(e) {
                e.preventDefault();
            });

            item.addEventListener('drop', function(e) {
                e.preventDefault();
                if (draggedElement !== this) {
                    const allItems = [...imagenesLista.querySelectorAll('.imagen-item')];
                    const draggedIndex = allItems.indexOf(draggedElement);
                    const targetIndex = allItems.indexOf(this);

                    if (draggedIndex < targetIndex) {
                        this.parentNode.insertBefore(draggedElement, this.nextSibling);
                    } else {
                        this.parentNode.insertBefore(draggedElement, this);
                    }
                }
            });
        });
    }

    function showSuccess(message) {
        successMessage.textContent = message;
        successMessage.style.display = 'block';
        errorMessage.style.display = 'none';
        setTimeout(() => {
            successMessage.style.display = 'none';
        }, 5000);
    }

    function showError(message) {
        errorMessage.textContent = message;
        errorMessage.style.display = 'block';
        successMessage.style.display = 'none';
    }

    // Logout functionality
    const logoutBtn = document.getElementById('logout-btn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', (e) => {
            e.preventDefault();
            if (window.sessionGuard && typeof window.sessionGuard.logout === 'function') {
                window.sessionGuard.logout();
            } else {
                window.location.href = 'login_admin.html';
            }
        });
    }
});
