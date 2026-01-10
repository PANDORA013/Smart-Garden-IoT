# Arduino Code for Raspberry Pi Pico W

## Quick Start

1. **Create Configuration File**
   - Copy `config.example.h` to `config.h`
   - Edit `config.h` with your WiFi and server details

2. **Install Required Libraries** (in Arduino IDE)
   - ArduinoJson (by Benoit Blanchon)
   - DHT sensor library (by Adafruit) - Install ALL dependencies
   - NTPClient (by Fabrice Weinberg)

3. **Select Board**
   - Tools → Board → Raspberry Pi Pico W

4. **Upload**
   - Open `pico_smart_gateway.ino`
   - Click Upload (→)

## Configuration

Edit `config.h` (created from `config.example.h`):

```cpp
// WiFi Settings
const char* WIFI_SSID = "YourWiFiName";
const char* WIFI_PASSWORD = "YourPassword";

// Server Settings  
const char* SERVER_URL = "http://192.168.1.100:8000/api/monitoring/insert";

// Device Settings
const char* DEVICE_ID = "PICO_GARDEN_01";
```

## Hardware Connections

| Pico W Pin | Component |
|------------|-----------|
| GP2 | DHT22 Data |
| GP26 (ADC0) | Soil Sensor Signal |
| GP5 | Relay IN |
| 3.3V | Sensors VCC |
| GND | Common Ground |

## Troubleshooting

**Upload Failed?**
- Hold BOOTSEL button while connecting USB
- Release BOOTSEL and try upload again

**WiFi Failed?**
- Check SSID and password in config.h
- Ensure using 2.4GHz WiFi (not 5GHz)

**Libraries Not Found?**
- Tools → Manage Libraries
- Search and install each library

## Documentation

For detailed instructions, see:
- [CONFIGURATION_GUIDE.md](../CONFIGURATION_GUIDE.md) - Complete setup guide
- [PANDUAN_UPLOAD_PICO_W.md](../PANDUAN_UPLOAD_PICO_W.md) - Step-by-step Indonesian guide
- [QUICK_START_PICO.md](../QUICK_START_PICO.md) - Quick reference
