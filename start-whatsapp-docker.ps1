# Script de Inicio del Sistema con WhatsApp
# Autor: GJA Team
# Fecha: Octubre 2025

Write-Host "===========================================" -ForegroundColor Cyan
Write-Host "  Sistema de Gestión - Congreso UAA" -ForegroundColor Cyan
Write-Host "  Iniciando con Servicio WhatsApp" -ForegroundColor Cyan
Write-Host "===========================================" -ForegroundColor Cyan
Write-Host ""

# Verificar que Docker Desktop está corriendo
Write-Host "🔍 Verificando Docker Desktop..." -ForegroundColor Yellow
$dockerProcess = Get-Process "Docker Desktop" -ErrorAction SilentlyContinue

if ($null -eq $dockerProcess) {
    Write-Host "❌ Docker Desktop no está corriendo." -ForegroundColor Red
    Write-Host "   Por favor, inicia Docker Desktop y vuelve a ejecutar este script." -ForegroundColor Red
    pause
    exit 1
}

Write-Host "✅ Docker Desktop está corriendo" -ForegroundColor Green
Write-Host ""

# Iniciar servicios con docker-compose
Write-Host "🚀 Iniciando servicios..." -ForegroundColor Yellow
docker-compose up -d

if ($LASTEXITCODE -ne 0) {
    Write-Host "❌ Error al iniciar los servicios." -ForegroundColor Red
    pause
    exit 1
}

Write-Host ""
Write-Host "⏳ Esperando a que los servicios estén listos..." -ForegroundColor Yellow
Start-Sleep -Seconds 5

# Verificar estado de los contenedores
Write-Host ""
Write-Host "📊 Estado de los contenedores:" -ForegroundColor Cyan
docker ps --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}"

Write-Host ""
Write-Host "===========================================" -ForegroundColor Green
Write-Host "  ✅ Sistema Iniciado Correctamente" -ForegroundColor Green
Write-Host "===========================================" -ForegroundColor Green
Write-Host ""

# URLs del sistema
Write-Host "🌐 URLs del Sistema:" -ForegroundColor Cyan
Write-Host "   📱 Aplicación Web:      http://localhost:8080" -ForegroundColor White
Write-Host "   💾 phpMyAdmin:          http://localhost:8081" -ForegroundColor White
Write-Host "   📲 API WhatsApp:        http://localhost:3001" -ForegroundColor White
Write-Host "   ✅ Health Check:        http://localhost:3001/health" -ForegroundColor White
Write-Host "   🧪 Panel de Pruebas:    http://localhost:8080/Proyecto_conectado/php/test_whatsapp_docker.php" -ForegroundColor White
Write-Host ""

# Verificar estado del servicio WhatsApp
Write-Host "📱 Verificando servicio WhatsApp..." -ForegroundColor Yellow
Start-Sleep -Seconds 3

$whatsappHealth = docker exec congreso_web curl -s http://whatsapp:3001/health 2>$null

if ($null -ne $whatsappHealth) {
    try {
        $healthData = $whatsappHealth | ConvertFrom-Json
        
        if ($healthData.status -eq "ready") {
            Write-Host "   ✅ Servicio WhatsApp: LISTO" -ForegroundColor Green
            Write-Host "   📱 Número configurado: +$($healthData.phoneNumber)" -ForegroundColor Green
        } elseif ($healthData.status -eq "initializing") {
            Write-Host "   ⏳ Servicio WhatsApp: INICIANDO..." -ForegroundColor Yellow
            Write-Host "   ℹ️  El servicio tardará unos segundos en estar listo." -ForegroundColor Yellow
        } else {
            Write-Host "   ⚠️  Servicio WhatsApp: $($healthData.status)" -ForegroundColor Yellow
        }
    } catch {
        Write-Host "   ⏳ Servicio WhatsApp iniciando..." -ForegroundColor Yellow
    }
} else {
    Write-Host "   ⏳ Servicio WhatsApp iniciando..." -ForegroundColor Yellow
}

Write-Host ""

# Verificar si es la primera vez (necesita escanear QR)
Write-Host "===========================================" -ForegroundColor Cyan
Write-Host "  ⚠️  PRIMERA VEZ - IMPORTANTE" -ForegroundColor Yellow
Write-Host "===========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Si es la PRIMERA VEZ que ejecutas el servicio WhatsApp," -ForegroundColor Yellow
Write-Host "necesitas vincular tu cuenta de WhatsApp:" -ForegroundColor Yellow
Write-Host ""
Write-Host "1. Ejecuta: " -NoNewline -ForegroundColor White
Write-Host "docker logs -f congreso_whatsapp" -ForegroundColor Cyan
Write-Host "2. Busca el código QR en la consola (ASCII art)" -ForegroundColor White
Write-Host "3. Abre WhatsApp → Dispositivos Vinculados → Vincular dispositivo" -ForegroundColor White
Write-Host "4. Escanea el código QR" -ForegroundColor White
Write-Host "5. Una vez vinculado, verás: '✅ Bot de WhatsApp iniciado correctamente'" -ForegroundColor White
Write-Host ""
Write-Host "Nota: Solo necesitas hacer esto UNA VEZ. La sesión se guarda." -ForegroundColor Gray
Write-Host ""

# Preguntar si quiere ver los logs de WhatsApp
Write-Host "¿Deseas ver los logs del servicio WhatsApp ahora? (S/N): " -NoNewline -ForegroundColor Cyan
$response = Read-Host

if ($response -eq "S" -or $response -eq "s" -or $response -eq "Y" -or $response -eq "y") {
    Write-Host ""
    Write-Host "📋 Logs del servicio WhatsApp (Presiona Ctrl+C para salir):" -ForegroundColor Cyan
    Write-Host ""
    docker logs -f congreso_whatsapp
} else {
    Write-Host ""
    Write-Host "===========================================" -ForegroundColor Green
    Write-Host "  Comandos Útiles:" -ForegroundColor White
    Write-Host "===========================================" -ForegroundColor Green
    Write-Host ""
    Write-Host "  Ver logs WhatsApp:" -ForegroundColor White
    Write-Host "    docker logs -f congreso_whatsapp" -ForegroundColor Cyan
    Write-Host ""
    Write-Host "  Ver todos los logs:" -ForegroundColor White
    Write-Host "    docker-compose logs -f" -ForegroundColor Cyan
    Write-Host ""
    Write-Host "  Reiniciar servicio WhatsApp:" -ForegroundColor White
    Write-Host "    docker-compose restart whatsapp" -ForegroundColor Cyan
    Write-Host ""
    Write-Host "  Detener todo:" -ForegroundColor White
    Write-Host "    docker-compose down" -ForegroundColor Cyan
    Write-Host ""
    Write-Host "  Estado de servicios:" -ForegroundColor White
    Write-Host "    docker ps" -ForegroundColor Cyan
    Write-Host ""
    Write-Host "===========================================" -ForegroundColor Green
    Write-Host ""
    Write-Host "🎉 ¡Sistema listo para usar!" -ForegroundColor Green
    Write-Host ""
    Write-Host "📚 Documentación:" -ForegroundColor Cyan
    Write-Host "   - INSTRUCCIONES_WHATSAPP_DOCKER.md" -ForegroundColor White
    Write-Host "   - GUIA_RAPIDA_WHATSAPP_DOCKER.md" -ForegroundColor White
    Write-Host "   - whatsapp-service/README.md" -ForegroundColor White
    Write-Host ""
}
