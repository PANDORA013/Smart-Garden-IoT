# 🌱 Universal IoT Dashboard

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-12.x-red?style=for-the-badge&logo=laravel" alt="Laravel">
  <img src="http## 📖 Dokumentasi

| File | Keterangan |
|------|------------|
| [DOKUMENTASI_AUTO_PROVISIONING.md](./DOKUMENTASI_AUTO_PROVISIONING.md) | ⭐ Plug & Play Auto-Provisioning (Complete Guide) |
| [INSTALL_ARDUINO.md](./INSTALL_ARDUINO.md) | Panduan install Arduino IDE + ESP32 |
| [QUICK_START.md](./QUICK_START.md) | Quick start guide |hields.io/badge/React-19.x-blue?style=for-the-badge&logo=react" alt="React">
  <img src="https://img.shields.io/badge/ESP32-Arduino-green?style=for-the-badge&logo=arduino" alt="ESP32">
  <img src="https://img.shields.io/badge/License-MIT-yellow?style=for-the-badge" alt="License">
</p>

> **Universal IoT Dashboard** adalah platform monitoring dan kontrol IoT real-time berbasis web yang dapat digunakan untuk berbagai jenis sensor dan aktuator. Mendukung ESP32/Arduino dengan komunikasi HTTP REST API.

---

## ✨ Features

### 🎯 Dashboard Real-time
- **Multi-sensor monitoring**: Temperature (DHT22), Humidity, Soil Moisture
- **Auto-refresh**: Update data setiap 3 detik
- **Live chart**: Grafik real-time untuk visualisasi data sensor
- **Manual control**: Toggle relay/pompa dari dashboard
- **System uptime**: Monitoring waktu operasional sistem

### � **NEW! Auto-Provisioning (Plug & Play)**
- **Zero-config setup**: Arduino baru langsung kerja tanpa setup manual
- **Dynamic configuration**: Ubah setting dari dashboard tanpa upload ulang code
- **Multi-device support**: Manage banyak alat dengan ID unik
- **Plant presets**: Default untuk cabai, tomat, dll (easy switch)
- **Remote control**: Update threshold real-time dari web

### �📊 Advanced Features
- **Activity Logs**: Riwayat aktivitas dengan filter dan timestamp
- **Device Management**: List perangkat IoT yang terhubung
- **API Documentation**: Built-in API reference
- **Responsive Design**: Mobile-friendly dengan Tailwind CSS
- **Backward Compatible**: Support legacy Cabai monitoring format

---

## 🚀 Quick Start

### Prerequisites
- PHP 8.4+ dengan Composer
- Node.js 18+ dengan NPM
- Arduino IDE 2.x (untuk ESP32 development)
- ESP32 Dev Module + Sensors

### Installation

1. **Clone Repository**
   ```bash
   git clone https://github.com/PANDORA013/Smart-Garden-IoT.git
   cd Smart-Garden-IoT
   ```

2. **Install Dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Setup Database**
   ```bash
   php artisan migrate
   ```

4. **Run Development Server**
   ```bash
   php artisan serve
   ```

5. **Access Dashboard**
   ```
   http://localhost:8000/
   ```

---

## 📱 Akses Dashboard

Dashboard utama tersedia di: **`http://localhost:8000/`**

---

## 🔌 API Endpoints

Base URL: `http://localhost:8000/api/monitoring`

| Method | Endpoint | Keterangan |
|--------|----------|------------|
| POST | `/insert` | Insert data dari ESP32 |
| GET | `/latest` | Ambil data terbaru |
| GET | `/stats` | Ambil statistik sistem |
| GET | `/history?limit=50` | Ambil riwayat data |
| GET | `/logs?limit=20` | Ambil activity logs |
| POST | `/relay/toggle` | Toggle relay manual |
| DELETE | `/cleanup?days=7` | Hapus data lama |

**Dokumentasi lengkap API:** [DOKUMENTASI_AUTO_PROVISIONING.md](./DOKUMENTASI_AUTO_PROVISIONING.md)

---

## 🧪 Testing Dashboard (Tanpa Hardware)

Jalankan script PowerShell untuk testing auto-provisioning:

```powershell
.\test-auto-provisioning.ps1
```

Script akan:
- Simulasi 3 Arduino check-in (CABAI_01, CABAI_02, TOMAT_01)
- Test device registration & config loading
- Test preset switching (Cabai → Tomat)
- Menampilkan hasil testing semua API endpoints

Setelah itu buka browser dan lihat dashboard ter-update!

---

## 🔧 Hardware Setup (ESP32)

### Wiring Diagram

| Sensor/Aktuator | ESP32 Pin | Keterangan |
|-----------------|-----------|------------|
| DHT22 Data | GPIO 4 | Temperature & Humidity |
| Soil Moisture Analog | GPIO 34 | Kelembaban tanah |
| Relay IN | GPIO 25 | Kontrol relay/pompa |
| LED Status | GPIO 2 | Built-in LED |

### Arduino Code (Auto-Provisioning) ⭐ **RECOMMENDED**

File: `arduino/auto_provisioning_esp32.ino`

**Install Library Arduino:**
1. DHT sensor library (by Adafruit)
2. ArduinoJson (by Benoit Blanchon)
3. WiFi (built-in ESP32)
4. HTTPClient (built-in ESP32)

**Panduan lengkap:** [INSTALL_ARDUINO.md](./INSTALL_ARDUINO.md)

---

## � **NEW! Auto-Provisioning Setup (Plug & Play)**

### Super Simple 3-Step Setup:

**File:** `arduino/auto_provisioning_esp32.ino` ⭐ **RECOMMENDED**

1. **Edit Device ID** (must be unique):
   ```cpp
   const char* DEVICE_ID = "CABAI_01";  // Change: CABAI_02, TOMAT_01, etc
   ```

2. **Edit WiFi & Server**:
   ```cpp
   const char* ssid = "YOUR_WIFI";
   const char* password = "YOUR_PASSWORD";
   const char* SERVER_IP = "192.168.1.70";  // Your laptop IP
   ```

3. **Upload & Done!**
   - Arduino auto check-in to server
   - Gets default Cabai configuration (40% threshold)
   - Starts working immediately!
   - Change settings from dashboard (no re-upload needed!)

**Full Guide:** [DOKUMENTASI_AUTO_PROVISIONING.md](./DOKUMENTASI_AUTO_PROVISIONING.md)

---

## �📖 Dokumentasi

| File | Keterangan |
|------|------------|
| [DOKUMENTASI_AUTO_PROVISIONING.md](./DOKUMENTASI_AUTO_PROVISIONING.md) | ⭐ **NEW!** Plug & Play Auto-Provisioning |
| [DOKUMENTASI_UNIVERSAL.md](./DOKUMENTASI_UNIVERSAL.md) | Dokumentasi lengkap Universal Dashboard |
| [DOKUMENTASI_CABAI.md](./DOKUMENTASI_CABAI.md) | Dokumentasi Cabai monitoring (legacy) |
| [INSTALL_ARDUINO.md](./INSTALL_ARDUINO.md) | Panduan install Arduino IDE + ESP32 |
| [QUICK_START.md](./QUICK_START.md) | Quick start guide |

---

## 🗂️ Project Structure

```
Smart-Garden-IoT/
├── app/
│   ├── Http/Controllers/
│   │   ├── MonitoringController.php     # Monitoring API Controller
│   │   └── DeviceController.php         # Device Management Controller
│   └── Models/
│       ├── Monitoring.php               # Monitoring Model
│       └── DeviceSetting.php            # Device Settings Model
├── arduino/
│   └── auto_provisioning_esp32.ino      # ⭐ ESP32 Auto-Provisioning Code
├── database/
│   └── migrations/
│       ├── *_create_monitorings_table.php
│       └── *_create_device_settings_table.php
├── resources/views/
│   └── universal-dashboard.blade.php    # Universal Dashboard
├── routes/
│   ├── web.php                          # Web Routes
│   └── api.php                          # API Routes (13 endpoints)
├── test-auto-provisioning.ps1           # Test script
└── README.md                            # This file
```

---

## 🎨 Screenshots

### Dashboard Real-time
![Dashboard](https://via.placeholder.com/800x400?text=Universal+IoT+Dashboard)

### Activity Logs
![Logs](https://via.placeholder.com/800x400?text=Activity+Logs)

### Device Management
![Devices](https://via.placeholder.com/800x400?text=Device+Management)

---

## 🔐 Security (Production)

⚠️ **IMPORTANT untuk Production Deployment:**

1. Ganti SQLite dengan MySQL/PostgreSQL
2. Tambahkan authentication (Laravel Sanctum)
3. Enable rate limiting untuk API
4. Setup HTTPS/SSL
5. Configure CORS policy
6. Enable firewall rules

---

## 🤝 Contributing

Contributions are welcome! Please follow these steps:

1. Fork repository
2. Create feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to branch (`git push origin feature/AmazingFeature`)
5. Open Pull Request

---

## 📝 Changelog

### v2.0.0 (2026-01-02) - Latest
- ✅ **Auto-Provisioning System** - Plug & Play Arduino setup
- ✅ **Multi-Device Support** - Manage unlimited devices with unique IDs
- ✅ **Plant Presets** - Quick switch between Cabai (40%), Tomat (60%)
- ✅ **Dynamic Configuration** - Update settings without re-uploading code
- ✅ **Device Management API** - 6 new endpoints for device control
- ✅ **Universal Dashboard** - Multi-sensor support (DHT22, Soil Moisture)
- ✅ **Real-time Monitoring** - 3-second auto-refresh
- ✅ **Activity Logs** - Track all system events
- ✅ **Comprehensive Documentation** - 500+ lines guide

---

## 📄 License

MIT License - Free to use and modify

---

## 👨‍💻 Developer

**PANDORA013**
- GitHub: [@PANDORA013](https://github.com/PANDORA013)
- Repository: [Smart-Garden-IoT](https://github.com/PANDORA013/Smart-Garden-IoT)

---

## 📞 Support

Jika ada masalah atau pertanyaan:
1. Buka [Issues](https://github.com/PANDORA013/Smart-Garden-IoT/issues)
2. Sertakan screenshot error
3. Detail environment (OS, PHP version, ESP32 board)

---

<p align="center">Made with ❤️ for IoT Community</p>


In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
# 🌱 Smart Garden IoT

> **Sistem Monitoring dan Kontrol Taman Pintar dengan IoT Dashboard**

[![Laravel](https://img.shields.io/badge/Laravel-12.39.0-red?logo=laravel)](https://laravel.com)
[![React](https://img.shields.io/badge/React-19.2.0-blue?logo=react)](https://react.dev)
[![Vite](https://img.shields.io/badge/Vite-7.2.4-purple?logo=vite)](https://vitejs.dev)
[![PHP](https://img.shields.io/badge/PHP-8.4.11-777BB4?logo=php)](https://www.php.net)

## 📋 Deskripsi Project

Smart Garden IoT adalah aplikasi web dashboard untuk monitoring dan kontrol sistem penyiraman taman otomatis. Dashboard menampilkan data sensor real-time dan kontrol pompa air dengan mode otomatis/manual.

### ✨ Fitur Utama

- 🌊 **Monitoring Kelembapan Tanah** - Real-time soil moisture tracking
- 💧 **Level Tangki Air** - Water level monitoring dengan sensor HC-SR04
- ⚡ **Konsumsi Daya** - Power usage monitoring
- 🌱 **Kegemburan Tanah** - Soil friability indicator
- 🤖 **Mode Otomatis** - AI-powered automatic watering system
- 🎮 **Mode Manual** - Manual pump control
- 📊 **Sistem Rekomendasi** - Smart advisory dengan color-coded alerts
- ⏰ **Timer Scheduling** - Scheduled watering dengan durasi setting

## 🛠️ Tech Stack

### Backend
- **Laravel 12.39.0** - PHP Framework
- **SQLite** - Database
- **PHP 8.4.11** - Server-side language

### Frontend
- **React 19.2.0** - UI Library
- **Vite 7.2.4** - Build Tool
- **Tailwind CSS 4.0** - Styling Framework
- **Lucide React** - Icon Library

## 📦 Instalasi

### Prerequisites
- PHP >= 8.2
- Composer
- Node.js >= 18.x
- NPM atau Yarn
- SQLite extension enabled

### Langkah-langkah Instalasi

1. **Clone Repository**
```bash
git clone https://github.com/PANDORA013/Smart-Garden-IoT.git
cd Smart-Garden-IoT
```

2. **Install PHP Dependencies**
```bash
composer install
```

3. **Setup Environment**
```bash
cp .env.example .env
php artisan key:generate
```

4. **Setup Database**
```bash
# Enable PDO SQLite di php.ini:
# extension=pdo_sqlite

php artisan migrate
```

5. **Install Node Dependencies**
```bash
npm install
```

6. **Build Assets**
```bash
# Development
npm run dev

# Production
npm run build
```

7. **Run Application**
```bash
php artisan serve
```

Dashboard akan tersedia di: `http://localhost:8000`

## 🎯 Cara Penggunaan

### Mode Otomatis
1. Toggle switch ke posisi "Auto"
2. Sistem akan otomatis:
   - Nyalakan pompa jika kelembapan < 35%
   - Matikan pompa jika kelembapan > 75%
   - Safety shutoff jika air tangki < 5%

### Mode Manual
1. Toggle switch ke posisi "Manual"
2. Klik tombol "NYALAKAN POMPA" atau "MATIKAN POMPA"
3. Atur jadwal dan durasi di panel Timer Settings

### Monitoring
- **Metric Cards** menampilkan data sensor real-time (update tiap 2 detik)
- **Recommendation Panel** memberikan saran berdasarkan kondisi
- **Water Tank Visualization** menampilkan level air secara visual

## 📊 Struktur Project

```
Smart-Garden-IoT/
├── app/                    # Laravel application logic
├── database/
│   ├── migrations/        # Database migrations
│   └── database.sqlite    # SQLite database
├── resources/
│   ├── js/
│   │   ├── app.jsx       # React entry point
│   │   └── SmartGardenApp.jsx  # Main dashboard component (189 lines)
│   ├── css/
│   │   └── app.css       # Tailwind CSS
│   └── views/
│       └── welcome.blade.php   # Blade template
├── public/               # Public assets
├── vite.config.js       # Vite configuration
└── package.json         # NPM dependencies
```

## 🔧 Konfigurasi

### Sensor Simulation Settings
Edit di `resources/js/SmartGardenApp.jsx`:

```javascript
// Initial values
const [moisture, setMoisture] = useState(65);      // 65%
const [waterLevel, setWaterLevel] = useState(75);  // 75%
const [powerUsage, setPowerUsage] = useState(42.3); // 42.3W

// Update interval
useEffect(() => {
  const interval = setInterval(() => {
    // Logic here
  }, 2000); // 2 seconds
}, [dependencies]);
```

### Auto Mode Thresholds
```javascript
if (moisture < 35 && waterLevel > 10) {
  setIsPumpOn(true);  // Turn ON
} else if (moisture > 75) {
  setIsPumpOn(false); // Turn OFF
}
```

## 🚀 Roadmap Development

### ✅ Phase 1 - Dashboard UI (COMPLETED)
- [x] React dashboard dengan sensor simulation
- [x] Auto/Manual mode toggle
- [x] Pump control logic
- [x] Real-time data updates
- [x] Recommendation system
- [x] Responsive design

### 🔄 Phase 2 - Backend Integration (In Progress)
- [ ] API endpoints untuk sensor data
- [ ] User authentication (Laravel Sanctum)
- [ ] Database models (sensors, logs, users)
- [ ] History & analytics

### 📅 Phase 3 - Hardware Integration (Planned)
- [ ] ESP32/Arduino connection
- [ ] Real sensor integration:
  - Soil moisture sensor
  - HC-SR04 ultrasonic sensor
  - Relay module for pump
  - Voltage sensor
- [ ] WebSocket/MQTT communication
- [ ] OTA updates

### 🎨 Phase 4 - Advanced Features (Future)
- [ ] Mobile app (React Native)
- [ ] Push notifications
- [ ] Multi-zone garden support
- [ ] Weather API integration
- [ ] Data export (Excel/CSV)
- [ ] Chart visualizations (Chart.js)

## 🐛 Troubleshooting

### Error: "could not find driver (sqlite)"
**Solusi:**
1. Buka `php.ini`
2. Uncomment: `extension=pdo_sqlite`
3. Restart web server

### Error: "no such table: sessions"
**Solusi:**
```bash
php artisan session:table
php artisan migrate
```

### Dashboard tidak muncul
**Solusi:**
1. Cek Vite dev server: `npm run dev`
2. Hard refresh browser: `Ctrl + Shift + R`
3. Clear browser cache
4. Build ulang: `npm run build`

## 📄 License

This project is open-sourced software licensed under the MIT license.

## 👨‍💻 Developer

**PANDORA013**
- GitHub: [@PANDORA013](https://github.com/PANDORA013)
- Repository: [Smart-Garden-IoT](https://github.com/PANDORA013/Smart-Garden-IoT)

## 🙏 Acknowledgments

- Laravel Framework
- React Team
- Vite Team
- Tailwind CSS
- Lucide Icons
- Community contributors

---

**⭐ Star this repository jika project ini bermanfaat!**
