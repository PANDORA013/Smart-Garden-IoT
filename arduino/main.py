# =============================================================================
# RASPBERRY PI PICO W - SMART GARDEN (FINAL)
# =============================================================================

import network
import time
import urequests
from machine import Pin, ADC, PWM, I2C, reset
import dht

# =============================================================================
# KONFIGURASI
# =============================================================================
WIFI_SSID = "CCTV_UISI"
WIFI_PASSWORD = "08121191"
SERVER_URL = "http://10.71.22.84:8000/api/monitoring/insert"
DEVICE_ID = "PICO_CABAI_01"
SEND_INTERVAL = 10  # detik

# =============================================================================
# HARDWARE
# =============================================================================
# DHT22 Temperature & Humidity
dht_sensor = dht.DHT22(Pin(2))

# Relay (GP16)
relay = Pin(16, Pin.OUT)
relay.value(0)

# Soil Moisture Sensor (ADC0 / GP26)
soil_adc = ADC(26)

# Servo Motor (GP9)
servo_pin = PWM(Pin(9))
servo_pin.freq(50)  # 50Hz untuk servo

# LCD I2C (GP0=SDA, GP1=SCL)
try:
    i2c = I2C(0, scl=Pin(1), sda=Pin(0), freq=400000)
    lcd_addr = i2c.scan()
    has_lcd = len(lcd_addr) > 0
    if has_lcd:
        print("LCD Found at:", hex(lcd_addr[0]))
except:
    has_lcd = False
    print("LCD not detected")

# =============================================================================
# KALIBRASI SOIL MOISTURE (WAJIB SESUAIKAN JIKA PERLU)
# =============================================================================
ADC_KERING = 3900   # sensor di udara
ADC_BASAH  = 1400   # sensor di air / tanah basah

# =============================================================================
# VARIABEL
# =============================================================================
temperature = 0
soil_moisture = 0
raw_adc = 0
pump_status = False

# Buffer untuk deteksi sensor disconnect (variability check)
adc_readings = []
ADC_BUFFER_SIZE = 3

# Status koneksi hardware
hardware_status = {
    "dht22": False,
    "soil_sensor": False,
    "relay": False,
    "servo": False,
    "lcd": False
}

# =============================================================================
# WIFI
# =============================================================================
def connect_wifi():
    wlan = network.WLAN(network.STA_IF)
    wlan.active(True)
    wlan.connect(WIFI_SSID, WIFI_PASSWORD)

    print("Connecting WiFi...")
    for _ in range(15):
        if wlan.status() == 3:
            print("WiFi Connected:", wlan.ifconfig()[0])
            return True
        time.sleep(1)

    print("WiFi Failed!")
    return False

# =============================================================================
# SENSOR
# =============================================================================
def read_sensors():
    global temperature, soil_moisture, raw_adc, hardware_status, adc_readings

    # ---- DHT22 ----
    try:
        dht_sensor.measure()
        temperature = dht_sensor.temperature()
        if temperature < 0 or temperature > 60:
            temperature = 0
            hardware_status["dht22"] = False
        else:
            hardware_status["dht22"] = True
    except:
        temperature = 0
        hardware_status["dht22"] = False

    # ---- SOIL MOISTURE ----
    raw_16 = soil_adc.read_u16()
    raw_adc = raw_16 >> 4

    # Tambahkan ke buffer untuk deteksi variability
    adc_readings.append(raw_adc)
    if len(adc_readings) > ADC_BUFFER_SIZE:
        adc_readings.pop(0)

    # Deteksi sensor disconnect dengan multiple checks:
    # 1. ADC > 4050 (sangat tinggi = floating/tidak tercolok)
    # 2. ADC < 50 (sangat rendah = short circuit)
    # 3. High variability (fluktuasi > 500 = unstable/floating)
    is_disconnected = False
    
    if raw_adc > 4050 or raw_adc < 50:
        is_disconnected = True
    elif len(adc_readings) >= ADC_BUFFER_SIZE:
        adc_min = min(adc_readings)
        adc_max = max(adc_readings)
        variability = adc_max - adc_min
        if variability > 500:  # Fluktuasi terlalu besar = sensor tidak stabil
            is_disconnected = True
    
    if is_disconnected:
        soil_moisture = 0
        hardware_status["soil_sensor"] = False
    else:
        soil_moisture = int(
            (ADC_KERING - raw_adc) * 100 / (ADC_KERING - ADC_BASAH)
        )

        if soil_moisture < 0:
            soil_moisture = 0
        if soil_moisture > 100:
            soil_moisture = 100
        
        hardware_status["soil_sensor"] = True

    # Relay dan Servo tetap False (hanya True jika ada command dari server)
    hardware_status["relay"] = False
    hardware_status["servo"] = False

    print("\n" + "="*40)
    print("SENSOR DATA")
    print("="*40)
    print("DHT22      :", temperature, "°C", "[OK]" if hardware_status["dht22"] else "[FAIL]")
    print("Soil Sensor:", soil_moisture, "%", "[OK]" if hardware_status["soil_sensor"] else "[FAIL]")
    print("ADC Value  :", raw_adc)
    if is_disconnected:
        print("⚠️  SOIL SENSOR DISCONNECTED!")
    print("="*40)

# =============================================================================
# SERVO CONTROL
# =============================================================================
def set_servo_angle(angle):
    if not hardware_status["servo"]:
        return
    duty = int(1000 + (angle / 180) * 8000)
    servo_pin.duty_u16(duty)

# =============================================================================
# LCD DISPLAY
# =============================================================================
def update_lcd(temp, soil):
    global hardware_status
    if not has_lcd:
        hardware_status["lcd"] = False
        return
    try:
        hardware_status["lcd"] = True
    except:
        hardware_status["lcd"] = False

# =============================================================================
# KIRIM & TERIMA DATA (2-WAY)
# =============================================================================
def send_and_receive():
    global pump_status

    wlan = network.WLAN(network.STA_IF)
    if not wlan.isconnected():
        print("❌ WiFi disconnected!")
        return

    try:
        payload = {
            "device_id": DEVICE_ID,
            "temperature": temperature,
            "soil_moisture": soil_moisture,
            "raw_adc": raw_adc,
            "relay_status": pump_status,
            "ip_address": wlan.ifconfig()[0],
            "hardware_status": hardware_status
        }

        print("\n📤 Sending data to server...")
        print("  Soil:", soil_moisture, "% | Sensor:", "OK" if hardware_status["soil_sensor"] else "FAIL")
        
        res = urequests.post(
            SERVER_URL,
            json=payload,
            headers={"Content-Type": "application/json"},
            timeout=5
        )

        print("✅ Server response:", res.status_code)

        if res.status_code in (200, 201):
            try:
                data = res.json()

                if "relay_command" in data:
                    pump_status = bool(data["relay_command"])
                    relay.value(1 if pump_status else 0)
                    hardware_status["relay"] = True
                    print("🔌 RELAY:", "ON" if pump_status else "OFF")

            except:
                pass

        res.close()

    except Exception as e:
        print("❌ Send error:", e)

# =============================================================================
# MAIN
# =============================================================================
print("\nSMART GARDEN PICO W")
print("Device:", DEVICE_ID)

if not connect_wifi():
    time.sleep(3)
    reset()

while True:
    read_sensors()
    send_and_receive()
    update_lcd(temperature, soil_moisture)
    time.sleep(SEND_INTERVAL)
