# 🚀 PANDUAN LENGKAP UPLOAD CODE KE RASPBERRY PI PICO W

# 🚀 PANDUAN LENGKAP UPLOAD CODE KE RASPBERRY PI PICO W

## ⚠️ PENTING - KONFIGURASI DIPERLUKAN!

Sebelum melanjutkan, Anda HARUS mengkonfigurasi WiFi dan server:

**LANGKAH PENTING:**
1. Buka folder `arduino/`
2. Copy file `config.example.h` → buat file baru bernama `config.h`
3. Edit `config.h` dengan detail Anda:
   - WiFi SSID (nama WiFi)
   - WiFi Password
   - Server URL (IP komputer Anda)
   - Device ID (nama unik untuk Pico W)

**Cari IP Address Anda:**
- Windows: Buka PowerShell, ketik `ipconfig`
- Cari "IPv4 Address" contoh: 192.168.1.100

Panduan lengkap: **CONFIGURATION_GUIDE.md**

---

## ✅ SYSTEM REQUIREMENTS:

- ✅ **Server Laravel**: Harus RUNNING di http://0.0.0.0:8000
- ✅ **WiFi**: 2.4GHz network (bukan 5GHz)
- ✅ **Pico W**: Terdeteksi di port USB
- ✅ **Config File**: `config.h` sudah dibuat dan diisi

---

## 📋 LANGKAH 1: INSTALL 3 LIBRARY DI ARDUINO IDE

### A. Buka Library Manager
1. Buka **Arduino IDE**
2. Klik menu: **Tools → Manage Libraries...**
   - Atau: **Sketch → Include Library → Manage Libraries**

### B. Install Library 1: ArduinoJson
1. Ketik di search box: **ArduinoJson**
2. Cari: **"ArduinoJson"** by Benoit Blanchon
3. Klik tombol **INSTALL** (pilih versi latest, biasanya 7.x)
4. Tunggu sampai muncul "INSTALLED"

### C. Install Library 2: DHT sensor library
1. Ketik di search box: **DHT**
2. Cari: **"DHT sensor library"** by Adafruit
3. Klik tombol **INSTALL**
4. ⚠️ **PENTING**: Popup akan muncul: "Would you like to install all the missing dependencies?"
5. ✅ **Klik: INSTALL ALL** (ini akan install Adafruit Unified Sensor juga)
6. Tunggu sampai semua selesai

### D. Install Library 3: NTPClient
1. Ketik di search box: **NTPClient**
2. Cari: **"NTPClient"** by Fabrice Weinberg
3. Klik tombol **INSTALL**
4. Tunggu sampai muncul "INSTALLED"

### E. Close Library Manager
Klik tombol **Close** setelah semua library terinstall

---

## 📂 LANGKAH 2: BUKA FILE ARDUINO

1. **Klik menu:** File → Open
2. **Browse ke folder arduino:**
   ```
   [Lokasi project Anda]/arduino/
   ```
3. **Pilih file:** `pico_smart_gateway.ino`
4. **Klik:** Open

⚠️ **PASTIKAN file `config.h` sudah ada di folder yang sama!**
File ini berisi konfigurasi WiFi dan server Anda.

Jika belum, copy dari `config.example.h` dan edit dengan detail Anda:
```cpp
const char* WIFI_SSID = "NamaWiFiAnda";
const char* WIFI_PASSWORD = "PasswordWiFiAnda";
const char* SERVER_URL = "http://192.168.1.100:8000/api/monitoring/insert";
const char* DEVICE_ID = "PICO_GARDEN_01";
```

---

## 🔌 LANGKAH 3: KONFIGURASI BOARD & PORT

### A. Pilih Board
1. Klik menu: **Tools → Board**
2. Pilih: **Raspberry Pi RP2040 Boards**
3. Pilih: **Raspberry Pi Pico W** (pastikan ada huruf W!)

### B. Pilih Port
1. Klik menu: **Tools → Port**
2. Pilih: **COM8 (USB Serial Device)**

### C. Verifikasi
Cek di bagian bawah Arduino IDE harus muncul:
```
Raspberry Pi Pico W on COM# (your port number)
```

---

## ✅ LANGKAH 4: COMPILE CODE (VERIFY)

1. **Klik tombol centang ✓** (Verify) di toolbar atas
2. Tunggu proses compile (pertama kali ~1-2 menit)
3. Perhatikan output di bagian bawah:

### ✅ Jika Berhasil:
```
Done compiling.
Sketch uses XXXXX bytes (X%) of program storage space.
Global variables use XXXXX bytes (X%) of dynamic memory.
```

### ❌ Jika Ada Error:
- **Error: WiFi.h not found** → Board Pico W belum dipilih dengan benar
- **Error: ArduinoJson.h not found** → Library belum terinstall
- **Error: DHT.h not found** → DHT library belum terinstall
- **Error: NTPClient.h not found** → NTPClient library belum terinstall
- **Error: config.h not found** → File config.h belum dibuat dari config.example.h

Jika ada error, screenshot dan cek panduan troubleshooting.

---

## 🚀 LANGKAH 5: UPLOAD KE PICO W

### A. Upload Normal
1. **Pastikan Pico W masih tercolok di USB**
2. **Klik tombol panah → (Upload)** di toolbar atas
3. Tunggu proses upload (~30-60 detik)
4. Perhatikan output di bagian bawah

### B. Jika Upload Berhasil:
```
Done uploading.
```

### C. Jika Upload Gagal "Failed to connect to RP2040":

**Cara 1: Upload Mode BOOTSEL**
1. **Cabut** Pico W dari USB
2. **Tekan dan tahan** tombol putih **BOOTSEL** di Pico W
3. Sambil tahan BOOTSEL, **colok** USB ke laptop
4. **Lepas** tombol BOOTSEL
5. Pico W akan muncul sebagai **USB Drive** (RPI-RP2)
6. **Upload lagi** dari Arduino IDE (klik →)

**Cara 2: Ganti USB Cable**
- Gunakan kabel USB data (bukan kabel charging only)

---

## 📡 LANGKAH 6: MONITOR SERIAL OUTPUT

### A. Buka Serial Monitor
1. Klik menu: **Tools → Serial Monitor**
   - Atau tekan: **Ctrl + Shift + M**

### B. Set Baud Rate
1. Di bagian **bawah kanan** Serial Monitor
2. Pilih dropdown: **115200 baud**

### C. Output yang Benar:

Anda akan melihat output seperti ini:

```
========================================
🌱 PICO W SMART GARDEN GATEWAY
========================================
🔌 Connecting to WiFi: Bocil
.....
✅ WiFi Connected!
📡 IP Address: 192.168.18.123
✅ Setup Complete!
========================================

📤 Sending data to server...
{"device_id":"PICO_CABAI_01","temperature":28.5,"soil_moisture":45.2,"raw_adc":2800,"relay_status":false,"ip_address":"192.168.18.123"}
✅ Server Response Code: 201
📥 Server Response:
{"message":"Data inserted successfully","device":"PICO_CABAI_01","config":{"mode":1,"adc_min":4095,"adc_max":1500,"batas_kering":40,"batas_basah":70}}
ℹ️ Tidak ada perubahan konfigurasi
```

### D. Troubleshooting Serial Monitor:

**❌ Jika muncul: "WiFi Connection Failed!"**
- Password atau SSID WiFi salah (cek di config.h)
- WiFi tidak aktif
- Jarak terlalu jauh dari router
- WiFi 5GHz (Pico W hanya support 2.4GHz)

**❌ Jika muncul: "HTTP Error: -1"**
- Server Laravel tidak running (jalankan: php artisan serve --host=0.0.0.0)
- IP address salah (cek dengan: ipconfig di PowerShell, update config.h)
- Firewall memblokir port 8000

**❌ Jika muncul: "DHT22 Error!"**
- Sensor DHT22 belum dicolok
- Pin DHT22 salah (harus di GPIO2)
- Sensor DHT22 rusak
- (Tidak masalah untuk testing, akan pakai nilai default 28.0°C)

---

## 🌐 LANGKAH 7: CEK DASHBOARD

### A. Buka Browser
Buka salah satu URL ini di browser:
- **Chrome/Edge/Firefox**: http://127.0.0.1:8000
- Atau: http://localhost:8000

### B. Yang Akan Anda Lihat:

✅ **Dashboard Smart Garden IoT**
- Device "PICO_CABAI_01" muncul di list
- Data sensor update setiap 10 detik
- Temperature: ~28.5°C
- Soil Moisture: ~45%
- Relay Status: OFF
- Last Update: (timestamp)

✅ **Grafik Real-time**
- Chart temperature dan soil moisture

✅ **Control Panel**
- Switch mode operasi
- Manual pump control
- Settings threshold

---

## 🎯 CHECKLIST AKHIR - PASTIKAN SEMUA INI OK:

```
✅ Arduino IDE installed
✅ Board "Raspberry Pi Pico/RP2040" installed
✅ 3 Libraries installed (ArduinoJson, DHT, NTPClient)
✅ File config.h created from config.example.h
✅ Config.h berisi WiFi dan Server details yang benar
✅ File pico_smart_gateway.ino opened
✅ Board selected: Raspberry Pi Pico W
✅ Port selected: COM# (your port)
✅ Code compiled successfully (Verify ✓)
✅ Code uploaded successfully (Upload →)
✅ Serial Monitor opened (115200 baud)
✅ WiFi connected (check SSID in serial output)
✅ Data sent to server (Response Code: 201)
✅ Dashboard showing your device
✅ Real-time data updating every 10 seconds
```

---

## 🆘 TROUBLESHOOTING UMUM

### Problem 1: "Missing FQBN" or "config.h not found"
**Solusi:** 
- Board belum dipilih: Tools → Board → Raspberry Pi Pico W
- File config.h belum dibuat: Copy config.example.h → config.h

### Problem 2: "WiFi.h not found"
**Solusi:** Board Pico W belum dipilih dengan benar, atau package RP2040 belum terinstall

### Problem 3: Upload Failed
**Solusi:** Gunakan mode BOOTSEL (tekan tombol BOOTSEL sambil colok USB)

### Problem 4: WiFi tidak connect
**Solusi:** 
- Cek SSID dan password di config.h (case-sensitive!)
- Pastikan WiFi 2.4GHz (bukan 5GHz)
- Dekatkan Pico W ke router
- Pastikan router tidak membatasi koneksi perangkat baru

### Problem 5: HTTP Error -1
**Solusi:**
- Restart server Laravel: `php artisan serve --host=0.0.0.0 --port=8000`
- Cek IP dengan: `ipconfig` di PowerShell
- Update IP di config.h jika IP berubah
- Test server di browser: http://YOUR_IP:8000

### Problem 6: Server tidak muncul data
**Solusi:**
- Cek Serial Monitor untuk error
- Pastikan Response Code: 201 (bukan 404 atau 500)
- Refresh dashboard (F5)

---

## 📞 PANDUAN TAMBAHAN

Untuk panduan lebih detail:
- **CONFIGURATION_GUIDE.md** - Panduan konfigurasi lengkap (English)
- **PICO_CONFIGURATION_CHECKLIST.md** - Checklist step-by-step
- **arduino/README.md** - Quick reference Arduino
- **micropython/README.md** - Quick reference MicroPython

---

## 🎉 SELAMAT!

Jika semua checklist di atas sudah ✅, maka sistem Smart Garden IoT Anda sudah **BERHASIL BERJALAN**!

Device Raspberry Pi Pico W akan:
- ✅ Membaca sensor setiap detik
- ✅ Mengirim data ke server setiap 10 detik
- ✅ Menerima konfigurasi dari server (2-way communication)
- ✅ Mengontrol pompa berdasarkan mode yang dipilih
- ✅ Menampilkan data real-time di dashboard

**Next Steps:**
- Hubungkan sensor DHT22 ke GPIO2
- Hubungkan sensor kelembaban tanah ke ADC (GPIO26)
- Hubungkan relay pompa ke GPIO5
- Test semua mode operasi (Basic, Fuzzy, Manual)

---

**Created:** January 10, 2026
**Project:** Smart Garden IoT System
**Hardware:** Raspberry Pi Pico W
**Software:** Laravel 12.x + Arduino IDE
