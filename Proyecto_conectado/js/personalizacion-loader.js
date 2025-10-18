// personalizacion-loader.js - Carga y aplica la personalización del sitio
(function() {
    'use strict';

    // Cargar personalización al cargar la página
    document.addEventListener('DOMContentLoaded', cargarPersonalizacion);

    async function cargarPersonalizacion() {
        try {
            const response = await fetch('../php/obtener_personalizacion.php?action=get_all');
            const data = await response.json();

            if (data.success) {
                // Aplicar colores
                if (data.colores) {
                    aplicarColores(data.colores);
                }

                // Aplicar imágenes del carrusel
                if (data.imagenes && data.imagenes.length > 0) {
                    aplicarImagenesCarrusel(data.imagenes);
                }
            }
        } catch (error) {
            console.error('Error al cargar personalización:', error);
            // Si hay error, usar valores por defecto (los que ya están en el HTML/CSS)
        }
    }

    function aplicarColores(colores) {
        // Crear un elemento <style> dinámico con las variables CSS
        const styleId = 'custom-personalization-styles';
        let styleElement = document.getElementById(styleId);
        
        if (!styleElement) {
            styleElement = document.createElement('style');
            styleElement.id = styleId;
            document.head.appendChild(styleElement);
        }

        // Construir CSS con los colores personalizados
        let cssRules = ':root {\n';
        
        Object.keys(colores).forEach(clave => {
            const cssVar = '--' + clave.replace(/_/g, '-');
            cssRules += `  ${cssVar}: ${colores[clave]};\n`;
        });
        
        cssRules += '}\n\n';

        // Aplicar colores a elementos específicos
        cssRules += `
/* Colores personalizados aplicados dinámicamente */
header .header-top {
    background-color: ${colores.color_header || '#4A4A4A'};
}

nav#main-navigation {
    background-color: ${colores.color_nav || '#333'};
}

nav#main-navigation ul li a:hover,
nav#main-navigation ul li a.active {
    background-color: ${colores.color_nav_hover || '#0056b3'};
}

footer {
    background-color: ${colores.color_footer || '#333'};
}

.sponsor-logos-header {
    background-color: ${colores.color_header || '#4A4A4A'};
}

.sponsor-logos-header,
.mini-carrusel-preview {
    background-color: ${colores.color_carrusel_fondo || '#6c757d'};
}

/* Botones y enlaces con color primario */
.button,
a.button {
    background-color: ${colores.color_primario || '#0056b3'};
}

.button:hover,
a.button:hover {
    background-color: ${colores.color_primario || '#0056b3'};
    filter: brightness(0.9);
}

/* Botones secundarios (registrarse, etc.) */
.button.register-button {
    background-color: ${colores.color_secundario || '#28a745'};
}

.button.register-button:hover {
    background-color: ${colores.color_secundario || '#28a745'};
    filter: brightness(0.9);
}

/* Enlaces con color primario */
a {
    color: ${colores.color_primario || '#0056b3'};
}

a:hover {
    color: ${colores.color_primario || '#0056b3'};
    filter: brightness(0.8);
}

/* Títulos y encabezados */
h2, h3 {
    color: ${colores.color_primario || '#0056b3'};
}

/* Navegación activa */
nav#main-navigation ul li a.active {
    background-color: ${colores.color_primario || '#0056b3'};
}

/* Bordes y detalles */
.content-box {
    border-left: 4px solid ${colores.color_primario || '#0056b3'};
}

.event-item:hover,
.certificate-item:hover {
    border-left: 4px solid ${colores.color_primario || '#0056b3'};
}
`;

        styleElement.textContent = cssRules;
    }

    function aplicarImagenesCarrusel(imagenes) {
        // Buscar el contenedor del carrusel en la página
        const logoTrack = document.querySelector('.logo-track');
        
        if (!logoTrack) {
            // Si no hay carrusel en esta página, no hacer nada
            return;
        }

        // Limpiar contenido actual
        logoTrack.innerHTML = '';

        // Crear elementos <img> para cada imagen
        const imagenesHTML = imagenes.map(img => {
            const imgElement = document.createElement('img');
            imgElement.src = img.url_imagen;
            imgElement.alt = img.alt_texto || 'Imagen del carrusel';
            imgElement.onerror = function() {
                // Si la imagen no carga, ocultarla
                this.style.display = 'none';
                console.warn('No se pudo cargar la imagen:', img.url_imagen);
            };
            return imgElement;
        });

        // Agregar imágenes al carrusel (duplicadas para efecto de bucle)
        imagenesHTML.forEach(img => logoTrack.appendChild(img));
        // Duplicar para bucle continuo
        imagenesHTML.forEach(img => logoTrack.appendChild(img.cloneNode(true)));
    }

    // Exponer función pública para recargar personalización
    window.recargarPersonalizacion = cargarPersonalizacion;

})();
