-- ============================================
-- SQL Commands to Add Social Media Verification Setting
-- ============================================
-- Run these commands to add the social media verification flag
-- and create the social_media_verifications table

-- 1. Add the require_social_media_verification setting to marketplace_settings table
INSERT INTO `marketplace_settings` (`key`, `value`, `created_at`, `updated_at`) 
VALUES ('require_social_media_verification', '1', NOW(), NOW())
ON DUPLICATE KEY UPDATE `value` = '1', `updated_at` = NOW();

-- 2. Create the social_media_verifications table if it doesn't exist
CREATE TABLE IF NOT EXISTS `social_media_verifications` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `listing_id` bigint(20) unsigned DEFAULT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `platform` enum('instagram','youtube','tiktok','twitter','facebook') NOT NULL,
  `account_id` varchar(255) DEFAULT NULL COMMENT 'Platform account ID from OAuth',
  `account_username` varchar(255) DEFAULT NULL COMMENT 'Platform account username',
  `status` tinyint(3) unsigned NOT NULL DEFAULT 0 COMMENT '0:pending, 1:verified, 2:failed',
  `verified_at` timestamp NULL DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `social_media_verifications_listing_id_foreign` (`listing_id`),
  KEY `social_media_verifications_user_id_foreign` (`user_id`),
  KEY `idx_listing_platform` (`listing_id`, `platform`),
  KEY `idx_account_id` (`account_id`),
  CONSTRAINT `social_media_verifications_listing_id_foreign` FOREIGN KEY (`listing_id`) REFERENCES `listings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `social_media_verifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Verify the setting was added (optional check)
SELECT * FROM `marketplace_settings` WHERE `key` = 'require_social_media_verification';

-- 4. Verify the table was created (optional check)
SHOW TABLES LIKE 'social_media_verifications';

-- ============================================
-- Notes:
-- - The setting defaults to '1' (enabled)
-- - Admins can toggle this in: Admin Panel > Marketplace > Configuration
-- - When enabled, users must verify social media accounts via OAuth before listing
-- ============================================

