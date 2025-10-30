# PowerShell Script to Upload BiggestLogs to Contabo Server
# This will compress and upload via SCP

Write-Host "🚀 BiggestLogs Deployment Upload Script" -ForegroundColor Cyan
Write-Host ""

$server = "root@75.119.139.18"
$remotePath = "/root/biggestlogs-deploy"
$localPath = "C:\Users\LENOVO LEGION\Documents\Biggestlogs"

Write-Host "📦 Creating archive of project files..." -ForegroundColor Yellow

# Create a temporary directory for files to archive
$tempDir = "$env:TEMP\biggestlogs-upload"
if (Test-Path $tempDir) {
    Remove-Item $tempDir -Recurse -Force
}
New-Item -ItemType Directory -Path $tempDir | Out-Null

# Copy essential files
Write-Host "Copying files..." -ForegroundColor Gray
Copy-Item -Path "$localPath\app" -Destination "$tempDir\app" -Recurse
Copy-Item -Path "$localPath\bootstrap" -Destination "$tempDir\bootstrap" -Recurse
Copy-Item -Path "$localPath\config" -Destination "$tempDir\config" -Recurse
Copy-Item -Path "$localPath\database" -Destination "$tempDir\database" -Recurse
Copy-Item -Path "$localPath\public" -Destination "$tempDir\public" -Recurse
Copy-Item -Path "$localPath\resources" -Destination "$tempDir\resources" -Recurse
Copy-Item -Path "$localPath\routes" -Destination "$tempDir\routes" -Recurse
Copy-Item -Path "$localPath\storage" -Destination "$tempDir\storage" -Recurse -Exclude "logs\*.log"
Copy-Item -Path "$localPath\.env.example" -Destination "$tempDir\.env.example" -ErrorAction SilentlyContinue
Copy-Item -Path "$localPath\composer.json" -Destination "$tempDir\composer.json"
Copy-Item -Path "$localPath\composer.lock" -Destination "$tempDir\composer.lock" -ErrorAction SilentlyContinue
Copy-Item -Path "$localPath\package.json" -Destination "$tempDir\package.json" -ErrorAction SilentlyContinue
Copy-Item -Path "$localPath\package-lock.json" -Destination "$tempDir\package-lock.json" -ErrorAction SilentlyContinue
Copy-Item -Path "$localPath\artisan" -Destination "$tempDir\artisan"
Copy-Item -Path "$localPath\vite.config.js" -Destination "$tempDir\vite.config.js" -ErrorAction SilentlyContinue
Copy-Item -Path "$localPath\tailwind.config.js" -Destination "$tempDir\tailwind.config.js" -ErrorAction SilentlyContinue
Copy-Item -Path "$localPath\postcss.config.js" -Destination "$tempDir\postcss.config.js" -ErrorAction SilentlyContinue
Copy-Item -Path "$localPath\DEPLOYMENT_SERVER.md" -Destination "$tempDir\DEPLOYMENT_SERVER.md"
Copy-Item -Path "$localPath\README.md" -Destination "$tempDir\README.md"

# Create the archive
$archivePath = "$env:TEMP\biggestlogs.tar.gz"
Write-Host "Compressing files..." -ForegroundColor Yellow

# Use tar if available (Windows 10+), otherwise suggest manual method
try {
    Push-Location $tempDir
    tar -czf $archivePath * 2>&1 | Out-Null
    Pop-Location
    
    Write-Host "✅ Archive created: $archivePath" -ForegroundColor Green
    Write-Host ""
    Write-Host "📤 Uploading to server..." -ForegroundColor Yellow
    Write-Host ""
    
    # Try to upload using SCP
    Write-Host "Attempting to upload via SCP..." -ForegroundColor Cyan
    scp $archivePath "$server`:$remotePath.tar.gz"
    
    if ($LASTEXITCODE -eq 0) {
        Write-Host ""
        Write-Host "✅ Upload successful!" -ForegroundColor Green
        Write-Host ""
        Write-Host "📝 Next steps:" -ForegroundColor Yellow
        Write-Host "1. SSH into the server: ssh $server" -ForegroundColor White
        Write-Host "2. Extract the files: cd /root" -ForegroundColor White
        Write-Host "   tar -xzf biggestlogs-deploy.tar.gz" -ForegroundColor White
        Write-Host "3. Move to web directory: mv biggestlogs-deploy/* /var/www/biggestlogs/" -ForegroundColor White
        Write-Host "4. Follow deployment guide: cat /var/www/biggestlogs/DEPLOYMENT_SERVER.md" -ForegroundColor White
    } else {
        Write-Host "⚠️  SCP upload failed. Please use manual upload method." -ForegroundColor Red
        Write-Host ""
        Write-Host "Manual upload instructions:" -ForegroundColor Yellow
        Write-Host "1. Archive created at: $archivePath" -ForegroundColor White
        Write-Host "2. Use WinSCP or FileZilla to upload to: $server" -ForegroundColor White
        Write-Host "3. Upload location: /root/biggestlogs-deploy.tar.gz" -ForegroundColor White
    }
    
    # Cleanup
    Remove-Item $tempDir -Recurse -Force
    
} catch {
    Write-Host "❌ Error: $_" -ForegroundColor Red
    Write-Host ""
    Write-Host "Alternative method:" -ForegroundColor Yellow
    Write-Host "Files prepared in: $tempDir" -ForegroundColor White
    Write-Host "Please manually archive and upload these files." -ForegroundColor White
}

Write-Host ""
Write-Host "📖 See DEPLOYMENT_SERVER.md for complete deployment guide" -ForegroundColor Cyan

