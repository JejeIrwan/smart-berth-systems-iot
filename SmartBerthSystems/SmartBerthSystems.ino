#include <Wire.h>
#include <U8g2lib.h>
#include <RTClib.h>
#include <WiFi.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>
#include <Adafruit_ADS1X15.h>

Adafruit_ADS1115 ads;
bool adsOK = false;

// ── WiFi ─────────────────────────────────────────────────────
const char* WIFI_SSID     = "YOUR_WIFI_SSID";
const char* WIFI_PASSWORD = "YOUR_WIFI_PASSWORD";

// ── Server ───────────────────────────────────────────────────
const char* SERVER_URL = "http://172.20.2.116/batamlog/api/update_berth.php";
const char* LOG_URL    = "http://172.20.2.116/batamlog/api/log_traffic.php";

// ── Pin Definitions: Sensor Jarak ───────────────────────────────
#define TRIG1_PIN   5 
#define ECHO1_PIN   18
#define TRIG2_PIN   19
#define ECHO2_PIN   4

// ── Pin Definitions: LED RGB ────────────────────────────────────
// LED RGB Berth 1 
#define LED1_R      26
#define LED1_G      25
#define LED1_B      33
// LED RGB Berth 2
#define LED2_R      32
#define LED2_G      27
#define LED2_B      14

// ── PWM Channel (LEDC) untuk RGB ────────────────────────────────
#define CH_LED1_R   0
#define CH_LED1_G   1
#define CH_LED1_B   2
#define CH_LED2_R   3
#define CH_LED2_G   4
#define CH_LED2_B   5
#define PWM_FREQ    5000
#define PWM_RES     8     // 0 - 255

#define BUZZER_PIN  13
#define WATER_PIN   36

// ── Threshold ─────────────────────────────────────────────────
#define DISTANCE_THRESHOLD  5.0    // cm
#define TIDE_SAFE            1650   // ADC (sesuai kalibrasi sebelumnya)

// ── Interval ──────────────────────────────────────────────────
#define SEND_INTERVAL   5000
#define OLED_INTERVAL   2000
#define BUZZER_INTERVAL 10000

// ── Objek Library ─────────────────────────────────────────────
U8G2_SH1106_128X64_NONAME_F_HW_I2C oled(U8G2_R0, U8X8_PIN_NONE);
RTC_DS3231 rtc;

// Kalibrasi ADS1115
// ── Kalibrasi ADS1115 (GAIN_ONE) — nilai ADC pada tiap kedalaman ──
//  Berdasarkan pengukuran NYATA sensormu:
//    kering (0 cm)      -> ADC ~1811
//    tercelup penuh(4cm)-> ADC ~6383
//  ADC1..ADC3 di bawah adalah ESTIMASI LINEAR antara 0-4 cm.
//  >> Untuk AKURASI MAKSIMAL: celup sensor tepat di 1, 2, 3 cm,
//     catat ADC-nya dari Serial, lalu ganti ADC1/ADC2/ADC3.
const float ADC0 = 1811;    // 0 cm (kering)
const float ADC1 = 2954;    // 1 cm (estimasi)
const float ADC2 = 4097;    // 2 cm (estimasi)
const float ADC3 = 5240;    // 3 cm (estimasi)
const float ADC4 = 6383;    // 4 cm (tercelup penuh)

// Moving Average ADS1115
float bacaADC() {
  if (!adsOK) return 0;          // ADS mati → jangan menggantung
  long total = 0;
  for (int i = 0; i < 20; i++) {
    total += ads.readADC_SingleEnded(0);
    delay(2);
  }
  return total / (float)20;
}

// Interpolasi linear ADC → Kedalaman (cm), di-clamp 0..4 cm
float hitungKedalaman(float adc) {
  if      (adc <= ADC0) return 0.0;                                   // di bawah baseline
  else if (adc <= ADC1) return 0.0 + (adc - ADC0) / (ADC1 - ADC0);
  else if (adc <= ADC2) return 1.0 + (adc - ADC1) / (ADC2 - ADC1);
  else if (adc <= ADC3) return 2.0 + (adc - ADC2) / (ADC3 - ADC2);
  else if (adc <= ADC4) return 3.0 + (adc - ADC3) / (ADC4 - ADC3);
  return 4.0;                                                        // di atas ADC4
}

// ── State ─────────────────────────────────────────────────────
struct BerthState {
  float  distanceCm;
  bool   isOccupied;
  bool   prevOccupied;
  String arrivalTime;
  unsigned long dockMillis;
};

struct TideState {
  int    avgADC;
  bool   isSafe;
  String label;
  String kedalaman;   // kedalaman miniatur (cm)
  String kedalamanM;  // kedalaman nyata (m)
  String kapal;       // rekomendasi kapal
  float  depthCm;     // kedalaman numerik (cm) untuk dikirim ke server
  float  depthM;      // kedalaman numerik (m) untuk dikirim ke server
};

BerthState berth1, berth2;
TideState  tide;

// ── Timer ─────────────────────────────────────────────────────
unsigned long lastSend         = 0;
unsigned long lastOledUpdate   = 0;
unsigned long lastBuzzerCheck  = 0;
int oledPage = 0;

// ── Forward Declarations ──────────────────────────────────────
float  ukurJarak(int trigPin, int echoPin);
void   updateTide();
void   updateBerth(BerthState &berth, int num);
void   setRGB(int chR, int chG, int chB, int r, int g, int b);
void   setLEDBerth(int chR, int chG, int chB, bool occupied, bool tideOk, String tideLabel);
void   tampilOLED();
void   kirimData();
void   logEvent(int num, String eventType, BerthState &berth); 
void   beep(int count, int onMs, int offMs);
String getWaktu();
String getDurasi(BerthState &berth);
void   connectWiFi();
void   syncNTP();


// ─────────────────────────────────────────────────────────────
//  SETUP
// ─────────────────────────────────────────────────────────────
void setup() {
  Serial.begin(115200);
  analogReadResolution(12);
  Serial.println("\n=== BATAM LOG - Smart Berth System (RGB) ===");

  // Pin modes sensor jarak
  pinMode(TRIG1_PIN,  OUTPUT);
  pinMode(ECHO1_PIN,  INPUT);
  pinMode(TRIG2_PIN,  OUTPUT);
  pinMode(ECHO2_PIN,  INPUT);
  pinMode(BUZZER_PIN, OUTPUT);
  digitalWrite(BUZZER_PIN, LOW);

  // Setup PWM channel untuk LED RGB
  ledcSetup(CH_LED1_R, PWM_FREQ, PWM_RES);
  ledcSetup(CH_LED1_G, PWM_FREQ, PWM_RES);
  ledcSetup(CH_LED1_B, PWM_FREQ, PWM_RES);
  ledcSetup(CH_LED2_R, PWM_FREQ, PWM_RES);
  ledcSetup(CH_LED2_G, PWM_FREQ, PWM_RES);
  ledcSetup(CH_LED2_B, PWM_FREQ, PWM_RES);

  ledcAttachPin(LED1_R, CH_LED1_R);
  ledcAttachPin(LED1_G, CH_LED1_G);
  ledcAttachPin(LED1_B, CH_LED1_B);
  ledcAttachPin(LED2_R, CH_LED2_R);
  ledcAttachPin(LED2_G, CH_LED2_G);
  ledcAttachPin(LED2_B, CH_LED2_B);

  // Matikan semua LED dulu
  setRGB(CH_LED1_R, CH_LED1_G, CH_LED1_B, 0, 0, 0);
  setRGB(CH_LED2_R, CH_LED2_G, CH_LED2_B, 0, 0, 0);

  // I2C
  Wire.begin(21, 22);
  Wire.setClock(100000);   // 100 kHz: lebih stabil untuk 3 device I2C
  delay(100);

  // OLED (init DULU agar pesan status bisa tampil)
  oled.begin();
  oled.clearBuffer();
  oled.setFont(u8g2_font_7x13B_tf);
  oled.drawStr(18, 22, "BATAM LOG");
  oled.setFont(u8g2_font_6x10_tf);
  oled.drawStr(8,  38, "Smart Berth System");
  oled.drawStr(12, 52, "Inisialisasi...");
  oled.sendBuffer();
  delay(1000);

   adsOK = ads.begin(0x48);
  if (!adsOK) {
    Serial.println("[ERROR] ADS1115 tidak ditemukan!");
  } else {
    ads.setGain(GAIN_ONE);
    Serial.println("[ADS1115] OK");
  }

  // RTC
  if (!rtc.begin()) {
    Serial.println(" ");
    oled.clearBuffer();
    oled.setFont(u8g2_font_6x10_tf);
    oled.drawStr(0, 30, " ");
    oled.sendBuffer();
    delay(2000);
  } else {
    if (rtc.lostPower()) {
      rtc.adjust(DateTime(F(__DATE__), F(__TIME__)));
    }
    Serial.println("[RTC] OK - " + getWaktu());
  }

  // Init state
  berth1 = {0, false, false, "", 0};
  berth2 = {0, false, false, "", 0};

  // Self test LED RGB: Merah -> Hijau -> Kuning -> Mati
  setRGB(CH_LED1_R, CH_LED1_G, CH_LED1_B, 255, 0, 0);
  setRGB(CH_LED2_R, CH_LED2_G, CH_LED2_B, 255, 0, 0);
  delay(400);
  setRGB(CH_LED1_R, CH_LED1_G, CH_LED1_B, 0, 255, 0);
  setRGB(CH_LED2_R, CH_LED2_G, CH_LED2_B, 0, 255, 0);
  delay(400);
  setRGB(CH_LED1_R, CH_LED1_G, CH_LED1_B, 255, 70, 0);
  setRGB(CH_LED2_R, CH_LED2_G, CH_LED2_B, 255, 70, 0);
  delay(400);
  setRGB(CH_LED1_R, CH_LED1_G, CH_LED1_B, 0, 0, 0);
  setRGB(CH_LED2_R, CH_LED2_G, CH_LED2_B, 0, 0, 0);

  // Koneksi WiFi
  connectWiFi();
  syncNTP();

  // Startup beep
  beep(1, 600, 0);

  Serial.println("[SYSTEM] Siap beroperasi.");
  Serial.println("======================================\n");
}

// ─────────────────────────────────────────────────────────────
//  LOOP
// ─────────────────────────────────────────────────────────────
void loop() {
  // 1. Update tide
  updateTide();

  // 2. Baca jarak sensor
  berth1.distanceCm = ukurJarak(TRIG1_PIN, ECHO1_PIN);
  delay(30);
  berth2.distanceCm = ukurJarak(TRIG2_PIN, ECHO2_PIN);

  // 3. Update status berth
  updateBerth(berth1, 1);
  updateBerth(berth2, 2);

  // 4. Kontrol LED RGB
  setLEDBerth(CH_LED1_R, CH_LED1_G, CH_LED1_B, berth1.isOccupied, tide.isSafe, tide.label);
  setLEDBerth(CH_LED2_R, CH_LED2_G, CH_LED2_B, berth2.isOccupied, tide.isSafe, tide.label);

  // 5. Buzzer warning surut setiap 10 detik
  if (millis() - lastBuzzerCheck >= BUZZER_INTERVAL) {
    lastBuzzerCheck = millis();
    if (!tide.isSafe) {
      beep(2, 150, 150);
    }
  }

  // 6. Update OLED setiap 2 detik
  if (millis() - lastOledUpdate >= OLED_INTERVAL) {
    lastOledUpdate = millis();
    tampilOLED();
    oledPage = (oledPage + 1) % 2;
  }

  // 7. Kirim data ke server setiap 5 detik
  if (millis() - lastSend >= SEND_INTERVAL) {
    lastSend = millis();
    if (WiFi.status() == WL_CONNECTED) {
      kirimData();
    } else {
      Serial.println("[WiFi] Terputus, reconnect...");
      connectWiFi();
    }
  }

  // 8. Serial Monitor
  Serial.println("--------------------------------------");
  Serial.println("[TIDE]    " + tide.label + " | ADC: " + String(tide.avgADC) + " | Kedalaman: " + tide.kedalaman + " (" + tide.kedalamanM + ") | Aman: " + (tide.isSafe ? "YA" : "TIDAK"));
  Serial.println("[BERTH 1] " + String(berth1.distanceCm, 1) + " cm | " + (berth1.isOccupied ? "OCCUPIED" : "AVAILABLE"));
  Serial.println("[BERTH 2] " + String(berth2.distanceCm, 1) + " cm | " + (berth2.isOccupied ? "OCCUPIED" : "AVAILABLE"));

  delay(300);
}

// ─────────────────────────────────────────────────────────────
//  FUNGSI: Ukur Jarak HC-SR04
// ─────────────────────────────────────────────────────────────
float ukurJarak(int trigPin, int echoPin) {
  const int N = 9;                 // ambil 9 sampel
  float v[N];
  int   valid = 0;

  for (int i = 0; i < N; i++) {
    digitalWrite(trigPin, LOW);
    delayMicroseconds(2);
    digitalWrite(trigPin, HIGH);
    delayMicroseconds(10);
    digitalWrite(trigPin, LOW);
    long dur = pulseIn(echoPin, HIGH, 30000);
    if (dur > 0) {
      float jarak = dur * 0.0343 / 2.0;
      if (jarak >= 2.0 && jarak <= 400.0) v[valid++] = jarak;  // buang di luar rentang HC-SR04
    }
    delay(10);
  }
  if (valid == 0) return 999.0;

  // Urutkan lalu ambil MEDIAN (lebih tahan outlier/pantulan ganda)
  for (int i = 0; i < valid - 1; i++)
    for (int j = i + 1; j < valid; j++)
      if (v[j] < v[i]) { float t = v[i]; v[i] = v[j]; v[j] = t; }

  return v[valid / 2];
}

// ─────────────────────────────────────────────────────────────
//  FUNGSI: Update Tide (Water Level Sensor Asli)
// ─────────────────────────────────────────────────────────────
void updateTide() {
  float adcRaw      = bacaADC();
  float kedalaman   = hitungKedalaman(adcRaw);
  float kedalamanM  = kedalaman;        // skala 1:100 → cm = meter nyata

  tide.avgADC = (int)adcRaw;
  tide.depthCm = kedalaman;    // simpan numerik untuk dikirim ke server
  tide.depthM  = kedalamanM;

  if (kedalaman < 1.5) {
    tide.label      = "SURUT";
    tide.isSafe     = false;
    tide.kedalaman  = String(kedalaman, 2) + " cm";
    tide.kedalamanM = String(kedalamanM, 2) + " m";
    tide.kapal      = "Berbahaya - jangan sandar";
  } else if (kedalaman < 3.0) {
    tide.label      = "NORMAL";
    tide.isSafe     = true;
    tide.kedalaman  = String(kedalaman, 2) + " cm";
    tide.kedalamanM = String(kedalamanM, 2) + " m";
    tide.kapal      = "Kapal kecil LCT < 500 ton";
  } else {
    tide.label      = "PASANG";
    tide.isSafe     = true;
    tide.kedalaman  = String(kedalaman, 2) + " cm";
    tide.kedalamanM = String(kedalamanM, 2) + " m";
    tide.kapal      = "LCT 500-1500 ton";
  }
}

// ─────────────────────────────────────────────────────────────
//  FUNGSI: Update Status Berth
// ─────────────────────────────────────────────────────────────
void updateBerth(BerthState &berth, int num) {
  bool nowOccupied = (berth.distanceCm <= DISTANCE_THRESHOLD);

  if (nowOccupied && !berth.prevOccupied) {
    berth.arrivalTime = getWaktu();
    berth.dockMillis  = millis();
    Serial.println("[BERTH " + String(num) + "] KAPAL TIBA - " + berth.arrivalTime);
    beep(3, 100, 100);
    if (WiFi.status() == WL_CONNECTED) logEvent(num, "ARRIVAL", berth);
  }

  if (!nowOccupied && berth.prevOccupied) {
    Serial.println("[BERTH " + String(num) + "] KAPAL BERANGKAT - Durasi: " + getDurasi(berth));
    if (WiFi.status() == WL_CONNECTED) logEvent(num, "DEPARTURE", berth);
    berth.arrivalTime = "";
    berth.dockMillis  = 0;
    beep(1, 600, 0);
  }

  berth.isOccupied   = nowOccupied;
  berth.prevOccupied = nowOccupied;
}

// ─────────────────────────────────────────────────────────────
//  FUNGSI: Set Warna RGB Mentah (PWM 0-255 per channel)
// ─────────────────────────────────────────────────────────────
void setRGB(int chR, int chG, int chB, int r, int g, int b) {
  ledcWrite(chR, r);
  ledcWrite(chG, g);
  ledcWrite(chB, b);
}

// ─────────────────────────────────────────────────────────────
//  FUNGSI: Kontrol LED RGB per Berth
//  - OCCUPIED          -> Merah   (255, 0, 0)
//  - AVAILABLE + SURUT  -> Kuning  (255, 70, 0)  [hasil kalibrasi]
//  - AVAILABLE + aman   -> Hijau   (0, 255, 0)
// ─────────────────────────────────────────────────────────────
void setLEDBerth(int chR, int chG, int chB, bool occupied, bool tideOk, String tideLabel) {
  if (occupied) {
    setRGB(chR, chG, chB, 255, 0, 0);          // Merah
  } else if (tideLabel == "SURUT") {
    setRGB(chR, chG, chB, 255, 70, 0);         // Kuning (kalibrasi)
  } else {
    setRGB(chR, chG, chB, 0, 255, 0);          // Hijau
  }
}

// ─────────────────────────────────────────────────────────────
//  FUNGSI: Tampilan OLED
// ─────────────────────────────────────────────────────────────
void tampilOLED() {
  oled.clearBuffer();
  oled.setFont(u8g2_font_6x10_tf);

  String rekomen;
  if      (tide.label == "PASANG") rekomen = "Aman sandar";
  else if (tide.label == "NORMAL") rekomen = "Aman sandar";
  else                             rekomen = "Jangan sandar";

  if (oledPage == 0) {
    oled.drawStr(0, 10, "BATAM LOG");
    String jam = getWaktu().substring(11, 19);
    oled.drawStr(70, 10, jam.c_str());
    oled.drawHLine(0, 13, 128);

    String tideStr = "Air: " + tide.label;
    oled.drawStr(0, 25, tideStr.c_str());
    oled.drawHLine(0, 28, 128);

    String b1 = "D1: " + String(berth1.isOccupied ? "OCCUPIED" : "AVAILABLE");
    oled.drawStr(0, 40, b1.c_str());
    String b1sub = berth1.isOccupied ? ("Sandar: " + getDurasi(berth1)) : rekomen;
    oled.drawStr(0, 50, b1sub.c_str());

    String b2 = "D2: " + String(berth2.isOccupied ? "OCCUPIED" : "AVAILABLE");
    oled.drawStr(0, 62, b2.c_str());

  } else {
    oled.drawStr(0, 10, "BATAM LOG");
    oled.drawHLine(0, 13, 128);

    String b2 = "D2: " + String(berth2.isOccupied ? "OCCUPIED" : "AVAILABLE");
    oled.drawStr(0, 25, b2.c_str());
    String b2sub = berth2.isOccupied ? ("Sandar: " + getDurasi(berth2)) : rekomen;
    oled.drawStr(0, 37, b2sub.c_str());
    oled.drawHLine(0, 42, 128);

    oled.setFont(u8g2_font_7x13B_tf);
    String jam = getWaktu().substring(11, 19);
    oled.drawStr(18, 58, jam.c_str());
  }

  oled.sendBuffer();
}

// ─────────────────────────────────────────────────────────────
//  FUNGSI: Kirim Data ke Server
// ─────────────────────────────────────────────────────────────
void kirimData() {
  HTTPClient http;
  http.begin(SERVER_URL);
  http.addHeader("Content-Type", "application/json");
  http.setTimeout(5000);

  StaticJsonDocument<512> doc;
  doc["timestamp"]   = getWaktu();
  doc["tide_adc"]    = tide.avgADC;
  doc["tide_status"] = tide.label;
  doc["tide_safe"]   = tide.isSafe;
  doc["tide_depth"]   = round(tide.depthCm * 100) / 100.0;  // cm, 2 desimal
  doc["tide_depth_m"] = round(tide.depthM  * 100) / 100.0;  // m, 2 desimal
  doc["tide_kapal"]   = tide.kapal;

  JsonObject b1 = doc.createNestedObject("berth1");
  b1["status"]        = berth1.isOccupied ? "OCCUPIED" : "AVAILABLE";
  b1["distance_cm"]   = berth1.distanceCm;
  b1["arrival_time"]  = berth1.arrivalTime;
  b1["dock_duration"] = berth1.isOccupied ? getDurasi(berth1) : "";

  JsonObject b2 = doc.createNestedObject("berth2");
  b2["status"]        = berth2.isOccupied ? "OCCUPIED" : "AVAILABLE";
  b2["distance_cm"]   = berth2.distanceCm;
  b2["arrival_time"]  = berth2.arrivalTime;
  b2["dock_duration"] = berth2.isOccupied ? getDurasi(berth2) : "";

  String payload;
  serializeJson(doc, payload);

  int code = http.POST(payload);
  Serial.println("[HTTP] Kirim data -> " + String(code));
  http.end();
}

// ─────────────────────────────────────────────────────────────
//  FUNGSI: Log Event Traffic
// ─────────────────────────────────────────────────────────────
void logEvent(int num, String eventType, BerthState &berth) {
  HTTPClient http;
  http.begin(LOG_URL);
  http.addHeader("Content-Type", "application/json");
  http.setTimeout(5000);

  StaticJsonDocument<256> doc;
  doc["berth_id"]      = num;
  doc["event_type"]    = eventType;
  doc["event_time"]    = getWaktu();
  doc["arrival_time"]  = berth.arrivalTime;
  doc["dock_duration"] = (eventType == "DEPARTURE") ? getDurasi(berth) : "";
  doc["tide_status"]   = tide.label;
  doc["tide_safe"]     = tide.isSafe;

  String payload;
  serializeJson(doc, payload);

  int code = http.POST(payload);
  Serial.println("[LOG] " + eventType + " Berth " + String(num) + " -> " + String(code));
  http.end();
}

// ─────────────────────────────────────────────────────────────
//  FUNGSI: Buzzer
// ─────────────────────────────────────────────────────────────
void beep(int count, int onMs, int offMs) {
  for (int i = 0; i < count; i++) {
    digitalWrite(BUZZER_PIN, HIGH);
    delay(onMs);
    digitalWrite(BUZZER_PIN, LOW);
    if (offMs > 0 && i < count - 1) delay(offMs);
  }
}

// ─────────────────────────────────────────────────────────────
//  FUNGSI: Ambil Waktu RTC
// ─────────────────────────────────────────────────────────────
String getWaktu() {
  DateTime now = rtc.now();
  char buf[25];
  sprintf(buf, "%02d/%02d/%04d %02d:%02d:%02d",
    now.day(), now.month(), now.year(),
    now.hour(), now.minute(), now.second());
  return String(buf);
}

// ─────────────────────────────────────────────────────────────
//  FUNGSI: Hitung Durasi Sandar
// ─────────────────────────────────────────────────────────────
String getDurasi(BerthState &berth) {
  if (berth.dockMillis == 0) return "0m";
  unsigned long elapsed = (millis() - berth.dockMillis) / 1000;
  unsigned long jam     = elapsed / 3600;
  unsigned long menit   = (elapsed % 3600) / 60;
  unsigned long detik   = elapsed % 60;
  char buf[16];
  if (jam > 0) sprintf(buf, "%luh%lum", jam, menit);
  else         sprintf(buf, "%lum%lus", menit, detik);
  return String(buf);
}

// ─────────────────────────────────────────────────────────────
//  FUNGSI: Koneksi WiFi
// ─────────────────────────────────────────────────────────────
void connectWiFi() {
  Serial.print("[WiFi] Menghubungkan ke " + String(WIFI_SSID));

  oled.clearBuffer();
  oled.setFont(u8g2_font_6x10_tf);
  oled.drawStr(0, 20, "Connecting WiFi...");
  oled.drawStr(0, 35, WIFI_SSID);
  oled.sendBuffer();

  WiFi.begin(WIFI_SSID, WIFI_PASSWORD);
  int attempt = 0;
  while (WiFi.status() != WL_CONNECTED && attempt < 20) {
    delay(500);
    Serial.print(".");
    attempt++;
  }

  if (WiFi.status() == WL_CONNECTED) {
    Serial.println("\n[WiFi] Terhubung! IP: " + WiFi.localIP().toString());
    oled.clearBuffer();
    oled.setFont(u8g2_font_6x10_tf);
    oled.drawStr(0, 20, "WiFi Terhubung!");
    oled.drawStr(0, 35, WiFi.localIP().toString().c_str());
    oled.sendBuffer();
    delay(1500);
  } else {
    Serial.println("\n[WiFi] GAGAL - Mode offline");
    oled.clearBuffer();
    oled.setFont(u8g2_font_6x10_tf);
    oled.drawStr(0, 20, "WiFi GAGAL");
    oled.drawStr(0, 35, "Mode Offline");
    oled.sendBuffer();
    delay(1500);
  }
}
void syncNTP() {
  Serial.println("[NTP] Sinkronisasi waktu...");
  configTime(7 * 3600, 0, "pool.ntp.org", "time.nist.gov"); // WIB = UTC+7

  struct tm timeinfo;
  int attempt = 0;
  while (!getLocalTime(&timeinfo) && attempt < 20) {
    delay(500);
    Serial.print(".");
    attempt++;
  }

  if (attempt >= 20) {
    Serial.println("\n[NTP] Gagal sync, pakai waktu RTC");
    return;
  }

  rtc.adjust(DateTime(
    timeinfo.tm_year + 1900,
    timeinfo.tm_mon  + 1,
    timeinfo.tm_mday,
    timeinfo.tm_hour,
    timeinfo.tm_min,
    timeinfo.tm_sec
  ));

  Serial.println("\n[NTP] Berhasil! Waktu: " + getWaktu());
}