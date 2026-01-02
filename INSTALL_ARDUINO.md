# 📥 PANDUAN INSTALL ARDUINO IDE + ESP32 SUPPORT

## 🎯 LANGKAH 1: Download Arduino IDE

1. **Buka browser** dan kunjungi:
   ```
   https://www.arduino.cc/en/software
   ```

2. **Pilih versi:**
   - **Windows:** Win 10 and newer, 64 bits
   - Download size: ~150 MB

3. **Install:**
   - Jalankan file installer (`.exe`)
   - Ikuti wizard install (Next > Next > Install)
   - Tunggu hingga selesai (~5 menit)

---

## 🔌 LANGKAH 2: Install ESP32 Board Support

### A. Tambahkan Board Manager URL

1. **Buka Arduino IDE**
2. **Menu:** `File > Preferences` (atau tekan `Ctrl+,`)
3. **Cari:** "Additional boards manager URLs"
4. **Paste URL ini:**
   ```
   https://espressif.github.io/arduino-esp32/package_esp32_index.json
   ```
5. **Klik:** OK

### B. Install ESP32 Board Package

1. **Menu:** `Tools > Board > Boards Manager`
2. **Search:** `esp32`
3. **Install:** "esp32 by Espressif Systems" (versi latest, ~300 MB)
4. **Tunggu download selesai** (~10-15 menit tergantung internet)

---

## 📚 LANGKAH 3: Install Library yang Dibutuhkan

### Library 1: ArduinoJson

1. **Menu:** `Sketch > Include Library > Manage Libraries`
2. **Search:** `ArduinoJson`
3. **Install:** "ArduinoJson" by Benoit Blanchon (pilih versi 6.x atau 7.x)
4. **Klik:** Install

### Library 2: HTTPClient (Sudah Include di ESP32)
✅ Tidak perlu install terpisah, sudah bundled dengan ESP32 core

### Library 3: WiFi (Sudah Include di ESP32)
✅ Tidak perlu install terpisah, sudah bundled dengan ESP32 core

---

## 🔍 LANGKAH 4: Deteksi ESP32 di Windows

### A. Install Driver USB (Jika ESP32 tidak terdeteksi)

ESP32 menggunakan salah satu dari chip USB berikut:
- **CP2102** (Silicon Labs)
- **CH340** (WCH)
- **FTDI**

**Download Driver:**
1. **CP2102 Driver:**
   - Link: https://www.silabs.com/developers/usb-to-uart-bridge-vcp-drivers
   - Pilih: "CP210x Windows Drivers"

2. **CH340 Driver:**
   - Link: https://sparks.gogo.co.nz/ch340.html
   - Download: CH341SER.EXE

**Install Driver:**
1. Download file driver
2. Jalankan installer
3. Restart komputer
4. Colokkan ESP32 ke USB

### B. Cek COM Port

1. **Buka:** Device Manager (`Win+X` > Device Manager)
2. **Expand:** "Ports (COM & LPT)"
3. **Cari:** "USB-SERIAL CH340 (COMx)" atau "Silicon Labs CP210x (COMx)"
4. **Catat nomor COM:** Contoh: COM3, COM4, dll

---

## ⚙️ LANGKAH 5: Konfigurasi Arduino IDE untuk ESP32

1. **Menu:** `Tools > Board > ESP32 Arduino`
2. **Pilih:** "ESP32 Dev Module"
3. **Menu:** `Tools > Port`
4. **Pilih:** COM port ESP32 (contoh: COM3)

### Pengaturan Advanced (Optional):

| Setting | Nilai |
|---------|-------|
| Upload Speed | 921600 |
| CPU Frequency | 240MHz (WiFi/BT) |
| Flash Frequency | 80MHz |
| Flash Mode | QIO |
| Flash Size | 4MB (32Mb) |
| Partition Scheme | Default 4MB with spiffs |

---

## 🧪 LANGKAH 6: Test Koneksi ESP32

### Test 1: Blink LED (Test Upload)

1. **Menu:** `File > Examples > 01.Basics > Blink`
2. **Klik:** Upload (tombol panah →)
3. **Tunggu:** "Done uploading"
4. **Hasil:** LED built-in ESP32 berkedip

Jika berhasil → ESP32 sudah terdeteksi! ✅

### Test 2: Serial Monitor

1. **Upload** kode Blink di atas
2. **Menu:** `Tools > Serial Monitor` (Ctrl+Shift+M)
3. **Set Baud Rate:** 115200
4. **Hasil:** Muncul output text

---

## 🌐 LANGKAH 7: Test WiFi Connection

Buat sketch baru dengan kode ini:

```cpp
#include <WiFi.h>

const char* ssid = "NAMA_WIFI_ANDA";
const char* password = "PASSWORD_WIFI_ANDA";

void setup() {
  Serial.begin(115200);
  delay(1000);
  
  Serial.println("\nConnecting to WiFi...");
  WiFi.begin(ssid, password);
  
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  
  Serial.println("\nWiFi Connected!");
  Serial.print("IP Address: ");
  Serial.println(WiFi.localIP());
}

void loop() {
  // Nothing here
}
```

**Upload dan lihat Serial Monitor:**
- Jika muncul IP address → WiFi berhasil! ✅

---

## 🚀 LANGKAH 8: Upload Code Monitoring Cabai

1. **Buka file:** `arduino/cabai_monitoring_esp32.ino`
2. **Edit WiFi config:**
   ```cpp
   const char* ssid = "WIFI_ANDA";
   const char* password = "PASSWORD_WIFI";
   ```
3. **Cek IP Laptop:** (jalankan `ipconfig` di CMD)
4. **Edit Server URL:**
   ```cpp
   const char* serverUrl = "http://192.168.1.X:8000/api/monitoring/insert";
   ```
5. **Upload ke ESP32**
6. **Buka Serial Monitor** (115200 baud)

---

## 🐛 TROUBLESHOOTING

### ❌ Error: "Board not found"
**Solusi:**
- Install ESP32 board support (Langkah 2)
- Restart Arduino IDE

### ❌ Error: "Port not found"
**Solusi:**
- Install driver USB (CP2102/CH340)
- Cek Device Manager
- Ganti kabel USB (gunakan kabel data, bukan kabel charge only)

### ❌ Error: "Upload failed"
**Solusi:**
- Tekan tombol BOOT di ESP32 saat upload
- Ganti Upload Speed jadi 115200
- Restart ESP32 (cabut-colok USB)

### ❌ Error: "WiFi not connected"
**Solusi:**
- Cek SSID dan password (case-sensitive!)
- Pastikan ESP32 dalam jangkauan WiFi
- Gunakan WiFi 2.4GHz (bukan 5GHz)

### ❌ Error: "HTTP Error -1" atau "Connection refused"
**Solusi:**
- Pastikan Laravel server running: `php artisan serve`
- Cek IP laptop: `ipconfig` (Windows) / `ifconfig` (Mac/Linux)
- Cek firewall: Allow port 8000
  ```powershell
  # Windows: Allow port 8000
  New-NetFirewallRule -DisplayName "Laravel Dev Server" -Direction Inbound -LocalPort 8000 -Protocol TCP -Action Allow
  ```

---

## ✅ CHECKLIST INSTALL

- [ ] Arduino IDE installed
- [ ] ESP32 board support installed
- [ ] ArduinoJson library installed
- [ ] USB Driver installed (CP2102/CH340)
- [ ] ESP32 terdeteksi di Device Manager
- [ ] Test Blink berhasil
- [ ] Test WiFi connection berhasil
- [ ] Code monitoring cabai ter-upload
- [ ] Serial Monitor menampilkan data sensor
- [ ] Laravel server running (php artisan serve)
- [ ] Dashboard menampilkan data real-time

---

## 📞 SUPPORT

Jika masih ada masalah:
1. Screenshot error message
2. Buka issue di GitHub: https://github.com/PANDORA013/Smart-Garden-IoT/issues
3. Sertakan:
   - Tipe ESP32 board
   - Versi Arduino IDE
   - Screenshot Serial Monitor
   - Screenshot error

---

**Setelah semua checklist selesai, sistem akan fully operational! 🚀**
