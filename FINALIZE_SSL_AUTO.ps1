$password = "61btnCsn5RUu1UBpJzXLhBmdd"
$plink = "C:\Program Files\PuTTY\plink.exe"

Write-Host "Configuring Apache SSL..." -ForegroundColor Yellow

$scriptContent = Get-Content "configure-apache-ssl.sh" -Raw
$encoded = [Convert]::ToBase64String([System.Text.Encoding]::UTF8.GetBytes($scriptContent))

& $plink -ssh -pw $password root@75.119.139.18 "echo '$encoded' | base64 -d > /root/configure-apache-ssl.sh"
& $plink -ssh -pw $password root@75.119.139.18 "chmod +x /root/configure-apache-ssl.sh"
& $plink -ssh -pw $password root@75.119.139.18 "bash /root/configure-apache-ssl.sh"

Write-Host "SSL Configuration Complete!" -ForegroundColor Green
Write-Host "Visit: https://biggestlogs.com" -ForegroundColor Cyan

