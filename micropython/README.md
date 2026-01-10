# MicroPython Code for Raspberry Pi Pico W

## Quick Start

1. **Install MicroPython Firmware**
   - Download from: https://micropython.org/download/rp2-pico-w/
   - Hold BOOTSEL, connect USB, copy .uf2 file

2. **Create Configuration File**
   - Copy `config.example.py` to `config.py`
   - Edit `config.py` with your WiFi and server details

3. **Upload Files**
   - Upload `config.py` to Pico W
   - Upload `main.py` to Pico W
   - Run `main.py`

## Configuration

Edit `config.py` (created from `config.example.py`):

```python
# WiFi Settings
WIFI_SSID = "YourWiFiName"
WIFI_PASSWORD = "YourPassword"

# Server Settings
SERVER_URL = "http://192.168.1.100:8000/api/monitoring/insert"

# Device Settings
DEVICE_ID = "PICO_GARDEN_01"
```

## Hardware Connections

| Pico W Pin | Component |
|------------|-----------|
| GP2 | DHT22 Data |
| GP26 (ADC0) | Soil Sensor Signal |
| GP5 | Relay IN |
| 3.3V | Sensors VCC |
| GND | Common Ground |

## Required Libraries

The following libraries are built into MicroPython:
- `network` - WiFi connectivity
- `urequests` - HTTP requests
- `ujson` - JSON handling
- `dht` - DHT22 sensor
- `machine` - Hardware control

**Optional for Mode 3 (Schedule):**
- `ntptime` - For accurate time synchronization

## Upload Methods

### Method 1: Using Thonny IDE
1. Install Thonny: https://thonny.org/
2. Configure: Tools → Options → Interpreter → MicroPython (Raspberry Pi Pico)
3. Open file → Save to Raspberry Pi Pico

### Method 2: Using ampy
```bash
pip install adafruit-ampy
ampy --port COM# put config.py
ampy --port COM# put main.py
```

### Method 3: Using rshell
```bash
pip install rshell
rshell --port COM#
cp config.py /pyboard/
cp main.py /pyboard/
```

## Running the Code

### Auto-run on boot:
Rename `main.py` to `boot.py` on the Pico W

### Manual run:
In Thonny or REPL:
```python
import main
```

## Troubleshooting

**ModuleNotFoundError: 'urequests'?**
- Install: `upip.install('micropython-urequests')`

**WiFi Failed?**
- Check SSID and password in config.py
- Ensure using 2.4GHz WiFi (not 5GHz)

**DHT22 Error?**
- Check wiring to GP2
- System will use default 28.0°C fallback

## Documentation

For detailed instructions, see:
- [CONFIGURATION_GUIDE.md](../CONFIGURATION_GUIDE.md) - Complete setup guide
- [PANDUAN_UPLOAD_PICO_W.md](../PANDUAN_UPLOAD_PICO_W.md) - Step-by-step Indonesian guide
- [QUICK_START_PICO.md](../QUICK_START_PICO.md) - Quick reference
