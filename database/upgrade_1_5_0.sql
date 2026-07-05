/*
 * Project: QazJumys
 * File: upgrade_1_5_0.sql
 * Author: Beck Sarbassov
 * Version: 1.5.0
 * Release Date: 2026-07-05
 * Last Updated: 2026-07-05
 * Copyright: © Beck Sarbassov. All rights reserved.
 *
 * EN: One-time upgrade script from v1.4.0 to v1.5.0 for login throttling.
 * RU: Одноразовый upgrade-скрипт с v1.4.0 до v1.5.0 для ограничения попыток входа.
 *
 * EN: Note: v1.5.0 removes the seeded owner account from seed.sql. Create the owner with: php bin/create_owner.php
 * RU: Примечание: v1.5.0 убирает owner-аккаунт из seed.sql. Создайте владельца командой: php bin/create_owner.php
 */

USE qazjumys_portal;

CREATE TABLE IF NOT EXISTS login_attempts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(180) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    succeeded TINYINT(1) NOT NULL DEFAULT 0,
    attempted_at DATETIME NOT NULL,
    INDEX idx_login_attempts_email_time (email, attempted_at),
    INDEX idx_login_attempts_ip_time (ip_address, attempted_at),
    INDEX idx_login_attempts_time (attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
