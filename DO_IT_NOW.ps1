# Force Install Webmin - No Questions Asked
Write-Host "🚀 FORCE INSTALLING WEBMIN NOW..." -ForegroundColor Cyan

$password = "61btnCsn5RUu1UBpJzXLhBmdd"
$server = "75.119.139.18"

# Accept SSH key first
Write-Host "📡 Accepting SSH key..." -ForegroundColor Yellow
echo y | & "C:\Program Files\PuTTY\plink.exe" -ssh root@$server exit 2>$null

# Install command
Write-Host "📦 Starting installation (this will take 3-5 minutes)..." -ForegroundColor Yellow

$installCmd = "apt-get update -y -qq; apt-get install -y wget perl libnet-ssleay-perl openssl libauthen-pam-perl libpam-runtime libio-pty-perl apt-show-versions python3 unzip -qq; cd /root; wget -q https://prdownloads.sourceforge.net/webadmin/webmin_2.000_all.deb; dpkg -i webmin_2.000_all.deb; apt-get install -f -y -qq; systemctl start webmin; systemctl enable webmin; echo 'DONE'"

Start-Process -FilePath "C:\Program Files\PuTTY\plink.exe" -ArgumentList "-ssh","-pw","$password","root@$server","$installCmd" -Wait -NoNewWindow

Write-Host ""
Write-Host "✅ INSTALLATION COMPLETE!" -ForegroundColor Green
Write-Host ""
Write-Host "🌐 OPEN BROWSER TO:" -ForegroundColor Cyan
Write-Host "   https://75.119.139.18:10000" -ForegroundColor White
Write-Host ""
Write-Host "👤 LOGIN:" -ForegroundColor Cyan  
Write-Host "   root / $password" -ForegroundColor White

