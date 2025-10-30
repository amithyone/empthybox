# 🌐 Domain Setup for biggestlogs.com

## DNS Settings for Your Domain

### Required DNS Records

Add these DNS records in your domain registrar (where you bought biggestlogs.com):

```
Type    | Name      | Value               | TTL
--------|-----------|---------------------|-------
A       | @         | 75.119.139.18       | 3600
A       | www       | 75.119.139.18       | 3600
A       | *         | 75.119.139.18       | 3600 (Wildcard for subdomains)
```

### What Each Record Does:

1. **A Record (@)** - Points biggestlogs.com to your server IP
2. **A Record (www)** - Points www.biggestlogs.com to your server IP
3. **A Record (*)** - Enables ALL subdomains to work automatically (api.biggestlogs.com, admin.biggestlogs.com, etc.)

---

## Where to Add DNS Records

Go to your domain registrar (GoDaddy, Namecheap, Cloudflare, etc.):

1. Login to your domain account
2. Find DNS Management / DNS Settings
3. Add the three A records above
4. Save changes
5. Wait 5-15 minutes for propagation

---

## Server Configuration

Once DNS is set up, we need to configure Apache to handle your domain and subdomains.

### Current Server IP: 75.119.139.18

---

## Complete Setup Steps

### Step 1: Add DNS Records (Do This First!)

Go to your domain registrar and add the DNS records shown above.

Wait 5-15 minutes, then verify with:
```
ping biggestlogs.com
```
Should return: 75.119.139.18

---

### Step 2: Configure Apache

Run these commands on your server (I'll create an automated script):

```bash
# Create virtual host for biggestlogs.com
cat > /etc/apache2/sites-available/biggestlogs.conf << 'EOF'
<VirtualHost *:80>
    ServerName biggestlogs.com
    ServerAlias www.biggestlogs.com *.biggestlogs.com
    DocumentRoot /var/www/biggestlogs/public
    
    <Directory /var/www/biggestlogs/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/biggestlogs-error.log
    CustomLog ${APACHE_LOG_DIR}/biggestlogs-access.log combined
</VirtualHost>
EOF

# Enable site
a2ensite biggestlogs.conf
a2dissite 000-default.conf

# Create necessary directories
mkdir -p /var/www/biggestlogs/public

# Restart Apache
systemctl restart apache2
```

---

### Step 3: SSL Certificate (Recommended)

Install Let's Encrypt free SSL:

```bash
apt-get install -y certbot python3-certbot-apache

# Get SSL certificate
certbot --apache -d biggestlogs.com -d www.biggestlogs.com

# Auto-renewal
certbot renew --dry-run
```

---

### Step 4: Enable Wildcard Subdomains in Laravel

Update your `.env` file:

```env
APP_URL=https://biggestlogs.com
APP_ENV=production
```

And in `config/app.php`:
```php
'url' => env('APP_URL', 'https://biggestlogs.com'),
```

---

## Subdomain Configuration

With the wildcard DNS (*) record, ALL subdomains will work automatically:

- biggestlogs.com ✓
- www.biggestlogs.com ✓
- admin.biggestlogs.com ✓
- api.biggestlogs.com ✓
- sms.biggestlogs.com ✓
- any.biggestlogs.com ✓

---

## Testing

After DNS propagates (5-15 minutes):

1. Visit: http://biggestlogs.com (should show your site)
2. Visit: http://www.biggestlogs.com (should show your site)
3. Visit: http://test.biggestlogs.com (should show your site)
4. Install SSL: Run certbot command above
5. Visit: https://biggestlogs.com (secure version)

---

## Current Status

- Webmin: ✅ Installed
- Server IP: 75.119.139.18
- Apache: Pending installation
- Domain: biggestlogs.com (needs DNS configuration)
- Subdomains: Will work with wildcard DNS record

---

## Next Steps

1. ✅ Add DNS records at your registrar (You do this)
2. ⏳ Wait for DNS propagation (5-15 minutes)
3. ⏳ Configure Apache virtual host (I'll automate this)
4. ⏳ Install SSL certificate (I'll automate this)
5. ⏳ Deploy BiggestLogs application (I'll automate this)

---

## Quick Checklist

- [ ] Add A record @ pointing to 75.119.139.18
- [ ] Add A record www pointing to 75.119.139.18
- [ ] Add A record * (wildcard) pointing to 75.119.139.18
- [ ] Wait for DNS propagation
- [ ] Run Apache configuration script
- [ ] Install SSL certificate
- [ ] Deploy application

---

**Need Help?**
- DNS issues: Check with your domain registrar
- Server issues: Contact me
- Testing: Use `nslookup biggestlogs.com` to check DNS

