import './bootstrap';
import React from 'react';
import ReactDOM from 'react-dom/client';
import CabaiMonitoringApp from './CabaiMonitoringApp';

console.log('App.jsx loaded!');
const appElement = document.getElementById('app');
console.log('App element:', appElement);

if (appElement) {
  ReactDOM.createRoot(appElement).render(
    <React.StrictMode>
      <CabaiMonitoringApp />
    </React.StrictMode>
  );
  console.log('React app mounted - Cabai Monitoring!');
} else {
  console.error('App element not found!');
}
