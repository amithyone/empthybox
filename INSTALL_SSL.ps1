Write-Host "Installing Free SSL Certificate..." -ForegroundColor Cyan

$password = "61btnCsn5RUu1UBpJzXLhBmdd"
$server = "75.119.139.18"

Write-Host "Uploading SSL installation script..." -ForegroundColor Yellow

$scriptContent = Get-Content "install-ssl.sh" -Raw
$encoded = [Convert]::ToBase64String([System.Text.Encoding]::UTF8.GetBytes($scriptContent))

$plink = "C:\Program Files\PuTTY\plink.exe"

& $plink -ssh -pw $password root@$server "echo '$encoded' | base64 -d > /root/install-ssl.sh"
& $plink -ssh -pw $password root@$server "chmod +x /root/install-ssl.sh"

Write-Host "Script uploaded" -ForegroundColor Green
Write-Host "Running SSL installation..." -ForegroundColor Yellow

& $plink -ssh -pw $password root@$server "bash /root/install-ssl.sh"

Write-Host ""
Write-Host "SSL Installation Complete!" -ForegroundColor Green
Write-Host "Access: https://biggestlogs.com" -ForegroundColor Cyan

