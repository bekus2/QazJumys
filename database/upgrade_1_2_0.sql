/*
 * Project: QazJumys
 * File: upgrade_1_2_0.sql
 * Author: Beck Sarbassov
 * Version: 1.2.0
 * Release Date: 2026-06-16
 * Last Updated: 2026-06-16
 * Copyright: © Beck Sarbassov. All rights reserved.
 *
 * EN: One-time upgrade script from v1.1.0 to v1.2.0 for unified accounts, owner tools, workflow, messaging, uploads, and notifications.
 * RU: Одноразовый upgrade-скрипт с v1.1.0 до v1.2.0 для единых аккаунтов, owner-инструментов, workflow, сообщений, файлов и уведомлений.
 */

USE qazjumys_portal;

ALTER TABLE users
    MODIFY role ENUM('member', 'owner', 'client', 'freelancer') NOT NULL DEFAULT 'member',
    ADD COLUMN IF NOT EXISTS status ENUM('active', 'blocked') NOT NULL DEFAULT 'active' AFTER role,
    ADD COLUMN IF NOT EXISTS password_reset_required TINYINT(1) NOT NULL DEFAULT 0 AFTER is_verified,
    ADD COLUMN IF NOT EXISTS blocked_reason VARCHAR(255) NULL AFTER password_reset_required,
    ADD COLUMN IF NOT EXISTS blocked_at DATETIME NULL AFTER blocked_reason,
    ADD COLUMN IF NOT EXISTS last_login_at DATETIME NULL AFTER blocked_at;

UPDATE users
SET role = 'member'
WHERE role IN ('client', 'freelancer');

ALTER TABLE projects
    ADD COLUMN IF NOT EXISTS accepted_proposal_id BIGINT UNSIGNED NULL AFTER category_id,
    ADD COLUMN IF NOT EXISTS assigned_freelancer_id BIGINT UNSIGNED NULL AFTER accepted_proposal_id,
    MODIFY status ENUM('open', 'in_progress', 'submitted', 'completed', 'cancelled') NOT NULL DEFAULT 'open',
    ADD COLUMN IF NOT EXISTS started_at DATETIME NULL AFTER status,
    ADD COLUMN IF NOT EXISTS submitted_at DATETIME NULL AFTER started_at,
    ADD COLUMN IF NOT EXISTS completed_at DATETIME NULL AFTER submitted_at,
    ADD COLUMN IF NOT EXISTS cancelled_at DATETIME NULL AFTER completed_at;

ALTER TABLE proposals
    MODIFY status ENUM('sent', 'shortlisted', 'accepted', 'declined', 'withdrawn', 'completed') NOT NULL DEFAULT 'sent',
    ADD COLUMN IF NOT EXISTS accepted_at DATETIME NULL AFTER status,
    ADD COLUMN IF NOT EXISTS declined_at DATETIME NULL AFTER accepted_at,
    ADD COLUMN IF NOT EXISTS completed_at DATETIME NULL AFTER declined_at;

CREATE TABLE IF NOT EXISTS messages (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id BIGINT UNSIGNED NOT NULL,
    proposal_id BIGINT UNSIGNED NULL,
    sender_id BIGINT UNSIGNED NOT NULL,
    receiver_id BIGINT UNSIGNED NOT NULL,
    body TEXT NOT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL,
    CONSTRAINT fk_messages_project FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    CONSTRAINT fk_messages_proposal FOREIGN KEY (proposal_id) REFERENCES proposals(id) ON DELETE SET NULL,
    CONSTRAINT fk_messages_sender FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_messages_receiver FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_messages_project_created (project_id, created_at),
    INDEX idx_messages_receiver_read (receiver_id, is_read)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS project_files (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id BIGINT UNSIGNED NOT NULL,
    proposal_id BIGINT UNSIGNED NULL,
    uploader_id BIGINT UNSIGNED NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    stored_name VARCHAR(255) NOT NULL,
    mime_type VARCHAR(120) NOT NULL,
    file_size BIGINT UNSIGNED NOT NULL,
    visibility ENUM('brief', 'proposal', 'delivery') NOT NULL DEFAULT 'brief',
    created_at DATETIME NOT NULL,
    CONSTRAINT fk_files_project FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    CONSTRAINT fk_files_proposal FOREIGN KEY (proposal_id) REFERENCES proposals(id) ON DELETE SET NULL,
    CONSTRAINT fk_files_uploader FOREIGN KEY (uploader_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_files_project_created (project_id, created_at),
    INDEX idx_files_uploader (uploader_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS complaints (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    reporter_id BIGINT UNSIGNED NOT NULL,
    reported_user_id BIGINT UNSIGNED NULL,
    project_id BIGINT UNSIGNED NULL,
    proposal_id BIGINT UNSIGNED NULL,
    subject VARCHAR(180) NOT NULL,
    body TEXT NOT NULL,
    status ENUM('open', 'reviewing', 'resolved', 'dismissed') NOT NULL DEFAULT 'open',
    owner_note TEXT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    CONSTRAINT fk_complaints_reporter FOREIGN KEY (reporter_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_complaints_reported FOREIGN KEY (reported_user_id) REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT fk_complaints_project FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE SET NULL,
    CONSTRAINT fk_complaints_proposal FOREIGN KEY (proposal_id) REFERENCES proposals(id) ON DELETE SET NULL,
    INDEX idx_complaints_status_created (status, created_at),
    INDEX idx_complaints_reported (reported_user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS notifications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    type VARCHAR(60) NOT NULL,
    title VARCHAR(180) NOT NULL,
    body TEXT NOT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    emailed_at DATETIME NULL,
    created_at DATETIME NOT NULL,
    CONSTRAINT fk_notifications_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_notifications_user_read (user_id, is_read, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS email_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    recipient_email VARCHAR(180) NOT NULL,
    subject VARCHAR(180) NOT NULL,
    body TEXT NOT NULL,
    status ENUM('queued', 'sent', 'failed') NOT NULL DEFAULT 'queued',
    error_message VARCHAR(255) NULL,
    created_at DATETIME NOT NULL,
    sent_at DATETIME NULL,
    CONSTRAINT fk_email_logs_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_email_logs_status_created (status, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS audit_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    owner_id BIGINT UNSIGNED NULL,
    action VARCHAR(80) NOT NULL,
    entity_type VARCHAR(80) NOT NULL,
    entity_id BIGINT UNSIGNED NULL,
    details TEXT NULL,
    created_at DATETIME NOT NULL,
    CONSTRAINT fk_audit_owner FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_audit_action_created (action, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO users (
    role, status, name, email, password_hash, city, headline, bio, skills, hourly_rate,
    rating, reviews_count, completed_projects, response_time, is_verified, password_reset_required, created_at, updated_at
)
VALUES (
    'owner', 'active', 'Beck Sarbassov', 'bek0435@gmail.com',
    '$2y$10$Ic0kEsuFv7cQaWnrOMbJFus/3nmKFXJJ2mo/taBBUCNRTJF01OHTW',
    'Қазақстан', 'QazJumys owner', 'Сайт иесі және негізгі әкімші аккаунты.',
    'owner, moderation, operations', NULL, 0.00, 0, 0, NULL, 1, 0, NOW(), NOW()
)
ON DUPLICATE KEY UPDATE
    role = 'owner',
    status = 'active',
    is_verified = 1,
    updated_at = NOW();
