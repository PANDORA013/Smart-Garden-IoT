import React, { useState } from 'react';
// 1. Import Dashboard (Pastikan path ini benar sesuai struktur folder Anda)
import CabaiMonitoringApp from './CabaiMonitoringApp'; 
// 2. Import Halaman Setting Baru
import SettingsMinimal from './Pages/SettingsMinimal'; 

export default function SmartGardenApp() {
    const [activeTab, setActiveTab] = useState('home'); // 'home' atau 'settings'

    return (
        <div className="min-h-screen bg-gray-50 pb-24 font-sans">
            {/* Header Sederhana */}
            <div className="pt-6 pb-2 text-center">
                <h1 className="text-2xl font-bold text-gray-800 tracking-tight">
                    Smart <span className="text-green-600">Garden</span>
                </h1>
            </div>

            {/* Konten Utama (Switching) */}
            <main className="max-w-md mx-auto px-4">
                {activeTab === 'home' ? (
                    <CabaiMonitoringApp />
                ) : (
                    <SettingsMinimal deviceId={1} />
                )}
            </main>

            {/* Navbar Bawah (Floating) */}
            <div className="fixed bottom-6 left-1/2 transform -translate-x-1/2 bg-white/90 backdrop-blur border border-gray-200 shadow-2xl rounded-full px-8 py-3 flex items-center space-x-10 z-50">
                <button onClick={() => setActiveTab('home')} className={`flex flex-col items-center ${activeTab === 'home' ? 'text-green-600' : 'text-gray-400'}`}>
                    <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    <span className="text-[10px] font-bold mt-1">Monitor</span>
                </button>
                <div className="w-px h-8 bg-gray-200"></div>
                <button onClick={() => setActiveTab('settings')} className={`flex flex-col items-center ${activeTab === 'settings' ? 'text-green-600' : 'text-gray-400'}`}>
                    <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    <span className="text-[10px] font-bold mt-1">Setting</span>
                </button>
            </div>
        </div>
    );
}

