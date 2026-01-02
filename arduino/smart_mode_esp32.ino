/**
 * ================================================================
 * SMART GARDEN IoT - 3 MODE CERDAS (ESP32)
 * ================================================================
 * 
 * MODE 1: BASIC THRESHOLD
 *   - Siram jika kelembaban < batas_siram
 *   - Stop jika kelembaban >= batas_stop
 * 
 * MODE 2: FUZZY LOGIC (AI)
 *   - Otomatis menghitung durasi siram
 *   - Berdasarkan suhu & kelembaban
 *   - Logika: Kering + Panas = Siram Lama
 *            Kering + Dingin = Siram Sebentar
 * 
 * MODE 3: SCHEDULE (Timer)
 *   - Siram otomatis pada jam yang ditentukan
 *   - Pagi (default 07:00) dan Sore (default 17:00)
 *   - Durasi sesuai setting dari web
 * 
 * Author: PANDORA013
 * Repository: https://github.com/PANDORA013/Smart-Garden-IoT
 * Version: v2.1 - Smart Mode Edition
 * ================================================================
 */

#include <WiFi.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>
#include <DHT.h>
#include <time.h>

// ===== KONFIGURASI WiFi & Server =====
const char* DEVICE_ID = "CABAI_01";              // 🔴 GANTI: ID unik per device
const char* ssid = "YOUR_WIFI_SSID";             // 🔴 GANTI: WiFi name
const char* password = "YOUR_WIFI_PASSWORD";     // 🔴 GANTI: WiFi password
const char* SERVER_IP = "192.168.1.70";          // 🔴 GANTI: IP Laptop/Server
const int SERVER_PORT = 8000;

// ===== Hardware Configuration =====
#define DHTPIN 4                // DHT22 Data Pin
#define DHTTYPE DHT22           // DHT22 (AM2302)
#define SOIL_SENSOR_PIN 34      // Soil Moisture Analog Pin
#define RELAY_PIN 25            // Relay Control Pin
#define LED_PIN 2               // Built-in LED

// ===== Sensor Objects =====
DHT dht(DHTPIN, DHTTYPE);

// ===== VARIABEL KONFIGURASI (Diupdate dari Server) =====
int modeOperasi = 1;            // 1=Basic, 2=Fuzzy, 3=Schedule (default Mode 1)
int sensorMin = 4095;           // ADC dry value
int sensorMax = 1500;           // ADC wet value
int batasSiram = 40;            // Threshold ON (%)
int batasStop = 70;             // Threshold OFF (%)
String jamPagi = "07:00";       // Schedule pagi (Mode 3)
String jamSore = "17:00";       // Schedule sore (Mode 3)
int durasiSiram = 5;            // Durasi dalam detik (Mode 3)
String plantType = "cabai";

// ===== Status & Timing =====
bool isPumpOn = false;
unsigned long lastSync = 0;
unsigned long lastSend = 0;
unsigned long pumpStartTime = 0;
bool scheduleRunToday_Pagi = false;
bool scheduleRunToday_Sore = false;

// ===== Interval =====
const unsigned long CONFIG_SYNC_INTERVAL = 60000;  // Sync config setiap 1 menit
const unsigned long DATA_SEND_INTERVAL = 5000;     // Kirim data setiap 5 detik

// ===== NTP Time Server =====
const char* ntpServer = "pool.ntp.org";
const long gmtOffset_sec = 25200;  // GMT+7 (Indonesia: 7 * 3600)
const int daylightOffset_sec = 0;

// ===== SETUP =====
void setup() {
  Serial.begin(115200);
  Serial.println("\n\n");
  Serial.println("========================================");
  Serial.println("  SMART GARDEN IoT - 3 MODE CERDAS");
  Serial.println("========================================");
  Serial.print("Device ID: ");
  Serial.println(DEVICE_ID);
  
  // Setup Hardware
  pinMode(RELAY_PIN, OUTPUT);
  pinMode(LED_PIN, OUTPUT);
  digitalWrite(RELAY_PIN, LOW);
  digitalWrite(LED_PIN, LOW);
  
  // Init sensors
  dht.begin();
  
  // Connect WiFi
  connectWiFi();
  
  // Sync time with NTP
  configTime(gmtOffset_sec, daylightOffset_sec, ntpServer);
  Serial.println("⏰ NTP Time synced");
  
  // Initial config sync
  syncConfiguration();
  
  Serial.println("✅ Setup Complete!");
  Serial.println("========================================\n");
}

// ===== MAIN LOOP =====
void loop() {
  // 1. Sync Config dari Server (Setiap 1 menit)
  if (millis() - lastSync > CONFIG_SYNC_INTERVAL) {
    syncConfiguration();
    lastSync = millis();
  }
  
  // 2. Baca Sensor
  float temperature = dht.readTemperature();
  float humidity = dht.readHumidity();
  float soilMoisture = readSoilMoisture();
  
  // 3. EKSEKUSI MODE YANG AKTIF
  switch (modeOperasi) {
    case 1:
      runModeBasic(soilMoisture);
      break;
    case 2:
      runModeFuzzy(soilMoisture, temperature);
      break;
    case 3:
      runModeSchedule();
      break;
    default:
      Serial.println("⚠️ Mode tidak valid, fallback ke Mode 1");
      runModeBasic(soilMoisture);
  }
  
  // 4. Kirim Data ke Server (Setiap 5 detik)
  if (millis() - lastSend > DATA_SEND_INTERVAL) {
    sendDataToAPI(temperature, humidity, soilMoisture);
    lastSend = millis();
  }
  
  // LED indicator (blink = active)
  digitalWrite(LED_PIN, (millis() / 1000) % 2);
  
  delay(1000);
}

// ===== CONNECT WiFi =====
void connectWiFi() {
  Serial.print("📡 Connecting to WiFi");
  WiFi.begin(ssid, password);
  
  int attempts = 0;
  while (WiFi.status() != WL_CONNECTED && attempts < 20) {
    delay(500);
    Serial.print(".");
    attempts++;
  }
  
  if (WiFi.status() == WL_CONNECTED) {
    Serial.println("\n✅ WiFi Connected!");
    Serial.print("   IP Address: ");
    Serial.println(WiFi.localIP());
  } else {
    Serial.println("\n❌ WiFi Connection Failed!");
  }
}

// ===== SYNC CONFIGURATION DARI SERVER =====
void syncConfiguration() {
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("❌ No WiFi - Skip sync");
    return;
  }
  
  HTTPClient http;
  String url = "http://" + String(SERVER_IP) + ":" + String(SERVER_PORT) + 
               "/api/device/check-in?device_id=" + String(DEVICE_ID) + 
               "&firmware=v2.1";
  
  Serial.println("\n🔄 Syncing configuration...");
  http.begin(url);
  int httpCode = http.GET();
  
  if (httpCode == 200) {
    String payload = http.getString();
    
    // Parse JSON
    StaticJsonDocument<1024> doc;
    DeserializationError error = deserializeJson(doc, payload);
    
    if (!error) {
      JsonObject config = doc["config"];
      
      // Update variabel global
      modeOperasi = config["mode"] | 1;
      sensorMin = config["sensor_min"] | 4095;
      sensorMax = config["sensor_max"] | 1500;
      batasSiram = config["batas_siram"] | 40;
      batasStop = config["batas_stop"] | 70;
      jamPagi = config["jam_pagi"] | "07:00:00";
      jamSore = config["jam_sore"] | "17:00:00";
      durasiSiram = config["durasi_siram"] | 5;
      plantType = config["plant_type"] | "cabai";
      
      // Parse jam (remove seconds)
      jamPagi = jamPagi.substring(0, 5);
      jamSore = jamSore.substring(0, 5);
      
      Serial.println("✅ Config updated!");
      Serial.print("   Mode: ");
      Serial.print(modeOperasi);
      Serial.print(" (");
      if (modeOperasi == 1) Serial.print("Basic");
      else if (modeOperasi == 2) Serial.print("Fuzzy Logic");
      else Serial.print("Schedule");
      Serial.println(")");
      Serial.print("   Plant: ");
      Serial.println(plantType);
      
      if (modeOperasi == 1) {
        Serial.print("   Threshold: ");
        Serial.print(batasSiram);
        Serial.print("% - ");
        Serial.print(batasStop);
        Serial.println("%");
      } else if (modeOperasi == 3) {
        Serial.print("   Schedule: ");
        Serial.print(jamPagi);
        Serial.print(" & ");
        Serial.println(jamSore);
        Serial.print("   Duration: ");
        Serial.print(durasiSiram);
        Serial.println(" sec");
      }
    }
  } else {
    Serial.print("❌ HTTP Error: ");
    Serial.println(httpCode);
  }
  
  http.end();
}

// ===== BACA SOIL MOISTURE =====
float readSoilMoisture() {
  int rawValue = analogRead(SOIL_SENSOR_PIN);
  
  // Map ADC value ke persentase (0-100%)
  float percentage = map(rawValue, sensorMin, sensorMax, 0, 100);
  percentage = constrain(percentage, 0, 100);
  
  return percentage;
}

// ===== MODE 1: BASIC THRESHOLD =====
void runModeBasic(float soilMoisture) {
  if (soilMoisture < batasSiram && !isPumpOn) {
    // Tanah kering - Nyalakan pompa
    digitalWrite(RELAY_PIN, HIGH);
    isPumpOn = true;
    Serial.println("💧 [MODE 1] Pompa ON - Tanah kering (" + String(soilMoisture, 1) + "%)");
  } 
  else if (soilMoisture >= batasStop && isPumpOn) {
    // Tanah cukup basah - Matikan pompa
    digitalWrite(RELAY_PIN, LOW);
    isPumpOn = false;
    Serial.println("🛑 [MODE 1] Pompa OFF - Tanah cukup basah (" + String(soilMoisture, 1) + "%)");
  }
}

// ===== MODE 2: FUZZY LOGIC (AI) =====
void runModeFuzzy(float soilMoisture, float temperature) {
  // Fuzzy Logic Rules:
  // 1. Kering (< 40%) + Panas (> 30°C) = Siram Lama (8 detik)
  // 2. Kering (< 40%) + Sedang (25-30°C) = Siram Sedang (5 detik)
  // 3. Kering (< 40%) + Dingin (< 25°C) = Siram Sebentar (3 detik)
  // 4. Normal (40-70%) = Tidak siram
  // 5. Basah (> 70%) = Tidak siram (safety)
  
  // Cek apakah pompa sedang berjalan (timed operation)
  if (isPumpOn) {
    if (millis() - pumpStartTime > (durasiSiram * 1000)) {
      digitalWrite(RELAY_PIN, LOW);
      isPumpOn = false;
      Serial.println("🛑 [MODE 2] Pompa OFF - Durasi selesai");
    }
    return; // Jangan evaluasi ulang jika masih jalan
  }
  
  // Evaluasi fuzzy rules
  if (soilMoisture < 40) {
    int fuzzyDuration = 5; // Default
    
    if (temperature > 30) {
      fuzzyDuration = 8; // Panas - siram lama
      Serial.println("🔥 [MODE 2] Kondisi: KERING + PANAS");
    } else if (temperature >= 25) {
      fuzzyDuration = 5; // Sedang
      Serial.println("🌤️ [MODE 2] Kondisi: KERING + SEDANG");
    } else {
      fuzzyDuration = 3; // Dingin - siram sebentar
      Serial.println("❄️ [MODE 2] Kondisi: KERING + DINGIN");
    }
    
    // Nyalakan pompa dengan durasi fuzzy
    digitalWrite(RELAY_PIN, HIGH);
    isPumpOn = true;
    pumpStartTime = millis();
    durasiSiram = fuzzyDuration;
    
    Serial.print("💧 [MODE 2] Pompa ON - Durasi: ");
    Serial.print(fuzzyDuration);
    Serial.print(" detik (Soil: ");
    Serial.print(soilMoisture, 1);
    Serial.print("%, Temp: ");
    Serial.print(temperature, 1);
    Serial.println("°C)");
  }
}

// ===== MODE 3: SCHEDULE (Timer) =====
void runModeSchedule() {
  // Get current time
  struct tm timeinfo;
  if (!getLocalTime(&timeinfo)) {
    Serial.println("⚠️ [MODE 3] Failed to obtain time");
    return;
  }
  
  char currentTime[6];
  strftime(currentTime, sizeof(currentTime), "%H:%M", &timeinfo);
  String currentTimeStr = String(currentTime);
  
  // Reset flag setiap hari (pada jam 00:00)
  if (currentTimeStr == "00:00") {
    scheduleRunToday_Pagi = false;
    scheduleRunToday_Sore = false;
    Serial.println("🔄 [MODE 3] Reset schedule flag");
  }
  
  // Cek schedule pagi
  if (currentTimeStr == jamPagi && !scheduleRunToday_Pagi && !isPumpOn) {
    runScheduledWatering("PAGI");
    scheduleRunToday_Pagi = true;
  }
  
  // Cek schedule sore
  if (currentTimeStr == jamSore && !scheduleRunToday_Sore && !isPumpOn) {
    runScheduledWatering("SORE");
    scheduleRunToday_Sore = true;
  }
  
  // Matikan pompa setelah durasi habis
  if (isPumpOn && millis() - pumpStartTime > (durasiSiram * 1000)) {
    digitalWrite(RELAY_PIN, LOW);
    isPumpOn = false;
    Serial.println("🛑 [MODE 3] Pompa OFF - Jadwal selesai");
  }
}

// ===== JALANKAN PENYIRAMAN TERJADWAL =====
void runScheduledWatering(String waktu) {
  digitalWrite(RELAY_PIN, HIGH);
  isPumpOn = true;
  pumpStartTime = millis();
  
  Serial.print("💧 [MODE 3] Pompa ON - Jadwal ");
  Serial.print(waktu);
  Serial.print(" (");
  Serial.print(durasiSiram);
  Serial.println(" detik)");
}

// ===== KIRIM DATA KE SERVER =====
void sendDataToAPI(float temperature, float humidity, float soilMoisture) {
  if (WiFi.status() != WL_CONNECTED) return;
  
  HTTPClient http;
  String url = "http://" + String(SERVER_IP) + ":" + String(SERVER_PORT) + "/api/monitoring/insert";
  
  http.begin(url);
  http.addHeader("Content-Type", "application/json");
  
  // JSON payload
  StaticJsonDocument<512> doc;
  doc["device_name"] = DEVICE_ID;
  doc["temperature"] = round(temperature * 10) / 10.0;
  doc["humidity"] = round(humidity * 10) / 10.0;
  doc["soil_moisture"] = round(soilMoisture * 10) / 10.0;
  doc["status_pompa"] = isPumpOn ? "Hidup" : "Mati";
  doc["mode"] = modeOperasi;
  
  String jsonString;
  serializeJson(doc, jsonString);
  
  int httpCode = http.POST(jsonString);
  
  if (httpCode == 200) {
    Serial.print("📤 Data sent | Mode: ");
    Serial.print(modeOperasi);
    Serial.print(" | Soil: ");
    Serial.print(soilMoisture, 1);
    Serial.print("% | Temp: ");
    Serial.print(temperature, 1);
    Serial.print("°C | Pump: ");
    Serial.println(isPumpOn ? "ON" : "OFF");
  }
  
  http.end();
}
