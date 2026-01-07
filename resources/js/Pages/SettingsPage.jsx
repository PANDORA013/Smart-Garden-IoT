import React, { useState, useEffect } from 'react';
import axios from 'axios';

export default function SettingsPage({ deviceId = 1 }) {
    const [loading, setLoading] = useState(false);
    const [message, setMessage] = useState('');
    
    // State sesuai database structure
    const [settings, setSettings] = useState({
        device_name: '',
        mode: 1, // 1: Basic, 2: Fuzzy, 3: Schedule, 4: Manual
        batas_siram: 40,
        batas_stop: 70,
        jam_pagi: '07:00',
        jam_sore: '17:00',
        durasi_siram: 5,
        sensor_min: 4095,
        sensor_max: 1500,
        is_active: true,
    });

    // Fetch data saat component dimuat
    useEffect(() => {
        loadSettings();
    }, [deviceId]);

    const loadSettings = async () => {
        try {
            const response = await axios.get(`/api/devices/${deviceId}`);
            if(response.data.success) {
                const data = response.data.data;
                // Format jam agar sesuai input type="time" (HH:mm)
                data.jam_pagi = data.jam_pagi ? data.jam_pagi.substring(0, 5) : '07:00';
                data.jam_sore = data.jam_sore ? data.jam_sore.substring(0, 5) : '17:00';
                setSettings(data);
            }
        } catch (error) {
            console.error("Gagal memuat setting", error);
        }
    };

    const handleChange = (e) => {
        setSettings({ ...settings, [e.target.name]: e.target.value });
    };

    const handleSave = async (e) => {
        e.preventDefault();
        setLoading(true);
        setMessage('');

        try {
            // Menggunakan endpoint updateMode karena handle hampir semua parameter
            await axios.post(`/api/devices/${deviceId}/mode`, {
                mode: settings.mode,
                batas_siram: settings.batas_siram,
                batas_stop: settings.batas_stop,
                jam_pagi: settings.jam_pagi,
                jam_sore: settings.jam_sore,
                durasi_siram: settings.durasi_siram,
                sensor_min: settings.sensor_min,
                sensor_max: settings.sensor_max,
            });
            
            // Update nama device terpisah jika perlu
            if(settings.device_name) {
                await axios.put(`/api/devices/${deviceId}`, {
                    device_name: settings.device_name
                });
            }

            setMessage('✅ Pengaturan berhasil disimpan!');
            setTimeout(() => setMessage(''), 3000);
        } catch (error) {
            setMessage('❌ Gagal menyimpan. Cek koneksi atau validasi.');
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="max-w-md mx-auto bg-white min-h-screen sm:min-h-0 sm:rounded-xl sm:shadow-sm sm:border border-gray-100 overflow-hidden">
            {/* Header Minimalis */}
            <div className="px-6 py-5 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                <h2 className="text-lg font-semibold text-gray-800">Pengaturan Alat</h2>
                <div className={`text-xs px-2 py-1 rounded-full ${settings.is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'}`}>
                    {settings.is_active ? 'Aktif' : 'Non-Aktif'}
                </div>
            </div>

            <form onSubmit={handleSave} className="p-6 space-y-6">
                
                {/* 1. Identitas Alat */}
                <div className="space-y-1">
                    <label className="text-xs font-bold text-gray-400 uppercase tracking-wider">Info Umum</label>
                    <input 
                        type="text" 
                        name="device_name"
                        value={settings.device_name}
                        onChange={handleChange}
                        className="w-full text-lg font-medium text-gray-800 border-b border-gray-200 focus:border-green-500 focus:outline-none py-2 placeholder-gray-300 transition-colors"
                        placeholder="Nama Alat (Cth: Kebun Depan)"
                    />
                </div>

                {/* 2. Mode Operasi (Core Logic) */}
                <div className="space-y-3">
                    <label className="text-xs font-bold text-gray-400 uppercase tracking-wider">Mode Operasi</label>
                    <div className="grid grid-cols-2 gap-3">
                        {[
                            { val: 1, label: '🌱 Basic' },
                            { val: 2, label: '🤖 AI Fuzzy' },
                            { val: 3, label: '📅 Jadwal' },
                            { val: 4, label: '🛠️ Manual' }
                        ].map((option) => (
                            <button
                                key={option.val}
                                type="button"
                                onClick={() => setSettings({...settings, mode: option.val})}
                                className={`py-2 px-3 text-sm rounded-lg border transition-all duration-200 ${
                                    settings.mode == option.val 
                                    ? 'border-green-500 bg-green-50 text-green-700 font-medium ring-1 ring-green-500' 
                                    : 'border-gray-200 text-gray-600 hover:border-gray-300'
                                }`}
                            >
                                {option.label}
                            </button>
                        ))}
                    </div>
                </div>

                {/* 3. Dynamic Inputs Based on Mode */}
                <div className="bg-gray-50 rounded-xl p-5 border border-gray-100 animate-fade-in">
                    
                    {/* MODE 1 & 4: THRESHOLD */}
                    {(settings.mode == 1 || settings.mode == 4) && (
                        <div className="space-y-4">
                            <div className="flex justify-between items-center">
                                <label className="text-sm text-gray-600">Batas Kering (Pompa ON)</label>
                                <div className="flex items-center space-x-2">
                                    <input 
                                        type="number" 
                                        name="batas_siram"
                                        value={settings.batas_siram}
                                        onChange={handleChange}
                                        className="w-16 text-center rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 text-sm py-1"
                                        min="0"
                                        max="100"
                                    />
                                    <span className="text-sm text-gray-500">%</span>
                                </div>
                            </div>
                            <div className="flex justify-between items-center">
                                <label className="text-sm text-gray-600">Batas Basah (Pompa OFF)</label>
                                <div className="flex items-center space-x-2">
                                    <input 
                                        type="number" 
                                        name="batas_stop"
                                        value={settings.batas_stop}
                                        onChange={handleChange}
                                        className="w-16 text-center rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 text-sm py-1"
                                        min="0"
                                        max="100"
                                    />
                                    <span className="text-sm text-gray-500">%</span>
                                </div>
                            </div>
                        </div>
                    )}

                    {/* MODE 3: SCHEDULE */}
                    {settings.mode == 3 && (
                        <div className="space-y-4">
                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <label className="block text-xs text-gray-500 mb-1">Jam Pagi</label>
                                    <input 
                                        type="time" 
                                        name="jam_pagi" 
                                        value={settings.jam_pagi} 
                                        onChange={handleChange} 
                                        className="w-full text-sm border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500" 
                                    />
                                </div>
                                <div>
                                    <label className="block text-xs text-gray-500 mb-1">Jam Sore</label>
                                    <input 
                                        type="time" 
                                        name="jam_sore" 
                                        value={settings.jam_sore} 
                                        onChange={handleChange} 
                                        className="w-full text-sm border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500" 
                                    />
                                </div>
                            </div>
                            <div>
                                <label className="block text-xs text-gray-500 mb-1">Durasi Siram (detik)</label>
                                <input 
                                    type="range" 
                                    name="durasi_siram" 
                                    min="1" 
                                    max="60" 
                                    value={settings.durasi_siram} 
                                    onChange={handleChange} 
                                    className="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-green-600" 
                                />
                                <div className="text-right text-xs text-green-600 font-medium">{settings.durasi_siram} detik</div>
                            </div>
                        </div>
                    )}

                    {/* MODE 2: FUZZY */}
                    {settings.mode == 2 && (
                        <div className="text-center py-2">
                            <div className="inline-block p-3 bg-blue-50 rounded-lg mb-2">
                                <i className="fa-solid fa-robot text-2xl text-blue-600"></i>
                            </div>
                            <p className="text-sm text-gray-500 italic">
                                Sistem akan otomatis menentukan penyiraman berdasarkan suhu dan kelembaban tanah menggunakan logika Fuzzy AI.
                            </p>
                        </div>
                    )}
                </div>

                {/* 4. Kalibrasi (Collapsible / Minimal) */}
                <details className="group">
                    <summary className="flex cursor-pointer items-center justify-between text-xs font-bold text-gray-400 uppercase tracking-wider py-2">
                        <span>🔧 Kalibrasi Sensor (Advanced)</span>
                        <span className="transition group-open:rotate-180">
                            <svg fill="none" height="20" shapeRendering="geometricPrecision" stroke="currentColor" strokeLinecap="round" strokeLinejoin="round" strokeWidth="1.5" viewBox="0 0 24 24" width="20"><path d="M6 9l6 6 6-6"></path></svg>
                        </span>
                    </summary>
                    <div className="mt-3 grid grid-cols-2 gap-4 bg-gray-50 p-4 rounded-lg border border-gray-200">
                        <div>
                            <label className="block text-xs text-gray-500 mb-1">Kering (Udara)</label>
                            <input 
                                type="number" 
                                name="sensor_min" 
                                value={settings.sensor_min} 
                                onChange={handleChange} 
                                className="w-full text-sm border-gray-300 rounded-md focus:ring-amber-500 focus:border-amber-500" 
                                min="0"
                                max="4095"
                            />
                        </div>
                        <div>
                            <label className="block text-xs text-gray-500 mb-1">Basah (Air)</label>
                            <input 
                                type="number" 
                                name="sensor_max" 
                                value={settings.sensor_max} 
                                onChange={handleChange} 
                                className="w-full text-sm border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" 
                                min="0"
                                max="4095"
                            />
                        </div>
                        <div className="col-span-2 text-xs text-gray-500 mt-2">
                            <i className="fa-solid fa-info-circle text-amber-500"></i> Nilai default: Kering=4095, Basah=1500
                        </div>
                    </div>
                </details>

                {/* Submit Button */}
                <div className="pt-2">
                    <button 
                        type="submit" 
                        disabled={loading}
                        className="w-full flex justify-center items-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 transition-colors"
                    >
                        {loading ? (
                            <>
                                <svg className="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                                    <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Menyimpan...
                            </>
                        ) : (
                            <>
                                <i className="fa-solid fa-save mr-2"></i>
                                Simpan Perubahan
                            </>
                        )}
                    </button>
                    
                    {message && (
                        <p className={`mt-3 text-center text-sm font-medium ${message.includes('Gagal') || message.includes('❌') ? 'text-red-500' : 'text-green-600'}`}>
                            {message}
                        </p>
                    )}
                </div>
            </form>
        </div>
    );
}
