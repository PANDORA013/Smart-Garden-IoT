# 🔧 Configuration Guide - Raspberry Pi Pico W Setup

## 📋 Overview

This guide will help you configure your Raspberry Pi Pico W to connect to your Smart Garden IoT system. You can use either **Arduino** (C++) or **MicroPython** - both implementations are fully compatible with the server.

---

## 🎯 Before You Begin

### Required Information

Before starting, gather the following information:

1. **WiFi Credentials**
   - Network Name (SSID)
   - Password
   - ⚠️ **Important**: Pico W only supports 2.4GHz WiFi networks

2. **Server Information**
   - Your computer's local IP address
   - Server port (default: 8000)

3. **Device Information**
   - Choose a unique device ID for your Pico W (e.g., "PICO_GARDEN_01")

---

## 📡 Step 1: Find Your Server IP Address

### On Windows:
1. Open **PowerShell** or **Command Prompt**
2. Type: `ipconfig`
3. Look for "IPv4 Address" under your active network adapter
4. Example: `192.168.1.100`

### On Mac/Linux:
1. Open **Terminal**
2. Type: `ifconfig` or `ip addr`
3. Look for "inet" address under your active network interface
4. Example: `192.168.1.100`

### Test Server Accessibility:
Make sure your Laravel server is running:
```bash
php artisan serve --host=0.0.0.0 --port=8000
```

---

## 🔌 Step 2: Choose Your Implementation

### Option A: Arduino (Recommended for Beginners)
- Easier to set up
- More stable WiFi connection
- Better library support
- Go to **Section 3A**

### Option B: MicroPython
- More flexible
- Python-like syntax
- Smaller code footprint
- Go to **Section 3B**

---

## 🛠️ Section 3A: Arduino Configuration

### 1. Install Arduino IDE

Download and install Arduino IDE 2.x from: https://www.arduino.cc/en/software

### 2. Install Pico W Board Support

1. Open Arduino IDE
2. Go to: **File → Preferences**
3. In "Additional Board Manager URLs", add:
   ```
   https://github.com/earlephilhower/arduino-pico/releases/download/global/package_rp2040_index.json
   ```
4. Go to: **Tools → Board → Boards Manager**
5. Search for: **"Raspberry Pi Pico"**
6. Install: **"Raspberry Pi Pico/RP2040"** by Earle F. Philhower, III

### 3. Install Required Libraries

Go to: **Tools → Manage Libraries** and install:

1. **ArduinoJson** (by Benoit Blanchon) - Latest version
2. **DHT sensor library** (by Adafruit) - Click "Install All" when prompted
3. **NTPClient** (by Fabrice Weinberg)

### 4. Create Configuration File

1. Navigate to: `arduino/` folder in the project
2. Copy `config.example.h` and rename it to `config.h`
3. Open `config.h` in a text editor
4. Update the following values:

```cpp
// WiFi Configuration
const char* WIFI_SSID = "YourWiFiName";         // Your WiFi SSID
const char* WIFI_PASSWORD = "YourPassword";      // Your WiFi password

// Server Configuration
const char* SERVER_URL = "http://192.168.1.100:8000/api/monitoring/insert"; // Your server IP

// Device Configuration
const char* DEVICE_ID = "PICO_GARDEN_01";       // Unique device name
```

5. Save the file

### 5. Modify Main Arduino File

Open `pico_smart_gateway.ino` and add this line at the top (after the header comment):

```cpp
#include "config.h"
```

Then replace the hardcoded configuration variables with the config file variables:
- Replace `ssid` with `WIFI_SSID`
- Replace `password` with `WIFI_PASSWORD`
- Replace `serverUrl` with `SERVER_URL`
- Replace `deviceId` with `DEVICE_ID`

### 6. Upload to Pico W

1. Connect Pico W via USB
2. Select: **Tools → Board → Raspberry Pi Pico W** (with W!)
3. Select: **Tools → Port → COM# (your port)**
4. Click: **Verify (✓)** to compile
5. Click: **Upload (→)** to upload

**If upload fails:**
- Unplug Pico W
- Hold the **BOOTSEL** button
- Plug in USB while holding BOOTSEL
- Release BOOTSEL
- Try upload again

### 7. Monitor Serial Output

1. Open: **Tools → Serial Monitor**
2. Set baud rate to: **115200**
3. You should see:
   ```
   ✅ WiFi Connected!
   📡 IP Address: 192.168.1.xxx
   ✅ Server Response Code: 201
   ```

---

## 🐍 Section 3B: MicroPython Configuration

### 1. Install MicroPython Firmware

1. Download latest MicroPython firmware for Pico W from: https://micropython.org/download/rp2-pico-w/
2. Hold **BOOTSEL** button on Pico W
3. Connect to USB while holding BOOTSEL
4. Pico W appears as USB drive (RPI-RP2)
5. Copy the `.uf2` firmware file to the drive
6. Pico W will reboot automatically

### 2. Install Thonny IDE (or your preferred editor)

Download from: https://thonny.org/

Configure Thonny:
1. Open Thonny
2. Go to: **Tools → Options → Interpreter**
3. Select: **MicroPython (Raspberry Pi Pico)**
4. Select the correct COM port

### 3. Create Configuration File

1. Navigate to: `micropython/` folder in the project
2. Copy `config.example.py` and rename it to `config.py`
3. Open `config.py` in a text editor
4. Update the following values:

```python
# WiFi Configuration
WIFI_SSID = "YourWiFiName"          # Your WiFi SSID
WIFI_PASSWORD = "YourPassword"       # Your WiFi password

# Server Configuration
SERVER_URL = "http://192.168.1.100:8000/api/monitoring/insert"  # Your server IP

# Device Configuration
DEVICE_ID = "PICO_GARDEN_01"        # Unique device name
```

5. Save the file

### 4. Modify Main Python File

Open `main.py` and add this line at the top:

```python
from config import *
```

Then replace the hardcoded configuration variables with imported ones from config.py.

### 5. Upload Files to Pico W

Using Thonny:
1. Open `config.py` → Click **Save** → Select **Raspberry Pi Pico** → Save as `config.py`
2. Open `main.py` → Click **Save** → Select **Raspberry Pi Pico** → Save as `main.py`

Or use `ampy` command-line tool:
```bash
ampy --port COM# put config.py
ampy --port COM# put main.py
```

### 6. Run and Monitor

In Thonny:
1. Click **Run** or press **F5**
2. View output in the Shell window
3. You should see:
   ```
   ✅ WiFi Connected! IP: 192.168.1.xxx
   ✅ Server Response Code: 201
   ```

---

## 🧪 Step 4: Test the Connection

### 1. Verify WiFi Connection

Check the serial output for:
```
✅ WiFi Connected!
📡 IP Address: 192.168.1.xxx
```

**Troubleshooting:**
- ❌ "WiFi Connection Failed" → Check SSID and password
- ❌ "WiFi Failed" → Make sure you're using 2.4GHz WiFi
- ❌ Still failing → Move Pico W closer to router

### 2. Verify Server Communication

Check the serial output for:
```
✅ Server Response Code: 201
📥 Server Response: {data}
```

**Troubleshooting:**
- ❌ "HTTP Error: -1" → Server not running or wrong IP
- ❌ "HTTP Error: 404" → Wrong API endpoint
- ❌ "HTTP Error: 500" → Server error, check Laravel logs

### 3. Verify Dashboard

1. Open browser: `http://localhost:8000` or `http://127.0.0.1:8000`
2. Your device should appear in the dashboard
3. Data should update every 10 seconds

---

## 🔌 Step 5: Connect Hardware

### Required Components:
- DHT22 Temperature & Humidity Sensor
- Capacitive Soil Moisture Sensor
- 5V Relay Module
- Water Pump (5V or 12V depending on relay)
- Jumper wires

### Wiring Diagram:

```
Pico W Pin    →    Component
---------------------------------
GP2          →    DHT22 Data Pin
3.3V         →    DHT22 VCC
GND          →    DHT22 GND

GP26 (ADC0)  →    Soil Sensor Signal
3.3V         →    Soil Sensor VCC
GND          →    Soil Sensor GND

GP5          →    Relay IN
5V (VBUS)    →    Relay VCC
GND          →    Relay GND

Relay COM    →    Pump Positive
Relay NO     →    Power Supply +
Pump Negative →   Power Supply -
```

### Safety Notes:
- ⚠️ **Never connect high voltage directly to Pico W**
- ⚠️ Use appropriate power supply for your pump
- ⚠️ Ensure all GND connections are common
- ⚠️ Double-check polarity before powering on

---

## ⚙️ Step 6: Configure Operation Modes

The system supports 3 operation modes (configured via dashboard):

### Mode 1: Basic Threshold (Default)
- Pump turns ON when soil moisture < 40%
- Pump turns OFF when soil moisture >= 70%
- Simple and reliable

### Mode 2: Fuzzy Logic AI
- Considers both temperature and soil moisture
- Hot weather → Longer watering time
- Cool weather → Shorter watering time
- Intelligent adaptation

### Mode 3: Schedule Timer
- Water at specific times (e.g., 7:00 AM and 5:00 PM)
- Fixed duration watering
- Predictable schedule

Configure these settings via the web dashboard after the device is connected.

---

## 🎛️ Step 7: Calibrate Soil Sensor (Optional)

For accurate readings, calibrate your soil moisture sensor:

1. **Dry Calibration:**
   - Keep sensor in open air (completely dry)
   - Note the ADC value from serial monitor
   - This is your `adc_min` value

2. **Wet Calibration:**
   - Submerge sensor in water (only the sensor part, not electronics!)
   - Note the ADC value from serial monitor
   - This is your `adc_max` value

3. **Update Configuration:**
   - Enter these values in the dashboard settings
   - The server will send updated calibration to Pico W

---

## 🔧 Advanced Configuration

### Adjust Send Interval

Default: 10 seconds

**Arduino:**
```cpp
const unsigned long SEND_INTERVAL = 10000; // milliseconds
```

**MicroPython:**
```python
SEND_INTERVAL = 10  # seconds
```

### Change Timezone (for Mode 3)

**Arduino:**
```cpp
const long GMT_OFFSET_SEC = 25200;  // UTC+7 (7 * 3600)
```

**MicroPython:**
```python
TIMEZONE_OFFSET = 7  # hours
```

Common timezones:
- UTC+0 (London): 0
- UTC+1 (Paris): 1  
- UTC+7 (Bangkok): 7
- UTC+8 (Singapore): 8
- UTC-5 (New York): -5
- UTC-8 (Los Angeles): -8

---

## 🐛 Troubleshooting

### WiFi Issues
| Problem | Solution |
|---------|----------|
| Can't connect to WiFi | Verify SSID and password are correct |
| WiFi drops frequently | Move closer to router, check 2.4GHz |
| Wrong IP displayed | Check network interface in ipconfig |

### Server Communication Issues
| Problem | Solution |
|---------|----------|
| HTTP Error -1 | Server not running, wrong IP address |
| HTTP Error 404 | Check API endpoint URL |
| HTTP Error 500 | Check Laravel logs, database connection |
| No response | Check firewall settings, allow port 8000 |

### Sensor Issues
| Problem | Solution |
|---------|----------|
| DHT22 Error | Check wiring, try different pin, check sensor |
| Soil sensor always 0% | Check wiring, calibrate sensor |
| Soil sensor always 100% | Swap adc_min and adc_max values |
| Erratic readings | Check power supply, use shorter wires |

### Upload Issues
| Problem | Solution |
|---------|----------|
| Port not detected | Install USB drivers, check cable |
| Upload fails | Use BOOTSEL button method |
| Code compiles but doesn't run | Check serial monitor for errors |

---

## 📚 Additional Resources

- **Arduino Documentation:** https://arduino-pico.readthedocs.io/
- **MicroPython Documentation:** https://docs.micropython.org/
- **Pico W Datasheet:** https://datasheets.raspberrypi.com/picow/pico-w-datasheet.pdf
- **Laravel Documentation:** https://laravel.com/docs

---

## 🆘 Getting Help

If you encounter issues not covered in this guide:

1. Check the serial monitor output for error messages
2. Review the `PANDUAN_UPLOAD_PICO_W.md` for detailed step-by-step instructions
3. Check the project README.md for general information
4. Verify your server is running and accessible

---

## ✅ Success Checklist

Before considering your setup complete, verify:

- [ ] WiFi credentials configured correctly
- [ ] Server IP address is correct and accessible
- [ ] Device ID is unique and meaningful
- [ ] Code compiles without errors
- [ ] Code uploads successfully to Pico W
- [ ] Serial monitor shows WiFi connected
- [ ] Serial monitor shows successful server response (201)
- [ ] Device appears in web dashboard
- [ ] Data updates every 10 seconds
- [ ] All sensors connected and reading correctly
- [ ] Relay/pump responds to commands
- [ ] Configuration changes from dashboard are received

---

**Last Updated:** January 10, 2026  
**Project:** Smart Garden IoT System  
**Version:** 1.0.0
