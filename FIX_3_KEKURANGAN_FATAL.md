# 🔧 Fix 3 Kekurangan Fatal Backend - Dokumentasi

## 📅 Date: January 2, 2026

---

## 🎯 Ringkasan Masalah & Solusi

Berdasarkan analisis, ada **3 kekurangan fatal** yang sudah diperbaiki:

---

## ❌ Masalah 1: Database Belum Sinkron

### **Status:** ✅ **SOLVED**

### **Deskripsi Masalah:**
Controller mencoba menyimpan `temperature`, `humidity`, `device_name`, `ip_address` tapi kolom-kolom tersebut tidak ada di database.

### **Dampak:**
```
SQLSTATE[42S22]: Column not found: 1054 Unknown column 'temperature'
```

### **Solusi:**
Migration sudah ada dan sudah di-run:

**File:** `database/migrations/2026_01_02_113158_update_monitorings_table_for_universal_iot.php`

```php
Schema::table('monitorings', function (Blueprint $table) {
    $table->float('temperature')->nullable()->after('id');
    $table->float('humidity')->nullable()->after('temperature');
    $table->boolean('relay_status')->default(false)->after('humidity');
    $table->string('device_name')->nullable()->after('relay_status');
    $table->string('ip_address')->nullable()->after('device_name');
});
```

### **Verifikasi:**
```bash
php artisan migrate:status
```

Output:
```
✅ 2026_01_02_000001_create_monitorings_table ............. [1] Ran
✅ 2026_01_02_113158_update_monitorings_table_for_universal_iot [1] Ran
```

### **Struktur Tabel Akhir:**
```sql
monitorings:
├── id
├── temperature (float, nullable)
├── humidity (float, nullable)
├── relay_status (boolean)
├── device_name (string, nullable)
├── ip_address (string, nullable)
├── soil_moisture (float, nullable)
├── status_pompa (string, nullable)
├── created_at
└── updated_at
```

---

## ❌ Masalah 2: Tidak Ada Tabel Penyimpan Mode

### **Status:** ✅ **SOLVED**

### **Deskripsi Masalah:**
Tidak ada tempat untuk menyimpan pilihan mode (Manual/Fuzzy/Jadwal) dan kalibrasi sensor.

### **Dampak:**
- User pilih "Mode Fuzzy" → Tidak ada yang di-save
- Refresh page → Kembali ke default
- Arduino tidak tahu mode apa yang dipilih

### **Solusi:**
Tabel `device_settings` sudah dibuat dengan migration lengkap.

**File:** `database/migrations/2026_01_02_115006_create_device_settings_table.php`

```php
Schema::create('device_settings', function (Blueprint $table) {
    $table->id();
    $table->string('device_id')->unique();
    $table->string('device_name')->nullable();
    $table->string('plant_type')->default('cabai');
    
    // MODE OPERASI
    $table->integer('mode')->default(1); // 1=Basic, 2=Fuzzy, 3=Schedule, 4=Manual
    
    // KALIBRASI SENSOR
    $table->integer('sensor_min')->default(4095); // ADC kering (udara)
    $table->integer('sensor_max')->default(1500); // ADC basah (air)
    
    // PARAMETER MODE 1 & 4: THRESHOLD
    $table->integer('batas_siram')->default(40); // Pompa ON jika < nilai ini
    $table->integer('batas_stop')->default(70);  // Pompa OFF jika >= nilai ini
    
    // PARAMETER MODE 3: SCHEDULE
    $table->time('jam_pagi')->default('07:00:00');
    $table->time('jam_sore')->default('17:00:00');
    $table->integer('durasi_siram')->default(5); // Detik
    
    // STATUS & INFO
    $table->boolean('is_active')->default(true);
    $table->timestamp('last_seen')->nullable();
    $table->string('firmware_version')->nullable();
    $table->text('notes')->nullable();
    
    $table->timestamps();
});
```

### **Verifikasi:**
```bash
php artisan migrate:status
```

Output:
```
✅ 2026_01_02_115006_create_device_settings_table ......... [1] Ran
```

### **Model:**
```php
// app/Models/DeviceSetting.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceSetting extends Model
{
    protected $fillable = [
        'device_id',
        'device_name',
        'plant_type',
        'mode',
        'sensor_min',
        'sensor_max',
        'batas_siram',
        'batas_stop',
        'jam_pagi',
        'jam_sore',
        'durasi_siram',
        'is_active',
        'last_seen',
        'firmware_version',
        'notes'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_seen' => 'datetime',
        'jam_pagi' => 'datetime',
        'jam_sore' => 'datetime'
    ];
}
```

---

## ❌ Masalah 3: Komunikasi Satu Arah (Arduino Tidak Tahu Mode)

### **Status:** ✅ **FIXED**

### **Deskripsi Masalah:**
Function `insert()` di MonitoringController hanya membalas "Data berhasil disimpan" tanpa mengirimkan config balik.

### **Dampak:**
- Arduino tidak tahu mode apa yang dipilih user
- Arduino tetap pakai mode default
- User ubah setting di web → Arduino tidak update

### **Solusi:**
Update function `insert()` untuk mengirim config balik ke Arduino.

#### **SEBELUM:**
```php
public function insert(Request $request)
{
    // Simpan data
    $monitoring = Monitoring::create($data);

    // ❌ Hanya balas success, tidak ada config
    return response()->json([
        'success' => true,
        'message' => 'Data berhasil disimpan',
        'data' => $monitoring
    ], 201);
}
```

#### **SESUDAH:**
```php
public function insert(Request $request)
{
    // 1. Simpan data sensor
    $monitoring = Monitoring::create($data);

    // 2. AUTO-PROVISIONING: Cek/Buat setting untuk device
    $deviceName = $request->device_name ?? 'ESP32-Default';
    $config = \App\Models\DeviceSetting::firstOrCreate(
        ['device_id' => $deviceName],
        [
            'device_name' => $deviceName,
            'plant_type' => 'cabai',
            'mode' => 1, // Default: Mode Pemula
            'batas_siram' => 40,
            'batas_stop' => 70,
            'jam_pagi' => '07:00:00',
            'jam_sore' => '17:00:00',
            'durasi_siram' => 5,
            'sensor_min' => 4095,
            'sensor_max' => 1500,
            'is_active' => true,
            'last_seen' => now()
        ]
    );

    // 3. ✅ KIRIM BALIK CONFIG KE ARDUINO
    return response()->json([
        'success' => true,
        'message' => 'Data berhasil disimpan',
        'data' => $monitoring,
        'config' => [
            'mode' => $config->mode,
            'batas_siram' => $config->batas_siram,
            'batas_stop' => $config->batas_stop,
            'jam_pagi' => substr($config->jam_pagi, 0, 5), // HH:MM
            'jam_sore' => substr($config->jam_sore, 0, 5),
            'durasi_siram' => $config->durasi_siram,
            'sensor_min' => $config->sensor_min,
            'sensor_max' => $config->sensor_max,
            'is_active' => $config->is_active
        ]
    ], 201);
}
```

### **Response Example:**
```json
{
  "success": true,
  "message": "Data berhasil disimpan",
  "data": {
    "id": 1,
    "device_name": "ESP32_Main",
    "temperature": 28.5,
    "humidity": 65,
    "soil_moisture": 42,
    "relay_status": true,
    "created_at": "2026-01-02 10:30:00"
  },
  "config": {
    "mode": 2,
    "batas_siram": 40,
    "batas_stop": 70,
    "jam_pagi": "07:00",
    "jam_sore": "17:00",
    "durasi_siram": 5,
    "sensor_min": 4095,
    "sensor_max": 1500,
    "is_active": true
  }
}
```

### **Arduino Integration:**
```cpp
// Di Arduino, setelah POST data:
StaticJsonDocument<1024> doc;
deserializeJson(doc, http.getString());

// Baca config dari response
int newMode = doc["config"]["mode"];
int threshold = doc["config"]["batas_siram"];
int stopThreshold = doc["config"]["batas_stop"];
String jamPagi = doc["config"]["jam_pagi"];

// Update variable global
currentMode = newMode;
BATAS_SIRAM = threshold;
BATAS_STOP = stopThreshold;

Serial.println("Config updated: Mode " + String(newMode));
```

---

## 🧪 Testing

### Test 1: Insert Data + Get Config

**Request:**
```bash
curl -X POST http://localhost:8000/api/monitoring/insert \
  -H "Content-Type: application/json" \
  -d '{
    "device_name": "ESP32_Test",
    "temperature": 29.5,
    "humidity": 68,
    "soil_moisture": 45,
    "relay_status": false
  }'
```

**Expected Response:**
```json
{
  "success": true,
  "message": "Data berhasil disimpan",
  "data": { ... },
  "config": {
    "mode": 1,
    "batas_siram": 40,
    "batas_stop": 70,
    "jam_pagi": "07:00",
    "jam_sore": "17:00",
    "durasi_siram": 5,
    "sensor_min": 4095,
    "sensor_max": 1500,
    "is_active": true
  }
}
```

### Test 2: Ubah Mode dari Web

**Request:**
```bash
curl -X POST http://localhost:8000/api/devices/1/mode \
  -H "Content-Type: application/json" \
  -d '{
    "mode": 2
  }'
```

**Verifikasi:**
Next time Arduino POST data → response `config.mode` akan berisi `2`

### Test 3: Auto-Provisioning

**Scenario:** Device baru pertama kali connect

**Request:**
```bash
curl -X POST http://localhost:8000/api/monitoring/insert \
  -H "Content-Type: application/json" \
  -d '{
    "device_name": "ESP32_NewDevice",
    "temperature": 27,
    "humidity": 60,
    "soil_moisture": 50
  }'
```

**Expected:**
- Database: Row baru di `device_settings` dengan default config
- Response: Config untuk device baru

---

## 📊 Flow Diagram

### **Komunikasi Dua Arah (Setelah Fix):**

```
┌─────────┐                  ┌─────────┐                  ┌──────────┐
│ Arduino │                  │ Laravel │                  │ Database │
└────┬────┘                  └────┬────┘                  └────┬─────┘
     │                            │                             │
     │ POST /api/monitoring/insert│                             │
     │ { temp, humidity, soil }   │                             │
     ├───────────────────────────>│                             │
     │                            │                             │
     │                            │ 1. Insert to monitorings    │
     │                            ├────────────────────────────>│
     │                            │                             │
     │                            │ 2. Get/Create device_settings│
     │                            ├────────────────────────────>│
     │                            │<────────────────────────────┤
     │                            │                             │
     │ Response: {                │                             │
     │   data: {...},             │                             │
     │   config: {                │                             │
     │     mode: 2,               │                             │
     │     batas_siram: 40        │                             │
     │   }                        │                             │
     │ }                          │                             │
     │<───────────────────────────┤                             │
     │                            │                             │
     │ Arduino baca config        │                             │
     │ dan update variable        │                             │
     │                            │                             │
```

---

## ✅ Checklist Status

### **Masalah 1: Database Sinkron**
- ✅ Migration `create_monitorings_table` - Ran
- ✅ Migration `update_monitorings_table` - Ran  
- ✅ Kolom `temperature` - Ada
- ✅ Kolom `humidity` - Ada
- ✅ Kolom `device_name` - Ada
- ✅ Kolom `relay_status` - Ada
- ✅ Kolom `ip_address` - Ada

### **Masalah 2: Tabel Settings**
- ✅ Migration `create_device_settings_table` - Ran
- ✅ Model `DeviceSetting` - Exists
- ✅ Kolom `mode` - Ada
- ✅ Kolom `batas_siram` / `batas_stop` - Ada
- ✅ Kolom `jam_pagi` / `jam_sore` - Ada
- ✅ Kolom `sensor_min` / `sensor_max` - Ada
- ✅ Route `/api/settings/update` - Ada
- ✅ Route `/api/devices/{id}/mode` - Ada

### **Masalah 3: Komunikasi 2 Arah**
- ✅ Function `insert()` updated - Kirim config balik
- ✅ Auto-provisioning - firstOrCreate() implemented
- ✅ Config includes mode - Yes
- ✅ Config includes thresholds - Yes
- ✅ Config includes schedule - Yes
- ✅ Config includes calibration - Yes

---

## 🎯 Summary

### **Sebelum Fix:**
❌ Database schema tidak lengkap  
❌ Tidak ada tabel settings  
❌ Arduino tidak dapat config balik  
❌ Mode tidak tersimpan  
❌ User ubah setting → Tidak ada efek

### **Setelah Fix:**
✅ Database schema lengkap (temperature, humidity, device_name, dll)  
✅ Tabel `device_settings` untuk simpan mode & kalibrasi  
✅ Arduino dapat config balik setiap kali POST data  
✅ Auto-provisioning untuk device baru  
✅ User ubah setting → Arduino update otomatis saat check-in  

---

## 🚀 Next Steps

1. ✅ **Backend Ready** - Semua 3 masalah fixed
2. ⏳ **Update Arduino Code** - Parse config dari response
3. ⏳ **Test End-to-End** - Web → Backend → Arduino
4. ⏳ **Production Deploy** - Siap untuk device real

---

**Status:** ✅ **ALL 3 ISSUES FIXED**

**Created:** January 2, 2026  
**Version:** Backend v3.1 (Fixed Fatal Issues)
