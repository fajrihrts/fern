-- ============================================
-- FERN: Portal e-Registrasi Magang BPS PPU
-- Database Schema
-- ============================================

-- 1. TABEL USERS
CREATE TABLE IF NOT EXISTS users (
    id CHAR(36) PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('super_admin','admin','peserta') NOT NULL DEFAULT 'peserta',
    profile_photo VARCHAR(255) NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. TABEL REGISTRATIONS
CREATE TABLE IF NOT EXISTS registrations (
    id CHAR(36) PRIMARY KEY,
    user_id CHAR(36) NOT NULL,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    university VARCHAR(255) NOT NULL,
    major VARCHAR(255) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    proposal_file VARCHAR(255) NOT NULL,
    transcript_file VARCHAR(255) NULL,
    recommendation_letter_file VARCHAR(255) NULL,
    certificate_files JSON NULL,
    status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    admin_notes TEXT NULL,
    internship_status ENUM('not_started','ongoing','completed','terminated') NOT NULL DEFAULT 'not_started',
    actual_start_date DATE NULL,
    actual_end_date DATE NULL,
    termination_reason TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. TABEL ATTENDANCE_REPORTS
CREATE TABLE IF NOT EXISTS attendance_reports (
    id CHAR(36) PRIMARY KEY,
    user_id CHAR(36) NOT NULL,
    registration_id CHAR(36) NOT NULL,
    date DATE NOT NULL,
    status ENUM('hadir','izin','sakit','alpha') NOT NULL DEFAULT 'hadir',
    check_in TIME NULL,
    check_out TIME NULL,
    activities TEXT NULL,
    learning TEXT NULL,
    obstacles TEXT NULL,
    photo_proof VARCHAR(255) NULL,
    is_confirmed TINYINT(1) NOT NULL DEFAULT 0,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (registration_id) REFERENCES registrations(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_date (user_id, date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. TABEL POSTS
CREATE TABLE IF NOT EXISTS posts (
    id CHAR(36) PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    image VARCHAR(255) NULL,
    content LONGTEXT NOT NULL,
    is_published TINYINT(1) NOT NULL DEFAULT 0,
    published_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. TABEL TESTIMONIALS
CREATE TABLE IF NOT EXISTS testimonials (
    id CHAR(36) PRIMARY KEY,
    user_id CHAR(36) NULL,
    name VARCHAR(255) NOT NULL,
    campus VARCHAR(255) NULL,
    university VARCHAR(255) NULL,
    major VARCHAR(255) NULL,
    text TEXT NULL,
    content TEXT NULL,
    rating TINYINT NULL,
    image VARCHAR(255) NULL,
    is_published TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. TABEL HOLIDAYS
CREATE TABLE IF NOT EXISTS holidays (
    id INT AUTO_INCREMENT PRIMARY KEY,
    date DATE NOT NULL,
    name VARCHAR(255) NOT NULL,
    type ENUM('national','collective_leave','special') NOT NULL DEFAULT 'national',
    description TEXT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. TABEL SESSIONS
CREATE TABLE IF NOT EXISTS sessions (
    id VARCHAR(255) PRIMARY KEY,
    user_id CHAR(36) NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    payload LONGTEXT NOT NULL,
    last_activity INT NOT NULL,
    INDEX (user_id),
    INDEX (last_activity)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default super admin
INSERT INTO users (id, name, email, password, role, created_at, updated_at) 
VALUES (
    '00000000-0000-0000-0000-000000000001',
    'Super Admin',
    'admin@fern.test',
    '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5lk7QqZqvT1q2',
    'super_admin',
    NOW(),
    NOW()
) ON DUPLICATE KEY UPDATE email=email;
-- Password: password
