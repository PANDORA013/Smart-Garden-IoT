import React, { useState, useEffect } from 'react';
import { Droplet, Zap, Activity, Timer, Settings, AlertTriangle, Power, Sprout, Waves } from 'lucide-react';

const SmartGardenApp = () => {
  const [isAutoMode, setIsAutoMode] = useState(true);
  const [isPumpOn, setIsPumpOn] = useState(true);
  const [moisture, setMoisture] = useState(65);
  const [soilFriability, setSoilFriability] = useState(72);
  const [waterLevel, setWaterLevel] = useState(75);
  const [powerUsage, setPowerUsage] = useState(42.3);
  const [voltage, setVoltage] = useState(220);
  const [timerSettings, setTimerSettings] = useState({ start: "07:00", duration: 15 });

  useEffect(() => {
    const interval = setInterval(() => {
      if (isPumpOn) {
        setMoisture(prev => Math.min(prev + 0.3, 100));
        setWaterLevel(prev => Math.max(prev - 0.15, 0));
        setPowerUsage(prev => 40 + Math.random() * 8);
      } else {
        setMoisture(prev => Math.max(prev - 0.05, 10));
        setPowerUsage(prev => 2 + Math.random() * 1);
      }

      if (isAutoMode) {
        if (moisture < 35 && waterLevel > 10) {
          setIsPumpOn(true);
        } else if (moisture > 75) {
          setIsPumpOn(false);
        }
      }

      if (waterLevel <= 5 && isPumpOn) {
        setIsPumpOn(false);
      }
    }, 2000);

    return () => clearInterval(interval);
  }, [isPumpOn, isAutoMode, moisture, waterLevel]);

  const getRecommendations = () => {
    const recs = [];
    if (moisture < 20) recs.push({ text: "Tanah sangat kering. Segera lakukan penyiraman intensif.", type: "urgent" });
    else if (moisture > 90) recs.push({ text: "Tanah terlalu basah. Hentikan penyiraman untuk mencegah akar busuk.", type: "warning" });
    if (soilFriability < 40) recs.push({ text: "Tanah mulai padat. Disarankan melakukan penggemburan.", type: "info" });
    if (waterLevel < 20) recs.push({ text: "Stok air menipis (<20%). Harap isi ulang tangki.", type: "urgent" });
    if (powerUsage > 50 && !isPumpOn) recs.push({ text: "Deteksi anomali daya tinggi saat pompa mati.", type: "warning" });
    if (recs.length === 0) recs.push({ text: "Kondisi lahan optimal. Tidak ada tindakan diperlukan.", type: "success" });
    return recs;
  };

  const togglePump = () => {
    if (isAutoMode) {
      alert("Matikan Mode Otomatis dulu untuk kontrol manual.");
      return;
    }
    if (waterLevel < 5 && !isPumpOn) {
      alert("Air tangki habis! Isi ulang dulu.");
      return;
    }
    setIsPumpOn(!isPumpOn);
  };

  const MetricCard = ({ title, value, icon, subtext, progress }) => (
    <div style={{ backgroundColor: 'white', borderRadius: '12px', padding: '20px', boxShadow: '0 2px 8px rgba(0,0,0,0.1)', border: '1px solid #e5e7eb', display: 'flex', flexDirection: 'column', gap: '12px' }}>
      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
        <span style={{ fontSize: '14px', color: '#6b7280', fontWeight: '500' }}>{title}</span>
        <div style={{ color: '#6b7280' }}>{icon}</div>
      </div>
      <div style={{ fontSize: '28px', fontWeight: '700', color: '#1f2937' }}>{value}</div>
      <div style={{ width: '100%', height: '8px', backgroundColor: '#e5e7eb', borderRadius: '4px', overflow: 'hidden' }}>
        <div style={{ width: `${Math.min(progress, 100)}%`, height: '100%', backgroundColor: progress > 70 ? '#f97316' : progress > 40 ? '#eab308' : '#3b82f6', transition: 'width 0.3s ease' }} />
      </div>
      <span style={{ fontSize: '12px', color: '#9ca3af' }}>{subtext}</span>
    </div>
  );

  return (
    <div style={{ minHeight: '100vh', backgroundColor: '#f1f5f9', padding: '24px', fontFamily: 'system-ui, -apple-system, sans-serif' }}>
      <header style={{ backgroundColor: '#059669', color: 'white', padding: '20px', marginBottom: '24px', borderRadius: '12px', boxShadow: '0 4px 12px rgba(0,0,0,0.15)' }}>
        <div style={{ maxWidth: '1200px', margin: '0 auto', display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
          <div style={{ display: 'flex', alignItems: 'center', gap: '12px' }}>
            <Sprout size={32} />
            <div>
              <h1 style={{ fontSize: '24px', fontWeight: 'bold', margin: '0' }}>Smart Garden IoT</h1>
              <p style={{ fontSize: '13px', opacity: 0.9, margin: '4px 0 0 0' }}>Connected System v1.0</p>
            </div>
          </div>
          <div style={{ display: 'flex', alignItems: 'center', gap: '16px' }}>
            <div style={{ padding: '8px 16px', borderRadius: '20px', fontSize: '13px', fontWeight: 'bold', display: 'flex', alignItems: 'center', gap: '8px', backgroundColor: isPumpOn ? '#3b82f6' : '#374151', color: 'white' }}>
              <Waves size={16} />
              {isPumpOn ? 'MENYIRAM...' : 'STANDBY'}
            </div>
            <div style={{ width: '14px', height: '14px', borderRadius: '50%', backgroundColor: voltage > 0 ? '#4ade80' : '#ef4444' }} />
          </div>
        </div>
      </header>

      <main style={{ maxWidth: '1200px', margin: '0 auto' }}>
        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(220px, 1fr))', gap: '20px', marginBottom: '24px' }}>
          <MetricCard title="Kelembapan Tanah" icon={<Droplet style={{ color: '#3b82f6' }} size={24} />} value={`${moisture.toFixed(1)}%`} subtext={moisture < 30 ? "Kering" : moisture > 70 ? "Basah" : "Normal"} progress={moisture} />
          <MetricCard title="Kegemburan Tanah" icon={<Activity style={{ color: '#b45309' }} size={24} />} value={`${soilFriability}/100`} subtext={soilFriability < 50 ? "Perlu Penggemburan" : "Gembur"} progress={soilFriability} />
          <MetricCard title="Level Tangki Air" icon={<Waves style={{ color: '#06b6d4' }} size={24} />} value={`${waterLevel.toFixed(1)}%`} subtext="Sensor: HC-SR04" progress={waterLevel} />
          <MetricCard title="Konsumsi Daya" icon={<Zap style={{ color: '#eab308' }} size={24} />} value={`${powerUsage.toFixed(1)} W`} subtext={`Timer: ${timerSettings.duration} m`} progress={Math.min((powerUsage / 50) * 100, 100)} />
        </div>

        <div style={{ display: 'grid', gridTemplateColumns: '2fr 1fr', gap: '24px' }}>
          <div style={{ backgroundColor: 'white', borderRadius: '12px', boxShadow: '0 2px 8px rgba(0,0,0,0.1)', overflow: 'hidden' }}>
            <div style={{ backgroundColor: '#f3f4f6', padding: '16px 24px', borderBottom: '1px solid #e5e7eb', display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
              <h2 style={{ fontWeight: '600', color: '#374151', display: 'flex', alignItems: 'center', gap: '8px', margin: '0', fontSize: '16px' }}><Settings size={20} /> Panel Kontrol</h2>
              <div style={{ display: 'flex', alignItems: 'center', gap: '12px' }}>
                <span style={{ fontSize: '13px', color: isAutoMode ? '#059669' : '#9ca3af', fontWeight: 'bold' }}>Auto</span>
                <button onClick={() => setIsAutoMode(!isAutoMode)} style={{ position: 'relative', display: 'inline-flex', height: '28px', width: '52px', alignItems: 'center', borderRadius: '20px', backgroundColor: isAutoMode ? '#059669' : '#d1d5db', border: 'none', cursor: 'pointer', transition: 'background-color 0.2s' }}>
                  <span style={{ display: 'inline-block', height: '20px', width: '20px', borderRadius: '50%', backgroundColor: 'white', transition: 'transform 0.2s', transform: isAutoMode ? 'translateX(26px)' : 'translateX(2px)', marginLeft: '2px' }} />
                </button>
                <span style={{ fontSize: '13px', color: !isAutoMode ? '#2563eb' : '#9ca3af', fontWeight: 'bold' }}>Manual</span>
              </div>
            </div>

            <div style={{ padding: '24px', display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '32px' }}>
              <div style={{ display: 'flex', flexDirection: 'column', alignItems: 'center', justifyContent: 'center', gap: '20px', padding: '20px', backgroundColor: '#f9fafb', borderRadius: '8px', border: '1px solid #f3f4f6' }}>
                <div style={{ padding: '20px', borderRadius: '50%', border: '4px solid', borderColor: isPumpOn ? '#60a5fa' : '#d1d5db', backgroundColor: isPumpOn ? '#dbeafe' : 'white', boxShadow: isPumpOn ? '0 0 20px rgba(59,130,246,0.3)' : 'none' }}>
                  <Power size={56} style={{ color: isPumpOn ? '#2563eb' : '#9ca3af' }} />
                </div>
                <button onClick={togglePump} disabled={isAutoMode} style={{ width: '100%', padding: '14px 24px', borderRadius: '8px', fontWeight: 'bold', boxShadow: '0 2px 8px rgba(0,0,0,0.1)', transition: 'all 0.2s', backgroundColor: isAutoMode ? '#e5e7eb' : isPumpOn ? '#ef4444' : '#059669', color: isAutoMode ? '#9ca3af' : 'white', cursor: isAutoMode ? 'not-allowed' : 'pointer', border: 'none', fontSize: '14px' }}>
                  {isAutoMode ? "Mode Otomatis Aktif" : isPumpOn ? "MATIKAN POMPA" : "NYALAKAN POMPA"}
                </button>
              </div>

              <div style={{ display: 'flex', flexDirection: 'column', gap: '16px' }}>
                <h3 style={{ fontSize: '12px', fontWeight: '600', color: '#6b7280', textTransform: 'uppercase', letterSpacing: '0.05em', margin: 0, marginBottom: '8px' }}>Pengaturan Timer (Auto)</h3>
                <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', padding: '12px', backgroundColor: 'white', border: '1px solid #e5e7eb', borderRadius: '8px' }}>
                  <div style={{ display: 'flex', alignItems: 'center', gap: '12px' }}>
                    <Timer size={20} style={{ color: '#a855f7' }} />
                    <div>
                      <p style={{ fontSize: '14px', fontWeight: '500', margin: 0 }}>Jadwal Penyiraman</p>
                      <p style={{ fontSize: '12px', color: '#6b7280', margin: 0 }}>Mulai Harian</p>
                    </div>
                  </div>
                  <input type="time" value={timerSettings.start} onChange={(e) => setTimerSettings({...timerSettings, start: e.target.value})} style={{ backgroundColor: '#f3f4f6', borderRadius: '4px', padding: '8px', fontSize: '13px', outline: 'none', border: '1px solid #d1d5db', cursor: 'pointer' }} />
                </div>
                <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', padding: '12px', backgroundColor: 'white', border: '1px solid #e5e7eb', borderRadius: '8px' }}>
                  <div style={{ display: 'flex', alignItems: 'center', gap: '12px' }}>
                    <Zap size={20} style={{ color: '#eab308' }} />
                    <div>
                      <p style={{ fontSize: '14px', fontWeight: '500', margin: 0 }}>Batas Durasi Power</p>
                      <p style={{ fontSize: '12px', color: '#6b7280', margin: 0 }}>Max penyiraman (menit)</p>
                    </div>
                  </div>
                  <input type="number" value={timerSettings.duration} onChange={(e) => setTimerSettings({...timerSettings, duration: parseInt(e.target.value)})} style={{ width: '60px', backgroundColor: '#f3f4f6', borderRadius: '4px', padding: '8px', fontSize: '13px', outline: 'none', border: '1px solid #d1d5db', textAlign: 'right', cursor: 'pointer' }} />
                </div>
              </div>
            </div>
          </div>

          <div style={{ backgroundColor: 'white', borderRadius: '12px', boxShadow: '0 2px 8px rgba(0,0,0,0.1)', overflow: 'hidden', display: 'flex', flexDirection: 'column' }}>
            <div style={{ background: 'linear-gradient(to right, #6366f1, #7c3aed)', padding: '16px 24px' }}>
              <h2 style={{ fontWeight: '600', color: 'white', display: 'flex', alignItems: 'center', gap: '8px', margin: 0, fontSize: '16px' }}><AlertTriangle size={20} /> Rekomendasi Cerdas</h2>
            </div>
            <div style={{ padding: '16px', flex: 1, overflowY: 'auto', display: 'flex', flexDirection: 'column', gap: '12px' }}>
              {getRecommendations().map((rec, idx) => (
                <div key={idx} style={{ padding: '12px', borderRadius: '8px', borderLeft: '4px solid', fontSize: '13px', lineHeight: '1.5', backgroundColor: rec.type === 'urgent' ? '#fef2f2' : rec.type === 'warning' ? '#fef3c7' : rec.type === 'success' ? '#f0fdf4' : '#eff6ff', borderColor: rec.type === 'urgent' ? '#dc2626' : rec.type === 'warning' ? '#d97706' : rec.type === 'success' ? '#16a34a' : '#3b82f6', color: rec.type === 'urgent' ? '#991b1b' : rec.type === 'warning' ? '#92400e' : rec.type === 'success' ? '#166534' : '#1e40af' }}>
                  {rec.text}
                </div>
              ))}
            </div>

            <div style={{ padding: '16px', borderTop: '1px solid #f3f4f6' }}>
              <p style={{ fontSize: '12px', fontWeight: '600', color: '#6b7280', margin: '0 0 12px 0' }}>VISUALISASI TANGKI AIR</p>
              <div style={{ width: '100%', height: '100px', backgroundColor: '#e5e7eb', borderRadius: '8px', position: 'relative', overflow: 'hidden', border: '1px solid #d1d5db', boxShadow: 'inset 0 2px 4px rgba(0,0,0,0.05)' }}>
                <div style={{ position: 'absolute', bottom: 0, left: 0, width: '100%', backgroundColor: '#06b6d4', transition: 'height 0.5s ease-in-out', opacity: 0.8, height: `${waterLevel}%` }}>
                  <div style={{ width: '100%', height: '6px', backgroundColor: '#22d3ee', opacity: 0.6, position: 'absolute', top: 0 }} />
                </div>
                <div style={{ position: 'absolute', inset: 0, display: 'flex', alignItems: 'center', justifyContent: 'center', zIndex: 10 }}>
                  <span style={{ fontSize: '18px', fontWeight: 'bold', color: '#1e293b', textShadow: '0 0 8px rgba(255,255,255,0.5)', backgroundColor: 'rgba(255,255,255,0.6)', padding: '6px 12px', borderRadius: '6px' }}>
                    {waterLevel.toFixed(0)}%
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>
  );
};

export default SmartGardenApp;
