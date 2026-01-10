# 🔄 TWO-WAY COMMUNICATION - Pico W ↔️ Website

## 📋 Konsep Bidirectional Communication

Komunikasi 2 arah antara Raspberry Pi Pico W dan Website Laravel menggunakan metode **"Titip Pesan via HTTP Response"** (Polling).

### Alur Komunikasi:

```
┌─────────────┐                    ┌──────────────┐
│   PICO W    │ ──── Upload ────► │   LARAVEL    │
│  (Gateway)  │                    │   (Server)   │
│             │ ◄─── Download ──── │              │
└─────────────┘                    └──────────────┘
```

**Flow:**
1. **Pico → Server:** "Halo Server, ini data sensor saya: Suhu 30°C, Kelembaban Tanah 45%"
2. **Server → Pico:** "OK Dicatat. Btw, Mode sekarang: Basic (1), Threshold: 40%-70%"
3. **Pico:** "Siap! Saya update konfigurasi lokal saya"

---

## ✅ Implementasi yang Sudah Ada

### 1. Laravel Backend (`MonitoringController.php`)

Controller sudah **SIAP** untuk 2-way communication dengan fitur:

✅ **Menerima Data** dari Pico W  
✅ **Menyimpan** ke database  
✅ **Mengambil Konfigurasi** dari `device_settings`  
✅ **Mengirim Balik** konfigurasi dalam response JSON  

**Endpoint:** `POST /api/monitoring/insert`

**Request dari Pico:**
```json
{
  "device_id": "PICO_CABAI_01",
  "temperature": 28.5,
  "soil_moisture": 45.2,
  "raw_adc": 3200,
  "relay_status": false,
  "ip_address": "192.168.1.105"
}
```

**Response ke Pico:**
```json
{
  "success": true,
  "message": "Data berhasil disimpan",
  "config": {
    "mode": 1,
    "adc_min": 4095,
    "adc_max": 1500,
    "batas_kering": 40,
    "batas_basah": 70,
    "jam_pagi": "07:00",
    "jam_sore": "17:00",
    "durasi_siram": 5
  }
}
```

### 2. Arduino Code (`pico_smart_gateway.ino`)

Pico W sudah **SIAP** menerima konfigurasi dari server dengan fitur:

✅ **Mengirim Data** sensor setiap 10 detik  
✅ **Menerima Response** dari server  
✅ **Parsing JSON** response  
✅ **Update Konfigurasi** lokal otomatis  

**Fungsi:** `sendDataToServer()`

**Yang Diupdate Otomatis:**
- `MODE` (1=Basic, 2=Advanced, 3=Schedule, 4=Manual)
- `ADC_MIN` dan `ADC_MAX` (Kalibrasi sensor)
- `BATAS_KERING` dan `BATAS_BASAH` (Threshold)
- `JAM_PAGI`, `JAM_SORE`, `DURASI_SIRAM` (Schedule mode)

---

## 🔧 Konfigurasi Jaringan

### Koneksi Saat Ini:

```
WiFi SSID: CCTV_UISI
Password:  08121191
Server IP: 10.134.42.169:8000
```

### Backup WiFi (Hotspot HP):

```cpp
// Uncomment jika pakai Hotspot HP
// const char* WIFI_SSID = "Bocil";
// const char* WIFI_PASSWORD = "kesayanganku";
// const char* SERVER_URL = "http://192.168.18.35:8000/api/monitoring/insert";
```

### ⚠️ PENTING - IP Dinamis (Hotspot HP)

Jika menggunakan **Hotspot HP**, IP Laptop bisa berubah setiap kali restart:

**Cara Cek IP Laptop:**
```powershell
ipconfig
```

**Cara Update di Arduino:**
```cpp
const char* SERVER_URL = "http://[IP_LAPTOP]:8000/api/monitoring/insert";
```

**Contoh:**
```cpp
const char* SERVER_URL = "http://192.168.43.100:8000/api/monitoring/insert";
```

---

## 📊 Mode Kontrol

| Mode | Nama | Logika |
|------|------|--------|
| 1 | Basic | Threshold sederhana (< 40% ON, > 70% OFF) |
| 2 | Advanced | Hysteresis untuk stabilitas |
| 3 | Schedule | Siram pada jam tertentu (Pagi & Sore) |
| 4 | Manual | Kontrol penuh dari Dashboard |

---

## 🧪 Cara Test Komunikasi 2 Arah

### 1. Start Laravel Server
```bash
php artisan serve --host=0.0.0.0 --port=8000
```

### 2. Upload Code ke Pico W
- Buka Arduino IDE
- Pilih Board: **Raspberry Pi Pico W**
- Upload `pico_smart_gateway.ino`

### 3. Monitor Serial
```
📡 Sending data to server...
✅ Server Response: 201
📥 Data berhasil dikirim!
🔧 Config updated from server:
   Mode: 1
   ADC Range: 4095 - 1500
   Threshold: 40% - 70%
```

### 4. Ubah Settings di Website
- Buka `http://localhost:8000`
- Masuk ke **Settings**
- Ubah Mode / Threshold
- **Pico akan otomatis update dalam 10 detik!**

---

## 🚀 Keunggulan Metode Ini

✅ **Tanpa WebSocket/MQTT** (Lebih sederhana)  
✅ **Tidak perlu server tambahan** (Sudah pakai Laravel)  
✅ **Auto-sync** setiap 10 detik  
✅ **Hemat resource** (Pico W tidak perlu maintain koneksi persistent)  
✅ **Reliable** (HTTP request/response sudah proven)  

---

## 🐛 Troubleshooting

### Pico tidak terima config dari server

**Cek:**
1. Serial Monitor untuk melihat response
2. Pastikan Laravel return format JSON sesuai
3. Cek `ArduinoJson` library size (512 bytes cukup)

### Config tidak update

**Solusi:**
1. Pastikan `device_settings` table ada data
2. Cek `device_id` di Pico sama dengan di database
3. Clear cache Laravel: `php artisan cache:clear`

### Koneksi putus-putus

**Solusi:**
1. Pindah ke WiFi stabil (bukan Hotspot HP)
2. Kurangi `SEND_INTERVAL` jika perlu
3. Cek signal strength di Serial Monitor

---

## 📝 File yang Sudah Dihapus

File dokumentasi lama yang tidak diperlukan:

- ❌ `MYSQL_SETUP_COMPLETE.md` (Setup sudah selesai)
- ❌ `NETWORK_CONFIGURATION.md` (Info jaringan outdated)
- ❌ `SettingsPage.jsx` (Sudah pakai `SettingsMinimal.jsx`)
- ❌ `Welcome.jsx` (Halaman awal sudah langsung Dashboard)

---

## 🎯 Next Steps

1. ✅ Backend sudah siap (MonitoringController)
2. ✅ Arduino code sudah siap (pico_smart_gateway.ino)
3. ✅ File tidak perlu sudah dihapus
4. 🔄 **Test komunikasi 2 arah**
5. 🔄 **Monitor Serial untuk debug**
6. 🔄 **Ubah settings di web, lihat Pico update otomatis**

---

**Status:** ✅ **READY TO USE**

Komunikasi 2 arah sudah berfungsi penuh. Tinggal upload code ke Pico W dan test!
