# Smart Garden IoT - System Requirements & Versions

## Server Environment
- **OS**: Windows (XAMPP)
- **Server**: Apache (XAMPP)
- **Port**: 8080 (configured), 8000 (Laravel dev server)
- **Current Time**: April 27, 2026

---

## Backend Stack

### PHP & Core
- **PHP Version**: 8.2.12 (CLI)
- **Zend Engine**: v4.2.12
- **OPcache**: Enabled v8.2.12

### Laravel Framework
- **Laravel**: 12.53.0
- **Composer**: 2.8.12 (2025-09-19)

### PHP Extensions
✅ **Database Support**:
- mysqli
- mysqlnd
- PDO
- pdo_mysql
- pdo_sqlite

✅ **All required extensions loaded**

### Backend Dependencies (composer.json)
```
"php": "^8.2",
"laravel/framework": "^12.0",
"laravel/sanctum": "^4.3",
"laravel/tinker": "^2.10.1",
"livewire/livewire": "^4.2"
```

### Dev Dependencies
- barryvdh/laravel-ide-helper: ^3.6
- fakerphp/faker: ^1.23
- laravel/pail: ^1.2.2
- laravel/pint: ^1.24
- laravel/sail: ^1.41
- mockery/mockery: ^1.6
- nunomaduro/collision: ^8.6
- phpunit/phpunit: ^11.5.3

---

## Frontend Stack

### Node.js & Package Manager
- **Node.js**: v24.5.0
- **npm**: 11.5.1

### Vite & Build Tools
- **Vite**: 7.3.1 (win32-x64)
- **laravel-vite-plugin**: ^2.0.0

### Frontend Dependencies (package.json)
```
"dependencies": {
  "lucide-react": "^0.554.0",
  "react": "^19.2.0",
  "react-dom": "^19.2.0"
}

"devDependencies": {
  "@tailwindcss/vite": "^4.0.0",
  "@vitejs/plugin-react": "^5.1.2",
  "axios": "^1.11.0",
  "concurrently": "^9.0.1",
  "laravel-vite-plugin": "^2.0.0",
  "tailwindcss": "^4.0.0",
  "vite": "^7.3.1"
}
```

### CSS Framework
- **Tailwind CSS**: 4.0.0 (with Vite integration)

---

## Database

### Configuration
- **Default Driver**: MySQL
- **Host**: 127.0.0.1 (localhost)
- **Port**: 3306
- **Database**: smart_garden
- **Charset**: utf8mb4
- **Collation**: utf8mb4_unicode_ci

### Migrations Status: ✅ ALL RUNNING
1. ✅ 2025_11_25_131119_create_sessions_table [Batch 1]
2. ✅ 2026_01_02_000001_create_monitorings_table [Batch 1]
3. ✅ 2026_01_02_115006_create_device_settings_table [Batch 1]
4. ✅ 2026_01_04_005441_add_connected_devices_to_monitorings_table [Batch 1]
5. ✅ 2026_01_14_195414_add_hardware_status_to_monitorings_table [Batch 1]
6. ✅ 2026_02_03_201417_create_personal_access_tokens_table [Batch 2]
7. ✅ 2026_02_03_201657_create_devices_table [Batch 2]
8. ✅ 2026_02_03_201918_add_relay_command_to_device_settings_table [Batch 3]
9. ✅ 2026_02_03_220000_add_indexes_to_monitorings_table [Batch 4]
10. ✅ 2026_03_09_010343_add_last_seen_at_to_device_settings_table [Batch 5]

---

## Application URLs

### Development Server
- **Laravel**: http://127.0.0.1:8000
- **Alternative**: http://127.0.0.1:8080 (Apache)

### API Endpoints
- **Base URL**: /api
- **Monitoring**: /api/monitoring
- **Devices**: /api/devices
- **Settings**: /api/settings

---

## Key Features Enabled

✅ Authentication & Authorization
- Sanctum (API token authentication)
- Blade templating with Auth
- Role-based middleware

✅ Real-time Monitoring
- Device status tracking
- Sensor data logging
- WebSocket support ready

✅ Build & Compilation
- Vite HMR (Hot Module Replacement)
- Tailwind CSS JIT compilation
- React component support

✅ Development Tools
- Laravel Pint (code formatting)
- PHPUnit (testing)
- IDE Helper generation

---

## Commands

### Development
```bash
# Start Laravel dev server
php artisan serve

# Start Vite dev server
npm run dev

# Both at once
npm run dev (from composer scripts)

# Network accessible dev
npm run dev:network
npm run serve:network
npm run start:network
```

### Build
```bash
# Build for production
npm run build
vite build
```

### Database
```bash
# Run migrations
php artisan migrate

# Rollback migrations
php artisan migrate:rollback

# Fresh migration
php artisan migrate:fresh
```

### Code Quality
```bash
# Format code
./vendor/bin/pint

# Run tests
php artisan test
./vendor/bin/phpunit
```

---

## System Health Status

| Component | Status | Version |
|-----------|--------|---------|
| PHP | ✅ OK | 8.2.12 |
| Laravel | ✅ OK | 12.53.0 |
| Composer | ✅ OK | 2.8.12 |
| Node.js | ✅ OK | v24.5.0 |
| npm | ✅ OK | 11.5.1 |
| Vite | ✅ OK | 7.3.1 |
| Database | ✅ OK | MySQL 8.0+ |
| Migrations | ✅ OK | All 10 ran |
| Extensions | ✅ OK | PDO, MySQLi, SQLite |

---

## Last Updated
**Date**: April 27, 2026  
**Time**: After successful GitHub push (commit 30af959)

---

## Notes
- Project is **production-ready** with all dependencies installed
- Database migrations successfully applied
- Frontend tooling configured for React + Tailwind CSS
- API structure supports real-time IoT monitoring
- All models created (Berita, Carousel, Gambar, Text, Device, Monitoring, etc.)
