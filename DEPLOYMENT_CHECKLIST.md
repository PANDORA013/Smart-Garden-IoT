# ✅ CHECKLIST - 2-Way Communication Setup

## 📋 Status Implementasi

### Backend (Laravel) ✅

- [x] **MonitoringController.php** sudah mendukung 2-way communication
- [x] Response JSON berisi config untuk Pico W
- [x] Auto-provisioning device settings
- [x] Cache optimization untuk performa
- [x] Migration cache table sudah dibuat dan di-migrate

### Frontend (React) ✅

- [x] Dashboard untuk monitoring real-time
- [x] SettingsMinimal.jsx untuk kontrol device
- [x] 4 Mode kontrol tersedia (Basic, Advanced, Schedule, Manual)
- [x] Update threshold dan kalibrasi sensor

### Arduino (Pico W) ✅

- [x] **pico_smart_gateway.ino** sudah mendukung 2-way communication
- [x] Kirim data sensor setiap 10 detik
- [x] Terima dan parse response dari server
- [x] Update konfigurasi lokal otomatis
- [x] Auto-reconnect WiFi jika putus

### Network Configuration ⚠️

- [x] WiFi CCTV_UISI terkonfigurasi
- [x] Backup WiFi (Hotspot HP) tersedia
- [ ] **TODO:** Verifikasi IP address saat ini
- [ ] **TODO:** Test koneksi Pico W ke Server

### Documentation ✅

- [x] TWO_WAY_COMMUNICATION.md (Panduan lengkap)
- [x] Alur komunikasi dijelaskan
- [x] Troubleshooting guide
- [x] Network configuration guide

---

## 🧪 Testing Checklist

### 1. Test Laravel Server

```bash
# Start server
php artisan serve --host=0.0.0.0 --port=8000

# Test API endpoint
curl -X POST http://localhost:8000/api/monitoring/insert \
  -H "Content-Type: application/json" \
  -d '{"device_id":"TEST","temperature":25,"soil_moisture":50}'
```

**Expected:** Response 201 dengan config object

### 2. Test Pico W Upload

- [ ] Upload code ke Pico W
- [ ] Buka Serial Monitor (115200 baud)
- [ ] Tunggu WiFi connect
- [ ] Cek data terkirim setiap 10 detik

**Expected di Serial Monitor:**
```
✅ WiFi Connected!
📡 Sending data to server...
✅ Server Response: 201
🔧 Config updated from server
```

### 3. Test 2-Way Communication

- [ ] Buka Website dashboard
- [ ] Masuk ke Settings
- [ ] Ubah **Mode** (misal dari 1 ke 2)
- [ ] Ubah **Threshold** (misal 40-70 ke 35-75)
- [ ] Tunggu 10 detik (interval Pico kirim data)
- [ ] Cek Serial Monitor

**Expected:**
```
🔧 Config updated from server:
   Mode: 2
   Threshold: 35% - 75%
```

### 4. Test Mode Kontrol

#### Mode 1 (Basic)
- [ ] Set Mode = 1
- [ ] Kelembaban < 40% → Pompa ON
- [ ] Kelembaban > 70% → Pompa OFF

#### Mode 2 (Advanced)
- [ ] Set Mode = 2
- [ ] Kelembaban < 35% → Pompa ON
- [ ] Kelembaban > 75% → Pompa OFF
- [ ] Hysteresis zone (35-75%) maintain status

#### Mode 3 (Schedule)
- [ ] Set Mode = 3
- [ ] Set Jam Pagi = "07:00"
- [ ] Set Jam Sore = "17:00"
- [ ] Pompa ON pada jam tersebut

#### Mode 4 (Manual)
- [ ] Set Mode = 4
- [ ] Toggle pompa dari dashboard
- [ ] Pompa follow dashboard command

---

## 🔧 Pre-Deployment Checklist

### Network

- [ ] Cek IP Laptop: `ipconfig`
- [ ] Update `SERVER_URL` di Arduino jika IP berubah
- [ ] Pastikan firewall tidak block port 8000
- [ ] Test ping dari HP ke Laptop

### Database

- [ ] Migration sudah di-run semua
- [ ] Table `device_settings` ada
- [ ] Table `monitorings` ada
- [ ] Table `cache` dan `cache_locks` ada

### Laravel

- [ ] `.env` configured dengan benar
- [ ] Database connection OK
- [ ] Cache cleared
- [ ] Server running di 0.0.0.0:8000

### Arduino

- [ ] Library installed:
  - WiFi.h
  - HTTPClient.h
  - ArduinoJson.h
  - DHT.h
  - NTPClient.h
- [ ] Board selected: Raspberry Pi Pico W
- [ ] Port selected (COMx atau /dev/ttyUSBx)

---

## ⚠️ Known Issues & Solutions

### Issue: IP Address berubah (Hotspot HP)

**Solution:**
```cpp
// Di Arduino, update SERVER_URL sesuai IP Laptop saat ini
const char* SERVER_URL = "http://[IP_BARU]:8000/api/monitoring/insert";
```

Cek IP: `ipconfig` (Windows) atau `ifconfig` (Mac/Linux)

### Issue: Pico tidak terima config

**Solution:**
1. Clear Laravel cache: `php artisan cache:clear`
2. Cek `device_id` di Pico sama dengan database
3. Cek Serial Monitor untuk error message

### Issue: Data tidak masuk database

**Solution:**
1. Cek Laravel log: `storage/logs/laravel.log`
2. Test API manual dengan curl atau Postman
3. Verify migration sudah di-run

### Issue: WiFi tidak connect

**Solution:**
1. Cek SSID dan password benar
2. Cek signal strength (harus > -70 dBm)
3. Restart router/hotspot
4. Upload ulang code ke Pico W

---

## 🚀 Deployment Steps

### 1. Persiapan

```bash
# Clone/Pull latest code
git pull origin main

# Install dependencies
composer install
npm install

# Run migrations
php artisan migrate

# Build frontend
npm run build
```

### 2. Start Server

```bash
# Development
php artisan serve --host=0.0.0.0 --port=8000

# Production (Nginx/Apache)
# Configure virtual host pointing to public/
```

### 3. Upload ke Pico W

1. Buka Arduino IDE
2. Pilih File → Open → `arduino/pico_smart_gateway.ino`
3. Tools → Board → Raspberry Pi Pico W
4. Tools → Port → (Pilih port Pico W)
5. Upload (Ctrl+U)
6. Buka Serial Monitor (Ctrl+Shift+M)

### 4. Verify

- [ ] Server running tanpa error
- [ ] Pico W connect ke WiFi
- [ ] Data masuk database setiap 10 detik
- [ ] Dashboard menampilkan data real-time
- [ ] Settings dapat mengubah mode dan threshold
- [ ] Pico W update config otomatis

---

## 📊 Monitoring

### Laravel Logs

```bash
tail -f storage/logs/laravel.log
```

### Pico W Serial Monitor

```
Arduino IDE → Tools → Serial Monitor (115200 baud)
```

### Database

```sql
-- Cek data terbaru
SELECT * FROM monitorings ORDER BY created_at DESC LIMIT 10;

-- Cek device settings
SELECT * FROM device_settings;
```

---

## 📝 Next Steps After Deployment

1. [ ] Monitor selama 24 jam untuk stabilitas
2. [ ] Test semua mode kontrol
3. [ ] Verify auto-reconnect WiFi works
4. [ ] Test kalibrasi sensor
5. [ ] Document any issues found
6. [ ] Optimize SEND_INTERVAL if needed
7. [ ] Add more sensors jika diperlukan

---

**Last Updated:** 10 Januari 2026

**Status:** ✅ **READY FOR TESTING**

Sistem komunikasi 2 arah sudah lengkap dan siap untuk di-test!
