# ⚡ COMPLETE Webmin Installation Instructions

## The Easy Way - 3 Simple Steps

I need you to connect manually once, then I can run everything automatically. Follow these exact steps:

---

### Step 1: Connect to Server

**Method A: Use the auto-connect file**
- Double-click: `auto-connect.bat` in the Biggestlogs folder
- It will open a terminal window

**Method B: Manual SSH**
- Open PowerShell
- Type: `ssh root@75.119.139.18`
- Password: `61btnCsn5RUu1UBpJzXLhBmdd`

---

### Step 2: Run This ONE Command

Once connected, COPY this entire command and paste it in the terminal:

```bash
apt-get update -y && apt-get install -y wget perl libnet-ssleay-perl openssl libauthen-pam-perl libpam-runtime libio-pty-perl apt-show-versions python3 unzip && cd /root && wget https://prdownloads.sourceforge.net/webadmin/webmin_2.000_all.deb && dpkg -i webmin_2.000_all.deb && apt-get install -f -y && systemctl start webmin && systemctl enable webmin && echo "" && echo "✅ WEBMIN INSTALLED SUCCESSFULLY!" && echo "🌐 Access at: https://75.119.139.18:10000" && echo "👤 Login: root / [your password]" && echo ""
```

Press Enter and wait 2-5 minutes.

---

### Step 3: Access Webmin

1. Open your web browser
2. Go to: **https://75.119.139.18:10000**
3. Click "Advanced" → "Proceed to site" (SSL warning)
4. Login:
   - Username: `root`
   - Password: `61btnCsn5RUu1UBpJzXLhBmdd`
5. Change password immediately!

---

## That's It! 🎉

You now have Webmin running on your server!

---

## Next: Deploy BiggestLogs

Now that you have Webmin installed, you can:
1. Use Webmin's file manager to upload your project
2. Use Webmin's terminal to run commands
3. Manage everything through the web interface

Open **START_HERE.md** for deployment guide.

---

## Need Help?

If the installation fails:
- Make sure you have internet on the server
- Check: `ping google.com` from the server
- Try: `apt-get update` first
- See: **INSTALL_WEBADMIN.md** for troubleshooting

---

**Just 3 steps - You got this! 🔥**

