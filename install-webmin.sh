#!/bin/bash
# Automated Webmin Installation Script for BiggestLogs Server
# Run this on: 75.119.139.18

set -e

echo "🚀 Installing Webmin on BiggestLogs Server..."
echo "=========================================="
echo ""

# Update system
echo "📦 Updating system packages..."
apt-get update -y
apt-get upgrade -y

# Install required dependencies
echo "🔧 Installing dependencies..."
apt-get install -y \
    wget \
    perl \
    libnet-ssleay-perl \
    openssl \
    libauthen-pam-perl \
    libpam-runtime \
    libio-pty-perl \
    apt-show-versions \
    python3 \
    python3-pip \
    unzip \
    apt-transport-https

# Download Webmin
echo "📥 Downloading Webmin..."
cd /root
wget https://prdownloads.sourceforge.net/webadmin/webmin_2.000_all.deb

# Alternative download if first fails
if [ ! -f "webmin_2.000_all.deb" ]; then
    echo "⚠️  First download failed, trying alternative..."
    wget https://download.webmin.com/download/repository/webmin_2.012_all.deb
fi

# Install Webmin
echo "📦 Installing Webmin..."
dpkg --install webmin*.deb 2>&1 | grep -v "^$" || true

# Fix any dependency issues
echo "🔧 Fixing dependencies..."
apt-get install -f -y

# Start and enable Webmin
echo "🚀 Starting Webmin service..."
systemctl start webmin
systemctl enable webmin

# Configure firewall
echo "🔥 Configuring firewall..."
if command -v ufw &> /dev/null; then
    ufw allow 10000/tcp
    echo "✅ Firewall rule added for port 10000"
fi

# Check Webmin status
echo ""
echo "📊 Checking Webmin status..."
systemctl status webmin --no-pager | head -10

# Get server IP
SERVER_IP=$(hostname -I | awk '{print $1}')

echo ""
echo "✅ Webmin installed successfully!"
echo "=========================================="
echo ""
echo "🌐 Access Webmin at:"
echo "   https://$SERVER_IP:10000"
echo "   https://75.119.139.18:10000"
echo ""
echo "👤 Login credentials:"
echo "   Username: root"
echo "   Password: [your root password]"
echo ""
echo "📝 Important Notes:"
echo "   1. The first time you access, you'll see an SSL warning"
echo "   2. Click 'Advanced' and 'Proceed to site'"
echo "   3. Change your password immediately after first login"
echo ""
echo "🔒 Security Recommendations:"
echo "   1. Change root password in Webmin"
echo "   2. Configure firewall to restrict access"
echo "   3. Setup SSL certificate"
echo ""
echo "📖 For more information, see: INSTALL_WEBADMIN.md"
echo ""

