import React, { useState, useEffect } from 'react';
import { Droplet, Power, AlertTriangle, Activity } from 'lucide-react';
import axios from 'axios';

const CabaiMonitoringApp = () => {
  // State untuk data sensor
  const [soilMoisture, setSoilMoisture] = useState(0);
  const [statusPompa, setStatusPompa] = useState('Mati');
  const [isLoading, setIsLoading] = useState(true);
  const [lastUpdate, setLastUpdate] = useState(null);

  // Fetch data dari API setiap 3 detik
  useEffect(() => {
    const fetchData = async () => {
      try {
        const response = await axios.get('/api/monitoring/latest');
        if (response.data.success) {
          setSoilMoisture(response.data.data.soil_moisture || 0);
          setStatusPompa(response.data.data.status_pompa || 'Mati');
          setLastUpdate(new Date());
        }
        setIsLoading(false);
      } catch (error) {
        console.error('Error fetching data:', error);
        setIsLoading(false);
      }
    };

    fetchData(); // Initial fetch
    const interval = setInterval(fetchData, 3000); // Update setiap 3 detik

    return () => clearInterval(interval);
  }, []);

  // Fungsi untuk mendapatkan status kelembapan
  const getMoistureStatus = () => {
    if (soilMoisture < 40) return { text: 'KERING', color: '#ef4444', bg: '#fee2e2' };
    if (soilMoisture < 70) return { text: 'NORMAL', color: '#22c55e', bg: '#dcfce7' };
    return { text: 'BASAH', color: '#3b82f6', bg: '#dbeafe' };
  };

  // Fungsi untuk mendapatkan rekomendasi
  const getRecommendation = () => {
    if (soilMoisture < 40) {
      return {
        text: '🚨 Tanah cabai terlalu kering! Pompa harus menyala untuk penyiraman.',
        type: 'urgent',
        bgColor: '#fee2e2',
        borderColor: '#dc2626',
        textColor: '#991b1b'
      };
    }
    if (soilMoisture > 80) {
      return {
        text: '⚠️ Tanah terlalu basah. Hentikan penyiraman untuk mencegah busuk akar.',
        type: 'warning',
        bgColor: '#fef3c7',
        borderColor: '#d97706',
        textColor: '#92400e'
      };
    }
    return {
      text: '✅ Kondisi tanah cabai optimal. Sistem berjalan normal.',
      type: 'success',
      bgColor: '#dcfce7',
      borderColor: '#16a34a',
      textColor: '#166534'
    };
  };

  const moistureStatus = getMoistureStatus();
  const recommendation = getRecommendation();

  if (isLoading) {
    return (
      <div style={{ minHeight: '100vh', display: 'flex', alignItems: 'center', justifyContent: 'center', backgroundColor: '#f1f5f9' }}>
        <div style={{ textAlign: 'center' }}>
          <Activity size={48} style={{ color: '#059669', animation: 'spin 1s linear infinite' }} />
          <p style={{ marginTop: '16px', fontSize: '18px', color: '#64748b' }}>Loading data...</p>
        </div>
      </div>
    );
  }

  return (
    <div style={{ minHeight: '100vh', backgroundColor: '#f1f5f9', padding: '24px', fontFamily: 'system-ui, -apple-system, sans-serif' }}>
      
      {/* HEADER */}
      <header style={{
        backgroundColor: '#dc2626',
        color: 'white',
        padding: '24px',
        borderRadius: '12px',
        boxShadow: '0 4px 12px rgba(220, 38, 38, 0.3)',
        marginBottom: '32px'
      }}>
        <div style={{ maxWidth: '1000px', margin: '0 auto', display: 'flex', justifyContent: 'space-between', alignItems: 'center', flexWrap: 'wrap', gap: '16px' }}>
          <div>
            <h1 style={{ fontSize: '32px', fontWeight: 'bold', margin: '0', display: 'flex', alignItems: 'center', gap: '12px' }}>
              🌶️ Monitoring Cabai IoT
            </h1>
            <p style={{ fontSize: '14px', opacity: 0.9, margin: '8px 0 0 0' }}>
              Sistem monitoring kelembapan tanah & kontrol pompa otomatis
            </p>
          </div>
          <div style={{
            padding: '12px 20px',
            borderRadius: '20px',
            fontSize: '14px',
            fontWeight: 'bold',
            display: 'flex',
            alignItems: 'center',
            gap: '8px',
            backgroundColor: statusPompa === 'Hidup' ? '#3b82f6' : '#374151',
            boxShadow: statusPompa === 'Hidup' ? '0 0 20px rgba(59, 130, 246, 0.5)' : 'none'
          }}>
            <Power size={18} />
            POMPA: {statusPompa.toUpperCase()}
          </div>
        </div>
      </header>

      <main style={{ maxWidth: '1000px', margin: '0 auto' }}>
        
        {/* METRIC SECTION */}
        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(300px, 1fr))', gap: '24px', marginBottom: '32px' }}>
          
          {/* Kelembapan Tanah Card */}
          <div style={{
            backgroundColor: 'white',
            borderRadius: '16px',
            padding: '32px',
            boxShadow: '0 4px 12px rgba(0,0,0,0.08)',
            border: `3px solid ${moistureStatus.color}`,
            position: 'relative',
            overflow: 'hidden'
          }}>
            <div style={{
              position: 'absolute',
              top: 0,
              left: 0,
              right: 0,
              height: '6px',
              backgroundColor: moistureStatus.color
            }} />
            
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', marginBottom: '16px' }}>
              <div>
                <p style={{ fontSize: '14px', color: '#64748b', margin: '0', fontWeight: '600' }}>KELEMBAPAN TANAH</p>
                <p style={{ fontSize: '48px', fontWeight: 'bold', color: '#1f2937', margin: '8px 0 0 0', lineHeight: 1 }}>
                  {soilMoisture.toFixed(1)}%
                </p>
              </div>
              <div style={{
                padding: '12px',
                borderRadius: '12px',
                backgroundColor: moistureStatus.bg
              }}>
                <Droplet size={32} style={{ color: moistureStatus.color }} />
              </div>
            </div>

            <div style={{
              padding: '8px 16px',
              borderRadius: '8px',
              backgroundColor: moistureStatus.bg,
              display: 'inline-block',
              marginBottom: '16px'
            }}>
              <span style={{ fontSize: '14px', fontWeight: 'bold', color: moistureStatus.color }}>
                STATUS: {moistureStatus.text}
              </span>
            </div>

            {/* Progress Bar */}
            <div style={{
              width: '100%',
              height: '12px',
              backgroundColor: '#e5e7eb',
              borderRadius: '6px',
              overflow: 'hidden',
              marginTop: '16px'
            }}>
              <div style={{
                width: `${Math.min(soilMoisture, 100)}%`,
                height: '100%',
                backgroundColor: moistureStatus.color,
                transition: 'width 0.5s ease',
                boxShadow: `0 0 10px ${moistureStatus.color}`
              }} />
            </div>

            <p style={{ fontSize: '12px', color: '#9ca3af', marginTop: '12px', margin: '12px 0 0 0' }}>
              Sensor: Soil Moisture v2.0
            </p>
          </div>

          {/* Status Pompa Card */}
          <div style={{
            backgroundColor: 'white',
            borderRadius: '16px',
            padding: '32px',
            boxShadow: '0 4px 12px rgba(0,0,0,0.08)',
            border: '2px solid #e5e7eb',
            display: 'flex',
            flexDirection: 'column',
            alignItems: 'center',
            justifyContent: 'center',
            gap: '24px'
          }}>
            <div style={{
              padding: '24px',
              borderRadius: '50%',
              border: '4px solid',
              borderColor: statusPompa === 'Hidup' ? '#3b82f6' : '#d1d5db',
              backgroundColor: statusPompa === 'Hidup' ? '#dbeafe' : '#f3f4f6',
              boxShadow: statusPompa === 'Hidup' ? '0 0 30px rgba(59, 130, 246, 0.4)' : 'none',
              transition: 'all 0.3s ease'
            }}>
              <Power size={64} style={{ color: statusPompa === 'Hidup' ? '#2563eb' : '#9ca3af' }} />
            </div>

            <div style={{ textAlign: 'center' }}>
              <p style={{ fontSize: '16px', color: '#64748b', margin: '0', fontWeight: '600' }}>STATUS POMPA AIR</p>
              <p style={{
                fontSize: '32px',
                fontWeight: 'bold',
                margin: '8px 0 0 0',
                color: statusPompa === 'Hidup' ? '#2563eb' : '#9ca3af'
              }}>
                {statusPompa.toUpperCase()}
              </p>
            </div>

            <div style={{
              width: '100%',
              padding: '12px',
              borderRadius: '8px',
              backgroundColor: statusPompa === 'Hidup' ? '#dbeafe' : '#f3f4f6',
              textAlign: 'center'
            }}>
              <p style={{
                fontSize: '13px',
                color: statusPompa === 'Hidup' ? '#1e40af' : '#64748b',
                margin: 0,
                fontWeight: '500'
              }}>
                {statusPompa === 'Hidup' ? '💧 Sedang menyiram tanaman cabai' : '⏸️ Menunggu trigger kelembapan'}
              </p>
            </div>
          </div>
        </div>

        {/* RECOMMENDATION PANEL */}
        <div style={{
          backgroundColor: 'white',
          borderRadius: '16px',
          padding: '32px',
          boxShadow: '0 4px 12px rgba(0,0,0,0.08)',
          border: '2px solid #e5e7eb',
          marginBottom: '32px'
        }}>
          <div style={{
            display: 'flex',
            alignItems: 'center',
            gap: '12px',
            marginBottom: '20px'
          }}>
            <AlertTriangle size={24} style={{ color: '#8b5cf6' }} />
            <h2 style={{ fontSize: '20px', fontWeight: 'bold', color: '#1f2937', margin: 0 }}>
              Rekomendasi Sistem
            </h2>
          </div>

          <div style={{
            padding: '20px',
            borderRadius: '12px',
            borderLeft: '6px solid',
            backgroundColor: recommendation.bgColor,
            borderColor: recommendation.borderColor,
            color: recommendation.textColor,
            fontSize: '15px',
            lineHeight: '1.6',
            fontWeight: '500'
          }}>
            {recommendation.text}
          </div>

          <div style={{
            marginTop: '24px',
            padding: '16px',
            backgroundColor: '#f8fafc',
            borderRadius: '8px',
            border: '1px solid #e2e8f0'
          }}>
            <p style={{ fontSize: '13px', color: '#64748b', margin: 0, fontWeight: '600' }}>
              📌 <strong>Logika Otomatis:</strong> Pompa akan <span style={{ color: '#ef4444', fontWeight: 'bold' }}>HIDUP</span> otomatis jika kelembapan <strong>&lt; 40%</strong>, dan <span style={{ color: '#22c55e', fontWeight: 'bold' }}>MATI</span> jika kelembapan sudah mencukupi.
            </p>
          </div>
        </div>

        {/* INFO PANEL */}
        <div style={{
          backgroundColor: '#1e293b',
          color: 'white',
          borderRadius: '12px',
          padding: '24px',
          display: 'flex',
          justifyContent: 'space-between',
          alignItems: 'center',
          flexWrap: 'wrap',
          gap: '16px'
        }}>
          <div>
            <p style={{ fontSize: '14px', opacity: 0.8, margin: 0 }}>Last Update:</p>
            <p style={{ fontSize: '16px', fontWeight: 'bold', margin: '4px 0 0 0' }}>
              {lastUpdate ? lastUpdate.toLocaleString('id-ID') : 'Menunggu data...'}
            </p>
          </div>
          <div style={{
            padding: '8px 16px',
            borderRadius: '8px',
            backgroundColor: 'rgba(255, 255, 255, 0.1)',
            fontSize: '13px',
            fontWeight: '600'
          }}>
            🔄 Auto-refresh setiap 3 detik
          </div>
        </div>

      </main>

      {/* CSS Animation */}
      <style>{`
        @keyframes spin {
          from { transform: rotate(0deg); }
          to { transform: rotate(360deg); }
        }
      `}</style>
    </div>
  );
};

export default CabaiMonitoringApp;
