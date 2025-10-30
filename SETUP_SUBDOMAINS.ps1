$password = "61btnCsn5RUu1UBpJzXLhBmdd"
$plink = "C:\Program Files\PuTTY\plink.exe"

Write-Host "Configuring subdomains..." -ForegroundColor Yellow

$scriptContent = Get-Content "setup-subdomains.sh" -Raw
$encoded = [Convert]::ToBase64String([System.Text.Encoding]::UTF8.GetBytes($scriptContent))

& $plink -ssh -pw $password root@75.119.139.18 "echo '$encoded' | base64 -d > /root/setup-subdomains.sh"
& $plink -ssh -pw $password root@75.119.139.18 "chmod +x /root/setup-subdomains.sh"
& $plink -ssh -pw $password root@75.119.139.18 "bash /root/setup-subdomains.sh"

Write-Host "Subdomains configured!" -ForegroundColor Green
Write-Host "Webmin: https://server.biggestlogs.com" -ForegroundColor Cyan
Write-Host "phpMyAdmin: https://db.biggestlogs.com" -ForegroundColor Cyan

