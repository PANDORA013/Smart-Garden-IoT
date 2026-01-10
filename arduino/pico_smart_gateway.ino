// =============================================================================
// RASPBERRY PI PICO W - SMART GARDEN IoT GATEWAY
// =============================================================================
// Author: Smart Garden IoT Project
// Date: 10 Januari 2026
// Version: 2.0
// 
// FITUR LENGKAP:
// ✅ WiFi Connection dengan Auto-Reconnect
// ✅ DHT22 Temperature & Humidity Sensor
// ✅ Capacitive Soil Moisture Sensor (ADC)
// ✅ Relay Control untuk Pompa Air
// ✅ HTTP POST ke Laravel Server (2-Way Communication)
// ✅ Auto-Provisioning Device Settings
// ✅ 3 Mode Kontrol (Basic, Advanced, Schedule)
// ✅ NTP Time Sync untuk Schedule Mode
// ✅ Serial Monitoring dengan Emoji
// 
// HARDWARE PINOUT:
// - GPIO 26 (ADC0) → Capacitive Soil Moisture Sensor
// - GPIO 2        → DHT22 Data Pin
// - GPIO 5        → Relay/Pompa Air
// - VCC/GND       → Power Supply 3.3V
// 
// NETWORK:
// - WiFi SSID: CCTV_UISI
// - Password: 08121191
// - Server: http://10.134.42.169:8000
// =============================================================================

#include <WiFi.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>
#include <DHT.h>
#include <NTPClient.h>
#include <WiFiUdp.h>

// =============================================================================
// KONFIGURASI JARINGAN - WiFi CCTV_UISI
// =============================================================================
const char* WIFI_SSID = "CCTV_UISI";
const char* WIFI_PASSWORD = "08121191";
const char* SERVER_URL = "http://10.134.42.169:8000/api/monitoring/insert";
const char* DEVICE_ID = "PICO_CABAI_01";

// Backup WiFi (jika CCTV_UISI tidak tersedia, uncomment untuk pakai Bocil)
// const char* WIFI_SSID = "Bocil";
// const char* WIFI_PASSWORD = "kesayanganku";
// const char* SERVER_URL = "http://192.168.18.35:8000/api/monitoring/insert";

// =============================================================================
// KONFIGURASI HARDWARE
// =============================================================================
#define DHT_PIN 2              // GPIO 2 untuk DHT22
#define DHT_TYPE DHT22
#define SOIL_PIN 26            // GPIO 26 (ADC0) untuk Soil Sensor
#define RELAY_PIN 5            // GPIO 5 untuk Relay/Pompa

DHT dht(DHT_PIN, DHT_TYPE);

// =============================================================================
// KONFIGURASI SENSOR & KONTROL
// =============================================================================
// Kalibrasi ADC Soil Sensor (0-4095 untuk Pico W ADC 12-bit)
int ADC_MIN = 4095;      // ADC saat tanah KERING (tidak ada air)
int ADC_MAX = 1500;      // ADC saat tanah BASAH (banyak air)

// Threshold Kelembaban (%)
int BATAS_KERING = 40;   // Jika < 40% → Pompa ON
int BATAS_BASAH = 70;    // Jika > 70% → Pompa OFF

// Mode Kontrol (dari server)
int MODE = 1;            // 1=Basic, 2=Advanced, 3=Schedule, 4=Manual
String JAM_PAGI = "07:00";
String JAM_SORE = "17:00";
int DURASI_SIRAM = 5;    // menit

// =============================================================================
// VARIABLE GLOBAL
// =============================================================================
unsigned long lastSendTime = 0;
const unsigned long SEND_INTERVAL = 10000;  // Kirim data tiap 10 detik
bool pumpStatus = false;
float temperature = 0;
float humidity = 0;
int soilMoisture = 0;
int rawADC = 0;

// NTP Client untuk Schedule Mode
WiFiUDP ntpUDP;
NTPClient timeClient(ntpUDP, "pool.ntp.org", 25200, 60000); // GMT+7 (WIB)

// =============================================================================
// SETUP - Inisialisasi Hardware & WiFi
// =============================================================================
void setup() {
  Serial.begin(115200);
  delay(1000);
  
  Serial.println("\n\n");
  Serial.println("═══════════════════════════════════════════════════════════");
  Serial.println("   🌱 RASPBERRY PI PICO W - SMART GARDEN IoT GATEWAY");
  Serial.println("═══════════════════════════════════════════════════════════");
  Serial.println();
  
  // Init Hardware
  Serial.println("🔧 Initializing Hardware...");
  pinMode(RELAY_PIN, OUTPUT);
  digitalWrite(RELAY_PIN, LOW);  // Pompa OFF saat start
  
  dht.begin();
  analogReadResolution(12);  // Pico W ADC 12-bit (0-4095)
  
  Serial.println("   ✅ DHT22 Sensor initialized");
  Serial.println("   ✅ Soil Moisture Sensor initialized (ADC)");
  Serial.println("   ✅ Relay initialized (Pump OFF)");
  Serial.println();
  
  // Connect WiFi
  connectWiFi();
  
  // Init NTP Client
  timeClient.begin();
  timeClient.update();
  
  Serial.println("═══════════════════════════════════════════════════════════");
  Serial.println("✅ SYSTEM READY!");
  Serial.println("═══════════════════════════════════════════════════════════\n");
}

// =============================================================================
// MAIN LOOP
// =============================================================================
void loop() {
  // Cek WiFi connection
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("⚠️  WiFi disconnected! Reconnecting...");
    connectWiFi();
  }
  
  // Update NTP time untuk Schedule Mode
  if (MODE == 3) {
    timeClient.update();
  }
  
  // Kirim data setiap SEND_INTERVAL
  if (millis() - lastSendTime >= SEND_INTERVAL) {
    lastSendTime = millis();
    
    // Baca semua sensor
    readSensors();
    
    // Kontrol pompa berdasarkan mode
    controlPump();
    
    // Kirim data ke server
    sendDataToServer();
  }
  
  delay(100);  // Small delay untuk stabilitas
}

// =============================================================================
// FUNGSI: Connect ke WiFi
// =============================================================================
void connectWiFi() {
  Serial.print("📡 Connecting to WiFi: ");
  Serial.println(WIFI_SSID);
  Serial.print("   Password: ");
  Serial.println(WIFI_PASSWORD);
  Serial.println();
  
  WiFi.begin(WIFI_SSID, WIFI_PASSWORD);
  
  int attempts = 0;
  while (WiFi.status() != WL_CONNECTED && attempts < 20) {
    delay(500);
    Serial.print("⏳ Attempt ");
    Serial.print(attempts + 1);
    Serial.println("/20...");
    attempts++;
  }
  
  if (WiFi.status() == WL_CONNECTED) {
    Serial.println();
    Serial.println("✅ WiFi Connected! 📡");
    Serial.print("   IP Address: ");
    Serial.println(WiFi.localIP());
    Serial.print("   Signal Strength: ");
    Serial.print(WiFi.RSSI());
    Serial.println(" dBm");
    Serial.println();
  } else {
    Serial.println();
    Serial.println("❌ WiFi Connection FAILED!");
    Serial.println("   Please check:");
    Serial.println("   - WiFi SSID & Password");
    Serial.println("   - WiFi signal strength");
    Serial.println("   - Router is powered on");
    Serial.println();
    Serial.println("⏳ Retrying in 5 seconds...");
    delay(5000);
    connectWiFi();  // Retry
  }
}

// =============================================================================
// FUNGSI: Baca Semua Sensor
// =============================================================================
void readSensors() {
  // Baca DHT22 (Temperature & Humidity)
  temperature = dht.readTemperature();
  humidity = dht.readHumidity();
  
  // Fallback jika DHT22 error
  if (isnan(temperature) || isnan(humidity)) {
    Serial.println("⚠️  DHT22 ERROR: Timeout reading sensor!");
    temperature = 28.0;  // Default fallback
    humidity = 60.0;
    Serial.println("   Using fallback values: 28°C, 60%");
  }
  
  // Baca Soil Moisture Sensor (ADC)
  delay(100);  // Stabilisasi ADC
  rawADC = analogRead(SOIL_PIN);
  
  // Konversi ADC ke Persentase (0-100%)
  // Rumus: map(value, fromLow, fromHigh, toLow, toHigh)
  soilMoisture = map(rawADC, ADC_MIN, ADC_MAX, 0, 100);
  soilMoisture = constrain(soilMoisture, 0, 100);  // Batasi 0-100%
  
  // Print sensor data
  Serial.println("📊 SENSOR DATA:");
  Serial.print("   🌡️  Temperature: ");
  Serial.print(temperature);
  Serial.println("°C");
  
  Serial.print("   💧 Humidity: ");
  Serial.print(humidity);
  Serial.println("%");
  
  Serial.print("   🌱 Soil Moisture: ");
  Serial.print(soilMoisture);
  Serial.print("% (ADC: ");
  Serial.print(rawADC);
  Serial.println(")");
  
  Serial.print("   💦 Pump Status: ");
  Serial.println(pumpStatus ? "ON 🟢" : "OFF 🔴");
  Serial.println();
}

// =============================================================================
// FUNGSI: Kontrol Pompa Berdasarkan Mode
// =============================================================================
void controlPump() {
  bool shouldPumpOn = false;
  
  switch(MODE) {
    case 1: // BASIC MODE - Threshold sederhana
      if (soilMoisture < BATAS_KERING && !pumpStatus) {
        shouldPumpOn = true;
        Serial.println("🔄 MODE 1 (Basic): Tanah KERING → Pompa ON");
      } else if (soilMoisture > BATAS_BASAH && pumpStatus) {
        shouldPumpOn = false;
        Serial.println("🔄 MODE 1 (Basic): Tanah BASAH → Pompa OFF");
      } else {
        shouldPumpOn = pumpStatus;  // Maintain current state
      }
      break;
      
    case 2: // ADVANCED MODE - Hysteresis untuk stabilitas
      if (soilMoisture < BATAS_KERING - 5 && !pumpStatus) {
        shouldPumpOn = true;
        Serial.println("🔄 MODE 2 (Advanced): Kelembaban rendah → Pompa ON");
      } else if (soilMoisture > BATAS_BASAH + 5 && pumpStatus) {
        shouldPumpOn = false;
        Serial.println("🔄 MODE 2 (Advanced): Kelembaban cukup → Pompa OFF");
      } else {
        shouldPumpOn = pumpStatus;  // Hysteresis zone
      }
      break;
      
    case 3: // SCHEDULE MODE - Siram pada jam tertentu
      {
        timeClient.update();
        String currentTime = timeClient.getFormattedTime().substring(0, 5);  // HH:MM
        
        if (currentTime == JAM_PAGI || currentTime == JAM_SORE) {
          shouldPumpOn = true;
          Serial.print("🔄 MODE 3 (Schedule): Waktu siram (");
          Serial.print(currentTime);
          Serial.println(") → Pompa ON");
        } else {
          shouldPumpOn = false;
        }
      }
      break;
      
    case 4: // MANUAL MODE - Kontrol dari dashboard
      shouldPumpOn = pumpStatus;  // No auto control
      Serial.println("🔄 MODE 4 (Manual): Kontrol dari Dashboard");
      break;
      
    default:
      shouldPumpOn = pumpStatus;
      break;
  }
  
  // Update pump status
  if (shouldPumpOn != pumpStatus) {
    pumpStatus = shouldPumpOn;
    digitalWrite(RELAY_PIN, pumpStatus ? HIGH : LOW);
    
    Serial.print("⚡ RELAY ");
    Serial.println(pumpStatus ? "ON ✅" : "OFF ❌");
    Serial.println();
  }
}

// =============================================================================
// FUNGSI: Kirim Data ke Laravel Server
// =============================================================================
void sendDataToServer() {
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("❌ Cannot send data: WiFi not connected!");
    return;
  }
  
  HTTPClient http;
  http.begin(SERVER_URL);
  http.addHeader("Content-Type", "application/json");
  http.setTimeout(5000);  // 5 second timeout
  
  // Buat JSON payload
  StaticJsonDocument<512> doc;
  doc["device_id"] = DEVICE_ID;
  doc["temperature"] = temperature;
  doc["humidity"] = humidity;
  doc["soil_moisture"] = soilMoisture;
  doc["raw_adc"] = rawADC;
  doc["relay_status"] = pumpStatus;
  doc["ip_address"] = WiFi.localIP().toString();
  
  String jsonPayload;
  serializeJson(doc, jsonPayload);
  
  Serial.println("📡 Sending data to server...");
  Serial.print("   URL: ");
  Serial.println(SERVER_URL);
  Serial.print("   Payload: ");
  Serial.println(jsonPayload);
  Serial.println();
  
  // Kirim POST request
  int httpCode = http.POST(jsonPayload);
  
  if (httpCode > 0) {
    Serial.print("✅ Server Response: ");
    Serial.println(httpCode);
    
    if (httpCode == 201 || httpCode == 200) {
      String response = http.getString();
      Serial.println("📥 Data berhasil dikirim!");
      
      // Parse response untuk ambil config dari server
      StaticJsonDocument<512> responseDoc;
      DeserializationError error = deserializeJson(responseDoc, response);
      
      if (!error && responseDoc.containsKey("config")) {
        // Update konfigurasi dari server (2-Way Communication)
        JsonObject config = responseDoc["config"];
        
        if (config.containsKey("mode")) {
          MODE = config["mode"];
        }
        if (config.containsKey("adc_min")) {
          ADC_MIN = config["adc_min"];
        }
        if (config.containsKey("adc_max")) {
          ADC_MAX = config["adc_max"];
        }
        if (config.containsKey("batas_kering")) {
          BATAS_KERING = config["batas_kering"];
        }
        if (config.containsKey("batas_basah")) {
          BATAS_BASAH = config["batas_basah"];
        }
        if (config.containsKey("jam_pagi")) {
          JAM_PAGI = config["jam_pagi"].as<String>();
        }
        if (config.containsKey("jam_sore")) {
          JAM_SORE = config["jam_sore"].as<String>();
        }
        if (config.containsKey("durasi_siram")) {
          DURASI_SIRAM = config["durasi_siram"];
        }
        
        Serial.println("🔧 Config updated from server:");
        Serial.print("   Mode: ");
        Serial.println(MODE);
        Serial.print("   ADC Range: ");
        Serial.print(ADC_MIN);
        Serial.print(" - ");
        Serial.println(ADC_MAX);
        Serial.print("   Threshold: ");
        Serial.print(BATAS_KERING);
        Serial.print("% - ");
        Serial.print(BATAS_BASAH);
        Serial.println("%");
      }
    }
  } else {
    Serial.print("❌ Connection failed! HTTP Error: ");
    Serial.println(httpCode);
    Serial.println("   Possible causes:");
    Serial.println("   - Server not running");
    Serial.println("   - Wrong IP address");
    Serial.println("   - Firewall blocking port 8000");
    Serial.println("   - Network issue");
  }
  
  http.end();
  Serial.println("═══════════════════════════════════════════════════════════\n");
}

// =============================================================================
// END OF CODE
// =============================================================================
