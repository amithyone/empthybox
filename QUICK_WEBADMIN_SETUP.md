# ⚡ Quick Webmin Setup - 3 Easy Steps

## Connect & Install Webmin in 5 Minutes

### Step 1: Open SSH Connection

**Option A: Using auto-connect.bat** (Easiest)
- Double-click: `auto-connect.bat` in the Biggestlogs folder
- Wait for it to connect

**Option B: Manual Connection**
```
ssh root@75.119.139.18
Password: 61btnCsn5RUu1UBpJzXLhBmdd
```

**Option C: Using WinSCP**
1. Open WinSCP
2. Connect to: `root@75.119.139.18`
3. Click "Open Terminal" button

---

### Step 2: Copy & Paste Installation Commands

Once connected, copy ALL of this and paste it:

```bash
apt-get update && apt-get upgrade -y && \
apt-get install -y wget perl libnet-ssleay-perl openssl libauthen-pam-perl libpam-runtime libio-pty-perl apt-show-versions python3 unzip && \
cd /root && \
wget https://prdownloads.sourceforge.net/webadmin/webmin_2.000_all.deb && \
dpkg --install webmin_2.000_all.deb && \
apt-get install -f -y && \
systemctl start webmin && \
systemctl enable webmin && \
ufw allow 10000/tcp 2>/dev/null || echo "Firewall not configured" && \
echo "" && \
echo "✅ Webmin installed!" && \
echo "🌐 Access at: https://$(hostname -I | awk '{print $1}'):10000" && \
echo "👤 Login: root / [your password]"
```

Press Enter and wait 2-5 minutes.

---

### Step 3: Access Webmin

1. Open your web browser
2. Go to: **https://75.119.139.18:10000**
3. Click "Advanced" → "Proceed to site" (for SSL warning)
4. Login:
   - Username: `root`
   - Password: `61btnCsn5RUu1UBpJzXLhBmdd`

---

## ✅ Done! 

You now have Webmin installed and can:
- ✅ Manage server via web interface
- ✅ Install software easily
- ✅ Configure Nginx
- ✅ Manage databases
- ✅ Monitor resources
- ✅ And much more!

---

## 🎯 Next Steps

Now that you have Webmin, you can deploy BiggestLogs easily:
1. Use Webmin's file manager to upload your project
2. Use Webmin's terminal to run deployment commands
3. Manage everything through the web interface

See: **START_HERE.md** for deployment guide

---

**Need Help?** See: **INSTALL_WEBADMIN.md** for detailed troubleshooting

