# 📊 Perbandingan Backend: Sebelum vs Sesudah Update

## 🎯 Tujuan Update

Menambahkan **backward compatibility** dengan pola API yang lebih sederhana sesuai permintaan user, sambil **mempertahankan fitur-fitur advanced** yang sudah ada.

---

## 📋 Struktur Backend Yang Sudah Ada (Sebelum Update)

### ✅ Database Schema (Sudah Lengkap)

#### Tabel `monitorings`
```sql
- id (primary key)
- device_name (index) ← Berfungsi sebagai device_id
- temperature (float)
- humidity (float)
- soil_moisture (float)
- relay_status (boolean)
- status_pompa (string)
- ip_address (string)
- timestamps
```

#### Tabel `device_settings`
```sql
- id (primary key)
- device_id (unique) ← Nama perangkat unik
- device_name (string)
- plant_type (string)
- mode (integer) ← 1=Basic, 2=Fuzzy, 3=Schedule, 4=Manual
- batas_siram (integer) ← Threshold ON
- batas_stop (integer) ← Threshold OFF
- jam_pagi (time) ← Schedule morning
- jam_sore (time) ← Schedule evening
- durasi_siram (integer) ← Duration in seconds
- sensor_min (integer) ← Calibration: Dry value (0-4095)
- sensor_max (integer) ← Calibration: Wet value (0-4095)
- firmware_version (string)
- is_active (boolean)
- last_seen (timestamp)
- timestamps
```

### ✅ API Endpoints (14 Endpoints)

#### DeviceController (8 endpoints)
1. `GET /api/device/check-in` - Arduino auto-provisioning
2. `GET /api/devices` - List all devices
3. `GET /api/devices/{id}` - Get single device
4. `PUT /api/devices/{id}` - Update device
5. `DELETE /api/devices/{id}` - Delete device
6. `POST /api/devices/{id}/preset` - Apply preset (cabai/tomat)
7. `POST /api/devices/{id}/mode` - **Update mode (1/2/3/4)**
8. `POST /api/devices/register` - Register new device (auto-provisioning)

#### MonitoringController (6 endpoints)
1. `POST /api/monitoring/insert` - Insert sensor data
2. `GET /api/monitoring/latest` - Get latest data
3. `GET /api/monitoring/history` - Get historical data
4. `GET /api/monitoring/stats` - Dashboard statistics
5. `GET /api/monitoring/logs` - Activity logs
6. `POST /api/monitoring/relay/toggle` - Manual relay control

---

## 🆕 Yang Ditambahkan (Update Terbaru)

### 2 Method Baru di MonitoringController

#### 1. `api_show()` - Multi-Device dengan Settings
**Endpoint:** `GET /api/monitoring`

**Purpose:** Mengambil data terakhir dari **SETIAP device** dengan join ke `device_settings`

**Response Format:**
```json
{
  "success": true,
  "count": 3,
  "data": [
    {
      "id": 1,
      "device_name": "ESP32_001",
      "temperature": 28.5,
      "humidity": 65.0,
      "soil_moisture": 42.3,
      "relay_status": true,
      "status_pompa": "Hidup",
      "created_at": "2026-01-02 10:30:00",
      "setting_id": 1,
      "mode": 1,
      "batas_siram": 40,
      "batas_stop": 70,
      "jam_pagi": "07:00:00",
      "jam_sore": "17:00:00",
      "durasi_siram": 5,
      "min_kering": 4095,
      "max_basah": 1500,
      "plant_type": "cabai",
      "firmware_version": "v2.0"
    },
    {
      "id": 2,
      "device_name": "ESP32_002",
      ...
    }
  ]
}
```

**Query Logic:**
```php
// Ambil data terakhir SETIAP device (group by device_name)
// Join dengan device_settings untuk mendapatkan mode & kalibrasi
DB::table('monitorings as m')
    ->leftJoin('device_settings as s', 'm.device_name', '=', 's.device_id')
    ->select('m.*', 's.mode', 's.batas_siram', ...)
    ->whereIn('m.id', function($query) {
        $query->select(DB::raw('MAX(id)'))
              ->from('monitorings')
              ->groupBy('device_name'); // Latest per device
    })
    ->get();
```

**Keunggulan:**
- ✅ **Multi-device support** - Menampilkan semua device dalam 1 request
- ✅ **Joined data** - Log sensor + Settings dalam 1 response
- ✅ **Frontend-friendly** - Tidak perlu 2 API call terpisah

---

#### 2. `updateSettings()` - Update Setting dari Modal
**Endpoint:** `POST /api/settings/update`

**Purpose:** Update setting device (Mode, Threshold, Schedule, Calibration)

**Request Format:**
```json
{
  "device_id": "ESP32_001",
  "mode": 1,
  "batas_kering": 40,
  "batas_stop": 70,
  "jam_pagi": "07:00",
  "jam_sore": "17:00",
  "durasi_siram": 5,
  "min_kering": 4095,
  "max_basah": 1500
}
```

**Response Format:**
```json
{
  "success": true,
  "status": "success",
  "message": "Setting berhasil diupdate",
  "data": {
    "id": 1,
    "device_id": "ESP32_001",
    "mode": 1,
    "batas_siram": 40,
    "batas_stop": 70,
    ...
  }
}
```

**Features:**
- ✅ **Auto-provisioning** - Jika device belum ada, otomatis create
- ✅ **Field name mapping** - Support naming convention lama & baru:
  - `batas_kering` → `batas_siram`
  - `min_kering` → `sensor_min`
  - `max_basah` → `sensor_max`
- ✅ **Partial update** - Hanya update field yang dikirim
- ✅ **Validation** - Mode (1-4), thresholds (0-100%), calibration (0-4095)

---

## 🔄 Routes Update

### Sebelum Update:
```php
// 14 endpoints dalam 2 group
Route::get('/device/check-in', ...);
Route::prefix('devices')->group(...);
Route::prefix('monitoring')->group(...);
```

### Setelah Update:
```php
// 16 endpoints (14 lama + 2 baru)
Route::get('/device/check-in', ...);
Route::prefix('devices')->group(...);
Route::prefix('monitoring')->group(...);

// BARU: Backward compatibility routes
Route::get('/monitoring', [MonitoringController::class, 'api_show']);
Route::post('/settings/update', [MonitoringController::class, 'updateSettings']);
```

---

## 📊 Perbandingan Endpoint

| Fungsi | Endpoint Lama (Advanced) | Endpoint Baru (Simple) | Status |
|--------|--------------------------|------------------------|--------|
| **Get Multi-Device** | `GET /api/devices` | `GET /api/monitoring` | ✅ Both Available |
| **Update Mode** | `POST /api/devices/{id}/mode` | `POST /api/settings/update` | ✅ Both Available |
| **Auto-provision** | `GET /api/device/check-in` | (Built into `insert()`) | ✅ Both Available |

---

## 🎯 Use Cases

### Use Case 1: Frontend Simple (Modal Lama)
```javascript
// Menggunakan endpoint simple
fetch('/api/monitoring')
  .then(res => res.json())
  .then(data => {
    // Langsung dapat data sensor + settings joined
    data.data.forEach(device => {
      console.log(device.device_name, device.mode, device.batas_siram);
    });
  });

// Update setting
fetch('/api/settings/update', {
  method: 'POST',
  body: JSON.stringify({
    device_id: 'ESP32_001',
    mode: 1,
    batas_kering: 40 // Support naming lama
  })
});
```

### Use Case 2: Frontend Advanced (Smart Config)
```javascript
// Menggunakan endpoint advanced
fetch('/api/devices')
  .then(res => res.json())
  .then(data => {
    // Dapat device list dengan metadata lengkap
  });

// Update mode dengan validation ketat
fetch('/api/devices/1/mode', {
  method: 'POST',
  body: JSON.stringify({
    mode: 4,
    batas_siram: 35,
    batas_stop: 75
  })
});
```

---

## ✅ Keuntungan Dual Endpoint

### 1. **Backward Compatibility**
- ✅ Frontend lama tetap jalan tanpa perubahan
- ✅ Support naming convention lama (`batas_kering`, `min_kering`)
- ✅ Response format simple untuk prototyping cepat

### 2. **Forward Compatibility**
- ✅ Frontend baru dapat fitur advanced (4 modes, validation)
- ✅ Naming convention standar (`batas_siram`, `sensor_min`)
- ✅ Response format terstruktur dengan metadata

### 3. **Developer Experience**
- ✅ **Pemula:** Pakai `/api/monitoring` dan `/api/settings/update` (simple)
- ✅ **Advanced:** Pakai `/api/devices/*` (full control)
- ✅ **Migration Path:** Mulai dari simple, upgrade ke advanced tanpa breaking

---

## 📝 Migration Guide

### Jika Menggunakan Frontend Lama:
```javascript
// TIDAK PERLU UBAH KODE APAPUN!
// Endpoint /api/monitoring dan /api/settings/update sudah tersedia
```

### Jika Ingin Upgrade ke Frontend Baru:
```javascript
// Ganti gradually:
// OLD: /api/monitoring → NEW: /api/devices
// OLD: /api/settings/update → NEW: /api/devices/{id}/mode

// Benefit: Dapat validation lebih ketat, error handling lebih baik
```

---

## 🧪 Testing

### Test Endpoint Baru:
```bash
# Test api_show (multi-device)
curl http://localhost:8000/api/monitoring

# Test updateSettings
curl -X POST http://localhost:8000/api/settings/update \
  -H "Content-Type: application/json" \
  -d '{
    "device_id": "ESP32_TEST",
    "mode": 1,
    "batas_kering": 40,
    "min_kering": 4095
  }'
```

### Expected Responses:
```json
// /api/monitoring
{
  "success": true,
  "count": 3,
  "data": [...]
}

// /api/settings/update
{
  "success": true,
  "status": "success",
  "message": "Setting berhasil diupdate",
  "data": {...}
}
```

---

## 🎯 Kesimpulan

### ✅ Yang Sudah Ada (Tidak Berubah):
- ✅ Database schema lengkap (monitorings + device_settings)
- ✅ Auto-provisioning via `/api/device/check-in`
- ✅ 4 Mode Cerdas (Basic, Fuzzy, Schedule, Manual)
- ✅ Device management (CRUD, preset, mode switching)
- ✅ Monitoring APIs (insert, latest, history, stats, logs)

### 🆕 Yang Ditambahkan (Update):
- ✅ `GET /api/monitoring` - Multi-device data dengan settings joined
- ✅ `POST /api/settings/update` - Simple update setting dengan auto-provision
- ✅ Field name mapping untuk backward compatibility
- ✅ Dokumentasi lengkap

### 🚀 Next Steps:
1. ✅ Backend updated dengan 2 method baru
2. ✅ Routes updated dengan backward compatibility
3. ⏳ Test endpoint baru via Postman/curl
4. ⏳ Frontend dapat pilih endpoint simple atau advanced
5. ⏳ Migration guide untuk upgrade gradual

---

**Status:** ✅ **Backend Fully Compatible** - Support both simple & advanced patterns!

**Created:** January 2, 2026  
**Version:** Backend v2.1 (Dual Endpoint)
