/*
 * =============================================================================
 * CONFIGURATION FILE FOR RASPBERRY PI PICO W - SMART GARDEN
 * =============================================================================
 * 
 * INSTRUCTIONS:
 * 1. Copy this file and rename it to "config.h" in the same directory
 * 2. Update all values marked with "CHANGE_ME" with your actual values
 * 3. Save the file
 * 4. Upload the code to your Pico W
 * 
 * =============================================================================
 */

#ifndef CONFIG_H
#define CONFIG_H

// ===========================
// WiFi CONFIGURATION
// ===========================
// Replace with your WiFi network name (SSID)
const char* WIFI_SSID = "YOUR_WIFI_SSID";        // CHANGE_ME: Your WiFi name

// Replace with your WiFi password
const char* WIFI_PASSWORD = "YOUR_WIFI_PASSWORD"; // CHANGE_ME: Your WiFi password

// ===========================
// SERVER CONFIGURATION
// ===========================
// Replace with your Laravel server IP address and port
// Find your IP with: ipconfig (Windows) or ifconfig (Mac/Linux)
// Default port is 8000 if running: php artisan serve --host=0.0.0.0 --port=8000
const char* SERVER_URL = "http://192.168.1.100:8000/api/monitoring/insert"; // CHANGE_ME: Your server IP

// ===========================
// DEVICE CONFIGURATION
// ===========================
// Unique identifier for this device (use meaningful names)
// Examples: "PICO_TOMATO_01", "PICO_GARDEN_A", "PICO_GREENHOUSE_01"
const char* DEVICE_ID = "PICO_GARDEN_01"; // CHANGE_ME: Your device name

// ===========================
// HARDWARE PIN CONFIGURATION
// ===========================
// Only change these if you connected sensors to different pins
const int SOIL_SENSOR_PIN = 26;  // ADC Pin (GP26 = ADC0) - Soil moisture sensor
const int DHT_PIN = 2;           // DHT22 Pin (GP2) - Temperature sensor
const int RELAY_PIN = 5;         // Relay Pin (GP5) - Water pump control
const int DHT_TYPE = 22;         // DHT sensor type (22 for DHT22)

// ===========================
// DEFAULT SENSOR CALIBRATION
// ===========================
// These values will be overridden by server configuration
// Only change if you want different initial values
const int DEFAULT_ADC_MIN = 4095;    // ADC value when sensor is dry (in air)
const int DEFAULT_ADC_MAX = 1500;    // ADC value when sensor is wet (in water)
const int DEFAULT_BATAS_KERING = 40; // Pump turns ON when moisture < 40%
const int DEFAULT_BATAS_BASAH = 70;  // Pump turns OFF when moisture >= 70%

// ===========================
// TIMING CONFIGURATION
// ===========================
const unsigned long SEND_INTERVAL = 10000; // Send data every 10 seconds (10000 ms)
const int WIFI_RETRY_ATTEMPTS = 20;        // Number of WiFi connection attempts

// ===========================
// NTP TIME SERVER (for Mode 3 - Schedule)
// ===========================
const char* NTP_SERVER = "pool.ntp.org";
const long GMT_OFFSET_SEC = 25200;  // UTC+7 for Indonesia (7 * 3600)
                                     // Change to your timezone offset in seconds
                                     // UTC+0: 0, UTC+1: 3600, UTC-5: -18000, etc.

#endif // CONFIG_H
