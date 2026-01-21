# 🌐 Network Access Guide - Smart Garden IoT

## 📱 Cara Akses Website dari Device Lain (HP, Pico W, dll)

### ✅ Setup sudah selesai! Tinggal jalankan:

---

## 🚀 Method 1: PowerShell Script (RECOMMENDED)

```powershell
.\start-network.ps1
```

Script ini akan:
- ✅ Detect IP address otomatis
- ✅ Start Laravel server di 0.0.0.0:8000
- ✅ Show akses URL untuk device lain

---

## 🚀 Method 2: NPM Script (Laravel + Vite sekaligus)

```bash
npm run start:network
```

Ini akan menjalankan:
- 🔵 Vite dev server (port 5173) untuk hot reload
- 🟣 Laravel serve (port 8000) untuk backend

---

## 🚀 Method 3: Manual (jika butuh kontrol penuh)

### Terminal 1 - Laravel Server:
```bash
php artisan serve --host=0.0.0.0 --port=8000
```

### Terminal 2 - Vite Dev Server (optional, untuk hot reload):
```bash
npm run dev:network
```

---

## 📡 URL untuk Akses

Dengan IP address: **192.168.18.35**

### 🌐 Website (dari HP/Laptop lain):
```
http://192.168.18.35:8000
```

### 🤖 Pico W API Endpoint:
```
http://192.168.18.35:8000/api/monitoring/insert
```

Update di `main_async.py`:
```python
SERVER_URL = "http://192.168.18.35:8000/api/monitoring/insert"
```

---

## 🔧 Troubleshooting

### ❌ "Cannot access from phone"

**Cek Firewall Windows:**
```powershell
# Allow port 8000 (Laravel)
New-NetFirewallRule -DisplayName "Laravel Dev Server" -Direction Inbound -LocalPort 8000 -Protocol TCP -Action Allow

# Allow port 5173 (Vite - optional)
New-NetFirewallRule -DisplayName "Vite Dev Server" -Direction Inbound -LocalPort 5173 -Protocol TCP -Action Allow
```

**Atau manual:**
1. Windows Defender Firewall → Advanced Settings
2. Inbound Rules → New Rule
3. Port → TCP → Port 8000 → Allow Connection

### ❌ "IP Address berubah"

IP dynamic bisa berubah tiap restart router. Solusi:

**Option 1: Set Static IP di Router**
- Masuk router admin (biasa 192.168.1.1)
- DHCP Settings → Reserved IP
- Bind MAC address laptop ke IP tetap

**Option 2: Update manual tiap berubah**
```bash
# Cek IP baru
ipconfig

# Update .env
APP_URL=http://[IP_BARU]:8000

# Update vite.config.js
hmr: { host: '[IP_BARU]' }

# Update main_async.py
SERVER_URL = "http://[IP_BARU]:8000/api/monitoring/insert"
```

### ❌ "Pico W masih gagal connect"

1. **Cek jaringan sama:**
   - Laptop: `ipconfig` → lihat subnet (192.168.18.x)
   - Pico W: WIFI_SSID harus sama

2. **Test koneksi:**
   ```powershell
   # Dari laptop, ping Pico W (jika tahu IP-nya)
   ping 192.168.18.xxx
   ```

3. **Cek Serial Monitor Pico W:**
   ```
   ✅ WiFi Connected: 192.168.18.45  <-- IP Pico W
   📡 Sending data...
   ✅ Data sent successfully
   ```

   Jika muncul error, cek:
   - `❌ Network error: ETIMEDOUT` → Firewall block
   - `❌ Server returned status: 404` → URL salah
   - `❌ WiFi Connection Failed` → Password WiFi salah

---

## 🔐 Security Note (Development Only)

⚠️ **PENTING:** Setup ini untuk development/testing!

**JANGAN deploy ke production dengan:**
- `--host=0.0.0.0` tanpa firewall
- `APP_DEBUG=true`
- Default Laravel encryption key

**Untuk production:**
1. Gunakan HTTPS dengan SSL certificate
2. Setup proper firewall rules
3. Use reverse proxy (Nginx/Apache)
4. Set `APP_DEBUG=false`
5. Generate unique `APP_KEY`

---

## 📊 Testing Checklist

- [ ] Start server: `.\start-network.ps1`
- [ ] Buka browser di laptop: `http://192.168.18.35:8000` ✅
- [ ] Buka browser di HP: `http://192.168.18.35:8000` ✅
- [ ] Pico W kirim data: check Serial Monitor ✅
- [ ] Dashboard update real-time ✅
- [ ] Test disconnect WiFi → auto reconnect ✅
- [ ] Test stop server → data di-queue ✅

---

## 🎯 Quick Reference

| Device | URL | Purpose |
|--------|-----|---------|
| 💻 Laptop Browser | `http://localhost:8000` | Development |
| 📱 Phone/Tablet | `http://192.168.18.35:8000` | Testing UI |
| 🤖 Pico W | `http://192.168.18.35:8000/api/monitoring/insert` | Send data |
| 🔧 Vite HMR | `http://192.168.18.35:5173` | Hot reload (optional) |

---

## 💡 Tips

1. **Gunakan IP static** untuk development yang stabil
2. **Bookmark URL** di HP untuk akses cepat
3. **Check firewall** jika device lain tidak bisa akses
4. **Monitor Serial Output** Pico W untuk troubleshoot
5. **Restart server** setelah update .env

---

## 📚 Related Files

- `start-network.ps1` - PowerShell script untuk start server
- `.env` - Configuration (APP_URL)
- `vite.config.js` - Vite network config
- `arduino/main_async.py` - Pico W code dengan SERVER_URL

---

**Happy Testing! 🚀**
