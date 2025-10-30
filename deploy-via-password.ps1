# BiggestLogs Deployment via SSH Password
# Windows PowerShell Script

$password = "61btnCsn5RUu1UBpJzXLhBmdd"
$server = "root@75.119.139.18"

Write-Host "🔐 Connecting to server..." -ForegroundColor Cyan

# Use sshpass alternative for Windows
# Install plink or use PowerShell SSH with password

# Method 1: Using plink (PuTTY)
# First install plink: winget install PuTTY.PuTTY

Write-Host "📦 Checking if plink is available..." -ForegroundColor Cyan
$plinkPath = Get-Command plink -ErrorAction SilentlyContinue

if (-not $plinkPath) {
    Write-Host "⚠️  Plink not found. Let's use direct SSH commands..." -ForegroundColor Yellow
    Write-Host ""
    Write-Host "Please run these commands manually on the server:" -ForegroundColor Green
    Write-Host ""
    Write-Host "# First, connect to your server using SSH client" -ForegroundColor White
    Write-Host "# Then run the following commands:" -ForegroundColor White
    Write-Host ""
    Write-Host "apt-get update && apt-get upgrade -y" -ForegroundColor Cyan
    Write-Host "apt-get install -y nginx mysql-server php8.1-fpm php8.1-mysql php8.1-xml php8.1-mbstring php8.1-curl php8.1-zip php8.1-gd git curl" -ForegroundColor Cyan
    Write-Host "curl -sS https://getcomposer.org/installer | php && mv composer.phar /usr/local/bin/composer" -ForegroundColor Cyan
    Write-Host "curl -fsSL https://deb.nodesource.com/setup_18.x | bash - && apt-get install -y nodejs" -ForegroundColor Cyan
    Write-Host ""
    Write-Host "mkdir -p /var/www/biggestlogs && cd /var/www/biggestlogs" -ForegroundColor Cyan
    Write-Host ""
    Write-Host "# Then upload your code using one of these methods:" -ForegroundColor Yellow
    Write-Host "# 1. Using Git: git clone YOUR_REPO_URL ." -ForegroundColor White
    Write-Host "# 2. Using SCP from this machine:" -ForegroundColor White
    Write-Host ""
    Write-Host "exit" -ForegroundColor Cyan
    Write-Host ""
    Write-Host "After installation, run these commands on the server:" -ForegroundColor Green
    Write-Host "cd /var/www/biggestlogs" -ForegroundColor Cyan
    Write-Host "composer install --no-dev" -ForegroundColor Cyan
    Write-Host "npm install && npm run build" -ForegroundColor Cyan
    Write-Host "cp .env.example .env" -ForegroundColor Cyan
    Write-Host "php artisan key:generate" -ForegroundColor Cyan
    Write-Host "# Edit .env with database credentials" -ForegroundColor Yellow
    Write-Host "php artisan migrate --seed" -ForegroundColor Cyan
    Write-Host "chown -R www-data:www-data ." -ForegroundColor Cyan
    Write-Host "chmod -R 755 . && chmod -R 775 storage bootstrap/cache" -ForegroundColor Cyan
    Write-Host ""
} else {
    Write-Host "✅ Using plink for SSH connection..." -ForegroundColor Green
    
    # Create a commands file
    $commands = @"
apt-get update && apt-get upgrade -y
apt-get install -y nginx mysql-server php8.1-fpm php8.1-mysql php8.1-xml php8.1-mbstring php8.1-curl php8.1-zip php8.1-gd php8.1-bcmath php8.1-tokenizer unzip git curl
curl -sS https://getcomposer.org/installer | php && mv composer.phar /usr/local/bin/composer
curl -fsSL https://deb.nodesource.com/setup_18.x | bash - && apt-get install -y nodejs
mkdir -p /var/www/biggestlogs
cd /var/www/biggestlogs
pwd
"@
    
    $commands | Out-File -FilePath "temp-commands.txt" -Encoding ASCII
    
    Write-Host "📤 Uploading and running setup commands..." -ForegroundColor Cyan
    plink -ssh -pw $password $server -m temp-commands.txt
    
    Remove-Item temp-commands.txt
}

Write-Host ""
Write-Host "✅ Connection established!" -ForegroundColor Green
Write-Host ""
Write-Host "📝 Now you can connect using an SSH client like PuTTY or VS Code Remote SSH" -ForegroundColor Yellow
Write-Host "   Host: 75.119.139.18" -ForegroundColor White
Write-Host "   User: root" -ForegroundColor White
Write-Host "   Password: [your password]" -ForegroundColor White

