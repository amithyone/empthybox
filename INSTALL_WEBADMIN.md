# Install Webmin on Contabo Server

## Server Information
- **IP**: 75.119.139.18
- **User**: root
- **Password**: 61btnCsn5RUu1UBpJzXLhBmdd

---

## What is Webmin?
Webmin is a web-based interface for Linux system administration. It allows you to:
- Manage users and groups
- Configure Apache/Nginx
- Manage MySQL databases
- Monitor system resources
- Configure firewall
- Install software packages
- View logs
- And much more!

**Access**: https://75.119.139.18:10000

---

## Quick Installation (One Command)

### Connect to Your Server
Using any method from START_HERE.md (WinSCP terminal, PuTTY, or SSH)

Then run these commands:

```bash
# Update system
apt-get update && apt-get upgrade -y

# Install Webmin
cd /root
wget https://prdownloads.sourceforge.net/webadmin/webmin_2.000_all.deb

# Alternative download if above fails:
# wget https://github.com/webmin/webmin/releases/download/2.012/webmin_2.012_all.deb

# Install dependencies
apt-get install -y perl libnet-ssleay-perl openssl libauthen-pam-perl libpam-runtime libio-pty-perl apt-show-versions python3 python3-pip unzip

# Install Webmin
dpkg --install webmin*.deb

# Fix any dependency issues
apt-get install -f -y

# Start and enable Webmin
systemctl start webmin
systemctl enable webmin

# Check status
systemctl status webmin
```

---

## Configure Firewall

If you have a firewall, allow Webmin:

```bash
# If using UFW
ufw allow 10000/tcp

# If using iptables
iptables -A INPUT -p tcp --dport 10000 -j ACCEPT
```

---

## Access Webmin

1. Open your web browser
2. Go to: **https://75.119.139.18:10000**
3. Accept the SSL warning (self-signed certificate)
4. Login with:
   - **Username**: root
   - **Password**: 61btnCsn5RUu1UBpJzXLhBmdd

---

## First Steps in Webmin

After logging in:

### 1. Change Admin Password
- Click "Change Passwords" on the left menu
- Select "root" user
- Enter new password

### 2. Configure System
- **System**: View system info, manage users
- **Servers**: Configure Apache, MySQL, etc.
- **Networking**: Configure firewall, network interfaces
- **Tools**: File manager, SSH login, command shell

### 3. Install Virtualmin (Optional - For Laravel/Domains)
Virtualmin is built on Webmin and provides advanced hosting features:

```bash
# Install Virtualmin
cd /root
wget http://software.virtualmin.com/gpl/scripts/virtualmin-install.sh
chmod +x virtualmin-install.sh
./virtualmin-install.sh --uninstall

# Or install with full features:
./virtualmin-install.sh
```

---

## Security Recommendations

### 1. Change Default Settings
- Change root password
- Disable root SSH login (use sudo instead)
- Setup firewall properly

### 2. SSL Certificate (Optional but Recommended)
```bash
# Install Let's Encrypt certificate for Webmin
certbot certonly --standalone -d 75.119.139.18

# Or use Webmin's built-in SSL certificate wizard:
# In Webmin: Webmin → Webmin Configuration → SSL Encryption
```

### 3. Restrict Access
Edit Webmin config to allow only your IP:
```bash
nano /etc/webmin/config
```

Add:
```
allowed=YOUR.IP.ADDRESS.*
```

Then restart:
```bash
systemctl restart webmin
```

---

## Using Webmin with BiggestLogs

### Install PHP-FPM via Webmin
1. Go to **System** → **Software Package Updates**
2. Install PHP 8.1 FPM

### Configure Nginx via Webmin
1. Go to **Servers** → **Nginx**
2. Add virtual server for your domain
3. Configure PHP-FPM integration

### Manage MySQL via Webmin
1. Go to **Servers** → **MySQL Database Server**
2. Create databases and users
3. Import/export databases

### Monitor Resources
- **System** → **System and Server Status**: CPU, RAM, Disk
- **System** → **Activity Monitor**: Running processes
- **System** → **Disk Quotas**: Disk usage

---

## Troubleshooting

### Can't Access Webmin
```bash
# Check if Webmin is running
systemctl status webmin

# Check if port is open
netstat -tlnp | grep 10000

# Restart Webmin
systemctl restart webmin

# Check Webmin logs
tail -f /var/log/webmin.log
```

### Forgot Webmin Password
```bash
# Reset Webmin password
/usr/share/webmin/changepass.pl /etc/webmin root NEW_PASSWORD
```

### Port Already in Use
```bash
# Change Webmin port
nano /etc/webmin/miniserv.conf

# Change: port=10000 to port=10001
# Then restart:
systemctl restart webmin
```

---

## Complete Installation Script

I'll create an automated script for you to run on the server:

```bash
# Connect to your server first, then run:

#!/bin/bash
echo "🚀 Installing Webmin..."
apt-get update
apt-get install -y wget perl libnet-ssleay-perl openssl libauthen-pam-perl libpam-runtime libio-pty-perl apt-show-versions python3 unzip

cd /tmp
wget https://prdownloads.sourceforge.net/webadmin/webmin_2.000_all.deb
dpkg --install webmin_2.000_all.deb
apt-get install -f -y

systemctl start webmin
systemctl enable webmin
ufw allow 10000/tcp

echo "✅ Webmin installed successfully!"
echo "🌐 Access at: https://$(hostname -I | awk '{print $1}'):10000"
echo "👤 Username: root"
```

---

## Next Steps

1. ✅ Install Webmin (follow steps above)
2. ✅ Access Webmin in browser
3. ✅ Change default password
4. ✅ Configure firewall
5. ✅ Install required software via Webmin
6. ✅ Deploy your BiggestLogs application

---

**Need help? Check Webmin documentation**: https://webmin.com/docs.html

