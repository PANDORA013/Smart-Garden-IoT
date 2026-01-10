# ✅ Pico W Configuration Checklist

Use this checklist to ensure your Raspberry Pi Pico W is properly configured for the Smart Garden IoT system.

---

## 📋 Pre-Configuration Checklist

Before starting configuration, ensure you have:

- [ ] **Laravel server running**
  ```bash
  php artisan serve --host=0.0.0.0 --port=8000
  ```
  
- [ ] **Your local IP address noted**
  - Windows: Run `ipconfig` in PowerShell
  - Mac/Linux: Run `ifconfig` or `ip addr`
  - Look for IPv4 address (e.g., 192.168.1.100)
  
- [ ] **WiFi network details**
  - Network name (SSID): _______________
  - Password: _______________
  - ⚠️ Must be 2.4GHz network (Pico W doesn't support 5GHz)
  
- [ ] **Device name chosen**
  - Unique ID for this Pico W: _______________
  - Examples: PICO_GARDEN_01, PICO_TOMATO_A, PICO_GREENHOUSE_01

---

## 🎯 Configuration Method Choice

Choose ONE method:

### Option A: Arduino (C++) ✅ Recommended for beginners
- [ ] Go to Arduino Configuration section below

### Option B: MicroPython (Python) ✅ More flexible
- [ ] Go to MicroPython Configuration section below

---

## 🔧 Arduino Configuration

### 1. Software Setup
- [ ] Arduino IDE 2.x installed from https://arduino.cc
- [ ] Raspberry Pi Pico board support installed
  - [ ] Board Manager URL added to preferences
  - [ ] "Raspberry Pi Pico/RP2040" package installed

### 2. Libraries Installation
In Arduino IDE, go to Tools → Manage Libraries and install:
- [ ] **ArduinoJson** (by Benoit Blanchon) - Any recent version
- [ ] **DHT sensor library** (by Adafruit)
  - [ ] Clicked "Install All" for dependencies
  - [ ] Adafruit Unified Sensor library installed
- [ ] **NTPClient** (by Fabrice Weinberg)

### 3. Configuration File
- [ ] Navigated to `arduino/` folder
- [ ] Copied `config.example.h` to `config.h`
- [ ] Opened `config.h` in text editor
- [ ] Updated `WIFI_SSID` with your WiFi name
- [ ] Updated `WIFI_PASSWORD` with your WiFi password
- [ ] Updated `SERVER_URL` with your server IP address
  - Format: `http://YOUR_IP:8000/api/monitoring/insert`
  - Example: `http://192.168.1.100:8000/api/monitoring/insert`
- [ ] Updated `DEVICE_ID` with unique device name
- [ ] Saved `config.h`

### 4. Code Compilation & Upload
- [ ] Opened `pico_smart_gateway.ino` in Arduino IDE
- [ ] Selected Board: **Tools → Board → Raspberry Pi Pico W** (with W!)
- [ ] Connected Pico W via USB
- [ ] Selected Port: **Tools → Port → COM# (USB Serial Device)**
- [ ] Verified board shows in status bar: "Raspberry Pi Pico W on COM#"
- [ ] Clicked **Verify (✓)** button - compilation successful
- [ ] Clicked **Upload (→)** button - upload successful
  - If failed: Used BOOTSEL method (hold button while plugging USB)

### 5. Testing & Verification
- [ ] Opened Serial Monitor: **Tools → Serial Monitor**
- [ ] Set baud rate to **115200**
- [ ] Saw message: `✅ WiFi Connected!`
- [ ] Saw message: `📡 IP Address: 192.168.x.x`
- [ ] Saw message: `✅ Server Response Code: 201`
- [ ] Noted Pico W's IP address: _______________

---

## 🐍 MicroPython Configuration

### 1. Firmware Installation
- [ ] Downloaded MicroPython firmware from https://micropython.org/download/rp2-pico-w/
- [ ] Held BOOTSEL button on Pico W
- [ ] Connected USB while holding BOOTSEL
- [ ] Pico W appeared as USB drive (RPI-RP2)
- [ ] Copied `.uf2` firmware file to drive
- [ ] Pico W rebooted automatically

### 2. IDE Setup
Choose one:

**Option 1: Thonny** (Recommended)
- [ ] Installed Thonny from https://thonny.org
- [ ] Configured: Tools → Options → Interpreter
- [ ] Selected: MicroPython (Raspberry Pi Pico)
- [ ] Selected correct COM port

**Option 2: Command line (ampy)**
- [ ] Installed ampy: `pip install adafruit-ampy`
- [ ] Verified connection: `ampy --port COM# ls`

### 3. Configuration File
- [ ] Navigated to `micropython/` folder
- [ ] Copied `config.example.py` to `config.py`
- [ ] Opened `config.py` in text editor
- [ ] Updated `WIFI_SSID` with your WiFi name
- [ ] Updated `WIFI_PASSWORD` with your WiFi password
- [ ] Updated `SERVER_URL` with your server IP address
  - Format: `http://YOUR_IP:8000/api/monitoring/insert`
  - Example: `http://192.168.1.100:8000/api/monitoring/insert`
- [ ] Updated `DEVICE_ID` with unique device name
- [ ] Saved `config.py`

### 4. File Upload
Using Thonny:
- [ ] Opened `config.py` in Thonny
- [ ] Clicked Save → Raspberry Pi Pico → Saved as `config.py`
- [ ] Opened `main.py` in Thonny
- [ ] Clicked Save → Raspberry Pi Pico → Saved as `main.py`

Or using ampy:
- [ ] Ran: `ampy --port COM# put config.py`
- [ ] Ran: `ampy --port COM# put main.py`

### 5. Testing & Verification
- [ ] Ran code in Thonny (F5) or REPL: `import main`
- [ ] Saw message: `✅ WiFi Connected! IP: 192.168.x.x`
- [ ] Saw message: `✅ Server Response Code: 201`
- [ ] Noted Pico W's IP address: _______________

---

## 🌐 Dashboard Verification

- [ ] Opened browser
- [ ] Navigated to: `http://localhost:8000` or `http://127.0.0.1:8000`
- [ ] Dashboard loaded successfully
- [ ] Device appeared in device list
- [ ] Device name matches configured DEVICE_ID
- [ ] Status shows: **ONLINE** or **CONNECTED**
- [ ] Temperature reading displayed
- [ ] Soil moisture reading displayed
- [ ] Last update timestamp shows recent time
- [ ] Data refreshes every 10 seconds

---

## 🔌 Hardware Connection

### Components Prepared
- [ ] Raspberry Pi Pico W
- [ ] DHT22 Temperature & Humidity Sensor
- [ ] Capacitive Soil Moisture Sensor
- [ ] 5V Relay Module
- [ ] Water Pump (with appropriate power supply)
- [ ] Breadboard (optional, for easier connections)
- [ ] Jumper wires (male-to-male, male-to-female)

### Sensor Connections
- [ ] **DHT22 to Pico W:**
  - [ ] DHT22 VCC → Pico W 3.3V (Pin 36)
  - [ ] DHT22 Data → Pico W GP2 (Pin 4)
  - [ ] DHT22 GND → Pico W GND (Pin 38)
  
- [ ] **Soil Sensor to Pico W:**
  - [ ] Soil Sensor VCC → Pico W 3.3V
  - [ ] Soil Sensor Signal → Pico W GP26/ADC0 (Pin 31)
  - [ ] Soil Sensor GND → Pico W GND
  
- [ ] **Relay to Pico W:**
  - [ ] Relay VCC → Pico W VBUS/5V (Pin 40)
  - [ ] Relay IN → Pico W GP5 (Pin 7)
  - [ ] Relay GND → Pico W GND

### Pump Connection (via Relay)
- [ ] Pump positive wire → Relay COM terminal
- [ ] Relay NO (Normally Open) → Power supply positive
- [ ] Pump negative wire → Power supply negative
- [ ] ⚠️ Verified power supply matches pump voltage requirement

### Safety Checks
- [ ] All connections tight and secure
- [ ] No exposed wires or short circuits
- [ ] Power supply rated for pump current draw
- [ ] Common ground connection between all components
- [ ] Relay rated for pump voltage and current

### Hardware Testing
- [ ] Powered on system
- [ ] DHT22 reading appears in Serial Monitor (not "DHT22 Error")
- [ ] Soil moisture reading appears (0-100%)
- [ ] Can manually trigger relay from dashboard
- [ ] Relay clicks when toggled
- [ ] Pump activates when relay turns ON
- [ ] Pump stops when relay turns OFF

---

## 🎛️ System Operation Modes

### Mode Configuration
Access the dashboard and configure operation mode:

**Mode 1: Basic Threshold** (Default)
- [ ] Set Dry Threshold (default: 40%)
- [ ] Set Wet Threshold (default: 70%)
- [ ] Pump turns ON when soil < dry threshold
- [ ] Pump turns OFF when soil >= wet threshold

**Mode 2: Fuzzy Logic AI**
- [ ] System automatically adjusts watering based on temperature
- [ ] Hot weather → longer watering
- [ ] Cool weather → shorter watering

**Mode 3: Schedule Timer**
- [ ] Set morning watering time (default: 07:00)
- [ ] Set evening watering time (default: 17:00)
- [ ] Set watering duration in seconds (default: 5)

### Mode Testing
- [ ] Selected mode from dashboard
- [ ] Configuration sent to Pico W
- [ ] Pico W confirmed config update in Serial Monitor
- [ ] Tested pump activation according to mode logic
- [ ] Pump behavior matches expected mode operation

---

## 🔬 Sensor Calibration (Optional but Recommended)

### Soil Moisture Sensor Calibration
- [ ] **Dry Calibration:**
  - [ ] Removed sensor from soil (completely dry in air)
  - [ ] Noted ADC value from Serial Monitor
  - [ ] Recorded dry value (adc_min): _______________
  
- [ ] **Wet Calibration:**
  - [ ] Submerged sensor in water (only sensing part!)
  - [ ] Noted ADC value from Serial Monitor
  - [ ] Recorded wet value (adc_max): _______________
  
- [ ] **Update Configuration:**
  - [ ] Entered calibration values in dashboard settings
  - [ ] Saved settings
  - [ ] Verified Pico W received updated calibration
  - [ ] Tested readings for accuracy

---

## 📊 Final System Verification

### Data Flow
- [ ] Sensor data sent every 10 seconds
- [ ] Dashboard updates automatically
- [ ] Historical data logging working
- [ ] Charts displaying data correctly

### Control Testing
- [ ] Manual pump control works from dashboard
- [ ] Automatic mode triggers pump correctly
- [ ] Configuration changes applied in real-time
- [ ] Device status accurate (online/offline)

### Stability Testing
- [ ] System runs for 10+ minutes without errors
- [ ] WiFi connection stable
- [ ] Server communication consistent
- [ ] No unexpected pump activations
- [ ] Sensor readings stable and reasonable

---

## 🆘 Troubleshooting Reference

If any step fails, refer to:

- **CONFIGURATION_GUIDE.md** - Detailed setup instructions
- **PANDUAN_UPLOAD_PICO_W.md** - Step-by-step Indonesian guide
- **arduino/README.md** - Arduino-specific quick reference
- **micropython/README.md** - MicroPython-specific quick reference
- **README.md** - Project overview and quick start

### Common Issues Quick Fix

**WiFi won't connect:**
- Verify SSID and password are exactly correct (case-sensitive)
- Ensure using 2.4GHz WiFi network
- Move Pico W closer to router
- Check router allows new device connections

**Server connection fails:**
- Verify server is running: `php artisan serve --host=0.0.0.0 --port=8000`
- Check IP address is correct (run ipconfig/ifconfig again)
- Test server in browser: http://YOUR_IP:8000
- Check firewall isn't blocking port 8000

**Sensors not working:**
- Verify wiring connections
- Check pin numbers match configuration
- Test sensors individually
- Replace sensor if readings don't change

**Upload fails:**
- Use BOOTSEL method (hold button during USB connect)
- Try different USB cable (must be data cable, not charging only)
- Check USB port works with other devices
- Reinstall board support in Arduino IDE

---

## ✅ Completion Confirmation

Once ALL items above are checked:

- [ ] **System is fully configured**
- [ ] **All tests passed**
- [ ] **Documentation reviewed**
- [ ] **Ready for deployment**

**Configuration Date:** _______________  
**Configured By:** _______________  
**Device ID:** _______________  
**Pico W IP:** _______________  
**Notes:** 
```
_____________________________________
_____________________________________
_____________________________________
```

---

## 🎉 Next Steps

With your Pico W fully configured:

1. **Monitor the system** for the first 24 hours
2. **Fine-tune thresholds** based on your plant needs
3. **Test all three modes** to find what works best
4. **Set up additional devices** if managing multiple zones
5. **Review logs** periodically for issues

**Your Smart Garden IoT system is now operational!** 🌱

---

**Document Version:** 1.0  
**Last Updated:** January 10, 2026  
**Project:** Smart Garden IoT System
