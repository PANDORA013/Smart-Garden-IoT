# 🌱 PANDUAN KONEKSI PICO W KE SERVER & WEBSITE

## 📡 Konfigurasi Jaringan (Sudah Terupdate)

### WiFi Settings:
- **SSID:** `Bocil`
- **Password:** `kesayanganku`

### Server Settings:
- **IP Address:** `192.168.18.35`
- **Port:** `8000`
- **API Endpoint:** `http://192.168.18.35:8000/api/monitoring/insert`
- **Device ID:** `PICO_CABAI_01`

---

## 🚀 CARA MENJALANKAN SISTEM

### **Opsi 1: Arduino IDE (Recommended - Full Features)**

#### Persiapan:
1. Install **Arduino IDE** (https://www.arduino.cc/en/software)
2. Install **Arduino-Pico Core**:
   - File → Preferences
   - Additional Board Manager URLs: `https://github.com/earlephilhower/arduino-pico/releases/download/global/package_rp2040_index.json`
   - Tools → Board → Boards Manager → Cari "Pico" → Install "Raspberry Pi Pico/RP2040"

3. Install Library:
   - Sketch → Include Library → Manage Libraries
   - Install:
     - `ArduinoJson` by Benoit Blanchon
     - `DHT sensor library` by Adafruit
     - `NTPClient` by Fabrice Weinberg

#### Upload ke Pico W:
1. Buka file: `arduino/pico_smart_gateway.ino`
2. Pilih Board:
   - Tools → Board → Raspberry Pi Pico/RP2040 → **Raspberry Pi Pico W**
3. Pilih Port:
   - Tools → Port → (pilih COM port Pico W Anda)
4. Upload:
   - Klik tombol **Upload** (→)
5. Buka Serial Monitor:
   - Tools → Serial Monitor (atau Ctrl+Shift+M)
   - Set Baud Rate: **115200**

#### Fitur Arduino (Full):
✅ 3 Mode Kontrol (Threshold, Fuzzy Logic, Schedule)
✅ 2-Way Communication (Terima config dari server)
✅ Auto-Provisioning
✅ Kalibrasi ADC dinamis
✅ NTP Time Sync

---

### **Opsi 2: Thonny IDE (MicroPython - Simple)**

#### Persiapan:
1. Install **Thonny** (https://thonny.org/)
2. Install **MicroPython** di Pico W:
   - Download firmware: https://micropython.org/download/rp2-pico-w/
   - Tekan tombol BOOTSEL di Pico W sambil colok USB
   - Copy file `.uf2` ke drive RPI-RP2

3. Setting Thonny:
   - Run → Select Interpreter → **MicroPython (Raspberry Pi Pico)**
   - Pilih Port COM Pico W

#### Upload ke Pico W:
1. Buka file: `arduino/pico_micropython.py`
2. Klik **File → Save as** → Pilih **Raspberry Pi Pico**
3. Simpan dengan nama: `main.py` (akan auto-run saat power on)
4. Klik **Run** (F5) untuk test

#### Fitur MicroPython (Simple):
✅ Kirim data ke server
✅ Kontrol pompa otomatis (Mode Threshold)
✅ Baca sensor DHT22 & Soil Moisture

---

## 🖥️ MENJALANKAN LARAVEL SERVER

### 1. Pastikan XAMPP/Laravel Berjalan:
```powershell
# Masuk ke folder project
cd "c:\xampp\htdocs\Smart Garden IoT"

# Jalankan Laravel Server
php artisan serve --host=0.0.0.0 --port=8000
```

**PENTING:** Gunakan `--host=0.0.0.0` agar server bisa diakses dari Pico W!

### 2. Cek IP Address Komputer Anda:
```powershell
ipconfig
```
Cari **IPv4 Address** di adapter WiFi/Ethernet Anda.
**Pastikan IP-nya `192.168.18.35`** (sesuai konfigurasi).

Jika berbeda, update:
- File Arduino: `pico_smart_gateway.ino` (line 35)
- File MicroPython: `pico_micropython.py` (line 26)

### 3. Test API Manual:
```powershell
# Test dari PowerShell
Invoke-RestMethod -Uri "http://192.168.18.35:8000/api/monitoring/insert" -Method POST -ContentType "application/json" -Body '{"device_id":"TEST","temperature":25,"soil_moisture":50,"raw_adc":3000,"relay_status":false}'
```

Jika berhasil, akan muncul response JSON.

---

## 🌐 AKSES WEBSITE DASHBOARD

### 1. Compile Frontend (Sekali saja):
```powershell
npm install
npm run build
```

### 2. Buka Browser:
```
http://localhost:8000
```
atau
```
http://192.168.18.35:8000
```

### 3. Dashboard akan menampilkan:
- ✅ Real-time data dari Pico W (update setiap 10 detik)
- ✅ Grafik suhu & kelembaban
- ✅ Status pompa (ON/OFF)
- ✅ Kontrol manual & konfigurasi mode

---

## 🔍 TROUBLESHOOTING

### ❌ Pico W tidak bisa konek WiFi:
- Cek SSID & Password sudah benar
- Pastikan WiFi 2.4GHz (Pico W tidak support 5GHz)
- Cek jarak Pico W ke router

### ❌ Pico W konek WiFi tapi error kirim data:
- Cek Laravel server berjalan: `php artisan serve --host=0.0.0.0`
- Cek IP address server sesuai: `ipconfig`
- Cek firewall Windows tidak block port 8000
- Test manual dengan `curl` atau Postman

### ❌ Website tidak menampilkan data:
- Cek database: `database/database.sqlite`
- Cek tabel `monitorings`: `php artisan tinker` → `DB::table('monitorings')->count()`
- Cek log Laravel: `storage/logs/laravel.log`
- Refresh browser (Ctrl+F5)

### ❌ Sensor DHT22 error:
- Cek koneksi pin (GPIO 2)
- Cek library DHT terinstall
- Ganti sensor jika rusak (code akan pakai default value 28°C)

---

## 📊 FLOW DATA (Pico W → Server → Website)

```
┌─────────────┐
│  PICO W     │
│  (Hardware) │
└──────┬──────┘
       │ 1. Baca Sensor (setiap 1 detik)
       │ 2. Kontrol Pompa
       │ 3. Kirim Data (setiap 10 detik)
       ▼
┌─────────────────────────┐
│  HTTP POST              │
│  192.168.18.35:8000     │
│  /api/monitoring/insert │
└──────┬──────────────────┘
       │ 4. Laravel Controller
       │    - Validasi data
       │    - Simpan ke database
       │    - Kirim config balik
       ▼
┌─────────────┐
│  Database   │
│  (SQLite)   │
└──────┬──────┘
       │ 5. Query data
       ▼
┌─────────────┐
│  Website    │
│  Dashboard  │
└─────────────┘
```

---

## 📝 CATATAN PENTING

1. **Pico W dan Komputer (Server) HARUS di jaringan WiFi yang SAMA**
2. **IP Address server (`192.168.18.35`) harus STATIS atau update konfigurasi jika berubah**
3. **Port 8000 harus terbuka (tidak diblock firewall)**
4. **Interval kirim data: 10 detik (bisa diubah di code)**

---

## 🎯 VALIDASI SISTEM BERJALAN

### ✅ Checklist:
- [ ] Pico W konek WiFi (LED berkedip, Serial Monitor tampil IP)
- [ ] Laravel server running (`php artisan serve --host=0.0.0.0`)
- [ ] Pico W kirim data (Serial Monitor: "✅ Server Response: 200")
- [ ] Database bertambah record (cek tabel `monitorings`)
- [ ] Website dashboard menampilkan data real-time
- [ ] Pompa menyala/mati otomatis sesuai kelembaban

Jika semua checklist ✅, sistem berhasil! 🎉

---

## 📞 KONTAK SUPPORT

Jika ada masalah, screenshot error dan kirim:
- Serial Monitor output (dari Pico W)
- Laravel log (`storage/logs/laravel.log`)
- Browser console error (F12)
