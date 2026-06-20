/*
 * Project: QazJumys
 * File: schema.sql
 * Author: Beck Sarbassov
 * Version: 1.3.0
 * Release Date: 2026-06-16
 * Last Updated: 2026-06-16
 * Copyright: © Beck Sarbassov. All rights reserved.
 *
 * EN: MySQL schema for unified accounts, projects, proposals, saves, searches, portfolios, milestones, reviews, messaging, uploads, complaints, notifications, verification, and owner tools.
 * RU: MySQL-схема для единых аккаунтов, проектов, откликов, сохранений, поисков, портфолио, milestones, reviews, сообщений, файлов, жалоб, уведомлений, верификации и owner-инструментов.
 */

CREATE DATABASE IF NOT EXISTS qazjumys_portal
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE qazjumys_portal;

CREATE TABLE IF NOT EXISTS users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    role ENUM('member', 'owner', 'client', 'freelancer') NOT NULL DEFAULT 'member',
    status ENUM('active', 'blocked') NOT NULL DEFAULT 'active',
    name VARCHAR(120) NOT NULL,
    email VARCHAR(180) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    city VARCHAR(80) NULL,
    headline VARCHAR(160) NULL,
    bio TEXT NULL,
    skills TEXT NULL,
    hourly_rate DECIMAL(12,2) NULL,
    rating DECIMAL(3,2) NOT NULL DEFAULT 0.00,
    reviews_count INT UNSIGNED NOT NULL DEFAULT 0,
    completed_projects INT UNSIGNED NOT NULL DEFAULT 0,
    response_time VARCHAR(40) NULL,
    is_verified TINYINT(1) NOT NULL DEFAULT 0,
    password_reset_required TINYINT(1) NOT NULL DEFAULT 0,
    blocked_reason VARCHAR(255) NULL,
    blocked_at DATETIME NULL,
    last_login_at DATETIME NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    INDEX idx_users_role (role),
    INDEX idx_users_status (status),
    INDEX idx_users_city (city),
    INDEX idx_users_role_rating (role, rating)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS categories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(80) NOT NULL UNIQUE,
    name VARCHAR(140) NOT NULL,
    description VARCHAR(255) NOT NULL,
    accent_color CHAR(7) NOT NULL DEFAULT '#06b6d4',
    sort_order INT UNSIGNED NOT NULL DEFAULT 100,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    INDEX idx_categories_active_sort (is_active, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS projects (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    client_id BIGINT UNSIGNED NOT NULL,
    category_id BIGINT UNSIGNED NOT NULL,
    accepted_proposal_id BIGINT UNSIGNED NULL,
    assigned_freelancer_id BIGINT UNSIGNED NULL,
    title VARCHAR(180) NOT NULL,
    description TEXT NOT NULL,
    project_type ENUM('fixed', 'hourly') NOT NULL DEFAULT 'fixed',
    experience_level ENUM('entry', 'intermediate', 'expert') NOT NULL DEFAULT 'intermediate',
    skills VARCHAR(500) NULL,
    location VARCHAR(80) NULL,
    is_remote TINYINT(1) NOT NULL DEFAULT 1,
    is_featured TINYINT(1) NOT NULL DEFAULT 0,
    is_urgent TINYINT(1) NOT NULL DEFAULT 0,
    views_count INT UNSIGNED NOT NULL DEFAULT 0,
    budget_min DECIMAL(12,2) NOT NULL DEFAULT 0,
    budget_max DECIMAL(12,2) NOT NULL DEFAULT 0,
    deadline_days INT UNSIGNED NOT NULL DEFAULT 7,
    status ENUM('open', 'in_progress', 'submitted', 'completed', 'cancelled') NOT NULL DEFAULT 'open',
    started_at DATETIME NULL,
    submitted_at DATETIME NULL,
    completed_at DATETIME NULL,
    cancelled_at DATETIME NULL,
    last_activity_at DATETIME NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    CONSTRAINT fk_projects_client FOREIGN KEY (client_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_projects_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT,
    CONSTRAINT fk_projects_assigned_freelancer FOREIGN KEY (assigned_freelancer_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_projects_status_created (status, created_at),
    INDEX idx_projects_featured_status (is_featured, status, created_at),
    INDEX idx_projects_budget_status (status, budget_max),
    INDEX idx_projects_type_level (project_type, experience_level),
    INDEX idx_projects_category_status (category_id, status),
    INDEX idx_projects_assigned_status (assigned_freelancer_id, status),
    INDEX idx_projects_views_status (status, views_count),
    INDEX idx_projects_activity (last_activity_at),
    FULLTEXT INDEX ft_projects_title_description_skills (title, description, skills)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS proposals (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id BIGINT UNSIGNED NOT NULL,
    freelancer_id BIGINT UNSIGNED NOT NULL,
    cover_letter TEXT NOT NULL,
    bid_amount DECIMAL(12,2) NOT NULL,
    delivery_days INT UNSIGNED NOT NULL DEFAULT 7,
    status ENUM('sent', 'shortlisted', 'accepted', 'declined', 'withdrawn', 'completed') NOT NULL DEFAULT 'sent',
    accepted_at DATETIME NULL,
    declined_at DATETIME NULL,
    completed_at DATETIME NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    CONSTRAINT fk_proposals_project FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    CONSTRAINT fk_proposals_freelancer FOREIGN KEY (freelancer_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY uq_proposals_project_freelancer (project_id, freelancer_id),
    INDEX idx_proposals_freelancer_created (freelancer_id, created_at),
    INDEX idx_proposals_project_status (project_id, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
