# 🎨 SettingsPage Component - Documentation

## Overview

**SettingsPage.jsx** adalah komponen React minimalis untuk mengatur konfigurasi Smart Garden IoT dengan UI yang clean dan responsif.

## 📁 File Location

```
resources/js/Pages/SettingsPage.jsx
```

## 🎯 Features

### 1. **Dynamic UI Based on Mode**
Tampilan input berubah otomatis sesuai mode yang dipilih:

- **Mode 1 (Basic)**: Input threshold kering/basah
- **Mode 2 (AI Fuzzy)**: Tidak ada input (full auto)
- **Mode 3 (Schedule)**: Input jam pagi/sore + durasi
- **Mode 4 (Manual)**: Input threshold custom

### 2. **Minimalist Design**
- Clean card layout dengan rounded corners
- Subtle borders dan shadows
- Spacing yang nyaman untuk mata
- Mobile-responsive

### 3. **Collapsible Calibration**
Section kalibrasi sensor disembunyikan dalam accordion untuk tidak membingungkan user awam.

### 4. **Real-time Feedback**
- Loading state saat menyimpan
- Toast notification (success/error)
- Visual feedback untuk mode selector

## 🚀 Usage

### Basic Integration

```jsx
import SettingsPage from './Pages/SettingsPage';

function App() {
  return <SettingsPage deviceId={1} />;
}
```

### Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `deviceId` | number | 1 | ID perangkat yang akan dikonfigurasi |

## 🔗 API Endpoints Used

### GET `/api/devices/{id}`
Mengambil data settings device saat ini.

**Response:**
```json
{
  "success": true,
  "data": {
    "device_name": "Kebun Depan",
    "mode": 1,
    "batas_siram": 40,
    "batas_stop": 70,
    "jam_pagi": "07:00",
    "jam_sore": "17:00",
    "durasi_siram": 5,
    "sensor_min": 4095,
    "sensor_max": 1500,
    "is_active": true
  }
}
```

### POST `/api/devices/{id}/mode`
Menyimpan konfigurasi mode dan parameter terkait.

**Request Body:**
```json
{
  "mode": 1,
  "batas_siram": 40,
  "batas_stop": 70,
  "jam_pagi": "07:00",
  "jam_sore": "17:00",
  "durasi_siram": 5,
  "sensor_min": 4095,
  "sensor_max": 1500
}
```

### PUT `/api/devices/{id}`
Update nama device.

**Request Body:**
```json
{
  "device_name": "Kebun Depan"
}
```

## 🎨 Design Principles

### Color Scheme
- **Primary**: Green (`bg-green-500`, `text-green-600`) - untuk success & main actions
- **Secondary**: Gray (`bg-gray-50`, `text-gray-600`) - untuk backgrounds & secondary text
- **Accent**: Blue (`ring-blue-500`) - untuk focused inputs
- **Status**: Red/Green badges untuk active/inactive

### Typography
- **Headers**: Semibold, 18px (lg)
- **Labels**: Bold uppercase, 10px (xs), gray-400
- **Inputs**: Medium, 14px (sm)

### Spacing
- **Section gaps**: 24px (`space-y-6`)
- **Input groups**: 16px (`space-y-4`)
- **Padding**: 24px (`p-6`)

## 📱 Responsive Behavior

- **Mobile** (`< 640px`): Full screen, no border radius
- **Desktop** (`>= 640px`): Card-style dengan max-width 28rem (448px)

## ⚙️ State Management

```jsx
const [settings, setSettings] = useState({
  device_name: '',
  mode: 1,
  batas_siram: 40,
  batas_stop: 70,
  jam_pagi: '07:00',
  jam_sore: '17:00',
  durasi_siram: 5,
  sensor_min: 4095,
  sensor_max: 1500,
  is_active: true,
});
```

## 🔧 Customization

### Changing Default Values

Edit state di `useState()`:

```jsx
const [settings, setSettings] = useState({
  mode: 2, // Default to AI Fuzzy
  batas_siram: 35, // More aggressive watering
  // ... other settings
});
```

### Adding New Modes

1. Tambah button di mode selector:
```jsx
{ val: 5, label: '⚡ Expert' }
```

2. Tambah conditional rendering:
```jsx
{settings.mode == 5 && (
  <div>Your custom inputs here</div>
)}
```

## 🧪 Testing

### Manual Testing Checklist

- [ ] Load settings dari API saat component mount
- [ ] Switch antar mode dan pastikan input berubah
- [ ] Simpan settings dan cek response dari server
- [ ] Test validation (batas_stop > batas_siram)
- [ ] Test kalibrasi (sensor_min > sensor_max)
- [ ] Test responsive design di mobile
- [ ] Test loading state saat save

### Browser Console Check

```javascript
// Cek settings state
console.log(settings);

// Cek API response
axios.get('/api/devices/1').then(res => console.log(res.data));
```

## 🐛 Troubleshooting

### Settings tidak ter-load
**Masalah**: Component kosong atau stuck di loading  
**Solusi**: 
1. Cek network tab di DevTools
2. Pastikan endpoint `/api/devices/{id}` return data yang benar
3. Cek console untuk error

### Save tidak berhasil
**Masalah**: Error saat klik "Simpan Perubahan"  
**Solusi**:
1. Cek validasi di backend (DeviceController)
2. Pastikan CSRF token valid (jika ada)
3. Cek format data yang dikirim sesuai dengan backend expectation

### Mode tidak berubah
**Masalah**: UI tidak update saat klik mode button  
**Solusi**:
```jsx
// Pastikan state update correctly
onClick={() => setSettings({...settings, mode: option.val})}
// Bukan:
onClick={() => settings.mode = option.val} // ❌ Wrong!
```

## 📚 Related Files

- `resources/js/SmartGardenApp.jsx` - Parent component
- `resources/js/app.jsx` - Entry point
- `resources/views/spa-dashboard.blade.php` - Blade view yang render React
- `app/Http/Controllers/DeviceController.php` - Backend API handler

## 🎉 Next Steps

1. **Add Validation Messages**: Show field-specific errors
2. **Add Confirmation Dialog**: Before saving critical changes
3. **Add Preview Mode**: Show how the settings will work before saving
4. **Add History**: Log perubahan settings (audit trail)
5. **Add Multi-Device Support**: Dropdown untuk pilih device yang akan dikonfigurasi

## 📝 Changelog

### v1.0.0 (2026-01-08)
- ✅ Initial release
- ✅ 4 mode support (Basic, Fuzzy, Schedule, Manual)
- ✅ Collapsible calibration
- ✅ Toast notifications
- ✅ Mobile responsive
