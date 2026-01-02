# 🚀 AUTO-PROVISIONING SYSTEM - PLUG & PLAY IoT

## 🎯 Konsep "Plug & Play"

Dengan sistem **Auto-Provisioning**, Arduino/ESP32 baru bisa langsung digunakan tanpa setup manual:

1. **Rakit hardware** → Pasang sensor dan relay
2. **Upload code** → Cukup ganti `DEVICE_ID`, `SSID`, dan `PASSWORD`
3. **Nyalakan alat** → Otomatis check-in ke server
4. **Server auto-configure** → Memberikan kalibrasi standar cabai
5. **Alat langsung bekerja** → Monitoring dan menyiram otomatis!

---

## ✨ Fitur Utama

### 1. **Auto-Detection & Registration**
- Arduino check-in pertama kali → Server otomatis buat profil baru
- Profil default: Kalibrasi cabai (batas siram 40%)
- Tidak perlu setup manual di dashboard

### 2. **Dynamic Configuration**
- Arduino sync config setiap 1 menit
- Perubahan setting dari dashboard langsung diterapkan
- Tidak perlu upload ulang code Arduino

### 3. **Multi-Device Support**
- Setiap alat punya ID unik (CABAI_01, CABAI_02, TOMAT_01)
- Setiap alat bisa punya setting berbeda
- Support berbagai jenis tanaman dengan preset

### 4. **Remote Control**
- Ubah threshold dari dashboard
- Apply preset (Cabai, Tomat) dengan 1 klik
- Aktifkan/nonaktifkan alat dari jarak jauh

---

## 📊 Alur Kerja Sistem

```
┌─────────────┐
│  Arduino    │
│  (New)      │
└──────┬──────┘
       │
       │ 1. Check-in: GET /api/device/check-in?device_id=CABAI_01
       ▼
┌─────────────────────────────────────┐
│  Laravel Server                     │
│  ┌────────────────────────────────┐ │
│  │ Check: Device exists?          │ │
│  │   NO → Create new with default │ │
│  │   YES → Load existing config   │ │
│  └────────────────────────────────┘ │
│                                     │
│  Return Configuration:              │
│  {                                  │
│    "sensor_min": 4095,              │
│    "sensor_max": 1500,              │
│    "batas_siram": 40,               │
│    "batas_stop": 70,                │
│    "plant_type": "cabai"            │
│  }                                  │
└──────┬──────────────────────────────┘
       │
       │ 2. Arduino applies config
       ▼
┌─────────────┐
│  Arduino    │
│  Working!   │
│  - Read     │
│  - Control  │
│  - Send     │
└─────────────┘
```

---

## 🔧 Setup Arduino (Super Simple!)

### 1. Upload Code
File: `arduino/auto_provisioning_esp32.ino`

### 2. Edit 3 Baris Saja:
```cpp
// Line 29: Nama unik alat Anda
const char* DEVICE_ID = "CABAI_01";  // Ganti: CABAI_02, TOMAT_01, dll

// Line 33-34: WiFi credentials
const char* ssid = "WIFI_RUMAH";
const char* password = "password123";

// Line 37: IP Laptop Anda
const char* SERVER_IP = "192.168.1.70";  // Cek dengan: ipconfig
```

### 3. Upload & Nyalakan
- Arduino otomatis connect WiFi
- Check-in ke server
- Dapat konfigurasi default cabai
- Langsung bekerja!

---

## 📱 API Endpoints Baru

### 1. Device Check-In (Auto-Provisioning)
**Endpoint:** `GET /api/device/check-in`

**Parameters:**
- `device_id` (required) - ID unik alat
- `firmware` (optional) - Versi firmware

**Response:**
```json
{
  "success": true,
  "message": "Device configuration retrieved",
  "is_new_device": true,
  "config": {
    "device_id": "CABAI_01",
    "plant_type": "cabai",
    "sensor_min": 4095,
    "sensor_max": 1500,
    "batas_siram": 40,
    "batas_stop": 70,
    "is_active": true
  }
}
```

**Arduino Implementation:**
```cpp
void syncConfiguration() {
  String url = "http://192.168.1.70:8000/api/device/check-in?device_id=CABAI_01&firmware=v2.0";
  HTTPClient http;
  http.begin(url);
  int code = http.GET();
  
  if (code == 200) {
    String response = http.getString();
    // Parse JSON dan update variabel
    // sensorMin, sensorMax, batasSiram, dll
  }
}
```

---

### 2. List All Devices
**Endpoint:** `GET /api/devices`

**Response:**
```json
{
  "success": true,
  "count": 3,
  "data": [
    {
      "id": 1,
      "device_id": "CABAI_01",
      "device_name": "CABAI_01",
      "plant_type": "cabai",
      "is_active": true,
      "last_seen": "2 minutes ago",
      "status": "online"
    }
  ]
}
```

**Status Values:**
- `online` - Last seen < 2 menit
- `idle` - Last seen 2-10 menit
- `offline` - Last seen > 10 menit
- `never_connected` - Belum pernah check-in

---

### 3. Get Device Detail
**Endpoint:** `GET /api/devices/{id}`

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "device_id": "CABAI_01",
    "device_name": "CABAI_01",
    "plant_type": "cabai",
    "sensor_min": 4095,
    "sensor_max": 1500,
    "batas_siram": 40,
    "batas_stop": 70,
    "is_active": true,
    "last_seen": "2026-01-02T12:30:00.000000Z",
    "firmware_version": "v2.0",
    "notes": null
  }
}
```

---

### 4. Update Device Settings
**Endpoint:** `PUT /api/devices/{id}`

**Request Body:**
```json
{
  "device_name": "Cabai Greenhouse A",
  "plant_type": "cabai",
  "sensor_min": 4000,
  "sensor_max": 1400,
  "batas_siram": 35,
  "batas_stop": 75,
  "is_active": true,
  "notes": "Greenhouse depan"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Device settings updated successfully",
  "data": { /* updated device */ }
}
```

**Efek:** Arduino akan mendapat config baru dalam 1 menit (saat sync berikutnya)

---

### 5. Apply Preset (Quick Setup)
**Endpoint:** `POST /api/devices/{id}/preset`

**Request Body:**
```json
{
  "preset": "tomat"
}
```

**Available Presets:**
- `cabai` - Batas siram 40%, batas stop 70%
- `tomat` - Batas siram 60%, batas stop 80%

**Response:**
```json
{
  "success": true,
  "message": "Preset tomat applied successfully",
  "data": { /* updated device */ }
}
```

---

### 6. Delete Device
**Endpoint:** `DELETE /api/devices/{id}`

**Response:**
```json
{
  "success": true,
  "message": "Device deleted successfully"
}
```

---

## 🎮 Skenario Penggunaan

### Skenario 1: Tambah Arduino Baru untuk Cabai
1. Rakit hardware (sensor + relay + ESP32)
2. Upload code, set `DEVICE_ID = "CABAI_03"`
3. Nyalakan → Otomatis dapat config cabai
4. Beres! Alat langsung kerja.

### Skenario 2: Ubah Cabai jadi Tomat
1. Buka dashboard (nanti kita buat UI-nya)
2. Pilih device "CABAI_01"
3. Klik "Apply Tomat Preset"
4. Dalam 1 menit, Arduino update config
5. Sekarang menyiram dengan threshold tomat (60%)

### Skenario 3: Kalibrasi Sensor Baru
1. Dashboard → Edit device
2. Ubah `sensor_min` dan `sensor_max`
3. Save → Arduino update dalam 1 menit
4. Tidak perlu upload ulang code!

### Skenario 4: Matikan Alat Sementara
1. Dashboard → Edit device
2. Set `is_active = false`
3. Arduino tetap monitoring tapi pompa OFF
4. Set `is_active = true` untuk aktifkan lagi

---

## 🧪 Testing Manual

### Test 1: Simulasi Arduino Check-In
```bash
curl "http://localhost:8000/api/device/check-in?device_id=CABAI_01&firmware=v2.0"
```

**Expected:** Device baru dibuat dengan config default cabai

### Test 2: Check Device List
```bash
curl http://localhost:8000/api/devices
```

**Expected:** List semua device yang sudah check-in

### Test 3: Update Device Settings
```bash
curl -X PUT http://localhost:8000/api/devices/1 \
  -H "Content-Type: application/json" \
  -d '{"batas_siram":35,"batas_stop":75}'
```

**Expected:** Settings updated, Arduino akan sync dalam 1 menit

### Test 4: Apply Tomat Preset
```bash
curl -X POST http://localhost:8000/api/devices/1/preset \
  -H "Content-Type: application/json" \
  -d '{"preset":"tomat"}'
```

**Expected:** Threshold berubah ke tomat (60%/80%)

---

## 📊 Database Schema: `device_settings`

| Column | Type | Default | Description |
|--------|------|---------|-------------|
| id | BIGINT | - | Primary key |
| device_id | VARCHAR(50) | - | ID unik alat (UNIQUE) |
| device_name | VARCHAR(100) | NULL | Nama readable |
| plant_type | VARCHAR(50) | 'cabai' | Jenis tanaman |
| sensor_min | INT | 4095 | ADC kering |
| sensor_max | INT | 1500 | ADC basah |
| batas_siram | INT | 40 | Pompa ON < nilai ini |
| batas_stop | INT | 70 | Pompa OFF >= nilai ini |
| is_active | BOOLEAN | TRUE | Aktif/nonaktif |
| last_seen | TIMESTAMP | NULL | Terakhir check-in |
| firmware_version | VARCHAR(20) | NULL | Versi firmware |
| notes | TEXT | NULL | Catatan admin |
| created_at | TIMESTAMP | - | Waktu registrasi |
| updated_at | TIMESTAMP | - | Waktu update terakhir |

---

## 🔐 Best Practices

### 1. Device Naming Convention
```
Format: {PLANT_TYPE}_{LOCATION}_{NUMBER}
Contoh:
- CABAI_GREEN_01 (Cabai di greenhouse, alat #1)
- TOMAT_OUT_01 (Tomat outdoor, alat #1)
- CABAI_LAB_A1 (Cabai di lab, section A, alat 1)
```

### 2. Firmware Versioning
```
Format: vX.Y
v1.0 - Basic monitoring
v2.0 - Auto-provisioning
v2.1 - Bug fixes
```

### 3. Maintenance Schedule
- **Setiap hari:** Cek device status (online/offline)
- **Setiap minggu:** Review threshold, sesuaikan jika perlu
- **Setiap bulan:** Kalibrasi ulang sensor (update sensor_min/max)

---

## 🚨 Troubleshooting

### Arduino tidak dapat config?
1. Cek WiFi credentials
2. Cek IP server (ipconfig)
3. Pastikan Laravel server running
4. Cek Serial Monitor (115200 baud)
5. Lihat response error dari server

### Device status offline?
1. Cek koneksi WiFi Arduino
2. Cek `last_seen` timestamp di database
3. Jika > 10 menit, Arduino mungkin hang/restart

### Config tidak update setelah edit?
1. Tunggu 1 menit (sync interval)
2. Cek Serial Monitor untuk konfirmasi sync
3. Restart Arduino jika perlu

### Pompa tidak menyala meski tanah kering?
1. Cek `is_active` di device settings (harus TRUE)
2. Cek `batas_siram` apakah sesuai
3. Cek wiring relay
4. Cek Serial Monitor untuk log auto control

---

## 📈 Roadmap

### v2.1 (Next)
- [ ] Dashboard UI untuk device management
- [ ] Real-time device status indicator
- [ ] Device control: pause/resume dari web
- [ ] Alert jika device offline > 1 jam

### v2.2 (Future)
- [ ] Multi-user dengan role (admin/viewer)
- [ ] Device grouping (by location/plant type)
- [ ] Batch operations (update multiple devices)
- [ ] Export device config (JSON/CSV)

### v3.0 (Advanced)
- [ ] OTA (Over-The-Air) firmware update
- [ ] Schedule watering (timer-based)
- [ ] Weather API integration
- [ ] Machine learning untuk optimal threshold

---

## 🎉 Kesimpulan

Dengan sistem **Auto-Provisioning**, Anda bisa:

✅ **Plug & Play** - Arduino baru langsung kerja tanpa setup manual  
✅ **Remote Control** - Ubah setting dari dashboard tanpa upload ulang  
✅ **Multi-Device** - Manage banyak alat dengan mudah  
✅ **Scalable** - Tambah alat baru tinggal nyalain aja  
✅ **Flexible** - Support berbagai jenis tanaman dengan preset  

**Tidak perlu coding ulang Arduino setiap kali ganti tanaman!** 🚀

---

**Next Step:** Buat UI Dashboard untuk device management (coming soon!)
