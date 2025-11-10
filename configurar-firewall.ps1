# Script para configurar Firewall de Windows para acceso externo a Docker
# EJECUTAR COMO ADMINISTRADOR

Write-Host "==========================================" -ForegroundColor Cyan
Write-Host "  Configuración de Firewall para Docker  " -ForegroundColor Cyan
Write-Host "==========================================" -ForegroundColor Cyan
Write-Host ""

# Verificar si se ejecuta como administrador
$currentPrincipal = New-Object Security.Principal.WindowsPrincipal([Security.Principal.WindowsIdentity]::GetCurrent())
$isAdmin = $currentPrincipal.IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)

if (-not $isAdmin) {
    Write-Host "❌ ERROR: Este script debe ejecutarse como Administrador" -ForegroundColor Red
    Write-Host ""
    Write-Host "Para ejecutar como administrador:" -ForegroundColor Yellow
    Write-Host "1. Clic derecho en el botón de Windows" -ForegroundColor Yellow
    Write-Host "2. Seleccionar 'Windows PowerShell (Admin)' o 'Terminal (Admin)'" -ForegroundColor Yellow
    Write-Host "3. Navegar a esta carpeta y ejecutar el script nuevamente" -ForegroundColor Yellow
    Write-Host ""
    Read-Host "Presiona Enter para salir"
    exit 1
}

Write-Host "✅ Ejecutándose como Administrador" -ForegroundColor Green
Write-Host ""

# Función para agregar regla de firewall
function Add-FirewallRuleIfNotExists {
    param(
        [string]$RuleName,
        [int]$Port,
        [string]$Description
    )
    
    Write-Host "Configurando regla: $RuleName (Puerto $Port)..." -ForegroundColor Yellow
    
    # Verificar si la regla ya existe
    $existingRule = Get-NetFirewallRule -DisplayName $RuleName -ErrorAction SilentlyContinue
    
    if ($existingRule) {
        Write-Host "  ℹ️  La regla ya existe, eliminando para recrear..." -ForegroundColor Cyan
        Remove-NetFirewallRule -DisplayName $RuleName -ErrorAction SilentlyContinue
    }
    
    # Crear la regla usando netsh (más compatible)
    $result = netsh advfirewall firewall add rule name="$RuleName" dir=in action=allow protocol=TCP localport=$Port
    
    if ($LASTEXITCODE -eq 0) {
        Write-Host "  ✅ Regla '$RuleName' creada exitosamente" -ForegroundColor Green
    } else {
        Write-Host "  ❌ Error al crear regla '$RuleName'" -ForegroundColor Red
    }
    Write-Host ""
}

# Obtener IP de la computadora
Write-Host "📍 Obteniendo dirección IP..." -ForegroundColor Cyan
$ipAddresses = Get-NetIPAddress -AddressFamily IPv4 | Where-Object {$_.IPAddress -notlike "127.*" -and $_.IPAddress -notlike "169.*"}

Write-Host "Direcciones IP detectadas:" -ForegroundColor White
foreach ($ip in $ipAddresses) {
    Write-Host "  - $($ip.IPAddress)" -ForegroundColor Green
}
Write-Host ""

# Configurar reglas de firewall
Write-Host "🔧 Configurando reglas de Firewall..." -ForegroundColor Cyan
Write-Host ""

Add-FirewallRuleIfNotExists -RuleName "Docker Web Puerto 8081" -Port 8081 -Description "Permite acceso a la aplicación web del Congreso de Mercadotecnia"
Add-FirewallRuleIfNotExists -RuleName "Docker WhatsApp Puerto 3001" -Port 3001 -Description "Permite acceso al servicio WhatsApp del Congreso"
Add-FirewallRuleIfNotExists -RuleName "Docker Oracle Puerto 1521" -Port 1521 -Description "Permite acceso a Oracle Database (opcional)"

# Verificar reglas creadas
Write-Host "🔍 Verificando reglas creadas..." -ForegroundColor Cyan
Write-Host ""

$rules = @("Docker Web Puerto 8081", "Docker WhatsApp Puerto 3001", "Docker Oracle Puerto 1521")

foreach ($ruleName in $rules) {
    $ruleCheck = netsh advfirewall firewall show rule name="$ruleName" 2>&1
    if ($LASTEXITCODE -eq 0) {
        Write-Host "  ✅ $ruleName - ACTIVA" -ForegroundColor Green
    } else {
        Write-Host "  ❌ $ruleName - NO ENCONTRADA" -ForegroundColor Red
    }
}

Write-Host ""
Write-Host "==========================================" -ForegroundColor Cyan
Write-Host "  ✅ Configuración Completada" -ForegroundColor Green
Write-Host "==========================================" -ForegroundColor Cyan
Write-Host ""

# Mostrar información de acceso
$mainIP = ($ipAddresses | Select-Object -First 1).IPAddress

Write-Host "📋 URLs para compartir con tu compañero:" -ForegroundColor Cyan
Write-Host ""
Write-Host "  Aplicación Web:" -ForegroundColor Yellow
Write-Host "    http://$mainIP:8081" -ForegroundColor White
Write-Host ""
Write-Host "  Registro:" -ForegroundColor Yellow
Write-Host "    http://$mainIP:8081/Front-end/registro_usuario.html" -ForegroundColor White
Write-Host ""
Write-Host "  Login:" -ForegroundColor Yellow
Write-Host "    http://$mainIP:8081/Front-end/login.html" -ForegroundColor White
Write-Host ""
Write-Host "  WhatsApp QR:" -ForegroundColor Yellow
Write-Host "    http://$mainIP:3001" -ForegroundColor White
Write-Host ""

Write-Host "⚠️  IMPORTANTE:" -ForegroundColor Yellow
Write-Host "  1. Ambas computadoras deben estar en la MISMA RED (mismo WiFi)" -ForegroundColor Yellow
Write-Host "  2. Docker debe estar corriendo: docker-compose ps" -ForegroundColor Yellow
Write-Host "  3. Si la IP cambia, ejecuta este script nuevamente" -ForegroundColor Yellow
Write-Host "  4. Puerto 8081 (no 8080) - cambio por conflicto con Oracle local" -ForegroundColor Yellow
Write-Host ""

Read-Host "Presiona Enter para salir"
