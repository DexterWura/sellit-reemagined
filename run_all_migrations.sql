-- ============================================
-- Migration: 0001_01_01_000000_create_users_table.php
-- ============================================

CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Migration: 0001_01_01_000001_create_cache_table.php
-- ============================================

CREATE TABLE IF NOT EXISTS `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Migration: 0001_01_01_000002_create_jobs_table.php
-- ============================================

CREATE TABLE IF NOT EXISTS `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Migration: 2025_01_15_000001_add_confidential_fields_to_listings.php
-- ============================================
-- Note: This adds columns to existing listings table. Make sure listings table exists first.
-- If columns already exist, you may get an error - that's okay, just skip those lines.

ALTER TABLE `listings` 
ADD COLUMN `is_confidential` tinyint(1) NOT NULL DEFAULT 0 AFTER `is_verified`,
ADD COLUMN `requires_nda` tinyint(1) NOT NULL DEFAULT 0 AFTER `is_confidential`,
ADD COLUMN `confidential_reason` text DEFAULT NULL AFTER `requires_nda`;

-- ============================================
-- Migration: 2025_01_15_000002_create_nda_documents_table.php
-- ============================================

CREATE TABLE IF NOT EXISTS `nda_documents` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `listing_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `document_path` varchar(500) DEFAULT NULL COMMENT 'Path to uploaded NDA document',
  `signature` varchar(255) DEFAULT NULL COMMENT 'Digital signature or name',
  `signed_at` timestamp NULL DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `status` enum('pending','signed','expired','revoked') NOT NULL DEFAULT 'pending',
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `nda_documents_listing_id_index` (`listing_id`),
  KEY `nda_documents_user_id_index` (`user_id`),
  KEY `nda_documents_listing_id_user_id_index` (`listing_id`,`user_id`),
  KEY `nda_documents_status_index` (`status`),
  CONSTRAINT `nda_documents_listing_id_foreign` FOREIGN KEY (`listing_id`) REFERENCES `listings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `nda_documents_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Migration: 2025_01_15_000003_create_migration_tracking_table.php
-- ============================================

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

-- ============================================
-- Migration: 2025_12_01_000001_create_listing_categories_table.php
-- ============================================

CREATE TABLE IF NOT EXISTS `listing_categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `icon` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `business_type` enum('domain','website','social_media_account','mobile_app','desktop_app') NOT NULL,
  `parent_id` bigint(20) unsigned NOT NULL DEFAULT 0,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `listing_categories_slug_unique` (`slug`),
  KEY `listing_categories_parent_id_index` (`parent_id`),
  KEY `listing_categories_status_index` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Migration: 2025_12_01_000002_create_listings_table.php
-- ============================================
-- Note: This is a large table. Make sure users and listing_categories tables exist first.

CREATE TABLE IF NOT EXISTS `listings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `listing_number` varchar(40) NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `category_id` bigint(20) unsigned DEFAULT NULL,
  `listing_category_id` bigint(20) unsigned DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(300) NOT NULL,
  `tagline` text DEFAULT NULL,
  `description` longtext NOT NULL,
  `business_type` enum('domain','website','social_media_account','mobile_app','desktop_app') NOT NULL,
  `sale_type` enum('fixed_price','auction') NOT NULL DEFAULT 'fixed_price',
  `asking_price` decimal(28,8) NOT NULL DEFAULT 0.00000000,
  `reserve_price` decimal(28,8) NOT NULL DEFAULT 0.00000000 COMMENT 'Minimum price for auction',
  `buy_now_price` decimal(28,8) NOT NULL DEFAULT 0.00000000 COMMENT 'Instant purchase price for auctions',
  `starting_bid` decimal(28,8) NOT NULL DEFAULT 0.00000000 COMMENT 'Starting bid for auctions',
  `bid_increment` decimal(28,8) NOT NULL DEFAULT 1.00000000 COMMENT 'Minimum bid increment',
  `current_bid` decimal(28,8) NOT NULL DEFAULT 0.00000000,
  `highest_bidder_id` bigint(20) unsigned NOT NULL DEFAULT 0,
  `total_bids` int(11) NOT NULL DEFAULT 0,
  `url` varchar(500) DEFAULT NULL COMMENT 'Website/app URL',
  `domain_name` varchar(255) DEFAULT NULL,
  `domain_extension` varchar(50) DEFAULT NULL,
  `domain_registrar` varchar(100) DEFAULT NULL,
  `domain_expiry` date DEFAULT NULL,
  `domain_age_years` int(11) NOT NULL DEFAULT 0,
  `platform` varchar(100) DEFAULT NULL COMMENT 'Instagram, YouTube, TikTok, etc.',
  `niche` varchar(100) DEFAULT NULL,
  `followers_count` bigint(20) NOT NULL DEFAULT 0,
  `subscribers_count` bigint(20) NOT NULL DEFAULT 0,
  `engagement_rate` decimal(8,4) NOT NULL DEFAULT 0.0000,
  `app_store_url` varchar(500) DEFAULT NULL,
  `play_store_url` varchar(500) DEFAULT NULL,
  `downloads_count` bigint(20) NOT NULL DEFAULT 0,
  `app_rating` decimal(3,2) NOT NULL DEFAULT 0.00,
  `tech_stack` varchar(500) DEFAULT NULL,
  `monthly_revenue` decimal(28,8) NOT NULL DEFAULT 0.00000000,
  `monthly_profit` decimal(28,8) NOT NULL DEFAULT 0.00000000,
  `yearly_revenue` decimal(28,8) NOT NULL DEFAULT 0.00000000,
  `yearly_profit` decimal(28,8) NOT NULL DEFAULT 0.00000000,
  `revenue_multiple` int(11) NOT NULL DEFAULT 0 COMMENT 'Asking price / yearly profit',
  `monthly_visitors` bigint(20) unsigned NOT NULL DEFAULT 0,
  `monthly_page_views` bigint(20) unsigned NOT NULL DEFAULT 0,
  `traffic_sources` varchar(500) DEFAULT NULL COMMENT 'JSON: organic, paid, social, etc.',
  `monetization_methods` varchar(500) DEFAULT NULL COMMENT 'JSON: ads, affiliate, products, etc.',
  `assets_included` text DEFAULT NULL COMMENT 'JSON: domain, content, email list, etc.',
  `is_verified` tinyint(1) NOT NULL DEFAULT 0,
  `revenue_verified` tinyint(1) NOT NULL DEFAULT 0,
  `traffic_verified` tinyint(1) NOT NULL DEFAULT 0,
  `verification_notes` text DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 0,
  `rejection_reason` text DEFAULT NULL,
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `featured_until` timestamp NULL DEFAULT NULL,
  `auction_start` timestamp NULL DEFAULT NULL,
  `auction_end` timestamp NULL DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `sold_at` timestamp NULL DEFAULT NULL,
  `winner_id` bigint(20) unsigned NOT NULL DEFAULT 0,
  `final_price` decimal(28,8) NOT NULL DEFAULT 0.00000000,
  `escrow_id` bigint(20) unsigned NOT NULL DEFAULT 0,
  `view_count` int(11) NOT NULL DEFAULT 0,
  `watchlist_count` int(11) NOT NULL DEFAULT 0,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `meta_keywords` varchar(500) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `listings_listing_number_unique` (`listing_number`),
  UNIQUE KEY `listings_slug_unique` (`slug`),
  KEY `listings_user_id_index` (`user_id`),
  KEY `listings_category_id_index` (`category_id`),
  KEY `listings_listing_category_id_index` (`listing_category_id`),
  KEY `listings_business_type_index` (`business_type`),
  KEY `listings_sale_type_index` (`sale_type`),
  KEY `listings_status_index` (`status`),
  KEY `listings_is_featured_index` (`is_featured`),
  KEY `listings_auction_end_index` (`auction_end`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Migration: 2025_12_01_000003_create_listing_images_table.php
-- ============================================

CREATE TABLE IF NOT EXISTS `listing_images` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `listing_id` bigint(20) unsigned NOT NULL,
  `image_path` varchar(500) NOT NULL,
  `thumbnail_path` varchar(500) DEFAULT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `alt_text` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `listing_images_listing_id_index` (`listing_id`),
  KEY `listing_images_is_primary_index` (`is_primary`),
  CONSTRAINT `listing_images_listing_id_foreign` FOREIGN KEY (`listing_id`) REFERENCES `listings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Migration: 2025_12_01_000004_create_listing_metrics_table.php
-- ============================================

CREATE TABLE IF NOT EXISTS `listing_metrics` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `listing_id` bigint(20) unsigned NOT NULL,
  `period_date` date NOT NULL,
  `period_type` varchar(20) NOT NULL DEFAULT 'monthly',
  `revenue` decimal(28,8) NOT NULL DEFAULT 0.00000000,
  `expenses` decimal(28,8) NOT NULL DEFAULT 0.00000000,
  `profit` decimal(28,8) NOT NULL DEFAULT 0.00000000,
  `visitors` bigint(20) NOT NULL DEFAULT 0,
  `page_views` bigint(20) NOT NULL DEFAULT 0,
  `unique_visitors` bigint(20) NOT NULL DEFAULT 0,
  `followers` bigint(20) NOT NULL DEFAULT 0,
  `subscribers` bigint(20) NOT NULL DEFAULT 0,
  `downloads` bigint(20) NOT NULL DEFAULT 0,
  `engagement_rate` decimal(8,4) NOT NULL DEFAULT 0.0000,
  `email_subscribers` bigint(20) NOT NULL DEFAULT 0,
  `notes` text DEFAULT NULL,
  `is_verified` tinyint(1) NOT NULL DEFAULT 0,
  `proof_document` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `listing_metrics_listing_id_period_date_index` (`listing_id`,`period_date`),
  UNIQUE KEY `listing_metrics_listing_id_period_date_period_type_unique` (`listing_id`,`period_date`,`period_type`),
  CONSTRAINT `listing_metrics_listing_id_foreign` FOREIGN KEY (`listing_id`) REFERENCES `listings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Migration: 2025_12_01_000005_create_bids_table.php
-- ============================================

CREATE TABLE IF NOT EXISTS `bids` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `bid_number` varchar(40) NOT NULL,
  `listing_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `amount` decimal(28,8) NOT NULL,
  `max_bid` decimal(28,8) NOT NULL DEFAULT 0.00000000 COMMENT 'Auto-bid maximum',
  `is_auto_bid` tinyint(1) NOT NULL DEFAULT 0,
  `status` tinyint(4) NOT NULL DEFAULT 0,
  `is_buy_now` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'If used buy now option',
  `notes` text DEFAULT NULL,
  `ip_address` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `bids_bid_number_unique` (`bid_number`),
  KEY `bids_listing_id_index` (`listing_id`),
  KEY `bids_user_id_index` (`user_id`),
  KEY `bids_status_index` (`status`),
  CONSTRAINT `bids_listing_id_foreign` FOREIGN KEY (`listing_id`) REFERENCES `listings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `bids_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Migration: 2025_12_01_000006_create_offers_table.php
-- ============================================

CREATE TABLE IF NOT EXISTS `offers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `offer_number` varchar(40) NOT NULL,
  `listing_id` bigint(20) unsigned NOT NULL,
  `buyer_id` bigint(20) unsigned NOT NULL,
  `seller_id` bigint(20) unsigned NOT NULL,
  `amount` decimal(28,8) NOT NULL,
  `message` text DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 0,
  `counter_amount` decimal(28,8) NOT NULL DEFAULT 0.00000000,
  `counter_message` text DEFAULT NULL,
  `countered_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `responded_at` timestamp NULL DEFAULT NULL,
  `escrow_id` bigint(20) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `offers_offer_number_unique` (`offer_number`),
  KEY `offers_listing_id_index` (`listing_id`),
  KEY `offers_buyer_id_index` (`buyer_id`),
  KEY `offers_seller_id_index` (`seller_id`),
  KEY `offers_status_index` (`status`),
  CONSTRAINT `offers_listing_id_foreign` FOREIGN KEY (`listing_id`) REFERENCES `listings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `offers_buyer_id_foreign` FOREIGN KEY (`buyer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `offers_seller_id_foreign` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Migration: 2025_12_01_000007_create_watchlist_table.php
-- ============================================

CREATE TABLE IF NOT EXISTS `watchlist` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `listing_id` bigint(20) unsigned NOT NULL,
  `notify_bid` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Notify on new bids',
  `notify_price_change` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Notify on price changes',
  `notify_ending` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Notify when auction ending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `watchlist_user_id_listing_id_unique` (`user_id`,`listing_id`),
  KEY `watchlist_user_id_index` (`user_id`),
  KEY `watchlist_listing_id_index` (`listing_id`),
  CONSTRAINT `watchlist_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `watchlist_listing_id_foreign` FOREIGN KEY (`listing_id`) REFERENCES `listings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Migration: 2025_12_01_000008_create_listing_views_table.php
-- ============================================

CREATE TABLE IF NOT EXISTS `listing_views` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `listing_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL DEFAULT 0,
  `ip_address` varchar(50) DEFAULT NULL,
  `user_agent` varchar(500) DEFAULT NULL,
  `referrer` varchar(500) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `listing_views_listing_id_index` (`listing_id`),
  KEY `listing_views_listing_id_created_at_index` (`listing_id`,`created_at`),
  CONSTRAINT `listing_views_listing_id_foreign` FOREIGN KEY (`listing_id`) REFERENCES `listings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Migration: 2025_12_01_000009_create_listing_questions_table.php
-- ============================================

CREATE TABLE IF NOT EXISTS `listing_questions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `listing_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `question` text NOT NULL,
  `answer` text DEFAULT NULL,
  `answered_at` timestamp NULL DEFAULT NULL,
  `is_public` tinyint(1) NOT NULL DEFAULT 1,
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `status` tinyint(4) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `listing_questions_listing_id_index` (`listing_id`),
  KEY `listing_questions_user_id_index` (`user_id`),
  CONSTRAINT `listing_questions_listing_id_foreign` FOREIGN KEY (`listing_id`) REFERENCES `listings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `listing_questions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Migration: 2025_12_01_000010_create_reviews_table.php
-- ============================================

CREATE TABLE IF NOT EXISTS `reviews` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `listing_id` bigint(20) unsigned NOT NULL,
  `escrow_id` bigint(20) unsigned NOT NULL DEFAULT 0,
  `reviewer_id` bigint(20) unsigned NOT NULL,
  `reviewed_user_id` bigint(20) unsigned NOT NULL,
  `review_type` enum('buyer_review','seller_review') NOT NULL,
  `overall_rating` tinyint(4) NOT NULL,
  `communication_rating` tinyint(4) DEFAULT NULL,
  `accuracy_rating` tinyint(4) DEFAULT NULL COMMENT 'As described rating',
  `timeliness_rating` tinyint(4) DEFAULT NULL,
  `review` text NOT NULL,
  `seller_response` text DEFAULT NULL,
  `responded_at` timestamp NULL DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `reviews_listing_id_index` (`listing_id`),
  KEY `reviews_reviewer_id_index` (`reviewer_id`),
  KEY `reviews_reviewed_user_id_index` (`reviewed_user_id`),
  UNIQUE KEY `reviews_listing_id_reviewer_id_review_type_unique` (`listing_id`,`reviewer_id`,`review_type`),
  CONSTRAINT `reviews_listing_id_foreign` FOREIGN KEY (`listing_id`) REFERENCES `listings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reviews_reviewer_id_foreign` FOREIGN KEY (`reviewer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reviews_reviewed_user_id_foreign` FOREIGN KEY (`reviewed_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Migration: 2025_12_01_000011_create_saved_searches_table.php
-- ============================================

CREATE TABLE IF NOT EXISTS `saved_searches` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `name` varchar(100) NOT NULL,
  `filters` text NOT NULL COMMENT 'JSON encoded search filters',
  `email_alerts` tinyint(1) NOT NULL DEFAULT 1,
  `alert_frequency` enum('instant','daily','weekly') NOT NULL DEFAULT 'daily',
  `last_alerted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `saved_searches_user_id_index` (`user_id`),
  CONSTRAINT `saved_searches_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Migration: 2025_12_01_000012_add_seller_fields_to_users_table.php
-- ============================================
-- Note: This adds columns to existing users table.

ALTER TABLE `users` 
ADD COLUMN `bio` text DEFAULT NULL AFTER `address`,
ADD COLUMN `website` varchar(255) DEFAULT NULL AFTER `bio`,
ADD COLUMN `company_name` varchar(255) DEFAULT NULL AFTER `website`,
ADD COLUMN `is_verified_seller` tinyint(1) NOT NULL DEFAULT 0 AFTER `company_name`,
ADD COLUMN `seller_verified_at` timestamp NULL DEFAULT NULL AFTER `is_verified_seller`,
ADD COLUMN `total_listings` int(11) NOT NULL DEFAULT 0 AFTER `seller_verified_at`,
ADD COLUMN `total_sales` int(11) NOT NULL DEFAULT 0 AFTER `total_listings`,
ADD COLUMN `total_sales_value` decimal(28,8) NOT NULL DEFAULT 0.00000000 AFTER `total_sales`,
ADD COLUMN `total_purchases` int(11) NOT NULL DEFAULT 0 AFTER `total_sales_value`,
ADD COLUMN `avg_rating` decimal(3,2) NOT NULL DEFAULT 0.00 AFTER `total_purchases`,
ADD COLUMN `total_reviews` int(11) NOT NULL DEFAULT 0 AFTER `avg_rating`;

-- ============================================
-- Migration: 2025_12_01_000013_create_marketplace_settings_table.php
-- ============================================

CREATE TABLE IF NOT EXISTS `marketplace_settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(100) NOT NULL,
  `value` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `marketplace_settings_key_unique` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Migration: 2025_12_01_000014_create_domain_verifications_table.php
-- ============================================

CREATE TABLE IF NOT EXISTS `domain_verifications` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `listing_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `domain` varchar(255) NOT NULL,
  `verification_method` enum('txt_file','dns_record') NOT NULL,
  `verification_token` varchar(100) NOT NULL,
  `txt_filename` varchar(100) DEFAULT NULL COMMENT 'For file upload method',
  `dns_record_name` varchar(100) DEFAULT NULL COMMENT 'For DNS method',
  `dns_record_value` varchar(255) DEFAULT NULL COMMENT 'For DNS method',
  `status` tinyint(3) unsigned NOT NULL DEFAULT 0 COMMENT '0:pending, 1:verified, 2:failed',
  `attempts` int(11) NOT NULL DEFAULT 0,
  `last_attempt_at` timestamp NULL DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `domain_verifications_listing_id_unique` (`listing_id`),
  KEY `domain_verifications_domain_index` (`domain`),
  KEY `domain_verifications_verification_token_index` (`verification_token`),
  CONSTRAINT `domain_verifications_listing_id_foreign` FOREIGN KEY (`listing_id`) REFERENCES `listings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `domain_verifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Migration: 2025_12_01_000015_add_verification_fields_to_listings_table.php
-- ============================================
-- Note: This adds columns to existing listings table.
-- If columns already exist, you may get an error - that's okay, just skip those lines.

ALTER TABLE `listings` 
ADD COLUMN `requires_verification` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Does this listing require domain verification' AFTER `is_verified`,
ADD COLUMN `auction_duration_days` int(11) DEFAULT NULL COMMENT 'Stored duration for auction start after approval' AFTER `verification_notes`;

