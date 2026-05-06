-- ============================================================
-- Hospital IS — Migrations (MySQL / XAMPP)
-- Запускать: phpMyAdmin → hospital_is → Импорт → этот файл
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ------------------------------------------------------------
-- 1. specializations (независимая, справочник)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `specializations` (
    `id`          INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `name`        VARCHAR(100)     NOT NULL,
    `description` TEXT,
    `image_url`   VARCHAR(500)     NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 2. users (независимая, единая авторизация для всех ролей)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
    `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `email`         VARCHAR(255) NOT NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `role`          ENUM('patient','doctor','admin') NOT NULL DEFAULT 'patient',
    `created_at`    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_users_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 3. patients (зависит от users)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `patients` (
    `id`                INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`           INT UNSIGNED NOT NULL,
    `full_name`         VARCHAR(255) NOT NULL,
    `birth_date`        DATE         NOT NULL,
    `phone`             VARCHAR(20),
    `gender`            ENUM('m','f','other') NOT NULL,
    `address`           TEXT,
    `chronic_diseases`  TEXT
        COMMENT 'Хронические заболевания — видны врачу при открытии приёма',
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_patients_user_id` (`user_id`),
    CONSTRAINT `fk_patients_user`
        FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 4. doctors (зависит от users, specializations)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `doctors` (
    `id`                INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`           INT UNSIGNED NOT NULL,
    `full_name`         VARCHAR(255) NOT NULL,
    `specialization_id` INT UNSIGNED NOT NULL,
    `bio`               TEXT,
    `photo_url`         VARCHAR(500),
    `is_active`         TINYINT(1)   NOT NULL DEFAULT 1,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_doctors_user_id` (`user_id`),
    INDEX `idx_doctors_spec` (`specialization_id`),
    CONSTRAINT `fk_doctors_user`
        FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_doctors_spec`
        FOREIGN KEY (`specialization_id`) REFERENCES `specializations`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 5. schedules (зависит от doctors)
--    day_of_week: 1=Пн, 2=Вт, 3=Ср, 4=Чт, 5=Пт, 6=Сб, 7=Вс
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `schedules` (
    `id`               INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `doctor_id`        INT UNSIGNED NOT NULL,
    `day_of_week`      TINYINT UNSIGNED NOT NULL COMMENT '1=Пн..7=Вс',
    `start_time`       TIME NOT NULL,
    `end_time`         TIME NOT NULL,
    `slot_duration_min` TINYINT UNSIGNED NOT NULL DEFAULT 30,
    PRIMARY KEY (`id`),
    INDEX `idx_schedules_doctor` (`doctor_id`),
    CONSTRAINT `fk_schedules_doctor`
        FOREIGN KEY (`doctor_id`) REFERENCES `doctors`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 6. schedule_exceptions (зависит от doctors)
--    Отпуска, нерабочие дни, особые расписания
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `schedule_exceptions` (
    `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `doctor_id`      INT UNSIGNED NOT NULL,
    `exception_date` DATE         NOT NULL,
    `is_day_off`     TINYINT(1)   NOT NULL DEFAULT 1,
    `note`           VARCHAR(255),
    PRIMARY KEY (`id`),
    INDEX `idx_sch_exc_doctor_date` (`doctor_id`, `exception_date`),
    CONSTRAINT `fk_sch_exc_doctor`
        FOREIGN KEY (`doctor_id`) REFERENCES `doctors`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 7. services (зависит от specializations)
--    Прайс-лист клиники
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `services` (
    `id`                INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    `name`              VARCHAR(255)   NOT NULL,
    `price`             DECIMAL(10,2)  NOT NULL,
    `specialization_id` INT UNSIGNED,
    `description`       TEXT,
    `image_url`         VARCHAR(500)   NULL,
    PRIMARY KEY (`id`),
    INDEX `idx_services_spec` (`specialization_id`),
    CONSTRAINT `fk_services_spec`
        FOREIGN KEY (`specialization_id`) REFERENCES `specializations`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 8. lab_tests (справочник анализов)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `lab_tests` (
    `id`           INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    `name`         VARCHAR(255)   NOT NULL,
    `category`     VARCHAR(100)   NOT NULL DEFAULT 'Общее',
    `description`  TEXT,
    `preparation`  TEXT,
    `price`        DECIMAL(10,2)  NOT NULL DEFAULT 0.00,
    `duration_min` TINYINT UNSIGNED NOT NULL DEFAULT 30,
    PRIMARY KEY (`id`),
    INDEX `idx_lab_tests_category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 9. appointments (зависит от patients, doctors)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `appointments` (
    `id`               INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `patient_id`       INT UNSIGNED NOT NULL,
    `appointment_type` ENUM('doctor','lab_test') NOT NULL DEFAULT 'doctor',
    `doctor_id`        INT UNSIGNED NULL,
    `lab_test_id`      INT UNSIGNED NULL,
    `scheduled_at`     DATETIME     NOT NULL,
    `status`           ENUM('pending','confirmed','in_progress','completed','cancelled')
                       NOT NULL DEFAULT 'pending',
    `created_at`       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_appt_patient`          (`patient_id`),
    INDEX `idx_appt_doctor`           (`doctor_id`),
    INDEX `idx_appt_scheduled`        (`scheduled_at`),
    INDEX `idx_appt_status_scheduled` (`status`, `scheduled_at`),
    INDEX `idx_appt_doctor_status`    (`doctor_id`, `status`),
    INDEX `idx_appt_patient_status`   (`patient_id`, `status`),
    CONSTRAINT `fk_appt_patient`
        FOREIGN KEY (`patient_id`) REFERENCES `patients`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_appt_doctor`
        FOREIGN KEY (`doctor_id`) REFERENCES `doctors`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_appt_lab_test`
        FOREIGN KEY (`lab_test_id`) REFERENCES `lab_tests`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 10. visits (зависит от appointments, связь 1:1)
--    Протокол приёма — создаётся когда врач нажимает "Начать приём"
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `visits` (
    `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `appointment_id` INT UNSIGNED NOT NULL,
    `started_at`     DATETIME     NOT NULL,
    `ended_at`       DATETIME,
    `complaints`     TEXT,
    `examination`    TEXT,
    `diagnosis`      TEXT,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_visits_appointment` (`appointment_id`),
    CONSTRAINT `fk_visits_appointment`
        FOREIGN KEY (`appointment_id`) REFERENCES `appointments`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 11. prescriptions (зависит от visits)
--     Назначения врача: препараты, процедуры, направления
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `prescriptions` (
    `id`       INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `visit_id` INT UNSIGNED NOT NULL,
    `type`     ENUM('drug','procedure','referral') NOT NULL,
    `name`     VARCHAR(255) NOT NULL,
    `dosage`   VARCHAR(100),
    `notes`    TEXT,
    PRIMARY KEY (`id`),
    INDEX `idx_prescriptions_visit` (`visit_id`),
    CONSTRAINT `fk_prescriptions_visit`
        FOREIGN KEY (`visit_id`) REFERENCES `visits`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 12. reviews (зависит от patients, doctors, appointments)
--     Отзыв можно оставить только после завершённого приёма
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `reviews` (
    `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `patient_id`     INT UNSIGNED NOT NULL,
    `doctor_id`      INT UNSIGNED NOT NULL,
    `appointment_id` INT UNSIGNED NOT NULL,
    `rating`         TINYINT UNSIGNED NOT NULL COMMENT '1–5',
    `review_text`    TEXT,
    `is_approved`    TINYINT(1) NOT NULL DEFAULT 0,
    `admin_reply`    TEXT,
    `admin_reply_at` TIMESTAMP NULL DEFAULT NULL,
    `created_at`     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_reviews_appointment`      (`appointment_id`),
    INDEX `idx_reviews_doctor`               (`doctor_id`),
    INDEX `idx_reviews_doctor_approved`      (`doctor_id`, `is_approved`),
    INDEX `idx_reviews_patient`              (`patient_id`),
    CONSTRAINT `fk_reviews_patient`
        FOREIGN KEY (`patient_id`) REFERENCES `patients`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_reviews_doctor`
        FOREIGN KEY (`doctor_id`) REFERENCES `doctors`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_reviews_appointment`
        FOREIGN KEY (`appointment_id`) REFERENCES `appointments`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 13. articles (независимая, статьи о здоровье)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `articles` (
    `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `slug`         VARCHAR(255) NOT NULL,
    `title`        VARCHAR(255) NOT NULL,
    `excerpt`      TEXT         NOT NULL,
    `body`         LONGTEXT     NOT NULL,
    `category`     VARCHAR(100) NOT NULL DEFAULT 'Общее',
    `read_time`    TINYINT UNSIGNED NOT NULL DEFAULT 3 COMMENT 'минуты',
    `published_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `is_published` TINYINT(1) NOT NULL DEFAULT 1,
    `author_id`    INT UNSIGNED NULL DEFAULT NULL,
    `image_url`    VARCHAR(512) NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_articles_slug` (`slug`),
    INDEX `idx_articles_category` (`category`),
    INDEX `idx_articles_published` (`is_published`),
    CONSTRAINT `fk_articles_author` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- P2.7: CHECK constraints
-- ------------------------------------------------------------
ALTER TABLE `reviews`
    ADD CONSTRAINT IF NOT EXISTS `chk_reviews_rating`
        CHECK (`rating` BETWEEN 1 AND 5);

ALTER TABLE `schedules`
    ADD CONSTRAINT IF NOT EXISTS `chk_schedules_times`
        CHECK (`start_time` < `end_time`);

-- ------------------------------------------------------------
-- P0.3: защита от race condition при бронировании
-- Generated column содержит scheduled_at для активных записей и NULL для
-- отменённых — уникальный индекс игнорирует NULL, поэтому у одного врача
-- не может быть двух активных записей на одно время.
-- Безопасно запускать повторно (IF NOT EXISTS).
-- ------------------------------------------------------------
ALTER TABLE `appointments`
    ADD COLUMN IF NOT EXISTS `active_slot` DATETIME GENERATED ALWAYS AS (
        IF(`status` <> 'cancelled', `scheduled_at`, NULL)
    ) STORED COMMENT 'Generated: NULL for cancelled, scheduled_at for active — used by unique index';

ALTER TABLE `appointments`
    DROP INDEX IF EXISTS `uq_appt_doctor_active_slot`;

ALTER TABLE `appointments`
    ADD UNIQUE KEY `uq_appt_doctor_active_slot` (`doctor_id`, `active_slot`);

-- ------------------------------------------------------------
-- Migrate existing databases (safe to run on fresh install too)
-- ------------------------------------------------------------
ALTER TABLE `doctors`
    ADD COLUMN IF NOT EXISTS `is_active` TINYINT(1) NOT NULL DEFAULT 1;

ALTER TABLE `specializations`
    ADD COLUMN IF NOT EXISTS `image_url` VARCHAR(500) NULL;

ALTER TABLE `articles`
    ADD COLUMN IF NOT EXISTS `is_published` TINYINT(1) NOT NULL DEFAULT 1,
    ADD COLUMN IF NOT EXISTS `author_id`    INT UNSIGNED NULL DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS `image_url`    VARCHAR(512) NULL DEFAULT NULL;

SET FOREIGN_KEY_CHECKS = 1;