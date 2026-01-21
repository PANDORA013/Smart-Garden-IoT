# 🚀 QUICK START: Setup Smart Garden IoT dalam Satu Jaringan

## 📋 **Requirement:**
- ✅ Laptop/PC dengan XAMPP
- ✅ Raspberry Pi Pico W
- ✅ Router WiFi (satu jaringan untuk semua device)
- ✅ Sensor soil, DHT11, LCD, Relay

---

## 🌐 **STEP 1: Setup Jaringan WiFi**

### **1.1. Pastikan Semua Device di Jaringan yang Sama**

```
Router WiFi: "Bocil" (password: kesayanganku)
    │
    ├─── 📱 Laptop/PC (192.168.18.X)    → Backend Laravel
    ├─── 🔧 Pico W    (192.168.18.Y)    → IoT Device
    └─── 📱 Phone     (192.168.18.Z)    → Dashboard Access
```

**Cek IP Address:**

**Windows (CMD/PowerShell):**
```powershell
ipconfig

# Output:
# Wireless LAN adapter Wi-Fi:
#   IPv4 Address. . . . . . . . . . . : 192.168.18.35  ← CATAT INI!
```

**macOS/Linux (Terminal):**
```bash
ifconfig

# Output:
# en0: flags=8863<UP,BROADCAST,SMART,RUNNING>
#      inet 192.168.18.35 netmask 0xffffff00 ← CATAT INI!
```

---

## 💻 **STEP 2: Setup Backend Laravel**

### **2.1. Start XAMPP Services**

1. **Buka XAMPP Control Panel**
2. **Start Apache** (port 80/443)
3. **Start MySQL** (port 3306)

### **2.2. Verify Database Connection**

1. Buka **phpMyAdmin**: `http://localhost/phpmyadmin`
2. Cek database `smart_garden` sudah ada
3. Cek table `monitorings` dan `device_settings` ada

### **2.3. Test Backend API**

**Buka Browser:**
```
http://localhost:8000/api/monitoring/logs?limit=5
```

**Expected Response:**
```json
[
  {
    "id": 831,
    "time": "16:06",
    "date": "2026-01-17",
    "level": "INFO",
    "device": "PICO_CABAI_01",
    "message": "Monitoring OK"
  }
]
```

✅ **Jika sukses** → Backend siap!  
❌ **Jika error** → Cek Laravel logs di `storage/logs/laravel.log`

### **2.4. Get Your Local IP**

**IMPORTANT!** Gunakan IP lokal laptop, **BUKAN** `localhost` atau `127.0.0.1`

```powershell
# Windows PowerShell
ipconfig | findstr "IPv4"

# Output:
# IPv4 Address. . . . . . . . . . . : 192.168.18.35  ← USE THIS!
```

**Your Server URL:**
```
http://192.168.18.35:8000/api/monitoring/insert
       ↑
       Ganti dengan IP laptop Anda!
```

---

## 🔧 **STEP 3: Configure Pico W**

### **3.1. Update WiFi & Server Config**

**Edit file: `arduino/main.py`**

```python
# ================= CONFIG =================
WIFI_SSID = "Bocil"                      # ← Nama WiFi router Anda
WIFI_PASSWORD = "kesayanganku"           # ← Password WiFi

SERVER_URL = "http://192.168.18.35:8000/api/monitoring/insert"
                     ↑↑↑↑↑↑↑↑↑↑↑↑↑↑
                     GANTI DENGAN IP LAPTOP ANDA!

DEVICE_ID = "PICO_CABAI_01"              # ← Nama device (unik per device)
SERVER_INTERVAL = 15000                  # 15 detik (jangan terlalu cepat!)
```

### **3.2. Upload Code ke Pico W**

**Menggunakan Thonny:**
1. Buka **Thonny IDE**
2. Connect Pico W via USB
3. Pilih **MicroPython (Raspberry Pi Pico)** di interpreter
4. Open `main.py`
5. Klik **Run** atau **Save to Raspberry Pi Pico**

**Menggunakan VS Code:**
1. Install extension **Pico-W-Go**
2. Connect Pico W via USB
3. Press `Ctrl+Shift+P` → "Pico-W-Go: Upload project to Pico"

### **3.3. Monitor Serial Output**

**Thonny:**
- Serial output otomatis tampil di bawah

**VS Code:**
- Press `Ctrl+Shift+P` → "Pico-W-Go: Open Terminal"

**Expected Output:**
```
SMART GARDEN START (SENSITIVE MODE)
Connecting WiFi...
WiFi OK: 192.168.18.41        ← IP Pico W (catat ini!)

RAW:1850 | EMA:1848
Sending data to server...
>> Data SENT, response: 201   ← SUCCESS!
CMD SERVER: False
```

✅ **Response 201** = Berhasil kirim data!  
❌ **Server error** = Cek firewall/IP address

---

## 🔥 **STEP 4: Setup Firewall (Windows)**

### **4.1. Allow Laravel Development Server**

**Windows Firewall:**
1. **Windows Security** → **Firewall & network protection**
2. **Allow an app through firewall**
3. Cari **PHP** atau **php.exe**
4. ✅ Centang **Private** dan **Public**

**PowerShell (as Administrator):**
```powershell
# Allow port 8000 for Laravel
New-NetFirewallRule -DisplayName "Laravel Dev Server" -Direction Inbound -LocalPort 8000 -Protocol TCP -Action Allow

# Allow port 80 for Apache (XAMPP)
New-NetFirewallRule -DisplayName "Apache HTTP" -Direction Inbound -LocalPort 80 -Protocol TCP -Action Allow
```

### **4.2. Test Connection dari Phone**

**Buka browser di phone (sambung WiFi yang sama):**
```
http://192.168.18.35:8000
       ↑↑↑↑↑↑↑↑↑↑↑↑↑↑
       IP laptop Anda
```

✅ **Dashboard tampil** → Firewall OK!  
❌ **Cannot connect** → Cek firewall lagi

---

## 🎯 **STEP 5: Verify System Working**

### **5.1. Check Pico Serial Monitor**

```
RAW:1850 | EMA:1848
Sending data to server...
>> Data SENT, response: 201   ← ✅ SUCCESS!
>> Server response: {
     "success": true,
     "data": {
       "soil_moisture": 98.7  ← ✅ Backend calculate!
     }
   }
CMD SERVER: False
```

### **5.2. Check Backend Logs**

**Laravel Log: `storage/logs/laravel.log`**
```
[2026-01-21 09:06:37] local.INFO: 📊 Collecting calibration samples - Device: PICO_CABAI_01, Current: 15/30
```

### **5.3. Check Dashboard**

**Open Browser:**
```
http://192.168.18.35:8000
```

**Expected:**
- ✅ Grafik menampilkan RAW ADC
- ✅ Soil Moisture terhitung (98%)
- ✅ Temperature terdeteksi (30°C)
- ✅ Relay status tampil

---

## 🛠️ **TROUBLESHOOTING**

### **Problem 1: Pico tidak bisa connect ke server**

**Symptom:**
```
Sending data to server...
>> Server error: [Errno 110] ETIMEDOUT
```

**Solution:**
1. ✅ Cek IP laptop: `ipconfig` → pastikan `192.168.18.X`
2. ✅ Update `SERVER_URL` di main.py dengan IP yang benar
3. ✅ Cek firewall: Pastikan port 8000 open
4. ✅ Test dari browser phone: `http://192.168.18.35:8000`
5. ✅ Restart router jika masih gagal

---

### **Problem 2: Response 404 Not Found**

**Symptom:**
```
>> Data SENT, response: 404
```

**Solution:**
1. ✅ Cek Laravel running: `php artisan serve --host=0.0.0.0 --port=8000`
2. ✅ Verify route exists:
   ```bash
   php artisan route:list | findstr "monitoring"
   ```
3. ✅ Pastikan endpoint benar: `/api/monitoring/insert`

---

### **Problem 3: Soil Moisture 0% terus**

**Symptom:**
```
RAW:1850 | SOIL: 0%
```

**Solution:**
1. ✅ Tunggu auto-calibration (30 samples ≈ 7.5 menit)
2. ✅ Cek logs: `storage/logs/laravel.log`
3. ✅ Manual reset calibration:
   ```sql
   UPDATE device_settings 
   SET sensor_min = 4095, sensor_max = 1500 
   WHERE device_id = 'PICO_CABAI_01';
   ```
4. ✅ Restart Pico W

---

### **Problem 4: WiFi connect failed**

**Symptom:**
```
Connecting WiFi...
WiFi FAILED
```

**Solution:**
1. ✅ Cek SSID dan password benar
2. ✅ Pastikan WiFi 2.4GHz (Pico W tidak support 5GHz!)
3. ✅ Cek jarak Pico ke router (max 10-15 meter)
4. ✅ Restart router jika perlu

---

## 📊 **Network Diagram**

```
┌─────────────────────────────────────────────────────┐
│              Router WiFi: "Bocil"                   │
│              192.168.18.1                           │
└─────────────────────────────────────────────────────┘
         │                 │                 │
         │                 │                 │
    ┌────▼─────┐     ┌────▼─────┐     ┌────▼─────┐
    │ Laptop   │     │ Pico W   │     │  Phone   │
    │          │     │          │     │          │
    │ Laravel  │◄────┤ Sensor   │     │ Browser  │
    │ Backend  │ POST│ Relay    │     │ Monitor  │
    │          │ JSON│ LCD      │     │          │
    │ :8000    │     │          │     │          │
    └──────────┘     └──────────┘     └──────────┘
   192.168.18.35   192.168.18.41   192.168.18.100
```

---

## ✅ **Success Checklist**

Pastikan semua ini ✅ sebelum lanjut:

- [ ] ✅ XAMPP Apache & MySQL running
- [ ] ✅ Laravel serve running di `:8000`
- [ ] ✅ IP laptop dicatat (contoh: `192.168.18.35`)
- [ ] ✅ Firewall port 8000 dibuka
- [ ] ✅ Pico W connect ke WiFi yang sama
- [ ] ✅ `SERVER_URL` di main.py sudah benar
- [ ] ✅ Serial Monitor tampil `response: 201`
- [ ] ✅ Dashboard bisa dibuka dari browser
- [ ] ✅ Soil moisture terhitung di backend
- [ ] ✅ Grafik menampilkan data real-time

---

## 🎉 **Final Test**

### **1. Test dari Pico Serial Monitor:**
```
✅ WiFi OK: 192.168.18.41
✅ RAW:1850 | EMA:1848
✅ >> Data SENT, response: 201
✅ >> Server response: {"success":true}
```

### **2. Test dari Browser (Laptop):**
```
http://localhost:8000  → ✅ Dashboard tampil
```

### **3. Test dari Browser (Phone):**
```
http://192.168.18.35:8000  → ✅ Dashboard tampil
```

### **4. Test Relay Control:**
```
Dashboard → Klik tombol "NYALAKAN POMPA"
Pico Serial Monitor → "🔌 RELAY COMMAND FROM SERVER: ON"
                   → Relay click sound! ✅
```

---

## 🚀 **Quick Commands**

### **Start Laravel Server:**
```bash
cd "c:\xampp\htdocs\Smart Garden IoT"
php artisan serve --host=0.0.0.0 --port=8000
```

### **Check Network:**
```powershell
# Windows
ipconfig | findstr "IPv4"
ping 192.168.18.41  # Test Pico connection

# Test API
curl http://192.168.18.35:8000/api/monitoring/logs?limit=1
```

### **Reset Calibration:**
```sql
-- phpMyAdmin atau MySQL CLI
UPDATE device_settings 
SET sensor_min = 4095, sensor_max = 1500 
WHERE device_id = 'PICO_CABAI_01';
```

---

## 📞 **Support**

**Common Issues:**
- WiFi tidak connect → Pastikan WiFi 2.4GHz
- Server timeout → Cek firewall & IP address
- Soil 0% → Tunggu auto-calibration (30 samples)
- Dashboard tidak buka → Cek Laravel serve running

**Log Files:**
- Laravel: `storage/logs/laravel.log`
- Pico W: Serial Monitor (Thonny/VS Code)
- Apache: `xampp/apache/logs/error.log`

---

## 🎯 **Summary**

1. **Setup WiFi** → Semua device satu jaringan
2. **Start XAMPP** → Apache + MySQL
3. **Start Laravel** → `php artisan serve --host=0.0.0.0`
4. **Get IP** → `ipconfig` → Catat IP (contoh: 192.168.18.35)
5. **Update Pico** → `SERVER_URL` dengan IP laptop
6. **Upload Pico** → Thonny/VS Code
7. **Test** → Serial Monitor response 201 ✅
8. **Open Dashboard** → Browser phone/laptop ✅

**Selamat! Sistem Smart Garden Anda sudah running!** 🌱💧🎉
