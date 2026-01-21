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
    lcd_online = True
except:
    lcd = None
    lcd_online = False

# ================= VARIABLE =================
raw_adc = 0
raw_adc_ema = None
temperature = 0
pump_status = False
command_executing = False  # Flag untuk status eksekusi command
last_command = None  # Simpan command terakhir yang diterima

# Hardware status (untuk deteksi sensor online)
dht11_online = False
soil_sensor_online = False
relay_online = True  # Relay selalu online (hardware)

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
    global raw_adc, raw_adc_ema, soil_sensor_online

    try:
        readings = []
        for _ in range(SAMPLES):
            readings.append(soil_adc.read_u16() >> 4)
            time.sleep(0.003)

        readings.sort()
        median = readings[len(readings)//2]
        avg = sum(readings) // len(readings)
        combined = (median + avg) // 2
        raw_adc = combined

        # EMA filter
        if raw_adc_ema is None:
            raw_adc_ema = raw_adc
        else:
            raw_adc_ema = int(
                EMA_ALPHA * raw_adc +
                (1 - EMA_ALPHA) * raw_adc_ema
            )

        # Sensor online jika ADC > 0
        soil_sensor_online = raw_adc > 0
        
        print(f"RAW:{raw_adc} | EMA:{raw_adc_ema}")
    except:
        soil_sensor_online = False
        print("SOIL SENSOR ERROR")

def read_dht():
    global temperature, dht11_online
    
    try:
        dht_sensor.measure()
        temp = dht_sensor.temperature()
        
        if temp > 0 and temp < 60:  # Valid range
            temperature = temp
            dht11_online = True
        else:
            dht11_online = False
    except:
        temperature = 0
        dht11_online = False

# ================= RELAY =================
def apply_relay():
    relay.value(0 if pump_status else 1)
    print(f"RELAY: {'ON' if pump_status else 'OFF'}")

# ================= LCD =================
def update_lcd():
    if not lcd:
        return
    lcd.move_to(0, 0)
    lcd.putstr(f"RAW:{raw_adc_ema if raw_adc_ema else 0:4}     ")
    lcd.move_to(0, 1)
    lcd.putstr(f"POMPA:{'ON ' if pump_status else 'OFF'}")

# ================= SERVER =================
def send_data(wlan):
    global pump_status, command_executing, last_command

    if not wlan or not wlan.isconnected():
        print("[ERROR] WiFi OFFLINE")
        return

    # Payload dengan hardware status + command execution status (komunikasi 2 arah)
    payload = {
        "device_id": DEVICE_ID,
        "raw_adc": raw_adc_ema if raw_adc_ema else 0,
        "raw_adc_raw": raw_adc,
        "temperature": temperature,
        "relay_status": 1 if pump_status else 0,
        "ip_address": wlan.ifconfig()[0],
        "command_executing": command_executing,  # Status eksekusi command
        "last_command": last_command,  # Command terakhir yang diterima
        "hardware_status": {
            "dht11": dht11_online,           # DHT11 sensor online/offline
            "soil_sensor": soil_sensor_online, # Soil sensor online/offline
            "relay": relay_online,             # Relay always online
            "lcd": lcd_online                  # LCD online/offline
        }
    }

    # Retry mechanism untuk handling ETIMEDOUT
    max_retries = 2
    for attempt in range(max_retries):
        r = None
        try:
            if attempt > 0:
                print(f"[RETRY] Attempt {attempt + 1}/{max_retries}")
                time.sleep(1)  # Wait 1s before retry
            
            print(f"[SEND] ADC:{raw_adc_ema} T:{temperature}°C")
            r = urequests.post(SERVER_URL, json=payload, timeout=5)
            
            if r.status_code in (200, 201):
                try:
                    data = r.json()
                    
                    # Tampilkan status hardware di log
                    hw = payload["hardware_status"]
                    print(f"[HW] DHT:{'+' if hw['dht11'] else '-'} SOIL:{'+' if hw['soil_sensor'] else '-'} LCD:{'+' if hw['lcd'] else '-'}")
                    
                    # KOMUNIKASI 2 ARAH: Terima command dari server
                    if "relay_command" in data and data["relay_command"] is not None:
                        new_pump_status = bool(data["relay_command"])
                        
                        if new_pump_status != pump_status:
                            # MULAI EKSEKUSI COMMAND
                            command_executing = True
                            last_command = "ON" if new_pump_status else "OFF"
                            
                            pump_status = new_pump_status
                            apply_relay()
                            
                            print(f"[CMD FROM SERVER] POMPA {last_command} - EXECUTING")
                            
                            # Update LCD immediately
                            update_lcd()
                            
                            # SELESAI EKSEKUSI - akan dikirim di next cycle
                            time.sleep(0.5)  # Beri waktu relay untuk aktif
                            command_executing = False
                            print(f"[CMD EXECUTED] POMPA {last_command} - DONE")
                        else:
                            # Command sama dengan status sekarang, sudah executed
                            command_executing = False
                            
                    print("[OK] Data sent")
                    r.close()
                    return  # Success - exit function
                    
                except ValueError as e:
                    print(f"[ERROR] JSON parse: {e}")
                except Exception as e:
                    print(f"[ERROR] Response: {e}")
            else:
                print(f"[ERROR] HTTP {r.status_code}")
                
        except OSError as e:
            # Network errors (ETIMEDOUT, ECONNREFUSED, etc)
            error_code = e.args[0] if e.args else "?"
            
            if error_code == 110:  # ETIMEDOUT
                print(f"[TIMEOUT] Attempt {attempt + 1} failed")
                if attempt < max_retries - 1:
                    continue  # Try again
                else:
                    print("[ERROR] All retries failed - server not responding")
            else:
                print(f"[ERROR] OSError {error_code}: {e}")
                
            command_executing = False
            
        except Exception as e:
            print(f"[ERROR] {type(e).__name__}: {e}")
            command_executing = False
            
        finally:
            # Always close connection
            if r:
                try:
                    r.close()
                except:
                    pass
        
        # If we reach here and it's the last attempt, break
        if attempt == max_retries - 1:
            break

# ================= MAIN =================
print("=== SMART GARDEN (2-WAY COMMUNICATION) ===")
wlan = connect_wifi()

if not wlan:
    print("[FATAL] WiFi connection failed. Reboot device.")
    import sys
    sys.exit()

last_send = 0
last_lcd = 0
send_failures = 0  # Track consecutive failures

while True:
    now = time.ticks_ms()

    read_soil()
    read_dht()

    if time.ticks_diff(now, last_lcd) > 1000:
        update_lcd()
        last_lcd = now

    if time.ticks_diff(now, last_send) > SERVER_INTERVAL:
        # Check WiFi before sending
        if not wlan.isconnected():
            print("[WIFI] Disconnected! Reconnecting...")
            wlan = connect_wifi()
            if not wlan:
                print("[ERROR] Reconnect failed. Continuing...")
                time.sleep(5)
                continue
        
        # Try to send data
        try:
            send_data(wlan)
            send_failures = 0  # Reset on success
        except:
            send_failures += 1
            print(f"[WARN] Send failed. Failures: {send_failures}")
            
            # If too many failures, reconnect WiFi
            if send_failures >= 3:
                print("[WIFI] Too many failures. Reconnecting...")
                wlan = connect_wifi()
                send_failures = 0
        
        last_send = now

    time.sleep(0.15)