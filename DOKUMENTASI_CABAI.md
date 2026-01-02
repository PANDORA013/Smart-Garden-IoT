# 🌶️ MONITORING CABAI IoT - DOKUMENTASI LENGKAP

## 📖 DAFTAR ISI
1. [Overview](#overview)
2. [Perubahan dari Smart Garden](#perubahan)
3. [Fitur yang Dihapus](#fitur-dihapus)
4. [Arsitektur System](#arsitektur)
5. [Hardware Requirements](#hardware)
6. [Software Requirements](#software)
7. [Instalasi Backend (Laravel)](#instalasi-backend)
8. [Instalasi Frontend (React)](#instalasi-frontend)
9. [Setup ESP32/Arduino](#setup-esp32)
10. [API Endpoints](#api-endpoints)
11. [Testing](#testing)
12. [Troubleshooting](#troubleshooting)

---

## 🎯 OVERVIEW <a name="overview"></a>

Project ini adalah **versi simplified** dari Smart Garden IoT yang **HANYA** fokus pada monitoring **Kelembapan Tanah** dan **Kontrol Pompa** untuk tanaman **Cabai**.

### Fitur Utama:
- 🌊 **Monitoring Kelembapan Tanah** (Soil Moisture)
- 💧 **Kontrol Pompa Otomatis** (Auto ON jika < 40%, OFF jika cukup)
- 📊 **Dashboard Real-time** (Update setiap 3 detik)
- 🤖 **Sistem Rekomendasi** berbasis threshold
- 📡 **API REST** untuk komunikasi ESP32 ↔ Server

---

## 🔄 PERUBAHAN DARI SMART GARDEN <a name="perubahan"></a>

| Sebelum (Smart Garden) | Sesudah (Cabai Monitoring) |
|------------------------|----------------------------|
| 4 Metric Cards (Kelembapan, Kegemburan, Level Air, Daya) | **2 Metric Cards** (Kelembapan, Status Pompa) |
| Mode Auto/Manual Toggle | **Auto-only** (pompa dikontrol ESP32) |
| Timer scheduling | ❌ Dihapus |
| Water tank visualization | ❌ Dihapus |
| Sensor simulation | ✅ **Data real dari ESP32** |
| 189 baris code | **~220 baris** (lebih fokus) |

---

## ❌ FITUR YANG DIHAPUS <a name="fitur-dihapus"></a>

### Frontend (React):
- ❌ Kegemburan Tanah (Soil Friability)
- ❌ Level Tangki Air (Water Level)
- ❌ Konsumsi Daya (Power Usage)
- ❌ Toggle Auto/Manual
- ❌ Timer Settings (jadwal + durasi)
- ❌ Water Tank Visualization
- ❌ Voltage indicator

### Backend (Laravel):
- ❌ Field `soilFriability`
- ❌ Field `waterLevel`
- ❌ Field `powerUsage`
- ❌ Field `voltage`
- ✅ **Hanya 2 field:** `soil_moisture` + `status_pompa`

### Hardware (ESP32):
- ❌ Sensor Ultrasonik (HC-SR04)
- ❌ Sensor Kegemburan
- ❌ Sensor Daya/Tegangan
- ✅ **Hanya:** Soil Moisture Sensor + Relay

---

## 🏗️ ARSITEKTUR SYSTEM <a name="arsitektur"></a>

```
┌─────────────────────────────────────────────────┐
│         MONITORING CABAI IoT SYSTEM              │
├─────────────────────────────────────────────────┤
│                                                   │
│  ┌──────────────┐    WiFi     ┌──────────────┐  │
│  │    ESP32     │◄──────────►│   LARAVEL    │  │
│  │ + Soil Sensor│             │   SERVER     │  │
│  │ + Relay      │             │  (API REST)  │  │
│  └──────────────┘             └──────────────┘  │
│         │                            │           │
│         │ GPIO                       │ Axios     │
│         ▼                            ▼           │
│  ┌──────────────┐             ┌──────────────┐  │
│  │   POMPA AIR  │             │   REACT UI   │  │
│  │   (12V DC)   │             │  DASHBOARD   │  │
│  └──────────────┘             └──────────────┘  │
│                                       ▲           │
│                                       │           │
│                                   Browser         │
└─────────────────────────────────────────────────┘
```

### Flow Data:
1. **ESP32** → Baca Soil Moisture Sensor (0-100%)
2. **ESP32** → Logika: `if (moisture < 40%) → Pompa ON`
3. **ESP32** → Kirim data ke Laravel API via HTTP POST
4. **Laravel** → Simpan ke database SQLite
5. **React** → Fetch data via API setiap 3 detik
6. **Dashboard** → Tampilkan real-time + rekomendasi

---

## 🛠️ HARDWARE REQUIREMENTS <a name="hardware"></a>

### Komponen Utama:

| No | Komponen | Spesifikasi | Harga Estimasi |
|----|----------|-------------|----------------|
| 1 | ESP32 Dev Board | 30 GPIO, WiFi, Bluetooth | Rp 50.000 |
| 2 | Soil Moisture Sensor | Analog output (0-4095) | Rp 15.000 |
| 3 | Relay Module 1 Channel | 5V/12V, Active High/Low | Rp 10.000 |
| 4 | Pompa Air DC | 12V 1A | Rp 30.000 |
| 5 | Power Supply 12V | Min 2A untuk pompa | Rp 25.000 |
| 6 | Kabel Jumper | Male-Female, Male-Male | Rp 10.000 |
| 7 | Breadboard | Optional (untuk prototype) | Rp 15.000 |
| **TOTAL** | | | **~Rp 155.000** |

### Wiring Diagram:

```
ESP32                Soil Moisture Sensor
GPIO 34 ────────────► Analog Out (AO)
3.3V    ────────────► VCC
GND     ────────────► GND

ESP32                Relay Module
GPIO 25 ────────────► IN (Signal)
5V/VIN  ────────────► VCC
GND     ────────────► GND

Relay                Pompa Air 12V
COM     ────────────► Power Supply (+)
NO      ────────────► Pompa (+)
Pompa (-) ──────────► Power Supply (-)
```

⚠️ **CATATAN PENTING:**
- Jangan hubungkan pompa langsung ke ESP32 (max 40mA per pin)!
- Gunakan power supply terpisah untuk pompa
- Cek datasheet relay: Active HIGH atau Active LOW

---

## 💻 SOFTWARE REQUIREMENTS <a name="software"></a>

### Laptop/PC:
- ✅ PHP >= 8.2
- ✅ Composer
- ✅ Node.js >= 18.x
- ✅ Laravel 12
- ✅ SQLite extension
- ✅ Git

### ESP32:
- ✅ Arduino IDE 2.x
- ✅ ESP32 Board Manager
- ✅ Library: ArduinoJson

### Browser:
- ✅ Chrome/Firefox/Edge (modern browser)

---

## 🚀 INSTALASI BACKEND (Laravel) <a name="instalasi-backend"></a>

### 1. Clone Repository
```bash
git clone https://github.com/PANDORA013/Smart-Garden-IoT.git
cd Smart-Garden-IoT
```

### 2. Install Dependencies
```bash
composer install
```

### 3. Setup Environment
```bash
cp .env.example .env
php artisan key:generate
```

### 4. Migrate Database
```bash
php artisan migrate:fresh
```

Output yang benar:
```
✓ 2025_11_25_131119_create_sessions_table .... DONE
✓ 2026_01_02_000001_create_monitorings_table .. DONE
```

### 5. Run Server
```bash
php artisan serve
```

Server akan berjalan di: `http://127.0.0.1:8000`

### 6. Test API (Optional)
```bash
# Test dengan curl/Postman:
curl -X POST http://127.0.0.1:8000/api/monitoring/insert \
  -H "Content-Type: application/json" \
  -d '{"soil_moisture": 35.5, "status_pompa": "Hidup"}'
```

Response sukses:
```json
{
  "success": true,
  "message": "Data berhasil disimpan",
  "data": {
    "id": 1,
    "soil_moisture": 35.5,
    "status_pompa": "Hidup",
    "created_at": "2026-01-02T10:30:00.000000Z"
  }
}
```

---

## 🎨 INSTALASI FRONTEND (React) <a name="instalasi-frontend"></a>

### 1. Install Node Dependencies
```bash
npm install
```

### 2. Build Assets
```bash
# Development (with hot reload):
npm run dev

# Production (optimized):
npm run build
```

### 3. Akses Dashboard
Buka browser: `http://localhost:8000`

Dashboard akan menampilkan:
- 🌶️ Header "Monitoring Cabai IoT"
- 📊 Metric Card: Kelembapan Tanah
- 🔌 Status Pompa (Hidup/Mati)
- 💡 Rekomendasi real-time

---

## 🔧 SETUP ESP32/ARDUINO <a name="setup-esp32"></a>

### 1. Install Arduino IDE
Download: https://arduino.cc/en/software

### 2. Install ESP32 Board
1. Buka: `File > Preferences`
2. Additional Boards Manager URLs:
   ```
   https://dl.espressif.com/dl/package_esp32_index.json
   ```
3. `Tools > Board > Boards Manager`
4. Search: `esp32` → Install

### 3. Install Library ArduinoJson
1. `Sketch > Include Library > Manage Libraries`
2. Search: `ArduinoJson`
3. Install versi latest (by Benoit Blanchon)

### 4. Edit Konfigurasi
Buka file: `arduino/cabai_monitoring_esp32.ino`

```cpp
// GANTI INI:
const char* ssid = "YOUR_WIFI_SSID";        // Nama WiFi Anda
const char* password = "YOUR_WIFI_PASSWORD"; // Password WiFi
const char* serverUrl = "http://192.168.1.100:8000/api/monitoring/insert";
                        // ^^^^^^^^^^^ Ganti dengan IP laptop Anda
```

**Cara cek IP laptop:**
- Windows: `ipconfig` (cari IPv4 Address)
- Mac/Linux: `ifconfig` atau `ip addr`

### 5. Kalibrasi Sensor
```cpp
// Di bagian ini:
const int SENSOR_MIN = 4095;  // Nilai saat KERING (di udara)
const int SENSOR_MAX = 1500;  // Nilai saat BASAH (dicelupkan air)
```

Cara kalibrasi:
1. Upload code dengan Serial Monitor aktif (115200 baud)
2. Lihat nilai `analogRead()` saat sensor di udara → set SENSOR_MIN
3. Celupkan sensor ke air → catat nilai → set SENSOR_MAX
4. Upload ulang code

### 6. Upload Code
1. `Tools > Board > ESP32 Arduino > ESP32 Dev Module`
2. `Tools > Port > Pilih COM port ESP32`
3. Klik tombol "Upload" (→)
4. Tunggu: "Done uploading"

### 7. Monitor Serial
`Tools > Serial Monitor` (Ctrl+Shift+M)

Output normal:
```
========================================
    MONITORING CABAI IoT - ESP32
========================================

[WiFi] Connected!
[WiFi] IP Address: 192.168.1.50

─────────────────────────────────────
🌶️  Kelembapan Tanah: 35.2% (KERING ⚠️)
💦  Status Pompa: Hidup 🟢
─────────────────────────────────────

[HTTP] Response code: 201
[HTTP] Data berhasil dikirim! ✓
```

---

## 📡 API ENDPOINTS <a name="api-endpoints"></a>

### 1. Insert Data (dari ESP32)
```http
POST /api/monitoring/insert
Content-Type: application/json

{
  "soil_moisture": 35.5,
  "status_pompa": "Hidup"
}
```

Response:
```json
{
  "success": true,
  "message": "Data berhasil disimpan",
  "data": { ... }
}
```

### 2. Get Latest Data (untuk Dashboard)
```http
GET /api/monitoring/latest
```

Response:
```json
{
  "success": true,
  "data": {
    "id": 10,
    "soil_moisture": 65.3,
    "status_pompa": "Mati",
    "created_at": "2026-01-02T10:30:00.000000Z"
  }
}
```

### 3. Get History (untuk Chart)
```http
GET /api/monitoring/history?limit=50
```

Response:
```json
{
  "success": true,
  "count": 50,
  "data": [ ... ]
}
```

### 4. Cleanup Old Data
```http
DELETE /api/monitoring/cleanup?days=7
```

Response:
```json
{
  "success": true,
  "message": "Berhasil menghapus 150 data lama (> 7 hari)",
  "deleted_count": 150
}
```

---

## 🧪 TESTING <a name="testing"></a>

### Test 1: Backend API (tanpa hardware)
```bash
# Test insert:
curl -X POST http://localhost:8000/api/monitoring/insert \
  -H "Content-Type: application/json" \
  -d '{"soil_moisture": 45, "status_pompa": "Mati"}'

# Test latest:
curl http://localhost:8000/api/monitoring/latest

# Test history:
curl "http://localhost:8000/api/monitoring/history?limit=10"
```

### Test 2: Dashboard (simulasi)
1. Buka: `http://localhost:8000`
2. Insert data manual via API (Test 1)
3. Tunggu 3 detik → Dashboard auto-refresh
4. Cek apakah data muncul

### Test 3: ESP32 + Hardware
1. Upload code ke ESP32
2. Buka Serial Monitor (115200 baud)
3. Lihat output koneksi WiFi
4. Cek response HTTP (harus 201)
5. Celupkan sensor ke air → Kelembapan naik → Pompa mati
6. Keringkan sensor → Kelembapan turun < 40% → Pompa hidup

---

## 🐛 TROUBLESHOOTING <a name="troubleshooting"></a>

### ❌ Error: "WiFi not connected"
**Penyebab:**
- SSID/password salah
- ESP32 jauh dari router

**Solusi:**
```cpp
// Cek SSID & password:
const char* ssid = "YOUR_WIFI_SSID";  // HARUS BENAR!
const char* password = "YOUR_WIFI_PASSWORD";
```

### ❌ Error: "HTTP Error -1"
**Penyebab:**
- Laravel server tidak running
- IP address salah
- Firewall block port 8000

**Solusi:**
1. Pastikan Laravel running: `php artisan serve`
2. Cek IP laptop: `ipconfig` (Windows) / `ifconfig` (Mac)
3. Nonaktifkan firewall sementara:
   - Windows: `Windows Defender Firewall > Allow an app > PHP`

### ❌ Kelembapan selalu 0% atau 100%
**Penyebab:**
- Sensor tidak dikalibrasi

**Solusi:**
```cpp
// Kalibrasi ulang:
const int SENSOR_MIN = 4095;  // Ganti dengan nilai di udara
const int SENSOR_MAX = 1500;  // Ganti dengan nilai di air
```

Cara:
1. Upload code
2. Buka Serial Monitor
3. Lihat nilai raw `analogRead()` saat sensor di udara → set SENSOR_MIN
4. Celupkan sensor ke air → catat nilai → set SENSOR_MAX

### ❌ Pompa tidak nyala
**Penyebab:**
- Relay wiring salah
- Relay active-low (terbalik logika)
- Power supply pompa tidak connect

**Solusi:**
1. Cek wiring relay:
   ```
   ESP32 GPIO 25 → Relay IN
   ESP32 5V → Relay VCC
   ESP32 GND → Relay GND
   ```
2. Jika relay active-LOW, tukar HIGH/LOW:
   ```cpp
   void controlPump() {
     if (soilMoisture < MOISTURE_THRESHOLD) {
       digitalWrite(RELAY_PIN, LOW);  // Ubah jadi LOW
       statusPompa = "Hidup";
     } else {
       digitalWrite(RELAY_PIN, HIGH); // Ubah jadi HIGH
       statusPompa = "Mati";
     }
   }
   ```

### ❌ Dashboard tidak update
**Penyebab:**
- Axios tidak fetch data
- CORS issue

**Solusi:**
1. Buka Browser Console (F12)
2. Cek error network
3. Pastikan endpoint benar: `/api/monitoring/latest`

### ❌ Database error: "no such table"
**Solusi:**
```bash
php artisan migrate:fresh
```

---

## 📚 REFERENSI

### Video Tutorial:
1. **ESP32 + Soil Moisture + Relay Control:**
   https://www.youtube.com/watch?v=mhLo4pFCW0w

### Dokumentasi:
- ESP32 Datasheet: https://espressif.com/en/products/socs/esp32
- Laravel API: https://laravel.com/docs/12.x
- React Hooks: https://react.dev/reference/react

---

## 📞 SUPPORT

**Developer:** PANDORA013  
**Repository:** https://github.com/PANDORA013/Smart-Garden-IoT  
**Issues:** https://github.com/PANDORA013/Smart-Garden-IoT/issues

---

**✅ PROJECT SIAP DIGUNAKAN!**

Jika ada pertanyaan, buka issue di GitHub atau kirim email.
