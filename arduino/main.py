import network
import time
import urequests
from machine import Pin, ADC, I2C
import dht
from pico_i2c_lcd import I2cLcd

# ================= CONFIG =================
WIFI_SSID = "Bocil"
WIFI_PASSWORD = "kesayanganku"
SERVER_URL = "http://192.168.18.35:8000/api/monitoring/insert"
DEVICE_ID = "PICO_CABAI_01"
SERVER_INTERVAL = 15000

# ================= SENSOR TUNING =================
SAMPLES = 32          # Oversampling
EMA_ALPHA = 0.25      # Sensitivitas (0.1 halus, 0.4 agresif)

# ================= HARDWARE =================
soil_adc = ADC(26)
dht_sensor = dht.DHT11(Pin(15))
relay = Pin(16, Pin.OUT, Pin.OPEN_DRAIN)
relay.value(1)  # OFF (active low)

# LCD
i2c = I2C(0, sda=Pin(0), scl=Pin(1), freq=400000)
try:
    lcd = I2cLcd(i2c, 0x27, 2, 16)
except:
    lcd = None

# ================= VARIABLE =================
raw_adc = 0
raw_adc_ema = None
temperature = 0
pump_status = False

# ================= WIFI =================
def connect_wifi():
    wlan = network.WLAN(network.STA_IF)
    wlan.active(True)
    wlan.connect(WIFI_SSID, WIFI_PASSWORD)

    for _ in range(15):
        if wlan.isconnected():
            print("WiFi OK:", wlan.ifconfig()[0])
            return wlan
        time.sleep(1)

    print("WiFi FAILED")
    return None

# ================= SENSOR =================
def read_soil():
    global raw_adc, raw_adc_ema

    readings = []
    for _ in range(SAMPLES):
        readings.append(soil_adc.read_u16() >> 4)
        time.sleep(0.003)

    readings.sort()
    median = readings[len(readings)//2]

    avg = sum(readings) // len(readings)

    combined = (median + avg) // 2
    raw_adc = combined

    # EMA filter (lebih sensitif & stabil)
    if raw_adc_ema is None:
        raw_adc_ema = raw_adc
    else:
        raw_adc_ema = int(
            EMA_ALPHA * raw_adc +
            (1 - EMA_ALPHA) * raw_adc_ema
        )

    print(f"RAW:{raw_adc} | EMA:{raw_adc_ema}")

def read_dht():
    global temperature
    try:
        dht_sensor.measure()
        temperature = dht_sensor.temperature()
    except:
        temperature = 0

# ================= RELAY =================
def apply_relay():
    relay.value(0 if pump_status else 1)

# ================= LCD =================
def update_lcd():
    if not lcd:
        return
    lcd.move_to(0, 0)
    lcd.putstr(f"RAW:{raw_adc_ema:4}     ")
    lcd.move_to(0, 1)
    lcd.putstr(f"POMPA:{'ON ' if pump_status else 'OFF'}")

# ================= SERVER =================
def send_data(wlan):
    global pump_status

    if not wlan or not wlan.isconnected():
        return

    payload = {
        "device_id": DEVICE_ID,
        "raw_adc": raw_adc_ema,   # KIRIM YANG SUDAH HALUS
        "raw_adc_raw": raw_adc,   # OPSIONAL (DEBUG)
        "temperature": temperature,
        "relay_status": 1 if pump_status else 0,
        "ip_address": wlan.ifconfig()[0]
    }

    try:
        r = urequests.post(SERVER_URL, json=payload, timeout=5)
        if r.status_code == 200:
            data = r.json()
            if "relay_command" in data:
                pump_status = bool(data["relay_command"])
                apply_relay()
                print("CMD SERVER:", pump_status)
        r.close()
    except Exception as e:
        print("SERVER ERROR:", e)

# ================= MAIN =================
print("=== SMART GARDEN START (SENSITIVE MODE) ===")
wlan = connect_wifi()

last_send = 0
last_lcd = 0

while True:
    now = time.ticks_ms()

    read_soil()
    read_dht()

    if time.ticks_diff(now, last_lcd) > 1000:
        update_lcd()
        last_lcd = now

    if time.ticks_diff(now, last_send) > SERVER_INTERVAL:
        send_data(wlan)
        last_send = now

    time.sleep(0.15)