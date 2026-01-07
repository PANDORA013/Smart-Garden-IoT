# 🚀 Quick Start Testing Guide

## 1. Insert Dummy Data

```bash
php insert_dummy_data.php
```

**Output yang diharapkan:**
```
✅ Device created: Smart Garden Test #1 (Device ID: PICO_TEST_01)
✅ Inserted 10 monitoring records
📈 Latest data: Temp 29.3°C, Soil 39%, Relay ON 🟢
```

---

## 2. Start Laravel Server

```bash
php artisan serve
```

Server akan jalan di: `http://127.0.0.1:8000`

---

## 3. Buka Browser & Test

### Test Scenario Quick Run:

1. **Buka Dashboard**: `http://127.0.0.1:8000/`
   - ✅ Cek Dashboard menampilkan data sensor
   - ✅ Cek grafik terisi

2. **Klik Menu "Pengaturan"** di sidebar
   - ✅ Card "Konfigurasi Perangkat" muncul
   - ✅ Form terisi dengan data: "Smart Garden Test #1"
   - ✅ Mode Basic ter-select (hijau)

3. **Test Switch Mode:**
   - Klik **Fuzzy AI** → Background jadi biru, tampil info AI
   - Klik **Jadwal** → Background jadi kuning, tampil time picker
   - Klik **Manual** → Background jadi abu, tampil threshold

4. **Test Save (Mode Basic):**
   - Switch ke **Basic**
   - Ubah: Nama jadi "Test Garden", Batas Kering jadi `35`, Batas Basah jadi `75`
   - Klik **"Simpan Perubahan"**
   - ✅ Notifikasi hijau muncul: "✅ Berhasil disimpan!"
   - ✅ Refresh page → Data tetap tersimpan

5. **Test Quick Actions:**
   - Klik **"Refresh Config"** → Alert "✅ Pengaturan berhasil dimuat ulang!"
   - Klik **"Test Pompa"** → Confirmation dialog muncul → Klik OK
     - (Akan error jika endpoint `/api/monitoring/relay/toggle` belum ada)

6. **Test Responsive:**
   - Press `F12` → Toggle Device Toolbar (`Ctrl+Shift+M`)
   - Pilih iPhone 12 Pro
   - ✅ Card full-width, buttons tetap 2x2, tidak ada horizontal scroll

---

## 4. Verify Database

```bash
php artisan tinker
```

```php
// Cek device setting
>>> App\Models\DeviceSetting::first()

// Cek monitoring data
>>> App\Models\Monitoring::latest()->take(5)->get()

// Cek apakah data tersimpan setelah save
>>> $device = App\Models\DeviceSetting::where('device_id', 'PICO_TEST_01')->first()
>>> $device->device_name  // Harus "Test Garden" jika sudah di-save
>>> $device->batas_siram  // Harus 35 jika sudah di-save
```

---

## 5. Check Network Requests (Browser DevTools)

1. Buka DevTools → **Network** tab
2. Klik menu "Pengaturan"
3. **Expected requests:**
   ```
   GET /api/devices           → Status 200
   GET /api/devices/PICO_TEST_01  → Status 200
   ```

4. Ubah setting → Klik **"Simpan Perubahan"**
5. **Expected requests:**
   ```
   POST /api/devices/PICO_TEST_01/mode  → Status 200
   PUT  /api/devices/PICO_TEST_01       → Status 200
   ```

---

## 6. Common Issues & Solutions

### Issue: "❌ Gagal menyimpan"
**Cause:** API endpoint tidak ditemukan
**Solution:** 
```bash
php artisan route:list | grep devices
```
Pastikan route ada:
- `GET /api/devices/{id}`
- `POST /api/devices/{id}/mode`
- `PUT /api/devices/{id}`

### Issue: Form tidak terisi data
**Cause:** Device tidak ditemukan di database
**Solution:**
```bash
php insert_dummy_data.php  # Jalankan ulang
```

### Issue: Mode buttons tidak berubah warna
**Cause:** JavaScript error di console
**Solution:** 
- Buka Console (F12)
- Cek error message
- Reload page dengan hard refresh: `Ctrl + Shift + R`

---

## 7. Test Result Checklist

- [ ] Dummy data berhasil di-insert
- [ ] Dashboard menampilkan data sensor
- [ ] Settings page terbuka tanpa error
- [ ] Form terisi dengan data device
- [ ] Mode switching berfungsi (4 mode)
- [ ] Dynamic area berubah sesuai mode
- [ ] Save settings berhasil (cek database)
- [ ] Notifikasi muncul dan hilang otomatis
- [ ] Quick Actions: Refresh Config berfungsi
- [ ] Responsive design OK di mobile view

---

## 8. Next Steps

Jika semua test ✅ PASS:
```bash
git add .
git commit -m "test: All settings page tests passing"
git push origin main
```

Jika ada yang ❌ FAIL:
- Catat error message
- Screenshot issue
- Debug step by step
- Fix & test ulang

---

**Happy Testing! 🎉**

Dokumentasi lengkap: `TESTING_SETTINGS_PAGE.md`
