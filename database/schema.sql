/*
 * Project: QazJumys
 * File: schema.sql
 * Author: Beck Sarbassov
 * Version: 1.1.0
 * Release Date: 2026-06-16
 * Last Updated: 2026-06-16
 * Copyright: © Beck Sarbassov. All rights reserved.
 *
 * EN: MySQL schema for users, categories, rich projects, and proposals.
 * RU: MySQL-схема для пользователей, категорий, расширенных проектов и откликов.
 */

CREATE DATABASE IF NOT EXISTS qazjumys_portal
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE qazjumys_portal;

CREATE TABLE IF NOT EXISTS users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    role ENUM('client', 'freelancer') NOT NULL,
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
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    INDEX idx_users_role (role),
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
    title VARCHAR(180) NOT NULL,
    description TEXT NOT NULL,
    project_type ENUM('fixed', 'hourly') NOT NULL DEFAULT 'fixed',
    experience_level ENUM('entry', 'intermediate', 'expert') NOT NULL DEFAULT 'intermediate',
    skills VARCHAR(500) NULL,
    location VARCHAR(80) NULL,
    is_remote TINYINT(1) NOT NULL DEFAULT 1,
    is_featured TINYINT(1) NOT NULL DEFAULT 0,
    is_urgent TINYINT(1) NOT NULL DEFAULT 0,
    budget_min DECIMAL(12,2) NOT NULL DEFAULT 0,
    budget_max DECIMAL(12,2) NOT NULL DEFAULT 0,
    deadline_days INT UNSIGNED NOT NULL DEFAULT 7,
    status ENUM('open', 'in_progress', 'completed', 'cancelled') NOT NULL DEFAULT 'open',
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    CONSTRAINT fk_projects_client FOREIGN KEY (client_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_projects_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT,
    INDEX idx_projects_status_created (status, created_at),
    INDEX idx_projects_featured_status (is_featured, status, created_at),
    INDEX idx_projects_budget_status (status, budget_max),
    INDEX idx_projects_type_level (project_type, experience_level),
    INDEX idx_projects_category_status (category_id, status),
    FULLTEXT INDEX ft_projects_title_description_skills (title, description, skills)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS proposals (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id BIGINT UNSIGNED NOT NULL,
    freelancer_id BIGINT UNSIGNED NOT NULL,
    cover_letter TEXT NOT NULL,
    bid_amount DECIMAL(12,2) NOT NULL,
    delivery_days INT UNSIGNED NOT NULL DEFAULT 7,
    status ENUM('sent', 'shortlisted', 'accepted', 'declined') NOT NULL DEFAULT 'sent',
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    CONSTRAINT fk_proposals_project FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    CONSTRAINT fk_proposals_freelancer FOREIGN KEY (freelancer_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY uq_proposals_project_freelancer (project_id, freelancer_id),
    INDEX idx_proposals_freelancer_created (freelancer_id, created_at),
    INDEX idx_proposals_project_status (project_id, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
