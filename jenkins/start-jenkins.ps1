# Jenkins Local Setup Script
# Run this in PowerShell

Write-Host "🚀 Starting Jenkins Local Setup..." -ForegroundColor Cyan

# Check if Docker is running
Write-Host "`nChecking Docker..." -ForegroundColor Yellow
docker ps | Out-Null
if ($LASTEXITCODE -ne 0) {
    Write-Host "❌ Docker is not running! Please start Docker Desktop first." -ForegroundColor Red
    exit 1
}
Write-Host "✅ Docker is running" -ForegroundColor Green

# Build Jenkins image
Write-Host "`n🔨 Building Jenkins custom image..." -ForegroundColor Yellow
docker build -t jenkins-custom:latest .
if ($LASTEXITCODE -ne 0) {
    Write-Host "❌ Failed to build Jenkins image!" -ForegroundColor Red
    exit 1
}
Write-Host "✅ Jenkins image built successfully" -ForegroundColor Green

# Start Jenkins
Write-Host "`n🚀 Starting Jenkins container..." -ForegroundColor Yellow
docker-compose up -d
if ($LASTEXITCODE -ne 0) {
    Write-Host "❌ Failed to start Jenkins!" -ForegroundColor Red
    exit 1
}

Write-Host "`n✅ Jenkins is starting up..." -ForegroundColor Green
Write-Host "`nWaiting for Jenkins to be ready (this may take 1-2 minutes)..." -ForegroundColor Yellow

# Wait for Jenkins to be ready
$maxAttempts = 60
$attempt = 0
do {
    Start-Sleep -Seconds 2
    $attempt++
    try {
        $response = Invoke-WebRequest -Uri "http://localhost:8080/login" -UseBasicParsing -TimeoutSec 2 -ErrorAction SilentlyContinue
        if ($response.StatusCode -eq 200) {
            break
        }
    } catch {
        # Keep waiting
    }
    Write-Host "." -NoNewline
} while ($attempt -lt $maxAttempts)

Write-Host ""

if ($attempt -ge $maxAttempts) {
    Write-Host "⚠️  Jenkins might still be starting. Check logs with: docker logs jenkins-local" -ForegroundColor Yellow
} else {
    Write-Host "`n🎉 Jenkins is ready!" -ForegroundColor Green
}

Write-Host "`n" + "="*60 -ForegroundColor Cyan
Write-Host "📍 Jenkins URL:    http://localhost:8080" -ForegroundColor White
Write-Host "📍 Blue Ocean:     http://localhost:8080/blue" -ForegroundColor White
Write-Host "🔑 Username:       admin" -ForegroundColor White
Write-Host "🔑 Password:       admin" -ForegroundColor White
Write-Host "="*60 -ForegroundColor Cyan

Write-Host "`n📝 Next steps:" -ForegroundColor Yellow
Write-Host "  1. Open http://localhost:8080" -ForegroundColor White
Write-Host "  2. Login with admin/admin" -ForegroundColor White
Write-Host "  3. Create GitHub credentials (Settings > Credentials)" -ForegroundColor White
Write-Host "  4. Create Multibranch Pipeline" -ForegroundColor White
Write-Host "  5. Enjoy! 🚀" -ForegroundColor White

Write-Host "`n💡 Useful commands:" -ForegroundColor Yellow
Write-Host "  Stop:    docker-compose down" -ForegroundColor White
Write-Host "  Logs:    docker-compose logs -f jenkins" -ForegroundColor White
Write-Host "  Restart: docker-compose restart jenkins" -ForegroundColor White
