// js_admin/admin_scan.js
document.addEventListener('DOMContentLoaded', () => {
    // ... (variables existentes) ...
    const eventoSelector = document.getElementById('evento-selector');
    const cameraFeedDiv = document.getElementById('camera-feed');
    const startScanButton = document.getElementById('start-scan-button');
    const stopScanButton = document.getElementById('stop-scan-button');
    const manualCodeInput = document.getElementById('manual-code');
    const submitManualCodeButton = document.getElementById('submit-manual-code');

    const resultMessage = document.getElementById('result-message');
    const resultStudentName = document.getElementById('result-student-name');
    const resultStudentId = document.getElementById('result-student-id');
    const resultEventName = document.getElementById('result-event-name');
    const resultEventId = document.getElementById('result-event-id'); 
    const resultInscripcionStatus = document.getElementById('result-inscripcion-status');
    const resultLastAction = document.getElementById('result-last-action');
    const resultServerResponse = document.getElementById('result-server-response');

    const btnRegistrarEntrada = document.getElementById('btn-registrar-entrada');
    const btnRegistrarSalida = document.getElementById('btn-registrar-salida');

    let html5QrCode = null;
    let currentScannedData = null; 
    let lastValidatedUserId = null;
    let lastValidatedEventId = null;

    // Para ayudar a depurar y evitar procesamiento múltiple del mismo QR escaneado rápidamente
    let lastProcessedQrTextFromScan = null;
    let lastProcessedQrTimestamp = 0;


    function resetResultDisplay(message = "Esperando acción...") {
        resultMessage.textContent = message;
        resultStudentName.textContent = '-';
        resultStudentId.textContent = '-';
        const selectedEventOption = eventoSelector.options[eventoSelector.selectedIndex];
        if (selectedEventOption && selectedEventOption.value) {
            resultEventName.textContent = selectedEventOption.text.split(' (')[0];
            if (resultEventId) resultEventId.textContent = selectedEventOption.value;
        } else {
            resultEventName.textContent = '-';
            if (resultEventId) resultEventId.textContent = '-';
        }
        resultInscripcionStatus.textContent = '-';
        resultLastAction.textContent = '-';
        resultServerResponse.textContent = '-';
        
        btnRegistrarEntrada.disabled = true;
        btnRegistrarSalida.disabled = true;
        btnRegistrarEntrada.classList.add('button-disabled');
        btnRegistrarSalida.classList.add('button-disabled');
    }

    function updateResultDisplay(data, esValidacion = false) {
        if (esValidacion) { 
            if (data.success) {
                resultMessage.textContent = data.mensaje_estado_general || 'Datos validados.';
                resultStudentName.textContent = data.nombre_usuario || 'N/A';
                resultStudentId.textContent = data.id_usuario || 'N/A';
                resultEventName.textContent = data.nombre_evento || 'N/A'; 
                if (resultEventId) resultEventId.textContent = data.id_evento || 'N/A';   
                resultInscripcionStatus.textContent = data.mensaje_inscripcion || 'N/A';
                resultLastAction.textContent = data.ultimo_estado_asistencia ? (data.ultimo_estado_asistencia.replace('_', ' ')) : 'Ninguna';
                
                btnRegistrarEntrada.disabled = !data.puede_registrar_entrada;
                btnRegistrarSalida.disabled = !data.puede_registrar_salida;
                
                btnRegistrarEntrada.classList.toggle('button-disabled', !data.puede_registrar_entrada);
                btnRegistrarSalida.classList.toggle('button-disabled', !data.puede_registrar_salida);

                lastValidatedUserId = data.id_usuario;
                lastValidatedEventId = data.id_evento; 
            } else {
                resetResultDisplay(data.error || data.mensaje_estado_general || 'Error en la validación.');
                resultServerResponse.textContent = data.error || data.mensaje_estado_general || 'Error desconocido.';
                currentScannedData = null; 
                lastValidatedUserId = null;
            }
        } else { 
            resultServerResponse.textContent = data.message || data.error || 'Acción procesada.';
            if (data.success) {
                resultMessage.textContent = "¡Éxito en el registro!";
                if (currentScannedData && lastValidatedEventId) { // currentScannedData debería ser el QR que se usó
                    validarCodigo(currentScannedData, lastValidatedEventId);
                } else if (manualCodeInput.value.trim() && lastValidatedEventId) { // Fallback al código manual si es el que se usó
                     validarCodigo(manualCodeInput.value.trim(), lastValidatedEventId);
                }
                else {
                    resetResultDisplay('Registro exitoso. Escanee o ingrese nuevo código.');
                }
            } else {
                resultMessage.textContent = "Error en Registro";
            }
        }
    }

    async function cargarEventos() {
        try {
            const response = await fetch('../php_admin/asistencia_controller.php?action=get_eventos_activos');
            if (!response.ok) throw new Error(`Error HTTP: ${response.status}`);
            const data = await response.json();
            if (data.success && data.eventos) {
                eventoSelector.innerHTML = '<option value="">-- Seleccione un Evento --</option>';
                data.eventos.forEach(evento => {
                    const option = document.createElement('option');
                    option.value = evento.id_evento;
                    const fechaFormateada = evento.fecha_inicio ? new Date(evento.fecha_inicio + 'T00:00:00').toLocaleDateString('es-ES') : 'Fecha no disp.';
                    option.textContent = `${evento.nombre_evento} (${fechaFormateada})`;
                    eventoSelector.appendChild(option);
                });
            } else {
                eventoSelector.innerHTML = '<option value="">-- Error al cargar eventos --</option>';
                alert(data.error || 'No se pudieron cargar los eventos.');
            }
        } catch (error) {
            console.error('Error fetching eventos:', error);
            eventoSelector.innerHTML = '<option value="">-- Error de conexión --</option>';
            alert('Error de conexión al cargar eventos: ' + error.message);
        }
    }

    // ---------- MODIFICACIONES PARA DEPURAR QR SCANNER ----------
    const qrCodeSuccessCallback = async (decodedText, decodedResult) => {
        const now = Date.now();
        console.log("--------------------------------------------------");
        console.log("[QR SCAN] Success Callback Disparado!");
        console.log("[QR SCAN] Decoded Text:", decodedText);
        console.log("[QR SCAN] Decoded Result Object:", JSON.stringify(decodedResult, null, 2));
        console.log("[QR SCAN] Timestamp actual:", now);
        console.log("[QR SCAN] Texto del último QR procesado:", lastProcessedQrTextFromScan);
        console.log("[QR SCAN] Timestamp del último QR procesado:", lastProcessedQrTimestamp);

        // Protección simple contra múltiples llamadas muy rápidas con el mismo QR.
        // Si el mismo texto fue procesado en los últimos 2 segundos, ignorar.
        if (decodedText === lastProcessedQrTextFromScan && (now - lastProcessedQrTimestamp < 2000)) {
            console.warn("[QR SCAN] Mismo QR detectado recientemente, ignorando esta lectura:", decodedText);
            console.log("--------------------------------------------------");
            return;
        }

        lastProcessedQrTextFromScan = decodedText;
        lastProcessedQrTimestamp = now;

        if (!decodedText) {
            console.error("[QR SCAN] decodedText está vacío o es nulo.");
            resultMessage.textContent = "Error en lectura de QR: No se obtuvo texto del código.";
             console.log("--------------------------------------------------");
            return;
        }

        // Colocar el texto decodificado en el input manual para visualización y posible uso
        manualCodeInput.value = decodedText;
        currentScannedData = decodedText; // Guardar el dato escaneado para revalidación posterior

        resultMessage.textContent = `Código QR Detectado: ${decodedText}. Validando...`;
        const selectedEventId = eventoSelector.value;

        if (!selectedEventId) {
            alert('Por favor, seleccione un evento primero.');
            resetResultDisplay('Seleccione un evento.');
            console.log("[QR SCAN] No hay evento seleccionado.");
            console.log("--------------------------------------------------");
            return;
        }
        
        console.log(`[QR SCAN] Llamando a validarCodigo con QR: "${decodedText}" y Evento ID: ${selectedEventId}`);
        console.log("--------------------------------------------------");
        await validarCodigo(decodedText, selectedEventId);
    };
    // ---------- FIN DE MODIFICACIONES PARA DEPURAR QR SCANNER ----------


    const config = { fps: 5, qrbox: { width: 280, height: 280 }, rememberLastUsedCamera: true, showTorchButtonIfSupported: true, showZoomSliderIfSupported: true};

    startScanButton.addEventListener('click', () => {
        if (!eventoSelector.value) {
            alert('Por favor, seleccione un evento antes de activar el escáner.');
            return;
        }
        if (!html5QrCode) {
            html5QrCode = new Html5Qrcode("camera-feed", { verbose: true }); // verbose: true para más logs de la librería
        }

        // Limpiar resultados e input antes de iniciar/reiniciar el escaneo
        manualCodeInput.value = '';
        currentScannedData = null; // Limpiar el dato escaneado previo
        resetResultDisplay("Activando escáner...");


        cameraFeedDiv.innerHTML = ''; 
        html5QrCode.start(
            { facingMode: "environment" },
            config,
            qrCodeSuccessCallback,
            (errorMessage) => { 
                // Este callback es para errores durante el escaneo (ej. no se encuentra QR)
                // No necesariamente un error fatal de la librería.
                // console.warn("QR Scanner non-fatal error message:", errorMessage); 
            }
        ).then(() => {
            startScanButton.style.display = 'none';
            stopScanButton.style.display = 'block';
            resultMessage.textContent = "Escáner Activado. Apunte al QR.";
            console.log("Escáner QR iniciado exitosamente.");
        }).catch(err => {
            console.error("Error GRAVE al iniciar el escáner QR:", err);
            alert("No se pudo iniciar el escáner QR: " + (err.name || err.message || err));
            cameraFeedDiv.innerHTML = `[Error al iniciar cámara: ${err.name || err.message}]`;
            // Asegurarse que los botones están en estado correcto si falla el inicio
            startScanButton.style.display = 'block';
            stopScanButton.style.display = 'none';
        });
    });

    stopScanButton.addEventListener('click', () => {
        if (html5QrCode && html5QrCode.isScanning) {
            html5QrCode.stop()
            .then(() => {
                console.log("Escáner QR detenido exitosamente por el usuario.");
                cameraFeedDiv.innerHTML = '[Escáner Detenido. Click en "Activar Escáner" para reanudar]';
            })
            .catch(err => {
                console.error("Error al detener el escáner QR:", err);
                cameraFeedDiv.innerHTML = '[Error al detener escáner]';
            })
            .finally(() => {
                startScanButton.style.display = 'block';
                stopScanButton.style.display = 'none';
                resetResultDisplay("Escáner detenido. Seleccione acción.");
            });
        } else {
             // Si por alguna razón se presiona stop y no estaba escaneando, asegurar estado de botones
            startScanButton.style.display = 'block';
            stopScanButton.style.display = 'none';
        }
    });

    async function validarCodigo(qrData, eventoId) {
        if (!eventoId) {
            alert('Error interno: ID de evento no disponible para validación.');
            resetResultDisplay('Error: ID de evento faltante.');
            return;
        }
        console.log(`Validando código: QR="${qrData}", EventoID="${eventoId}"`);
        resultMessage.textContent = 'Validando código...';
        btnRegistrarEntrada.disabled = true;
        btnRegistrarSalida.disabled = true;
        btnRegistrarEntrada.classList.add('button-disabled');
        btnRegistrarSalida.classList.add('button-disabled');

        const formData = new FormData();
        formData.append('qr_data', qrData);
        formData.append('id_evento', eventoId);

        try {
            const response = await fetch('../php_admin/asistencia_controller.php?action=validar_qr', {
                method: 'POST',
                body: formData
            });
            if (!response.ok) {
                let errorText = `Error HTTP: ${response.status}`;
                try { const errorData = await response.json(); errorText = errorData.error || errorData.message || errorText; } catch (e) {}
                throw new Error(errorText);
            }
            const data = await response.json();
            console.log("Respuesta de validar_qr:", data);
            updateResultDisplay(data, true); 

            if (data.success && data.id_usuario) {
                // currentScannedData ya se establece en qrCodeSuccessCallback si vino de allí,
                // o si es manual, qrData es el valor del input.
                // Considerar si es necesario reasignar currentScannedData aquí siempre.
                // Si la validación es exitosa, qrData ES el dato bueno.
                currentScannedData = qrData;
            } else {
                currentScannedData = null; 
            }
        } catch (error) {
            console.error('Error en validación fetch:', error);
            resetResultDisplay('Error de conexión al validar: ' + error.message);
            currentScannedData = null; 
        }
    }

    submitManualCodeButton.addEventListener('click', async () => {
        const qrData = manualCodeInput.value.trim();
        const selectedEventId = eventoSelector.value;
        currentScannedData = qrData; // Asumir que el código manual es el que se va a usar

        if (!selectedEventId) {
            alert('Por favor, seleccione un evento primero.');
            resetResultDisplay('Seleccione un evento.');
            return;
        }
        if (!qrData) {
            alert('Por favor, ingrese un código QR manualmente.');
            resetResultDisplay('Ingrese un código.');
            return;
        }
        await validarCodigo(qrData, selectedEventId);
    });

    eventoSelector.addEventListener('change', () => {
        resetResultDisplay("Evento cambiado. Esperando nueva acción...");
        manualCodeInput.value = ''; 
        currentScannedData = null; 
        lastValidatedUserId = null; 
        lastValidatedEventId = eventoSelector.value; // Actualizar ID de evento seleccionado

        if (html5QrCode && html5QrCode.isScanning) {
            stopScanButton.click(); 
        }
        const selectedOption = eventoSelector.options[eventoSelector.selectedIndex];
        if (selectedOption && selectedOption.value) {
             resultEventName.textContent = selectedOption.text.split(' (')[0]; 
             if(resultEventId) resultEventId.textContent = selectedOption.value;
        } else {
             resultEventName.textContent = '-';
             if(resultEventId) resultEventId.textContent = '-';
        }
    });

    async function registrarAsistencia(tipoRegistro) {
        if (!lastValidatedUserId || !lastValidatedEventId) {
            alert('Primero valide un código QR o ingrese un código manualmente que resulte en una validación exitosa.');
            return;
        }
        // Verificar explícitamente si los botones están habilitados según el estado actual
        // Esto es una doble verificación a lo que el backend determinó.
        if (tipoRegistro === 'entrada' && btnRegistrarEntrada.disabled) {
            alert('No se puede registrar entrada en el estado actual (verifique mensajes).');
            return;
        }
        if (tipoRegistro === 'salida' && btnRegistrarSalida.disabled) {
            alert('No se puede registrar salida en el estado actual (verifique mensajes).');
            return;
        }


        resultMessage.textContent = `Registrando ${tipoRegistro}...`;
        const formData = new FormData();
        formData.append('id_usuario', lastValidatedUserId);
        formData.append('id_evento', lastValidatedEventId);
        formData.append('tipo_registro', tipoRegistro);

        try {
            const response = await fetch('../php_admin/asistencia_controller.php?action=registrar_asistencia', {
                method: 'POST',
                body: formData
            });
            if (!response.ok) {
                let errorText = `Error HTTP: ${response.status}`;
                try { const errorData = await response.json(); errorText = errorData.error || errorData.message || errorText; } catch (e) {}
                throw new Error(errorText);
            }
            const data = await response.json();
            console.log("Respuesta de registrar_asistencia:", data);
            updateResultDisplay(data, false);
        } catch (error) {
            console.error(`Error registrando ${tipoRegistro}:`, error);
            updateResultDisplay({success: false, error: `Error de conexión al registrar ${tipoRegistro}: ${error.message}`}, false);
        }
    }

    btnRegistrarEntrada.addEventListener('click', () => registrarAsistencia('entrada'));
    btnRegistrarSalida.addEventListener('click', () => registrarAsistencia('salida'));

    cargarEventos().then(() => {
        resetResultDisplay(); 
    });
});