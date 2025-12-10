# Script de verificación del sistema Docker
# Sistema de Gestión - Congreso de Mercadotecnia

Write-Host "`n========================================" -ForegroundColor Cyan
Write-Host "  VERIFICACIÓN DEL SISTEMA DOCKER" -ForegroundColor Green
Write-Host "========================================`n" -ForegroundColor Cyan

# Verificar Docker
Write-Host "🐳 Verificando Docker..." -ForegroundColor Yellow
try {
    $dockerVersion = docker --version
    Write-Host "✅ $dockerVersion" -ForegroundColor Green
} catch {
    Write-Host "❌ Docker no está instalado o no está en el PATH" -ForegroundColor Red
    exit 1
}

# Verificar contenedores
Write-Host "`n📦 Verificando contenedores..." -ForegroundColor Yellow
$containers = docker ps --filter "name=congreso" --format "{{.Names}}"
$expectedContainers = @("congreso_oracle_db", "congreso_web_oracle", "congreso_whatsapp")

foreach ($expected in $expectedContainers) {
    if ($containers -contains $expected) {
        $status = docker inspect -f '{{.State.Status}}' $expected
        Write-Host "✅ $expected : $status" -ForegroundColor Green
    } else {
        Write-Host "❌ $expected : No encontrado" -ForegroundColor Red
    }
}

# Verificar salud de Oracle
Write-Host "`n🗄️ Verificando salud de Oracle DB..." -ForegroundColor Yellow
$oracleHealth = docker inspect -f '{{.State.Health.Status}}' congreso_oracle_db 2>$null
if ($oracleHealth -eq "healthy") {
    Write-Host "✅ Oracle DB está saludable" -ForegroundColor Green
} else {
    Write-Host "⚠️ Oracle DB: $oracleHealth" -ForegroundColor Yellow
}

# URLs de acceso
Write-Host "`n📍 URLs de acceso:" -ForegroundColor Cyan
Write-Host "   🌐 Aplicación Web:     http://localhost:8081" -ForegroundColor White
Write-Host "   📊 Oracle EM Express:  https://localhost:5500/em" -ForegroundColor White
Write-Host "   💬 WhatsApp Service:   http://localhost:3001" -ForegroundColor White

# Credenciales
Write-Host "`n🔑 Credenciales Oracle:" -ForegroundColor Cyan
Write-Host "   Usuario:    sys" -ForegroundColor White
Write-Host "   Password:   OraclePass123!" -ForegroundColor White
Write-Host "   Service:    FREEPDB1" -ForegroundColor White
Write-Host "   Puerto:     1521" -ForegroundColor White

# Comandos útiles
Write-Host "`n💡 Comandos útiles:" -ForegroundColor Cyan
Write-Host "   Ver logs web:       docker logs congreso_web_oracle -f" -ForegroundColor White
Write-Host "   Ver logs Oracle:    docker logs congreso_oracle_db -f" -ForegroundColor White
Write-Host "   Ver logs WhatsApp:  docker logs congreso_whatsapp -f" -ForegroundColor White
Write-Host "   Detener sistema:    docker compose down" -ForegroundColor White
Write-Host "   Reiniciar sistema:  docker compose restart" -ForegroundColor White

Write-Host "`n========================================`n" -ForegroundColor Cyan
