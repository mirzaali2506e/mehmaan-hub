-- Add price_period and price_per_day columns to properties (idempotent)
-- Run this ONLY if you imported mehmaan_hub.sql BEFORE these columns were added.
-- If you're importing mehmaan_hub.sql fresh, this migration is NOT needed.
SET @col1 = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'properties' AND COLUMN_NAME = 'price_period');
SET @sql1 = IF(@col1 = 0, 'ALTER TABLE properties ADD COLUMN price_period ENUM(\'per_day\',\'per_month\',\'both\') NOT NULL DEFAULT \'per_month\' AFTER price', 'SELECT 1');
PREPARE s1 FROM @sql1; EXECUTE s1; DEALLOCATE PREPARE s1;

SET @col2 = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'properties' AND COLUMN_NAME = 'price_per_day');
SET @sql2 = IF(@col2 = 0, 'ALTER TABLE properties ADD COLUMN price_per_day DECIMAL(10,2) DEFAULT NULL AFTER price_period', 'SELECT 1');
PREPARE s2 FROM @sql2; EXECUTE s2; DEALLOCATE PREPARE s2;
