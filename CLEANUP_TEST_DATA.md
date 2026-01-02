# 🧹 Cleanup Test Data - Dokumentasi

## 📅 Date: January 2, 2026

---

## 🎯 Tujuan

Menghapus **semua data test mode** dari database agar dashboard hanya menampilkan **data device real**.

---

## 🗑️ Data yang Dihapus

### Device Settings (device_settings table)
- `TEST_MODE_1` - Device untuk test mode Basic
- `TEST_MODE_2` - Device untuk test mode Fuzzy AI
- `TEST_MODE_3` - Device untuk test mode Schedule
- `AUTO_PROVISION_TEST` - Device untuk test auto-provisioning
- `TEST_BACKWARD_COMPAT` - Device untuk test backward compatibility

### Monitoring Logs (monitorings table)
- Semua logs dari device test di atas
- `Manual Control` - Logs dari manual relay toggle

---

## 📊 Status Database Setelah Cleanup

```
Device Settings: 0 records
Monitoring Logs: 0 records
```

**Database sekarang bersih dan siap menerima data ESP32 real!**

---

## 🚀 Cara Menggunakan Device Real

### 1. **Upload Firmware ESP32**

Gunakan salah satu firmware:

#### Option A: Universal IoT (Recommended)
```arduino
File: arduino/universal_iot_esp32.ino

Features:
- Auto-provisioning (device otomatis register)
- DHT22 sensor (suhu & kelembapan)
- Soil moisture sensor
- Relay control
- WiFi auto-reconnect
```

#### Option B: Smart Mode
```arduino
File: arduino/smart_mode_esp32.ino

Features:
- 4 Mode Cerdas (Basic, Fuzzy, Schedule, Manual)
- Auto check-in setiap 30 detik
- NTP time sync
- Advanced threshold logic
```

### 2. **Konfigurasi WiFi di ESP32**

Edit file `.ino` bagian WiFi:

```cpp
const char* ssid = "YOUR_WIFI_SSID";
const char* password = "YOUR_WIFI_PASSWORD";
const char* serverUrl = "http://192.168.1.100:8000"; // Ganti dengan IP Laravel Anda
```

### 3. **Upload & Monitor Serial**

1. Compile & upload ke ESP32
2. Buka Serial Monitor (115200 baud)
3. ESP32 akan otomatis:
   - Connect ke WiFi
   - Register device via `/api/device/check-in`
   - Kirim data sensor via `/api/monitoring/insert`

### 4. **Lihat Dashboard**

```
http://localhost:8000
```

Dashboard akan otomatis menampilkan:
- ✅ Nama device (dari ESP32)
- ✅ Suhu & kelembapan real-time
- ✅ Soil moisture
- ✅ Status relay/pompa
- ✅ Mode operasi (Basic/Fuzzy/Schedule/Manual)

---

## 📡 API Endpoints untuk ESP32

### 1. Auto-Provisioning (Check-in)
```http
GET /api/device/check-in?device_id=ESP32_CABAI_01

Response:
{
  "device_id": "ESP32_CABAI_01",
  "mode": 1,
  "batas_siram": 40,
  "batas_stop": 70,
  ...
}
```

### 2. Insert Sensor Data
```http
POST /api/monitoring/insert
Content-Type: application/json

{
  "device_name": "ESP32_CABAI_01",
  "temperature": 28.5,
  "humidity": 65.0,
  "soil_moisture": 42.3,
  "relay_status": true,
  "ip_address": "192.168.1.105"
}
```

---

## 🔧 Script Cleanup

### File: `cleanup-test-data.php`

```php
<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\DeviceSetting;
use App\Models\Monitoring;

// Hapus test devices
$testDevices = ['TEST_MODE_1', 'TEST_MODE_2', 'TEST_MODE_3', ...];
DeviceSetting::whereIn('device_id', $testDevices)->delete();
Monitoring::whereIn('device_name', $testDevices)->delete();
```

### Cara Menjalankan

```bash
php cleanup-test-data.php
```

---

## 🎯 Fokus Dashboard untuk 1 Tanaman

### Halaman Dashboard
**Tujuan:** Monitoring real-time kondisi tanaman

**Tampilan:**
```
┌─────────────────────────────────────────┐
│ 📊 Monitoring Real-time                │
├─────────────────────────────────────────┤
│ ┌──────┐ ┌──────┐ ┌──────┐ ┌──────┐   │
│ │ 28°C │ │ 65% │ │ 42% │ │ OFF  │    │
│ │ Suhu │ │Udara│ │Tanah│ │Relay │    │
│ └──────┘ └──────┘ └──────┘ └──────┘   │
│                                         │
│ 🌿 ESP32_CABAI_01                      │
│ Jenis: Cabai | Mode: Basic Threshold   │
│                                         │
│ 📈 Grafik Real-time (30 data points)   │
│ [Chart: Temperature, Humidity, Soil]    │
└─────────────────────────────────────────┘
```

### Halaman Perangkat
**Tujuan:** Info device & kontrol

**Tampilan:**
```
┌─────────────────────────────────────────┐
│ 📡 Informasi Perangkat                  │
├─────────────────────────────────────────┤
│ Nama: ESP32_CABAI_01                    │
│ IP Address: 192.168.1.105               │
│ Status: 🟢 Online                       │
│ Last Seen: Baru saja                    │
│ Firmware: v2.1                          │
│                                         │
│ [🔄 Reboot Device]                      │
└─────────────────────────────────────────┘
```

### Halaman Pengaturan
**Tujuan:** Ubah mode operasi

**Tampilan:**
```
┌─────────────────────────────────────────┐
│ ⚙️ Konfigurasi Mode Operasi            │
├─────────────────────────────────────────┤
│ Device: [ESP32_CABAI_01 ▼]              │
│                                         │
│ Pilih Mode:                             │
│ ⭕ Mode 1: Basic Threshold              │
│ ⚪ Mode 2: Smart AI (Fuzzy)            │
│ ⚪ Mode 3: Terjadwal (Timer)           │
│ ⚪ Mode 4: Manual (Custom)             │
│                                         │
│ [💾 Simpan Pengaturan]                  │
└─────────────────────────────────────────┘
```

---

## 🧪 Testing dengan Data Real

### 1. Insert Data Manual (via Postman)

```http
POST http://localhost:8000/api/monitoring/insert
Content-Type: application/json

{
  "device_name": "ESP32_CABAI_01",
  "temperature": 28,
  "humidity": 65,
  "soil_moisture": 42,
  "relay_status": false
}
```

### 2. Cek Dashboard

Refresh browser → Data akan muncul di card monitoring

### 3. Test Mode Switching

1. Buka halaman **Pengaturan**
2. Pilih Mode 2 (Smart AI)
3. Klik **Simpan**
4. ESP32 akan dapat konfigurasi baru saat check-in berikutnya

---

## 📝 Catatan Penting

### ✅ Setelah Cleanup

- Database bersih dari test data
- Siap menerima data ESP32 real
- Dashboard fokus pada 1 tanaman

### ⚠️ Sebelum Deploy ESP32

1. **Ganti WiFi SSID & Password** di code ESP32
2. **Ganti Server URL** dengan IP laptop/server Anda
3. **Test koneksi** via Serial Monitor
4. **Pastikan Laravel running** di `php artisan serve`

### 🔧 Troubleshooting

**Problem:** Dashboard kosong
**Solution:** 
1. Cek Serial Monitor ESP32 → Apakah data terkirim?
2. Cek Laravel logs: `storage/logs/laravel.log`
3. Test API manual via Postman

**Problem:** ESP32 tidak konek WiFi
**Solution:**
1. Cek SSID & password
2. Cek jarak ESP32 ke router
3. Reset ESP32 (tombol EN)

---

## 🎉 Kesimpulan

✅ **Test data berhasil dihapus**  
✅ **Database bersih untuk data real**  
✅ **Dashboard siap monitoring 1 tanaman**  
✅ **ESP32 ready to deploy**

**Next Steps:**
1. Upload firmware ke ESP32
2. Test koneksi & data sensor
3. Monitor dashboard real-time
4. Enjoy smart gardening! 🌱

---

**File:** `cleanup-test-data.php`  
**Created:** January 2, 2026  
**Status:** ✅ **Completed**
