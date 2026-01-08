# 🌱 Smart Garden IoT

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-10.x-red?style=for-the-badge&logo=laravel" alt="Laravel">
  <img src="https://img.shields.io/badge/JavaScript-Vanilla-yellow?style=for-the-badge&logo=javascript" alt="JavaScript">
  <img src="https://img.shields.io/badge/Raspberry%20Pi%20Pico%20W-C2145?style=for-the-badge&logo=raspberrypi" alt="Pico W">
  <img src="https://img.shields.io/badge/License-MIT-blue?style=for-the-badge" alt="License">
  <img src="https://img.shields.io/badge/Version-1.0.0-success?style=for-the-badge" alt="Version">
</p>

<p align="center">
  <strong>IoT Monitoring System for Smart Garden</strong><br>
  Real-time sensor monitoring with intelligent watering control
</p>

---

## 📋 Overview

Smart Garden IoT is a complete web-based monitoring and control system for automated plant watering. Features real-time sensor data visualization, intelligent operation modes, and remote device management via dashboard.

---

## ✨ Key Features

### 🎯 Real-time Monitoring
- Temperature & soil moisture tracking
- Live data charts with auto-refresh (3s interval)
- Historical data analysis
- Device status monitoring

### 🤖 Intelligent Control Modes
- **Basic Mode**: Simple threshold-based watering
- **Fuzzy AI Mode**: Adaptive watering based on temperature
- **Manual Mode**: Combined threshold + schedule control

### 📱 Device Management
- Auto-provisioning for new devices
- Remote configuration updates
- Multi-device support
- Manual pump control

### 📊 Dashboard Features
- Responsive web interface
- Activity logs with timestamps
- Connected sensors auto-detection
- Real-time status updates

---

## 🚀 Quick Start

### Prerequisites
- PHP 8.1+ with Composer
- MySQL Database
- Raspberry Pi Pico W
- DHT22 temperature sensor
- Capacitive soil moisture sensor

### Installation

1. **Clone Repository**
   ```bash
   git clone https://github.com/YOUR_USERNAME/Smart-Garden-IoT.git
   cd Smart-Garden-IoT
   ```

2. **Install Dependencies**
   ```bash
   composer install
   ```

3. **Setup Environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Configure Database**
   - Create MySQL database: `smart_garden`
   - Update database credentials in `.env` file

5. **Run Migrations**
   ```bash
   php artisan migrate
   ```

6. **Run Server**
   ```bash
   php artisan serve
   ```

7. **Access Dashboard**
   ```
   http://localhost:8000
   ```

---

## � Hardware Setup

### Required Components
- Raspberry Pi Pico W
- DHT22 Temperature Sensor
- Capacitive Soil Moisture Sensor
- 5V Relay Module
- Water Pump
- Jumper Wires

### Arduino Code
Upload the Arduino sketch from `arduino/pico_smart_gateway.ino` to your Pico W.

**Configuration:**
- Update WiFi credentials in the code
- Set server IP address
- Upload to Pico W via Arduino IDE

---

## � API Endpoints

Base URL: `/api/monitoring`

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/insert` | Send sensor data |
| GET | `/latest` | Get latest reading |
| GET | `/stats` | Get statistics |
| GET | `/history` | Get data history |
| POST | `/relay/toggle` | Control relay |

---

## 🛠️ Technology Stack

- **Backend**: Laravel 10.x
- **Frontend**: Vanilla JavaScript, Tailwind CSS
- **Database**: MySQL
- **Hardware**: Raspberry Pi Pico W
- **Sensors**: DHT22, Capacitive Soil Sensor
- **Charts**: Chart.js

---

## 📖 Documentation

- [CHANGELOG.md](./CHANGELOG.md) - Version history and release notes

---

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

---

## 📄 License

This project is open source and available under the MIT License.

---

## 👨‍💻 Author

**Smart Garden IoT Team**

---

## 🙏 Acknowledgments

- Laravel Framework
- Raspberry Pi Foundation
- Adafruit for sensor libraries
- Chart.js for visualization

---

**Version**: 1.0.0  
**Last Updated**: January 2026


---
