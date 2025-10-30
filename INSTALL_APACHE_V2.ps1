Write-Host "Installing Apache, PHP, and phpMyAdmin..." -ForegroundColor Cyan

$password = "61btnCsn5RUu1UBpJzXLhBmdd"
$server = "75.119.139.18"

Write-Host "Uploading script..." -ForegroundColor Yellow

$scriptContent = Get-Content "install-apache-phpmyadmin.sh" -Raw
$encoded = [Convert]::ToBase64String([System.Text.Encoding]::UTF8.GetBytes($scriptContent))

$plink = "C:\Program Files\PuTTY\plink.exe"

& $plink -ssh -pw $password root@$server "echo '$encoded' | base64 -d > /root/install-apache-phpmyadmin.sh"
& $plink -ssh -pw $password root@$server "chmod +x /root/install-apache-phpmyadmin.sh"

Write-Host "Script uploaded" -ForegroundColor Green
Write-Host "Running installation..." -ForegroundColor Yellow

& $plink -ssh -pw $password root@$server "bash /root/install-apache-phpmyadmin.sh"

Write-Host ""
Write-Host "INSTALLATION COMPLETE!" -ForegroundColor Green
Write-Host "phpMyAdmin URL: http://$server/phpmyadmin" -ForegroundColor Cyan
Write-Host "Apache Auth - Username: admin, Password: admin123" -ForegroundColor Yellow
Write-Host "phpMyAdmin - Username: root, Password: [your MySQL password]" -ForegroundColor Yellow

