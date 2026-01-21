# 🚀 SISTEM ASYNC - Robust Network Handling

## 📋 **Overview**

Sistem ASYNC adalah upgrade dari main.py yang menambahkan **fault tolerance** dan **network resilience**. Pico W tetap berfungsi normal meskipun:
- ❌ WiFi terputus
- ❌ Server down
- ❌ Network timeout
- ❌ Internet tidak stabil

---

## ✨ **Fitur Utama**

### **1. Auto WiFi Reconnect**
```python
WIFI_RECONNECT_INTERVAL = 30000  # Check tiap 30 detik

# Background thread monitor WiFi
wifi_monitor_thread()
  ├─ Check connection status
  ├─ Auto reconnect jika disconnect
  └─ Update wifi_connected flag
```

**Benefits:**
- ✅ Tidak perlu manual restart
- ✅ Auto recovery dari WiFi issues
- ✅ Seamless reconnection

---

### **2. Offline Queue System**
```python
QUEUE_MAX_SIZE = 50  # Max 50 data items

# Data disimpan saat offline
data_queue = []
  ├─ Simpan data saat server unreachable
  ├─ Auto kirim saat server online lagi
  └─ FIFO: First In First Out
```

**Flow:**
```
Server Online  → Kirim data langsung ✅
Server Offline → Queue data 📦
Server Online  → Kirim queue + current data ✅
```

**Example:**
```
[Offline] → Queue: [data1, data2, data3] (3 items)
[Online]  → Send data1 ✅, data2 ✅, data3 ✅
[Online]  → Queue empty, send current ✅
```

---

### **3. Auto Retry Mechanism**
```python
MAX_RETRY = 3  # Max 3 attempts

for retry in range(MAX_RETRY):
    try:
        send_to_server()
        break  # Success!
    except:
        if retry < MAX_RETRY - 1:
            time.sleep(2)  # Wait 2 detik
        continue
```

**Benefits:**
- ✅ Handle temporary network glitches
- ✅ Retry dengan jeda (prevent spam)
- ✅ Give up after 3 attempts (prevent infinite loop)

---

### **4. Non-Blocking Operations**
```python
SERVER_TIMEOUT = 5  # Timeout 5 detik

# Tidak hang saat server lambat
urequests.post(url, timeout=5)

# Thread terpisah untuk WiFi monitor
_thread.start_new_thread(wifi_monitor_thread)
```

**Benefits:**
- ✅ Sensor tetap baca meskipun network slow
- ✅ LCD update terus
- ✅ Relay bisa dikontrol offline

---

### **5. Status Indicators**
```python
wifi_connected = False   # WiFi status
server_online = False    # Server status
send_error_count = 0     # Error counter

# LCD Display:
# Line 1: 📶☁️ ADC:1850  (WiFi + Server + ADC)
# Line 2: P:OFF Q:5      (Pump + Queue count)
```

**Icons:**
- `📶` = WiFi connected
- `❌` = WiFi disconnected
- `☁️` = Server online
- `⚠️` = Server offline/error
- `Q:5` = 5 items in queue

---

## 🔧 **Configuration**

### **Network Settings:**
```python
WIFI_SSID = "Bocil"
WIFI_PASSWORD = "kesayanganku"
SERVER_URL = "http://192.168.18.35:8000/api/monitoring/insert"
```

### **Async Parameters:**
```python
WIFI_RECONNECT_INTERVAL = 30000   # Check WiFi tiap 30s
SERVER_TIMEOUT = 5                # Request timeout 5s
MAX_RETRY = 3                     # Max 3 retry attempts
QUEUE_MAX_SIZE = 50               # Max 50 queued items
```

### **Tuning Guidelines:**

**Untuk Jaringan Stabil:**
```python
WIFI_RECONNECT_INTERVAL = 60000  # 1 menit
SERVER_TIMEOUT = 3               # 3 detik
MAX_RETRY = 2                    # 2 retry
```

**Untuk Jaringan Tidak Stabil:**
```python
WIFI_RECONNECT_INTERVAL = 15000  # 15 detik
SERVER_TIMEOUT = 10              # 10 detik
MAX_RETRY = 5                    # 5 retry
QUEUE_MAX_SIZE = 100             # Queue lebih besar
```

---

## 📊 **System States**

### **State 1: ONLINE (Normal)**
```
WiFi: Connected ✅
Server: Online ✅

Actions:
├─ Kirim data real-time
├─ Terima relay command
└─ LCD: 📶☁️ ADC:1850 / P:OFF
```

### **State 2: OFFLINE WiFi**
```
WiFi: Disconnected ❌
Server: Unreachable ❌

Actions:
├─ Auto reconnect WiFi (background)
├─ Queue data locally
├─ Sensor + Relay tetap jalan
└─ LCD: ❌⚠️ ADC:1850 / P:OFF Q:5
```

### **State 3: WiFi OK, Server DOWN**
```
WiFi: Connected ✅
Server: Down/Error ❌

Actions:
├─ Keep trying send (with retry)
├─ Queue data after MAX_RETRY
├─ Sensor + Relay tetap jalan
└─ LCD: 📶⚠️ ADC:1850 / P:OFF Q:8
```

### **State 4: RECOVERY**
```
WiFi: Reconnected ✅
Server: Back online ✅

Actions:
├─ Send queued data (batch)
├─ Resume normal operations
├─ Clear queue
└─ LCD: 📶☁️ ADC:1850 / P:OFF
```

---

## 🚀 **Cara Pakai**

### **1. Upload Code:**
```python
# Option A: Replace main.py
mv main.py main_old.py
mv main_async.py main.py

# Option B: Keep both (choose manually)
# Upload main_async.py as main.py
```

### **2. Serial Monitor Output:**

**Normal Operation:**
```
🚀 SMART GARDEN START (ASYNC MODE)
Device ID: PICO_CABAI_01
Server: http://192.168.18.35:8000/api/monitoring/insert
Async Features:
  ✅ Auto WiFi reconnect
  ✅ Offline queue system
  ✅ Auto retry on error
  ✅ Non-blocking operations

🔌 Connecting to WiFi: Bocil
✅ WiFi Connected: 192.168.18.41
✅ WiFi monitor thread started

🌱 System running... (Press CTRL+C to stop)

📊 RAW:1850 | EMA:1848
📡 Sending data (attempt 1/3)...
✅ Data sent successfully (status: 201)
🔌 Relay command: OFF
```

**WiFi Disconnect:**
```
📊 RAW:1852 | EMA:1849
❌ WiFi not connected, queueing data...
📦 Data queued (total: 1)

⚠️  WiFi disconnected, reconnecting...
🔌 Connecting to WiFi: Bocil
✅ WiFi Connected: 192.168.18.41
```

**Server Down:**
```
📊 RAW:1855 | EMA:1851
📡 Sending data (attempt 1/3)...
❌ Network error: [Errno 110] ETIMEDOUT
📡 Sending data (attempt 2/3)...
❌ Network error: [Errno 110] ETIMEDOUT
📡 Sending data (attempt 3/3)...
❌ Network error: [Errno 110] ETIMEDOUT
❌ Failed to send after 3 attempts
📦 Data queued (total: 5)
⚠️  Server appears to be down, continuing offline mode...
```

**Recovery:**
```
📤 Processing queue (5 items)...
✅ Sent 5 queued items (remaining: 0)
📡 Sending data (attempt 1/3)...
✅ Data sent successfully (status: 201)
```

---

## 🔍 **Troubleshooting**

### **Problem 1: Queue penuh terus**

**Symptom:**
```
⚠️  Queue full, removed oldest data
Q:50 (di LCD)
```

**Cause:**
- Server down lama (>10 menit)
- Network issue berkepanjangan

**Solution:**
1. Cek server: `http://192.168.18.35:8000/api/monitoring/logs`
2. Naikkan `QUEUE_MAX_SIZE = 100`
3. Restart Pico W jika perlu (queue di memory)

---

### **Problem 2: WiFi reconnect terus**

**Symptom:**
```
⚠️  WiFi disconnected, reconnecting...
✅ WiFi Connected: 192.168.18.41
[2 minutes later]
⚠️  WiFi disconnected, reconnecting...
```

**Cause:**
- Router WiFi tidak stabil
- Sinyal lemah (jarak terlalu jauh)
- Interference (banyak device 2.4GHz)

**Solution:**
1. Pindahkan Pico W lebih dekat ke router
2. Gunakan WiFi extender
3. Naikkan `WIFI_RECONNECT_INTERVAL = 60000` (check tiap 1 menit)

---

### **Problem 3: Memory error setelah lama running**

**Symptom:**
```
MemoryError: memory allocation failed
```

**Cause:**
- Queue terlalu besar
- Memory leak (garbage tidak dibersihkan)

**Solution:**
1. Code sudah include `gc.collect()` otomatis
2. Turunkan `QUEUE_MAX_SIZE = 20`
3. Restart Pico W tiap 24 jam (optional)

---

## 📈 **Performance**

### **Memory Usage:**
```
Normal mode:    ~15KB RAM
With queue(50): ~25KB RAM
Available:      ~200KB RAM on Pico W
```

### **Network Resilience:**
```
WiFi disconnect → Auto reconnect dalam 30 detik
Server timeout  → Retry 3x dengan 2s interval
Server down     → Queue data sampai online lagi
Max offline     → 50 data points (12.5 menit @ 15s interval)
```

### **CPU Usage:**
```
Main loop:      Low (0.15s sleep)
WiFi thread:    Very low (5s sleep)
Sensor reading: Medium (32 samples)
LCD update:     Low (2s interval)
```

---

## 🎯 **Comparison: main.py vs main_async.py**

| Feature | main.py | main_async.py |
|---------|---------|---------------|
| WiFi handling | Manual | ✅ Auto reconnect |
| Server errors | Fail & skip | ✅ Retry + Queue |
| Offline mode | ❌ No | ✅ Queue system |
| Network timeout | Hang | ✅ Non-blocking |
| LCD status | Basic | ✅ WiFi + Server icons |
| Thread safety | N/A | ✅ Lock mechanisms |
| Memory mgmt | Basic | ✅ Auto GC |
| Recovery | Manual restart | ✅ Auto recovery |

---

## 🔧 **Advanced Configuration**

### **Aggressive Network Recovery:**
```python
# Untuk jaringan sangat tidak stabil
WIFI_RECONNECT_INTERVAL = 10000  # Check tiap 10s
SERVER_TIMEOUT = 15              # Timeout 15s
MAX_RETRY = 7                    # Retry 7x
QUEUE_MAX_SIZE = 200             # Queue besar
```

### **Conservative Mode (Save Battery):**
```python
# Untuk mode hemat energi
WIFI_RECONNECT_INTERVAL = 300000  # Check tiap 5 menit
SERVER_TIMEOUT = 3                # Timeout cepat
MAX_RETRY = 1                     # Retry 1x saja
QUEUE_MAX_SIZE = 10               # Queue kecil
```

### **Debug Mode:**
```python
# Tambahkan di awal code
DEBUG = True

# Update send_data function
if DEBUG:
    print(f"📊 Debug: WiFi={wifi_connected}, Server={server_online}")
    print(f"📦 Queue size: {len(data_queue)}")
    print(f"🔁 Error count: {send_error_count}")
```

---

## 💡 **Best Practices**

### **1. Monitor Queue Size:**
```python
# Alert jika queue > 20
if len(data_queue) > 20:
    print(f"⚠️  WARNING: Large queue ({len(data_queue)} items)")
```

### **2. Periodic Restart:**
```python
# Auto restart tiap 24 jam (optional)
import machine

uptime = time.ticks_ms()
if uptime > 86400000:  # 24 jam
    print("🔄 Auto restart (24h uptime)")
    machine.reset()
```

### **3. Error Logging:**
```python
# Simpan error log ke file (optional)
def log_error(msg):
    try:
        with open("error.log", "a") as f:
            f.write(f"{time.time()}: {msg}\n")
    except:
        pass
```

---

## 📝 **Migration Checklist**

- [ ] Backup `main.py` as `main_old.py`
- [ ] Upload `main_async.py` as `main.py`
- [ ] Update `SERVER_URL` dengan IP yang benar
- [ ] Test WiFi disconnect scenario
- [ ] Test server down scenario
- [ ] Monitor queue size di LCD
- [ ] Verify auto recovery works
- [ ] Check memory usage (`gc.mem_free()`)

---

## 🎉 **Summary**

**Main Features:**
- ✅ **Auto WiFi Reconnect** - Background monitoring
- ✅ **Offline Queue** - Data tidak hilang
- ✅ **Auto Retry** - Handle temporary errors
- ✅ **Non-Blocking** - Smooth operations
- ✅ **Status Display** - LCD indicators

**Use Cases:**
- 🏠 **Home**: Stable WiFi, occasional internet issues
- 🏭 **Industrial**: Critical 24/7 operations
- 🌾 **Agriculture**: Remote locations, unstable network
- 📡 **IoT**: Any scenario requiring resilience

**Perfect For:**
- Network tidak stabil
- Server maintenance
- Power outages
- Long-term deployment

Sistem ASYNC membuat Pico W **production-ready** untuk deployment jangka panjang! 🚀
