<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

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
