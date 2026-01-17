"""
ALAT KALIBRASI SENSOR TANAH
================================
Tool ini untuk mengukur nilai ADC sensor tanah
dalam format 12-bit (0-4095)

CARA MENGGUNAKAN:
1. Upload file ini ke Pico W
2. Jalankan dan buka Serial Monitor
3. Cabut sensor dari tanah → Catat nilai ADC (ini ADC_KERING)
4. Celupkan sensor ke air → Catat nilai ADC (ini ADC_BASAH)
5. Copy kedua nilai tersebut ke main.py

CATATAN:
- Capacitive Sensor: ADC Kering > ADC Basah (contoh: 3800 vs 1500)
- Resistive Sensor: ADC Kering < ADC Basah (contoh: 500 vs 3500)
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
    
    # 5. Tentukan kondisi berdasarkan nilai
    if raw_12bit > 3500:
        condition = "🌵 SANGAT KERING (Sensor di udara)"
    elif raw_12bit > 3000:
        condition = "⚠️  KERING"
    elif raw_12bit > 2000:
        condition = "💧 LEMBAB"
    elif raw_12bit > 1500:
        condition = "💦 BASAH"
    else:
        condition = "🌊 SANGAT BASAH (Sensor di air)"
    
    # 6. Tampilkan hasil
    print(f"ADC: {raw_12bit:4d} | Avg: {avg:4d} | {condition}")
    
    time.sleep(0.5)
