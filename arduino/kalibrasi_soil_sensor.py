"""
ALAT KALIBRASI SENSOR TANAH
================================
Tool ini untuk mengukur nilai ADC sensor tanah
dalam format 12-bit (0-4095)

CARA MENGGUNAKAN:
1. Upload file ini ke Pico W
2. Jalankan dan buka Serial Monitor
3. Pastikan tanah KERING → Catat nilai ADC (ini ADC_KERING)
4. SIRAM tanah hingga basah → Catat nilai ADC (ini ADC_BASAH)
5. Copy kedua nilai tersebut ke main.py atau website

JENIS SENSOR:
- CAPACITIVE: ADC Kering (3000-4000) > ADC Basah (500-1500)
  Contoh: Kering=3800, Basah=1500, Range=2300
  
- RESISTIVE: ADC Kering (0-100) < ADC Basah (1500-2000)
  Contoh: Kering=15, Basah=1805, Range=1790

CATATAN:
Sistem otomatis deteksi jenis sensor berdasarkan nilai ADC
"""

from machine import ADC, Pin
import time

# Inisialisasi Sensor di Pin 26 (GP26)
soil = ADC(Pin(26))

print("=" * 50)
print("=== ALAT KALIBRASI SENSOR TANAH ===")
print("=" * 50)
print("Format: 12-bit ADC (0-4095)")
print("Siapkan sensor Anda...")
print("=" * 50)
time.sleep(2)

# Buffer untuk rata-rata
readings = []
BUFFER_SIZE = 10

while True:
    # 1. Baca nilai Raw 16-bit (Standar MicroPython: 0-65535)
    raw_16bit = soil.read_u16()
     
    # 2. Konversi ke 12-bit (Shift 4 bit ke kanan: 0-65535 → 0-4095)
    raw_12bit = raw_16bit >> 4
    
    # 3. Simpan ke buffer untuk rata-rata
    readings.append(raw_12bit)
    if len(readings) > BUFFER_SIZE:
        readings.pop(0)
    
    # 4. Hitung rata-rata
    avg = sum(readings) // len(readings)
    
    # 5. Tentukan kondisi berdasarkan nilai (support kedua jenis sensor)
    # Deteksi auto: Jika ADC < 500 kemungkinan resistive, jika > 1500 kemungkinan capacitive
    if raw_12bit < 500:
        # RESISTIVE SENSOR (low ADC = dry)
        if raw_12bit < 50:
            condition = "🌵 SANGAT KERING (Resistive)"
        elif raw_12bit < 200:
            condition = "⚠️  KERING (Resistive)"
        elif raw_12bit < 800:
            condition = "💧 LEMBAB (Resistive)"
        else:
            condition = "💦 BASAH (Resistive)"
    else:
        # CAPACITIVE SENSOR (high ADC = dry)
        if raw_12bit > 3500:
            condition = "🌵 SANGAT KERING (Capacitive)"
        elif raw_12bit > 3000:
            condition = "⚠️  KERING (Capacitive)"
        elif raw_12bit > 2000:
            condition = "💧 LEMBAB (Capacitive)"
        elif raw_12bit > 1500:
            condition = "💦 BASAH (Capacitive)"
        else:
            condition = "🌊 SANGAT BASAH (Capacitive)"
    
    # 6. Tampilkan hasil
    print(f"ADC: {raw_12bit:4d} | Avg: {avg:4d} | {condition}")
    
    time.sleep(0.5)
