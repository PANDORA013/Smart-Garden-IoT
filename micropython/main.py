import network
import urequests as requests
import ujson
import time
from machine import Pin, ADC, RTC
from dht import DHT22

# ===========================
# ✅ SUDAH DIKONFIGURASI UNTUK ANDA
# ===========================
SSID = "Bocil"              # WiFi Anda
PASSWORD = "kesayanganku"   # Password WiFi Anda
# IP Server Laravel Anda
SERVER_URL = "http://192.168.18.35:8000/api/monitoring/insert"

# ===========================
# KONFIGURASI HARDWARE
# ===========================
# Sesuai dengan Arduino: Soil Sensor: GP26, DHT: GP2, Relay: GP5
soil_sensor = ADC(26)
dht_sensor = DHT22(Pin(2))
relay = Pin(5, Pin.OUT)

# ===========================
# VARIABEL GLOBAL & DEFAULT
# ===========================
DEVICE_ID = "PICO_CABAI_01"

# Config Defaults (Sesuai source asli)
config = {
    "mode": 1,
    "adc_min": 4095,    # Kering
    "adc_max": 1500,    # Basah
    "batas_kering": 40,
    "batas_basah": 70,
    "jam_pagi": "07:00",
    "jam_sore": "17:00",
    "durasi_siram": 5
}

pump_state = False
pump_start_time = 0
last_send_time = 0
SEND_INTERVAL = 10  # Detik

# Setup RTC (Waktu)
rtc = RTC()

# ===========================
# FUNGSI-FUNGSI UTAMA
# ===========================

def connect_wifi():
    wlan = network.WLAN(network.STA_IF)
    wlan.active(True)
    wlan.connect(SSID, PASSWORD)
    
    print(f"🔌 Connecting to {SSID}...", end="")
    max_wait = 20
    while max_wait > 0:
        if wlan.status() < 0 or wlan.status() >= 3:
            break
        max_wait -= 1
        print(".", end="")
        time.sleep(1)
        
    if wlan.status() == 3:
        print(f"\n✅ WiFi Connected! IP: {wlan.ifconfig()[0]}")
        sync_time() # Sinkronisasi waktu sederhana via HTTP header nanti
    else:
        print("\n❌ WiFi Connection Failed")

def sync_time():
    # Mengambil waktu kasar dari internet jika memungkinkan (Opsional untuk Mode 3)
    # Untuk presisi tinggi disarankan pakai library ntptime
    try:
        import ntptime
        ntptime.settime()
        print("⏰ Time Synced with NTP")
    except:
        print("⚠️ NTP Sync Failed (OK untuk Mode 1 & 2)")

def map_adc_to_percent(adc_val, min_val, max_val):
    # Clamp
    if adc_val > min_val: adc_val = min_val
    if adc_val < max_val: adc_val = max_val
    
    # Calculate Percent
    try:
        percent = ((min_val - adc_val) / (min_val - max_val)) * 100.0
    except ZeroDivisionError:
        percent = 0.0
        
    return max(0.0, min(100.0, percent))

def control_pump(soil_moisture, temp):
    global pump_state, pump_start_time, config
    should_pump_on = False
    current_millis = time.ticks_ms()
    
    # === MODE 1: BASIC THRESHOLD ===
    if config['mode'] == 1:
        if soil_moisture < config['batas_kering'] and not pump_state:
            should_pump_on = True
            print("🟢 Mode 1: Tanah kering, Pompa ON")
        elif soil_moisture >= config['batas_basah'] and pump_state:
            should_pump_on = False
            print("🟢 Mode 1: Tanah basah, Pompa OFF")
        else:
            should_pump_on = pump_state

    # === MODE 2: FUZZY LOGIC AI ===
    elif config['mode'] == 2:
        if soil_moisture < 40 and not pump_state:
            duration = 5
            # Logika Fuzzy Sederhana
            if temp > 30:
                duration = 8
                print("🔵 Mode 2: Panas -> Siram 8 detik")
            elif temp > 25:
                duration = 5
                print("🔵 Mode 2: Sedang -> Siram 5 detik")
            else:
                duration = 3
                print("🔵 Mode 2: Dingin -> Siram 3 detik")
            
            should_pump_on = True
            pump_start_time = current_millis
            config['durasi_siram'] = duration # Override durasi sementara
            
        elif pump_state and (time.ticks_diff(current_millis, pump_start_time) >= config['durasi_siram'] * 1000):
            should_pump_on = False
            print("🔵 Mode 2: Durasi selesai")
        else:
            should_pump_on = pump_state

    # === MODE 3: SCHEDULE ===
    elif config['mode'] == 3:
        # Format Jam HH:MM
        t = time.localtime()
        current_time = "{:02d}:{:02d}".format(t[3], t[4]) # Hour:Minute
        
        if (current_time == config['jam_pagi'] or current_time == config['jam_sore']) and not pump_state:
             # Cek agar tidak looping trigger di menit yang sama (perlu flag tambahan di real implementation)
             # Di sini kita pakai logika sederhana timer
             if time.ticks_diff(current_millis, pump_start_time) > 60000: # Debounce 1 menit
                should_pump_on = True
                pump_start_time = current_millis
                print(f"🔴 Mode 3: Jadwal Siram {current_time}")
        elif pump_state and (time.ticks_diff(current_millis, pump_start_time) >= config['durasi_siram'] * 1000):
            should_pump_on = False
            print("🔴 Mode 3: Selesai")
        else:
            should_pump_on = pump_state

    # Hardware Actuation
    if should_pump_on != pump_state:
        pump_state = should_pump_on
        relay.value(1 if pump_state else 0)

def send_data(raw_adc, soil, temp):
    global config, last_send_time
    
    wlan = network.WLAN(network.STA_IF)
    if not wlan.isconnected():
        return

    payload = {
        "device_id": DEVICE_ID,
        "temperature": temp,
        "soil_moisture": soil,
        "raw_adc": raw_adc,
        "relay_status": pump_state,
        "ip_address": wlan.ifconfig()[0]
    }
    
    print("\n📤 Sending data...")
    print(f"Data: {payload}")
    try:
        response = requests.post(SERVER_URL, json=payload, headers={'Content-Type': 'application/json'})
        if response.status_code in [200, 201]:
            print(f"✅ Server Response Code: {response.status_code}")
            print(f"Response: {response.text}")
            try:
                parse_config(response.json())
            except:
                print("⚠️ No config in response")
        else:
            print(f"❌ HTTP Error: {response.status_code}")
        response.close()
    except Exception as e:
        print(f"❌ Connection Error: {e}")

def parse_config(json_data):
    global config
    if 'config' not in json_data:
        print("ℹ️ No config update from server")
        return

    new_conf = json_data['config']
    changed = False
    
    # Loop keys to update
    for key in config:
        if key in new_conf and new_conf[key] != config[key]:
            print(f"🔄 Config {key} changed: {config[key]} -> {new_conf[key]}")
            config[key] = new_conf[key]
            changed = True
            
    if changed:
        print("✅ Config Updated from Server!")
    else:
        print("ℹ️ No config changes")

# ===========================
# STARTUP MESSAGE
# ===========================
print("\n" + "="*40)
print("🌱 PICO W SMART GARDEN GATEWAY")
print("   MicroPython Version")
print("="*40)
print(f"Device ID: {DEVICE_ID}")
print(f"WiFi SSID: {SSID}")
print(f"Server: {SERVER_URL}")
print("="*40 + "\n")

# ===========================
# MAIN LOOP
# ===========================
connect_wifi()

print("\n🚀 Starting main loop...\n")

while True:
    try:
        # 1. Baca Sensor
        raw_adc = soil_sensor.read_u16() >> 4 # Convert 16bit to 12bit (0-4095) agar sama dengan Arduino
        soil_moisture = map_adc_to_percent(raw_adc, config['adc_min'], config['adc_max'])
        
        try:
            dht_sensor.measure()
            temp = dht_sensor.temperature()
        except:
            temp = 28.0 # Default fallback
            print("⚠️ DHT22 Error, using default 28.0°C")
            
        # 2. Kontrol Pompa
        control_pump(soil_moisture, temp)
        
        # 3. Kirim Data (Non-blocking check)
        if time.time() - last_send_time >= SEND_INTERVAL:
            send_data(raw_adc, soil_moisture, temp)
            last_send_time = time.time()
            
        time.sleep(1)
        
    except KeyboardInterrupt:
        print("\n🛑 Stopped by user")
        relay.value(0)  # Matikan relay
        break
    except Exception as e:
        print(f"⚠️ Error in loop: {e}")
        time.sleep(1)
