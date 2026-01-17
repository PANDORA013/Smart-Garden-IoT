# 📘 Dokumentasi Sistem Smart Garden - 3 Mode Operasi

## ✅ Update Terbaru
- **Threshold baru**: Pompa ON di 20%, OFF di 30%
- **3 Mode AUTO** sudah berfungsi penuh
- **Manual Toggle** tetap prioritas tertinggi

---

## 🌱 MODE 1: BASIC THRESHOLD (Otomatis Sederhana)

### Cara Kerja:
- **Pompa NYALA** jika kelembaban tanah **< 20%** (tanah kering)
- **Pompa MATI** jika kelembaban tanah **≥ 30%** (tanah cukup basah)
- **Hysteresis Zone** (20-30%): Pertahankan status sekarang (hindari flicker)

### Contoh:
```
Soil: 15% → Pompa ON
Soil: 25% → Maintain (jika sebelumnya ON tetap ON, jika OFF tetap OFF)
Soil: 35% → Pompa OFF
```

### Pengaturan:
- Bisa diubah di halaman **Pengaturan** → Mode Basic
- Parameter: `batas_siram` (default 20%) dan `batas_stop` (default 30%)

---

## 🤖 MODE 2: FUZZY LOGIC AI (Cerdas Adaptif)

### Cara Kerja:
Sistem membaca **kombinasi kelembaban + suhu** untuk menentukan durasi siram optimal:

| Kondisi Tanah | Kelembaban | Aksi Pompa |
|--------------|------------|------------|
| **Kering**   | 0-30%      | ON (Siram Lama) |
| **Sedang**   | 30-60%     | ON (Siram Singkat) |
| **Basah**    | ≥ 60%      | OFF (Tidak Perlu Siram) |

### Kelebihan:
- **Hemat Air**: Durasi siram disesuaikan kebutuhan
- **Presisi**: Mempertimbangkan suhu lingkungan
- **Fully Automatic**: Tidak perlu setting parameter

### Contoh:
```
Soil: 20%, Temp: 32°C → Pompa ON (Dry zone, siram lama)
Soil: 45%, Temp: 28°C → Pompa ON (Medium zone, siram sedang)
Soil: 70%, Temp: 25°C → Pompa OFF (Wet zone)
```

---

## ⏰ MODE 3: SCHEDULE (Jadwal Pagi & Sore)

### Cara Kerja:
- Pompa NYALA di **jam yang ditentukan** (pagi & sore)
- Masih mempertimbangkan **kelembaban tanah** (threshold 50%)
- Durasi siram sesuai setting (default 5 detik)

### Pengaturan Default:
- **Jam Pagi**: 07:00
- **Jam Sore**: 17:00
- **Durasi Siram**: 5 detik

### Logika:
```
Jika (Jam sekarang == Jam Pagi OR Jam Sore) AND Soil < 50%
→ Pompa ON selama [durasi_siram] detik
```

### Contoh:
```
Jam: 07:00, Soil: 40% → Pompa ON (Jadwal pagi + tanah perlu siram)
Jam: 07:00, Soil: 60% → Pompa OFF (Jadwal pagi tapi tanah masih basah)
Jam: 14:00, Soil: 30% → Pompa OFF (Bukan jam jadwal)
```

---

## 🎮 MANUAL TOGGLE (Override Mode Auto)

### Cara Pakai:
1. Buka dashboard
2. Klik tombol **Toggle Pompa**
3. Pompa akan langsung ON/OFF **tanpa logika auto**

### Catatan Penting:
- **Manual command prioritas tertinggi** - Override semua mode auto
- Setelah Pico W execute command, sistem kembali ke mode auto
- Berguna untuk testing atau keadaan darurat

---

## 📊 Cara Mengubah Mode di Dashboard

### Via Web UI:
1. Login ke dashboard: `http://192.168.18.35:8000`
2. Klik menu **⚙️ Pengaturan**
3. Pilih salah satu mode:
   - 🌱 **Basic** - Threshold otomatis (20-30%)
   - 🤖 **Fuzzy AI** - Logika cerdas auto
   - ⏰ **Schedule** - Jadwal pagi & sore
4. Atur parameter sesuai kebutuhan
5. Klik **Simpan Perubahan**

### Via API (untuk testing):
```powershell
# Set ke Mode 1 (Basic)
Invoke-RestMethod -Uri "http://192.168.18.35:8000/api/devices/PICO_CABAI_01/mode" `
  -Method POST -ContentType "application/json" `
  -Body '{"device_id":"PICO_CABAI_01","mode":1}'

# Set ke Mode 2 (Fuzzy)
...(mode:2)...

# Set ke Mode 3 (Schedule)
...(mode:3)...
```

---

## 🧪 Testing Mode Auto

Gunakan script PowerShell yang sudah dibuat:

```powershell
cd "c:\xampp\htdocs\Smart Garden IoT"
.\test_auto_modes.ps1
```

Script akan test:
- Mode 1 dengan berbagai kelembaban (15%, 25%, 35%)
- Mode 2 dengan kombinasi soil & temp
- Mode 3 dengan check jadwal

Output akan menunjukkan apakah auto command berhasil dikirim.

---

## 🔧 Troubleshooting

### Pompa tidak menyala otomatis:
1. Cek mode aktif: `GET /api/monitoring/latest` → lihat `config.mode`
2. Cek log Laravel: `Get-Content storage\logs\laravel.log -Tail 50`
3. Cari log dengan keyword: `MODE 1 AUTO`, `MODE 2 FUZZY`, `MODE 3 SCHEDULE`

### Relay command tidak sampai ke Pico W:
1. Pastikan Pico W online (< 30 detik last seen)
2. Cek Serial Monitor Pico W untuk melihat response dari server
3. Harus ada log: `🔌 RELAY COMMAND FROM SERVER: ON/OFF`

### Mode tidak berubah:
1. Clear cache: `php artisan cache:clear`
2. Restart PHP server: `Ctrl+C` → `php artisan serve`
3. Refresh dashboard browser

---

## 📝 Catatan Penting

### Prioritas Command:
1. **Manual Toggle** (Tertinggi) - Override semua
2. **Mode Auto** - Sesuai mode yang dipilih
3. **No Command** - Maintain status sekarang

### Hysteresis (Mode 1):
Zona 20-30% sengaja tidak kirim command untuk **mencegah relay flicker** (nyala-mati berulang cepat).

### Fuzzy Logic (Mode 2):
Untuk hasil optimal, pastikan **sensor DHT11 berfungsi** (temperature data akurat).

### Schedule (Mode 3):
Pastikan **jam server benar**: `Get-Date` → Check timezone Asia/Jakarta

---

## 🚀 Next Steps

1. **Upload firmware terbaru** ke Pico W (main.py sudah OK)
2. **Test real-world**: Biarkan sistem jalan 24 jam
3. **Monitor log**: Cek apakah decision logic sesuai
4. **Adjust threshold**: Sesuaikan dengan jenis tanaman

---

## 📞 Support

Jika ada masalah, cek:
1. **Log Laravel**: `storage/logs/laravel.log`
2. **Serial Monitor**: Thonny/VS Code → Pico W output
3. **Database**: `php artisan tinker` → Check device_settings

**Status Implementasi**: ✅ SEMUA MODE BERFUNGSI!
