-- ============================================================
--  BATAM LOG — Database Schema
--  Smart Berth Monitoring System
--  Compatible: MySQL 5.7+ / MariaDB 10.3+
-- ============================================================

CREATE DATABASE IF NOT EXISTS batamlog
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE batamlog;

-- ─────────────────────────────────────────────────────────────
--  TABLE: admin
--  Menyimpan akun admin / petugas dermaga
-- ─────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS admin (
  id            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  username      VARCHAR(50)     NOT NULL UNIQUE,
  password_hash VARCHAR(255)    NOT NULL,           -- bcrypt hash
  full_name     VARCHAR(100)    NOT NULL,
  role          ENUM('superadmin','operator')
                                NOT NULL DEFAULT 'operator',
  is_active     TINYINT(1)      NOT NULL DEFAULT 1,
  last_login    DATETIME            NULL,
  created_at    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB;

-- Default superadmin: username=admin | password=batamlog2024
-- Hash di-generate dengan password_hash('batamlog2024', PASSWORD_BCRYPT)
INSERT INTO admin (username, password_hash, full_name, role) VALUES
(
  'admin',
  '$2a$12$tn7hvm026YYz07zl/lubk.Mk9csMO5SVLc../ERJQ4eiQlQFzOpUO',
  'Super Administrator',
  'superadmin'
),
(
  'petugas1',
  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
  'Petugas Dermaga 1',
  'operator'
);

-- ─────────────────────────────────────────────────────────────
--  TABLE: berth
--  Data master dermaga (berth / pelabuhan)
-- ─────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS berth (
  id            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  berth_code    VARCHAR(10)     NOT NULL UNIQUE,     -- D1, D2
  berth_name    VARCHAR(100)    NOT NULL,
  description   VARCHAR(255)        NULL,
  is_active     TINYINT(1)      NOT NULL DEFAULT 1,
  created_at    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB;

INSERT INTO berth (berth_code, berth_name, description) VALUES
('D1', 'Terminal Berth 01', 'Dermaga utama sebelah barat'),
('D2', 'Terminal Berth 02', 'Dermaga utama sebelah timur');

-- ─────────────────────────────────────────────────────────────
--  TABLE: berth_status
--  Status realtime setiap dermaga (di-update tiap 5 detik dari ESP32)
-- ─────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS berth_status (
  id            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  berth_id      INT UNSIGNED    NOT NULL,
  status        ENUM('AVAILABLE','OCCUPIED')
                                NOT NULL DEFAULT 'AVAILABLE',
  distance_cm   DECIMAL(6,2)       NULL,             -- jarak HC-SR04
  tide_status   VARCHAR(30)        NULL,             -- PASANG / SURUT / dll
  tide_adc      INT                NULL,             -- nilai ADC water level
  tide_safe     TINYINT(1)         NULL,             -- 1=aman, 0=tidak aman
  arrival_time  DATETIME           NULL,             -- waktu kapal tiba
  dock_duration VARCHAR(20)        NULL,             -- durasi sandar (string)
  updated_at    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP
                                ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  FOREIGN KEY (berth_id) REFERENCES berth(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Inisialisasi status awal kedua dermaga
INSERT INTO berth_status (berth_id, status, distance_cm) VALUES
(1, 'AVAILABLE', 999.00),
(2, 'AVAILABLE', 999.00);

-- ─────────────────────────────────────────────────────────────
--  TABLE: tide_log
--  Log kondisi pasang surut air laut
-- ─────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS tide_log (
  id            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  tide_adc      INT             NOT NULL,
  tide_status   VARCHAR(30)     NOT NULL,
  tide_safe     TINYINT(1)      NOT NULL,
  tide_depth    DECIMAL(6,2)        NULL,             -- kedalaman air (cm)
  tide_depth_m  DECIMAL(5,2)        NULL,             -- kedalaman air (m)
  tide_kapal    VARCHAR(60)         NULL,             -- rekomendasi kapal
  recorded_at   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  INDEX idx_recorded_at (recorded_at)
) ENGINE=InnoDB;

-- ─────────────────────────────────────────────────────────────
--  TABLE: traffic_log
--  Log kedatangan dan keberangkatan kapal (history lengkap)
-- ─────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS traffic_log (
  id            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  berth_id      INT UNSIGNED    NOT NULL,
  event_type    ENUM('ARRIVAL','DEPARTURE')
                                NOT NULL,
  event_time    DATETIME        NOT NULL,
  arrival_time  DATETIME            NULL,
  departure_time DATETIME           NULL,
  dock_duration VARCHAR(20)         NULL,             -- contoh: "2h30m"
  dock_seconds  INT UNSIGNED        NULL,             -- total detik sandar
  tide_status   VARCHAR(30)         NULL,
  tide_safe     TINYINT(1)          NULL,
  condition     ENUM('NORMAL','SURUT_WARNING','FORCED')
                                NOT NULL DEFAULT 'NORMAL',
  created_at    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  FOREIGN KEY (berth_id) REFERENCES berth(id) ON DELETE CASCADE,
  INDEX idx_event_time  (event_time),
  INDEX idx_berth_event (berth_id, event_type)
) ENGINE=InnoDB;

-- ─────────────────────────────────────────────────────────────
--  VIEW: v_berth_overview
--  Digunakan dashboard untuk tampilkan status semua dermaga
-- ─────────────────────────────────────────────────────────────
CREATE OR REPLACE VIEW v_berth_overview AS
SELECT
  b.id            AS berth_id,
  b.berth_code,
  b.berth_name,
  bs.status,
  bs.distance_cm,
  bs.tide_status,
  bs.tide_adc,
  bs.tide_safe,
  bs.arrival_time,
  bs.dock_duration,
  bs.updated_at
FROM berth b
LEFT JOIN berth_status bs ON bs.berth_id = b.id
WHERE b.is_active = 1;

-- ─────────────────────────────────────────────────────────────
--  VIEW: v_traffic_history
--  History traffic dengan nama dermaga
-- ─────────────────────────────────────────────────────────────
CREATE OR REPLACE VIEW v_traffic_history AS
SELECT
  tl.id,
  b.berth_code,
  b.berth_name,
  tl.event_type,
  tl.event_time,
  tl.arrival_time,
  tl.departure_time,
  tl.dock_duration,
  tl.tide_status,
  tl.tide_safe,
  tl.condition,
  tl.created_at
FROM traffic_log tl
JOIN berth b ON b.id = tl.berth_id
ORDER BY tl.event_time DESC;
