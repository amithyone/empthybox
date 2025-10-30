$password = "61btnCsn5RUu1UBpJzXLhBmdd"
$plink = "C:\Program Files\PuTTY\plink.exe"

Write-Host "Uploading and running final configuration..." -ForegroundColor Yellow

$scriptContent = Get-Content "setup-subdomains-final.sh" -Raw
$encoded = [Convert]::ToBase64String([System.Text.Encoding]::UTF8.GetBytes($scriptContent))

& $plink -ssh -pw $password root@75.119.139.18 "echo '$encoded' | base64 -d > /root/setup-subdomains-final.sh"
& $plink -ssh -pw $password root@75.119.139.18 "chmod +x /root/setup-subdomains-final.sh"
& $plink -ssh -pw $password root@75.119.139.18 "bash /root/setup-subdomains-final.sh"

Write-Host ""
Write-Host "Complete! Subdomains configured!" -ForegroundColor Green
Write-Host "Visit: https://server.biggestlogs.com" -ForegroundColor Cyan
Write-Host "Visit: https://db.biggestlogs.com" -ForegroundColor Cyan

