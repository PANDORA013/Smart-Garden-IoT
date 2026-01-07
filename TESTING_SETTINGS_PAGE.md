# 🧪 Testing Settings Page - Step by Step Guide

## 📋 Prerequisites
- ✅ Laravel server running (`php artisan serve`)
- ✅ Browser terbuka di `http://127.0.0.1:8000/`
- ✅ Database SQLite/MySQL sudah ter-migrate

---

## 🎯 Test Scenario 1: Load Settings Page

### Steps:
1. Buka dashboard: `http://127.0.0.1:8000/`
2. Klik menu **"Pengaturan"** di sidebar kiri
3. Tunggu halaman loading

### Expected Result:
- ✅ Card "Konfigurasi Perangkat" muncul dengan header "Aktif" (hijau)
- ✅ Input "Nama Perangkat" terisi (atau kosong jika belum ada data)
- ✅ 4 tombol mode muncul (Basic, Fuzzy AI, Jadwal, Manual)
- ✅ Mode Basic ter-select secara default (background hijau)
- ✅ Dynamic settings area menampilkan 2 input: Batas Kering & Batas Basah
- ✅ Card "Status Mode Aktif" menampilkan informasi mode
- ✅ Card "Quick Actions" dengan 2 tombol: Test Pompa & Refresh Config

---

## 🎯 Test Scenario 2: Switch Mode (Basic → Fuzzy AI)

### Steps:
1. Klik tombol **"Fuzzy AI"** (robot emoji 🤖)

### Expected Result:
- ✅ Tombol Fuzzy AI berubah warna jadi **biru** (border-blue-500, bg-blue-50)
- ✅ Tombol Basic kembali ke warna abu-abu
- ✅ Dynamic settings area berubah menampilkan:
  - Icon robot besar 🤖
  - Judul "Mode Fuzzy Logic AI"
  - Deskripsi sistem AI
  - Info box dengan 3 aturan fuzzy:
    - 🔥 Panas (>30°C) + Kering = Siram 8 detik
    - ☀️ Sedang (25-30°C) + Kering = Siram 5 detik
    - ❄️ Dingin (<25°C) + Kering = Siram 3 detik
- ✅ Card "Status Mode Aktif" update jadi "🤖 Fuzzy Logic AI"

---

## 🎯 Test Scenario 3: Switch Mode (Fuzzy AI → Jadwal)

### Steps:
1. Klik tombol **"Jadwal"** (calendar emoji 📅)

### Expected Result:
- ✅ Tombol Jadwal berubah warna jadi **kuning** (border-yellow-500, bg-yellow-50)
- ✅ Dynamic settings area menampilkan:
  - 2 input time: "⏰ Jadwal Pagi" dan "🌅 Jadwal Sore"
  - Default value: 07:00 dan 17:00
  - Slider "⏱️ Durasi Siram (detik)" dengan range 1-60
  - Display value slider di sebelah kanan (hijau)
  - Info box kuning dengan icon clock
- ✅ Card "Status Mode Aktif" update jadi "📅 Schedule Timer"

---

## 🎯 Test Scenario 4: Switch Mode (Jadwal → Manual)

### Steps:
1. Klik tombol **"Manual"** (tool emoji 🛠️)

### Expected Result:
- ✅ Tombol Manual berubah warna jadi **abu-abu gelap** (border-slate-500, bg-slate-50)
- ✅ Dynamic settings area kembali menampilkan input threshold seperti mode Basic:
  - Batas Kering (ON)
  - Batas Basah (OFF)
- ✅ Card "Status Mode Aktif" update jadi "🛠️ Manual Control"

---

## 🎯 Test Scenario 5: Edit & Save Settings (Mode Basic)

### Steps:
1. Switch ke mode **"Basic"**
2. Isi form:
   - Nama Perangkat: `"Smart Garden Test"`
   - Batas Kering: `35` (ubah dari 40)
   - Batas Basah: `75` (ubah dari 70)
3. Klik tombol **"Simpan Perubahan"** (biru)

### Expected Result:
- ✅ Tombol berubah jadi "Menyimpan..." (disabled)
- ✅ Request POST ke `/api/devices/1/mode` dengan payload:
  ```json
  {
    "mode": 1,
    "batas_siram": 35,
    "batas_stop": 75
  }
  ```
- ✅ Request PUT ke `/api/devices/1` dengan payload:
  ```json
  {
    "device_name": "Smart Garden Test"
  }
  ```
- ✅ Notifikasi hijau muncul: **"✅ Berhasil disimpan!"**
- ✅ Notifikasi hilang otomatis setelah 3 detik
- ✅ Tombol kembali jadi "Simpan Perubahan"

---

## 🎯 Test Scenario 6: Edit & Save Settings (Mode Jadwal)

### Steps:
1. Switch ke mode **"Jadwal"**
2. Isi form:
   - Nama Perangkat: `"Smart Garden Test"`
   - Jam Pagi: `06:30`
   - Jam Sore: `18:00`
   - Durasi: Geser slider ke `10` detik
3. Klik **"Simpan Perubahan"**

### Expected Result:
- ✅ Request POST ke `/api/devices/1/mode` dengan payload:
  ```json
  {
    "mode": 3,
    "jam_pagi": "06:30",
    "jam_sore": "18:00",
    "durasi_siram": 10
  }
  ```
- ✅ Notifikasi hijau muncul
- ✅ Slider value update ke "10 detik"

---

## 🎯 Test Scenario 7: Quick Actions - Refresh Config

### Steps:
1. Klik tombol **"Refresh Config"** di card Quick Actions

### Expected Result:
- ✅ Function `loadMinimalSettings()` dipanggil
- ✅ Request GET ke `/api/devices/1`
- ✅ Form ter-update dengan data dari server
- ✅ Alert muncul: **"✅ Pengaturan berhasil dimuat ulang!"**

---

## 🎯 Test Scenario 8: Quick Actions - Test Pompa

### Steps:
1. Klik tombol **"Test Pompa"** di card Quick Actions
2. Klik **"OK"** di confirmation dialog

### Expected Result:
- ✅ Confirmation dialog muncul: "Tes pompa akan menyalakan pompa selama 5 detik. Lanjutkan?"
- ✅ Request POST ke `/api/monitoring/relay/toggle` dengan payload:
  ```json
  {
    "status": true,
    "test_mode": true,
    "duration": 5
  }
  ```
- ✅ Alert muncul: **"✅ Pompa berhasil dinyalakan! Akan mati otomatis setelah 5 detik."**
- ✅ (Jika error) Alert merah: **"❌ Gagal menyalakan pompa."**

---

## 🎯 Test Scenario 9: Error Handling - Network Error

### Steps:
1. Matikan Laravel server (`Ctrl+C` di terminal)
2. Di browser, ubah setting apapun
3. Klik **"Simpan Perubahan"**

### Expected Result:
- ✅ Notifikasi merah muncul: **"❌ Gagal menyimpan."**
- ✅ Tombol kembali enable
- ✅ Console browser menampilkan error log

---

## 🎯 Test Scenario 10: Responsive Design (Mobile View)

### Steps:
1. Buka Developer Tools (`F12`)
2. Toggle Device Toolbar (`Ctrl+Shift+M`)
3. Pilih device: iPhone 12 Pro atau Samsung Galaxy S20
4. Buka halaman Settings

### Expected Result:
- ✅ Card full-width di mobile
- ✅ Mode buttons tetap 2x2 grid
- ✅ Input fields full-width
- ✅ Tombol simpan full-width
- ✅ Info cards stack vertikal (1 kolom)
- ✅ Semua text terbaca jelas
- ✅ Tidak ada horizontal scroll

---

## 🧪 Manual Testing Checklist

### Visual Design
- [ ] Header "⚙️ Pengaturan Sistem" dengan subtitle
- [ ] Card putih dengan shadow dan border slate-100
- [ ] Green dot "Aktif" animasi pulse di header card
- [ ] Mode buttons dengan emoji dan deskripsi kecil
- [ ] Warna mode buttons konsisten (hijau, biru, kuning, abu)
- [ ] Dynamic area background slate-50
- [ ] Blue button "Simpan Perubahan" dengan shadow
- [ ] Info cards grid 2 kolom di desktop

### Functionality
- [ ] Load settings dari server saat page switch
- [ ] Mode switching instant tanpa lag
- [ ] Dynamic area update sesuai mode
- [ ] Save settings berhasil (cek database)
- [ ] Notification muncul dan hilang otomatis
- [ ] Quick actions berfungsi
- [ ] Refresh settings update form
- [ ] Test pompa kirim request

### Integration
- [ ] API `/api/devices/1` return correct data
- [ ] API `/api/devices/1/mode` accept POST
- [ ] API `/api/devices/1` accept PUT
- [ ] API `/api/monitoring/relay/toggle` exist

---

## 🐛 Known Issues / Expected Errors

### If API endpoint doesn't exist:
```
❌ Error: Tidak dapat menghubungi server.
```
**Solution:** Pastikan route API sudah terdaftar di `routes/api.php`

### If device_id=1 tidak ada di database:
```
❌ Gagal menyimpan.
```
**Solution:** Insert dummy device via Tinker:
```php
php artisan tinker
>>> $device = new App\Models\DeviceSetting();
>>> $device->device_name = 'Smart Garden #1';
>>> $device->mode = 1;
>>> $device->batas_siram = 40;
>>> $device->batas_stop = 70;
>>> $device->save();
```

---

## ✅ Test Result Summary

| Test Case | Status | Notes |
|-----------|--------|-------|
| Load Settings Page | ⚪ Not Tested | |
| Switch Mode (Basic → Fuzzy AI) | ⚪ Not Tested | |
| Switch Mode (Fuzzy AI → Jadwal) | ⚪ Not Tested | |
| Switch Mode (Jadwal → Manual) | ⚪ Not Tested | |
| Edit & Save (Mode Basic) | ⚪ Not Tested | |
| Edit & Save (Mode Jadwal) | ⚪ Not Tested | |
| Quick Action - Refresh Config | ⚪ Not Tested | |
| Quick Action - Test Pompa | ⚪ Not Tested | |
| Error Handling | ⚪ Not Tested | |
| Responsive Design | ⚪ Not Tested | |

**Legend:**
- ✅ Pass
- ❌ Fail
- ⚪ Not Tested
- ⚠️ Partial Pass

---

## 📸 Screenshots (Optional)

Tambahkan screenshot untuk setiap test scenario:
- [ ] Settings page loaded
- [ ] Mode Basic selected
- [ ] Mode Fuzzy AI selected
- [ ] Mode Jadwal selected
- [ ] Mode Manual selected
- [ ] Save success notification
- [ ] Error notification
- [ ] Mobile view

---

## 🚀 Next Steps After Testing

1. ✅ Pastikan semua test scenario PASS
2. ✅ Fix issues yang ditemukan
3. ✅ Update dokumentasi jika ada perubahan
4. ✅ Commit changes: `git commit -m "test: Verify settings page functionality"`
5. ✅ Deploy to production

---

**Testing Date:** ___________
**Tested By:** ___________
**Browser:** ___________
**Result:** ⚪ Pass / ⚪ Fail
