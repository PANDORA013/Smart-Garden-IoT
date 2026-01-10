# 🐍 PANDUAN MENGGUNAKAN THONNY IDE DENGAN PICO W

## 📋 PERSIAPAN AWAL

### 1. Install Thonny IDE
- Download dari: https://thonny.org/
- Install versi terbaru (Windows)
- Buka Thonny setelah install

### 2. Install MicroPython di Pico W

**PENTING: Lakukan sekali saja saat pertama kali setup**

#### Langkah Install MicroPython:

1. **Download Firmware MicroPython untuk Pico W:**
   - Buka: https://micropython.org/download/rp2-pico-w/
   - Download file `.uf2` terbaru (contoh: `rp2-pico-w-20240602-v1.23.0.uf2`)

2. **Masukkan Pico W ke BOOTSEL Mode:**
   - Tekan dan tahan tombol **BOOTSEL** di Pico W
   - Sambil tetap menekan, colokkan kabel USB ke komputer
   - Lepas tombol BOOTSEL
   - Pico W akan muncul sebagai drive **RPI-RP2** di File Explorer

3. **Copy Firmware:**
   - Drag & drop file `.uf2` ke drive **RPI-RP2**
   - Pico W akan restart otomatis
   - Drive RPI-RP2 akan hilang (ini normal!)

4. **Setting Thonny:**
   - Buka Thonny
   - Klik menu: **Run** → **Select Interpreter**
   - Pilih: **MicroPython (Raspberry Pi Pico)**
   - Port: Pilih COM port Pico W Anda (contoh: COM5)
   - Klik **OK**

5. **Test Koneksi:**
   - Di Shell Thonny (bawah), tekan **Ctrl+C** untuk stop program
   - Ketik: `print("Hello Pico W!")`
   - Tekan **Enter**
   - Jika muncul output, koneksi berhasil! ✅

---

## 🚀 CARA UPLOAD CODE KE PICO W

### Opsi 1: Upload sebagai `main.py` (Auto-Run saat Power ON)

**File `main.py` akan otomatis berjalan setiap kali Pico W dinyalakan**

1. **Buka File di Thonny:**
   - File → Open
   - Pilih: `c:\xampp\htdocs\Smart Garden IoT\arduino\pico_micropython.py`

2. **Save ke Pico W:**
   - File → **Save as...**
   - Pilih: **Raspberry Pi Pico** (BUKAN "This computer")
   - Nama file: `main.py` (harus persis seperti ini!)
   - Klik **OK**

3. **Test Run:**
   - Klik tombol **Run** (▶️) atau tekan **F5**
   - Lihat output di Shell (bawah)
   - Harus muncul:
     ```
     🌱 PICO W SMART GARDEN (MicroPython)
     🔌 Connecting to WiFi: Bocil
     ✅ WiFi Connected!
     📡 IP Address: 192.168.18.xxx
     ```

4. **Reset Pico W:**
   - Unplug & plug kembali USB
   - Atau tekan tombol reset di Pico W
   - Program akan auto-run!

---

### Opsi 2: Upload dengan Nama Custom (Manual Run)

Jika ingin simpan dengan nama lain:

1. File → Save as → Raspberry Pi Pico
2. Nama file: `smart_garden.py` (atau nama lain)
3. Untuk run: Buka file di Thonny, klik Run

---

## 🔧 KONFIGURASI JARINGAN (SUDAH SIAP PAKAI)

File `pico_micropython.py` sudah dikonfigurasi dengan:

```python
SSID = "Bocil"
PASSWORD = "kesayanganku"
SERVER_URL = "http://192.168.18.35:8000/api/monitoring/insert"
DEVICE_ID = "PICO_CABAI_01"
```

✅ **Tidak perlu diubah lagi!** (Kecuali WiFi atau IP server berubah)

---

## 📊 MONITORING PICO W DI THONNY

### Shell Output yang Normal:

```
========================================
🌱 PICO W SMART GARDEN (MicroPython)
========================================
🔌 Connecting to WiFi: Bocil..........
✅ WiFi Connected!
📡 IP Address: 192.168.18.100
✅ Setup Complete!
========================================

📊 Temp: 28.5°C | Soil: 65.3% | Pump: OFF

📤 Sending data to server...
   Temp: 28.5°C | Soil: 65.3% | Pump: False
✅ Server Response: 200
📥 Data berhasil dikirim!

📊 Temp: 28.6°C | Soil: 64.8% | Pump: OFF
...
```

### Indikator Sukses:
- ✅ `WiFi Connected` - Koneksi WiFi berhasil
- ✅ `Server Response: 200` - Data terkirim ke server
- ✅ `Data berhasil dikirim!` - Database updated

---

## ⚠️ TROUBLESHOOTING THONNY

### Problem 1: "Device is busy or does not respond"

**Solusi:**
1. Tekan **Ctrl+C** di Shell untuk stop program
2. Jika masih busy, klik **Stop** (⏹️) di toolbar
3. Atau unplug & plug USB kembali

---

### Problem 2: WiFi tidak connect

**Gejala:**
```
🔌 Connecting to WiFi: Bocil....................
❌ WiFi Connection Failed!
```

**Solusi:**
1. Cek SSID & Password di code (line 27-28)
2. Pastikan WiFi 2.4GHz (Pico W tidak support 5GHz)
3. Cek jarak Pico W ke router
4. Restart router WiFi

---

### Problem 3: Server tidak bisa diakses

**Gejala:**
```
❌ Upload Error: [Errno 113] EHOSTUNREACH
```

**Solusi:**
1. Pastikan Laravel server running:
   ```powershell
   cd "c:\xampp\htdocs\Smart Garden IoT"
   php artisan serve --host=0.0.0.0 --port=8000
   ```

2. Cek IP address komputer:
   ```powershell
   ipconfig
   ```
   Harus `192.168.18.35` (sesuai konfigurasi)

3. Cek firewall Windows tidak block port 8000

4. **Test dari PowerShell:**
   ```powershell
   cd "c:\xampp\htdocs\Smart Garden IoT"
   .\test-picow-connection.ps1
   ```

---

### Problem 4: DHT22 Error

**Gejala:**
```
⚠️ DHT22 Error: [Errno 5] EIO
```

**Solusi:**
1. Cek kabel DHT22 di GPIO 2
2. DHT22 butuh resistor pull-up 10kΩ
3. Code akan pakai default value (28°C) jika sensor error
4. Ganti sensor jika rusak

---

### Problem 5: Sensor Tanah tidak akurat

**Solusi - Kalibrasi Sensor:**

1. Edit file di Thonny, cari fungsi `map_adc()` (line 69):

```python
def map_adc(val):
    # Kalibrasi: Sesuaikan dengan sensor Anda
    min_val = 65535  # ← Sensor di UDARA (kering)
    max_val = 20000  # ← Sensor di AIR (basah)
```

2. **Cara Kalibrasi:**
   - **Test Kering:** Angkat sensor dari tanah, catat nilai `raw_adc`
     → Update `min_val` dengan nilai ini
   
   - **Test Basah:** Celupkan sensor ke air, catat nilai `raw_adc`
     → Update `max_val` dengan nilai ini

3. Save & run ulang

---

## 🎛️ MENGUBAH KONFIGURASI

### Ubah Threshold Pompa:

Edit line 42-43:
```python
BATAS_KERING = 40   # Pompa ON jika < 40%
BATAS_BASAH = 70    # Pompa OFF jika >= 70%
```

Contoh untuk tanaman yang butuh air lebih banyak:
```python
BATAS_KERING = 50   # Pompa ON lebih cepat
BATAS_BASAH = 80    # Pompa OFF lebih lambat
```

### Ubah Interval Kirim Data:

Edit line 172:
```python
time.sleep(10)  # ← Ubah angka ini (dalam detik)
```

Contoh kirim setiap 30 detik:
```python
time.sleep(30)
```

---

## 🛠️ FILE MANAGEMENT DI PICO W

### Lihat File di Pico W:

1. Di Thonny, klik tab **Files** (View → Files)
2. Panel kiri: File di komputer
3. Panel kanan: File di Pico W

### Hapus File di Pico W:

1. Klik kanan file di panel **Raspberry Pi Pico**
2. Pilih **Delete**

### Backup File dari Pico W:

1. Klik kanan file di panel **Raspberry Pi Pico**
2. Pilih **Download to** → Pilih folder di komputer

---

## 📝 TIPS & BEST PRACTICES

### ✅ DO:
- Selalu **stop program** (Ctrl+C) sebelum unplug USB
- Save code sebagai `main.py` untuk auto-run
- Monitor Shell output untuk debug
- Test WiFi & server terlebih dahulu

### ❌ DON'T:
- Jangan unplug USB saat program running
- Jangan lupa start Laravel server
- Jangan pakai WiFi 5GHz
- Jangan lupa kalibrasi sensor

---

## 🔄 WORKFLOW NORMAL

1. **Sebelum Upload:**
   ```powershell
   # Start Laravel Server
   cd "c:\xampp\htdocs\Smart Garden IoT"
   php artisan serve --host=0.0.0.0 --port=8000
   ```

2. **Upload & Test:**
   - Buka Thonny
   - Open: `pico_micropython.py`
   - Save as → Raspberry Pi Pico → `main.py`
   - Klik Run (F5)
   - Monitor output di Shell

3. **Verify:**
   - Cek WiFi connected
   - Cek data terkirim (Status 200)
   - Buka dashboard: http://192.168.18.35:8000
   - Lihat data real-time

4. **Deploy:**
   - Unplug Pico W dari komputer
   - Pasang ke power supply (USB charger)
   - Pico W akan auto-run `main.py`

---

## 📞 QUICK REFERENCE

### Shortcut Thonny:
- **F5** - Run program
- **Ctrl+C** - Stop program
- **Ctrl+D** - Soft reboot Pico W
- **Ctrl+F** - Hard reboot Pico W

### Terminal Laravel:
```powershell
# Start server
php artisan serve --host=0.0.0.0 --port=8000

# Test koneksi
.\test-picow-connection.ps1

# Cek database
php artisan tinker
>>> DB::table('monitorings')->count()
>>> DB::table('monitorings')->latest()->first()
```

### Dashboard:
- URL: http://192.168.18.35:8000
- Update setiap 10 detik
- Grafik real-time

---

## 🎉 CHECKLIST READY TO USE

- [ ] MicroPython firmware installed di Pico W
- [ ] Thonny IDE configured (interpreter = MicroPython Pico)
- [ ] File `pico_micropython.py` uploaded as `main.py`
- [ ] WiFi connected (lihat IP address di Shell)
- [ ] Laravel server running (`php artisan serve --host=0.0.0.0`)
- [ ] Data terkirim ke server (Status 200)
- [ ] Dashboard menampilkan data (http://192.168.18.35:8000)
- [ ] Sensor DHT22 & soil working
- [ ] Relay/pompa responding

**Jika semua ✅, sistem siap digunakan!** 🚀

---

## 📚 RESOURCES

- MicroPython Docs: https://docs.micropython.org/
- Pico W Docs: https://www.raspberrypi.com/documentation/microcontrollers/
- Thonny Tutorial: https://thonny.org/
- Laravel Docs: https://laravel.com/docs

---

**Happy Coding! 🌱**
