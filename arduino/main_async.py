"""
SMART GARDEN IoT - ASYNC VERSION
================================
Robust asynchronous system dengan:
- Non-blocking WiFi & HTTP operations
- Auto-reconnect WiFi
- Data queue dengan retry mechanism
- Watchdog timer untuk stability
- Error handling & logging
"""

import network
import time
import urequests
from machine import Pin, ADC, I2C, WDT
import dht
from pico_i2c_lcd import I2cLcd
import _thread
import json

# ================= CONFIG =================
WIFI_SSID = "Bocil"
WIFI_PASSWORD = "kesayanganku"
SERVER_URL = "http://192.168.18.35:8000/api/monitoring/insert"
DEVICE_ID = "PICO_CABAI_01"
SERVER_INTERVAL = 15000  # ms

# ================= ASYNC CONFIG =================
MAX_QUEUE_SIZE = 50       # Max data buffer
RETRY_ATTEMPTS = 3        # Retry count per request
WIFI_RECONNECT_DELAY = 30 # seconds
HTTP_TIMEOUT = 10         # seconds
WATCHDOG_TIMEOUT = 30000  # 30 seconds (ms)

# ================= SENSOR TUNING =================
SAMPLES = 32
EMA_ALPHA = 0.25

# ================= HARDWARE =================
soil_adc = ADC(26)
dht_sensor = dht.DHT11(Pin(15))
relay = Pin(16, Pin.OUT, Pin.OPEN_DRAIN)
relay.value(1)  # OFF

# LCD
i2c = I2C(0, sda=Pin(0), scl=Pin(1), freq=400000)
try:
    lcd = I2cLcd(i2c, 0x27, 2, 16)
except:
    lcd = None

# ================= GLOBAL VARIABLES =================
raw_adc = 0
raw_adc_ema = None
temperature = 0
pump_status = False

# Network state
wlan = None
wifi_connected = False
server_reachable = True

# Data queue (untuk buffer jika offline)
data_queue = []
queue_lock = _thread.allocate_lock()

# Watchdog
wdt = None
watchdog_enabled = True

# Stats
stats = {
    "sent_success": 0,
    "sent_failed": 0,
    "wifi_reconnects": 0,
    "queue_size": 0,
    "uptime": 0
}

# ================= LOGGING =================
def log(level, message):
    """Simple logging dengan timestamp"""
    timestamp = time.localtime()
    print(f"[{timestamp[3]:02d}:{timestamp[4]:02d}:{timestamp[5]:02d}] {level}: {message}")

# ================= WATCHDOG =================
def init_watchdog():
    """Initialize watchdog timer untuk auto-restart jika hang"""
    global wdt, watchdog_enabled
    try:
        wdt = WDT(timeout=WATCHDOG_TIMEOUT)
        watchdog_enabled = True
        log("INFO", "Watchdog enabled (30s timeout)")
    except Exception as e:
        watchdog_enabled = False
        log("WARN", f"Watchdog not available: {e}")

def feed_watchdog():
    """Feed watchdog untuk prevent restart"""
    global wdt, watchdog_enabled
    if watchdog_enabled and wdt:
        try:
            wdt.feed()
        except:
            pass

# ================= WIFI MANAGEMENT (ASYNC) =================
def connect_wifi():
    """Connect ke WiFi dengan timeout"""
    global wlan, wifi_connected
    
    log("INFO", f"Connecting to WiFi: {WIFI_SSID}...")
    
    wlan = network.WLAN(network.STA_IF)
    wlan.active(True)
    
    if wlan.isconnected():
        wifi_connected = True
        log("INFO", f"WiFi already connected: {wlan.ifconfig()[0]}")
        return True
    
    wlan.connect(WIFI_SSID, WIFI_PASSWORD)
    
    # Wait max 15 seconds
    for i in range(15):
        if wlan.isconnected():
            wifi_connected = True
            log("INFO", f"WiFi connected: {wlan.ifconfig()[0]}")
            return True
        time.sleep(1)
    
    wifi_connected = False
    log("ERROR", "WiFi connection failed!")
    return False

def wifi_monitor_thread():
    """Background thread untuk monitor WiFi status"""
    global wifi_connected, wlan, stats
    
    log("INFO", "WiFi monitor thread started")
    
    while True:
        try:
            if wlan and wlan.isconnected():
                if not wifi_connected:
                    wifi_connected = True
                    log("INFO", "WiFi reconnected!")
            else:
                if wifi_connected:
                    wifi_connected = False
                    log("WARN", "WiFi disconnected!")
                
                # Auto-reconnect
                log("INFO", "Attempting WiFi reconnect...")
                if connect_wifi():
                    stats["wifi_reconnects"] += 1
                else:
                    log("ERROR", "WiFi reconnect failed, retry in 30s...")
                    time.sleep(WIFI_RECONNECT_DELAY)
        except Exception as e:
            log("ERROR", f"WiFi monitor error: {e}")
        
        time.sleep(10)  # Check every 10 seconds

# ================= DATA QUEUE MANAGEMENT =================
def queue_data(payload):
    """Add data to queue (thread-safe)"""
    global data_queue, queue_lock, stats
    
    with queue_lock:
        if len(data_queue) < MAX_QUEUE_SIZE:
            data_queue.append({
                "payload": payload,
                "timestamp": time.time(),
                "retry_count": 0
            })
            stats["queue_size"] = len(data_queue)
            log("DEBUG", f"Data queued (size: {len(data_queue)})")
            return True
        else:
            log("WARN", "Queue full! Dropping oldest data...")
            data_queue.pop(0)  # Remove oldest
            data_queue.append({
                "payload": payload,
                "timestamp": time.time(),
                "retry_count": 0
            })
            return True

def dequeue_data():
    """Get data from queue (thread-safe)"""
    global data_queue, queue_lock, stats
    
    with queue_lock:
        if len(data_queue) > 0:
            item = data_queue.pop(0)
            stats["queue_size"] = len(data_queue)
            return item
        return None

# ================= HTTP SENDER (ASYNC) =================
def send_to_server(payload, retry=True):
    """Send data to server dengan retry mechanism"""
    global pump_status, server_reachable, stats
    
    if not wifi_connected:
        log("WARN", "WiFi not connected, queueing data...")
        queue_data(payload)
        return False
    
    attempt = 0
    max_attempts = RETRY_ATTEMPTS if retry else 1
    
    while attempt < max_attempts:
        try:
            log("DEBUG", f"Sending data (attempt {attempt+1}/{max_attempts})...")
            
            r = urequests.post(
                SERVER_URL, 
                json=payload, 
                timeout=HTTP_TIMEOUT
            )
            
            status = r.status_code
            log("INFO", f"Server response: {status}")
            
            if status in (200, 201):
                # Success!
                data = r.json()
                
                # Update relay command
                if "relay_command" in data:
                    pump_status = bool(data["relay_command"])
                    apply_relay()
                    log("INFO", f"Relay command: {'ON' if pump_status else 'OFF'}")
                
                r.close()
                stats["sent_success"] += 1
                server_reachable = True
                return True
            else:
                log("WARN", f"Server error: {status}")
                r.close()
                attempt += 1
                
        except OSError as e:
            # Network error (timeout, connection refused, etc)
            log("ERROR", f"Network error: {e}")
            server_reachable = False
            attempt += 1
            
        except Exception as e:
            log("ERROR", f"Send error: {e}")
            attempt += 1
        
        if attempt < max_attempts:
            time.sleep(2)  # Wait before retry
    
    # Failed after all attempts
    stats["sent_failed"] += 1
    log("ERROR", "Send failed after all attempts, queueing...")
    queue_data(payload)
    return False

def sender_thread():
    """Background thread untuk kirim data dari queue"""
    global data_queue
    
    log("INFO", "Sender thread started")
    
    while True:
        try:
            # Process queue if WiFi connected
            if wifi_connected and len(data_queue) > 0:
                item = dequeue_data()
                
                if item:
                    log("INFO", f"Processing queued data (retry: {item['retry_count']})")
                    
                    success = send_to_server(item["payload"], retry=False)
                    
                    if not success:
                        item["retry_count"] += 1
                        
                        if item["retry_count"] < RETRY_ATTEMPTS:
                            # Re-queue if retry count not exceeded
                            queue_data(item["payload"])
                        else:
                            log("WARN", "Max retry exceeded, dropping data")
            
            time.sleep(5)  # Check queue every 5 seconds
            
        except Exception as e:
            log("ERROR", f"Sender thread error: {e}")
            time.sleep(5)

# ================= SENSOR FUNCTIONS =================
def read_soil():
    """Read soil sensor dengan EMA filtering"""
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

    # EMA filter
    if raw_adc_ema is None:
        raw_adc_ema = raw_adc
    else:
        raw_adc_ema = int(
            EMA_ALPHA * raw_adc +
            (1 - EMA_ALPHA) * raw_adc_ema
        )

def read_dht():
    """Read DHT11 sensor"""
    global temperature
    try:
        dht_sensor.measure()
        temperature = dht_sensor.temperature()
    except:
        temperature = 0

def apply_relay():
    """Apply relay status"""
    relay.value(0 if pump_status else 1)

def update_lcd():
    """Update LCD display"""
    if not lcd:
        return
    try:
        lcd.move_to(0, 0)
        status = "ON " if pump_status else "OFF"
        wifi = "W" if wifi_connected else "X"
        lcd.putstr(f"ADC:{raw_adc_ema:4} {wifi} {status}")
        
        lcd.move_to(0, 1)
        lcd.putstr(f"Q:{stats['queue_size']:2} T:{temperature:2}C")
    except:
        pass

# ================= STATS DISPLAY =================
def show_stats():
    """Display statistics"""
    log("INFO", "=" * 50)
    log("INFO", "SYSTEM STATISTICS:")
    log("INFO", f"  Uptime: {stats['uptime']} seconds")
    log("INFO", f"  WiFi Status: {'Connected' if wifi_connected else 'Disconnected'}")
    log("INFO", f"  WiFi Reconnects: {stats['wifi_reconnects']}")
    log("INFO", f"  Sent Success: {stats['sent_success']}")
    log("INFO", f"  Sent Failed: {stats['sent_failed']}")
    log("INFO", f"  Queue Size: {stats['queue_size']}/{MAX_QUEUE_SIZE}")
    log("INFO", f"  Server: {'Reachable' if server_reachable else 'Unreachable'}")
    log("INFO", "=" * 50)

# ================= MAIN =================
def main():
    global stats
    
    log("INFO", "=" * 50)
    log("INFO", "SMART GARDEN ASYNC START")
    log("INFO", f"Device: {DEVICE_ID}")
    log("INFO", f"Server: {SERVER_URL}")
    log("INFO", "=" * 50)
    
    # Initialize watchdog
    init_watchdog()
    
    # Initial WiFi connection
    connect_wifi()
    
    # Start background threads
    try:
        _thread.start_new_thread(wifi_monitor_thread, ())
        log("INFO", "WiFi monitor thread spawned")
    except Exception as e:
        log("ERROR", f"Failed to start WiFi monitor: {e}")
    
    try:
        _thread.start_new_thread(sender_thread, ())
        log("INFO", "Sender thread spawned")
    except Exception as e:
        log("ERROR", f"Failed to start sender thread: {e}")
    
    # Main loop
    last_send = 0
    last_lcd = 0
    last_stats = 0
    start_time = time.time()
    
    log("INFO", "Entering main loop...")
    
    while True:
        try:
            now = time.ticks_ms()
            
            # Feed watchdog
            feed_watchdog()
            
            # Update uptime
            stats["uptime"] = int(time.time() - start_time)
            
            # Read sensors (always, even if offline)
            read_soil()
            read_dht()
            
            # Update LCD
            if time.ticks_diff(now, last_lcd) > 1000:
                update_lcd()
                last_lcd = now
            
            # Send data to server
            if time.ticks_diff(now, last_send) > SERVER_INTERVAL:
                payload = {
                    "device_id": DEVICE_ID,
                    "raw_adc": raw_adc_ema,
                    "raw_adc_raw": raw_adc,
                    "temperature": temperature,
                    "relay_status": 1 if pump_status else 0,
                    "ip_address": wlan.ifconfig()[0] if wifi_connected else "0.0.0.0"
                }
                
                # Try send directly first
                if wifi_connected:
                    send_to_server(payload)
                else:
                    # Queue if offline
                    queue_data(payload)
                
                last_send = now
            
            # Show stats every 60 seconds
            if time.ticks_diff(now, last_stats) > 60000:
                show_stats()
                last_stats = now
            
            time.sleep(0.1)
            
        except KeyboardInterrupt:
            log("INFO", "Keyboard interrupt, exiting...")
            break
        except Exception as e:
            log("ERROR", f"Main loop error: {e}")
            time.sleep(1)

# ================= ENTRY POINT =================
if __name__ == "__main__":
    main()
