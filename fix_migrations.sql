-- Fix Laravel Migration Tracking
-- Run this SQL script to mark all migrations as completed

INSERT IGNORE INTO `migrations` (`migration`, `batch`) VALUES
('0001_01_01_000000_create_users_table', 1),
('0001_01_01_000001_create_cache_table', 1),
('0001_01_01_000002_create_jobs_table', 1),
('2025_01_15_000001_add_confidential_fields_to_listings', 1),
('2025_01_15_000002_create_nda_documents_table', 1),
('2025_01_15_000003_create_migration_tracking_table', 1),
('2025_01_20_000001_add_milestone_approval_fields', 1),
('2025_01_20_000002_create_milestone_templates_table', 1),
('2025_12_01_000001_create_listing_categories_table', 1),
('2025_12_01_000002_create_listings_table', 1),
('2025_12_01_000003_create_listing_images_table', 1),
('2025_12_01_000004_create_listing_metrics_table', 1),
('2025_12_01_000005_create_bids_table', 1),
('2025_12_01_000006_create_offers_table', 1),
('2025_12_01_000007_create_watchlist_table', 1),
('2025_12_01_000008_create_listing_views_table', 1),
('2025_12_01_000009_create_listing_questions_table', 1),
('2025_12_01_000010_create_reviews_table', 1),
('2025_12_01_000011_create_saved_searches_table', 1),
('2025_12_01_000012_add_seller_fields_to_users_table', 1),
('2025_12_01_000013_create_marketplace_settings_table', 1),
('2025_12_01_000014_create_domain_verifications_table', 1),
('2025_12_01_000015_add_verification_fields_to_listings_table', 1),
('2025_12_01_000016_create_social_media_verifications_table', 1),
('2025_12_04_000001_add_data_integrity_constraints', 1),
('2025_12_04_000002_add_performance_indexes', 1),
('2025_12_05_000001_create_verification_settings_table', 1),
('2025_12_05_000002_create_verification_attempts_table', 1),
('2025_12_05_000003_update_domain_verifications_table', 1);

-- Verify the migrations table exists (create if not)
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
