#include <WiFi.h>
#include <HTTPClient.h>

// --- KONFIGURASI WIFI & IP STATIC ---
const char* WIFI_SSID = "SEID-PCS";
const char* WIFI_PASSWORD = "SeidPcs01";

// Atur IP Static untuk ESP32
IPAddress local_IP(192, 168, 189, 230);
IPAddress gateway(192, 168, 189, 129);
IPAddress subnet(255, 255, 255, 128);

// --- KONFIGURASI SERVER ---
const char* SERVER_URL = "http://192.168.180.181/Line-Monitor/api_receive.php";
const char* MACHINE_ID = "IDU-Helium";

// --- KONFIGURASI PIN GPIO ---
const int SENSOR_PIN = 22;  // GPIO 22 sebagai Input Sensor (INPUT_PULLDOWN)

// --- VARIABEL GLOBAL ---
// Default status awal LOW karena menggunakan Internal Pull-Down
int previous_state = LOW;
int current_state = LOW;

// Variabel Kontrol Toggle & Timer
bool is_counting = false;   // Status aktif/non-aktif counting
int count = 0;              // Durasi hitungan (dalam detik)
unsigned long last_time = 0;
const long interval = 1000; // Interval 1 detik

void sendDataToServer(int final_count) {
  if (WiFi.status() == WL_CONNECTED) {
    HTTPClient http;
    http.begin(SERVER_URL);
    http.addHeader("Content-Type", "application/json");
    http.setTimeout(3000);

    String payload = "{\"machine_id\":\"" + String(MACHINE_ID) + "\",\"count\":" + String(final_count) + "}";

    Serial.print("["); Serial.print(MACHINE_ID); Serial.print("] Mengirim JSON: ");
    Serial.println(payload);

    int httpResponseCode = http.POST(payload);

    if (httpResponseCode > 0) {
      Serial.print("["); Serial.print(MACHINE_ID); Serial.print("] Data terkirim ke Server (");
      Serial.print(httpResponseCode); Serial.println(")");
    } else {
      Serial.print("["); Serial.print(MACHINE_ID); Serial.print("] Gagal koneksi ke server, error: ");
      Serial.println(http.errorToString(httpResponseCode));
    }

    http.end();
  } else {
    Serial.println("WiFi terputus! Gagal mengirim data.");
  }
}

void setup() {
  Serial.begin(115200);

  // Gunakan INPUT_PULLDOWN agar default status pin adalah LOW saat tidak ditekan
  pinMode(SENSOR_PIN, INPUT_PULLDOWN);

  Serial.println("\n--- Starting ESP32 Machine Counter (Toggle Mode 3.3V) ---");

  // Konfigurasi IP Static
  if (!WiFi.config(local_IP, gateway, subnet)) {
    Serial.println("Gagal mengkonfigurasi IP Static!");
  }

  // Koneksi ke WiFi
  WiFi.begin(WIFI_SSID, WIFI_PASSWORD);
  Serial.print("Menghubungkan ke WiFi");
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  
  Serial.println("\nWiFi Terhubung!");
  Serial.print("IP Static ESP32: ");
  Serial.println(WiFi.localIP());
}

void loop() {
  current_state = digitalRead(SENSOR_PIN);

  // 1. DETEKSI TOMBOL DIPENCET (Transisi LOW ke HIGH saat terhubung ke 3.3V)
  if (previous_state == LOW && current_state == HIGH) {
    is_counting = !is_counting; // Toggle status (Start <-> Stop)

    if (is_counting) {
      // Pencetan Pertama: MULAILAH COUNTING
      count = 0;
      last_time = millis();
      Serial.print("["); Serial.print(MACHINE_ID); Serial.println("] === START COUNTING ===");
    } 
    else {
      // Pencetan Kedua: STOP COUNTING & KIRIM DATA
      Serial.print("["); Serial.print(MACHINE_ID); Serial.print("] === STOP COUNTING === Total Durasi: ");
      Serial.print(count); Serial.println(" detik");

      // Kirim hasil akhir ke server
      sendDataToServer(count);

      count = 0; // Reset hitungan
    }

    delay(200); // Debounce
  }

  previous_state = current_state;

  // 2. HITUNG DURASI TIAP DETIK (Saat is_counting == true)
  if (is_counting) {
    unsigned long current_time = millis();
    if (current_time - last_time >= interval) {
      last_time = current_time;
      count++; // Tambah 1 detik

      Serial.print("["); Serial.print(MACHINE_ID); Serial.print("] Running Count: ");
      Serial.print(count); Serial.println(" s");
    }
  }
}