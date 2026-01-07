import React from 'react';
import SettingsPage from './Pages/SettingsPage';

function SmartGardenApp() {
  return (
    <div className="min-h-screen bg-gradient-to-br from-green-50 to-blue-50 py-8 px-4">
      <div className="text-center mb-8">
        <h1 className="text-4xl font-bold text-green-700 mb-2">
          🌿 Smart Garden IoT
        </h1>
        <p className="text-gray-600">
          Pengaturan Sistem Penyiraman Otomatis
        </p>
      </div>
      
      {/* Panggil Component Settings */}
      <SettingsPage deviceId={1} /> 
      
    </div>
  );
}

export default SmartGardenApp;
