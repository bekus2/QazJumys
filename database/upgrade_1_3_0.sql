/*
 * Project: QazJumys
 * File: upgrade_1_3_0.sql
 * Author: Beck Sarbassov
 * Version: 1.3.0
 * Release Date: 2026-06-21
 * Last Updated: 2026-06-21
 * Copyright: © Beck Sarbassov. All rights reserved.
 *
 * EN: One-time upgrade script from v1.2.0 to v1.3.0 for saves, searches, portfolios, milestones, reviews, verification, and project activity metrics.
 * RU: Одноразовый upgrade-скрипт с v1.2.0 до v1.3.0 для сохранений, поисков, портфолио, milestones, reviews, верификации и метрик активности проектов.
 */

USE qazjumys_portal;

ALTER TABLE projects
    ADD COLUMN IF NOT EXISTS views_count INT UNSIGNED NOT NULL DEFAULT 0 AFTER is_urgent,
    ADD COLUMN IF NOT EXISTS last_activity_at DATETIME NULL AFTER cancelled_at;

CREATE TABLE IF NOT EXISTS saved_projects (
    user_id BIGINT UNSIGNED NOT NULL,
    project_id BIGINT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL,
    PRIMARY KEY (user_id, project_id),
    CONSTRAINT fk_saved_projects_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_saved_projects_project FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    INDEX idx_saved_projects_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS saved_searches (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    label VARCHAR(140) NOT NULL,
    query_string VARCHAR(800) NOT NULL,
    created_at DATETIME NOT NULL,
    CONSTRAINT fk_saved_searches_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_saved_searches_user_created (user_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS project_milestones (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id BIGINT UNSIGNED NOT NULL,
    owner_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(180) NOT NULL,
    due_date DATE NULL,
    status ENUM('planned', 'done') NOT NULL DEFAULT 'planned',
    created_at DATETIME NOT NULL,
    completed_at DATETIME NULL,
    CONSTRAINT fk_milestones_project FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    CONSTRAINT fk_milestones_owner FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_milestones_project_status (project_id, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS reviews (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id BIGINT UNSIGNED NOT NULL,
    reviewer_id BIGINT UNSIGNED NOT NULL,
    reviewee_id BIGINT UNSIGNED NOT NULL,
    rating TINYINT UNSIGNED NOT NULL,
    comment TEXT NOT NULL,
    created_at DATETIME NOT NULL,
    CONSTRAINT fk_reviews_project FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    CONSTRAINT fk_reviews_reviewer FOREIGN KEY (reviewer_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_reviews_reviewee FOREIGN KEY (reviewee_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY uq_reviews_project_reviewer_reviewee (project_id, reviewer_id, reviewee_id),
    INDEX idx_reviews_reviewee_created (reviewee_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS portfolio_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(160) NOT NULL,
    description TEXT NOT NULL,
    url VARCHAR(255) NULL,
    skills VARCHAR(400) NULL,
    created_at DATETIME NOT NULL,
    CONSTRAINT fk_portfolio_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_portfolio_user_created (user_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS verification_requests (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    note TEXT NOT NULL,
    owner_note TEXT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    reviewed_at DATETIME NULL,
    CONSTRAINT fk_verification_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_verification_status_created (status, created_at),
    INDEX idx_verification_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

UPDATE projects
SET last_activity_at = COALESCE(last_activity_at, updated_at, created_at)
WHERE last_activity_at IS NULL;
