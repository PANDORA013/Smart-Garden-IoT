# =============================================================================
# RASPBERRY PI PICO W - SMART GARDEN (2-Way Communication)
# =============================================================================
# WiFi: AAB / 081211915711
# Server: 192.168.0.102:8000
# =============================================================================

import network
import time
import ujson
import urequests
from machine import Pin, ADC, reset
import dht

# =============================================================================
# KONFIGURASI
# =============================================================================
WIFI_SSID = "AAB"
WIFI_PASSWORD = "081211915711"
SERVER_URL = "http://192.168.0.102:8000/api/monitoring/insert"
DEVICE_ID = "PICO_CABAI_01"
SEND_INTERVAL = 10

# =============================================================================
# HARDWARE SETUP
# =============================================================================
dht_sensor = dht.DHT22(Pin(2))
relay = Pin(5, Pin.OUT)
relay.value(0)
soil_adc = ADC(26)

# =============================================================================
# VARIABEL
# =============================================================================
ADC_MIN = 4095
ADC_MAX = 1500
pump_status = False
temperature = 0
soil_moisture = 0
raw_adc = 0

# =============================================================================
# FUNGSI
# =============================================================================

def connect_wifi():
    wlan = network.WLAN(network.STA_IF)
    wlan.active(True)
    wlan.connect(WIFI_SSID, WIFI_PASSWORD)
    
    print("Connecting to WiFi...")
    for i in range(15):
        if wlan.status() == 3:
            break
        time.sleep(1)
    
    if wlan.status() == 3:
        ip = wlan.ifconfig()[0]
        print("WiFi Connected! IP:", ip)
        return True
    else:
        print("WiFi Failed!")
        return False

def read_sensors():
    global temperature, soil_moisture, raw_adc
    
    # DHT22
    try:
        dht_sensor.measure()
        temperature = dht_sensor.temperature()
        if temperature <= 0 or temperature > 60:
            temperature = 0
    except:
        temperature = 0
    
    # Soil Sensor
    raw_16bit = soil_adc.read_u16()
    raw_adc = raw_16bit >> 4
    
    # Floating pin detection
    if raw_adc < 500 or raw_adc > 4000:
        soil_moisture = 0
        raw_adc = 0
    else:
        # Map 4095-1500 to 0-100%
        soil_moisture = int((raw_adc - ADC_MIN) * (100 - 0) / (ADC_MAX - ADC_MIN) + 0)
        if soil_moisture < 0:
            soil_moisture = 0
        if soil_moisture > 100:
            soil_moisture = 100
    
    print("\nSensors:")
    print("  Temp:", temperature, "C")
    print("  Soil:", soil_moisture, "%")
    print("  ADC:", raw_adc)

def send_and_receive():
    global pump_status
    
    if not network.WLAN(network.STA_IF).isconnected():
        print("WiFi disconnected!")
        return
    
    try:
        ip_addr = network.WLAN(network.STA_IF).ifconfig()[0]
        
        # Kirim data ke server
        payload = {
            "device_id": DEVICE_ID,
            "temperature": temperature,
            "soil_moisture": soil_moisture,
            "raw_adc": raw_adc,
            "relay_status": pump_status,
            "ip_address": ip_addr
        }
        
        print("\nSending data...")
        res = urequests.post(
            SERVER_URL, 
            json=payload, 
            headers={"Content-Type": "application/json"},
            timeout=5
        )
        
        if res.status_code in [200, 201]:
            print("Server OK:", res.status_code)
            
            # Terima perintah dari server (2-way communication)
            try:
                response_data = res.json()
                
                # Cek apakah ada perintah relay dari server
                if "relay_command" in response_data:
                    new_relay = response_data["relay_command"]
                    if new_relay != pump_status:
                        pump_status = new_relay
                        relay.value(1 if pump_status else 0)
                        print("RELAY CHANGED BY SERVER:", "ON" if pump_status else "OFF")
                
                # Cek apakah ada update config
                if "config" in response_data:
                    config = response_data["config"]
                    print("Config received:", config.get("mode", "N/A"))
                    
            except:
                pass  # Response tidak ada JSON, skip
        else:
            print("Server Error:", res.status_code)
        
        res.close()
        
    except Exception as e:
        print("Send failed:", str(e))

# =============================================================================
# MAIN LOOP
# =============================================================================
print("\n" + "="*50)
print("PICO W SMART GARDEN")
print("="*50)
print("Device ID:", DEVICE_ID)
print("Server:", SERVER_URL)
print("="*50 + "\n")

# Connect WiFi
if not connect_wifi():
    print("Restarting...")
    time.sleep(3)
    reset()

print("\nSystem Ready!\n")

# Main Loop
while True:
    read_sensors()
    send_and_receive()
    print("\nSleep", SEND_INTERVAL, "seconds...\n")
    time.sleep(SEND_INTERVAL)
