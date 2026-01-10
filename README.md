# 🌱 Smart Garden IoT

Sistem monitoring dan kontrol IoT untuk tanaman otomatis berbasis Laravel, React, dan Raspberry Pi Pico W.

## ✨ Fitur

- **Real-time Monitoring**: Temperature & soil moisture tracking
- **4 Mode Kontrol**: Basic, Advanced, Schedule, Manual
- **2-Way Communication**: Pico W ↔️ Laravel via HTTP
- **Responsive Dashboard**: Monitoring & kontrol real-time
- **Auto-Provisioning**: Device settings otomatis

## 🚀 Quick Start

### Prerequisites
- PHP 8.1+ dengan Composer
- MySQL Database
- Raspberry Pi Pico W
- DHT22 & Soil Moisture Sensor

### Installation

```bash
# Clone & Install
git clone https://github.com/PANDORA013/Smart-Garden-IoT.git
cd Smart-Garden-IoT
composer install
npm install

# Setup
cp .env.example .env
php artisan key:generate
php artisan migrate

# Build & Run
npm run build
php artisan serve --host=0.0.0.0 --port=8000
```

### Hardware Setup

1. Upload `arduino/pico_smart_gateway.ino` ke Pico W
2. Update WiFi credentials di code
3. Update SERVER_URL sesuai IP laptop
4. Monitor via Serial (115200 baud)

## 📡 API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/monitoring/insert` | Kirim data sensor (2-way) |
| GET | `/api/monitoring/latest` | Data terbaru |
| GET | `/api/monitoring/stats` | Statistik |
| GET | `/api/monitoring/history` | History data |

## 🛠️ Tech Stack

- **Backend**: Laravel 10.x
- **Frontend**: React, Inertia.js, Tailwind CSS
- **Database**: MySQL
- **Hardware**: Raspberry Pi Pico W
- **Sensors**: DHT22, Capacitive Soil Sensor

## 📄 License

MIT License

---

**Version**: 2.0.0  
**Last Updated**: 10 Januari 2026
