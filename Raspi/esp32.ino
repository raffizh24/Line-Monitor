#include <WiFi.h>
#include <HTTPClient.h>

// --- KONFIGURASI WIFI & IP STATIC ---
const char* WIFI_SSID = "SEID-PCS";
const char* WIFI_PASSWORD = "SeidPcs01";

IPAddress local_IP(192, 168, 189, 230);
IPAddress gateway(192, 168, 189, 129);
IPAddress subnet(255, 255, 255, 128);

// --- KONFIGURASI SERVER ---
const char* SERVER_URL = "http://192.168.180.181/Line-Monitor/api_receive.php";
const char* MACHINE_ID = "IDU-Helium";

const int SENSOR_PIN = 22; // GPIO 22 (INPUT_PULLDOWN)

int previous_state = LOW;
int current_state = LOW;
unsigned long stop_start_time = 0;

void sendStopDataToServer(int duration_seconds) {
  if (WiFi.status() == WL_CONNECTED) {
    HTTPClient http;
    http.begin(SERVER_URL);
    http.addHeader("Content-Type", "application/json");
    http.setTimeout(3000);

    String payload = "{\"machine_id\":\"" + String(MACHINE_ID) + "\",\"status\":\"OFF\",\"stop_duration\":" + String(duration_seconds) + "}";
    int httpResponseCode = http.POST(payload);

    Serial.printf("[%s] Sent OFF Data (%ds) -> HTTP: %d\n", MACHINE_ID, duration_seconds, httpResponseCode);
    http.end();
  } else {
    Serial.println("WiFi Disconnected!");
  }
}

void setup() {
  Serial.begin(115200);
  pinMode(SENSOR_PIN, INPUT_PULLDOWN);

  if (!WiFi.config(local_IP, gateway, subnet)) {
    Serial.println("IP Static Config Failed!");
  }

  WiFi.begin(WIFI_SSID, WIFI_PASSWORD);
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
  }
  Serial.printf("\nWiFi Connected! IP: %s\n", WiFi.localIP().toString().c_str());
}

void loop() {
  current_state = digitalRead(SENSOR_PIN);

  // 1. Tombol Ditekan -> Mulai STOP/OFF
  if (previous_state == LOW && current_state == HIGH) {
    stop_start_time = millis();
    Serial.printf("[%s] Status: STOP\n", MACHINE_ID);
    delay(200);
  }

  // 2. Tombol Dilepas -> Selesai STOP, Kirim Durasi & Kembali ON
  if (previous_state == HIGH && current_state == LOW) {
    int stop_duration_sec = (millis() - stop_start_time) / 1000;
    Serial.printf("[%s] Status: RUNNING (Stopped for %ds)\n", MACHINE_ID, stop_duration_sec);
    sendStopDataToServer(stop_duration_sec);
    delay(200);
  }

  previous_state = current_state;
}