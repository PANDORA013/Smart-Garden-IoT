# 🌐 NETWORK CONFIGURATION GUIDE
## Raspberry Pi Pico W + Laravel Server

---

## 📋 **STATUS KONFIGURASI SAAT INI**

### ✅ **Server (Komputer)**
- **Interface:** Ethernet 2
- **IP Address:** `10.134.42.169`
- **Network:** 10.134.42.0/24
- **Laravel Server:** Running on port 8000
- **MySQL:** Running
- **Firewall:** Port 8000 OPEN

### ✅ **Raspberry Pi Pico W**
- **WiFi SSID:** `Bocil`
- **WiFi Password:** `kesayanganku`
- **Server URL:** `http://10.134.42.169:8000/api/monitoring/insert`
- **Device ID:** `PICO_CABAI_01`

---

## 🎯 **CARA KERJA JARINGAN**

```
┌─────────────────────────────────────────────────────────────┐
│                   ROUTER WIFI "Bocil"                        │
│                   Network: 192.168.18.x                      │
└───────┬────────────────────────────────┬────────────────────┘
        │                                 │
        │ WiFi                            │ Ethernet
        │                                 │
    ┌───▼────────────┐              ┌────▼──────────────┐
    │  Pico W        │              │  Server           │
    │  IP: DHCP      │◄────HTTP─────│  10.134.42.169    │
    │  (WiFi Bocil)  │              │  (Ethernet 2)     │
    └────────────────┘              └───────────────────┘
         Sensor Data ───────────────► Laravel + MySQL
```

**PENTING:** 
- Server pakai **Ethernet** (10.134.42.169)
- Pico W pakai **WiFi "Bocil"**
- Keduanya harus bisa saling berkomunikasi via router

---

## 🔧 **CARA UPDATE KONFIGURASI**

### **Metode 1: Otomatis (Recommended)**

```powershell
# Jalankan script checker
.\check-network.ps1

# Script akan otomatis:
# ✅ Detect IP server
# ✅ Update network_config.py
# ✅ Check firewall
# ✅ Test API endpoint
```

### **Metode 2: Manual**

#### **1. Cek IP Server:**
```powershell
ipconfig | Select-String "IPv4|Ethernet"
```

#### **2. Update `arduino/network_config.py`:**
```python
# Edit bagian ini sesuai IP yang terdeteksi:
SERVER_URL_ETHERNET = "http://10.134.42.169:8000/api/monitoring/insert"

# Atau jika pakai WiFi:
SERVER_URL_WIFI = "http://192.168.18.35:8000/api/monitoring/insert"

# Pilih yang aktif:
SSID = SSID_ETHERNET
PASSWORD = PASSWORD_ETHERNET
SERVER_URL = SERVER_URL_ETHERNET
```

#### **3. Upload ke Pico W (Thonny IDE):**
- File → Open → `network_config.py`
- File → Save As → Pilih **"Raspberry Pi Pico"**
- Save as: `network_config.py`
- Ulangi untuk `pico_micropython.py`

#### **4. Reset Pico W:**
- Tekan **CTRL+D** di Thonny Shell
- Atau tekan tombol **BOOTSEL** di Pico W

---

## 🧪 **TESTING KONEKSI**

### **Test 1: Ping Server dari Komputer Lain**
```powershell
# Dari komputer/HP di jaringan yang sama
ping 10.134.42.169
```

### **Test 2: Browser Test**
```
http://10.134.42.169:8000
http://10.134.42.169:8000/api/monitoring/stats
```

### **Test 3: Pico W Serial Monitor**
Di Thonny IDE, lihat output:
```
✅ WiFi Connected! 📡
IP Address: 192.168.18.41
📡 Sending data to server...
✅ Server Response: 201
📥 Data berhasil dikirim!
```

---

## 🔀 **SKENARIO JARINGAN**

### **Skenario 1: Server Ethernet + Pico WiFi (CURRENT)**
```
Server: 10.134.42.169 (Ethernet)
Pico W: 192.168.18.x (WiFi "Bocil")
Config: SERVER_URL_ETHERNET
Status: ✅ WORKING (via router)
```

### **Skenario 2: Semua WiFi**
```
Server: 192.168.18.35 (WiFi "Bocil")
Pico W: 192.168.18.x (WiFi "Bocil")
Config: SERVER_URL_WIFI
Status: ✅ RECOMMENDED (same network)
```

### **Skenario 3: Hotspot HP**
```
Server: Tethering via Hotspot HP
Pico W: Connect ke Hotspot HP
Config: Update SERVER_URL sesuai IP hotspot
Status: ✅ PORTABLE (bisa dibawa-bawa)
```

---

## ⚠️ **TROUBLESHOOTING**

### **Problem 1: Pico W tidak bisa connect ke server**

**Symptoms:**
```
❌ Connection failed!
HTTP Error: -2 (ECONNREFUSED)
```

**Solutions:**
1. Cek IP server dengan `ipconfig`
2. Pastikan Pico W dan Server dalam **jaringan yang sama**
3. Test dengan `ping 10.134.42.169` dari komputer lain
4. Cek firewall: `.\check-network.ps1`
5. Restart Laravel server:
   ```powershell
   Stop-Process -Name php -Force
   php artisan serve --host=0.0.0.0 --port=8000
   ```

### **Problem 2: IP Server berubah (DHCP)**

**Symptoms:**
- Kemarin work, hari ini tidak
- Server IP berubah dari 10.134.42.169 ke IP lain

**Solutions:**
1. Jalankan `.\check-network.ps1` (auto-update)
2. Upload ulang `network_config.py` ke Pico W
3. **PERMANENT FIX:** Set static IP di router untuk server MAC address

### **Problem 3: Website tidak bisa diakses dari HP/komputer lain**

**Symptoms:**
- Localhost work, tapi `http://10.134.42.169:8000` timeout

**Solutions:**
1. Laravel server harus pakai `--host=0.0.0.0` (bukan `127.0.0.1`)
2. Firewall harus allow port 8000:
   ```powershell
   New-NetFirewallRule -DisplayName "Laravel Port 8000" `
       -Direction Inbound -LocalPort 8000 -Protocol TCP -Action Allow
   ```
3. Test dengan `http://localhost:8000` dulu

### **Problem 4: DHT22 Sensor Timeout**

**Symptoms:**
```
⚠️  DHT22 ERROR: Timeout reading sensor!
Using fallback value: 28°C
```

**Solutions:**
1. Cek koneksi kabel DHT22 ke GPIO 2
2. Tambahkan pull-up resistor 10kΩ (VCC ke Data pin)
3. Fallback value tetap akan dikirim (tidak blocking)

---

## 📁 **FILE PENTING**

| File | Lokasi | Fungsi |
|------|--------|--------|
| `network_config.py` | `arduino/` | Konfigurasi WiFi & Server URL |
| `pico_micropython.py` | `arduino/` | Main program Pico W |
| `check-network.ps1` | Root project | Network diagnostic tool |
| `.env` | Root project | Laravel database config |

---

## 🚀 **QUICK START**

### **Setup Awal (Sekali Aja):**
```powershell
# 1. Cek jaringan & auto-update config
.\check-network.ps1

# 2. Start Laravel server
php artisan serve --host=0.0.0.0 --port=8000

# 3. Upload ke Pico W (via Thonny)
#    - network_config.py
#    - pico_micropython.py

# 4. Reset Pico W (CTRL+D)

# 5. Buka browser
http://10.134.42.169:8000
```

### **Daily Use:**
```powershell
# Start server
php artisan serve --host=0.0.0.0 --port=8000

# Pico W otomatis connect & kirim data setiap 10 detik
```

---

## 📊 **MONITORING**

### **Check Network Status:**
```powershell
.\check-network.ps1
```

### **Check Database:**
```powershell
php check-database.php
```

### **Watch Server Logs:**
```powershell
# Terminal 1: Laravel Server
php artisan serve --host=0.0.0.0 --port=8000

# Terminal 2: Watch logs
Get-Content storage/logs/laravel.log -Tail 20 -Wait
```

---

## 🔐 **SECURITY NOTES**

1. **WiFi Password:** Jangan commit `network_config.py` ke public repo
2. **Firewall:** Port 8000 hanya open untuk local network
3. **Production:** Gunakan HTTPS + authentication untuk internet access

---

**Tanggal Update:** 10 Januari 2026
**Status:** ✅ Production Ready (Ethernet + WiFi)
**Next Update:** Jika IP server berubah, jalankan `.\check-network.ps1`
