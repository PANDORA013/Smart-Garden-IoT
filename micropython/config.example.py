"""
=============================================================================
CONFIGURATION FILE FOR RASPBERRY PI PICO W - SMART GARDEN (MicroPython)
=============================================================================

INSTRUCTIONS:
1. Copy this file and rename it to "config.py" in the same directory
2. Update all values marked with "CHANGE_ME" with your actual values
3. Save the file
4. Upload both config.py and main.py to your Pico W

=============================================================================
"""

# ===========================
# WiFi CONFIGURATION
# ===========================
# Replace with your WiFi network name (SSID)
WIFI_SSID = "YOUR_WIFI_SSID"        # CHANGE_ME: Your WiFi name

# Replace with your WiFi password
WIFI_PASSWORD = "YOUR_WIFI_PASSWORD" # CHANGE_ME: Your WiFi password

# ===========================
# SERVER CONFIGURATION
# ===========================
# Replace with your Laravel server IP address and port
# Find your IP with: ipconfig (Windows) or ifconfig (Mac/Linux)
# Default port is 8000 if running: php artisan serve --host=0.0.0.0 --port=8000
SERVER_URL = "http://192.168.1.100:8000/api/monitoring/insert" # CHANGE_ME: Your server IP

# ===========================
# DEVICE CONFIGURATION
# ===========================
# Unique identifier for this device (use meaningful names)
# Examples: "PICO_TOMATO_01", "PICO_GARDEN_A", "PICO_GREENHOUSE_01"
DEVICE_ID = "PICO_GARDEN_01" # CHANGE_ME: Your device name

# ===========================
# HARDWARE PIN CONFIGURATION
# ===========================
# Only change these if you connected sensors to different pins
SOIL_SENSOR_PIN = 26  # ADC Pin (GP26) - Soil moisture sensor
DHT_PIN = 2           # DHT22 Pin (GP2) - Temperature sensor
RELAY_PIN = 5         # Relay Pin (GP5) - Water pump control

# ===========================
# DEFAULT SENSOR CALIBRATION
# ===========================
# These values will be overridden by server configuration
# Only change if you want different initial values
DEFAULT_CONFIG = {
    "mode": 1,              # Operating mode: 1=Basic, 2=Fuzzy AI, 3=Schedule
    "adc_min": 4095,        # ADC value when sensor is dry (in air)
    "adc_max": 1500,        # ADC value when sensor is wet (in water)
    "batas_kering": 40,     # Pump turns ON when moisture < 40%
    "batas_basah": 70,      # Pump turns OFF when moisture >= 70%
    "jam_pagi": "07:00",    # Morning watering time (Mode 3)
    "jam_sore": "17:00",    # Evening watering time (Mode 3)
    "durasi_siram": 5       # Watering duration in seconds
}

# ===========================
# TIMING CONFIGURATION
# ===========================
SEND_INTERVAL = 10  # Send data every N seconds
WIFI_TIMEOUT = 20   # WiFi connection timeout in seconds

# ===========================
# NTP TIME SERVER (for Mode 3 - Schedule)
# ===========================
NTP_HOST = "pool.ntp.org"
TIMEZONE_OFFSET = 7  # UTC offset in hours (e.g., 7 for Indonesia UTC+7)
                     # Change to your timezone: UTC+0: 0, UTC+1: 1, UTC-5: -5, etc.
