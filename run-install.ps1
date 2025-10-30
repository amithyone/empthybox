# Install Webmin on BiggestLogs Server
param(
    [string]$Password = "61btnCsn5RUu1UBpJzXLhBmdd",
    [string]$Server = "root@75.119.139.18"
)

Write-Host "🚀 Installing Webmin on BiggestLogs Server..." -ForegroundColor Cyan
Write-Host ""

$plink = "C:\Program Files\PuTTY\plink.exe"

if (-not (Test-Path $plink)) {
    Write-Host "❌ PuTTY not found. Installing..." -ForegroundColor Red
    winget install PuTTY.PuTTY --silent
    Start-Sleep -Seconds 5
}

Write-Host "📡 Connecting to server..." -ForegroundColor Yellow

$commands = @'
apt-get update -y
apt-get install -y wget perl libnet-ssleay-perl openssl libauthen-pam-perl libpam-runtime libio-pty-perl apt-show-versions python3 unzip
cd /root
wget https://prdownloads.sourceforge.net/webadmin/webmin_2.000_all.deb
dpkg -i webmin_2.000_all.deb
apt-get install -f -y
systemctl start webmin
systemctl enable webmin
echo "✅ Webmin installed successfully!"
echo ""
echo "🌐 Access at: https://75.119.139.18:10000"
echo "👤 Login: root"
'@

$commands | Out-File -FilePath "$env:TEMP\webmin-install.sh" -Encoding ASCII -NoNewline

try {
    & $plink -ssh -pw $Password -batch $Server "bash -s" < "$env:TEMP\webmin-install.sh"
    
    Write-Host ""
    Write-Host "✅ Installation completed!" -ForegroundColor Green
    Write-Host ""
    Write-Host "🌐 Open your browser and go to:" -ForegroundColor Cyan
    Write-Host "   https://75.119.139.18:10000" -ForegroundColor White
    Write-Host ""
    Write-Host "👤 Login with:" -ForegroundColor Cyan
    Write-Host "   Username: root" -ForegroundColor White
    Write-Host "   Password: $Password" -ForegroundColor White
    
} catch {
    Write-Host "❌ Error: $_" -ForegroundColor Red
    Write-Host ""
    Write-Host "Please run manually:" -ForegroundColor Yellow
    Write-Host "   plink -ssh -pw $Password $Server" -ForegroundColor White
}

Remove-Item "$env:TEMP\webmin-install.sh" -ErrorAction SilentlyContinue

