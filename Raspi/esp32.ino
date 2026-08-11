#include <WiFi.h>
#include <HTTPClient.h>

// --- KONFIGURASI WIFI (DHCP) ---
const char* WIFI_SSID     = "SEID-MO";
const char* WIFI_PASSWORD = "MainOffice001";

// --- KONFIGURASI SERVER & MACHINE ID ---
const char* SERVER_URL = "http://192.168.180.181/Line-Monitor/api_receive.php";
const char* MACHINE_ID = "ODU-Vacuum";

// --- KONFIGURASI PIN SENSOR/TOMBOL ---
const int SENSOR_PIN = 22; // GPIO 22 (Pull-Down)

// --- VARIABEL GLOBAL TOMBOL ---
int previous_state = LOW;
int current_state  = LOW;
unsigned long stop_start_time = 0;
unsigned long last_debounce_time = 0;
const unsigned long DEBOUNCE_DELAY = 100; // Debounce 100ms

// --- VARIABEL TIMER PENGECEKAN WIFI & FLAG SIBUK ---
unsigned long last_wifi_check = 0;
const unsigned long WIFI_CHECK_INTERVAL = 3000; // Cek WiFi tiap 3 detik
bool is_sending_data = false; // Flag penanda agar pengecekan WiFi tidak mengganggu HTTP

// ==========================================
// FUNGSI CEK KONEKSI WIFI (NON-BLOCKING)
// ==========================================
void checkWiFiConnection() {
  if (!is_sending_data) {
    if (WiFi.status() != WL_CONNECTED) {
      Serial.println("[WiFi Warning] WiFi Terputus! Reconnecting...");
      WiFi.reconnect();
    }
  }
}

// ==========================================
// FUNGSI UTAMA PENGIRIMAN DATA HTTP
// ==========================================
bool sendDataToServer(String payload) {
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("[HTTP Error] Tidak dapat mengirim, WiFi terputus.");
    return false;
  }

  is_sending_data = true;

  WiFiClient client;
  HTTPClient http;
  bool success = false;

  client.setTimeout(3); // Timeout socket TCP 3 detik

  if (http.begin(client, SERVER_URL)) {
    http.addHeader("Content-Type", "application/json");
    http.addHeader("Connection", "close"); // Memaksa Apache menutup socket setelah respon
    http.setTimeout(3000);

    int httpCode = http.POST(payload);

    if (httpCode > 0) {
      Serial.printf("[%s] Sent Data Success -> HTTP: %d\n", MACHINE_ID, httpCode);
      success = true;
    } else {
      Serial.printf("[%s] Sent Data Failed -> Error: %s\n", MACHINE_ID, http.errorToString(httpCode).c_str());
    }

    http.end();
  } else {
    Serial.println("[HTTP Error] HTTP Begin Failed!");
  }

  client.stop(); // Bersihkan socket TCP
  
  is_sending_data = false;
  return success;
}

// ==========================================
// FUNGSI KIRIM PAYLOAD STATUS "OFF"
// ==========================================
void sendStatusOFF() {
  String payload = "{\"machine_id\":\"" + String(MACHINE_ID) + "\",\"status\":\"OFF\"}";
  
  if (!sendDataToServer(payload)) {
    delay(500);
    Serial.println("Retrying Sent OFF...");
    sendDataToServer(payload);
  }
  delay(300);
}

// ==========================================
// FUNGSI KIRIM PAYLOAD STATUS "ON"
// ==========================================
void sendStatusON(int duration_seconds) {
  String payload = "{\"machine_id\":\"" + String(MACHINE_ID) + "\",\"status\":\"ON\",\"stop_duration\":" + String(duration_seconds) + "}";
  
  if (!sendDataToServer(payload)) {
    delay(500);
    Serial.println("Retrying Sent ON...");
    sendDataToServer(payload);
  }
  delay(300);
}

// ==========================================
// SETUP
// ==========================================
void setup() {
  Serial.begin(115200);
  pinMode(SENSOR_PIN, INPUT_PULLDOWN);

  WiFi.setSleep(false); // Matikan WiFi Power Save agar latency tetap stabil

  Serial.println("\n--- ESP32 Line Monitor Starting (DHCP Mode) ---");

  // Koneksi DHCP (Tanpa WiFi.config)
  WiFi.begin(WIFI_SSID, WIFI_PASSWORD);
  Serial.print("Connecting to WiFi via DHCP");
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }

  // Menampilkan IP Dinamis dari Router
  Serial.printf("\nWiFi Connected! IP Dynamic ESP32: %s\n", WiFi.localIP().toString().c_str());
}

// ==========================================
// LOOP UTAMA
// ==========================================
void loop() {
  unsigned long current_time = millis();

  // 1. CEK KONEKSI WIFI SETIAP 3 DETIK
  if (current_time - last_wifi_check >= WIFI_CHECK_INTERVAL) {
    last_wifi_check = current_time;
    checkWiFiConnection();
  }

  // 2. LOGIKA BACA TOMBOL DENGAN DEBOUNCE
  int reading = digitalRead(SENSOR_PIN);

  if (reading != previous_state) {
    last_debounce_time = current_time;
  }

  if ((current_time - last_debounce_time) > DEBOUNCE_DELAY) {
    if (reading != current_state) {
      current_state = reading;

      // KONDISI 1: Tombol Ditekan (LOW -> HIGH) -> Kirim Status OFF
      if (current_state == HIGH) {
        stop_start_time = millis();
        sendStatusOFF();
      }

      // KONDISI 2: Tombol Dilepas (HIGH -> LOW) -> Kirim Status ON + Durasi
      if (current_state == LOW) {
        int stop_duration_sec = (millis() - stop_start_time) / 1000;
        sendStatusON(stop_duration_sec);
      }
    }
  }

  previous_state = reading;
}