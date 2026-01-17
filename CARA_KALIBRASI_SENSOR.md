# 📊 Cara Kalibrasi Sensor Soil Moisture - Smart Garden IoT

## 🎯 Kenapa Perlu Kalibrasi?

Setiap sensor soil moisture memiliki **nilai ADC yang berbeda-beda** tergantung:
- Jenis sensor (Capacitive vs Resistive)
- Kondisi sensor (baru/lama)
- Kualitas koneksi kabel
- Voltase power supply

**Tanpa kalibrasi**, sistem tidak bisa tahu:
- Berapa nilai ADC saat tanah **kering** (Pompa harus ON)
- Berapa nilai ADC saat tanah **basah** (Pompa harus OFF)

---

## 📋 2 Metode Kalibrasi

### **Metode 1: MANUAL (Recommended - Paling Akurat)**
Set nilai ADC langsung di `main.py` berdasarkan test sensor.

### **Metode 2: AUTO (Experimental)**
Sistem belajar dari 20 sample data pertama (butuh waktu 5 menit).

---

## 🛠️ METODE 1: Kalibrasi Manual (RECOMMENDED)

### Langkah-Langkah:

#### **Step 1: Upload Code Awal**
1. Buka file `main.py`
2. Pastikan `AUTO_CALIBRATION = False`
3. Upload ke Pico W via Thonny

#### **Step 2: Buka Serial Monitor**
- **Thonny**: View → Shell
- **VS Code**: Terminal dengan serial connection

#### **Step 3: Test Sensor di Udara (Kering)**
```python
# 1. CABUT sensor dari tanah (biarkan di udara)
# 2. Tunggu 10 detik
# 3. Lihat Serial Monitor, cari baris seperti:
#    RAW: 42356 | SOIL: 85% | Status: OK
#    ^^^^^ INI NILAI ADC_KERING
```

**Catat nilai RAW** → Ini adalah **ADC_KERING**

Contoh hasil:
```
RAW: 42356 | SOIL: 85% | Cal: 2000/35000 | Status: OK
     ^^^^^ → ADC_KERING = 42356
```

#### **Step 4: Test Sensor di Air (Basah)**
```python
# 1. CELUPKAN sensor ke dalam air/tanah sangat basah
# 2. Tunggu 10 detik
# 3. Lihat Serial Monitor:
#    RAW: 15234 | SOIL: 15% | Status: OK
#    ^^^^^ INI NILAI ADC_BASAH
```

**Catat nilai RAW** → Ini adalah **ADC_BASAH**

Contoh hasil:
```
RAW: 15234 | SOIL: 15% | Cal: 2000/35000 | Status: OK
     ^^^^^ → ADC_BASAH = 15234
```

#### **Step 5: Update Code**
Buka `main.py`, cari bagian kalibrasi, update nilai:

```python
# ===== UPDATE NILAI DI SINI =====
ADC_KERING = 42356   # Ganti dengan nilai RAW saat sensor di udara
ADC_BASAH  = 15234   # Ganti dengan nilai RAW saat sensor di air
```

#### **Step 6: Upload Ulang**
1. Save file `main.py`
2. Upload ke Pico W
3. Reset/Restart Pico W

#### **Step 7: Verifikasi**
Setelah restart, lihat Serial Monitor:
```
📊 CALIBRATION INFO:
   Mode: MANUAL (local values)
   ADC_KERING (Dry): 42356  ✅ Sudah update!
   ADC_BASAH (Wet): 15234   ✅ Sudah update!
```

Sekarang test sensor:
- **Di udara** → Harus tampil **SOIL: 100%** (kering)
- **Di air** → Harus tampil **SOIL: 0%** (basah)
- **Di tanah lembab** → Harus tampil **SOIL: 30-70%**

---

## 🤖 METODE 2: Kalibrasi Otomatis (Experimental)

### Setup:
1. Buka `main.py`
2. Set `AUTO_CALIBRATION = True`
3. Upload ke Pico W

### Cara Kerja:
- Sistem akan mengumpulkan **20 sample data pertama** (± 5 menit)
- Menghitung nilai **MIN (basah)** dan **MAX (kering)** dari sample
- Auto-update ke database
- Kirim nilai kalibrasi ke Pico W via response server

### Proses:
```
1. Upload code → Pico W mulai kirim data
2. Serial Monitor:
   ⏳ Server collecting calibration samples... (1/20)
   ⏳ Server collecting calibration samples... (2/20)
   ...
   ⏳ Server collecting calibration samples... (20/20)
   
3. Setelah 20 sample:
   🎯 AUTO CALIBRATION SUCCESS
   📊 CALIBRATION UPDATED from server: Kering=42000, Basah=15000
   ✅ System calibrated and ready
```

### ⚠️ Persyaratan:
- Sensor harus **dalam kondisi bervariasi** (basah dan kering)
- Jika sensor selalu di tanah lembab, range akan terlalu kecil
- Minimal range: **5000** (Max - Min)

---

## 📊 Cara Membaca Nilai Serial Monitor

### Format Output:
```
RAW: 25000 | SOIL: 45% | Cal: 42000/15000 | Status: OK
     ^^^^^    ^^^^^       ^^^^^  ^^^^^       ^^^^^
     |        |           |      |           └─ Status sensor OK/FAIL
     |        |           |      └─ ADC_BASAH (Wet)
     |        |           └─ ADC_KERING (Dry)
     |        └─ Kelembaban hasil konversi (0-100%)
     └─ Nilai ADC raw dari sensor (0-65535)
```

### Interpretasi:
| RAW ADC | Kondisi | SOIL % | Aksi Pompa |
|---------|---------|--------|------------|
| > 40000 | Sangat Kering | 100% | ON |
| 30000-40000 | Kering | 60-100% | ON |
| 20000-30000 | Lembab | 30-60% | Tergantung mode |
| 15000-20000 | Basah | 10-30% | OFF |
| < 15000 | Sangat Basah | 0-10% | OFF |

---

## 🔧 Troubleshooting

### ❌ Problem: Nilai RAW selalu 0 atau 65535
**Penyebab**: Sensor disconnect atau koneksi jelek

**Solusi**:
1. Cek kabel sensor (VCC, GND, Signal)
2. Pastikan sensor terhubung ke **Pin GP26 (ADC0)**
3. Test dengan multimeter: Cek voltage di pin Signal (harus 0-3.3V)

---

### ❌ Problem: Nilai RAW fluktuasi ekstrem
**Contoh**: `RAW: 5000 → 50000 → 3000` dalam 3 detik

**Penyebab**: Sensor tidak stabil atau floating pin

**Solusi**:
1. Tambahkan capacitor 100nF antara Signal dan GND
2. Pastikan kabel tidak terlalu panjang (max 30cm)
3. Jauhkan dari sumber noise (relay, motor)

---

### ❌ Problem: SOIL% tidak berubah meskipun sensor basah/kering
**Penyebab**: Nilai ADC_KERING dan ADC_BASAH salah atau terbalik

**Solusi**:
Pastikan:
- **ADC_KERING** > **ADC_BASAH** (harus lebih besar!)
- Range minimal 5000 (contoh: 40000 vs 15000 = OK ✅)

---

### ❌ Problem: Soil% selalu 0% atau 100%
**Penyebab**: Sensor stuck di satu nilai

**Solusi**:
1. Ganti sensor (mungkin rusak)
2. Cek voltase power: Harus 3.3V atau 5V stabil
3. Test sensor dengan Arduino/ESP32 untuk validasi

---

## 📱 Cara Update Kalibrasi Via Dashboard (Future Feature)

*Coming soon - Update kalibrasi langsung dari web dashboard tanpa upload code*

Rencana:
1. Dashboard → Pengaturan → Kalibrasi Sensor
2. Klik "Mode Kalibrasi"
3. Ikuti wizard: Test Kering → Test Basah → Save
4. Nilai otomatis terkirim ke Pico W

---

## 💡 Tips & Best Practices

### ✅ Kalibrasi yang Baik:
- Lakukan di **kondisi ekstrem** (sangat kering & sangat basah)
- Tunggu sensor stabil (10-15 detik) sebelum catat nilai
- Lakukan kalibrasi ulang setiap **3 bulan** (sensor aging)

### ✅ Maintenance:
- Bersihkan sensor dari kotoran/karat
- Jangan biarkan sensor terendam air 24/7
- Gunakan sensor capacitive (lebih tahan lama)

### ✅ Validasi:
Setelah kalibrasi, test dengan:
- **Tanah pot kering** → Harus 80-100%
- **Tanah pot setelah siram** → Harus 20-40%
- **Sensor di air** → Harus 0-10%

---

## 📞 Support

Jika masih ada masalah:
1. Screenshot Serial Monitor
2. Screenshot nilai ADC (kering & basah)
3. Foto koneksi hardware

**Status Kalibrasi**: ✅ READY TO USE!
