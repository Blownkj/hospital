-- ── Таблица анализов ──────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `lab_tests` (
    `id`           INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `name`         VARCHAR(255)  NOT NULL,
    `category`     VARCHAR(100)  NOT NULL COMMENT 'Группа анализов',
    `description`  TEXT,
    `preparation`  TEXT          COMMENT 'Как подготовиться',
    `price`        DECIMAL(10,2) NOT NULL,
    `duration_min` TINYINT UNSIGNED NOT NULL DEFAULT 15,
    PRIMARY KEY (`id`),
    INDEX `idx_lab_tests_category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Изменяем appointments: doctor_id может быть NULL для анализов ─────────
ALTER TABLE `appointments`
    ADD COLUMN `appointment_type` ENUM('doctor','lab_test')
        NOT NULL DEFAULT 'doctor'
        AFTER `patient_id`,
    ADD COLUMN `lab_test_id` INT UNSIGNED NULL
        AFTER `doctor_id`,
    MODIFY COLUMN `doctor_id` INT UNSIGNED NULL,
    ADD CONSTRAINT `fk_appt_lab_test`
        FOREIGN KEY (`lab_test_id`) REFERENCES `lab_tests`(`id`) ON DELETE SET NULL;