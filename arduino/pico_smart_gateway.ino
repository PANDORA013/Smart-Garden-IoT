/*
 * =============================================================================
 * RASPBERRY PI PICO W - SMART GARDEN GATEWAY (2-Way Communication)
 * =============================================================================
 * 
 * FITUR UTAMA:
 * ✅ 2-Way Communication (Kirim Data + Terima Config dari Server)
 * ✅ Auto-Provisioning (Server otomatis buat device_settings)
 * ✅ Support 3 Mode:
 *    - Mode 1: Basic Threshold (Batas Kering/Basah)
 *    - Mode 2: Fuzzy Logic AI (Suhu + Kelembaban → Durasi Siram)
 *    - Mode 3: Schedule Timer (Jam Pagi/Sore + Durasi)
 * ✅ Kalibrasi ADC Dinamis (Dari Server, bisa diubah via Dashboard)
 * 
 * KONEKSI HARDWARE:
 * - Pin ADC (GP26-GP28) → Sensor Kelembaban Tanah (Capacitive/Resistive)
 * - Pin 2 (GP2) → DHT22 (Suhu & Kelembaban Udara)
 * - Pin 5 (GP5) → Relay/Pompa Air
 * 
 * =============================================================================
 */

#include <WiFi.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>
#include <DHT.h>
#include <NTPClient.h>
#include <WiFiUdp.h>

// ===========================
// KONFIGURASI WiFi & SERVER
// ===========================
const char* ssid = "YOUR_WIFI_SSID";        // Ganti dengan WiFi Anda
const char* password = "YOUR_WIFI_PASSWORD"; // Ganti dengan Password WiFi
const char* serverUrl = "http://192.168.1.100:8000/api/monitoring/insert"; // Ganti dengan IP Server Laravel

// ===========================
// KONFIGURASI HARDWARE
// ===========================
const int SOIL_SENSOR_PIN = 26;  // ADC Pin (GP26 = ADC0)
const int DHT_PIN = 2;           // DHT22 Pin
const int RELAY_PIN = 5;         // Relay Pin
const int DHT_TYPE = DHT22;

DHT dht(DHT_PIN, DHT_TYPE);

// ===========================
// DEVICE IDENTITY
// ===========================
String deviceId = "PICO_CABAI_01"; // ID Unik Alat (Ganti sesuai kebutuhan)

// ===========================
// VARIABEL KONFIGURASI (Dari Server)
// ===========================
int mode = 1;              // Default Mode 1 (Basic Threshold)
int adcMin = 4095;         // ADC Sensor Kering (di udara) - DEFAULT
int adcMax = 1500;         // ADC Sensor Basah (di air) - DEFAULT
int batasKering = 40;      // Pompa ON jika kelembaban < 40%
int batasBasah = 70;       // Pompa OFF jika kelembaban >= 70%
String jamPagi = "07:00";  // Jadwal Pagi (Mode 3)
String jamSore = "17:00";  // Jadwal Sore (Mode 3)
int durasiSiram = 5;       // Durasi siram dalam detik (Mode 3)

// ===========================
// NTP TIME (untuk Mode 3)
// ===========================
WiFiUDP ntpUDP;
NTPClient timeClient(ntpUDP, "pool.ntp.org", 25200, 60000); // UTC+7 (Indonesia)

// ===========================
// STATE VARIABLES
// ===========================
bool pumpState = false;
unsigned long lastSendTime = 0;
unsigned long pumpStartTime = 0;
const unsigned long SEND_INTERVAL = 10000; // Kirim data setiap 10 detik

void setup() {
  Serial.begin(115200);
  delay(1000);
  
  Serial.println("\n========================================");
  Serial.println("🌱 PICO W SMART GARDEN GATEWAY");
  Serial.println("========================================");
  
  // Setup Hardware
  pinMode(RELAY_PIN, OUTPUT);
  digitalWrite(RELAY_PIN, LOW);
  dht.begin();
  
  // Connect WiFi
  connectWiFi();
  
  // Start NTP
  timeClient.begin();
  
  Serial.println("✅ Setup Complete!");
  Serial.println("========================================\n");
}

void loop() {
  // Update NTP Time
  timeClient.update();
  
  // Baca Sensor
  int rawADC = analogRead(SOIL_SENSOR_PIN);
  float soilMoisture = mapADCtoPercent(rawADC, adcMin, adcMax);
  float temperature = dht.readTemperature();
  
  // Validasi Sensor DHT22
  if (isnan(temperature)) {
    Serial.println("⚠️ DHT22 Error! Using default values...");
    temperature = 28.0;
  }
  
  // Logika Kontrol Pompa (Berdasarkan Mode)
  controlPump(soilMoisture, temperature);
  
  // Kirim Data ke Server (Setiap 10 Detik)
  if (millis() - lastSendTime >= SEND_INTERVAL) {
    sendDataToServer(rawADC, soilMoisture, temperature);
    lastSendTime = millis();
  }
  
  delay(1000);
}

// ===========================
// FUNGSI: Connect WiFi
// ===========================
void connectWiFi() {
  Serial.print("🔌 Connecting to WiFi: ");
  Serial.println(ssid);
  
  WiFi.begin(ssid, password);
  int attempts = 0;
  
  while (WiFi.status() != WL_CONNECTED && attempts < 20) {
    delay(500);
    Serial.print(".");
    attempts++;
  }
  
  if (WiFi.status() == WL_CONNECTED) {
    Serial.println("\n✅ WiFi Connected!");
    Serial.print("📡 IP Address: ");
    Serial.println(WiFi.localIP());
  } else {
    Serial.println("\n❌ WiFi Connection Failed!");
  }
}

// ===========================
// FUNGSI: Konversi ADC → Persen
// ===========================
float mapADCtoPercent(int adc, int minVal, int maxVal) {
  // Clamp ADC ke range valid
  if (adc > minVal) adc = minVal;
  if (adc < maxVal) adc = maxVal;
  
  // Map: ADC tinggi (kering) = 0%, ADC rendah (basah) = 100%
  float percent = (float)(minVal - adc) / (float)(minVal - maxVal) * 100.0;
  
  // Clamp ke 0-100%
  if (percent < 0) percent = 0;
  if (percent > 100) percent = 100;
  
  return percent;
}

// ===========================
// FUNGSI: Kontrol Pompa (3 Mode)
// ===========================
void controlPump(float soil, float temp) {
  bool shouldPumpOn = false;
  
  // === MODE 1: BASIC THRESHOLD ===
  if (mode == 1) {
    if (soil < batasKering && !pumpState) {
      shouldPumpOn = true;
      Serial.println("🟢 Mode 1: Tanah kering, Pompa ON");
    } else if (soil >= batasBasah && pumpState) {
      shouldPumpOn = false;
      Serial.println("🟢 Mode 1: Tanah basah, Pompa OFF");
    } else {
      shouldPumpOn = pumpState; // Maintain current state
    }
  }
  
  // === MODE 2: FUZZY LOGIC AI ===
  else if (mode == 2) {
    if (soil < 40 && !pumpState) { // Hanya jika tanah kering
      int duration = 5; // Default 5 detik
      
      // Logika Fuzzy berdasarkan Suhu
      if (temp > 30) {
        duration = 8; // Panas → Siram lebih lama
        Serial.println("🔵 Mode 2 (Fuzzy): Panas + Kering → Siram 8 detik");
      } else if (temp > 25) {
        duration = 5; // Sedang → Siram normal
        Serial.println("🔵 Mode 2 (Fuzzy): Sedang + Kering → Siram 5 detik");
      } else {
        duration = 3; // Dingin → Siram sebentar
        Serial.println("🔵 Mode 2 (Fuzzy): Dingin + Kering → Siram 3 detik");
      }
      
      // Nyalakan pompa dengan timer
      shouldPumpOn = true;
      pumpStartTime = millis();
      durasiSiram = duration; // Set durasi dinamis
    } else if (pumpState && (millis() - pumpStartTime >= durasiSiram * 1000)) {
      // Matikan setelah durasi selesai
      shouldPumpOn = false;
      Serial.println("🔵 Mode 2 (Fuzzy): Durasi selesai, Pompa OFF");
    } else {
      shouldPumpOn = pumpState;
    }
  }
  
  // === MODE 3: SCHEDULE TIMER ===
  else if (mode == 3) {
    String currentTime = timeClient.getFormattedTime().substring(0, 5); // "HH:MM"
    
    if ((currentTime == jamPagi || currentTime == jamSore) && !pumpState) {
      shouldPumpOn = true;
      pumpStartTime = millis();
      Serial.print("🔴 Mode 3 (Schedule): Waktu siram ");
      Serial.println(currentTime);
    } else if (pumpState && (millis() - pumpStartTime >= durasiSiram * 1000)) {
      shouldPumpOn = false;
      Serial.println("🔴 Mode 3 (Schedule): Durasi selesai, Pompa OFF");
    } else {
      shouldPumpOn = pumpState;
    }
  }
  
  // Update Relay
  if (shouldPumpOn != pumpState) {
    pumpState = shouldPumpOn;
    digitalWrite(RELAY_PIN, pumpState ? HIGH : LOW);
  }
}

// ===========================
// FUNGSI: Kirim Data ke Server (2-Way Communication)
// ===========================
void sendDataToServer(int rawADC, float soil, float temp) {
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("❌ WiFi not connected!");
    return;
  }
  
  HTTPClient http;
  http.begin(serverUrl);
  http.addHeader("Content-Type", "application/json");
  
  // Buat JSON Payload
  StaticJsonDocument<512> doc;
  doc["device_id"] = deviceId;
  doc["temperature"] = temp;
  doc["soil_moisture"] = soil;
  doc["raw_adc"] = rawADC;
  doc["relay_status"] = pumpState;
  doc["ip_address"] = WiFi.localIP().toString();
  
  String jsonPayload;
  serializeJson(doc, jsonPayload);
  
  // Kirim HTTP POST
  Serial.println("\n📤 Sending data to server...");
  Serial.println(jsonPayload);
  
  int httpCode = http.POST(jsonPayload);
  
  if (httpCode > 0) {
    Serial.print("✅ Server Response Code: ");
    Serial.println(httpCode);
    
    if (httpCode == HTTP_CODE_OK || httpCode == 201) {
      String response = http.getString();
      Serial.println("📥 Server Response:");
      Serial.println(response);
      
      // PARSING CONFIG DARI SERVER (2-Way Communication)
      parseServerConfig(response);
    }
  } else {
    Serial.print("❌ HTTP Error: ");
    Serial.println(http.errorToString(httpCode));
  }
  
  http.end();
}

// ===========================
// FUNGSI: Parse Config dari Server (OTAK CERDAS)
// ===========================
void parseServerConfig(String response) {
  StaticJsonDocument<1024> doc;
  DeserializationError error = deserializeJson(doc, response);
  
  if (error) {
    Serial.print("❌ JSON Parse Error: ");
    Serial.println(error.c_str());
    return;
  }
  
  // Cek apakah ada object "config"
  if (!doc.containsKey("config")) {
    Serial.println("⚠️ No config in response");
    return;
  }
  
  JsonObject config = doc["config"];
  
  // Update Konfigurasi dari Server
  int newMode = config["mode"] | mode;
  int newAdcMin = config["adc_min"] | adcMin;
  int newAdcMax = config["adc_max"] | adcMax;
  int newBatasKering = config["batas_kering"] | batasKering;
  int newBatasBasah = config["batas_basah"] | batasBasah;
  String newJamPagi = config["jam_pagi"] | jamPagi;
  String newJamSore = config["jam_sore"] | jamSore;
  int newDurasi = config["durasi_siram"] | durasiSiram;
  
  // Deteksi Perubahan
  bool changed = false;
  if (newMode != mode) {
    Serial.print("🔄 Mode berubah: ");
    Serial.print(mode);
    Serial.print(" → ");
    Serial.println(newMode);
    mode = newMode;
    changed = true;
  }
  
  if (newAdcMin != adcMin || newAdcMax != adcMax) {
    Serial.println("🔄 Kalibrasi ADC berubah!");
    Serial.print("   ADC Min: ");
    Serial.print(adcMin);
    Serial.print(" → ");
    Serial.println(newAdcMin);
    Serial.print("   ADC Max: ");
    Serial.print(adcMax);
    Serial.print(" → ");
    Serial.println(newAdcMax);
    adcMin = newAdcMin;
    adcMax = newAdcMax;
    changed = true;
  }
  
  if (newBatasKering != batasKering || newBatasBasah != batasBasah) {
    Serial.println("🔄 Threshold berubah!");
    batasKering = newBatasKering;
    batasBasah = newBatasBasah;
    changed = true;
  }
  
  if (newJamPagi != jamPagi || newJamSore != jamSore || newDurasi != durasiSiram) {
    Serial.println("🔄 Jadwal berubah!");
    jamPagi = newJamPagi;
    jamSore = newJamSore;
    durasiSiram = newDurasi;
    changed = true;
  }
  
  if (changed) {
    Serial.println("✅ Konfigurasi berhasil diupdate dari server!");
  } else {
    Serial.println("ℹ️ Tidak ada perubahan konfigurasi");
  }
}
