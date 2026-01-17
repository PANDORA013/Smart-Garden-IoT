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
SERVER_INTERVAL = 15000  # milidetik (15 detik)

# Interval pengiriman data ke server (milidetik)
# Disarankan minimal 10000 (10 detik) agar tidak spamming/hang
SERVER_INTERVAL = 15000 

# ================= HARDWARE =================
# Pastikan Pin sesuai dengan wiring fisik Anda
dht_sensor = dht.DHT11(Pin(15, Pin.IN, Pin.PULL_UP))
soil_adc = ADC(26) # Pin GP26

# RELAY: Ganti angka 20 dengan pin yang Anda gunakan (misal 16 atau 20)
# Gunakan Pin.OPEN_DRAIN agar kompatibel dengan relay 5V
relay = Pin(16, Pin.OUT, Pin.OPEN_DRAIN) 

# Pastikan logika Relay (Active Low vs Active High)
# Active Low: 1 = MATI, 0 = NYALA
relay.value(1) # Matikan saat awal

i2c = I2C(0, sda=Pin(0), scl=Pin(1), freq=400000)
# Cek alamat I2C LCD, biasanya 0x27, tapi kadang 0x3F
lcd = I2cLcd(i2c, 0x27, 2, 16)

# ================= KALIBRASI (DATA BARU) =================
# PENTING: Sesuaikan dengan sensor kamu!
# Cara kalibrasi:
# 1. Pastikan tanah KERING (jangan siram beberapa jam) → Catat nilai RAW (ini ADC_KERING)
# 2. SIRAM tanah hingga basah → Catat nilai RAW (ini ADC_BASAH)

# ===== CARA KALIBRASI CEPAT =====
# 1. Upload code ini ke Pico W
# 2. Buka Serial Monitor (Thonny/VS Code)
# 3. Pastikan tanah KERING → Lihat nilai RAW → Copy angka ke ADC_KERING
# 4. SIRAM tanah hingga basah → Lihat nilai RAW → Copy ke ADC_BASAH
# 5. Update nilai di bawah ini, lalu upload ulang

# CATATAN: Nilai ADC dalam format 12-bit (0-4095)
# CATATAN: Sistem otomatis deteksi jenis sensor:
#   - CAPACITIVE: Tanah KERING = ADC tinggi (3000-4000), Tanah BASAH = ADC rendah (500-1500)
#   - RESISTIVE: Tanah KERING = ADC rendah (0-100), Tanah BASAH = ADC tinggi (1500-2000)
ADC_KERING = 3800   # Nilai saat tanah kering (UPDATE SESUAI TEST)
ADC_BASAH  = 1500   # Nilai saat tanah basah (UPDATE SESUAI TEST)

# ===== MODE KALIBRASI OTOMATIS =====
# Set AUTO_CALIBRATION = True untuk mode kalibrasi otomatis
# Sistem akan menggunakan nilai dari server (config.adc_min dan config.adc_max)
AUTO_CALIBRATION = False  # False = gunakan nilai manual di atas

# ===== MODE KALIBRASI STARTUP =====
# Set STARTUP_CALIBRATION = True untuk kalibrasi saat startup
# Sistem akan memandu Anda untuk kalibrasi sensor sebelum run
STARTUP_CALIBRATION = True  # True = kalibrasi saat startup

# Jika kelembapan di bawah threshold ini, pompa nyala
SOIL_THRESHOLD = 40

# ================= VARIABLE =================
temperature = 0
soil_moisture = 0
raw_adc = 0
pump_status = False

# Buffer untuk deteksi sensor disconnect
adc_readings = []
ADC_BUFFER_SIZE = 3

# Status koneksi hardware (untuk website)
hardware_status = {
    "dht11": False,
    "soil_sensor": False,
    "relay": False,
    "lcd": False
}

# ================= KALIBRASI STARTUP =================
def calibrate_sensor():
    """
    Fungsi kalibrasi sensor soil yang ditanam di tanah
    Memandu user untuk kalibrasi kondisi KERING vs BASAH
    """
    global ADC_KERING, ADC_BASAH
    
    lcd.clear()
    lcd.putstr("KALIBRASI MODE")
    
    print("\n" + "=" * 50)
    print("🔧 MODE KALIBRASI SENSOR SOIL")
    print("=" * 50)
    print("Sistem akan membantu Anda mengkalibrasi sensor.")
    print("PENTING: Sensor TIDAK perlu dicabut dari tanah!")
    print("Kalibrasi dilakukan dengan SIRAM tanah saja.")
    print("=" * 50)
    
    # STEP 1: Kalibrasi Tanah KERING
    print("\n📍 STEP 1: KALIBRASI TANAH KERING")
    print("   Instruksi: Pastikan tanah dalam kondisi KERING")
    print("   (Jangan siram tanah beberapa jam sebelumnya)")
    print("   Tekan CTRL+C untuk skip kalibrasi ini\n")
    
    lcd.clear()
    lcd.putstr("STEP 1: KERING")
    lcd.move_to(0, 1)
    lcd.putstr("Tanah kering!")
    
    time.sleep(3)
    print("   Mengukur dalam 3 detik...")
    time.sleep(1)
    print("   3...")
    time.sleep(1)
    print("   2...")
    time.sleep(1)
    print("   1...")
    time.sleep(1)
    
    # Ambil 10 sampel untuk rata-rata
    samples_kering = []
    print("   📊 Mengambil 10 sampel dari tanah KERING...")
    for i in range(10):
        raw_16bit = soil_adc.read_u16()
        raw_12bit = raw_16bit >> 4
        samples_kering.append(raw_12bit)
        print(f"      Sample {i+1}/10: {raw_12bit}")
        time.sleep(0.3)
    
    ADC_KERING = sum(samples_kering) // len(samples_kering)
    print(f"\n   ✅ ADC_KERING (Tanah kering): {ADC_KERING}")
    
    # STEP 2: Kalibrasi Tanah BASAH
    print("\n📍 STEP 2: KALIBRASI TANAH BASAH")
    print("   Instruksi: SIRAM tanah dengan AIR secukupnya")
    print("   (Siram hingga tanah benar-benar lembab/basah)")
    print("   Tekan CTRL+C untuk skip kalibrasi ini\n")
    
    lcd.clear()
    lcd.putstr("STEP 2: BASAH")
    lcd.move_to(0, 1)
    lcd.putstr("SIRAM tanah!")
    
    time.sleep(10)  # Kasih waktu lebih lama untuk siram dan meresap
    print("   Tunggu air meresap ke sensor...")
    time.sleep(2)
    print("   Mengukur dalam 3 detik...")
    time.sleep(1)
    print("   3...")
    time.sleep(1)
    print("   2...")
    time.sleep(1)
    print("   1...")
    time.sleep(1)
    
    # Ambil 10 sampel untuk rata-rata
    samples_basah = []
    print("   📊 Mengambil 10 sampel dari tanah BASAH...")
    for i in range(10):
        raw_16bit = soil_adc.read_u16()
        raw_12bit = raw_16bit >> 4
        samples_basah.append(raw_12bit)
        print(f"      Sample {i+1}/10: {raw_12bit}")
        time.sleep(0.3)
    
    ADC_BASAH = sum(samples_basah) // len(samples_basah)
    print(f"\n   ✅ ADC_BASAH (Tanah basah): {ADC_BASAH}")
    
    # VALIDASI HASIL
    print("\n" + "=" * 50)
    print("📊 HASIL KALIBRASI:")
    print("=" * 50)
    print(f"   ADC_KERING (Tanah kering): {ADC_KERING}")
    print(f"   ADC_BASAH (Tanah basah):   {ADC_BASAH}")
    print(f"   Range ADC: {ADC_KERING - ADC_BASAH}")
    
    # Validasi range dan deteksi jenis sensor
    range_adc = abs(ADC_KERING - ADC_BASAH)
    
    # Cek apakah range cukup besar
    if range_adc < 100:
        print("\n   ⚠️  WARNING: Range terlalu kecil (< 100)!")
        print("   ⚠️  Pastikan tanah benar-benar KERING di STEP 1")
        print("   ⚠️  dan benar-benar BASAH di STEP 2.")
        lcd.clear()
        lcd.putstr("Range kecil!")
        lcd.move_to(0, 1)
        lcd.putstr("Ulangi!")
        time.sleep(3)
        return False
    
    # Deteksi jenis sensor berdasarkan nilai ADC
    if ADC_KERING > ADC_BASAH:
        sensor_type = "CAPACITIVE"
        print("\n   📡 Jenis sensor: CAPACITIVE (Kering=Tinggi, Basah=Rendah)")
    else:
        sensor_type = "RESISTIVE"
        print("\n   📡 Jenis sensor: RESISTIVE (Kering=Rendah, Basah=Tinggi)")
        # Untuk resistive sensor, tukar nilai agar formula tetap konsisten
        ADC_KERING, ADC_BASAH = ADC_BASAH, ADC_KERING
        print(f"   🔄 Nilai ditukar: ADC_KERING={ADC_KERING}, ADC_BASAH={ADC_BASAH}")
    
    print("\n   ✅ Kalibrasi BERHASIL!")
    print("   ✅ Sensor siap digunakan!")
    print(f"   ✅ Sensor type: {sensor_type}")
    print("=" * 50)
    
    lcd.clear()
    lcd.putstr("Kalibrasi OK!")
    lcd.move_to(0, 1)
    lcd.putstr(f"{sensor_type[:3]}: {range_adc}")
    time.sleep(2)
    
    return True

# ================= WIFI =================
def connect_wifi():
    lcd.clear()
    lcd.putstr("Connect WiFi...")
    wlan = network.WLAN(network.STA_IF)
    wlan.active(True)
    wlan.connect(WIFI_SSID, WIFI_PASSWORD)

    print("Connecting WiFi...")
    max_wait = 15
    while max_wait > 0:
        if wlan.status() < 0 or wlan.status() >= 3:
            break
        max_wait -= 1
        print('Waiting for connection...')
        time.sleep(1)

    if wlan.status() != 3:
        print("WiFi FAILED")
        lcd.clear()
        lcd.putstr("WiFi FAILED")
        time.sleep(2)
        return None
    else:
        print("WiFi OK:", wlan.ifconfig()[0])
        lcd.clear()
        lcd.putstr("WiFi OK!")
        time.sleep(1)
        return wlan

# ================= SENSOR =================
def read_dht():
    global temperature, hardware_status
    try:
        dht_sensor.measure()
        temp = dht_sensor.temperature()
        # Validasi sederhana agar tidak 0 tiba-tiba
        if temp > 0 and temp < 100:
            temperature = temp
            hardware_status["dht11"] = True
        else:
            temperature = 0
            hardware_status["dht11"] = False
    except Exception as e:
        temperature = 0
        hardware_status["dht11"] = False
        print("DHT Error:", e)

def read_soil():
    global soil_moisture, raw_adc, hardware_status, adc_readings, ADC_KERING, ADC_BASAH
    
    # Baca ADC 16-bit dan konversi ke 12-bit (untuk konsistensi dengan tool kalibrasi)
    raw_16bit = soil_adc.read_u16()
    raw_adc = raw_16bit >> 4  # Shift 4 bit ke kanan: 0-65535 → 0-4095

    # Tambahkan ke buffer untuk deteksi disconnect
    adc_readings.append(raw_adc)
    if len(adc_readings) > ADC_BUFFER_SIZE:
        adc_readings.pop(0)

    # Deteksi sensor disconnect HANYA dengan variability check
    # (Sensor disconnect biasanya fluktuasi ekstrem karena floating pin)
    is_disconnected = False
    
    if len(adc_readings) >= ADC_BUFFER_SIZE:
        adc_min = min(adc_readings)
        adc_max = max(adc_readings)
        variability = adc_max - adc_min
        
        # Jika fluktuasi > 1000 dalam 3 readings = sensor tidak stabil (disesuaikan untuk 12-bit)
        if variability > 1000:
            is_disconnected = True
            print(f"⚠️  High variability detected: {variability} (min:{adc_min}, max:{adc_max})")
    
    if is_disconnected:
        soil_moisture = 0
        hardware_status["soil_sensor"] = False
        print("⚠️  SOIL SENSOR DISCONNECTED/UNSTABLE!")
    else:
        # GUNAKAN nilai kalibrasi (lokal atau dari server)
        adc_kering = ADC_KERING
        adc_basah = ADC_BASAH
        
        # CLAMPING: Batasi nilai agar tidak bablas
        val_constrained = raw_adc
        if val_constrained < adc_basah:
            val_constrained = adc_basah
        if val_constrained > adc_kering:
            val_constrained = adc_kering

        # RUMUS LINEAR: Semakin TINGGI ADC = Semakin KERING (untuk capacitive sensor)
        # Formula DIBALIK: 100 - (ADC - ADC_BASAH) / (ADC_KERING - ADC_BASAH) * 100
        # Hasil: 100% = Sangat Basah, 0% = Sangat Kering
        try:
            # Hitung persentase kekeringan dulu
            dryness = int((val_constrained - adc_basah) / (adc_kering - adc_basah) * 100)
            # Balik jadi kelembaban (100% = basah, 0% = kering)
            soil_moisture = 100 - dryness
            # Batasi 0-100%
            if soil_moisture < 0: soil_moisture = 0
            if soil_moisture > 100: soil_moisture = 100
        except ZeroDivisionError:
            soil_moisture = 0
        
        hardware_status["soil_sensor"] = True
    
    print(f"RAW: {raw_adc} (12-bit) | SOIL: {soil_moisture}% | Cal: {adc_kering}/{adc_basah} | Status: {'OK' if hardware_status['soil_sensor'] else 'FAIL'}")


# ================= RELAY CONTROL (FULL REMOTE - NO AUTO LOGIC) =================
# Relay dikontrol 100% dari website via relay_command
# Pico W TIDAK punya logika otomatis sendiri
def apply_relay_status():
    """Apply relay status sesuai variable pump_status"""
    global hardware_status
    
    if pump_status:
        relay.value(0)  # ON (Active Low)
        hardware_status["relay"] = True
    else:
        relay.value(1)  # OFF (Active Low)
        hardware_status["relay"] = False
# ================= LCD =================
def update_lcd():
    global hardware_status
    try:
        # Format baris 1: Temp & Status Pompa
        lcd.move_to(0, 0)
        status_str = "ON " if pump_status else "OFF"
        lcd.putstr(f"T:{temperature}C P:{status_str}")
        
        # Format baris 2: Kelembapan
        lcd.move_to(0, 1)
        lcd.putstr(f"Soil:{soil_moisture}%     ")
        
        hardware_status["lcd"] = True
    except Exception as e:
        hardware_status["lcd"] = False
        print("LCD Error:", e) 

# ================= SERVER (2-WAY COMMUNICATION - FULL REMOTE CONTROL) =================
def send_data(wlan):
    global pump_status, hardware_status, ADC_KERING, ADC_BASAH
    
    if not wlan or not wlan.isconnected():
        print("WiFi Lost. Skipping send.")
        return

    try:
        print("Sending data to server...")
        payload = {
            "device_id": DEVICE_ID,
            "temperature": temperature,
            "soil_moisture": soil_moisture,
            "raw_adc": raw_adc,
            "relay_status": 1 if pump_status else 0,
            "ip_address": wlan.ifconfig()[0],
            "hardware_status": hardware_status
        }
        
        r = urequests.post(SERVER_URL, json=payload, timeout=5)
        
        print(">> Data SENT, response:", r.status_code)
        
        # ===== TERIMA COMMAND & CONFIG DARI SERVER =====
        if r.status_code in (200, 201):
            try:
                data = r.json()
                print(">> Server response:", data)  # Debug: lihat response lengkap
                
                # 1. CEK KALIBRASI dari server (jika AUTO_CALIBRATION = True)
                if AUTO_CALIBRATION and "config" in data:
                    config = data["config"]
                    
                    # Update nilai kalibrasi dari server
                    if "adc_min" in config and "adc_max" in config:
                        new_kering = config["adc_min"]
                        new_basah = config["adc_max"]
                        
                        # Validasi: Pastikan nilai masuk akal
                        if new_kering != 4095 and new_basah != 1500 and new_kering != new_basah:
                            if ADC_KERING != new_kering or ADC_BASAH != new_basah:
                                ADC_KERING = new_kering
                                ADC_BASAH = new_basah
                                print(f"📊 CALIBRATION UPDATED from server: Kering={ADC_KERING}, Basah={ADC_BASAH}")
                    
                    # Info status kalibrasi
                    if config.get("calibration_status") == "collecting_samples":
                        print("⏳ Server collecting calibration samples...")
                    elif config.get("is_calibrated") == True:
                        print("✅ System calibrated and ready")
                
                # 2. CEK RELAY COMMAND dari website
                if "relay_command" in data and data["relay_command"] is not None:
                    # Update pump_status sesuai command dari server
                    pump_status = bool(data["relay_command"])
                    apply_relay_status()  # Apply ke hardware
                    print(f"🔌 RELAY COMMAND FROM SERVER: {'ON' if pump_status else 'OFF'}")
                else:
                    print(">> No relay_command in response")
                    
            except Exception as e:
                print(">> Error parsing response:", e)
        
        r.close()

    except Exception as e:
        print(">> Server error:", e)

# ================= MAIN =================
print("\nSMART GARDEN START")
print("=" * 50)
print(f"Device ID: {DEVICE_ID}")
print(f"Server: {SERVER_URL}")
print(f"Interval: {SERVER_INTERVAL}ms")
print("=" * 50)

# Inisialisasi Hardware Awal
lcd.clear()
lcd.putstr("System Starting")
time.sleep(2)

# ===== KALIBRASI STARTUP (OPSIONAL) =====
if STARTUP_CALIBRATION:
    try:
        print("\n🔧 Memulai kalibrasi startup...")
        calibration_success = calibrate_sensor()
        
        if not calibration_success:
            print("⚠️  Kalibrasi dibatalkan atau gagal.")
            print("⚠️  Menggunakan nilai default dari code.")
            lcd.clear()
            lcd.putstr("Using default")
            time.sleep(2)
    except KeyboardInterrupt:
        print("\n⚠️  Kalibrasi di-skip oleh user.")
        print("⚠️  Menggunakan nilai default dari code.")
        lcd.clear()
        lcd.putstr("Calibration skip")
        time.sleep(2)
else:
    print("\n📌 STARTUP_CALIBRATION = False")
    print("📌 Menggunakan nilai kalibrasi dari code.")

# Set relay status awal dan update hardware_status
apply_relay_status()
print(f"✅ Initial relay status: {'ON' if pump_status else 'OFF'}")

# ===== TAMPILKAN INFO KALIBRASI =====
print("\n📊 CALIBRATION INFO:")
print(f"   Mode: {'AUTO (from server)' if AUTO_CALIBRATION else 'MANUAL (local values)'}")
print(f"   Format: 12-bit ADC (0-4095)")
print(f"   ADC_KERING (Tanah kering): {ADC_KERING}")
print(f"   ADC_BASAH (Tanah basah): {ADC_BASAH}")
print("\n💡 CARA KALIBRASI MANUAL:")
print("   1. Lihat nilai RAW (12-bit) di Serial Monitor")
print("   2. Pastikan tanah KERING (jangan siram beberapa jam)")
print("   3. Catat nilai RAW → Update ADC_KERING")
print("   4. SIRAM tanah hingga basah → Catat nilai RAW")
print("   5. Catat nilai RAW → Update ADC_BASAH")
print("   6. Nilai ADC: 0-4095 (12-bit)")
print("=" * 50)

wlan = connect_wifi()

# Timer Variables
last_dht = 0
last_lcd = 0
last_server = 0

while True:
    now = time.ticks_ms()

    # 1. Baca Sensor (Soil & DHT)
    read_soil()

    # 2. Baca DHT (Tiap 2 detik)
    if time.ticks_diff(now, last_dht) > 2000:
        read_dht()
        last_dht = now

    # 3. Update LCD (Tiap 1 detik)
    if time.ticks_diff(now, last_lcd) > 1000:
        update_lcd()
        last_lcd = now

    # 4. Kirim ke Server & Terima Command (Tiap 15 detik)
    if time.ticks_diff(now, last_server) > SERVER_INTERVAL:
        if wlan and wlan.isconnected():
            send_data(wlan)  # Website yang kontrol relay via relay_command
        else:
            print("Wifi disconnected, trying to reconnect...")
            wlan = connect_wifi()
        last_server = now

    # Sleep kecil agar CPU tidak panas
    time.sleep(0.1)
