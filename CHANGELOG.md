# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2026-01-08

### Added
- 3 operation modes: Basic Threshold, Fuzzy Logic AI, Manual (Threshold + Schedule)
- Real-time sensor monitoring dashboard
- Auto-provisioning device management
- Manual relay/pump control
- Live data charts with Chart.js
- Activity logs with filtering
- Device management page
- RESTful API architecture
- Raspberry Pi Pico W Arduino code
- SQLite database for monitoring
- Responsive design with Tailwind CSS

### Changed
- Merged Mode 3 (Schedule) and Mode 4 (Manual) into unified Manual mode
- Simplified Manual mode by removing duration setting
- Updated to adaptive pump duration (controlled by threshold)
- Switched from React to vanilla JavaScript for better performance

### Removed
- Unused React components (SettingsMinimal, SmartGardenApp, CabaiMonitoringApp)
- Unused app.jsx file
- MySQL setup file (using SQLite)
- Test scripts and documentation
- Dead code and temporary files

### Fixed
- Server URL configuration for correct IP address
- Mode switching logic in Arduino code
- Thrashing issue in Manual mode

### Security
- No sensitive data in repository
- Proper .gitignore configuration
- Environment variables for credentials

---

## Release Notes

### v1.0.0 - Production Ready Release

This is the first stable release of Smart Garden IoT system. The system is production-ready with complete features for monitoring and controlling IoT devices.

**Key Features:**
- Complete sensor monitoring (Temperature, Soil Moisture)
- 3 intelligent operation modes
- Real-time dashboard with auto-refresh
- Raspberry Pi Pico W support
- Clean and maintainable codebase

**Technical Stack:**
- Backend: Laravel 10.x
- Frontend: Vanilla JavaScript + Tailwind CSS
- Database: SQLite
- Hardware: Raspberry Pi Pico W, DHT22, Soil Sensor

**Deployment:**
- Server: PHP 8.4+ with built-in server
- No complex setup required
- Auto-provisioning for new devices

---

For more information, see the [README.md](README.md) file.
