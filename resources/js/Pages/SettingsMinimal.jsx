import React, { useState, useEffect } from 'react';
import axios from 'axios';

export default function SettingsMinimal({ deviceId = 1 }) {
    const [loading, setLoading] = useState(false);
    const [notif, setNotif] = useState({ show: false, msg: '', type: '' });
    const [settings, setSettings] = useState({
        device_name: '',
        mode: 1,
        batas_siram: 40,
        batas_stop: 70,
        jam_pagi: '07:00',
        jam_sore: '17:00',
        durasi_siram: 5,
    });

    useEffect(() => {
        loadSettings();
    }, [deviceId]);

    const loadSettings = async () => {
        try {
            // Pastikan route ini sesuai dengan DeviceController Anda
            const res = await axios.get(`/api/devices/${deviceId}`);
            if(res.data.success) {
                const d = res.data.data;
                if(d.jam_pagi) d.jam_pagi = d.jam_pagi.substring(0,5);
                if(d.jam_sore) d.jam_sore = d.jam_sore.substring(0,5);
                setSettings(d);
            }
        } catch (e) { console.error(e); }
    };

    const handleSave = async () => {
        setLoading(true);
        try {
            await axios.post(`/api/devices/${deviceId}/mode`, settings);
            if(settings.device_name) {
                await axios.put(`/api/devices/${deviceId}`, { device_name: settings.device_name });
            }
            setNotif({ show: true, msg: '✅ Berhasil disimpan!', type: 'success' });
            setTimeout(() => setNotif({ show: false }), 3000);
        } catch (e) {
            setNotif({ show: true, msg: '❌ Gagal menyimpan.', type: 'error' });
        }
        setLoading(false);
    };

    const setMode = (val) => setSettings({...settings, mode: val});

    return (
        <div className="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div className="px-6 py-4 border-b border-gray-50 flex justify-between items-center">
                <h2 className="text-lg font-bold text-gray-800">Pengaturan</h2>
                <div className="h-2 w-2 rounded-full bg-green-500 animate-pulse"></div>
            </div>
            <div className="p-6 space-y-6">
                <div className="space-y-1">
                    <label className="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Nama Perangkat</label>
                    <input type="text" value={settings.device_name} onChange={(e) => setSettings({...settings, device_name: e.target.value})} className="w-full text-lg font-medium border-b border-gray-100 focus:border-green-500 outline-none py-2" />
                </div>
                <div className="space-y-2">
                    <label className="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Mode Operasi</label>
                    <div className="grid grid-cols-2 gap-2">
                        {[{id: 1, label: 'Basic'}, {id: 2, label: 'Fuzzy AI'}, {id: 3, label: 'Jadwal'}, {id: 4, label: 'Manual'}].map((m) => (
                            <button key={m.id} onClick={() => setMode(m.id)} className={`py-2 px-3 rounded-xl text-sm font-medium transition-all ${settings.mode == m.id ? 'bg-green-50 text-green-700 border border-green-500' : 'border border-gray-200 text-gray-500'}`}>{m.label}</button>
                        ))}
                    </div>
                </div>
                {/* Area Setting Dinamis */}
                <div className="bg-gray-50 rounded-xl p-5 border border-gray-100 space-y-4">
                    {(settings.mode == 1 || settings.mode == 4) && (
                        <>
                            <div className="flex justify-between"><span className="text-sm text-gray-600">Batas Kering (ON)</span><div className="bg-white border rounded px-2"><input type="number" value={settings.batas_siram} onChange={(e)=>setSettings({...settings, batas_siram: e.target.value})} className="w-10 text-center outline-none text-sm"/>%</div></div>
                            <div className="flex justify-between"><span className="text-sm text-gray-600">Batas Basah (OFF)</span><div className="bg-white border rounded px-2"><input type="number" value={settings.batas_stop} onChange={(e)=>setSettings({...settings, batas_stop: e.target.value})} className="w-10 text-center outline-none text-sm"/>%</div></div>
                        </>
                    )}
                    {settings.mode == 3 && (
                        <div className="grid grid-cols-2 gap-4">
                            <div><label className="text-xs text-gray-400">Jam Pagi</label><input type="time" value={settings.jam_pagi} onChange={(e)=>setSettings({...settings, jam_pagi: e.target.value})} className="w-full text-sm rounded border-gray-200"/></div>
                            <div><label className="text-xs text-gray-400">Jam Sore</label><input type="time" value={settings.jam_sore} onChange={(e)=>setSettings({...settings, jam_sore: e.target.value})} className="w-full text-sm rounded border-gray-200"/></div>
                        </div>
                    )}
                    {settings.mode == 2 && <p className="text-xs text-center text-gray-500 italic">Mode Otomatis Fuzzy AI</p>}
                </div>
                <button onClick={handleSave} disabled={loading} className="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-3 rounded-xl shadow-lg shadow-green-100 transition-all">{loading ? '...' : 'Simpan Perubahan'}</button>
                {notif.show && <div className={`text-center text-xs ${notif.type === 'error' ? 'text-red-500' : 'text-green-600'}`}>{notif.msg}</div>}
            </div>
        </div>
    );
}
