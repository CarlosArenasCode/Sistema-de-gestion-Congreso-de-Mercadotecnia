/**
 * terminos_modal.js
 * Se encarga de verificar si el usuario aceptó los TyC y bloquear la pantalla si no.
 */

document.addEventListener('DOMContentLoaded', function() {
    // Solo ejecutamos si estamos en una página que requiere verificación
    // (Evitamos que corra en login o registro para no molestar antes de entrar)
    
    // MODIFICACIÓN: Se deshabilita la verificación automática al inicio
    // verificarTerminos();
});

function verificarTerminos() {
    fetch('../php/verificar_tyc.php')
        .then(response => response.json())
        .then(data => {
            // Si la petición fue exitosa Y el usuario NO ha aceptado (aceptado === false)
            if (data.status === 'success' && data.aceptado === false) {
                mostrarModalTyC();
            }
        })
        .catch(error => {
            console.error('Error verificando TyC:', error);
        });
}

function mostrarModalTyC() {
    // HTML del Modal con estilos inline para asegurar que se vea bien sin CSS externo
    const modalHTML = `
        <div id="modalTyC" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.9); z-index: 99999; display: flex; justify-content: center; align-items: center; font-family: 'Segoe UI', sans-serif; backdrop-filter: blur(5px);">
            <div style="background: white; width: 90%; max-width: 480px; padding: 30px; border-radius: 12px; box-shadow: 0 20px 50px rgba(0,0,0,0.5); text-align: center; position: relative; animation: fadeIn 0.5s ease;">
                
                <!-- Icono o Logo -->
                <div style="margin-bottom: 20px;">
                    <img src="../Logos/MKT_LOGO.png" alt="Logo" style="height: 60px;">
                </div>

                <h2 style="color: #2c3e50; margin: 0 0 15px 0; font-size: 24px;">Aviso Importante</h2>
                
                <p style="color: #555; margin-bottom: 25px; line-height: 1.6; font-size: 15px;">
                    Hola. Para continuar utilizando el <strong>Sistema de Gestión del Congreso</strong>, requerimos tu consentimiento para el tratamiento de tus datos personales (Matrícula, QR, Asistencias), conforme a nuestra nueva política de privacidad.
                </p>
                
                <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 25px; border: 1px solid #e9ecef; text-align: left;">
                    <label style="display: flex; align-items: start; gap: 10px; cursor: pointer;">
                        <input type="checkbox" id="checkLeido" style="margin-top: 4px; transform: scale(1.2);">
                        <span style="font-size: 14px; color: #333;">He leído y acepto los <a href="terminos_legales.html" target="_blank" style="color: #3498db; text-decoration: none; font-weight: bold;">Términos y Condiciones</a> y el Aviso de Privacidad.</span>
                    </label>
                </div>

                <div style="display: flex; flex-direction: column; gap: 10px;">
                    <button id="btnAceptarTyC" disabled style="padding: 14px; background: #bdc3c7; color: white; border: none; border-radius: 8px; font-size: 16px; font-weight: bold; cursor: not-allowed; transition: all 0.3s;">
                        Continuar al Sistema
                    </button>
                </div>
                
                <p style="font-size: 11px; color: #aaa; margin-top: 20px;">
                    Acción requerida por única ocasión. <br> Fecha: ${new Date().toLocaleDateString()}
                </p>
            </div>
        </div>
        <style>
            @keyframes fadeIn { from { opacity: 0; transform: translateY(-20px); } to { opacity: 1; transform: translateY(0); } }
        </style>
    `;

    document.body.insertAdjacentHTML('beforeend', modalHTML);
    
    // Bloquear scroll
    document.body.style.overflow = 'hidden';

    // Lógica del Checkbox
    const check = document.getElementById('checkLeido');
    const btn = document.getElementById('btnAceptarTyC');

    check.addEventListener('change', function() {
        if (this.checked) {
            btn.disabled = false;
            btn.style.background = '#27ae60';
            btn.style.cursor = 'pointer';
            btn.style.boxShadow = '0 4px 15px rgba(39, 174, 96, 0.3)';
        } else {
            btn.disabled = true;
            btn.style.background = '#bdc3c7';
            btn.style.cursor = 'not-allowed';
            btn.style.boxShadow = 'none';
        }
    });

    btn.addEventListener('click', enviarAceptacion);
}

function enviarAceptacion() {
    const btn = document.getElementById('btnAceptarTyC');
    const originalText = btn.textContent;
    
    btn.disabled = true;
    btn.textContent = 'Guardando...';
    btn.style.opacity = '0.8';

    fetch('../php/aceptar_tyc.php', { method: 'POST' })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                // Animación de salida
                const modal = document.getElementById('modalTyC');
                modal.style.transition = 'opacity 0.5s';
                modal.style.opacity = '0';
                
                setTimeout(() => {
                    modal.remove();
                    document.body.style.overflow = 'auto'; // Restaurar scroll
                }, 500);
            } else {
                alert('Error: ' + data.message);
                btn.disabled = false;
                btn.textContent = originalText;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error de conexión. Por favor verifica tu internet.');
            btn.disabled = false;
            btn.textContent = originalText;
        });
}