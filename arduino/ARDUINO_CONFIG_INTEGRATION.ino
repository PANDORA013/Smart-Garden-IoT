// ============================================
// ARDUINO ESP32 - INTEGRASI CONFIG RESPONSE
// ============================================
// Date: January 2, 2026
// Backend Version: v3.1 (Fixed Fatal Issues)

#include <WiFi.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>
#include <NTPClient.h>
#include <WiFiUdp.h>

// ============================================
// GLOBAL VARIABLES - AKAN DI-UPDATE DARI SERVER
// ============================================
int currentMode = 1;           // Default: Mode Pemula
int BATAS_SIRAM = 40;          // % kelembaban untuk start pompa
int BATAS_STOP = 70;           // % kelembaban untuk stop pompa
String jam_pagi = "07:00";     // Jam siram pagi
String jam_sore = "17:00";     // Jam siram sore
int durasi_siram = 5;          // Detik
int sensor_min = 4095;         // ADC kering
int sensor_max = 1500;         // ADC basah
bool is_active = true;         // Device active/inactive

// Hardware pins
#define SOIL_SENSOR_PIN 34
#define DHT_PIN 32
#define RELAY_PIN 25

// Server config
const char* SERVER_URL = "http://192.168.1.100:8000/api/monitoring/insert";
const char* DEVICE_NAME = "ESP32_Main";

// ============================================
// FUNGSI: KIRIM DATA + TERIMA CONFIG
// ============================================
void sendDataAndGetConfig() {
    if (WiFi.status() != WL_CONNECTED) {
        Serial.println("❌ WiFi not connected!");
        return;
    }

    HTTPClient http;
    http.begin(SERVER_URL);
    http.addHeader("Content-Type", "application/json");

    // 1. BACA SENSOR
    float temperature = readTemperature();
    float humidity = readHumidity();
    int soilRaw = analogRead(SOIL_SENSOR_PIN);
    int soilPercent = map(soilRaw, sensor_max, sensor_min, 100, 0);
    soilPercent = constrain(soilPercent, 0, 100);
    bool relayState = digitalRead(RELAY_PIN);

    // 2. BUAT JSON
    StaticJsonDocument<512> requestDoc;
    requestDoc["device_name"] = DEVICE_NAME;
    requestDoc["temperature"] = temperature;
    requestDoc["humidity"] = humidity;
    requestDoc["soil_moisture"] = soilPercent;
    requestDoc["relay_status"] = relayState;
    requestDoc["firmware_version"] = "v2.1";

    String requestBody;
    serializeJson(requestDoc, requestBody);

    Serial.println("\n📤 Sending data to server...");
    Serial.println(requestBody);

    // 3. POST KE SERVER
    int httpCode = http.POST(requestBody);

    if (httpCode > 0) {
        String response = http.getString();
        Serial.println("📥 Response received:");
        Serial.println(response);

        // 4. PARSE CONFIG DARI RESPONSE
        StaticJsonDocument<1024> responseDoc;
        DeserializationError error = deserializeJson(responseDoc, response);

        if (error) {
            Serial.print("❌ JSON parse error: ");
            Serial.println(error.c_str());
            http.end();
            return;
        }

        // 5. ✅ AMBIL CONFIG DARI SERVER (FIX MASALAH #3)
        if (responseDoc.containsKey("config")) {
            JsonObject config = responseDoc["config"];

            // Update global variables
            int newMode = config["mode"] | 1;
            int newBatasSiram = config["batas_siram"] | 40;
            int newBatasStop = config["batas_stop"] | 70;
            String newJamPagi = config["jam_pagi"] | "07:00";
            String newJamSore = config["jam_sore"] | "17:00";
            int newDurasi = config["durasi_siram"] | 5;
            int newSensorMin = config["sensor_min"] | 4095;
            int newSensorMax = config["sensor_max"] | 1500;
            bool newIsActive = config["is_active"] | true;

            // Cek apakah ada perubahan
            if (newMode != currentMode) {
                Serial.println("\n🔄 CONFIG UPDATE DETECTED!");
                Serial.print("   Mode changed: ");
                Serial.print(currentMode);
                Serial.print(" → ");
                Serial.println(newMode);
                currentMode = newMode;
            }

            if (newBatasSiram != BATAS_SIRAM || newBatasStop != BATAS_STOP) {
                Serial.print("   Threshold changed: ");
                Serial.print(BATAS_SIRAM);
                Serial.print("%-");
                Serial.print(BATAS_STOP);
                Serial.print("% → ");
                Serial.print(newBatasSiram);
                Serial.print("%-");
                Serial.print(newBatasStop);
                Serial.println("%");
                BATAS_SIRAM = newBatasSiram;
                BATAS_STOP = newBatasStop;
            }

            if (newJamPagi != jam_pagi || newJamSore != jam_sore) {
                Serial.print("   Schedule changed: ");
                Serial.print(jam_pagi);
                Serial.print(" & ");
                Serial.print(jam_sore);
                Serial.print(" → ");
                Serial.print(newJamPagi);
                Serial.print(" & ");
                Serial.println(newJamSore);
                jam_pagi = newJamPagi;
                jam_sore = newJamSore;
            }

            if (newDurasi != durasi_siram) {
                Serial.print("   Duration changed: ");
                Serial.print(durasi_siram);
                Serial.print("s → ");
                Serial.print(newDurasi);
                Serial.println("s");
                durasi_siram = newDurasi;
            }

            if (newSensorMin != sensor_min || newSensorMax != sensor_max) {
                Serial.print("   Calibration changed: [");
                Serial.print(sensor_min);
                Serial.print(",");
                Serial.print(sensor_max);
                Serial.print("] → [");
                Serial.print(newSensorMin);
                Serial.print(",");
                Serial.print(newSensorMax);
                Serial.println("]");
                sensor_min = newSensorMin;
                sensor_max = newSensorMax;
            }

            is_active = newIsActive;

            Serial.println("✅ Config updated successfully!");
            printCurrentConfig();
        } else {
            Serial.println("⚠️  No config in response (using current settings)");
        }
    } else {
        Serial.print("❌ HTTP Error: ");
        Serial.println(httpCode);
    }

    http.end();
}

// ============================================
// FUNGSI: PRINT CONFIG
// ============================================
void printCurrentConfig() {
    Serial.println("\n📋 CURRENT CONFIG:");
    Serial.print("   Mode: ");
    Serial.print(currentMode);
    Serial.print(" (");
    switch (currentMode) {
        case 1: Serial.print("Pemula"); break;
        case 2: Serial.print("AI Fuzzy"); break;
        case 3: Serial.print("Terjadwal"); break;
        case 4: Serial.print("Manual"); break;
    }
    Serial.println(")");
    Serial.print("   Threshold: ");
    Serial.print(BATAS_SIRAM);
    Serial.print("% - ");
    Serial.print(BATAS_STOP);
    Serial.println("%");
    Serial.print("   Schedule: ");
    Serial.print(jam_pagi);
    Serial.print(" & ");
    Serial.println(jam_sore);
    Serial.print("   Duration: ");
    Serial.print(durasi_siram);
    Serial.println("s");
    Serial.print("   Calibration: [");
    Serial.print(sensor_min);
    Serial.print(", ");
    Serial.print(sensor_max);
    Serial.println("]");
    Serial.print("   Active: ");
    Serial.println(is_active ? "Yes" : "No");
    Serial.println();
}

// ============================================
// FUNGSI: LOGIKA SIRAM (SESUAI MODE)
// ============================================
void handleIrrigation(int soilPercent) {
    if (!is_active) {
        Serial.println("⚠️  Device inactive - irrigation disabled");
        return;
    }

    switch (currentMode) {
        case 1: // MODE PEMULA
            mode1_basic(soilPercent);
            break;

        case 2: // MODE AI FUZZY
            mode2_fuzzy(soilPercent);
            break;

        case 3: // MODE TERJADWAL
            mode3_schedule();
            break;

        case 4: // MODE MANUAL
            mode4_manual(soilPercent);
            break;

        default:
            Serial.println("❌ Unknown mode!");
    }
}

// ============================================
// MODE 1: PEMULA (THRESHOLD 40%-70%)
// ============================================
void mode1_basic(int soilPercent) {
    static bool pompaAktif = false;

    if (soilPercent < BATAS_SIRAM && !pompaAktif) {
        Serial.println("💧 Mode Pemula: Soil < 40% → Start pompa");
        digitalWrite(RELAY_PIN, HIGH);
        pompaAktif = true;
    } 
    else if (soilPercent >= BATAS_STOP && pompaAktif) {
        Serial.println("✅ Mode Pemula: Soil >= 70% → Stop pompa");
        digitalWrite(RELAY_PIN, LOW);
        pompaAktif = false;
    }
}

// ============================================
// MODE 2: AI FUZZY (HEMAT AIR 30-40%)
// ============================================
void mode2_fuzzy(int soilPercent) {
    // Fuzzy logic: adjustable berdasarkan suhu & humidity
    float temperature = readTemperature();
    float humidity = readHumidity();

    // Fuzzy adjustment
    int adjustedThreshold = BATAS_SIRAM;
    if (temperature > 30) adjustedThreshold -= 5;  // Panas → siram lebih cepat
    if (humidity < 50) adjustedThreshold -= 5;     // Kering → siram lebih cepat

    static bool pompaAktif = false;

    if (soilPercent < adjustedThreshold && !pompaAktif) {
        Serial.print("🤖 Mode Fuzzy: Soil < ");
        Serial.print(adjustedThreshold);
        Serial.println("% → Start pompa");
        digitalWrite(RELAY_PIN, HIGH);
        pompaAktif = true;
    } 
    else if (soilPercent >= BATAS_STOP && pompaAktif) {
        Serial.println("✅ Mode Fuzzy: Soil >= 70% → Stop pompa");
        digitalWrite(RELAY_PIN, LOW);
        pompaAktif = false;
    }
}

// ============================================
// MODE 3: TERJADWAL (NTP SYNC)
// ============================================
WiFiUDP ntpUDP;
NTPClient timeClient(ntpUDP, "pool.ntp.org", 25200, 60000); // GMT+7

void mode3_schedule() {
    timeClient.update();
    
    String currentTime = timeClient.getFormattedTime();
    String currentHour = currentTime.substring(0, 5); // HH:MM

    static bool sudahSiramPagi = false;
    static bool sudahSiramSore = false;
    static String lastDate = "";

    // Reset flag setiap hari baru
    String today = String(timeClient.getDay());
    if (today != lastDate) {
        sudahSiramPagi = false;
        sudahSiramSore = false;
        lastDate = today;
    }

    // Cek jam pagi
    if (currentHour == jam_pagi && !sudahSiramPagi) {
        Serial.print("🌅 Mode Jadwal: Siram pagi (");
        Serial.print(jam_pagi);
        Serial.println(")");
        digitalWrite(RELAY_PIN, HIGH);
        delay(durasi_siram * 1000);
        digitalWrite(RELAY_PIN, LOW);
        sudahSiramPagi = true;
    }

    // Cek jam sore
    if (currentHour == jam_sore && !sudahSiramSore) {
        Serial.print("🌇 Mode Jadwal: Siram sore (");
        Serial.print(jam_sore);
        Serial.println(")");
        digitalWrite(RELAY_PIN, HIGH);
        delay(durasi_siram * 1000);
        digitalWrite(RELAY_PIN, LOW);
        sudahSiramSore = true;
    }
}

// ============================================
// MODE 4: MANUAL (CUSTOM THRESHOLD)
// ============================================
void mode4_manual(int soilPercent) {
    // Sama seperti Mode 1, tapi user bisa custom threshold
    static bool pompaAktif = false;

    if (soilPercent < BATAS_SIRAM && !pompaAktif) {
        Serial.print("🛠️  Mode Manual: Soil < ");
        Serial.print(BATAS_SIRAM);
        Serial.println("% → Start pompa");
        digitalWrite(RELAY_PIN, HIGH);
        pompaAktif = true;
    } 
    else if (soilPercent >= BATAS_STOP && pompaAktif) {
        Serial.print("✅ Mode Manual: Soil >= ");
        Serial.print(BATAS_STOP);
        Serial.println("% → Stop pompa");
        digitalWrite(RELAY_PIN, LOW);
        pompaAktif = false;
    }
}

// ============================================
// HELPER FUNCTIONS
// ============================================
float readTemperature() {
    // Implement DHT22 reading
    return 28.5; // Placeholder
}

float readHumidity() {
    // Implement DHT22 reading
    return 65.0; // Placeholder
}

// ============================================
// SETUP & LOOP
// ============================================
void setup() {
    Serial.begin(115200);
    pinMode(RELAY_PIN, OUTPUT);
    digitalWrite(RELAY_PIN, LOW);

    // Connect WiFi
    WiFi.begin("Your_SSID", "Your_Password");
    while (WiFi.status() != WL_CONNECTED) {
        delay(500);
        Serial.print(".");
    }
    Serial.println("\n✅ WiFi connected!");

    // Init NTP
    timeClient.begin();

    // First check-in
    sendDataAndGetConfig();
}

void loop() {
    // Baca sensor
    int soilRaw = analogRead(SOIL_SENSOR_PIN);
    int soilPercent = map(soilRaw, sensor_max, sensor_min, 100, 0);
    soilPercent = constrain(soilPercent, 0, 100);

    // Handle irrigation berdasarkan mode
    handleIrrigation(soilPercent);

    // Kirim data setiap 30 detik + terima config update
    static unsigned long lastSend = 0;
    if (millis() - lastSend > 30000) {
        sendDataAndGetConfig();
        lastSend = millis();
    }

    delay(1000);
}

// ============================================
// CARA KERJA:
// ============================================
// 1. Arduino POST data sensor ke /api/monitoring/insert
// 2. Backend save data + cek/buat device_settings
// 3. Backend kirim balik config (mode, threshold, schedule, dll)
// 4. Arduino parse config dari response
// 5. Arduino update variable global (currentMode, BATAS_SIRAM, dll)
// 6. Arduino execute logika siram sesuai mode yang baru
//
// BENEFIT:
// ✅ User ubah mode di web → Arduino auto update
// ✅ User ubah threshold → Arduino auto update
// ✅ User ubah jadwal → Arduino auto update
// ✅ Tidak perlu hard-code config di Arduino
// ✅ Device baru auto-provision dengan default config
