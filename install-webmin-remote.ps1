# PowerShell Script to Install Webmin Remotely on Contabo Server

Write-Host "🚀 BiggestLogs - Remote Webmin Installation" -ForegroundColor Cyan
Write-Host ""

$serverIP = "75.119.139.18"
$username = "root"
$password = "61btnCsn5RUu1UBpJzXLhBmdd"

Write-Host "Connecting to server..." -ForegroundColor Yellow

# Check if plink is available
$plinkPath = Get-Command plink -ErrorAction SilentlyContinue

if (-not $plinkPath) {
    Write-Host "❌ plink not found. Installing PuTTY..." -ForegroundColor Red
    
    # Try to install PuTTY
    try {
        winget install PuTTY.PuTTY --accept-package-agreements --accept-source-agreements
        Write-Host "✅ PuTTY installed. Please run this script again." -ForegroundColor Green
        exit
    } catch {
        Write-Host "❌ Could not install PuTTY automatically." -ForegroundColor Red
        Write-Host ""
        Write-Host "Please install PuTTY manually:" -ForegroundColor Yellow
        Write-Host "  1. Download: https://the.earth.li/~sgtatham/putty/latest/w64/putty.exe" -ForegroundColor White
        Write-Host "  2. Run: installer-putty.exe" -ForegroundColor White
        Write-Host "  3. Then run this script again" -ForegroundColor White
        exit 1
    }
}

Write-Host "✅ plink found" -ForegroundColor Green
Write-Host ""

# Upload and run the installation script
$scriptPath = "$PSScriptRoot\install-webmin.sh"

if (-not (Test-Path $scriptPath)) {
    Write-Host "❌ install-webmin.sh not found in current directory" -ForegroundColor Red
    Write-Host "Please make sure you're running this from the BiggestLogs project directory"
    exit 1
}

Write-Host "📤 Uploading installation script..." -ForegroundColor Yellow

# Upload script
$pscpPath = Get-Command pscp -ErrorAction SilentlyContinue

if ($pscpPath) {
    $remotePath = "${username}@${serverIP}:/root/install-webmin.sh"
    pscp -pw $password $scriptPath $remotePath
    Write-Host "✅ Script uploaded" -ForegroundColor Green
} else {
    Write-Host "⚠️  pscp not found. Will use alternative method..." -ForegroundColor Yellow
    
    # Use plink to upload via echo
    $scriptContent = Get-Content $scriptPath -Raw
    $encodedContent = [Convert]::ToBase64String([System.Text.Encoding]::UTF8.GetBytes($scriptContent))
    
    plink -ssh -pw $password "$username@$serverIP" "echo '$encodedContent' | base64 -d > /root/install-webmin.sh; chmod +x /root/install-webmin.sh"
    Write-Host "✅ Script uploaded" -ForegroundColor Green
}

Write-Host ""
Write-Host "🚀 Running Webmin installation on server..." -ForegroundColor Yellow
Write-Host "(This will take a few minutes)" -ForegroundColor Gray
Write-Host ""

# Run the installation script remotely
plink -ssh -pw $password "$username@$serverIP" "bash /root/install-webmin.sh"

Write-Host ""
Write-Host "✅ Installation completed!" -ForegroundColor Green
Write-Host ""
Write-Host "🌐 Webmin is now available at:" -ForegroundColor Cyan
Write-Host "   https://$serverIP:10000" -ForegroundColor White
Write-Host ""
Write-Host "👤 Login with:" -ForegroundColor Cyan
Write-Host "   Username: root" -ForegroundColor White
Write-Host "   Password: [your root password]" -ForegroundColor White
Write-Host ""
Write-Host "🔒 First Steps:" -ForegroundColor Yellow
Write-Host "   1. Open browser and go to the URL above" -ForegroundColor White
Write-Host "   2. Accept SSL certificate warning" -ForegroundColor White
Write-Host "   3. Login with root credentials" -ForegroundColor White
Write-Host "   4. Change password immediately" -ForegroundColor White
Write-Host ""
Write-Host "📖 See INSTALL_WEBADMIN.md for detailed guide" -ForegroundColor Cyan

