/**
 * ================================================================
 * AUTO-PROVISIONING SMART IRRIGATION SYSTEM
 * ================================================================
 * 
 * Fitur "Plug & Play":
 * 1. Alat baru otomatis dapat konfigurasi dari server
 * 2. Tidak perlu hardcode nilai kalibrasi di Arduino
 * 3. Setting bisa diubah dari dashboard tanpa upload ulang code
 * 4. Support multi-device dengan profil berbeda per alat
 * 
 * Hardware:
 * - ESP32 Dev Module
 * - Soil Moisture Sensor - GPIO 34 (Analog)
 * - Relay Module - GPIO 25
 * - LED Status - GPIO 2 (Built-in)
 * 
 * Author: PANDORA013
 * Repository: https://github.com/PANDORA013/Smart-Garden-IoT
 * Version: 2.0 (Auto-Provisioning)
 * ================================================================
 */

#include <WiFi.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>

// ===== IDENTITAS ALAT (HARUS UNIK!) =====
// Ganti dengan nama unik untuk setiap alat
const char* DEVICE_ID = "CABAI_01";  // Contoh: CABAI_01, CABAI_02, TOMAT_01, dll
const char* FIRMWARE_VERSION = "v2.0";

// ===== WiFi Configuration =====
const char* ssid = "NAMA_WIFI_ANDA";           // Ganti dengan SSID WiFi Anda
const char* password = "PASSWORD_WIFI_ANDA";   // Ganti dengan Password WiFi Anda

// ===== Server Configuration =====
const char* SERVER_IP = "192.168.1.70";        // Ganti dengan IP Laptop Anda
const int SERVER_PORT = 8000;

// Auto-generated URLs
String checkInUrl = "http://" + String(SERVER_IP) + ":" + String(SERVER_PORT) + "/api/device/check-in";
String dataUrl = "http://" + String(SERVER_IP) + ":" + String(SERVER_PORT) + "/api/monitoring/insert";

// ===== Hardware Configuration =====
#define SOIL_SENSOR_PIN 34      // Soil Moisture Analog Pin
#define RELAY_PIN 25            // Relay Control Pin
#define LED_PIN 2               // Built-in LED

// ===== Konfigurasi Dinamis (Diambil dari Server) =====
int sensorMin = 4095;           // Default: Sensor kering (akan di-update dari server)
int sensorMax = 1500;           // Default: Sensor basah (akan di-update dari server)
int batasSiram = 40;            // Default: Siram jika < 40% (akan di-update dari server)
int batasStop = 70;             // Default: Stop jika >= 70% (akan di-update dari server)
String plantType = "cabai";     // Jenis tanaman (akan di-update dari server)
bool deviceActive = true;       // Status alat (akan di-update dari server)

// ===== Timing Configuration =====
#define CONFIG_SYNC_INTERVAL 60000  // Sync config setiap 1 menit
#define DATA_SEND_INTERVAL 5000     // Kirim data setiap 5 detik
#define WIFI_RETRY_DELAY 5000       // Retry WiFi setiap 5 detik

// ===== Global Variables =====
unsigned long lastConfigSync = 0;
unsigned long lastDataSend = 0;
bool configSynced = false;
String deviceIP = "";

/**
 * Setup WiFi Connection
 */
void setupWiFi() {
  Serial.println();
  Serial.println("==========================================");
  Serial.println("  CONNECTING TO WIFI");
  Serial.println("==========================================");
  Serial.print("SSID: ");
  Serial.println(ssid);
  
  WiFi.mode(WIFI_STA);
  WiFi.begin(ssid, password);
  
  int attempts = 0;
  while (WiFi.status() != WL_CONNECTED && attempts < 20) {
    delay(500);
    Serial.print(".");
    digitalWrite(LED_PIN, !digitalRead(LED_PIN)); // Blink LED
    attempts++;
  }
  
  if (WiFi.status() == WL_CONNECTED) {
    digitalWrite(LED_PIN, HIGH);
    Serial.println("\n[OK] WiFi Connected!");
    deviceIP = WiFi.localIP().toString();
    Serial.print("IP Address: ");
    Serial.println(deviceIP);
    Serial.print("Signal Strength: ");
    Serial.print(WiFi.RSSI());
    Serial.println(" dBm");
  } else {
    digitalWrite(LED_PIN, LOW);
    Serial.println("\n[ERROR] WiFi Connection Failed!");
  }
  Serial.println("==========================================\n");
}

/**
 * FITUR UTAMA: AUTO-PROVISIONING
 * Check-in ke server untuk mendapatkan konfigurasi
 * Jika alat baru, server otomatis membuat profil dengan default cabai
 */
void syncConfiguration() {
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("[ERROR] WiFi not connected. Cannot sync config.");
    return;
  }
  
  HTTPClient http;
  String url = checkInUrl + "?device_id=" + String(DEVICE_ID) + "&firmware=" + String(FIRMWARE_VERSION);
  
  Serial.println("\n--- AUTO-PROVISIONING: Syncing Configuration ---");
  Serial.println("URL: " + url);
  
  http.begin(url);
  int httpCode = http.GET();
  
  if (httpCode == 200) {
    String response = http.getString();
    Serial.println("Response: " + response);
    
    // Parse JSON response
    StaticJsonDocument<512> doc;
    DeserializationError error = deserializeJson(doc, response);
    
    if (error) {
      Serial.print("[ERROR] JSON Parse Failed: ");
      Serial.println(error.c_str());
      http.end();
      return;
    }
    
    // Update konfigurasi dari server
    if (doc["success"] == true) {
      JsonObject config = doc["config"];
      
      sensorMin = config["sensor_min"];
      sensorMax = config["sensor_max"];
      batasSiram = config["batas_siram"];
      batasStop = config["batas_stop"];
      plantType = config["plant_type"].as<String>();
      deviceActive = config["is_active"];
      
      bool isNewDevice = doc["is_new_device"];
      
      Serial.println("\n[OK] Configuration Updated Successfully!");
      Serial.println("========================================");
      Serial.println("Device ID: " + String(DEVICE_ID));
      Serial.println("Plant Type: " + plantType);
      Serial.println("Sensor Min (Dry): " + String(sensorMin));
      Serial.println("Sensor Max (Wet): " + String(sensorMax));
      Serial.println("Batas Siram: " + String(batasSiram) + "%");
      Serial.println("Batas Stop: " + String(batasStop) + "%");
      Serial.println("Device Active: " + String(deviceActive ? "YES" : "NO"));
      
      if (isNewDevice) {
        Serial.println("\n✨ NEW DEVICE DETECTED!");
        Serial.println("✅ Auto-configured with default Cabai settings");
      }
      
      Serial.println("========================================\n");
      
      configSynced = true;
      digitalWrite(LED_PIN, HIGH);
    }
  } else {
    Serial.print("[ERROR] HTTP Error: ");
    Serial.println(httpCode);
    Serial.println("Server may be offline or IP address incorrect");
    digitalWrite(LED_PIN, LOW);
  }
  
  http.end();
}

/**
 * Read Soil Moisture Sensor
 * Menggunakan kalibrasi dinamis dari server
 */
float readSoilMoisture() {
  int rawValue = analogRead(SOIL_SENSOR_PIN);
  
  // Mapping ADC value ke percentage menggunakan nilai dari server
  float moisture = map(rawValue, sensorMin, sensorMax, 0, 100);
  
  // Constrain to 0-100%
  if (moisture < 0) moisture = 0;
  if (moisture > 100) moisture = 100;
  
  return moisture;
}

/**
 * Auto Control Relay based on threshold dari server
 */
bool autoControlRelay(float soilMoisture) {
  static bool relayState = false;
  
  // Jika device tidak aktif, matikan relay
  if (!deviceActive) {
    digitalWrite(RELAY_PIN, LOW);
    return false;
  }
  
  // Logika pompa: ON jika < batasSiram, OFF jika >= batasStop
  if (soilMoisture < batasSiram && !relayState) {
    digitalWrite(RELAY_PIN, HIGH);
    relayState = true;
    Serial.println("[AUTO] 💧 Pompa ON: Kelembaban < " + String(batasSiram) + "%");
  } 
  else if (soilMoisture >= batasStop && relayState) {
    digitalWrite(RELAY_PIN, LOW);
    relayState = false;
    Serial.println("[AUTO] ⛔ Pompa OFF: Kelembaban >= " + String(batasStop) + "%");
  }
  
  return relayState;
}

/**
 * Send Data to Laravel API
 */
void sendDataToAPI(float soilMoisture, bool relayStatus) {
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("[ERROR] WiFi not connected. Cannot send data.");
    return;
  }
  
  HTTPClient http;
  http.begin(dataUrl);
  http.addHeader("Content-Type", "application/json");
  
  // Create JSON payload
  StaticJsonDocument<256> doc;
  doc["soil_moisture"] = soilMoisture;
  doc["status_pompa"] = relayStatus ? "Hidup" : "Mati";
  doc["relay_status"] = relayStatus;
  doc["device_name"] = DEVICE_ID;
  doc["ip_address"] = deviceIP;
  
  String jsonString;
  serializeJson(doc, jsonString);
  
  Serial.println("\n--- Sending Data to Server ---");
  Serial.println("Payload: " + jsonString);
  
  int httpResponseCode = http.POST(jsonString);
  
  if (httpResponseCode > 0) {
    if (httpResponseCode == 201) {
      Serial.println("[OK] Data sent successfully! (201 Created)");
      digitalWrite(LED_PIN, HIGH);
    } else {
      Serial.print("[WARN] Server returned: ");
      Serial.println(httpResponseCode);
    }
  } else {
    Serial.print("[ERROR] HTTP Error: ");
    Serial.println(httpResponseCode);
    digitalWrite(LED_PIN, LOW);
  }
  
  http.end();
  Serial.println("------------------------------\n");
}

/**
 * Setup Function
 */
void setup() {
  // Initialize Serial
  Serial.begin(115200);
  delay(2000);
  
  Serial.println("\n\n");
  Serial.println("================================================");
  Serial.println("  AUTO-PROVISIONING SMART IRRIGATION");
  Serial.println("  Device ID: " + String(DEVICE_ID));
  Serial.println("  Firmware: " + String(FIRMWARE_VERSION));
  Serial.println("  GitHub: PANDORA013/Smart-Garden-IoT");
  Serial.println("================================================\n");
  
  // Initialize Pins
  pinMode(RELAY_PIN, OUTPUT);
  pinMode(LED_PIN, OUTPUT);
  digitalWrite(RELAY_PIN, LOW);  // Relay OFF
  digitalWrite(LED_PIN, LOW);    // LED OFF
  
  // Initialize Soil Sensor
  pinMode(SOIL_SENSOR_PIN, INPUT);
  Serial.println("[OK] Soil Moisture Sensor initialized");
  
  // Connect to WiFi
  setupWiFi();
  
  // PENTING: Sync konfigurasi dari server sebelum mulai operasi
  if (WiFi.status() == WL_CONNECTED) {
    syncConfiguration();
  }
  
  Serial.println("Setup Complete! Starting main loop...\n");
}

/**
 * Main Loop
 */
void loop() {
  unsigned long currentTime = millis();
  
  // Check WiFi connection
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("[WARN] WiFi Disconnected. Reconnecting...");
    setupWiFi();
    delay(WIFI_RETRY_DELAY);
    return;
  }
  
  // Sync configuration setiap CONFIG_SYNC_INTERVAL (1 menit)
  // Ini penting agar perubahan setting dari dashboard langsung diterapkan
  if (currentTime - lastConfigSync >= CONFIG_SYNC_INTERVAL || !configSynced) {
    lastConfigSync = currentTime;
    syncConfiguration();
  }
  
  // Read sensors dan send data setiap DATA_SEND_INTERVAL (5 detik)
  if (currentTime - lastDataSend >= DATA_SEND_INTERVAL) {
    lastDataSend = currentTime;
    
    // Jika konfigurasi belum tersync, skip operasi
    if (!configSynced) {
      Serial.println("[WARN] Configuration not synced yet. Skipping operation...");
      return;
    }
    
    // Read Soil Moisture
    float soilMoisture = readSoilMoisture();
    
    // Auto control relay
    bool relayStatus = autoControlRelay(soilMoisture);
    
    // Print sensor readings
    Serial.println("========== SENSOR READINGS ==========");
    Serial.print("Soil Moisture: ");
    Serial.print(soilMoisture);
    Serial.println(" %");
    
    Serial.print("Relay Status: ");
    Serial.println(relayStatus ? "ON (Pompa Menyala)" : "OFF (Pompa Mati)");
    
    Serial.print("Plant Type: ");
    Serial.println(plantType);
    Serial.println("=====================================\n");
    
    // Send data to server
    sendDataToAPI(soilMoisture, relayStatus);
  }
  
  // Small delay to prevent watchdog issues
  delay(100);
}
