# 🚀 QUICK START GUIDE - ESP32 CONNECTION

## ✅ SUDAH SELESAI (Backend Ready!)

- ✅ Laravel server running (http://localhost:8000)
- ✅ API endpoint configured (`/api/monitoring/insert`)
- ✅ Firewall rule created (Port 8000 allowed)
- ✅ Database migrated (monitorings table ready)

---

## 📥 YANG PERLU DIINSTALL (Hardware Side)

### 1. Download & Install Arduino IDE

**Link Download:**
```
https://www.arduino.cc/en/software
```

- Pilih: **Windows Win 10 and newer, 64 bits**
- Size: ~150 MB
- Install seperti biasa (Next > Next > Install)

---

### 2. Install ESP32 Board Support

**Setelah Arduino IDE terbuka:**

1. `File > Preferences` (Ctrl+,)
2. Di bagian "Additional boards manager URLs", paste:
   ```
   https://espressif.github.io/arduino-esp32/package_esp32_index.json
   ```
3. Klik OK
4. `Tools > Board > Boards Manager`
5. Search: `esp32`
6. Install: "esp32 by Espressif Systems"
7. Tunggu download (~300 MB, sekitar 10-15 menit)

---

### 3. Install Library ArduinoJson

1. `Sketch > Include Library > Manage Libraries`
2. Search: `ArduinoJson`
3. Install: "ArduinoJson" by Benoit Blanchon

---

### 4. Install USB Driver (Jika ESP32 Tidak Terdeteksi)

ESP32 menggunakan chip:
- **CP2102** (Silicon Labs)
- **CH340** (WCH)

**Download:**
- CP2102: https://www.silabs.com/developers/usb-to-uart-bridge-vcp-drivers
- CH340: https://sparks.gogo.co.nz/ch340.html

Install driver, restart PC, colokkan ESP32.

**Cek di Device Manager:**
- `Win+X > Device Manager`
- Cari di "Ports (COM & LPT)"
- Harusnya muncul: "USB-SERIAL CH340 (COM3)" atau similar

---

## 🔌 KONFIGURASI ESP32 CODE

### 1. Buka File Arduino

```
arduino/cabai_monitoring_esp32.ino
```

### 2. Edit WiFi Configuration

**Cari bagian ini (baris ~27-28):**

```cpp
const char* ssid = "YOUR_WIFI_SSID";           
const char* password = "YOUR_WIFI_PASSWORD";   
```

**Ganti dengan WiFi Anda:**

```cpp
const char* ssid = "NamaWiFiAnda";           // Contoh: "TP-Link_123"
const char* password = "PasswordWiFi123";     // Contoh: "mypassword"
```

### 3. Edit Server URL

**Cari bagian ini (baris ~31-32):**

```cpp
const char* serverUrl = "http://YOUR_LAPTOP_IP:8000/api/monitoring/insert";
```

**Cari IP laptop Anda:**

```powershell
# Di PowerShell atau CMD, jalankan:
ipconfig
```

Cari bagian **"Wireless LAN adapter Wi-Fi"** atau **"Ethernet adapter"**, lalu catat **IPv4 Address**.

Contoh output:
```
IPv4 Address. . . . . . . . . . . : 192.168.1.70
```

**Ganti dengan IP laptop Anda:**

```cpp
const char* serverUrl = "http://192.168.1.70:8000/api/monitoring/insert";
//                            ^^^^^^^^^^^ IP laptop Anda
```

---

## 📤 UPLOAD KE ESP32

### 1. Konfigurasi Board

Di Arduino IDE:
- `Tools > Board > ESP32 Arduino > ESP32 Dev Module`
- `Tools > Port > Pilih COM port ESP32` (contoh: COM3)

### 2. Upload Code

1. Klik tombol **Upload** (panah →)
2. Tunggu proses compile & upload
3. Jika muncul "Connecting...", tekan tombol **BOOT** di ESP32
4. Tunggu hingga "Done uploading"

### 3. Buka Serial Monitor

1. `Tools > Serial Monitor` (Ctrl+Shift+M)
2. Set baud rate: **115200**
3. Lihat output koneksi

**Output yang benar:**

```
========================================
    MONITORING CABAI IoT - ESP32
========================================

[WiFi] Connecting to: NamaWiFiAnda
[WiFi] Connected!
[WiFi] IP Address: 192.168.1.50

─────────────────────────────────────
🌶️  Kelembapan Tanah: 65.2% (NORMAL ✓)
💦  Status Pompa: Mati 🔴
─────────────────────────────────────

[HTTP] Response code: 201
[HTTP] Data berhasil dikirim! ✓
```

---

## 🧪 TEST TANPA HARDWARE (Simulasi)

### Test 1: Insert Data Manual (via PowerShell)

```powershell
# Kirim data dummy ke API:
$body = '{"soil_moisture": 35.5, "status_pompa": "Hidup"}'
curl -X POST "http://localhost:8000/api/monitoring/insert" -H "Content-Type: application/json" -d $body
```

Expected response:
```json
{
  "success": true,
  "message": "Data berhasil disimpan",
  "data": { ... }
}
```

### Test 2: Lihat Dashboard

1. Buka browser: `http://localhost:8000`
2. Dashboard akan auto-refresh setiap 3 detik
3. Data yang Anda kirim via API akan muncul

---

## 🔧 TROUBLESHOOTING

### ❌ Error: "WiFi not connected"

**Penyebab:** SSID atau password salah

**Solusi:**
- Cek SSID dan password WiFi (case-sensitive!)
- Pastikan WiFi 2.4GHz (ESP32 tidak support 5GHz)

---

### ❌ Error: "HTTP Error -1"

**Penyebab:** Tidak dapat connect ke Laravel server

**Solusi:**

1. **Cek Laravel running:**
   ```bash
   php artisan serve
   ```

2. **Cek IP laptop:**
   ```powershell
   ipconfig
   ```
   Pastikan IP di ESP32 code sama dengan IP laptop!

3. **Cek Firewall:**
   Port 8000 harus allowed. Jalankan sebagai Admin:
   ```powershell
   New-NetFirewallRule -DisplayName "Laravel" -Direction Inbound -LocalPort 8000 -Protocol TCP -Action Allow
   ```

4. **Test ping:**
   ```powershell
   # Di laptop, cek IP ESP32 (lihat Serial Monitor)
   ping 192.168.1.50
   ```

---

### ❌ ESP32 tidak terdeteksi di Device Manager

**Solusi:**
- Install driver CP2102 atau CH340
- Ganti kabel USB (gunakan kabel DATA, bukan charge-only!)
- Restart PC setelah install driver

---

### ❌ Dashboard tidak update

**Solusi:**

1. Buka Browser Console (F12)
2. Lihat Network tab
3. Cek apakah ada request ke `/api/monitoring/latest`
4. Jika error CORS, tambahkan di `config/cors.php`:
   ```php
   'allowed_origins' => ['*'],
   ```

---

## 📚 FILE DOKUMENTASI LENGKAP

- 📖 **DOKUMENTASI_CABAI.md** - Panduan lengkap (~600 baris)
- 📖 **INSTALL_ARDUINO.md** - Step-by-step install Arduino IDE
- 📄 **ESP32_CONFIG.txt** - Auto-generated configuration
- 🔧 **setup-esp32.ps1** - Auto-setup script

---

## ✅ CHECKLIST

Pastikan semua ini sudah selesai:

**Backend (Sudah OK!):**
- [x] Laravel server running
- [x] API endpoint configured
- [x] Firewall allowed
- [x] Database migrated

**Frontend (Sudah OK!):**
- [x] Dashboard accessible
- [x] Real-time refresh working

**Hardware (Perlu Action!):**
- [ ] Arduino IDE installed
- [ ] ESP32 board support installed
- [ ] ArduinoJson library installed
- [ ] USB driver installed (jika perlu)
- [ ] ESP32 terdeteksi di Device Manager
- [ ] Code Arduino di-edit (WiFi + Server URL)
- [ ] Code di-upload ke ESP32
- [ ] Sensor soil moisture dipasang (GPIO 34)
- [ ] Relay module dipasang (GPIO 25)
- [ ] Pompa air terhubung ke relay

---

## 🎯 TARGET AKHIR

Setelah semua checklist selesai:

1. ✅ ESP32 baca sensor kelembapan tanah
2. ✅ ESP32 kirim data ke Laravel API setiap 5 detik
3. ✅ Dashboard update real-time (refresh 3 detik)
4. ✅ Pompa otomatis hidup jika kelembapan < 40%
5. ✅ Pompa otomatis mati jika kelembapan sudah cukup
6. ✅ Rekomendasi sistem berubah sesuai kondisi

---

## 📞 SUPPORT

Jika masih ada masalah:
- Baca: `INSTALL_ARDUINO.md` (lebih detail)
- GitHub Issues: https://github.com/PANDORA013/Smart-Garden-IoT/issues

---

**🚀 SELAMAT! Sistem siap untuk monitoring cabai real-time!**
