# Upload BiggestLogs to server and deploy

$SERVER = "75.119.139.18"
$PASSWORD = "61btnCsn5RUu1UBpJzXLhBmdd"
$USER = "root"
$SERVER_PATH = "/var/www/biggestlogs"
$LOCAL_PATH = "C:\Users\LENOVO LEGION\Documents\Biggestlogs"

Write-Host "================================" -ForegroundColor Cyan
Write-Host "  Uploading BiggestLogs to Server" -ForegroundColor Green
Write-Host "================================" -ForegroundColor Cyan
Write-Host ""

# First, create a tar archive
Write-Host "Creating archive..." -ForegroundColor Yellow
$TEMP_TAR = "biggestlogs_deploy.tar.gz"

# Create archive excluding unnecessary files
$excludeFiles = @(
    "node_modules",
    "vendor",
    ".git",
    "storage\logs\*.log",
    ".env"
)

# Use PowerShell Compress-Archive
Compress-Archive -Path ".\*" -DestinationPath "$TEMP_TAR" -Force
Write-Host "Archive created!" -ForegroundColor Green

# Upload to server
Write-Host "Uploading to server..." -ForegroundColor Yellow
& "C:\Program Files\PuTTY\pscp.exe" -pw $PASSWORD $TEMP_TAR ${USER}@${SERVER}:/root/
Write-Host "Upload complete!" -ForegroundColor Green

# Clean up local archive
Remove-Item $TEMP_TAR -ErrorAction SilentlyContinue

# Now run the deployment script on server
Write-Host "Starting deployment on server..." -ForegroundColor Yellow
Write-Host ""

# First, upload the deployment script
& "C:\Program Files\PuTTY\pscp.exe" -pw $PASSWORD deploy-to-server.sh ${USER}@${SERVER}:/root/

# Extract and deploy on server
& "C:\Program Files\PuTTY\plink.exe" -ssh -pw $PASSWORD -batch ${USER}@${SERVER} "
rm -rf $SERVER_PATH;
mkdir -p $SERVER_PATH;
cd /root;
tar -xzf biggestlogs_deploy.tar.gz -C $SERVER_PATH;
cd $SERVER_PATH;
bash /root/deploy-to-server.sh;
"

Write-Host ""
Write-Host "================================" -ForegroundColor Cyan
Write-Host "  Deployment Complete!" -ForegroundColor Green
Write-Host "================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Visit: https://biggestlogs.com" -ForegroundColor Green
Write-Host "Admin: admin@biggestlogs.com / password" -ForegroundColor Yellow
Write-Host ""

