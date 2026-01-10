# ⚡ QUICK START - UPLOAD PICO W

## 🎯 5 MENIT SETUP

### 1️⃣ INSTALL LIBRARIES (di Arduino IDE)
Tools → Manage Libraries → Install:
- ✅ ArduinoJson
- ✅ DHT sensor library (+ Install ALL dependencies)
- ✅ NTPClient

### 2️⃣ BUKA FILE
File → Open → `C:\xampp\htdocs\Smart Garden IoT\arduino\pico_smart_gateway.ino`

### 3️⃣ PILIH BOARD & PORT
- Tools → Board → Raspberry Pi Pico W
- Tools → Port → COM8

### 4️⃣ UPLOAD
- Click ✓ (Verify) → Wait
- Click → (Upload) → Wait
- Done!

### 5️⃣ MONITOR
- Tools → Serial Monitor
- Set: 115200 baud
- Lihat: WiFi Connected + Data Sent

---

## 🌐 AKSES DASHBOARD
Browser → http://127.0.0.1:8000

---

## 🆘 TROUBLESHOOTING CEPAT

**Upload Failed?**
→ Tekan BOOTSEL sambil colok USB, upload lagi

**WiFi Failed?**
→ Cek password "kesayanganku" dan WiFi 2.4GHz

**HTTP Error?**
→ Server Laravel harus running di VS Code terminal

---

## ✅ SUCCESS INDICATOR

Serial Monitor harus menampilkan:
```
✅ WiFi Connected!
📡 IP Address: 192.168.18.xxx
✅ Server Response Code: 201
```

Dashboard harus menampilkan:
```
Device: PICO_CABAI_01
Status: ONLINE
Data: Updating setiap 10 detik
```

---

Baca panduan lengkap: `PANDUAN_UPLOAD_PICO_W.md`
