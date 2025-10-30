# Automated SSH Connection and Deployment Script
# BiggestLogs to Contabo Server

Write-Host "🚀 BiggestLogs Server Connection Script" -ForegroundColor Cyan
Write-Host ""

$serverIP = "75.119.139.18"
$username = "root"
$password = "61btnCsn5RUu1UBpJzXLhBmdd"

# Check if OpenSSH is available
Write-Host "Checking SSH availability..." -ForegroundColor Yellow
$sshPath = Get-Command ssh -ErrorAction SilentlyContinue

if (-not $sshPath) {
    Write-Host "❌ SSH not found. Please install OpenSSH client." -ForegroundColor Red
    Write-Host "Run: winget install Microsoft.OpenSSH.Beta" -ForegroundColor Yellow
    exit 1
}

Write-Host "✅ SSH found" -ForegroundColor Green
Write-Host ""

# Method 1: Use plink (PuTTY) if available - supports password
$plinkPath = Get-Command plink -ErrorAction SilentlyContinue

if ($plinkPath) {
    Write-Host "✅ Found plink (PuTTY) - using for automated connection" -ForegroundColor Green
    Write-Host "Connecting to server..." -ForegroundColor Yellow
    
    # Save password temporarily for plink
    $plinkCommands = @"
cd /root
pwd
ls -la
echo '✅ Connected successfully!'
echo 'Ready for deployment commands.'
"@
    
    $plinkCommands | Out-File -FilePath "$env:TEMP\plink-commands.txt" -Encoding ASCII
    
    # Connect using plink
    plink -ssh -pw $password $username@$serverIP -m "$env:TEMP\plink-commands.txt"
    
    Remove-Item "$env:TEMP\plink-commands.txt" -ErrorAction SilentlyContinue
    
} else {
    Write-Host "⚠️  plink not found" -ForegroundColor Yellow
    Write-Host ""
    Write-Host "Manual connection required:" -ForegroundColor Cyan
    Write-Host "=========================" -ForegroundColor White
    Write-Host "You need to manually connect using one of these methods:" -ForegroundColor Yellow
    Write-Host ""
    Write-Host "Option 1: Install PuTTY (Recommended)" -ForegroundColor Green
    Write-Host "  winget install PuTTY.PuTTY" -ForegroundColor White
    Write-Host "  Then run this script again" -ForegroundColor White
    Write-Host ""
    Write-Host "Option 2: Manual SSH Connection" -ForegroundColor Green
    Write-Host "  ssh root@75.119.139.18" -ForegroundColor White
    Write-Host "  Password: 61btnCsn5RUu1UBpJzXLhBmdd" -ForegroundColor White
    Write-Host ""
    Write-Host "Option 3: Use VS Code Remote SSH" -ForegroundColor Green
    Write-Host "  1. Install 'Remote - SSH' extension" -ForegroundColor White
    Write-Host "  2. Connect to: root@75.119.139.18" -ForegroundColor White
    Write-Host "  3. Open folder: /root" -ForegroundColor White
    Write-Host ""
    Write-Host "After connecting, download QUICK_DEPLOY.md to the server" -ForegroundColor Yellow
    Write-Host "and follow the deployment instructions." -ForegroundColor Yellow
}

Write-Host ""
Write-Host "📝 Next Steps:" -ForegroundColor Cyan
Write-Host "1. Connect to the server (see options above)" -ForegroundColor White
Write-Host "2. Upload your BiggestLogs project files" -ForegroundColor White
Write-Host "3. Follow the deployment guide" -ForegroundColor White
Write-Host ""
Write-Host "📖 See QUICK_DEPLOY.md for complete instructions" -ForegroundColor Yellow

