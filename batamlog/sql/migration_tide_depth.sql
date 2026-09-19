-- ============================================================
--  MIGRASI: Tambah kolom kedalaman air ke tide_log
--  Jalankan HANYA jika database sudah ada & berisi data
--  (kalau import batamlog_db.sql dari awal, tidak perlu ini).
--  Aman dijalankan berulang di MySQL 8+ / MariaDB 10.3+
-- ============================================================

ALTER TABLE tide_log
  ADD COLUMN IF NOT EXISTS tide_depth   DECIMAL(6,2) NULL AFTER tide_safe,
  ADD COLUMN IF NOT EXISTS tide_depth_m DECIMAL(5,2) NULL AFTER tide_depth,
  ADD COLUMN IF NOT EXISTS tide_kapal   VARCHAR(60)  NULL AFTER tide_depth_m;

-- Jika hosting menolak "IF NOT EXISTS" pada ADD COLUMN (MySQL versi lama),
-- pakai versi manual di bawah (hapus komentar), jalankan satu-satu:
-- ALTER TABLE tide_log ADD COLUMN tide_depth   DECIMAL(6,2) NULL AFTER tide_safe;
-- ALTER TABLE tide_log ADD COLUMN tide_depth_m DECIMAL(5,2) NULL AFTER tide_depth;
-- ALTER TABLE tide_log ADD COLUMN tide_kapal   VARCHAR(60)  NULL AFTER tide_depth_m;
