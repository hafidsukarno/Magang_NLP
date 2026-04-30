Write-Host "🚀 Starting all services..." -ForegroundColor Green
Write-Host ""

# Start OCR Service in background
Write-Host "Starting OCR Service (port 5000)..." -ForegroundColor Yellow
Start-Process powershell -ArgumentList "-NoExit", "-Command", "cd '$pwd\ektrasi data identitas'; python main.py"

# Wait a bit for OCR to start
Start-Sleep -Seconds 3

# Start Laravel Server in background
Write-Host "Starting Laravel Server (port 8000)..." -ForegroundColor Yellow
Start-Process powershell -ArgumentList "-NoExit", "-Command", "cd '$pwd'; php artisan serve"

# Start Queue Worker in background
Write-Host "Starting Queue Worker..." -ForegroundColor Yellow
Start-Process powershell -ArgumentList "-NoExit", "-Command", "cd '$pwd'; php artisan queue:work"

Write-Host ""
Write-Host "✅ All services started!" -ForegroundColor Green
Write-Host ""
Write-Host "OCR Service:  http://127.0.0.1:8500" -ForegroundColor Cyan
Write-Host "Laravel:      http://127.0.0.1:8000" -ForegroundColor Cyan
Write-Host ""
Write-Host "Check individual windows for logs" -ForegroundColor Yellow
