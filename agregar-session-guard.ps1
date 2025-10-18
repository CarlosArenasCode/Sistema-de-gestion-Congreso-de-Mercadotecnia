# Script para agregar session-guard.js a todas las páginas protegidas
# Este script modifica los archivos HTML para incluir el sistema de protección de sesión

Write-Host "================================" -ForegroundColor Cyan
Write-Host "Sistema de Protección de Sesión" -ForegroundColor Cyan
Write-Host "Agregando session-guard.js a páginas protegidas" -ForegroundColor Cyan
Write-Host "================================" -ForegroundColor Cyan
Write-Host ""

# Rutas base
$frontendPath = "Proyecto_conectado\Front-end"

# Páginas que deben ser protegidas
$paginasProtegidas = @(
    # Páginas de estudiantes
    "horario.html",
    "mi_qr.html",
    "mis_constancias.html",
    "justificar_falta.html",
    
    # Páginas de administrador
    "admin_dashboard.html",
    "admin_asistencia.html",
    "admin_constancias.html",
    "admin_eventos.html",
    "admin_inscripciones.html",
    "admin_justificacion.html",
    "admin_scan_qr.html",
    "admin_usuarios.html"
)

# Código a insertar antes del cierre de </body>
$sessionGuardCode = @'

    <!-- Script de protección de sesión (DEBE IR PRIMERO) -->
    <script src="../js/session-guard.js"></script>
</body>
'@

$archivosModificados = 0
$archivosYaProtegidos = 0
$archivosNoEncontrados = 0

foreach ($pagina in $paginasProtegidas) {
    $rutaCompleta = Join-Path $frontendPath $pagina
    
    if (Test-Path $rutaCompleta) {
        Write-Host "Procesando: $pagina" -ForegroundColor Yellow
        
        # Leer el contenido del archivo
        $contenido = Get-Content $rutaCompleta -Raw -Encoding UTF8
        
        # Verificar si ya tiene session-guard.js
        if ($contenido -match 'session-guard\.js') {
            Write-Host "  ✓ Ya protegido" -ForegroundColor Green
            $archivosYaProtegidos++
        }
        else {
            # Agregar session-guard antes del cierre de </body>
            if ($contenido -match '</body>') {
                $contenido = $contenido -replace '</body>', $sessionGuardCode
                
                # Guardar el archivo modificado
                $contenido | Set-Content $rutaCompleta -Encoding UTF8 -NoNewline
                
                Write-Host "  ✓ Protección agregada" -ForegroundColor Green
                $archivosModificados++
            }
            else {
                Write-Host "  ✗ No se encontró etiqueta </body>" -ForegroundColor Red
            }
        }
    }
    else {
        Write-Host "  ✗ Archivo no encontrado: $rutaCompleta" -ForegroundColor Red
        $archivosNoEncontrados++
    }
    
    Write-Host ""
}

# Resumen
Write-Host "================================" -ForegroundColor Cyan
Write-Host "Resumen de la operación:" -ForegroundColor Cyan
Write-Host "================================" -ForegroundColor Cyan
Write-Host "Archivos modificados: $archivosModificados" -ForegroundColor Green
Write-Host "Archivos ya protegidos: $archivosYaProtegidos" -ForegroundColor Yellow
Write-Host "Archivos no encontrados: $archivosNoEncontrados" -ForegroundColor Red
Write-Host ""
Write-Host "✓ Proceso completado" -ForegroundColor Green
Write-Host ""
Write-Host "Siguiente paso:" -ForegroundColor Cyan
Write-Host "1. Verifica los cambios con: git diff" -ForegroundColor White
Write-Host "2. Prueba el sistema accediendo a las páginas protegidas" -ForegroundColor White
Write-Host "3. Copia los archivos al contenedor Docker si es necesario" -ForegroundColor White
