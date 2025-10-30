# Deploy BiggestLogs to server

$SERVER = "75.119.139.18"
$PASSWORD = "61btnCsn5RUu1UBpJzXLhBmdd"

Write-Host "================================" -ForegroundColor Cyan
Write-Host "  Deploying BiggestLogs App" -ForegroundColor Green
Write-Host "================================" -ForegroundColor Cyan
Write-Host ""

# Create archive
Write-Host "Creating archive..." -ForegroundColor Yellow
$exclude = @("node_modules", "vendor", ".git", ".env")
$files = Get-ChildItem -Path . -Recurse | Where-Object {
    $excluded = $false
    foreach ($item in $exclude) {
        if ($_.FullName -like "*\$item\*") { $excluded = $true; break }
    }
    return !$excluded
}

Compress-Archive -Path $files -DestinationPath "biggestlogs.zip" -Force
Write-Host "Archive created!" -ForegroundColor Green

# Upload
Write-Host "Uploading to server..." -ForegroundColor Yellow
& "C:\Program Files\PuTTY\pscp.exe" -pw $PASSWORD biggestlogs.zip root@${SERVER}:/root/
& "C:\Program Files\PuTTY\pscp.exe" -pw $PASSWORD setup-laravel.sh root@${SERVER}:/root/

# Deploy
Write-Host "Deploying on server..." -ForegroundColor Yellow
& "C:\Program Files\PuTTY\plink.exe" -ssh -pw $PASSWORD -batch root@${SERVER} "
rm -rf /var/www/biggestlogs;
mkdir -p /var/www/biggestlogs;
cd /root;
unzip -q biggestlogs.zip -d /var/www/biggestlogs;
bash /root/setup-laravel.sh;
"

# Cleanup
Remove-Item biggestlogs.zip -ErrorAction SilentlyContinue

Write-Host ""
Write-Host "================================" -ForegroundColor Cyan
Write-Host "  Deployment Complete!" -ForegroundColor Green
Write-Host "================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Visit: https://biggestlogs.com" -ForegroundColor Green
Write-Host "Admin: admin@biggestlogs.com / password" -ForegroundColor Yellow
Write-Host ""

