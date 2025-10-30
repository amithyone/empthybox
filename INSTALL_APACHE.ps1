# Install Apache and phpMyAdmin via Webmin
Write-Host "🚀 Installing Apache, PHP, and phpMyAdmin..." -ForegroundColor Cyan

$password = "61btnCsn5RUu1UBpJzXLhBmdd"
$server = "75.119.139.18"

Write-Host "📤 Uploading installation script..." -ForegroundColor Yellow

# Read and upload the bash script
$scriptContent = Get-Content "install-apache-phpmyadmin.sh" -Raw
$encoded = [Convert]::ToBase64String([System.Text.Encoding]::UTF8.GetBytes($scriptContent))

& "C:\Program Files\PuTTY\plink.exe" -ssh -pw $password root@$server "echo '$encoded' | base64 -d > /root/install-apache-phpmyadmin.sh"
& "C:\Program Files\PuTTY\plink.exe" -ssh -pw $password root@$server "chmod +x /root/install-apache-phpmyadmin.sh"

Write-Host "✅ Script uploaded" -ForegroundColor Green
Write-Host ""
Write-Host "🚀 Running installation (may take 5-10 minutes)..." -ForegroundColor Yellow
Write-Host ""

& "C:\Program Files\PuTTY\plink.exe" -ssh -pw $password root@$server "bash /root/install-apache-phpmyadmin.sh"

Write-Host ""
Write-Host "✅ INSTALLATION COMPLETE!" -ForegroundColor Green
Write-Host ""
Write-Host "🌐 phpMyAdmin URL:" -ForegroundColor Cyan
Write-Host "   http://$server/phpmyadmin" -ForegroundColor White
Write-Host ""
Write-Host "🔐 Login Information:" -ForegroundColor Yellow
Write-Host "   Apache Auth (optional extra security):" -ForegroundColor White
Write-Host "   - Username: admin" -ForegroundColor Gray
Write-Host "   - Password: admin123" -ForegroundColor Gray
Write-Host ""
Write-Host "   phpMyAdmin (main login):" -ForegroundColor White
Write-Host "   - Username: root" -ForegroundColor Gray
Write-Host "   - Password: [your MySQL root password]" -ForegroundColor Gray
Write-Host ""
Write-Host "⚠️  Change the Apache auth password immediately!" -ForegroundColor Red
Write-Host ""

