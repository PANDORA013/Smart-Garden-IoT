# 🎮 Smart Config: Wizard Pemandu Strategi Penyiraman

## 📋 Daftar Isi
1. [Pengenalan](#pengenalan)
2. [Konsep "Tidak Ribet" tapi "Fleksibel"](#konsep)
3. [4 Mode Pilihan](#4-mode-pilihan)
4. [Cara Penggunaan](#cara-penggunaan)
5. [Perbedaan Mode Rekomendasi vs Manual](#perbedaan)
6. [API Documentation](#api-documentation)
7. [User Flow](#user-flow)

---

## 🌟 Pengenalan

**Smart Config** adalah fitur Wizard (Pemandu) yang memudahkan user memilih strategi penyiraman tanpa perlu ribet mengatur parameter teknis.

### 🎯 Tujuan:
- **Pemula** tidak perlu tahu berapa persen kelembapan ideal (otomatis 40%-70%)
- **User Berpengalaman** tetap bisa kontrol penuh dengan Mode Manual
- **One-Click Setup** untuk 3 mode rekomendasi
- **Fleksibel** dengan 1 mode manual advanced

---

## 💡 Konsep "Tidak Ribet" tapi "Fleksibel"

### Filosofi Desain:

```
┌─────────────────────────────────────────┐
│  🎮 TOMBOL BESAR di Dashboard Utama     │
│     "Atur Strategi Penyiraman"          │
└─────────────────────────────────────────┘
              ↓
    ┌──────────────────────┐
    │  4 KARTU VISUAL      │
    │  (Easy to Understand)│
    └──────────────────────┘
              ↓
    ┌──────────────────────┐
    │  KLIK & SIMPAN       │
    │  (No Complex Setup)  │
    └──────────────────────┘
```

**User Journey:**
1. Klik tombol besar "🎮 Atur Strategi Penyiraman"
2. Lihat 4 kartu dengan emoji besar dan penjelasan singkat
3. Pilih satu kartu (highlight otomatis)
4. (Opsional) Isi detail jika Mode Jadwal/Manual
5. Klik "Simpan & Terapkan"
6. ✅ Selesai! Arduino auto-update dalam 1 menit

---

## 🎴 4 Mode Pilihan

### 1️⃣ Mode Pemula 🌱 (Rekomendasi)
**Tagline:** *"Paling mudah. Siram otomatis jika tanah kering (< 40%). Tanpa ribet."*

**Karakteristik:**
- ✅ **One-Click Setup** - Tidak perlu input apa-apa
- ✅ Otomatis set ke **40% ON / 70% OFF**
- ✅ Cocok untuk: **Pemula, Tanaman Umum**
- ✅ Badge: **"Rekomendasi Awal"** (Hijau)

**Backend Logic:**
```php
if ($request->mode == 1) {
    $updateData['batas_siram'] = 40; // Force standard
    $updateData['batas_stop'] = 70;
}
```

**Arduino Behavior:**
```cpp
if (soilMoisture < 40) {
    digitalWrite(RELAY_PIN, HIGH); // Pompa ON
}
if (soilMoisture >= 70) {
    digitalWrite(RELAY_PIN, LOW); // Pompa OFF
}
```

---

### 2️⃣ Mode AI (Fuzzy) 🤖 (Rekomendasi)
**Tagline:** *"Hemat air & presisi. Menyesuaikan siraman dengan suhu udara panas/dingin."*

**Karakteristik:**
- ✅ **Fully Automatic** - Zero configuration
- ✅ **Fuzzy Logic AI** - Durasi siram adaptif
- ✅ Cocok untuk: **Hemat Air, Efisiensi Maksimal**
- ✅ Badge: **"Paling Efisien"** (Biru)

**Fuzzy Rules:**
| Kelembapan | Suhu        | Durasi Siram |
|------------|-------------|--------------|
| Kering     | Panas (>30°C) | **8 detik**  |
| Kering     | Sedang (25-30°C) | **5 detik** |
| Kering     | Dingin (<25°C) | **3 detik** |

**Reasoning:** Cuaca panas = evaporasi cepat → butuh siram lebih lama

---

### 3️⃣ Mode Terjadwal 📅 (Rekomendasi)
**Tagline:** *"Siram rutin pagi & sore. Cocok untuk pembiasaan tanaman."*

**Karakteristik:**
- ⚙️ **Semi-Auto** - User cukup isi jam
- ⚙️ Default: **07:00 (Pagi) & 17:00 (Sore)**
- ✅ Cocok untuk: **Tanaman dengan Rutinitas Tetap**
- ✅ Badge: **"Teratur"** (Kuning)

**Input yang Diperlukan:**
- ⏰ Jam Pagi (default: 07:00)
- 🌅 Jam Sore (default: 17:00)
- ⏱️ Durasi Siram (default: 5 detik)

**Arduino Behavior:**
```cpp
void runModeSchedule() {
    timeClient.update();
    String currentTime = timeClient.getFormattedTime().substring(0, 5);
    
    if (currentTime == jamPagi || currentTime == jamSore) {
        digitalWrite(RELAY_PIN, HIGH);
        delay(durasiSiram * 1000);
        digitalWrite(RELAY_PIN, LOW);
    }
}
```

---

### 4️⃣ Mode Manual 🛠️ (Advanced)
**Tagline:** *"Kendali penuh. Anda tentukan sendiri kapan pompa menyala."*

**Karakteristik:**
- 🎛️ **Full Control** - User geser slider sesuka hati
- 🎛️ User menentukan **Batas ON & OFF** sendiri
- ✅ Cocok untuk: **User Berpengalaman, Riset**
- ✅ Badge: **"Advanced"** (Abu-abu)

**Input yang Diperlukan:**
- 📊 **Slider 1:** Batas Kering (Pompa ON) - Range: 0-100%
- 📊 **Slider 2:** Batas Basah (Pompa OFF) - Range: 0-100%
- ⚠️ **Validasi:** Batas OFF harus > Batas ON

**UI Component:**
```html
<!-- Slider Interactive -->
<input type="range" id="range-manual" min="0" max="100" value="40">
<span id="val-manual">40%</span>

<input type="range" id="range-manual-stop" min="0" max="100" value="70">
<span id="val-manual-stop">70%</span>
```

**Backend Validation:**
```php
if ($request->mode == 4) {
    if ($updateData['batas_stop'] <= $updateData['batas_siram']) {
        return response()->json([
            'success' => false,
            'message' => 'Batas Basah (OFF) harus lebih tinggi dari Batas Kering (ON)'
        ], 422);
    }
}
```

---

## 🚀 Cara Penggunaan

### Step 1: Akses Smart Config
```
Dashboard Utama → Klik tombol besar:
┌───────────────────────────────────────┐
│ 🎮 Atur Strategi Penyiraman           │
└───────────────────────────────────────┘
```

### Step 2: Pilih Perangkat
```
📱 Pilih Perangkat:
[Dropdown: Cabai Merah (ESP32_001) ▼]
```

### Step 3: Pilih Mode (Klik Kartu)
```
┌─────────────┬─────────────┐
│   🌱        │    🤖       │
│ Mode Pemula │ Mode AI     │
│ ✅ Rekom.   │ ⭐ Efisien  │
└─────────────┴─────────────┘
┌─────────────┬─────────────┐
│   📅        │    🛠️       │
│ Terjadwal   │ Manual      │
│ ⏰ Teratur  │ 🎛️ Advanced│
└─────────────┴─────────────┘
```

### Step 4: (Opsional) Isi Detail
**Jika Mode 1 atau 2:**
```
┌────────────────────────────────────────┐
│ ℹ️ Mode Otomatis Aktif                 │
│ Sistem akan mengatur semuanya secara   │
│ otomatis. Tidak perlu input apa-apa.   │
└────────────────────────────────────────┘
```

**Jika Mode 3 (Jadwal):**
```
⏰ Jam Pagi:   [07:00]
🌅 Jam Sore:   [17:00]
⏱️ Durasi:     [5] detik
```

**Jika Mode 4 (Manual):**
```
Batas Kering (ON):   [====●====] 40%
Batas Basah (OFF):   [========●] 70%
```

### Step 5: Simpan
```
[ Batal ]  [ ✅ Simpan & Terapkan ]
```

### Step 6: Konfirmasi
```
✅ Berhasil! 🌱 Mode Pemula telah diterapkan.

Arduino akan update konfigurasi dalam 1 menit.
```

---

## 📊 Perbedaan Mode Rekomendasi vs Manual

| Aspek               | Mode 1-3 (Rekomendasi) | Mode 4 (Manual)       |
|---------------------|------------------------|-----------------------|
| **Kompleksitas**    | ⭐ Sangat Mudah        | ⭐⭐⭐ Advanced        |
| **Input User**      | Minimal / Zero         | Slider Custom         |
| **Klik untuk Setup**| 2 klik (Pilih + Simpan)| 3-4 klik (Geser + Simpan)|
| **Target User**     | Pemula, Umum           | Berpengalaman, Riset  |
| **Fleksibilitas**   | Pre-defined            | Full Customizable     |
| **Risiko Salah**    | ❌ Rendah (Auto-safe)  | ⚠️ Tinggi (User Error)|

**Analogi:**
- **Mode 1-3:** Seperti **"Preset Camera"** di smartphone (Portrait, Landscape, Night)
- **Mode 4:** Seperti **"Pro Mode"** di kamera DSLR (Manual ISO, Shutter, Aperture)

---

## 📡 API Documentation

### Endpoint: Update Mode
```http
POST /api/devices/{id}/mode
Content-Type: application/json
```

### Request Examples:

#### Mode 1: Pemula
```json
{
  "mode": 1,
  "batas_siram": 40,
  "batas_stop": 70
}
```

#### Mode 2: Fuzzy AI
```json
{
  "mode": 2
}
```

#### Mode 3: Jadwal
```json
{
  "mode": 3,
  "jam_pagi": "07:00",
  "jam_sore": "17:00",
  "durasi_siram": 5
}
```

#### Mode 4: Manual
```json
{
  "mode": 4,
  "batas_siram": 35,
  "batas_stop": 80
}
```

### Response Success:
```json
{
  "success": true,
  "message": "Mode berhasil diubah ke Mode Pemula (Basic)",
  "data": {
    "id": 1,
    "device_id": "ESP32_001",
    "mode": 1,
    "batas_siram": 40,
    "batas_stop": 70
  }
}
```

### Response Error (Mode 4 Validation):
```json
{
  "success": false,
  "message": "Batas Basah (OFF) harus lebih tinggi dari Batas Kering (ON)"
}
```

---

## 🎬 User Flow Diagram

```
┌─────────────────────────────────────────┐
│   USER MEMBUKA DASHBOARD UTAMA          │
└─────────────────┬───────────────────────┘
                  ↓
┌─────────────────────────────────────────┐
│  MELIHAT TOMBOL BESAR:                  │
│  "🎮 Atur Strategi Penyiraman"          │
└─────────────────┬───────────────────────┘
                  ↓ [KLIK]
┌─────────────────────────────────────────┐
│   MODAL WIZARD MUNCUL                   │
│   ┌──────────────────────────────┐      │
│   │ Pilih Perangkat: [Dropdown]  │      │
│   └──────────────────────────────┘      │
│                                         │
│   ┌─────────┐  ┌─────────┐             │
│   │🌱 Pemula│  │🤖 AI    │             │
│   └─────────┘  └─────────┘             │
│   ┌─────────┐  ┌─────────┐             │
│   │📅 Jadwal│  │🛠️ Manual│             │
│   └─────────┘  └─────────┘             │
└─────────────────┬───────────────────────┘
                  ↓ [PILIH KARTU]
┌─────────────────────────────────────────┐
│   KARTU TERPILIH HIGHLIGHT (Border)     │
│   ┌──────────────────────────────┐      │
│   │ ⚙️ Konfigurasi Detail         │      │
│   │                              │      │
│   │ [INPUT CONDITIONAL]          │      │
│   │ - Mode 1/2: No input         │      │
│   │ - Mode 3: Jam pagi/sore      │      │
│   │ - Mode 4: Slider threshold   │      │
│   └──────────────────────────────┘      │
└─────────────────┬───────────────────────┘
                  ↓ [KLIK "SIMPAN"]
┌─────────────────────────────────────────┐
│   POST /api/devices/{id}/mode           │
│   ↓                                     │
│   Backend Validation & Update DB        │
│   ↓                                     │
│   Response Success                      │
└─────────────────┬───────────────────────┘
                  ↓
┌─────────────────────────────────────────┐
│   ALERT: ✅ Berhasil!                   │
│   "Mode Pemula telah diterapkan"        │
│   "Arduino update dalam 1 menit"        │
└─────────────────┬───────────────────────┘
                  ↓
┌─────────────────────────────────────────┐
│   MODAL CLOSE, DASHBOARD REFRESH        │
└─────────────────────────────────────────┘
```

---

## 🎨 UI Component Hierarchy

```
smartConfigModal (div)
├── modal-header (bg-gradient red)
│   ├── title: "🎮 Pilih Metode Perawatan Tanaman"
│   └── close button
├── modal-body (bg-slate-50)
│   ├── device-selection (dropdown)
│   ├── mode-cards (grid 2x2)
│   │   ├── card-mode-1 (green border on select)
│   │   ├── card-mode-2 (blue border on select)
│   │   ├── card-mode-3 (yellow border on select)
│   │   └── card-mode-4 (slate border on select)
│   └── detail-settings (hidden by default)
│       ├── msg-auto (for mode 1 & 2)
│       ├── input-jadwal (for mode 3)
│       └── input-manual (for mode 4)
└── modal-footer
    ├── button-cancel
    └── button-save (green gradient)
```

---

## 🧪 Testing Checklist

### Frontend Testing:
- [ ] Tombol besar muncul di dashboard utama
- [ ] Modal terbuka saat tombol diklik
- [ ] Dropdown device ter-populate dengan benar
- [ ] Klik kartu Mode 1: Border hijau, show "Mode Otomatis Aktif"
- [ ] Klik kartu Mode 2: Border biru, show "Mode Otomatis Aktif"
- [ ] Klik kartu Mode 3: Border kuning, show input jam & durasi
- [ ] Klik kartu Mode 4: Border abu, show 2 slider
- [ ] Slider Mode 4 update nilai realtime
- [ ] Validasi: Batas OFF < Batas ON → Error message
- [ ] Klik "Simpan" → POST API → Success alert
- [ ] Modal close setelah sukses

### Backend Testing:
```bash
# Test Mode 1
curl -X POST http://localhost:8000/api/devices/1/mode \
  -H "Content-Type: application/json" \
  -d '{"mode": 1, "batas_siram": 40, "batas_stop": 70}'

# Test Mode 2
curl -X POST http://localhost:8000/api/devices/1/mode \
  -H "Content-Type: application/json" \
  -d '{"mode": 2}'

# Test Mode 3
curl -X POST http://localhost:8000/api/devices/1/mode \
  -H "Content-Type: application/json" \
  -d '{"mode": 3, "jam_pagi": "07:00", "jam_sore": "17:00", "durasi_siram": 5}'

# Test Mode 4
curl -X POST http://localhost:8000/api/devices/1/mode \
  -H "Content-Type: application/json" \
  -d '{"mode": 4, "batas_siram": 35, "batas_stop": 80}'

# Test Validation Error (Mode 4)
curl -X POST http://localhost:8000/api/devices/1/mode \
  -H "Content-Type: application/json" \
  -d '{"mode": 4, "batas_siram": 70, "batas_stop": 40}'
```

---

## 🎯 Keunggulan Smart Config

### ✅ Untuk Pemula:
1. **Zero Learning Curve** - Tidak perlu tahu istilah "threshold" atau "kelembapan ideal"
2. **One-Click Setup** - Pilih kartu → Simpan → Selesai
3. **Safe Defaults** - Mode 1 otomatis 40%-70% (aman untuk kebanyakan tanaman)
4. **Visual Guidance** - Emoji besar + penjelasan singkat

### ✅ Untuk Advanced User:
1. **Mode Manual** - Full control dengan slider
2. **Custom Threshold** - Geser sesuka hati
3. **No Limitation** - Range 0-100% bebas (dengan validasi)

### ✅ Untuk Developer:
1. **Clean Code** - Conditional rendering based on mode
2. **Validation** - Server-side check untuk Mode 4
3. **Extensible** - Mudah tambah Mode 5, 6, dst
4. **RESTful API** - Standard JSON response

---

## 📝 Catatan Penting

### ⚠️ Perbedaan Mode 1 vs Mode 4
Meskipun sama-sama menggunakan threshold, ada perbedaan filosofi:

| Aspek            | Mode 1 (Pemula)       | Mode 4 (Manual)       |
|------------------|-----------------------|-----------------------|
| **Tujuan**       | Kemudahan             | Kontrol Penuh         |
| **Default Value**| Hard-coded (40-70%)   | User-defined          |
| **UI**           | No input (auto)       | Slider interactive    |
| **Target**       | Pengguna baru         | Eksperimen/Riset      |

### 🔐 Backend Security
Semua input di-validasi:
```php
// Validation rules
'mode' => 'required|integer|in:1,2,3,4',
'batas_siram' => 'nullable|integer|min:0|max:100',
'batas_stop' => 'nullable|integer|min:0|max:100',
```

### 🔄 Arduino Auto-Sync
Arduino melakukan check-in setiap 60 detik:
```cpp
void loop() {
    if (millis() - lastCheckIn > 60000) {
        syncConfiguration(); // GET /api/device/check-in
        lastCheckIn = millis();
    }
}
```

---

## 🚀 Kesimpulan

**Smart Config** adalah solusi **"Best of Both Worlds"**:
- **Tidak ribet** untuk pemula dengan 3 mode rekomendasi one-click
- **Tetap fleksibel** untuk advanced user dengan Mode Manual

**Formula Sukses:**
```
Easy to Start + Powerful when Needed = Happy Users
```

---

**Created by:** Your Team  
**Last Updated:** January 2, 2026  
**Version:** 1.0
