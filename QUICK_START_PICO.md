# ⚡ QUICK START - UPLOAD PICO W

## ⚠️ IMPORTANT: Configuration Required First!

Before following these steps, you MUST configure your WiFi credentials and server URL:
- Copy `config.example.h` to `config.h` (Arduino)
- OR copy `config.example.py` to `config.py` (MicroPython)
- Edit with YOUR WiFi name, password, and server IP

See: **CONFIGURATION_GUIDE.md** for detailed instructions

---

## 🎯 5 MENIT SETUP

### 1️⃣ INSTALL LIBRARIES (di Arduino IDE)
Tools → Manage Libraries → Install:
- ✅ ArduinoJson
- ✅ DHT sensor library (+ Install ALL dependencies)
- ✅ NTPClient

### 2️⃣ CREATE CONFIG FILE
- Copy `arduino/config.example.h` → `arduino/config.h`
- Edit `config.h` with your WiFi & Server details
- Save file

### 3️⃣ BUKA FILE
File → Open → `arduino/pico_smart_gateway.ino`

### 4️⃣ PILIH BOARD & PORT
- Tools → Board → Raspberry Pi Pico W
- Tools → Port → COM# (your port)

### 5️⃣ UPLOAD
- Click ✓ (Verify) → Wait
- Click → (Upload) → Wait
- Done!

### 6️⃣ MONITOR
- Tools → Serial Monitor
- Set: 115200 baud
- Lihat: WiFi Connected + Data Sent

---

## 🌐 AKSES DASHBOARD
Browser → http://127.0.0.1:8000

---

## 🆘 TROUBLESHOOTING CEPAT

**Config file not found?**
→ Copy `config.example.h` to `config.h` and edit with your details

**Upload Failed?**
→ Tekan BOOTSEL sambil colok USB, upload lagi

**WiFi Failed?**
→ Cek credentials di config.h dan pastikan WiFi 2.4GHz

**HTTP Error?**
→ Server Laravel harus running dan IP address benar

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

## 📚 DETAILED GUIDES

For complete instructions:
- **PICO_CONFIGURATION_CHECKLIST.md** - Step-by-step checklist
- **CONFIGURATION_GUIDE.md** - Comprehensive setup guide  
- **PANDUAN_UPLOAD_PICO_W.md** - Detailed Indonesian guide
