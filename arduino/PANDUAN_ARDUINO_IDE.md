# 📘 PANDUAN ARDUINO IDE - pico_smart_gateway.ino

**File:** `C:\xampp\htdocs\Smart Garden IoT\arduino\pico_smart_gateway.ino`
**Tanggal:** 10 Januari 2026
**Status:** ✅ Ready to Upload

---

## 🎯 **FITUR LENGKAP DALAM SATU FILE**

✅ **Konfigurasi WiFi CCTV_UISI** (Hardcoded, tidak perlu file terpisah)
✅ **DHT22 Sensor** (Temperature & Humidity)
✅ **Soil Moisture Sensor** (Capacitive ADC)
✅ **Relay Control** (Pompa Air)
✅ **HTTP Communication** dengan Laravel Server
✅ **2-Way Communication** (Terima config dari server)
✅ **4 Mode Kontrol** (Basic, Advanced, Schedule, Manual)
✅ **NTP Time Sync** untuk Schedule Mode
✅ **Auto-Reconnect WiFi**
✅ **Serial Monitoring** dengan emoji untuk debugging

---

## 📡 **KONFIGURASI JARINGAN (Baris 38-47)**

```cpp
// WiFi CCTV_UISI (AKTIF)
const char* WIFI_SSID = "CCTV_UISI";
const char* WIFI_PASSWORD = "08121191";
const char* SERVER_URL = "http://10.134.42.169:8000/api/monitoring/insert";
const char* DEVICE_ID = "PICO_CABAI_01";

// Backup WiFi Bocil (comment/uncomment untuk switch)
// const char* WIFI_SSID = "Bocil";
// const char* WIFI_PASSWORD = "kesayanganku";
// const char* SERVER_URL = "http://192.168.18.35:8000/api/monitoring/insert";
```

**CARA GANTI WiFi:**
1. Comment baris WiFi aktif (tambahkan `//` di depan)
2. Uncomment baris WiFi backup (hapus `//`)
3. Upload ulang ke Pico W

---

## 🔌 **HARDWARE PINOUT**

| Pin | Fungsi | Keterangan |
|-----|--------|------------|
| GPIO 26 (ADC0) | Soil Moisture Sensor | Capacitive sensor analog |
| GPIO 2 | DHT22 Data | Temperature & Humidity |
| GPIO 5 | Relay | Kontrol pompa air |
| VCC (3.3V) | Power | Semua sensor |
| GND | Ground | Common ground |

---

## 🚀 **CARA UPLOAD KE PICO W (Arduino IDE)**

### **Prerequisites:**

1. **Install Arduino IDE 2.x**
   - Download: https://www.arduino.cc/en/software

2. **Install Raspberry Pi Pico Board**
   ```
   File → Preferences → Additional Board Manager URLs
   Add: https://github.com/earlephilhower/arduino-pico/releases/download/global/package_rp2040_index.json
   
   Tools → Board → Boards Manager
   Search: "pico"
   Install: "Raspberry Pi Pico/RP2040" by Earle F. Philhower III
   ```

3. **Install Required Libraries**
   ```
   Tools → Manage Libraries
   
   Install:
   - DHT sensor library (by Adafruit)
   - Adafruit Unified Sensor
   - ArduinoJson (by Benoit Blanchon)
   - NTPClient (by Fabrice Weinberg)
   ```

---

### **Upload Steps:**

#### **1. Open File**
```
File → Open
Navigate to: C:\xampp\htdocs\Smart Garden IoT\arduino\pico_smart_gateway.ino
```

#### **2. Select Board**
```
Tools → Board → Raspberry Pi Pico/RP2040 → Raspberry Pi Pico W
```

#### **3. Configure Board Settings**
```
Tools → CPU Speed → 133 MHz
Tools → Optimize → Small (-Os)
Tools → Flash Size → 2MB (Sketch: 1MB, FS: 1MB)
```

#### **4. Connect Pico W**
- Tekan dan tahan tombol **BOOTSEL** pada Pico W
- Sambil tahan, colok USB ke komputer
- Lepas tombol BOOTSEL
- Pico W akan muncul sebagai USB Drive

#### **5. Select Port**
```
Tools → Port → [Pilih COM port Pico W]
```

#### **6. Upload**
```
Sketch → Upload
atau klik tombol Upload (→)
```

#### **7. Monitor Serial**
```
Tools → Serial Monitor
Baud Rate: 115200
```

**Expected Output:**
```
═══════════════════════════════════════════════════════════
   🌱 RASPBERRY PI PICO W - SMART GARDEN IoT GATEWAY
═══════════════════════════════════════════════════════════

🔧 Initializing Hardware...
   ✅ DHT22 Sensor initialized
   ✅ Soil Moisture Sensor initialized
   ✅ Relay initialized (Pump OFF)

📡 Connecting to WiFi: CCTV_UISI
   Password: 08121191

⏳ Attempt 1/20...
⏳ Attempt 2/20...

✅ WiFi Connected! 📡
   IP Address: 192.168.x.x
   Signal Strength: -45 dBm

═══════════════════════════════════════════════════════════
✅ SYSTEM READY!
═══════════════════════════════════════════════════════════

📊 SENSOR DATA:
   🌡️  Temperature: 28.5°C
   💧 Humidity: 65%
   🌱 Soil Moisture: 75% (ADC: 2500)
   💦 Pump Status: OFF 🔴

📡 Sending data to server...
   URL: http://10.134.42.169:8000/api/monitoring/insert
   Payload: {"device_id":"PICO_CABAI_01",...}

✅ Server Response: 201
📥 Data berhasil dikirim!
```

---

## 🔧 **KONFIGURASI SENSOR (Baris 68-76)**

### **Kalibrasi ADC Soil Sensor:**

```cpp
int ADC_MIN = 4095;      // ADC saat tanah KERING
int ADC_MAX = 1500;      // ADC saat tanah BASAH
```

**Cara Kalibrasi:**
1. Upload sketch ke Pico W
2. Buka Serial Monitor
3. **Test KERING:** Sensor di udara → catat ADC value → update ADC_MIN
4. **Test BASAH:** Sensor di air → catat ADC value → update ADC_MAX
5. Upload ulang

### **Threshold Kontrol:**

```cpp
int BATAS_KERING = 40;   // < 40% → Pompa ON
int BATAS_BASAH = 70;    // > 70% → Pompa OFF
```

**Note:** Setting ini akan di-override oleh server saat first run (2-way communication).

---

## 🎮 **MODE KONTROL**

### **Mode 1: BASIC**
- Threshold sederhana
- Soil < 40% → Pompa ON
- Soil > 70% → Pompa OFF

### **Mode 2: ADVANCED**
- Hysteresis untuk stabilitas
- Soil < 35% → Pompa ON
- Soil > 75% → Pompa OFF
- Zone 35-75% → Maintain state

### **Mode 3: SCHEDULE**
- Siram pada waktu tertentu
- Default: 07:00 (pagi) & 17:00 (sore)
- Durasi: 5 menit

### **Mode 4: MANUAL**
- Kontrol dari dashboard
- No automatic control

**Ganti Mode:** Via dashboard web atau edit `MODE = 1` di baris 79.

---

## 🔄 **2-WAY COMMUNICATION**

### **Pico W → Server (POST):**
```json
{
  "device_id": "PICO_CABAI_01",
  "temperature": 28.5,
  "humidity": 65,
  "soil_moisture": 75,
  "raw_adc": 2500,
  "relay_status": false,
  "ip_address": "192.168.1.105"
}
```

### **Server → Pico W (Response):**
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

**Fungsi:** Pico W otomatis update konfigurasi sesuai setting di dashboard.

---

## 🧪 **TESTING**

### **Test 1: WiFi Connection**
```
Serial Monitor → Check:
✅ WiFi Connected! 📡
✅ IP Address ditampilkan
```

### **Test 2: Sensor Reading**
```
Serial Monitor → Check:
✅ Temperature value (bukan NaN)
✅ Humidity value (bukan NaN)
✅ Soil Moisture 0-100%
✅ ADC raw value 0-4095
```

### **Test 3: Server Communication**
```
Serial Monitor → Check:
✅ Server Response: 201
✅ Data berhasil dikirim!
```

### **Test 4: Relay Control**
```
Serial Monitor → Check:
⚡ RELAY ON ✅  (atau OFF ❌)
Physical: LED relay menyala/mati
```

---

## ⚠️ **TROUBLESHOOTING**

### **Problem 1: WiFi tidak connect**

**Symptoms:**
```
❌ WiFi Connection FAILED!
```

**Solutions:**
1. Check SSID & Password benar
2. Check WiFi signal strength (dekat router)
3. Check router aktif
4. Try restart Pico W (unplug-replug USB)

---

### **Problem 2: DHT22 Timeout**

**Symptoms:**
```
⚠️  DHT22 ERROR: Timeout reading sensor!
Using fallback values: 28°C, 60%
```

**Solutions:**
1. Check kabel DHT22 ke GPIO 2
2. Check VCC & GND terhubung
3. Tambahkan pull-up resistor 10kΩ (Data pin ke VCC)
4. Ganti sensor DHT22 (mungkin rusak)

**Note:** System tetap jalan dengan fallback value.

---

### **Problem 3: Server Connection Failed**

**Symptoms:**
```
❌ Connection failed! HTTP Error: -1
```

**Solutions:**
1. Check server IP benar (`ipconfig`)
2. Check Laravel server running:
   ```powershell
   php artisan serve --host=0.0.0.0 --port=8000
   ```
3. Check firewall port 8000:
   ```powershell
   .\check-network.ps1
   ```
4. Ping test:
   ```powershell
   ping 10.134.42.169
   ```

---

### **Problem 4: Soil Sensor Always 0% or 100%**

**Symptoms:**
```
🌱 Soil Moisture: 0% (ADC: 4095)
atau
🌱 Soil Moisture: 100% (ADC: 1500)
```

**Solutions:**
1. **Perlu Kalibrasi!** Update ADC_MIN dan ADC_MAX:
   - Test di udara (kering) → catat ADC
   - Test di air (basah) → catat ADC
   - Update baris 68-69
2. Check kabel sensor terhubung ke GPIO 26
3. Check sensor tidak rusak/korosi

---

## 🔀 **SWITCH WiFi**

### **Dari CCTV_UISI ke Bocil:**

Edit baris 38-47:

```cpp
// Comment WiFi CCTV_UISI:
// const char* WIFI_SSID = "CCTV_UISI";
// const char* WIFI_PASSWORD = "08121191";
// const char* SERVER_URL = "http://10.134.42.169:8000/api/monitoring/insert";

// Uncomment WiFi Bocil:
const char* WIFI_SSID = "Bocil";
const char* WIFI_PASSWORD = "kesayanganku";
const char* SERVER_URL = "http://192.168.18.35:8000/api/monitoring/insert";
```

Upload ulang ke Pico W.

---

## 📊 **MONITORING & DEBUGGING**

### **Serial Monitor Commands:**
```
- Baud Rate: 115200
- Line Ending: Both NL & CR
- Timestamp: Enable (optional)
```

### **Key Indicators:**
```
✅ = Success
❌ = Error
⚠️  = Warning
📡 = Network activity
🌡️  = Temperature
💧 = Humidity
🌱 = Soil moisture
💦 = Pump status
⚡ = Relay action
```

---

## 📚 **FILE PERBANDINGAN**

| File | Kelebihan | Kekurangan |
|------|-----------|------------|
| **pico_smart_gateway.ino** | ✅ All-in-one<br>✅ Mudah edit<br>✅ Arduino IDE friendly<br>✅ Library support lengkap | ❌ Perlu install Arduino IDE<br>❌ Perlu install libraries |
| **pico_micropython.py** | ✅ Thonny friendly<br>✅ Simple syntax<br>✅ Fast upload | ❌ Butuh network_config.py terpisah<br>❌ Limited libraries |

**Recommendation:** 
- **Arduino (.ino)** untuk production & features lengkap
- **MicroPython (.py)** untuk prototyping & testing cepat

---

## 🎯 **CHECKLIST SETUP**

- [ ] Install Arduino IDE 2.x
- [ ] Install Raspberry Pi Pico board support
- [ ] Install required libraries (DHT, ArduinoJson, NTPClient)
- [ ] Open pico_smart_gateway.ino
- [ ] Check WiFi credentials (CCTV_UISI / 08121191)
- [ ] Check server IP (10.134.42.169)
- [ ] Connect Pico W (BOOTSEL mode)
- [ ] Select correct board & port
- [ ] Upload sketch
- [ ] Open Serial Monitor (115200 baud)
- [ ] Verify WiFi connected
- [ ] Verify sensor readings
- [ ] Verify server communication
- [ ] Test relay control
- [ ] Open dashboard: http://10.134.42.169:8000

---

**Status:** ✅ Production Ready
**Last Update:** 10 Januari 2026
**WiFi:** CCTV_UISI
**Server:** 10.134.42.169:8000
