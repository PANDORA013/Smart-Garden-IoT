/*
 * ================================
 * MONITORING CABAI IoT - ESP32
 * ================================
 * 
 * Deskripsi:
 * Program untuk monitoring kelembapan tanah dan kontrol pompa otomatis
 * untuk tanaman cabai menggunakan ESP32 + Soil Moisture Sensor
 * 
 * Hardware yang digunakan:
 * - ESP32 Dev Board
 * - Soil Moisture Sensor (Analog)
 * - Relay Module 1 Channel (untuk pompa)
 * - Pompa Air DC 12V
 * - Power Supply 12V untuk pompa
 * 
 * Pin Configuration:
 * - Soil Moisture Sensor -> GPIO 34 (ADC1_CH6)
 * - Relay Module -> GPIO 25
 * 
 * Author: PANDORA013
 * Repository: https://github.com/PANDORA013/Smart-Garden-IoT
 * Date: January 2026
 */

#include <WiFi.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>

// ========== KONFIGURASI WiFi ==========
const char* ssid = "YOUR_WIFI_SSID";           // Ganti dengan SSID WiFi Anda
const char* password = "YOUR_WIFI_PASSWORD";   // Ganti dengan password WiFi Anda

// ========== KONFIGURASI SERVER ==========
const char* serverUrl = "http://YOUR_LAPTOP_IP:8000/api/monitoring/insert";
// Contoh: "http://192.168.1.100:8000/api/monitoring/insert"
// Cari IP laptop: ipconfig (Windows) / ifconfig (Mac/Linux)

// ========== PIN CONFIGURATION ==========
#define SOIL_MOISTURE_PIN 34    // Pin analog untuk sensor kelembapan tanah
#define RELAY_PIN 25            // Pin digital untuk relay pompa

// ========== THRESHOLD SETTINGS ==========
const float MOISTURE_THRESHOLD = 40.0;  // Pompa hidup jika kelembapan < 40%
const int SENSOR_MIN = 4095;            // Nilai ADC saat tanah kering (12-bit ADC)
const int SENSOR_MAX = 1500;            // Nilai ADC saat tanah basah (kalibrasi manual)

// ========== VARIABEL GLOBAL ==========
float soilMoisture = 0;
String statusPompa = "Mati";
unsigned long lastSendTime = 0;
const unsigned long SEND_INTERVAL = 5000;  // Kirim data setiap 5 detik

// ========== SETUP ==========
void setup() {
  Serial.begin(115200);
  delay(1000);
  
  Serial.println("\n========================================");
  Serial.println("    MONITORING CABAI IoT - ESP32");
  Serial.println("========================================\n");
  
  // Setup Pin Mode
  pinMode(RELAY_PIN, OUTPUT);
  digitalWrite(RELAY_PIN, LOW);  // Pastikan pompa mati saat startup
  
  // Koneksi WiFi
  connectWiFi();
  
  Serial.println("\n[INFO] Sistem siap! Mulai monitoring...\n");
}

// ========== LOOP UTAMA ==========
void loop() {
  // 1. Baca sensor kelembapan tanah
  soilMoisture = readSoilMoisture();
  
  // 2. Logika kontrol pompa otomatis
  controlPump();
  
  // 3. Kirim data ke server setiap 5 detik
  if (millis() - lastSendTime >= SEND_INTERVAL) {
    sendDataToServer();
    lastSendTime = millis();
  }
  
  // 4. Print status ke Serial Monitor
  printStatus();
  
  delay(1000);  // Delay 1 detik
}

// ========== FUNGSI: Koneksi WiFi ==========
void connectWiFi() {
  Serial.print("[WiFi] Connecting to: ");
  Serial.println(ssid);
  
  WiFi.begin(ssid, password);
  
  int attempts = 0;
  while (WiFi.status() != WL_CONNECTED && attempts < 20) {
    delay(500);
    Serial.print(".");
    attempts++;
  }
  
  if (WiFi.status() == WL_CONNECTED) {
    Serial.println("\n[WiFi] Connected!");
    Serial.print("[WiFi] IP Address: ");
    Serial.println(WiFi.localIP());
    Serial.print("[WiFi] Signal Strength: ");
    Serial.print(WiFi.RSSI());
    Serial.println(" dBm");
  } else {
    Serial.println("\n[WiFi] Connection FAILED!");
    Serial.println("[WiFi] Restart ESP32 dan cek SSID/Password!");
  }
}

// ========== FUNGSI: Baca Sensor Kelembapan ==========
float readSoilMoisture() {
  // Baca nilai ADC (0-4095 untuk ESP32)
  int rawValue = analogRead(SOIL_MOISTURE_PIN);
  
  // Konversi ke persentase (0-100%)
  // PENTING: Kalibrasi SENSOR_MIN dan SENSOR_MAX sesuai sensor Anda!
  // Cara kalibrasi:
  // 1. Celupkan sensor ke air -> catat nilai (ini SENSOR_MAX)
  // 2. Keringkan sensor di udara -> catat nilai (ini SENSOR_MIN)
  
  float moisture = map(rawValue, SENSOR_MIN, SENSOR_MAX, 0, 100);
  moisture = constrain(moisture, 0, 100);  // Batasi 0-100%
  
  return moisture;
}

// ========== FUNGSI: Kontrol Pompa Otomatis ==========
void controlPump() {
  if (soilMoisture < MOISTURE_THRESHOLD) {
    // Tanah KERING (<40%) -> NYALAKAN pompa
    digitalWrite(RELAY_PIN, HIGH);  // Ubah jadi LOW jika relay active-low
    statusPompa = "Hidup";
  } else {
    // Tanah NORMAL/BASAH -> MATIKAN pompa
    digitalWrite(RELAY_PIN, LOW);   // Ubah jadi HIGH jika relay active-low
    statusPompa = "Mati";
  }
}

// ========== FUNGSI: Kirim Data ke Server ==========
void sendDataToServer() {
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("[HTTP] WiFi not connected! Skipping...");
    return;
  }
  
  HTTPClient http;
  http.begin(serverUrl);
  http.addHeader("Content-Type", "application/json");
  
  // Buat JSON payload
  StaticJsonDocument<200> doc;
  doc["soil_moisture"] = soilMoisture;
  doc["status_pompa"] = statusPompa;
  
  String jsonString;
  serializeJson(doc, jsonString);
  
  // Kirim POST request
  int httpResponseCode = http.POST(jsonString);
  
  if (httpResponseCode > 0) {
    Serial.print("[HTTP] Response code: ");
    Serial.println(httpResponseCode);
    
    if (httpResponseCode == 201) {
      Serial.println("[HTTP] Data berhasil dikirim! ✓");
    }
  } else {
    Serial.print("[HTTP] Error: ");
    Serial.println(http.errorToString(httpResponseCode).c_str());
  }
  
  http.end();
}

// ========== FUNGSI: Print Status ke Serial Monitor ==========
void printStatus() {
  Serial.println("─────────────────────────────────────");
  Serial.print("🌶️  Kelembapan Tanah: ");
  Serial.print(soilMoisture, 1);
  Serial.print("% ");
  
  if (soilMoisture < 40) {
    Serial.println("(KERING ⚠️)");
  } else if (soilMoisture < 70) {
    Serial.println("(NORMAL ✓)");
  } else {
    Serial.println("(BASAH 💧)");
  }
  
  Serial.print("💦  Status Pompa: ");
  Serial.print(statusPompa);
  if (statusPompa == "Hidup") {
    Serial.println(" 🟢");
  } else {
    Serial.println(" 🔴");
  }
  Serial.println("─────────────────────────────────────\n");
}

/*
 * ========================================
 * CARA INSTALL & UPLOAD KE ESP32
 * ========================================
 * 
 * 1. Install Arduino IDE dari https://arduino.cc/en/software
 * 
 * 2. Install ESP32 Board:
 *    - Buka: File > Preferences
 *    - Additional Boards Manager URLs: 
 *      https://dl.espressif.com/dl/package_esp32_index.json
 *    - Tools > Board > Boards Manager > Search "esp32" > Install
 * 
 * 3. Install Library yang dibutuhkan:
 *    - ArduinoJson (by Benoit Blanchon)
 *    Cara: Sketch > Include Library > Manage Libraries > Search > Install
 * 
 * 4. Konfigurasi Board:
 *    - Tools > Board > ESP32 Arduino > ESP32 Dev Module
 *    - Tools > Port > Pilih COM port ESP32 Anda
 * 
 * 5. Edit Konfigurasi:
 *    - Ganti YOUR_WIFI_SSID dengan nama WiFi Anda
 *    - Ganti YOUR_WIFI_PASSWORD dengan password WiFi
 *    - Ganti YOUR_LAPTOP_IP dengan IP laptop (cek: ipconfig / ifconfig)
 * 
 * 6. Upload Code:
 *    - Klik tombol "Upload" (panah kanan)
 *    - Tunggu hingga muncul "Done uploading"
 * 
 * 7. Monitor Serial:
 *    - Tools > Serial Monitor (atau Ctrl+Shift+M)
 *    - Set baud rate: 115200
 *    - Lihat data real-time!
 * 
 * ========================================
 * TROUBLESHOOTING
 * ========================================
 * 
 * ❌ Error "WiFi not connected":
 *    - Cek SSID dan password WiFi
 *    - Pastikan ESP32 dalam jangkauan WiFi
 * 
 * ❌ Error "HTTP Error -1":
 *    - Cek IP address server (laptop)
 *    - Pastikan Laravel running: php artisan serve
 *    - Cek firewall Windows (allow port 8000)
 * 
 * ❌ Nilai kelembapan aneh (selalu 0% atau 100%):
 *    - Kalibrasi sensor! Baca bagian readSoilMoisture()
 *    - Celupkan ke air -> catat nilai -> set SENSOR_MAX
 *    - Keringkan di udara -> catat nilai -> set SENSOR_MIN
 * 
 * ❌ Pompa tidak nyala:
 *    - Cek wiring relay
 *    - Beberapa relay active-LOW (tukar HIGH/LOW di controlPump())
 *    - Cek power supply pompa 12V
 * 
 * ========================================
 * REFERENSI VIDEO
 * ========================================
 * 
 * Tutorial lengkap ESP32 + Soil Moisture + Relay:
 * https://www.youtube.com/watch?v=mhLo4pFCW0w
 * 
 * ========================================
 */
