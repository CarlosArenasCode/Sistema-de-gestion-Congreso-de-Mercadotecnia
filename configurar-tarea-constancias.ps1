# configurar-tarea-constancias.ps1
# Script para configurar Task Scheduler de Windows para generar constancias automáticamente

Write-Host "=== Configurador de Tarea Automática de Constancias ===" -ForegroundColor Cyan
Write-Host ""

# Rutas
$scriptPath = Join-Path $PSScriptRoot "Proyecto_conectado\php\generar_constancias_automaticas.php"
$phpPath = "C:\xampp\php\php.exe"  # Ajustar si XAMPP está en otra ubicación

# Verificar que existen los archivos necesarios
if (-not (Test-Path $scriptPath)) {
    Write-Host "ERROR: No se encuentra el script PHP en: $scriptPath" -ForegroundColor Red
    exit 1
}

if (-not (Test-Path $phpPath)) {
    Write-Host "ADVERTENCIA: PHP no encontrado en $phpPath" -ForegroundColor Yellow
    Write-Host "Por favor, ingresa la ruta completa a php.exe:" -ForegroundColor Yellow
    $phpPath = Read-Host
    
    if (-not (Test-Path $phpPath)) {
        Write-Host "ERROR: Ruta de PHP no válida" -ForegroundColor Red
        exit 1
    }
}

Write-Host "✓ Script PHP encontrado: $scriptPath" -ForegroundColor Green
Write-Host "✓ PHP encontrado: $phpPath" -ForegroundColor Green
Write-Host ""

# Preguntar configuración
Write-Host "Configuración de la tarea automática:" -ForegroundColor Cyan
Write-Host "1. Cada 15 minutos (recomendado para producción)" -ForegroundColor White
Write-Host "2. Cada 30 minutos" -ForegroundColor White
Write-Host "3. Cada hora" -ForegroundColor White
Write-Host "4. Manual (ejecutar solo cuando se solicite)" -ForegroundColor White
Write-Host ""
$opcion = Read-Host "Selecciona una opción (1-4)"

$taskName = "Generar_Constancias_Automaticas"
$taskDescription = "Genera constancias automáticamente para eventos finalizados del Congreso de Mercadotecnia"

# Eliminar tarea existente si existe
$existingTask = Get-ScheduledTask -TaskName $taskName -ErrorAction SilentlyContinue
if ($existingTask) {
    Write-Host "Eliminando tarea existente..." -ForegroundColor Yellow
    Unregister-ScheduledTask -TaskName $taskName -Confirm:$false
}

# Crear acción
$action = New-ScheduledTaskAction -Execute $phpPath -Argument "`"$scriptPath`"" -WorkingDirectory (Split-Path $scriptPath)

# Crear trigger según opción
switch ($opcion) {
    "1" {
        # Cada 15 minutos
        $trigger = New-ScheduledTaskTrigger -Once -At (Get-Date) -RepetitionInterval (New-TimeSpan -Minutes 15) -RepetitionDuration ([TimeSpan]::MaxValue)
        Write-Host "Configurando ejecución cada 15 minutos..." -ForegroundColor Green
    }
    "2" {
        # Cada 30 minutos
        $trigger = New-ScheduledTaskTrigger -Once -At (Get-Date) -RepetitionInterval (New-TimeSpan -Minutes 30) -RepetitionDuration ([TimeSpan]::MaxValue)
        Write-Host "Configurando ejecución cada 30 minutos..." -ForegroundColor Green
    }
    "3" {
        # Cada hora
        $trigger = New-ScheduledTaskTrigger -Once -At (Get-Date) -RepetitionInterval (New-TimeSpan -Hours 1) -RepetitionDuration ([TimeSpan]::MaxValue)
        Write-Host "Configurando ejecución cada hora..." -ForegroundColor Green
    }
    "4" {
        # Manual (sin trigger automático)
        $trigger = $null
        Write-Host "Tarea configurada para ejecución manual solamente..." -ForegroundColor Green
    }
    default {
        Write-Host "Opción no válida. Usando configuración predeterminada: cada 30 minutos" -ForegroundColor Yellow
        $trigger = New-ScheduledTaskTrigger -Once -At (Get-Date) -RepetitionInterval (New-TimeSpan -Minutes 30) -RepetitionDuration ([TimeSpan]::MaxValue)
    }
}

# Configurar para ejecutar con permisos del usuario actual
$principal = New-ScheduledTaskPrincipal -UserId $env:USERNAME -LogonType Interactive -RunLevel Highest

# Configuración adicional
$settings = New-ScheduledTaskSettingsSet -AllowStartIfOnBatteries -DontStopIfGoingOnBatteries -StartWhenAvailable -RunOnlyIfNetworkAvailable

try {
    # Registrar la tarea
    if ($trigger) {
        Register-ScheduledTask -TaskName $taskName -Action $action -Trigger $trigger -Principal $principal -Settings $settings -Description $taskDescription -Force | Out-Null
    } else {
        Register-ScheduledTask -TaskName $taskName -Action $action -Principal $principal -Settings $settings -Description $taskDescription -Force | Out-Null
    }
    
    Write-Host ""
    Write-Host "✓ Tarea creada exitosamente: $taskName" -ForegroundColor Green
    Write-Host ""
    Write-Host "Detalles de la tarea:" -ForegroundColor Cyan
    Write-Host "  Nombre: $taskName" -ForegroundColor White
    Write-Host "  Comando: $phpPath `"$scriptPath`"" -ForegroundColor White
    
    if ($trigger) {
        Write-Host "  Frecuencia: Automática según configuración" -ForegroundColor White
    } else {
        Write-Host "  Frecuencia: Manual" -ForegroundColor White
    }
    
    Write-Host ""
    Write-Host "Comandos útiles:" -ForegroundColor Cyan
    Write-Host "  Ver tarea:        Get-ScheduledTask -TaskName '$taskName' | fl" -ForegroundColor Yellow
    Write-Host "  Ejecutar ahora:   Start-ScheduledTask -TaskName '$taskName'" -ForegroundColor Yellow
    Write-Host "  Deshabilitar:     Disable-ScheduledTask -TaskName '$taskName'" -ForegroundColor Yellow
    Write-Host "  Habilitar:        Enable-ScheduledTask -TaskName '$taskName'" -ForegroundColor Yellow
    Write-Host "  Eliminar:         Unregister-ScheduledTask -TaskName '$taskName' -Confirm:`$false" -ForegroundColor Yellow
    Write-Host ""
    
    # Preguntar si ejecutar ahora
    $ejecutar = Read-Host "¿Deseas ejecutar la tarea ahora para probar? (S/N)"
    if ($ejecutar -eq 'S' -or $ejecutar -eq 's') {
        Write-Host ""
        Write-Host "Ejecutando tarea..." -ForegroundColor Cyan
        Start-ScheduledTask -TaskName $taskName
        Start-Sleep -Seconds 3
        
        # Verificar logs
        $logDir = Join-Path $PSScriptRoot "Proyecto_conectado\logs"
        $logFile = Join-Path $logDir ("constancias_auto_" + (Get-Date -Format "yyyy-MM-dd") + ".log")
        
        if (Test-Path $logFile) {
            Write-Host ""
            Write-Host "=== LOG DE EJECUCIÓN ===" -ForegroundColor Cyan
            Get-Content $logFile -Tail 30
            Write-Host ""
            Write-Host "Log completo en: $logFile" -ForegroundColor Green
        } else {
            Write-Host ""
            Write-Host "Log no encontrado aún. Verifica en: $logDir" -ForegroundColor Yellow
        }
    }
    
} catch {
    Write-Host ""
    Write-Host "ERROR al crear la tarea: $_" -ForegroundColor Red
    Write-Host ""
    Write-Host "Asegúrate de ejecutar este script con permisos de administrador" -ForegroundColor Yellow
    exit 1
}

Write-Host ""
Write-Host "=== Configuración completada ===" -ForegroundColor Green
