# =============================================================================
# RASPBERRY PI PICO W - SMART GARDEN (Versi Thonny/MicroPython)
# =============================================================================
# WiFi: AAB
# Pass: 081211915711
# Server: 192.168.0.102:8000 (FIXED!)
# =============================================================================

import network
import time
import ujson
import urequests
import ntptime
from machine import Pin, ADC, RTC
import dht

# =============================================================================
# 1. KONFIGURASI JARINGAN
# =============================================================================
WIFI_SSID = "AAB"
WIFI_PASSWORD = "081211915711"

# ✅ FIXED: IP Laptop yang benar (192.168.0.102)
SERVER_URL = "http://192.168.0.102:8000/api/monitoring/insert" 

DEVICE_ID = "PICO_CABAI_01"

# =============================================================================
# 2. INISIALISASI HARDWARE
# =============================================================================
# Sensor DHT22 di Pin GP2
try:
    dht_sensor = dht.DHT22(Pin(2))
except:
    print("Error: Sensor DHT tidak terdeteksi")

# Relay (Pompa) di Pin GP5
relay = Pin(5, Pin.OUT)
relay.value(0) # Default Mati (OFF)

# Sensor Tanah di Pin GP26 (ADC0)
soil_adc = ADC(26)

# =============================================================================
# 3. VARIABEL & SETTING
# =============================================================================
# Kalibrasi (Range ADC Pico: 0 - 65535, kita ubah ke 12-bit biar sama kayak C++)
ADC_MIN = 4095   # Kering (Udara)
ADC_MAX = 1500   # Basah (Air)

BATAS_KERING = 40  # Pompa ON di bawah ini
BATAS_BASAH = 70   # Pompa OFF di atas ini

MODE = 1           # 1=Basic, 2=Fuzzy, 3=Jadwal, 4=Manual
JAM_PAGI = "07:00"
JAM_SORE = "17:00"
SEND_INTERVAL = 10 # Kirim data tiap 10 detik

pump_status = False
temperature = 0
soil_moisture = 0
raw_adc_12bit = 0

# =============================================================================
# 4. FUNGSI UTAMA
# =============================================================================

def connect_wifi():
    wlan = network.WLAN(network.STA_IF)
    wlan.active(True)
    wlan.connect(WIFI_SSID, WIFI_PASSWORD)
    
    print(f"\n📡 Menghubungkan ke: {WIFI_SSID} ...")
    
    # Tunggu max 15 detik
    for i in range(15):
        if wlan.status() < 0 or wlan.status() >= 3:
            break
        print(".", end="")
        time.sleep(1)
        
    if wlan.status() != 3:
        print("\n❌ Gagal Konek WiFi. Cek Password atau Hotspot HP Anda.")
        return False
    else:
        print("\n✅ WiFi Terhubung!")
        ip = wlan.ifconfig()[0]
        print(f"   IP Pico: {ip}")
        print(f"   Akan kirim data ke: {SERVER_URL}")
        return True

def map_value(x, in_min, in_max, out_min, out_max):
    return (x - in_min) * (out_max - out_min) / (in_max - in_min) + out_min

def read_sensors():
    global temperature, soil_moisture, raw_adc_12bit
    
    # Baca Suhu
    try:
        dht_sensor.measure()
        temperature = dht_sensor.temperature()
    except:
        temperature = 0 # Error value

    # Baca Tanah (Konversi 16-bit ke 12-bit)
    raw_16bit = soil_adc.read_u16()
    raw_adc_12bit = raw_16bit >> 4 
    
    # Hitung Persen (Makin besar nilai ADC = Makin Kering)
    try:
        soil_moisture = int(map_value(raw_adc_12bit, ADC_MIN, ADC_MAX, 0, 100))
    except:
        soil_moisture = 0
        
    # Batasi 0-100%
    if soil_moisture < 0: soil_moisture = 0
    if soil_moisture > 100: soil_moisture = 100

    print("\n📊 DATA SENSOR:")
    print(f"   Temp: {temperature}°C")
    print(f"   Tanah: {soil_moisture}% (ADC: {raw_adc_12bit})")
    print(f"   Pompa: {'NYALA 🟢' if pump_status else 'MATI 🔴'}")

def control_pump():
    global pump_status
    target_pump = False
    
    # Mode 1: Basic
    if MODE == 1:
        if soil_moisture < BATAS_KERING and not pump_status:
            target_pump = True
        elif soil_moisture > BATAS_BASAH and pump_status:
            target_pump = False
        else:
            target_pump = pump_status
            
    # Eksekusi Relay
    if target_pump != pump_status:
        pump_status = target_pump
        relay.value(1 if pump_status else 0)
        print(f"⚡ POMPA BERUBAH JADI: {'ON' if pump_status else 'OFF'}")

def send_data():
    if not network.WLAN(network.STA_IF).isconnected():
        print("❌ WiFi Putus")
        return

    try:
        ip_addr = network.WLAN(network.STA_IF).ifconfig()[0]
        payload = {
            "device_id": DEVICE_ID,
            "temperature": temperature,
            "soil_moisture": soil_moisture,
            "raw_adc": raw_adc_12bit,
            "relay_status": pump_status,
            "ip_address": ip_addr
        }
        
        print(f"📡 Mengirim ke Server...")
        res = urequests.post(SERVER_URL, json=payload, headers={'Content-Type': 'application/json'}, timeout=5)
        
        if res.status_code == 200 or res.status_code == 201:
            print(f"✅ Server Balas: {res.status_code} - Data Tersimpan!")
        else:
            print(f"⚠️  Server Balas: {res.status_code}")
            
        res.close()
        
    except Exception as e:
        print(f"❌ Gagal Kirim: {e}")
        print("   Cek:")
        print("   1. Laravel jalan di http://192.168.0.102:8000")
        print("   2. Firewall Windows sudah dibuka (port 8000)")
        print("   3. Pico W dan Laptop di jaringan yang sama")

# =============================================================================
# LOOP UTAMA
# =============================================================================
print("\n" + "="*60)
print("  🌱 PICO W SMART GARDEN - MicroPython")
print("="*60)
print(f"  Device ID: {DEVICE_ID}")
print(f"  Server: {SERVER_URL}")
print("="*60 + "\n")

# 1. Konek WiFi
if not connect_wifi():
    print("❌ Restarting karena gagal konek WiFi...")
    time.sleep(3)
    machine.reset()

print("\n✅ SISTEM SIAP! Mulai monitoring...\n")

# 2. Loop
while True:
    read_sensors()
    control_pump()
    send_data()
    print(f"💤 Tidur {SEND_INTERVAL} detik...\n")
    time.sleep(SEND_INTERVAL)
