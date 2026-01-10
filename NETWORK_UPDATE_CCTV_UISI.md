# ⚡ NETWORK UPDATE - WiFi CCTV_UISI

**Tanggal:** 10 Januari 2026
**Status:** ✅ Konfigurasi Updated

---

## 📡 **KONFIGURASI BARU**

### **WiFi Network**
- **SSID:** `CCTV_UISI`
- **Password:** `08121191`
- **Type:** WiFi Network

### **Server**
- **IP Address:** `10.134.42.169` (Ethernet)
- **Server URL:** `http://10.134.42.169:8000/api/monitoring/insert`
- **Dashboard:** `http://10.134.42.169:8000`

### **Pico W Device**
- **Device ID:** `PICO_CABAI_01`
- **WiFi:** CCTV_UISI
- **Expected IP:** 192.168.x.x (DHCP dari router CCTV_UISI)

---

## 🔄 **PERUBAHAN DARI KONFIGURASI LAMA**

| Item | Lama (Bocil) | Baru (CCTV_UISI) |
|------|--------------|------------------|
| SSID | Bocil | CCTV_UISI |
| Password | kesayanganku | 08121191 |
| Server IP | 10.134.42.169 | 10.134.42.169 (sama) |
| Network | Bocil WiFi | CCTV_UISI WiFi |

---

## 📋 **FILE YANG SUDAH DIUPDATE**

✅ **arduino/network_config.py**
- SSID_CCTV = "CCTV_UISI"
- PASSWORD_CCTV = "08121191"
- SERVER_URL_CCTV = "http://10.134.42.169:8000/api/monitoring/insert"

✅ **arduino/pico_micropython.py**
- Fallback config updated ke CCTV_UISI

✅ **Backup konfigurasi WiFi Bocil tersimpan** (bisa switch kapan saja)

---

## 🚀 **CARA UPLOAD KE PICO W**

### **Via Thonny IDE:**

1. **Connect Pico W ke USB**
   - Pastikan Thonny detect Pico W

2. **Upload network_config.py**
   ```
   File → Open → arduino/network_config.py
   File → Save As → Raspberry Pi Pico
   Save dengan nama: network_config.py
   ```

3. **Upload pico_micropython.py**
   ```
   File → Open → arduino/pico_micropython.py
   File → Save As → Raspberry Pi Pico
   Save dengan nama: main.py (agar auto-run saat boot)
   ```

4. **Reset Pico W**
   - Press **CTRL+D** di Thonny Shell
   - Atau press tombol **BOOTSEL** di Pico W
   - Atau disconnect-reconnect USB

5. **Monitor Serial Output**
   ```
   ✅ Network config loaded from network_config.py
   📡 SSID: CCTV_UISI
   🌐 Server: http://10.134.42.169:8000/api/monitoring/insert
   🆔 Device: PICO_CABAI_01
   
   🔌 Connecting to WiFi CCTV_UISI...
   ✅ WiFi Connected! 📡
   IP Address: 192.168.x.x
   
   📡 Sending data to server...
   ✅ Server Response: 201
   📥 Data berhasil dikirim!
   ```

---

## 🧪 **TESTING KONEKSI**

### **Test 1: Ping Server (dari komputer lain di WiFi CCTV_UISI)**
```powershell
ping 10.134.42.169
```

### **Test 2: Browser Test**
```
http://10.134.42.169:8000
http://10.134.42.169:8000/api/monitoring/stats
```

### **Test 3: Manual API Test**
```powershell
Invoke-RestMethod -Uri "http://10.134.42.169:8000/api/monitoring/insert" `
  -Method POST `
  -Body (@{
    device_id="PICO_CABAI_01"
    temperature=28.5
    soil_moisture=75
    raw_adc=2500
    relay_status=$false
    ip_address="192.168.x.x"
  } | ConvertTo-Json) `
  -ContentType "application/json"
```

---

## 🔀 **SWITCH KEMBALI KE WiFi BOCIL (Jika Perlu)**

Edit `arduino/network_config.py`:

```python
# Comment baris CCTV:
# SSID = SSID_CCTV
# PASSWORD = PASSWORD_CCTV
# SERVER_URL = SERVER_URL_CCTV

# Uncomment baris Bocil:
SSID = SSID_BOCIL
PASSWORD = PASSWORD_BOCIL
SERVER_URL = SERVER_URL_BOCIL
```

Upload ulang `network_config.py` ke Pico W → Reset.

---

## ⚠️ **TROUBLESHOOTING**

### **Problem 1: Pico W tidak bisa connect ke WiFi CCTV_UISI**

**Symptoms:**
```
❌ WiFi connection failed!
Retrying...
```

**Solutions:**
1. Pastikan WiFi **CCTV_UISI** aktif dan dalam jangkauan
2. Cek password benar: `08121191`
3. Restart router WiFi CCTV_UISI
4. Coba manual connect laptop/HP ke CCTV_UISI terlebih dahulu
5. Cek MAC filtering di router (whitelist MAC Pico W jika perlu)

### **Problem 2: Connect WiFi berhasil, tapi tidak bisa kirim data**

**Symptoms:**
```
✅ WiFi Connected! IP: 192.168.x.x
❌ Connection failed! HTTP Error: -2
```

**Solutions:**
1. Pastikan server IP `10.134.42.169` masih benar:
   ```powershell
   ipconfig
   ```
2. Ping test dari laptop ke server:
   ```powershell
   ping 10.134.42.169
   ```
3. Cek Laravel server running:
   ```powershell
   php artisan serve --host=0.0.0.0 --port=8000
   ```
4. Cek firewall:
   ```powershell
   .\check-network.ps1
   ```

### **Problem 3: Server IP berubah**

**Symptoms:**
- Kemarin work, hari ini tidak
- Server pindah dari Ethernet ke WiFi, atau sebaliknya

**Solutions:**
1. Cek IP server terbaru:
   ```powershell
   ipconfig | Select-String "IPv4"
   ```
2. Update `network_config.py`:
   ```python
   SERVER_URL_CCTV = "http://[IP_BARU]:8000/api/monitoring/insert"
   ```
3. Upload ulang ke Pico W

---

## 📊 **NETWORK TOPOLOGY**

```
┌─────────────────────────────────────────────────────────┐
│           ROUTER WiFi "CCTV_UISI"                       │
│           Password: 08121191                             │
│           Network: 192.168.x.0/24                        │
└────────┬──────────────────────────────┬─────────────────┘
         │                               │
         │ WiFi                          │ Ethernet (?)
         │                               │
     ┌───▼──────────┐              ┌────▼────────────┐
     │  Pico W      │              │  Server PC      │
     │  192.168.x.x │◄───HTTP──────│  10.134.42.169  │
     │  (WiFi)      │              │  (Ethernet 2)   │
     └──────────────┘              └─────────────────┘
      Sensor Data ─────────────────► Laravel + MySQL
```

**CATATAN:** 
- Server pakai **Ethernet** (10.134.42.169)
- Pico W pakai **WiFi CCTV_UISI**
- Koneksi melalui router CCTV_UISI

---

## ✅ **CHECKLIST SETUP**

- [x] Update network_config.py (SSID + Password)
- [x] Update pico_micropython.py (fallback config)
- [ ] Upload network_config.py ke Pico W
- [ ] Upload pico_micropython.py (as main.py) ke Pico W
- [ ] Reset Pico W
- [ ] Monitor serial output (cek WiFi connected)
- [ ] Test browser: http://10.134.42.169:8000
- [ ] Verifikasi data masuk ke database

---

## 🎯 **NEXT STEPS**

1. **Start Laravel Server:**
   ```powershell
   php artisan serve --host=0.0.0.0 --port=8000
   ```

2. **Upload ke Pico W via Thonny:**
   - network_config.py
   - pico_micropython.py (save as main.py)

3. **Monitor Koneksi:**
   - Serial output di Thonny
   - Dashboard: http://10.134.42.169:8000
   - Database check: `php check-database.php`

4. **Verify Data Flow:**
   - Pico W kirim data setiap 10 detik
   - Dashboard update otomatis
   - Database bertambah record baru

---

**Status:** ✅ Ready to Upload
**WiFi:** CCTV_UISI
**Server:** 10.134.42.169:8000
