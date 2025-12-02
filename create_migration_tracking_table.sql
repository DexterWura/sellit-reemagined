-- SQL to create migration_tracking table
-- Run this SQL manually if the migration hasn't been run yet

CREATE TABLE IF NOT EXISTS `migration_tracking` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `migration_name` varchar(255) NOT NULL,
  `file_hash` varchar(64) DEFAULT NULL COMMENT 'SHA256 hash of migration file',
  `file_size` int(11) DEFAULT NULL,
  `file_modified_at` timestamp NULL DEFAULT NULL,
  `last_run_at` timestamp NULL DEFAULT NULL,
  `run_count` int(11) NOT NULL DEFAULT 0,
  `status` enum('pending','ran','modified','failed') NOT NULL DEFAULT 'pending',
  `error_message` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `migration_tracking_migration_name_unique` (`migration_name`),
  KEY `migration_tracking_status_index` (`status`),
  KEY `migration_tracking_migration_name_index` (`migration_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

