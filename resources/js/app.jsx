import './bootstrap';
import React from 'react';
import ReactDOM from 'react-dom/client';
import SmartGardenApp from './SmartGardenApp';

console.log('App.jsx loaded!');
const appElement = document.getElementById('app');
console.log('App element:', appElement);

if (appElement) {
  ReactDOM.createRoot(appElement).render(
    <React.StrictMode>
      <SmartGardenApp />
    </React.StrictMode>
  );
  console.log('React app mounted!');
} else {
  console.error('App element not found!');
}
