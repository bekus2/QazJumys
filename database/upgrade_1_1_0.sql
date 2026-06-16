/*
 * Project: QazJumys
 * File: upgrade_1_1_0.sql
 * Author: Beck Sarbassov
 * Version: 1.1.0
 * Release Date: 2026-06-16
 * Last Updated: 2026-06-16
 * Copyright: © Beck Sarbassov. All rights reserved.
 *
 * EN: Optional upgrade script for databases created from version 1.0.0.
 * RU: Дополнительный скрипт обновления для баз, созданных из версии 1.0.0.
 */

USE qazjumys_portal;

ALTER TABLE users
    ADD COLUMN headline VARCHAR(160) NULL AFTER city,
    ADD COLUMN hourly_rate DECIMAL(12,2) NULL AFTER skills,
    ADD COLUMN rating DECIMAL(3,2) NOT NULL DEFAULT 0.00 AFTER hourly_rate,
    ADD COLUMN reviews_count INT UNSIGNED NOT NULL DEFAULT 0 AFTER rating,
    ADD COLUMN completed_projects INT UNSIGNED NOT NULL DEFAULT 0 AFTER reviews_count,
    ADD COLUMN response_time VARCHAR(40) NULL AFTER completed_projects,
    ADD COLUMN is_verified TINYINT(1) NOT NULL DEFAULT 0 AFTER response_time,
    ADD INDEX idx_users_role_rating (role, rating);

ALTER TABLE projects
    ADD COLUMN project_type ENUM('fixed', 'hourly') NOT NULL DEFAULT 'fixed' AFTER description,
    ADD COLUMN experience_level ENUM('entry', 'intermediate', 'expert') NOT NULL DEFAULT 'intermediate' AFTER project_type,
    ADD COLUMN skills VARCHAR(500) NULL AFTER experience_level,
    ADD COLUMN location VARCHAR(80) NULL AFTER skills,
    ADD COLUMN is_remote TINYINT(1) NOT NULL DEFAULT 1 AFTER location,
    ADD COLUMN is_featured TINYINT(1) NOT NULL DEFAULT 0 AFTER is_remote,
    ADD COLUMN is_urgent TINYINT(1) NOT NULL DEFAULT 0 AFTER is_featured,
    ADD INDEX idx_projects_featured_status (is_featured, status, created_at),
    ADD INDEX idx_projects_budget_status (status, budget_max),
    ADD INDEX idx_projects_type_level (project_type, experience_level);

ALTER TABLE projects
    DROP INDEX ft_projects_title_description,
    ADD FULLTEXT INDEX ft_projects_title_description_skills (title, description, skills);
