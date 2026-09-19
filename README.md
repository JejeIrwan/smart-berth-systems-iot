# 🚢 Smart Berth Monitoring System (IoT)

Sistem pemantauan dermaga pintar (*smart berth*) berbasis Internet of Things (IoT) yang mengintegrasikan mikrokontroler ESP32, sensor ultrasonik, modul RTC, dan display OLED dengan dashboard web berbasis PHP & MySQL untuk manajemen lalu lintas dan antrean sandar kapal logistik.

---

## 📌 Ringkasan Sistem
Proyek ini mengotomatisasi pemantauan status sandar kapal pada dermaga. Sistem mendeteksi keberadaan kapal dan mengukur jarak secara *real-time*, menyinkronkan waktu operasional melalui RTC, menampilkan status lokal melalui layar OLED dan LED indikator, serta mengirimkan data telemetri ke server pusat via REST API HTTP POST.

---

## 🛠️ Arsitektur & Teknologi

### Hardware / Firmware
* **Mikrokontroler:** ESP32 Development Board
* **Sensor & Modul:**
  * Sensor Ultrasonik (HC-SR04) – Deteksi keberadaan dan jarak kapal
  * Sensor Ketinggian Air (Water Level Sensor)
  * Modul RTC DS3231 – Sinkronisasi waktu operasional
  * Layar OLED SH1106 (I2C) – Tampilan status lokal
  * Bicolour LED – Indikator status ketersediaan dermaga
* **Bahasa & Lingkungan:** C/C++, Arduino IDE

### Software & Backend
* **Frontend Web:** HTML5, CSS3, JavaScript, Bootstrap / Tailwind CSS
* **Backend:** PHP (REST API endpoint & Session Management)
* **Database:** MySQL / MariaDB (Skema antrean, riwayat log lalu lintas, dan status dermaga)

---

## 📁 Struktur Repositori

```text
smart-berth-systems-iot/
├── SmartBerthSystems/           # Firmware ESP32 (.ino)
├── batamlog/
│   ├── api/                     # Endpoint REST API (auth, queue, logging, status)
│   ├── config/                  # Konfigurasi koneksi database (db.php)
│   ├── dashboard/               # Tampilan antarmuka dashboard monitoring web
│   └── sql/                     # File migrasi skema database (.sql)
└── README.md                    # Dokumentasi repositori