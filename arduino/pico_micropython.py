# =============================================================================
# RASPBERRY PI PICO W - SMART GARDEN (MicroPython Version)
# =============================================================================
# UNTUK THONNY IDE - Upload langsung ke Pico W
# 
# FITUR:
# ✅ Kirim data sensor ke Laravel Server
# ✅ Kontrol pompa otomatis berdasarkan kelembaban tanah
# ✅ DHT22 untuk suhu & kelembaban udara
# ✅ Capacitive Soil Moisture Sensor
# 
# HARDWARE:
# - GPIO 26 (ADC0) → Sensor Kelembaban Tanah
# - GPIO 2 → DHT22
# - GPIO 5 → Relay/Pompa Air
# =============================================================================

import network
import urequests as requests
import time
from machine import Pin, ADC
from dht import DHT22

# Import konfigurasi jaringan (bisa diubah tanpa edit file utama)
try:
    from network_config import SSID, PASSWORD, SERVER_URL, DEVICE_ID
    print("✅ Network config loaded from network_config.py")
except:
    # Fallback jika network_config.py tidak ada
    print("⚠️  network_config.py not found, using default config")
    SSID = "CCTV_UISI"
    PASSWORD = "08121191"
    SERVER_URL = "http://10.134.42.169:8000/api/monitoring/insert"
    DEVICE_ID = "PICO_CABAI_01"

# ===========================
# KONFIGURASI HARDWARE
# ===========================
soil = ADC(26)          # Pin ADC untuk sensor tanah
dht_sensor = DHT22(Pin(2))  # DHT22 di GPIO 2
relay = Pin(5, Pin.OUT)     # Relay di GPIO 5
relay.value(0)              # Pompa OFF saat start

# ===========================
# VARIABEL KONFIGURASI
# ===========================
BATAS_KERING = 40   # Pompa ON jika < 40%
BATAS_BASAH = 70    # Pompa OFF jika >= 70%

def connect_wifi():
    """Koneksi ke WiFi"""
    wlan = network.WLAN(network.STA_IF)
    wlan.active(True)
    wlan.connect(SSID, PASSWORD)
    
    print("🔌 Connecting to WiFi:", SSID, end="")
    attempts = 0
    
    while not wlan.isconnected() and attempts < 20:
        time.sleep(1)
        print(".", end="")
        attempts += 1
    
    if wlan.isconnected():
        print("\n✅ WiFi Connected!")
        print("📡 IP Address:", wlan.ifconfig()[0])
        return True
    else:
        print("\n❌ WiFi Connection Failed!")
        return False

def map_adc(val):
    """Konversi ADC 16-bit (0-65535) ke Persen (0-100%)"""
    # Kalibrasi: Sesuaikan dengan sensor Anda
    min_val = 65535  # Sensor Kering (di udara)
    max_val = 20000  # Sensor Basah (di air)
    
    # Clamp nilai
    if val > min_val: 
        val = min_val
    if val < max_val: 
        val = max_val
    
    # Map ke persen
    percent = (min_val - val) / (min_val - max_val) * 100
    
    # Clamp 0-100%
    if percent < 0: 
        percent = 0
    if percent > 100: 
        percent = 100
        
    return percent

def control_pump(soil_moisture):
    """Kontrol Pompa Otomatis (Mode Basic Threshold)"""
    current_state = bool(relay.value())
    
    if soil_moisture < BATAS_KERING and not current_state:
        relay.value(1)
        print("🟢 Tanah kering, Pompa ON")
        return True
    elif soil_moisture >= BATAS_BASAH and current_state:
        relay.value(0)
        print("🔴 Tanah basah, Pompa OFF")
        return False
    else:
        return current_state

def send_data_to_server(temp, soil_moisture, raw_adc, pump_status):
    """Kirim data ke Laravel Server"""
    data = {
        "device_id": DEVICE_ID,
        "temperature": temp,
        "soil_moisture": soil_moisture,
        "raw_adc": raw_adc,
        "relay_status": pump_status
    }
    
    try:
        print("\n📤 Sending data to server...")
        print(f"   Temp: {temp}°C | Soil: {soil_moisture}% | Pump: {pump_status}")
        
        res = requests.post(SERVER_URL, json=data, timeout=5)
        
        print(f"✅ Server Response: {res.status_code}")
        
        if res.status_code in [200, 201]:
            print("📥 Data berhasil dikirim!")
        
        res.close()
        return True
        
    except Exception as e:
        print(f"❌ Upload Error: {e}")
        return False

# ===========================
# MAIN PROGRAM
# ===========================
print("\n========================================")
print("🌱 PICO W SMART GARDEN (MicroPython)")
print("========================================")

# Connect WiFi
if not connect_wifi():
    print("⚠️ Running without WiFi...")

print("✅ Setup Complete!")
print("========================================\n")

# Main Loop
while True:
    try:
        # Baca Sensor Tanah
        raw_adc = soil.read_u16()
        soil_moisture = map_adc(raw_adc)
        
        # Baca Sensor DHT22
        try:
            dht_sensor.measure()
            time.sleep(0.5)  # Delay untuk DHT22 selesai baca
            temp = dht_sensor.temperature()
        except Exception as e:
            print(f"⚠️ DHT22 Error: {e}")
            temp = 28.0  # Default value
        
        # Kontrol Pompa
        pump_status = control_pump(soil_moisture)
        
        # Print Status
        print(f"📊 Temp: {temp}°C | Soil: {soil_moisture:.1f}% | Pump: {'ON' if pump_status else 'OFF'}")
        
        # Kirim Data ke Server (Setiap 10 detik)
        send_data_to_server(temp, soil_moisture, raw_adc, pump_status)
        
        # Delay 10 detik
        time.sleep(10)
        
    except KeyboardInterrupt:
        print("\n⚠️ Program dihentikan oleh user")
        relay.value(0)  # Matikan pompa
        break
        
    except Exception as e:
        print(f"❌ Error: {e}")
        time.sleep(1)

print("👋 Program selesai")
