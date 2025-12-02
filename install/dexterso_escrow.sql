-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Dec 01, 2025 at 03:55 PM
-- Server version: 10.11.13-MariaDB
-- PHP Version: 8.4.13

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `dexterso_escrow`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(40) DEFAULT NULL,
  `email` varchar(40) DEFAULT NULL,
  `username` varchar(40) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `name`, `email`, `username`, `email_verified_at`, `image`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Super Admin', 'info@dextersoft.com', 'admin', NULL, '6385aa4e5c8371669704270.png', '$2y$12$c9.R7kl1OaTAr0q6bfB5fOkDWHyYZaH.YmJ.ur9sbc/TBYoen13Na', 'xMod3XfyWRDIVqDhgqcYOPPXOkmqQi8QMRz31RWG4unEzUzSXw0Bo2MfwktE', NULL, '2025-07-13 11:49:30');

-- --------------------------------------------------------

--
-- Table structure for table `admin_notifications`
--

CREATE TABLE `admin_notifications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `title` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `click_url` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `admin_password_resets`
--

CREATE TABLE `admin_password_resets` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `email` varchar(40) NOT NULL,
  `token` varchar(40) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bids`
--

CREATE TABLE `bids` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `bid_number` varchar(40) NOT NULL,
  `listing_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `amount` decimal(28,8) NOT NULL,
  `max_bid` decimal(28,8) NOT NULL DEFAULT 0.00000000 COMMENT 'Auto-bid maximum',
  `is_auto_bid` tinyint(1) NOT NULL DEFAULT 0,
  `status` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '0:active, 1:outbid, 2:winning, 3:won, 4:lost, 5:cancelled',
  `is_buy_now` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'If used buy now option',
  `notes` text DEFAULT NULL,
  `ip_address` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `conversations`
--

CREATE TABLE `conversations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `escrow_id` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `buyer_id` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `seller_id` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `status` tinyint(3) UNSIGNED NOT NULL DEFAULT 1 COMMENT '1=>running, 0=>closed',
  `is_group` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '0=>only 2 person, 1=> also admin will be added',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `deposits`
--

CREATE TABLE `deposits` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `milestone_id` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `user_id` int(10) UNSIGNED NOT NULL,
  `method_code` int(10) UNSIGNED NOT NULL,
  `amount` decimal(28,8) NOT NULL DEFAULT 0.00000000,
  `method_currency` varchar(40) NOT NULL,
  `charge` decimal(28,8) NOT NULL DEFAULT 0.00000000,
  `rate` decimal(28,8) NOT NULL DEFAULT 0.00000000,
  `final_amount` decimal(28,8) NOT NULL DEFAULT 0.00000000,
  `detail` text DEFAULT NULL,
  `btc_amount` varchar(255) DEFAULT NULL,
  `btc_wallet` varchar(255) DEFAULT NULL,
  `trx` varchar(40) DEFAULT NULL,
  `payment_try` int(11) NOT NULL DEFAULT 0,
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1=>success, 2=>pending, 3=>cancel',
  `from_api` tinyint(1) NOT NULL DEFAULT 0,
  `admin_feedback` varchar(255) DEFAULT NULL,
  `success_url` varchar(255) DEFAULT NULL,
  `failed_url` varchar(255) DEFAULT NULL,
  `last_cron` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `device_tokens`
--

CREATE TABLE `device_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `is_app` tinyint(1) NOT NULL DEFAULT 0,
  `token` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `domain_verifications`
--

CREATE TABLE `domain_verifications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `listing_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `domain` varchar(255) NOT NULL,
  `verification_method` enum('txt_file','dns_record') NOT NULL,
  `verification_token` varchar(100) NOT NULL,
  `txt_filename` varchar(100) DEFAULT NULL COMMENT 'For file upload method',
  `dns_record_name` varchar(100) DEFAULT NULL COMMENT 'For DNS method',
  `dns_record_value` varchar(255) DEFAULT NULL COMMENT 'For DNS method',
  `status` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '0:pending, 1:verified, 2:failed',
  `attempts` int(11) NOT NULL DEFAULT 0,
  `last_attempt_at` timestamp NULL DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `escrows`
--

CREATE TABLE `escrows` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `escrow_number` varchar(40) DEFAULT NULL,
  `seller_id` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `buyer_id` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `creator_id` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `amount` decimal(28,8) NOT NULL DEFAULT 0.00000000,
  `paid_amount` decimal(28,8) NOT NULL DEFAULT 0.00000000,
  `charge_payer` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `charge` decimal(28,8) NOT NULL DEFAULT 0.00000000,
  `buyer_charge` decimal(28,8) NOT NULL DEFAULT 0.00000000,
  `seller_charge` decimal(28,8) NOT NULL DEFAULT 0.00000000,
  `dispute_charge` decimal(28,8) NOT NULL DEFAULT 0.00000000,
  `category_id` int(10) UNSIGNED DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `status` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '0 => not accepted\r\n1 => dispatched and completed\r\n2 => accepted and running\r\n8 => disputed\r\n9 => cancelled',
  `invitation_mail` varchar(40) DEFAULT NULL,
  `disputer_id` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `dispute_note` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `escrow_charges`
--

CREATE TABLE `escrow_charges` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `minimum` decimal(28,8) NOT NULL DEFAULT 0.00000000,
  `maximum` decimal(28,8) NOT NULL DEFAULT 0.00000000,
  `fixed_charge` decimal(28,8) NOT NULL DEFAULT 0.00000000,
  `percent_charge` decimal(5,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `extensions`
--

CREATE TABLE `extensions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `act` varchar(40) NOT NULL,
  `name` varchar(40) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `script` text DEFAULT NULL,
  `shortcode` text DEFAULT NULL COMMENT 'object',
  `support` text DEFAULT NULL COMMENT 'help section',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1=>enable, 2=>disable',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `extensions`
--

INSERT INTO `extensions` (`id`, `act`, `name`, `description`, `image`, `script`, `shortcode`, `support`, `status`, `created_at`, `updated_at`) VALUES
(1, 'tawk-chat', 'Tawk.to', 'Key location is shown bellow', 'tawky_big.png', '<script>\r\n                        var Tawk_API=Tawk_API||{}, Tawk_LoadStart=new Date();\r\n                        (function(){\r\n                        var s1=document.createElement(\"script\"),s0=document.getElementsByTagName(\"script\")[0];\r\n                        s1.async=true;\r\n                        s1.src=\"https://embed.tawk.to/{{app_key}}\";\r\n                        s1.charset=\"UTF-8\";\r\n                        s1.setAttribute(\"crossorigin\",\"*\");\r\n                        s0.parentNode.insertBefore(s1,s0);\r\n                        })();\r\n                    </script>', '{\"app_key\":{\"title\":\"App Key\",\"value\":\"------\"}}', 'twak.png', 0, '2019-10-18 23:16:05', '2021-05-18 05:37:12'),
(2, 'google-recaptcha2', 'Google Recaptcha 2', 'Key location is shown bellow', 'recaptcha3.png', '\r\n<script src=\"https://www.google.com/recaptcha/api.js\"></script>\r\n<div class=\"g-recaptcha\" data-sitekey=\"{{site_key}}\" data-callback=\"verifyCaptcha\"></div>\r\n<div id=\"g-recaptcha-error\"></div>', '{\"site_key\":{\"title\":\"Site Key\",\"value\":\"-----------------------\"},\"secret_key\":{\"title\":\"Secret Key\",\"value\":\"-----------------------\"}}', 'recaptcha.png', 0, '2019-10-18 23:16:05', '2024-06-13 02:52:57'),
(3, 'custom-captcha', 'Custom Captcha', 'Just Put Any Random String', 'customcaptcha.png', NULL, '{\"random_key\":{\"title\":\"Random String\",\"value\":\"SecureString\"}}', 'na', 0, '2019-10-18 23:16:05', '2024-06-12 05:37:00'),
(4, 'google-analytics', 'Google Analytics', 'Key location is shown bellow', 'google_analytics.png', '<script async src=\"https://www.googletagmanager.com/gtag/js?id={{measurement_id}}\"></script>\n                <script>\n                  window.dataLayer = window.dataLayer || [];\n                  function gtag(){dataLayer.push(arguments);}\n                  gtag(\"js\", new Date());\n                \n                  gtag(\"config\", \"{{measurement_id}}\");\n                </script>', '{\"measurement_id\":{\"title\":\"Measurement ID\",\"value\":\"------\"}}', 'ganalytics.png', 0, NULL, '2024-06-13 02:52:44'),
(5, 'fb-comment', 'Facebook Comment ', 'Key location is shown bellow', 'Facebook.png', '<div id=\"fb-root\"></div><script async defer crossorigin=\"anonymous\" src=\"https://connect.facebook.net/en_GB/sdk.js#xfbml=1&version=v4.0&appId={{app_key}}&autoLogAppEvents=1\"></script>', '{\"app_key\":{\"title\":\"App Key\",\"value\":\"----\"}}', 'fb_com.png', 0, NULL, '2024-06-13 02:52:47');

-- --------------------------------------------------------

--
-- Table structure for table `forms`
--

CREATE TABLE `forms` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `act` varchar(40) DEFAULT NULL,
  `form_data` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `forms`
--

INSERT INTO `forms` (`id`, `act`, `form_data`, `created_at`, `updated_at`) VALUES
(7, 'kyc', '{\"full_name\":{\"name\":\"Full Name\",\"label\":\"full_name\",\"is_required\":\"required\",\"extensions\":null,\"options\":[],\"type\":\"text\"},\"nid_number\":{\"name\":\"NID Number\",\"label\":\"nid_number\",\"is_required\":\"required\",\"extensions\":null,\"options\":[],\"type\":\"text\"},\"gender\":{\"name\":\"Gender\",\"label\":\"gender\",\"is_required\":\"required\",\"extensions\":null,\"options\":[\"Male\",\"Female\",\"Others\"],\"type\":\"select\"},\"you_hobby\":{\"name\":\"You Hobby\",\"label\":\"you_hobby\",\"is_required\":\"required\",\"extensions\":null,\"options\":[\"Programming\",\"Gardening\",\"Traveling\",\"Others\"],\"type\":\"checkbox\"},\"nid_photo\":{\"name\":\"NID Photo\",\"label\":\"nid_photo\",\"is_required\":\"required\",\"extensions\":\"jpg,png\",\"options\":[],\"type\":\"file\"}}', '2022-03-17 02:56:14', '2022-10-13 06:13:55');

-- --------------------------------------------------------

--
-- Table structure for table `frontends`
--

CREATE TABLE `frontends` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `data_keys` varchar(40) DEFAULT NULL,
  `data_values` longtext DEFAULT NULL,
  `seo_content` longtext DEFAULT NULL,
  `tempname` varchar(40) DEFAULT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `frontends`
--

INSERT INTO `frontends` (`id`, `data_keys`, `data_values`, `seo_content`, `tempname`, `slug`, `created_at`, `updated_at`) VALUES
(1, 'seo.data', '{\"seo_image\":\"1\",\"keywords\":[\"escrow\",\"online escrow system\",\"online payment\",\"online transaction\",\"safe online transaction\"],\"description\":\"Escrowlab is a platform for a secure online payment processing system.\",\"social_title\":\"EscrowLab - Escrow Payment Platform\",\"social_description\":\"Escrowlab is a platform for a secure online payment processing system.\",\"image\":\"666979cc7e7961718188492.png\"}', NULL, 'basic', '', '2020-07-04 23:42:52', '2024-06-12 04:34:52'),
(2, 'about.content', '{\"has_image\":\"1\",\"heading\":\"Transact Safely With Our Peer-to-Peer Escrow-Style Payment Platform\",\"subheading\":\"Lorem ipsum dolor sit amet consectetuer adipiscing elit. Aenean modo lula eget dolor. Aenean massa. Cum sociis natoque penatibus et magnis turient montes nascetur ridiculus mus.\",\"image\":\"63affcfe9f1611672477950.png\"}', NULL, 'basic', NULL, '2020-10-28 00:51:20', '2022-12-31 05:21:14'),
(3, 'blog.content', '{\"heading\":\"Latest News And Tips\",\"subheading\":\"Maecenas tempus tellus eget condimentum rhoncus sequam semper libero sit amet\"}', NULL, 'basic', NULL, '2020-10-28 00:51:34', '2022-12-28 07:26:39'),
(4, 'blog.element', '{\"has_image\":[\"1\"],\"title\":\"Curabitur a felis in nunc fringilla tristique abot escrow.\",\"description\":\"<p class=\\\"fs--18px\\\" style=\\\"margin-right:0px;margin-left:0px;color:rgb(102,102,102);font-size:1.125rem;\\\">Nobis,\\r\\n maiores. Dolores nesciunt, quibusdam sed, velit dicta qui atque, ad \\r\\ndoloribus eveniet cupiditate pariatur doloremque nihil harum nemo \\r\\nvoluptatum illum. Alias doloribus eveniet cupiditate.<\\/p><p class=\\\"mt-3\\\" style=\\\"margin-right:0px;margin-left:0px;color:rgb(102,102,102);font-size:15px;\\\">Fugiat\\r\\n doloremque mollitia a adipisci voluptas natus aperiam numquam libero \\r\\nfacilis. Veniam dignissimos natus ab doloremque exercitationem minima \\r\\nneque, iusto, ullam nostrum impedit dolores quos architecto aut ipsa \\r\\nnihil dolore facere, inventore quidem voluptates. Impedit, obcaecati \\r\\nnumquam! Corrupti eos alias quibusdam sint cupiditate at iure \\r\\nreprehenderit a debitis id enim explicabo, nemo expedita magni nesciunt \\r\\nexcepturi dicta omnis. Minus quibusdam nulla officia eos ipsam soluta, \\r\\niusto omnis repellendus consequuntur cupiditate temporibus, commodi \\r\\nexpedita atque architecto praesentium suscipit molestias dignissimos, \\r\\nimpedit itaque aliquam nam dolore explicabo! Ad dolores beatae ipsum \\r\\nnemo provident voluptatibus Minus quibusdam nulla officia eos.<\\/p><blockquote style=\\\"margin-top:1.25rem;padding:1.875rem;background-color:rgb(241,241,241);font-style:italic;color:rgb(102,102,102);font-size:15px;\\\">Lorem\\r\\n ipsum dolor sit amet consectetur adipisicing elit. Consequatur autem \\r\\nquis odio, praesentium deserunt est dolores aliquid, eos officia ad quia\\r\\n voluptatum, tempore nulla ex necessitatibus recusandae itaque ipsam \\r\\nbeatae. Corrupti eos alias quibusdam sint cupiditate dolores beatae.<\\/blockquote><p class=\\\"mt-4\\\" style=\\\"margin-right:0px;margin-left:0px;color:rgb(102,102,102);font-size:15px;\\\">Totam\\r\\n minima nulla, placeat quidem, omnis sed laboriosam fuga maxime animi, \\r\\ncupiditate molestias consectetur. Labore, assumenda eaque. Officiis \\r\\nvoluptas accusantium alias nostrum fugit dolore eos blanditiis aperiam, \\r\\nillo consequuntur repellendus doloribus a porro exercitationem quia. \\r\\nAccusamus molestiae beatae alias, veritatis delectus reiciendis est \\r\\nharum ex nesciunt rem? Recusandae et nihil id ducimus aliquid, pariatur \\r\\naut tempore doloremque ratione cum in non eius maiores a voluptatum, \\r\\nquam aliquam perspiciatis magnam provident. Et odit saepe illo libero, \\r\\nvoluptatem optio aliquam deserunt nam accusamus in commodi dolorum \\r\\npariatur. Et quo labore velit nesciunt.<\\/p>\",\"image\":\"6384a4f42a1f11669637364.png\"}', NULL, 'basic', 'curabitur-a-felis-in-nunc-fringilla-tristique-abot-escrow', '2020-10-28 00:57:19', '2023-01-01 06:59:23'),
(5, 'contact_us.content', '{\"title\":\"Auctor gravida vestibulu\",\"short_details\":\"55f55\",\"email_address\":\"5555f\",\"contact_details\":\"5555h\",\"contact_number\":\"5555a\",\"latitude\":\"5555h\",\"longitude\":\"5555s\",\"website_footer\":\"5555qqq\"}', NULL, 'basic', NULL, '2020-10-28 00:59:19', '2020-11-01 04:51:54'),
(6, 'counter.content', '{\"heading\":\"Latest News\",\"sub_heading\":\"Register New Account\"}', NULL, 'basic', NULL, '2020-10-28 01:04:02', '2020-10-28 01:04:02'),
(7, 'social_icon.element', '{\"title\":\"Facebook\",\"icon\":\"<i class=\\\"fab fa-facebook-f\\\"><\\/i>\",\"url\":\"https:\\/\\/www.facebook.com\"}', NULL, 'basic', NULL, '2020-11-12 04:07:30', '2022-11-28 09:57:03'),
(8, 'feature.content', '{\"has_image\":\"1\",\"heading\":\"Provided Features\",\"subheading\":\"Lorem ipsum dolor sit amet consectetuer dipiscing elit. Aenean modo lula eget dolor. Aenean massa.\",\"image\":\"6384a766f132a1669637990.png\"}', NULL, 'basic', NULL, '2021-01-03 23:40:54', '2022-12-31 05:28:42'),
(9, 'service.content', '{\"heading\":\"Escrow Product And Services\",\"subheading\":\"Lorem ipsum dolor sit amet consectetuer adipiscing elit. Aenean modo lula eget dolor. Aenean massa.\"}', NULL, 'basic', NULL, '2021-03-06 01:27:34', '2022-12-31 06:24:55'),
(10, 'banner.content', '{\"has_image\":\"1\",\"heading\":\"Never Buy Or Sell Online Without Middlemen  Escrow\",\"subheading\":\"The most trusted escrow platform in Africa\",\"background_image\":\"6384a44f37ffb1669637199.png\",\"front_image\":\"6384a44f87fea1669637199.png\"}', NULL, 'basic', '', '2021-05-02 06:09:30', '2025-07-13 13:55:34'),
(11, 'policy_pages.element', '{\"title\":\"Privacy Policy\",\"details\":\"<div class=\\\"mb-5\\\" style=\\\"color:rgb(111,111,111);font-family:Nunito, sans-serif;margin-bottom:3rem;\\\"><h3 class=\\\"mb-3\\\" style=\\\"font-weight:600;line-height:1.3;font-size:24px;font-family:Exo, sans-serif;color:rgb(54,54,54);\\\">What information do we collect?<\\/h3><p class=\\\"font-18\\\" style=\\\"margin-right:0px;margin-left:0px;font-size:18px;\\\">We gather data from you when you register on our site, submit a request, buy any services, react to an overview, or round out a structure. At the point when requesting any assistance or enrolling on our site, as suitable, you might be approached to enter your: name, email address, or telephone number. You may, nonetheless, visit our site anonymously.<\\/p><\\/div><div class=\\\"mb-5\\\" style=\\\"color:rgb(111,111,111);font-family:Nunito, sans-serif;margin-bottom:3rem;\\\"><h3 class=\\\"mb-3\\\" style=\\\"font-weight:600;line-height:1.3;font-size:24px;font-family:Exo, sans-serif;color:rgb(54,54,54);\\\">How do we protect your information?<\\/h3><p class=\\\"font-18\\\" style=\\\"margin-right:0px;margin-left:0px;font-size:18px;\\\">All provided delicate\\/credit data is sent through Stripe.<br \\/>After an exchange, your private data (credit cards, social security numbers, financials, and so on) won\'t be put away on our workers.<\\/p><\\/div><div class=\\\"mb-5\\\" style=\\\"color:rgb(111,111,111);font-family:Nunito, sans-serif;margin-bottom:3rem;\\\"><h3 class=\\\"mb-3\\\" style=\\\"font-weight:600;line-height:1.3;font-size:24px;font-family:Exo, sans-serif;color:rgb(54,54,54);\\\">Do we disclose any information to outside parties?<\\/h3><p class=\\\"font-18\\\" style=\\\"margin-right:0px;margin-left:0px;font-size:18px;\\\">We don\'t sell, exchange, or in any case move to outside gatherings by and by recognizable data. This does exclude confided in outsiders who help us in working our site, leading our business, or adjusting you, since those gatherings consent to keep this data private. We may likewise deliver your data when we accept discharge is suitable to follow the law, implement our site strategies, or ensure our own or others\' rights, property, or wellbeing.<\\/p><\\/div><div class=\\\"mb-5\\\" style=\\\"color:rgb(111,111,111);font-family:Nunito, sans-serif;margin-bottom:3rem;\\\"><h3 class=\\\"mb-3\\\" style=\\\"font-weight:600;line-height:1.3;font-size:24px;font-family:Exo, sans-serif;color:rgb(54,54,54);\\\">Children\'s Online Privacy Protection Act Compliance<\\/h3><p class=\\\"font-18\\\" style=\\\"margin-right:0px;margin-left:0px;font-size:18px;\\\">We are consistent with the prerequisites of COPPA (Children\'s Online Privacy Protection Act), we don\'t gather any data from anybody under 13 years old. Our site, items, and administrations are completely coordinated to individuals who are in any event 13 years of age or more established.<\\/p><\\/div><div class=\\\"mb-5\\\" style=\\\"color:rgb(111,111,111);font-family:Nunito, sans-serif;margin-bottom:3rem;\\\"><h3 class=\\\"mb-3\\\" style=\\\"font-weight:600;line-height:1.3;font-size:24px;font-family:Exo, sans-serif;color:rgb(54,54,54);\\\">Changes to our Privacy Policy<\\/h3><p class=\\\"font-18\\\" style=\\\"margin-right:0px;margin-left:0px;font-size:18px;\\\">If we decide to change our privacy policy, we will post those changes on this page.<\\/p><\\/div><div class=\\\"mb-5\\\" style=\\\"color:rgb(111,111,111);font-family:Nunito, sans-serif;margin-bottom:3rem;\\\"><h3 class=\\\"mb-3\\\" style=\\\"font-weight:600;line-height:1.3;font-size:24px;font-family:Exo, sans-serif;color:rgb(54,54,54);\\\">How long we retain your information?<\\/h3><p class=\\\"font-18\\\" style=\\\"margin-right:0px;margin-left:0px;font-size:18px;\\\">At the point when you register for our site, we cycle and keep your information we have about you however long you don\'t erase the record or withdraw yourself (subject to laws and guidelines).<\\/p><\\/div><div class=\\\"mb-5\\\" style=\\\"color:rgb(111,111,111);font-family:Nunito, sans-serif;margin-bottom:3rem;\\\"><h3 class=\\\"mb-3\\\" style=\\\"font-weight:600;line-height:1.3;font-size:24px;font-family:Exo, sans-serif;color:rgb(54,54,54);\\\">What we don\\u2019t do with your data<\\/h3><p class=\\\"font-18\\\" style=\\\"margin-right:0px;margin-left:0px;font-size:18px;\\\">We don\'t and will never share, unveil, sell, or in any case give your information to different organizations for the promoting of their items or administrations.<\\/p><\\/div>\"}', NULL, 'basic', 'privacy-policy', '2021-06-09 08:50:42', '2021-06-09 08:50:42'),
(12, 'policy_pages.element', '{\"title\":\"Terms of Service\",\"details\":\"<div class=\\\"mb-5\\\" style=\\\"color:rgb(111,111,111);font-family:Nunito, sans-serif;margin-bottom:3rem;\\\"><p class=\\\"font-18\\\" style=\\\"margin-right:0px;margin-left:0px;font-size:18px;\\\">We claim all authority to dismiss, end, or handicap any help with or without cause per administrator discretion. This is a Complete independent facilitating, on the off chance that you misuse our ticket or Livechat or emotionally supportive network by submitting solicitations or protests we will impair your record. The solitary time you should reach us about the seaward facilitating is if there is an issue with the worker. We have not many substance limitations and everything is as per laws and guidelines. Try not to join on the off chance that you intend to do anything contrary to the guidelines, we do check these things and we will know, don\'t burn through our own and your time by joining on the off chance that you figure you will have the option to sneak by us and break the terms.<\\/p><\\/div><div class=\\\"mb-5\\\" style=\\\"color:rgb(111,111,111);font-family:Nunito, sans-serif;margin-bottom:3rem;\\\"><ul class=\\\"font-18\\\" style=\\\"padding-left:15px;list-style-type:disc;font-size:18px;\\\"><li style=\\\"margin-top:0px;margin-right:0px;margin-left:0px;\\\">Configuration requests - If you have a fully managed dedicated server with us then we offer custom PHP\\/MySQL configurations, firewalls for dedicated IPs, DNS, and httpd configurations.<\\/li><li style=\\\"margin-top:0px;margin-right:0px;margin-left:0px;\\\">Software requests - Cpanel Extension Installation will be granted as long as it does not interfere with the security, stability, and performance of other users on the server.<\\/li><li style=\\\"margin-top:0px;margin-right:0px;margin-left:0px;\\\">Emergency Support - We do not provide emergency support \\/ Phone Support \\/ LiveChat Support. Support may take some hours sometimes.<\\/li><li style=\\\"margin-top:0px;margin-right:0px;margin-left:0px;\\\">Webmaster help - We do not offer any support for webmaster related issues and difficulty including coding, &amp; installs, Error solving. if there is an issue where a library or configuration of the server then we can help you if it\'s possible from our end.<\\/li><li style=\\\"margin-top:0px;margin-right:0px;margin-left:0px;\\\">Backups - We keep backups but we are not responsible for data loss, you are fully responsible for all backups.<\\/li><li style=\\\"margin-top:0px;margin-right:0px;margin-left:0px;\\\">We Don\'t support any child porn or such material.<\\/li><li style=\\\"margin-top:0px;margin-right:0px;margin-left:0px;\\\">No spam-related sites or material, such as email lists, mass mail programs, and scripts, etc.<\\/li><li style=\\\"margin-top:0px;margin-right:0px;margin-left:0px;\\\">No harassing material that may cause people to retaliate against you.<\\/li><li style=\\\"margin-top:0px;margin-right:0px;margin-left:0px;\\\">No phishing pages.<\\/li><li style=\\\"margin-top:0px;margin-right:0px;margin-left:0px;\\\">You may not run any exploitation script from the server. reason can be terminated immediately.<\\/li><li style=\\\"margin-top:0px;margin-right:0px;margin-left:0px;\\\">If Anyone attempting to hack or exploit the server by using your script or hosting, we will terminate your account to keep safe other users.<\\/li><li style=\\\"margin-top:0px;margin-right:0px;margin-left:0px;\\\">Malicious Botnets are strictly forbidden.<\\/li><li style=\\\"margin-top:0px;margin-right:0px;margin-left:0px;\\\">Spam, mass mailing, or email marketing in any way are strictly forbidden here.<\\/li><li style=\\\"margin-top:0px;margin-right:0px;margin-left:0px;\\\">Malicious hacking materials, trojans, viruses, &amp; malicious bots running or for download are forbidden.<\\/li><li style=\\\"margin-top:0px;margin-right:0px;margin-left:0px;\\\">Resource and cronjob abuse is forbidden and will result in suspension or termination.<\\/li><li style=\\\"margin-top:0px;margin-right:0px;margin-left:0px;\\\">Php\\/CGI proxies are strictly forbidden.<\\/li><li style=\\\"margin-top:0px;margin-right:0px;margin-left:0px;\\\">CGI-IRC is strictly forbidden.<\\/li><li style=\\\"margin-top:0px;margin-right:0px;margin-left:0px;\\\">No fake or disposal mailers, mass mailing, mail bombers, SMS bombers, etc.<\\/li><li style=\\\"margin-top:0px;margin-right:0px;margin-left:0px;\\\">NO CREDIT OR REFUND will be granted for interruptions of service, due to User Agreement violations.<\\/li><\\/ul><\\/div><div class=\\\"mb-5\\\" style=\\\"color:rgb(111,111,111);font-family:Nunito, sans-serif;margin-bottom:3rem;\\\"><h3 class=\\\"mb-3\\\" style=\\\"font-weight:600;line-height:1.3;font-size:24px;font-family:Exo, sans-serif;color:rgb(54,54,54);\\\">Terms &amp; Conditions for Users<\\/h3><p class=\\\"font-18\\\" style=\\\"margin-right:0px;margin-left:0px;font-size:18px;\\\">Before getting to this site, you are consenting to be limited by these site Terms and Conditions of Use, every single appropriate law, and guidelines, and concur that you are answerable for consistency with any material neighborhood laws. If you disagree with any of these terms, you are restricted from utilizing or getting to this site.<\\/p><\\/div><div class=\\\"mb-5\\\" style=\\\"color:rgb(111,111,111);font-family:Nunito, sans-serif;margin-bottom:3rem;\\\"><h3 class=\\\"mb-3\\\" style=\\\"font-weight:600;line-height:1.3;font-size:24px;font-family:Exo, sans-serif;color:rgb(54,54,54);\\\">Support<\\/h3><p class=\\\"font-18\\\" style=\\\"margin-right:0px;margin-left:0px;font-size:18px;\\\">Whenever you have downloaded our item, you may get in touch with us for help through email and we will give a valiant effort to determine your issue. We will attempt to answer using the Email for more modest bug fixes, after which we will refresh the center bundle. Content help is offered to confirmed clients by Tickets as it were. Backing demands made by email and Livechat.<\\/p><p class=\\\"my-3 font-18 font-weight-bold\\\" style=\\\"margin-right:0px;margin-left:0px;font-size:18px;\\\">On the off chance that your help requires extra adjustment of the System, at that point, you have two alternatives:<\\/p><ul class=\\\"font-18\\\" style=\\\"padding-left:15px;list-style-type:disc;font-size:18px;\\\"><li style=\\\"margin-top:0px;margin-right:0px;margin-left:0px;\\\">Hang tight for additional update discharge.<\\/li><li style=\\\"margin-top:0px;margin-right:0px;margin-left:0px;\\\">Or on the other hand, enlist a specialist (We offer customization for extra charges).<\\/li><\\/ul><\\/div><div class=\\\"mb-5\\\" style=\\\"color:rgb(111,111,111);font-family:Nunito, sans-serif;margin-bottom:3rem;\\\"><h3 class=\\\"mb-3\\\" style=\\\"font-weight:600;line-height:1.3;font-size:24px;font-family:Exo, sans-serif;color:rgb(54,54,54);\\\">Ownership<\\/h3><p class=\\\"font-18\\\" style=\\\"margin-right:0px;margin-left:0px;font-size:18px;\\\">You may not guarantee scholarly or selective possession of any of our items, altered or unmodified. All items are property, we created them. Our items are given \\\"with no guarantees\\\" without guarantee of any sort, either communicated or suggested. On no occasion will our juridical individual be subject to any harms including, however not restricted to, immediate, roundabout, extraordinary, accidental, or significant harms or different misfortunes emerging out of the utilization of or powerlessness to utilize our items.<\\/p><\\/div><div class=\\\"mb-5\\\" style=\\\"color:rgb(111,111,111);font-family:Nunito, sans-serif;margin-bottom:3rem;\\\"><h3 class=\\\"mb-3\\\" style=\\\"font-weight:600;line-height:1.3;font-size:24px;font-family:Exo, sans-serif;color:rgb(54,54,54);\\\">Warranty<\\/h3><p class=\\\"font-18\\\" style=\\\"margin-right:0px;margin-left:0px;font-size:18px;\\\">We don\'t offer any guarantee or assurance of these Services in any way. When our Services have been modified we can\'t ensure they will work with all outsider plugins, modules, or internet browsers. Program similarity ought to be tried against the show formats on the demo worker. If you don\'t mind guarantee that the programs you use will work with the component, as we can not ensure that our systems will work with all program mixes.<\\/p><\\/div><div class=\\\"mb-5\\\" style=\\\"color:rgb(111,111,111);font-family:Nunito, sans-serif;margin-bottom:3rem;\\\"><h3 class=\\\"mb-3\\\" style=\\\"font-weight:600;line-height:1.3;font-size:24px;font-family:Exo, sans-serif;color:rgb(54,54,54);\\\">Unauthorized\\/Illegal Usage<\\/h3><p class=\\\"font-18\\\" style=\\\"margin-right:0px;margin-left:0px;font-size:18px;\\\">You may not utilize our things for any illicit or unapproved reason or may you, in the utilization of the stage, disregard any laws in your locale (counting yet not restricted to copyright laws) just as the laws of your nation and International law. Specifically, it is disallowed to utilize the things on our foundation for pages that advance: brutality, illegal intimidation, hard sexual entertainment, bigotry, obscenity content or warez programming joins.<br \\/><br \\/>You can\'t imitate, copy, duplicate, sell, exchange or adventure any of our segment, utilization of the offered on our things, or admittance to the administration without the express composed consent by us or item proprietor.<br \\/><br \\/>Our Members are liable for all substance posted on the discussion and demo and movement that happens under your record.<br \\/><br \\/>We hold the chance of hindering your participation account quickly if we will think about a particularly not allowed conduct.<br \\/><br \\/>If you make a record on our site, you are liable for keeping up the security of your record, and you are completely answerable for all exercises that happen under the record and some other activities taken regarding the record. You should quickly inform us, of any unapproved employments of your record or some other penetrates of security.<\\/p><\\/div><div class=\\\"mb-5\\\" style=\\\"color:rgb(111,111,111);font-family:Nunito, sans-serif;margin-bottom:3rem;\\\"><h3 class=\\\"mb-3\\\" style=\\\"font-weight:600;line-height:1.3;font-size:24px;font-family:Exo, sans-serif;color:rgb(54,54,54);\\\">Fiverr, Seoclerks Sellers Or Affiliates<\\/h3><p class=\\\"font-18\\\" style=\\\"margin-right:0px;margin-left:0px;font-size:18px;\\\">We do NOT ensure full SEO campaign conveyance within 24 hours. We make no assurance for conveyance time by any means. We give our best assessment to orders during the putting in of requests, anyway, these are gauges. We won\'t be considered liable for loss of assets, negative surveys or you being prohibited for late conveyance. If you are selling on a site that requires time touchy outcomes, utilize Our SEO Services at your own risk.<\\/p><\\/div><div class=\\\"mb-5\\\" style=\\\"color:rgb(111,111,111);font-family:Nunito, sans-serif;margin-bottom:3rem;\\\"><h3 class=\\\"mb-3\\\" style=\\\"font-weight:600;line-height:1.3;font-size:24px;font-family:Exo, sans-serif;color:rgb(54,54,54);\\\">Payment\\/Refund Policy<\\/h3><p class=\\\"font-18\\\" style=\\\"margin-right:0px;margin-left:0px;font-size:18px;\\\">No refund or cash back will be made. After a deposit has been finished, it is extremely unlikely to invert it. You should utilize your equilibrium on requests our administrations, Hosting, SEO campaign. You concur that once you complete a deposit, you won\'t document a debate or a chargeback against us in any way, shape, or form.<br \\/><br \\/>If you document a debate or chargeback against us after a deposit, we claim all authority to end every single future request, prohibit you from our site. False action, for example, utilizing unapproved or taken charge cards will prompt the end of your record. There are no special cases.<\\/p><\\/div><div class=\\\"mb-5\\\" style=\\\"color:rgb(111,111,111);font-family:Nunito, sans-serif;margin-bottom:3rem;\\\"><h3 class=\\\"mb-3\\\" style=\\\"font-weight:600;line-height:1.3;font-size:24px;font-family:Exo, sans-serif;color:rgb(54,54,54);\\\">Free Balance \\/ Coupon Policy<\\/h3><p class=\\\"font-18\\\" style=\\\"margin-right:0px;margin-left:0px;font-size:18px;\\\">We offer numerous approaches to get FREE Balance, Coupons and Deposit offers yet we generally reserve the privilege to audit it and deduct it from your record offset with any explanation we may it is a sort of misuse. If we choose to deduct a few or all of free Balance from your record balance, and your record balance becomes negative, at that point the record will naturally be suspended. If your record is suspended because of a negative Balance you can request to make a custom payment to settle your equilibrium to actuate your record.<\\/p><\\/div>\"}', NULL, 'basic', 'terms-of-service', '2021-06-09 08:51:18', '2021-06-09 08:51:18'),
(13, 'about.element', '{\"icon\":\"<i class=\\\"fas fa-shield-alt\\\"><\\/i>\",\"details\":\"Safe and secure transactions\"}', NULL, 'basic', NULL, '2022-11-28 09:31:25', '2022-11-28 09:31:25'),
(14, 'about.element', '{\"icon\":\"<i class=\\\"fas fa-file-alt\\\"><\\/i>\",\"details\":\"Hassle Free setup of the Escrow Account\"}', NULL, 'basic', NULL, '2022-11-28 09:31:42', '2023-01-05 13:49:40'),
(15, 'about.element', '{\"icon\":\"<i class=\\\"fas fa-home\\\"><\\/i>\",\"details\":\"Doorstep service\"}', NULL, 'basic', NULL, '2022-11-28 09:31:57', '2023-01-05 13:49:40'),
(16, 'about.element', '{\"icon\":\"<i class=\\\"fas fa-hourglass-end\\\"><\\/i>\",\"details\":\"Nil fee for account setup\"}', NULL, 'basic', NULL, '2022-11-28 09:32:14', '2023-01-05 13:49:40'),
(17, 'blog.element', '{\"has_image\":[\"1\"],\"title\":\"Fusce vulputate eleifend sapien Vestibulum purus quam scelerisque mollis seonummy metus.\",\"description\":\"<p class=\\\"fs--18px\\\" style=\\\"margin-right:0px;margin-left:0px;color:rgb(102,102,102);font-size:1.125rem;\\\">Nobis,\\r\\n maiores. Dolores nesciunt, quibusdam sed, velit dicta qui atque, ad \\r\\ndoloribus eveniet cupiditate pariatur doloremque nihil harum nemo \\r\\nvoluptatum illum. Alias doloribus eveniet cupiditate.<\\/p><p class=\\\"mt-3\\\" style=\\\"margin-right:0px;margin-left:0px;color:rgb(102,102,102);font-size:15px;\\\">Fugiat\\r\\n doloremque mollitia a adipisci voluptas natus aperiam numquam libero \\r\\nfacilis. Veniam dignissimos natus ab doloremque exercitationem minima \\r\\nneque, iusto, ullam nostrum impedit dolores quos architecto aut ipsa \\r\\nnihil dolore facere, inventore quidem voluptates. Impedit, obcaecati \\r\\nnumquam! Corrupti eos alias quibusdam sint cupiditate at iure \\r\\nreprehenderit a debitis id enim explicabo, nemo expedita magni nesciunt \\r\\nexcepturi dicta omnis. Minus quibusdam nulla officia eos ipsam soluta, \\r\\niusto omnis repellendus consequuntur cupiditate temporibus, commodi \\r\\nexpedita atque architecto praesentium suscipit molestias dignissimos, \\r\\nimpedit itaque aliquam nam dolore explicabo! Ad dolores beatae ipsum \\r\\nnemo provident voluptatibus Minus quibusdam nulla officia eos.<\\/p><blockquote style=\\\"margin-top:1.25rem;padding:1.875rem;background-color:rgb(241,241,241);font-style:italic;color:rgb(102,102,102);font-size:15px;\\\">Lorem\\r\\n ipsum dolor sit amet consectetur adipisicing elit. Consequatur autem \\r\\nquis odio, praesentium deserunt est dolores aliquid, eos officia ad quia\\r\\n voluptatum, tempore nulla ex necessitatibus recusandae itaque ipsam \\r\\nbeatae. Corrupti eos alias quibusdam sint cupiditate dolores beatae.<\\/blockquote><p class=\\\"mt-4\\\" style=\\\"margin-right:0px;margin-left:0px;color:rgb(102,102,102);font-size:15px;\\\">Totam\\r\\n minima nulla, placeat quidem, omnis sed laboriosam fuga maxime animi, \\r\\ncupiditate molestias consectetur. Labore, assumenda eaque. Officiis \\r\\nvoluptas accusantium alias nostrum fugit dolore eos blanditiis aperiam, \\r\\nillo consequuntur repellendus doloribus a porro exercitationem quia. \\r\\nAccusamus molestiae beatae alias, veritatis delectus reiciendis est \\r\\nharum ex nesciunt rem? Recusandae et nihil id ducimus aliquid, pariatur \\r\\naut tempore doloremque ratione cum in non eius maiores a voluptatum, \\r\\nquam aliquam perspiciatis magnam provident. Et odit saepe illo libero, \\r\\nvoluptatem optio aliquam deserunt nam accusamus in commodi dolorum \\r\\npariatur. Et quo labore velit nesciunt.<\\/p>\",\"image\":\"6384a51ec33881669637406.png\"}', NULL, 'basic', 'fusce-vulputate-eleifend-sapien-vestibulum-purus-quam-scelerisque-mollis-seonummy-metus', '2022-11-28 09:40:06', '2023-01-05 13:49:40'),
(18, 'blog.element', '{\"has_image\":[\"1\"],\"title\":\"Curabitur a felis in nunc fringilla tristique abot escrow.\",\"description\":\"<p class=\\\"fs--18px\\\" style=\\\"margin-right:0px;margin-left:0px;color:rgb(102,102,102);font-size:1.125rem;\\\">Nobis,\\r\\n maiores. Dolores nesciunt, quibusdam sed, velit dicta qui atque, ad \\r\\ndoloribus eveniet cupiditate pariatur doloremque nihil harum nemo \\r\\nvoluptatum illum. Alias doloribus eveniet cupiditate.<\\/p><p class=\\\"mt-3\\\" style=\\\"margin-right:0px;margin-left:0px;color:rgb(102,102,102);font-size:15px;\\\">Fugiat\\r\\n doloremque mollitia a adipisci voluptas natus aperiam numquam libero \\r\\nfacilis. Veniam dignissimos natus ab doloremque exercitationem minima \\r\\nneque, iusto, ullam nostrum impedit dolores quos architecto aut ipsa \\r\\nnihil dolore facere, inventore quidem voluptates. Impedit, obcaecati \\r\\nnumquam! Corrupti eos alias quibusdam sint cupiditate at iure \\r\\nreprehenderit a debitis id enim explicabo, nemo expedita magni nesciunt \\r\\nexcepturi dicta omnis. Minus quibusdam nulla officia eos ipsam soluta, \\r\\niusto omnis repellendus consequuntur cupiditate temporibus, commodi \\r\\nexpedita atque architecto praesentium suscipit molestias dignissimos, \\r\\nimpedit itaque aliquam nam dolore explicabo! Ad dolores beatae ipsum \\r\\nnemo provident voluptatibus Minus quibusdam nulla officia eos.<\\/p><blockquote style=\\\"margin-top:1.25rem;padding:1.875rem;background-color:rgb(241,241,241);font-style:italic;color:rgb(102,102,102);font-size:15px;\\\">Lorem\\r\\n ipsum dolor sit amet consectetur adipisicing elit. Consequatur autem \\r\\nquis odio, praesentium deserunt est dolores aliquid, eos officia ad quia\\r\\n voluptatum, tempore nulla ex necessitatibus recusandae itaque ipsam \\r\\nbeatae. Corrupti eos alias quibusdam sint cupiditate dolores beatae.<\\/blockquote><p class=\\\"mt-4\\\" style=\\\"margin-right:0px;margin-left:0px;color:rgb(102,102,102);font-size:15px;\\\">Totam\\r\\n minima nulla, placeat quidem, omnis sed laboriosam fuga maxime animi, \\r\\ncupiditate molestias consectetur. Labore, assumenda eaque. Officiis \\r\\nvoluptas accusantium alias nostrum fugit dolore eos blanditiis aperiam, \\r\\nillo consequuntur repellendus doloribus a porro exercitationem quia. \\r\\nAccusamus molestiae beatae alias, veritatis delectus reiciendis est \\r\\nharum ex nesciunt rem? Recusandae et nihil id ducimus aliquid, pariatur \\r\\naut tempore doloremque ratione cum in non eius maiores a voluptatum, \\r\\nquam aliquam perspiciatis magnam provident. Et odit saepe illo libero, \\r\\nvoluptatem optio aliquam deserunt nam accusamus in commodi dolorum \\r\\npariatur. Et quo labore velit nesciunt.<\\/p>\",\"image\":\"6384a53bd5d981669637435.png\"}', NULL, 'basic', 'curabitur-a-felis-in-nunc-fringilla-tristique-abot-escrow', '2022-11-28 09:40:35', '2023-01-05 13:49:40'),
(19, 'blog.element', '{\"has_image\":[\"1\"],\"title\":\"Curabitur a felis in nunc fringilla tristique abot escrow.\",\"description\":\"<p class=\\\"fs--18px\\\" style=\\\"margin-right:0px;margin-left:0px;color:rgb(102,102,102);font-size:1.125rem;\\\">Nobis,\\r\\n maiores. Dolores nesciunt, quibusdam sed, velit dicta qui atque, ad \\r\\ndoloribus eveniet cupiditate pariatur doloremque nihil harum nemo \\r\\nvoluptatum illum. Alias doloribus eveniet cupiditate.<\\/p><p class=\\\"mt-3\\\" style=\\\"margin-right:0px;margin-left:0px;color:rgb(102,102,102);font-size:15px;\\\">Fugiat\\r\\n doloremque mollitia a adipisci voluptas natus aperiam numquam libero \\r\\nfacilis. Veniam dignissimos natus ab doloremque exercitationem minima \\r\\nneque, iusto, ullam nostrum impedit dolores quos architecto aut ipsa \\r\\nnihil dolore facere, inventore quidem voluptates. Impedit, obcaecati \\r\\nnumquam! Corrupti eos alias quibusdam sint cupiditate at iure \\r\\nreprehenderit a debitis id enim explicabo, nemo expedita magni nesciunt \\r\\nexcepturi dicta omnis. Minus quibusdam nulla officia eos ipsam soluta, \\r\\niusto omnis repellendus consequuntur cupiditate temporibus, commodi \\r\\nexpedita atque architecto praesentium suscipit molestias dignissimos, \\r\\nimpedit itaque aliquam nam dolore explicabo! Ad dolores beatae ipsum \\r\\nnemo provident voluptatibus Minus quibusdam nulla officia eos.<\\/p><blockquote style=\\\"margin-top:1.25rem;padding:1.875rem;background-color:rgb(241,241,241);font-style:italic;color:rgb(102,102,102);font-size:15px;\\\">Lorem\\r\\n ipsum dolor sit amet consectetur adipisicing elit. Consequatur autem \\r\\nquis odio, praesentium deserunt est dolores aliquid, eos officia ad quia\\r\\n voluptatum, tempore nulla ex necessitatibus recusandae itaque ipsam \\r\\nbeatae. Corrupti eos alias quibusdam sint cupiditate dolores beatae.<\\/blockquote><p class=\\\"mt-4\\\" style=\\\"margin-right:0px;margin-left:0px;color:rgb(102,102,102);font-size:15px;\\\">Totam\\r\\n minima nulla, placeat quidem, omnis sed laboriosam fuga maxime animi, \\r\\ncupiditate molestias consectetur. Labore, assumenda eaque. Officiis \\r\\nvoluptas accusantium alias nostrum fugit dolore eos blanditiis aperiam, \\r\\nillo consequuntur repellendus doloribus a porro exercitationem quia. \\r\\nAccusamus molestiae beatae alias, veritatis delectus reiciendis est \\r\\nharum ex nesciunt rem? Recusandae et nihil id ducimus aliquid, pariatur \\r\\naut tempore doloremque ratione cum in non eius maiores a voluptatum, \\r\\nquam aliquam perspiciatis magnam provident. Et odit saepe illo libero, \\r\\nvoluptatem optio aliquam deserunt nam accusamus in commodi dolorum \\r\\npariatur. Et quo labore velit nesciunt.<\\/p>\",\"image\":\"6384a5b250b331669637554.jpg\"}', NULL, 'basic', 'curabitur-a-felis-in-nunc-fringilla-tristique-abot-escrow', '2022-11-28 09:42:34', '2023-01-05 13:49:40'),
(20, 'blog.element', '{\"has_image\":[\"1\"],\"title\":\"Curabitur a felis in nunc fringilla tristique abot escrow.\",\"description\":\"<p class=\\\"fs--18px\\\" style=\\\"margin-right:0px;margin-left:0px;color:rgb(102,102,102);font-size:1.125rem;\\\">Nobis,\\r\\n maiores. Dolores nesciunt, quibusdam sed, velit dicta qui atque, ad \\r\\ndoloribus eveniet cupiditate pariatur doloremque nihil harum nemo \\r\\nvoluptatum illum. Alias doloribus eveniet cupiditate.<\\/p><p class=\\\"mt-3\\\" style=\\\"margin-right:0px;margin-left:0px;color:rgb(102,102,102);font-size:15px;\\\">Fugiat\\r\\n doloremque mollitia a adipisci voluptas natus aperiam numquam libero \\r\\nfacilis. Veniam dignissimos natus ab doloremque exercitationem minima \\r\\nneque, iusto, ullam nostrum impedit dolores quos architecto aut ipsa \\r\\nnihil dolore facere, inventore quidem voluptates. Impedit, obcaecati \\r\\nnumquam! Corrupti eos alias quibusdam sint cupiditate at iure \\r\\nreprehenderit a debitis id enim explicabo, nemo expedita magni nesciunt \\r\\nexcepturi dicta omnis. Minus quibusdam nulla officia eos ipsam soluta, \\r\\niusto omnis repellendus consequuntur cupiditate temporibus, commodi \\r\\nexpedita atque architecto praesentium suscipit molestias dignissimos, \\r\\nimpedit itaque aliquam nam dolore explicabo! Ad dolores beatae ipsum \\r\\nnemo provident voluptatibus Minus quibusdam nulla officia eos.<\\/p><blockquote style=\\\"margin-top:1.25rem;padding:1.875rem;background-color:rgb(241,241,241);font-style:italic;color:rgb(102,102,102);font-size:15px;\\\">Lorem\\r\\n ipsum dolor sit amet consectetur adipisicing elit. Consequatur autem \\r\\nquis odio, praesentium deserunt est dolores aliquid, eos officia ad quia\\r\\n voluptatum, tempore nulla ex necessitatibus recusandae itaque ipsam \\r\\nbeatae. Corrupti eos alias quibusdam sint cupiditate dolores beatae.<\\/blockquote><p class=\\\"mt-4\\\" style=\\\"margin-right:0px;margin-left:0px;color:rgb(102,102,102);font-size:15px;\\\">Totam\\r\\n minima nulla, placeat quidem, omnis sed laboriosam fuga maxime animi, \\r\\ncupiditate molestias consectetur. Labore, assumenda eaque. Officiis \\r\\nvoluptas accusantium alias nostrum fugit dolore eos blanditiis aperiam, \\r\\nillo consequuntur repellendus doloribus a porro exercitationem quia. \\r\\nAccusamus molestiae beatae alias, veritatis delectus reiciendis est \\r\\nharum ex nesciunt rem? Recusandae et nihil id ducimus aliquid, pariatur \\r\\naut tempore doloremque ratione cum in non eius maiores a voluptatum, \\r\\nquam aliquam perspiciatis magnam provident. Et odit saepe illo libero, \\r\\nvoluptatem optio aliquam deserunt nam accusamus in commodi dolorum \\r\\npariatur. Et quo labore velit nesciunt.<\\/p>\",\"image\":\"6384a5d6da8ef1669637590.jpg\"}', NULL, 'basic', 'curabitur-a-felis-in-nunc-fringilla-tristique-abot-escrow', '2022-11-28 09:43:10', '2023-01-05 13:49:40'),
(21, 'blog.element', '{\"has_image\":[\"1\"],\"title\":\"Curabitur a felis in nunc fringilla tristique abot escrow.\",\"description\":\"<p class=\\\"fs--18px\\\" style=\\\"margin-right:0px;margin-left:0px;color:rgb(102,102,102);font-size:1.125rem;\\\">Nobis,\\r\\n maiores. Dolores nesciunt, quibusdam sed, velit dicta qui atque, ad \\r\\ndoloribus eveniet cupiditate pariatur doloremque nihil harum nemo \\r\\nvoluptatum illum. Alias doloribus eveniet cupiditate.<\\/p><p class=\\\"mt-3\\\" style=\\\"margin-right:0px;margin-left:0px;color:rgb(102,102,102);font-size:15px;\\\">Fugiat\\r\\n doloremque mollitia a adipisci voluptas natus aperiam numquam libero \\r\\nfacilis. Veniam dignissimos natus ab doloremque exercitationem minima \\r\\nneque, iusto, ullam nostrum impedit dolores quos architecto aut ipsa \\r\\nnihil dolore facere, inventore quidem voluptates. Impedit, obcaecati \\r\\nnumquam! Corrupti eos alias quibusdam sint cupiditate at iure \\r\\nreprehenderit a debitis id enim explicabo, nemo expedita magni nesciunt \\r\\nexcepturi dicta omnis. Minus quibusdam nulla officia eos ipsam soluta, \\r\\niusto omnis repellendus consequuntur cupiditate temporibus, commodi \\r\\nexpedita atque architecto praesentium suscipit molestias dignissimos, \\r\\nimpedit itaque aliquam nam dolore explicabo! Ad dolores beatae ipsum \\r\\nnemo provident voluptatibus Minus quibusdam nulla officia eos.<\\/p><blockquote style=\\\"margin-top:1.25rem;padding:1.875rem;background-color:rgb(241,241,241);font-style:italic;color:rgb(102,102,102);font-size:15px;\\\">Lorem\\r\\n ipsum dolor sit amet consectetur adipisicing elit. Consequatur autem \\r\\nquis odio, praesentium deserunt est dolores aliquid, eos officia ad quia\\r\\n voluptatum, tempore nulla ex necessitatibus recusandae itaque ipsam \\r\\nbeatae. Corrupti eos alias quibusdam sint cupiditate dolores beatae.<\\/blockquote><p class=\\\"mt-4\\\" style=\\\"margin-right:0px;margin-left:0px;color:rgb(102,102,102);font-size:15px;\\\">Totam\\r\\n minima nulla, placeat quidem, omnis sed laboriosam fuga maxime animi, \\r\\ncupiditate molestias consectetur. Labore, assumenda eaque. Officiis \\r\\nvoluptas accusantium alias nostrum fugit dolore eos blanditiis aperiam, \\r\\nillo consequuntur repellendus doloribus a porro exercitationem quia. \\r\\nAccusamus molestiae beatae alias, veritatis delectus reiciendis est \\r\\nharum ex nesciunt rem? Recusandae et nihil id ducimus aliquid, pariatur \\r\\naut tempore doloremque ratione cum in non eius maiores a voluptatum, \\r\\nquam aliquam perspiciatis magnam provident. Et odit saepe illo libero, \\r\\nvoluptatem optio aliquam deserunt nam accusamus in commodi dolorum \\r\\npariatur. Et quo labore velit nesciunt.<\\/p>\",\"image\":\"6384a60c8bc571669637644.jpg\"}', NULL, 'basic', 'curabitur-a-felis-in-nunc-fringilla-tristique-abot-escrow', '2022-11-28 09:44:04', '2023-01-05 13:49:40'),
(22, 'breadcrumb.content', '{\"has_image\":\"1\",\"image\":\"6384a640850f61669637696.png\"}', NULL, 'basic', NULL, '2022-11-28 09:44:56', '2023-01-05 13:49:40'),
(23, 'contact.content', '{\"has_image\":\"1\",\"heading\":\"Send Your Messages\",\"latitude\":\"23.7925\",\"longitude\":\"90.4078\",\"image\":\"63a837f70b4e01671968759.png\"}', NULL, 'basic', NULL, '2022-11-28 09:45:29', '2023-01-05 13:49:40'),
(24, 'contact.element', '{\"icon\":\"<i class=\\\"fas fa-map-marker-alt\\\"><\\/i>\",\"heading\":\"Office Location\",\"details\":\"15205 North Kierland Blvd.100\"}', NULL, 'basic', NULL, '2022-11-28 09:46:01', '2023-01-05 13:49:40'),
(25, 'contact.element', '{\"icon\":\"<i class=\\\"fas fa-envelope-open-text\\\"><\\/i>\",\"heading\":\"Email address\",\"details\":\"support@mail.com\"}', NULL, 'basic', NULL, '2022-11-28 09:46:24', '2023-01-05 13:49:40'),
(26, 'contact.element', '{\"icon\":\"<i class=\\\"fas fa-phone-alt\\\"><\\/i>\",\"heading\":\"Phone number\",\"details\":\"123 - 456 - 7890\"}', NULL, 'basic', NULL, '2022-11-28 09:47:40', '2023-01-05 13:49:40'),
(27, 'coverage.content', '{\"has_image\":\"1\",\"heading\":\"Online Escrow Service In The World\",\"subheading\":\"Online Escrow Service In The World\",\"image\":\"6384a6f9afd101669637881.png\"}', NULL, 'basic', NULL, '2022-11-28 09:48:01', '2023-01-05 13:49:40'),
(28, 'faq.content', '{\"has_image\":\"1\",\"heading\":\"Frequently Asked Questions\",\"subheading\":\"Lorem ipsum dolor sit amet consectetuer dipiscing elit. Aenean modo lula eget dolor. Aenean massa.\",\"image\":\"6384a71bd22901669637915.png\"}', NULL, 'basic', NULL, '2022-11-28 09:48:35', '2023-01-05 13:49:40'),
(29, 'faq.element', '{\"question\":\"What should you not do while in escrow?\",\"answer\":\"<span style=\\\"color:rgb(0,42,71);font-weight:bolder;font-family:Roboto, sans-serif;\\\">This is the first item\'s accordion body.<\\/span><span style=\\\"color:rgb(0,42,71);font-family:Roboto, sans-serif;\\\">\\u00a0It is shown by default, until the collapse plugin adds the appropriate classes that we use to style each element. These classes control the overall appearance, as well as the showing and hiding via CSS transitions. You can modify any of this with custom CSS or overriding our default variables. It\'s also worth noting that just about any HTML can go within the\\u00a0<\\/span><code style=\\\"font-family:\'SFMono-Regular\', Menlo, Monaco, Consolas, \'Liberation Mono\', \'Courier New\', monospace;\\\">.accordion-body<\\/code><span style=\\\"color:rgb(0,42,71);font-family:Roboto, sans-serif;\\\">, though the transition does limit overflow.<\\/span><br \\/>\"}', NULL, 'basic', NULL, '2022-11-28 09:48:51', '2023-01-05 13:49:40'),
(30, 'faq.element', '{\"question\":\"What should you not do while in escrow?\",\"answer\":\"<span style=\\\"color:rgb(0,42,71);font-weight:bolder;font-family:Roboto, sans-serif;\\\">This is the first item\'s accordion body.<\\/span><span style=\\\"color:rgb(0,42,71);font-family:Roboto, sans-serif;\\\">\\u00a0It is shown by default, until the collapse plugin adds the appropriate classes that we use to style each element. These classes control the overall appearance, as well as the showing and hiding via CSS transitions. You can modify any of this with custom CSS or overriding our default variables. It\'s also worth noting that just about any HTML can go within the\\u00a0<\\/span><code style=\\\"font-family:\'SFMono-Regular\', Menlo, Monaco, Consolas, \'Liberation Mono\', \'Courier New\', monospace;\\\">.accordion-body<\\/code><span style=\\\"color:rgb(0,42,71);font-family:Roboto, sans-serif;\\\">, though the transition does limit overflow.<\\/span><br \\/>\"}', NULL, 'basic', NULL, '2022-11-28 09:49:11', '2023-01-05 13:49:41'),
(31, 'faq.element', '{\"question\":\"How long can funds be held in escrow?\",\"answer\":\"<span style=\\\"color:rgb(0,42,71);font-weight:bolder;font-family:Roboto, sans-serif;\\\">This is the first item\'s accordion body.<\\/span><span style=\\\"color:rgb(0,42,71);font-family:Roboto, sans-serif;\\\">\\u00a0It is shown by default, until the collapse plugin adds the appropriate classes that we use to style each element. These classes control the overall appearance, as well as the showing and hiding via CSS transitions. You can modify any of this with custom CSS or overriding our default variables. It\'s also worth noting that just about any HTML can go within the\\u00a0<\\/span><code style=\\\"font-family:\'SFMono-Regular\', Menlo, Monaco, Consolas, \'Liberation Mono\', \'Courier New\', monospace;\\\">.accordion-body<\\/code><span style=\\\"color:rgb(0,42,71);font-family:Roboto, sans-serif;\\\">, though the transition does limit overflow.<\\/span><br \\/>\"}', NULL, 'basic', NULL, '2022-11-28 09:49:19', '2023-01-05 13:49:41'),
(32, 'feature.element', '{\"icon\":\"<i class=\\\"fas fa-code\\\"><\\/i>\",\"title\":\"Built for developers, by developers\",\"details\":\"Maecenas tempus tellus eget condimentu ncus sem quam semper libero sit amet.\"}', NULL, 'basic', NULL, '2022-11-28 09:50:13', '2023-01-05 13:49:41'),
(33, 'feature.element', '{\"icon\":\"<i class=\\\"fas fa-shield-alt\\\"><\\/i>\",\"title\":\"Protecting both buyers and sellers\",\"details\":\"Maecenas tempus tellus eget condimentu ncus sem quam semper libero sit amet.\"}', NULL, 'basic', NULL, '2022-11-28 09:50:32', '2023-01-05 13:49:41'),
(34, 'feature.element', '{\"icon\":\"<i class=\\\"fab fa-html5\\\"><\\/i>\",\"title\":\"User friendly and responsive\",\"details\":\"Maecenas tempus tellus eget condimentu ncus sem quam semper libero sit amet.\"}', NULL, 'basic', NULL, '2022-11-28 09:50:50', '2023-01-05 13:49:41'),
(35, 'how_works.content', '{\"heading\":\"How The Escrow Process Works\",\"subheading\":\"Lorem ipsum dolor sit amet consectetuer adipiscing elit. Aenean modo lula eget dolor. Aenean massa.\"}', NULL, 'basic', NULL, '2022-11-28 09:51:08', '2023-01-05 13:49:41'),
(36, 'how_works.element', '{\"icon\":\"<i class=\\\"fas fa-user-cog\\\"><\\/i>\",\"details\":\"Buyer and seller agree\"}', NULL, 'basic', NULL, '2022-11-28 09:51:24', '2023-01-05 13:49:41'),
(37, 'how_works.element', '{\"icon\":\"<i class=\\\"fas fa-paper-plane\\\"><\\/i>\",\"details\":\"Submits Payment to Escrow\"}', NULL, 'basic', NULL, '2022-11-28 09:51:40', '2023-01-05 13:49:41'),
(38, 'how_works.element', '{\"icon\":\"<i class=\\\"fas fa-box-open\\\"><\\/i>\",\"details\":\"Delivers Goods or Service to Buyer\"}', NULL, 'basic', NULL, '2022-11-28 09:51:54', '2023-01-05 13:49:41'),
(39, 'how_works.element', '{\"icon\":\"<i class=\\\"fas fa-credit-card\\\"><\\/i>\",\"details\":\"Releases Payment to Seller\"}', NULL, 'basic', NULL, '2022-11-28 09:52:08', '2023-01-05 13:49:41'),
(40, 'partner.element', '{\"has_image\":\"1\",\"image\":\"6384a80ce97291669638156.png\"}', NULL, 'basic', NULL, '2022-11-28 09:52:36', '2023-01-05 13:49:41'),
(41, 'partner.element', '{\"has_image\":\"1\",\"image\":\"6384a816911b11669638166.png\"}', NULL, 'basic', NULL, '2022-11-28 09:52:46', '2023-01-05 13:49:41'),
(42, 'partner.element', '{\"has_image\":\"1\",\"image\":\"6384a8211beb81669638177.png\"}', NULL, 'basic', NULL, '2022-11-28 09:52:57', '2023-01-05 13:49:41'),
(43, 'partner.element', '{\"has_image\":\"1\",\"image\":\"6384a82ab1dc61669638186.png\"}', NULL, 'basic', NULL, '2022-11-28 09:53:06', '2023-01-05 13:49:41'),
(44, 'partner.element', '{\"has_image\":\"1\",\"image\":\"6384a831bf72e1669638193.png\"}', NULL, 'basic', NULL, '2022-11-28 09:53:13', '2023-01-05 13:49:41'),
(45, 'partner.element', '{\"has_image\":\"1\",\"image\":\"6384a83baf2121669638203.png\"}', NULL, 'basic', NULL, '2022-11-28 09:53:23', '2023-01-05 13:49:41');
INSERT INTO `frontends` (`id`, `data_keys`, `data_values`, `seo_content`, `tempname`, `slug`, `created_at`, `updated_at`) VALUES
(46, 'policy_pages.element', '{\"title\":\"Payment Policy\",\"details\":\"<div class=\\\"mb-5\\\" style=\\\"margin-bottom:3rem;color:rgb(111,111,111);font-family:Nunito, sans-serif;\\\"><h3 class=\\\"mb-3\\\" style=\\\"font-weight:600;line-height:1.3;font-size:24px;color:rgb(54,54,54);font-family:Exo, sans-serif;\\\">What information do we collect?<\\/h3><p class=\\\"font-18\\\" style=\\\"margin-right:0px;margin-left:0px;font-size:18px;\\\">We gather data from you when you register on our site, submit a request, buy any services, react to an overview, or round out a structure. At the point when requesting any assistance or enrolling on our site, as suitable, you might be approached to enter your: name, email address, or telephone number. You may, nonetheless, visit our site anonymously.<\\/p><\\/div><div class=\\\"mb-5\\\" style=\\\"margin-bottom:3rem;color:rgb(111,111,111);font-family:Nunito, sans-serif;\\\"><h3 class=\\\"mb-3\\\" style=\\\"font-weight:600;line-height:1.3;font-size:24px;color:rgb(54,54,54);font-family:Exo, sans-serif;\\\">How do we protect your information?<\\/h3><p class=\\\"font-18\\\" style=\\\"margin-right:0px;margin-left:0px;font-size:18px;\\\">All provided delicate\\/credit data is sent through Stripe.<br \\/>After an exchange, your private data (credit cards, social security numbers, financials, and so on) won\'t be put away on our workers.<\\/p><\\/div><div class=\\\"mb-5\\\" style=\\\"margin-bottom:3rem;color:rgb(111,111,111);font-family:Nunito, sans-serif;\\\"><h3 class=\\\"mb-3\\\" style=\\\"font-weight:600;line-height:1.3;font-size:24px;color:rgb(54,54,54);font-family:Exo, sans-serif;\\\">Do we disclose any information to outside parties?<\\/h3><p class=\\\"font-18\\\" style=\\\"margin-right:0px;margin-left:0px;font-size:18px;\\\">We don\'t sell, exchange, or in any case move to outside gatherings by and by recognizable data. This does exclude confided in outsiders who help us in working our site, leading our business, or adjusting you, since those gatherings consent to keep this data private. We may likewise deliver your data when we accept discharge is suitable to follow the law, implement our site strategies, or ensure our own or others\' rights, property, or wellbeing.<\\/p><\\/div><div class=\\\"mb-5\\\" style=\\\"margin-bottom:3rem;color:rgb(111,111,111);font-family:Nunito, sans-serif;\\\"><h3 class=\\\"mb-3\\\" style=\\\"font-weight:600;line-height:1.3;font-size:24px;color:rgb(54,54,54);font-family:Exo, sans-serif;\\\">Children\'s Online Privacy Protection Act Compliance<\\/h3><p class=\\\"font-18\\\" style=\\\"margin-right:0px;margin-left:0px;font-size:18px;\\\">We are consistent with the prerequisites of COPPA (Children\'s Online Privacy Protection Act), we don\'t gather any data from anybody under 13 years old. Our site, items, and administrations are completely coordinated to individuals who are in any event 13 years of age or more established.<\\/p><\\/div><div class=\\\"mb-5\\\" style=\\\"margin-bottom:3rem;color:rgb(111,111,111);font-family:Nunito, sans-serif;\\\"><h3 class=\\\"mb-3\\\" style=\\\"font-weight:600;line-height:1.3;font-size:24px;color:rgb(54,54,54);font-family:Exo, sans-serif;\\\">Changes to our Privacy Policy<\\/h3><p class=\\\"font-18\\\" style=\\\"margin-right:0px;margin-left:0px;font-size:18px;\\\">If we decide to change our privacy policy, we will post those changes on this page.<\\/p><\\/div><div class=\\\"mb-5\\\" style=\\\"margin-bottom:3rem;color:rgb(111,111,111);font-family:Nunito, sans-serif;\\\"><h3 class=\\\"mb-3\\\" style=\\\"font-weight:600;line-height:1.3;font-size:24px;color:rgb(54,54,54);font-family:Exo, sans-serif;\\\">How long we retain your information?<\\/h3><p class=\\\"font-18\\\" style=\\\"margin-right:0px;margin-left:0px;font-size:18px;\\\">At the point when you register for our site, we cycle and keep your information we have about you however long you don\'t erase the record or withdraw yourself (subject to laws and guidelines).<\\/p><\\/div><div class=\\\"mb-5\\\" style=\\\"margin-bottom:3rem;color:rgb(111,111,111);font-family:Nunito, sans-serif;\\\"><h3 class=\\\"mb-3\\\" style=\\\"font-weight:600;line-height:1.3;font-size:24px;color:rgb(54,54,54);font-family:Exo, sans-serif;\\\">What we don\\u2019t do with your data<\\/h3><p class=\\\"font-18\\\" style=\\\"margin-right:0px;margin-left:0px;font-size:18px;\\\">We don\'t and will never share, unveil, sell, or in any case give your information to different organizations for the promoting of their items or administrations.<\\/p><\\/div>\"}', NULL, 'basic', 'payment-policy', '2022-11-28 09:53:56', '2023-01-05 13:49:41'),
(47, 'policy_pages.element', '{\"title\":\"Company Rules\",\"details\":\"<div class=\\\"mb-5\\\" style=\\\"margin-bottom:3rem;color:rgb(111,111,111);font-family:Nunito, sans-serif;\\\"><h3 class=\\\"mb-3\\\" style=\\\"font-weight:600;line-height:1.3;font-size:24px;color:rgb(54,54,54);font-family:Exo, sans-serif;\\\">What information do we collect?<\\/h3><p class=\\\"font-18\\\" style=\\\"margin-right:0px;margin-left:0px;font-size:18px;\\\">We gather data from you when you register on our site, submit a request, buy any services, react to an overview, or round out a structure. At the point when requesting any assistance or enrolling on our site, as suitable, you might be approached to enter your: name, email address, or telephone number. You may, nonetheless, visit our site anonymously.<\\/p><\\/div><div class=\\\"mb-5\\\" style=\\\"margin-bottom:3rem;color:rgb(111,111,111);font-family:Nunito, sans-serif;\\\"><h3 class=\\\"mb-3\\\" style=\\\"font-weight:600;line-height:1.3;font-size:24px;color:rgb(54,54,54);font-family:Exo, sans-serif;\\\">How do we protect your information?<\\/h3><p class=\\\"font-18\\\" style=\\\"margin-right:0px;margin-left:0px;font-size:18px;\\\">All provided delicate\\/credit data is sent through Stripe.<br \\/>After an exchange, your private data (credit cards, social security numbers, financials, and so on) won\'t be put away on our workers.<\\/p><\\/div><div class=\\\"mb-5\\\" style=\\\"margin-bottom:3rem;color:rgb(111,111,111);font-family:Nunito, sans-serif;\\\"><h3 class=\\\"mb-3\\\" style=\\\"font-weight:600;line-height:1.3;font-size:24px;color:rgb(54,54,54);font-family:Exo, sans-serif;\\\">Do we disclose any information to outside parties?<\\/h3><p class=\\\"font-18\\\" style=\\\"margin-right:0px;margin-left:0px;font-size:18px;\\\">We don\'t sell, exchange, or in any case move to outside gatherings by and by recognizable data. This does exclude confided in outsiders who help us in working our site, leading our business, or adjusting you, since those gatherings consent to keep this data private. We may likewise deliver your data when we accept discharge is suitable to follow the law, implement our site strategies, or ensure our own or others\' rights, property, or wellbeing.<\\/p><\\/div><div class=\\\"mb-5\\\" style=\\\"margin-bottom:3rem;color:rgb(111,111,111);font-family:Nunito, sans-serif;\\\"><h3 class=\\\"mb-3\\\" style=\\\"font-weight:600;line-height:1.3;font-size:24px;color:rgb(54,54,54);font-family:Exo, sans-serif;\\\">Children\'s Online Privacy Protection Act Compliance<\\/h3><p class=\\\"font-18\\\" style=\\\"margin-right:0px;margin-left:0px;font-size:18px;\\\">We are consistent with the prerequisites of COPPA (Children\'s Online Privacy Protection Act), we don\'t gather any data from anybody under 13 years old. Our site, items, and administrations are completely coordinated to individuals who are in any event 13 years of age or more established.<\\/p><\\/div><div class=\\\"mb-5\\\" style=\\\"margin-bottom:3rem;color:rgb(111,111,111);font-family:Nunito, sans-serif;\\\"><h3 class=\\\"mb-3\\\" style=\\\"font-weight:600;line-height:1.3;font-size:24px;color:rgb(54,54,54);font-family:Exo, sans-serif;\\\">Changes to our Privacy Policy<\\/h3><p class=\\\"font-18\\\" style=\\\"margin-right:0px;margin-left:0px;font-size:18px;\\\">If we decide to change our privacy policy, we will post those changes on this page.<\\/p><\\/div><div class=\\\"mb-5\\\" style=\\\"margin-bottom:3rem;color:rgb(111,111,111);font-family:Nunito, sans-serif;\\\"><h3 class=\\\"mb-3\\\" style=\\\"font-weight:600;line-height:1.3;font-size:24px;color:rgb(54,54,54);font-family:Exo, sans-serif;\\\">How long we retain your information?<\\/h3><p class=\\\"font-18\\\" style=\\\"margin-right:0px;margin-left:0px;font-size:18px;\\\">At the point when you register for our site, we cycle and keep your information we have about you however long you don\'t erase the record or withdraw yourself (subject to laws and guidelines).<\\/p><\\/div><div class=\\\"mb-5\\\" style=\\\"margin-bottom:3rem;color:rgb(111,111,111);font-family:Nunito, sans-serif;\\\"><h3 class=\\\"mb-3\\\" style=\\\"font-weight:600;line-height:1.3;font-size:24px;color:rgb(54,54,54);font-family:Exo, sans-serif;\\\">What we don\\u2019t do with your data<\\/h3><p class=\\\"font-18\\\" style=\\\"margin-right:0px;margin-left:0px;font-size:18px;\\\">We don\'t and will never share, unveil, sell, or in any case give your information to different organizations for the promoting of their items or administrations.<\\/p><\\/div>\"}', NULL, 'basic', 'company-rules', '2022-11-28 09:54:23', '2023-01-05 13:49:41'),
(48, 'service.element', '{\"icon\":\"<i class=\\\"fas fa-globe\\\"><\\/i>\",\"title\":\"Domain Names\",\"details\":\"parturient montes nasceturri necns quam felisultricies necpe\"}', NULL, 'basic', NULL, '2022-11-28 09:55:05', '2023-01-05 13:49:41'),
(49, 'service.element', '{\"icon\":\"<i class=\\\"fas fa-car\\\"><\\/i>\",\"title\":\"Motor Vehicles\",\"details\":\"parturient montes nasceturri necns quam felisultricies necpe\"}', NULL, 'basic', NULL, '2022-11-28 09:55:29', '2023-01-05 13:49:41'),
(50, 'service.element', '{\"icon\":\"<i class=\\\"fas fa-laptop\\\"><\\/i>\",\"title\":\"Electronics\",\"details\":\"parturient montes nasceturri necns quam felisultricies necpe\"}', NULL, 'basic', NULL, '2022-11-28 09:55:44', '2023-01-05 13:49:41'),
(51, 'service.element', '{\"icon\":\"<i class=\\\"fas fa-home\\\"><\\/i>\",\"title\":\"General Merchandise\",\"details\":\"parturient montes nasceturri necns quam felisultricies necpe\"}', NULL, 'basic', NULL, '2022-11-28 09:56:00', '2023-01-05 13:49:41'),
(52, 'social_icon.content', '{\"footer_text\":\"Maecenas vestibulum moll ellentesqueut neque. etesque habitant ristique enectus et netus etalesuada famesac turpi gestas. In dui magna.escrow.\"}', NULL, 'basic', NULL, '2022-11-28 09:56:27', '2023-01-05 13:49:41'),
(53, 'social_icon.element', '{\"title\":\"Twitter\",\"icon\":\"<i class=\\\"fab fa-twitter\\\"><\\/i>\",\"url\":\"https:\\/\\/www.twitter.com\\/\"}', NULL, 'basic', NULL, '2022-11-28 09:57:22', '2023-01-05 13:49:41'),
(54, 'social_icon.element', '{\"title\":\"Linkedin\",\"icon\":\"<i class=\\\"fab fa-linkedin-in\\\"><\\/i>\",\"url\":\"https:\\/\\/www.linkedin.com\\/\"}', NULL, 'basic', NULL, '2022-11-28 09:57:42', '2023-01-05 13:49:41'),
(55, 'social_icon.element', '{\"title\":\"Instagram\",\"icon\":\"<i class=\\\"fab fa-instagram\\\"><\\/i>\",\"url\":\"https:\\/\\/www.instagram.com\\/\"}', NULL, 'basic', NULL, '2022-11-28 09:57:59', '2023-01-05 13:49:41'),
(56, 'testimonial.content', '{\"heading\":\"What say our happy client\",\"subheading\":\"What say our happy client\"}', NULL, 'basic', NULL, '2022-11-28 09:59:02', '2023-01-05 13:49:41'),
(57, 'testimonial.element', '{\"has_image\":\"1\",\"name\":\"Sherrinford William\",\"location\":\"USA, California\",\"review\":\"Donec mollis hendrerit risus. Phasellus nec sem iellen tesque facilisis. Etiam imperdiet imperdiet orci. Nunc nec neque. Phasellus leo dolor tempus nonauctor ethendrerit\",\"image\":\"6384b049549ed1669640265.png\"}', NULL, 'basic', NULL, '2022-11-28 10:27:45', '2023-01-05 13:49:42'),
(58, 'testimonial.element', '{\"has_image\":\"1\",\"name\":\"Esther Howard\",\"location\":\"Client , USA\",\"review\":\"Donec mollis hendrerit risus. Phasellus nec sem iellen tesque facilisis. Etiam imperdiet imperdiet orci. Nunc nec neque. Phasellus leo dolor tempus nonauctor ethendrerit\",\"image\":\"6384b0650a2e21669640293.png\"}', NULL, 'basic', NULL, '2022-11-28 10:28:13', '2023-01-05 13:49:42'),
(59, 'testimonial.element', '{\"has_image\":\"1\",\"name\":\"Mr Kevin\",\"location\":\"Canada, Local\",\"review\":\"Donec mollis hendrerit risus. Phasellus nec sem iellen tesque facilisis. Etiam imperdiet imperdiet orci. Nunc nec neque. Phasellus leo dolor tempus nonauctor ethendrerit\",\"image\":\"6384b07ae7efb1669640314.png\"}', NULL, 'basic', NULL, '2022-11-28 10:28:34', '2023-01-05 13:49:42'),
(60, 'partner.content', '{\"heading\":\"OUR PARTNERS\",\"subheading\":\"Lorem ipsum dolor sit amet consectetuer adipiscing elit. Aenean modo lula eget dolor. Aenean massa.\"}', NULL, 'basic', NULL, '2022-11-28 10:29:41', '2023-01-05 13:49:42'),
(61, 'footer_section.content', '{\"footer_text\":\"Maecenas vestibulum moll ellentesqueut neque. etesque habitant ristique enectus et netus etalesuada famesac turpi gestas. In dui magna.escrow.\"}', NULL, 'basic', NULL, '2022-12-24 03:51:13', '2023-01-05 13:49:42'),
(62, 'register.content', '{\"has_image\":\"1\",\"heading\":\"Create Account\",\"subheading\":\"Maecenas tempus tellus eget condimentum rhoncus sequam semper libero sit amet\",\"image\":\"63a7eec439be21671950020.png\"}', NULL, 'basic', NULL, '2022-12-25 00:33:40', '2023-01-05 13:50:37'),
(63, 'login.content', '{\"has_image\":\"1\",\"heading\":\"Login Account\",\"subheading\":\"Maecenas tempus tellus eget condimentum rhoncus sequam semper libero sit amet\",\"image\":\"63a7ef030faf31671950083.png\"}', NULL, 'basic', NULL, '2022-12-25 00:34:43', '2023-01-05 13:50:37'),
(64, 'verify_email.content', '{\"has_image\":\"1\",\"image\":\"63a93db4212121672035764.png\"}', NULL, 'basic', NULL, '2022-12-26 00:22:44', '2023-01-05 13:50:37'),
(65, 'verify_section.content', '{\"has_image\":\"1\",\"image\":\"63a93f835ff161672036227.png\"}', NULL, 'basic', NULL, '2022-12-26 00:30:27', '2023-01-05 13:50:37'),
(66, 'reset_section.content', '{\"has_image\":\"1\",\"title\":\"To recover your account please provide your email or username to find your account.\",\"image\":\"63a94087e32a01672036487.png\"}', NULL, 'basic', NULL, '2022-12-26 00:34:47', '2023-01-05 13:50:37'),
(67, 'verify_code.content', '{\"has_image\":\"1\",\"title\":\"A 6 digit verification code sent to your email address\",\"image\":\"63a94167cd6b51672036711.png\"}', NULL, 'basic', NULL, '2022-12-26 00:38:31', '2023-01-05 13:50:37'),
(68, 'maintenance.data', '{\"description\":\"<h2 style=\\\"text-align:center;\\\"><span><font size=\\\"6\\\">We\'re just tuning up a few things.<\\/font><\\/span><\\/h2><p>We apologize for the inconvenience but Front is currently undergoing planned maintenance. Thanks for your patience.<br><\\/p>\",\"image\":\"66698efcb72391718193916.png\"}', NULL, 'basic', NULL, '2022-12-27 05:35:11', '2024-06-12 06:05:16'),
(69, 'cookie.data', '{\"short_description\":\"We may use cookies or any other tracking technologies when you visit our website, including any other media form, mobile website, or mobile application related or connected to help customize the Site and improve your experience.\",\"policy\":\"<div class=\\\"mb-5\\\" style=\\\"font-family:Nunito, sans-serif;margin-bottom:3rem;\\\"><h3 class=\\\"mb-3\\\" style=\\\"font-weight:600;line-height:1.3;font-size:24px;font-family:Exo, sans-serif;\\\">What information do we collect?<\\/h3><p class=\\\"font-18\\\" style=\\\"margin-right:0px;margin-left:0px;font-size:18px;\\\">We gather data from you when you register on our site, submit a request, buy any services, react to an overview, or round out a structure. At the point when requesting any assistance or enrolling on our site, as suitable, you might be approached to enter your: name, email address, or telephone number. You may, nonetheless, visit our site anonymously.<\\/p><\\/div><div class=\\\"mb-5\\\" style=\\\"font-family:Nunito, sans-serif;margin-bottom:3rem;\\\"><h3 class=\\\"mb-3\\\" style=\\\"font-weight:600;line-height:1.3;font-size:24px;font-family:Exo, sans-serif;\\\">How do we protect your information?<\\/h3><p class=\\\"font-18\\\" style=\\\"margin-right:0px;margin-left:0px;font-size:18px;\\\">All provided delicate\\/credit data is sent through Stripe.<br \\/>After an exchange, your private data (credit cards, social security numbers, financials, and so on) won\'t be put away on our workers.<\\/p><\\/div><div class=\\\"mb-5\\\" style=\\\"font-family:Nunito, sans-serif;margin-bottom:3rem;\\\"><h3 class=\\\"mb-3\\\" style=\\\"font-weight:600;line-height:1.3;font-size:24px;font-family:Exo, sans-serif;\\\">Do we disclose any information to outside parties?<\\/h3><p class=\\\"font-18\\\" style=\\\"margin-right:0px;margin-left:0px;font-size:18px;\\\">We don\'t sell, exchange, or in any case move to outside gatherings by and by recognizable data. This does exclude confided in outsiders who help us in working our site, leading our business, or adjusting you, since those gatherings consent to keep this data private. We may likewise deliver your data when we accept discharge is suitable to follow the law, implement our site strategies, or ensure our own or others\' rights, property, or wellbeing.<\\/p><\\/div><div class=\\\"mb-5\\\" style=\\\"font-family:Nunito, sans-serif;margin-bottom:3rem;\\\"><h3 class=\\\"mb-3\\\" style=\\\"font-weight:600;line-height:1.3;font-size:24px;font-family:Exo, sans-serif;\\\">Children\'s Online Privacy Protection Act Compliance<\\/h3><p class=\\\"font-18\\\" style=\\\"margin-right:0px;margin-left:0px;font-size:18px;\\\">We are consistent with the prerequisites of COPPA (Children\'s Online Privacy Protection Act), we don\'t gather any data from anybody under 13 years old. Our site, items, and administrations are completely coordinated to individuals who are in any event 13 years of age or more established.<\\/p><\\/div><div class=\\\"mb-5\\\" style=\\\"font-family:Nunito, sans-serif;margin-bottom:3rem;\\\"><h3 class=\\\"mb-3\\\" style=\\\"font-weight:600;line-height:1.3;font-size:24px;font-family:Exo, sans-serif;\\\">Changes to our Privacy Policy<\\/h3><p class=\\\"font-18\\\" style=\\\"margin-right:0px;margin-left:0px;font-size:18px;\\\">If we decide to change our privacy policy, we will post those changes on this page.<\\/p><\\/div><div class=\\\"mb-5\\\" style=\\\"font-family:Nunito, sans-serif;margin-bottom:3rem;\\\"><h3 class=\\\"mb-3\\\" style=\\\"font-weight:600;line-height:1.3;font-size:24px;font-family:Exo, sans-serif;\\\">How long we retain your information?<\\/h3><p class=\\\"font-18\\\" style=\\\"margin-right:0px;margin-left:0px;font-size:18px;\\\">At the point when you register for our site, we cycle and keep your information we have about you however long you don\'t erase the record or withdraw yourself (subject to laws and guidelines).<\\/p><\\/div><div class=\\\"mb-5\\\" style=\\\"font-family:Nunito, sans-serif;margin-bottom:3rem;\\\"><h3 class=\\\"mb-3\\\" style=\\\"font-weight:600;line-height:1.3;font-size:24px;font-family:Exo, sans-serif;\\\">What we don\\u2019t do with your data<\\/h3><p class=\\\"font-18\\\" style=\\\"margin-right:0px;margin-left:0px;font-size:18px;\\\">We don\'t and will never share, unveil, sell, or in any case give your information to different organizations for the promoting of their items or administrations.<\\/p><\\/div>\"}', NULL, NULL, NULL, '2022-12-27 06:13:47', '2023-01-05 13:50:37'),
(70, 'ban_page.content', '{\"has_image\":\"1\",\"heading\":\"THIS ACCOUNT IS BANNED.\",\"image\":\"63ad206976b1d1672290409.png\"}', NULL, 'basic', NULL, '2022-12-28 23:06:49', '2023-01-05 13:50:37'),
(71, 'subscribe.content', '{\"heading\":\"Subscribe us to know latest updates\"}', NULL, 'basic', NULL, '2022-12-31 06:42:31', '2023-01-05 13:50:37'),
(552, 'register_disable.content', '{\"has_image\":\"1\",\"heading\":\"Registration Currently Disabled\",\"subheading\":\"Page you are looking for doesn\'t exit or an other error occurred or temporarily unavailable.\",\"button_name\":\"Go to Home\",\"button_url\":\"\\/\",\"image\":\"66488b6067df71716030304.png\"}', NULL, 'basic', '', '2024-05-18 05:05:04', '2024-05-18 05:05:04'),
(553, 'auth.content', '{\"has_image\":\"1\",\"image\":\"66698bc4132da1718193092.png\"}', NULL, 'basic', '', '2024-06-12 05:51:32', '2024-06-12 05:51:32'),
(554, 'kyc.content', '{\"required\":\"Complete KYC to unlock the full potential of our platform! KYC helps us verify your identity and keep things secure. It is quick and easy just follow the on-screen instructions. Get started with KYC verification now!\",\"pending\":\"Your KYC verification is being reviewed. We might need some additional information. You will get an email update soon. In the meantime, explore our platform with limited features.\",\"reject\":\"We regret to inform you that the Know Your Customer (KYC) information provided has been reviewed and unfortunately, it has not met our verification standards.\"}', NULL, 'basic', '', '2024-05-18 05:06:56', '2024-05-18 05:06:56'),
(555, 'marketplace_hero.content', '{\"heading\":\"Buy & Sell Online Businesses\",\"subheading\":\"The trusted marketplace for domains, websites, apps, and social media accounts\",\"button_text\":\"Browse Marketplace\",\"button_url\":\"/marketplace\",\"status\":\"1\"}', NULL, 'basic', '', '2025-12-01 10:23:22', '2025-12-01 10:23:22'),
(556, 'marketplace_featured.content', '{\"heading\":\"Featured Listings\",\"subheading\":\"Hand-picked premium businesses verified by our team\",\"status\":\"1\",\"limit\":\"6\"}', NULL, 'basic', '', '2025-12-01 10:23:22', '2025-12-01 10:23:22'),
(557, 'marketplace_popular.content', '{\"heading\":\"Most Popular\",\"subheading\":\"Trending businesses with the highest engagement\",\"status\":\"1\",\"limit\":\"6\"}', NULL, 'basic', '', '2025-12-01 10:23:22', '2025-12-01 10:23:22'),
(558, 'marketplace_new.content', '{\"heading\":\"Just Listed\",\"subheading\":\"Fresh opportunities just added to the marketplace\",\"status\":\"1\",\"limit\":\"6\"}', NULL, 'basic', '', '2025-12-01 10:23:22', '2025-12-01 10:23:22'),
(559, 'marketplace_ending.content', '{\"heading\":\"Auctions Ending Soon\",\"subheading\":\"Hurry! These auctions are closing soon\",\"status\":\"1\",\"limit\":\"6\"}', NULL, 'basic', '', '2025-12-01 10:23:22', '2025-12-01 10:23:22'),
(560, 'marketplace_domains.content', '{\"heading\":\"Premium Domains\",\"subheading\":\"High-value domain names for your next project\",\"status\":\"1\",\"limit\":\"4\"}', NULL, 'basic', '', '2025-12-01 10:23:22', '2025-12-01 10:23:22'),
(561, 'marketplace_websites.content', '{\"heading\":\"Profitable Websites\",\"subheading\":\"Revenue-generating websites ready for acquisition\",\"status\":\"1\",\"limit\":\"4\"}', NULL, 'basic', '', '2025-12-01 10:23:22', '2025-12-01 10:23:22'),
(562, 'marketplace_apps.content', '{\"heading\":\"Mobile & Desktop Apps\",\"subheading\":\"Established applications with active user bases\",\"status\":\"1\",\"limit\":\"4\"}', NULL, 'basic', '', '2025-12-01 10:23:22', '2025-12-01 10:23:22'),
(563, 'marketplace_social.content', '{\"heading\":\"Social Media Accounts\",\"subheading\":\"Built-in audiences ready for monetization\",\"status\":\"1\",\"limit\":\"4\"}', NULL, 'basic', '', '2025-12-01 10:23:22', '2025-12-01 10:23:22'),
(564, 'marketplace_stats.content', '{\"heading\":\"Marketplace Statistics\",\"status\":\"1\",\"show_total_listings\":\"1\",\"show_total_sold\":\"1\",\"show_total_value\":\"1\",\"show_active_users\":\"1\"}', NULL, 'basic', '', '2025-12-01 10:23:22', '2025-12-01 10:23:22'),
(565, 'marketplace_cta.content', '{\"heading\":\"Ready to Sell Your Business?\",\"subheading\":\"List your online business and reach thousands of qualified buyers\",\"button_text\":\"Start Selling\",\"button_url\":\"/user/listing/create\",\"status\":\"1\"}', NULL, 'basic', '', '2025-12-01 10:23:22', '2025-12-01 10:23:22');

-- --------------------------------------------------------

--
-- Table structure for table `gateways`
--

CREATE TABLE `gateways` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `form_id` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `code` int(11) DEFAULT NULL,
  `name` varchar(40) DEFAULT NULL,
  `alias` varchar(40) NOT NULL DEFAULT 'NULL',
  `image` varchar(255) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1=>enable, 2=>disable',
  `gateway_parameters` text DEFAULT NULL,
  `supported_currencies` text DEFAULT NULL,
  `crypto` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0: fiat currency, 1: crypto currency',
  `extra` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `gateways`
--

INSERT INTO `gateways` (`id`, `form_id`, `code`, `name`, `alias`, `image`, `status`, `gateway_parameters`, `supported_currencies`, `crypto`, `extra`, `description`, `created_at`, `updated_at`) VALUES
(1, 0, 101, 'Paypal', 'Paypal', '663a38d7b455d1715091671.png', 1, '{\"paypal_email\":{\"title\":\"PayPal Email\",\"global\":true,\"value\":\"sb-owud61543012@business.example.com\"}}', '{\"AUD\":\"AUD\",\"BRL\":\"BRL\",\"CAD\":\"CAD\",\"CZK\":\"CZK\",\"DKK\":\"DKK\",\"EUR\":\"EUR\",\"HKD\":\"HKD\",\"HUF\":\"HUF\",\"INR\":\"INR\",\"ILS\":\"ILS\",\"JPY\":\"JPY\",\"MYR\":\"MYR\",\"MXN\":\"MXN\",\"TWD\":\"TWD\",\"NZD\":\"NZD\",\"NOK\":\"NOK\",\"PHP\":\"PHP\",\"PLN\":\"PLN\",\"GBP\":\"GBP\",\"RUB\":\"RUB\",\"SGD\":\"SGD\",\"SEK\":\"SEK\",\"CHF\":\"CHF\",\"THB\":\"THB\",\"USD\":\"$\"}', 0, NULL, NULL, '2019-09-14 13:14:22', '2021-05-21 00:04:38'),
(2, 0, 102, 'Perfect Money', 'PerfectMoney', '663a3920e30a31715091744.png', 1, '{\"passphrase\":{\"title\":\"ALTERNATE PASSPHRASE\",\"global\":true,\"value\":\"hR26aw02Q1eEeUPSIfuwNypXX\"},\"wallet_id\":{\"title\":\"PM Wallet\",\"global\":false,\"value\":\"\"}}', '{\"USD\":\"$\",\"EUR\":\"\\u20ac\"}', 0, NULL, NULL, '2019-09-14 13:14:22', '2021-05-21 01:35:33'),
(3, 0, 103, 'Stripe Hosted', 'Stripe', '663a39861cb9d1715091846.png', 1, '{\"secret_key\":{\"title\":\"Secret Key\",\"global\":true,\"value\":\"sk_test_51I6GGiCGv1sRiQlEi5v1or9eR0HVbuzdMd2rW4n3DxC8UKfz66R4X6n4yYkzvI2LeAIuRU9H99ZpY7XCNFC9xMs500vBjZGkKG\"},\"publishable_key\":{\"title\":\"PUBLISHABLE KEY\",\"global\":true,\"value\":\"pk_test_51I6GGiCGv1sRiQlEOisPKrjBqQqqcFsw8mXNaZ2H2baN6R01NulFS7dKFji1NRRxuchoUTEDdB7ujKcyKYSVc0z500eth7otOM\"}}', '{\"USD\":\"USD\",\"AUD\":\"AUD\",\"BRL\":\"BRL\",\"CAD\":\"CAD\",\"CHF\":\"CHF\",\"DKK\":\"DKK\",\"EUR\":\"EUR\",\"GBP\":\"GBP\",\"HKD\":\"HKD\",\"INR\":\"INR\",\"JPY\":\"JPY\",\"MXN\":\"MXN\",\"MYR\":\"MYR\",\"NOK\":\"NOK\",\"NZD\":\"NZD\",\"PLN\":\"PLN\",\"SEK\":\"SEK\",\"SGD\":\"SGD\"}', 0, NULL, NULL, '2019-09-14 13:14:22', '2021-05-21 00:48:36'),
(4, 0, 104, 'Skrill', 'Skrill', '663a39494c4a91715091785.png', 1, '{\"pay_to_email\":{\"title\":\"Skrill Email\",\"global\":true,\"value\":\"merchant@skrill.com\"},\"secret_key\":{\"title\":\"Secret Key\",\"global\":true,\"value\":\"---\"}}', '{\"AED\":\"AED\",\"AUD\":\"AUD\",\"BGN\":\"BGN\",\"BHD\":\"BHD\",\"CAD\":\"CAD\",\"CHF\":\"CHF\",\"CZK\":\"CZK\",\"DKK\":\"DKK\",\"EUR\":\"EUR\",\"GBP\":\"GBP\",\"HKD\":\"HKD\",\"HRK\":\"HRK\",\"HUF\":\"HUF\",\"ILS\":\"ILS\",\"INR\":\"INR\",\"ISK\":\"ISK\",\"JOD\":\"JOD\",\"JPY\":\"JPY\",\"KRW\":\"KRW\",\"KWD\":\"KWD\",\"MAD\":\"MAD\",\"MYR\":\"MYR\",\"NOK\":\"NOK\",\"NZD\":\"NZD\",\"OMR\":\"OMR\",\"PLN\":\"PLN\",\"QAR\":\"QAR\",\"RON\":\"RON\",\"RSD\":\"RSD\",\"SAR\":\"SAR\",\"SEK\":\"SEK\",\"SGD\":\"SGD\",\"THB\":\"THB\",\"TND\":\"TND\",\"TRY\":\"TRY\",\"TWD\":\"TWD\",\"USD\":\"USD\",\"ZAR\":\"ZAR\",\"COP\":\"COP\"}', 0, NULL, NULL, '2019-09-14 13:14:22', '2021-05-21 01:30:16'),
(5, 0, 105, 'PayTM', 'Paytm', '663a390f601191715091727.png', 1, '{\"MID\":{\"title\":\"Merchant ID\",\"global\":true,\"value\":\"DIY12386817555501617\"},\"merchant_key\":{\"title\":\"Merchant Key\",\"global\":true,\"value\":\"bKMfNxPPf_QdZppa\"},\"WEBSITE\":{\"title\":\"Paytm Website\",\"global\":true,\"value\":\"DIYtestingweb\"},\"INDUSTRY_TYPE_ID\":{\"title\":\"Industry Type\",\"global\":true,\"value\":\"Retail\"},\"CHANNEL_ID\":{\"title\":\"CHANNEL ID\",\"global\":true,\"value\":\"WEB\"},\"transaction_url\":{\"title\":\"Transaction URL\",\"global\":true,\"value\":\"https:\\/\\/pguat.paytm.com\\/oltp-web\\/processTransaction\"},\"transaction_status_url\":{\"title\":\"Transaction STATUS URL\",\"global\":true,\"value\":\"https:\\/\\/pguat.paytm.com\\/paytmchecksum\\/paytmCallback.jsp\"}}', '{\"AUD\":\"AUD\",\"ARS\":\"ARS\",\"BDT\":\"BDT\",\"BRL\":\"BRL\",\"BGN\":\"BGN\",\"CAD\":\"CAD\",\"CLP\":\"CLP\",\"CNY\":\"CNY\",\"COP\":\"COP\",\"HRK\":\"HRK\",\"CZK\":\"CZK\",\"DKK\":\"DKK\",\"EGP\":\"EGP\",\"EUR\":\"EUR\",\"GEL\":\"GEL\",\"GHS\":\"GHS\",\"HKD\":\"HKD\",\"HUF\":\"HUF\",\"INR\":\"INR\",\"IDR\":\"IDR\",\"ILS\":\"ILS\",\"JPY\":\"JPY\",\"KES\":\"KES\",\"MYR\":\"MYR\",\"MXN\":\"MXN\",\"MAD\":\"MAD\",\"NPR\":\"NPR\",\"NZD\":\"NZD\",\"NGN\":\"NGN\",\"NOK\":\"NOK\",\"PKR\":\"PKR\",\"PEN\":\"PEN\",\"PHP\":\"PHP\",\"PLN\":\"PLN\",\"RON\":\"RON\",\"RUB\":\"RUB\",\"SGD\":\"SGD\",\"ZAR\":\"ZAR\",\"KRW\":\"KRW\",\"LKR\":\"LKR\",\"SEK\":\"SEK\",\"CHF\":\"CHF\",\"THB\":\"THB\",\"TRY\":\"TRY\",\"UGX\":\"UGX\",\"UAH\":\"UAH\",\"AED\":\"AED\",\"GBP\":\"GBP\",\"USD\":\"USD\",\"VND\":\"VND\",\"XOF\":\"XOF\"}', 0, NULL, NULL, '2019-09-14 13:14:22', '2021-05-21 03:00:44'),
(6, 0, 106, 'Payeer', 'Payeer', '663a38c9e2e931715091657.png', 1, '{\"merchant_id\":{\"title\":\"Merchant ID\",\"global\":true,\"value\":\"866989763\"},\"secret_key\":{\"title\":\"Secret key\",\"global\":true,\"value\":\"7575\"}}', '{\"USD\":\"USD\",\"EUR\":\"EUR\",\"RUB\":\"RUB\"}', 0, '{\"status\":{\"title\": \"Status URL\",\"value\":\"ipn.Payeer\"}}', NULL, '2019-09-14 13:14:22', '2022-08-28 10:11:14'),
(7, 0, 107, 'PayStack', 'Paystack', '663a38fc814e91715091708.png', 1, '{\"public_key\":{\"title\":\"Public key\",\"global\":true,\"value\":\"pk_test_cd330608eb47970889bca397ced55c1dd5ad3783\"},\"secret_key\":{\"title\":\"Secret key\",\"global\":true,\"value\":\"sk_test_8a0b1f199362d7acc9c390bff72c4e81f74e2ac3\"}}', '{\"USD\":\"USD\",\"NGN\":\"NGN\"}', 0, '{\"callback\":{\"title\": \"Callback URL\",\"value\":\"ipn.Paystack\"},\"webhook\":{\"title\": \"Webhook URL\",\"value\":\"ipn.Paystack\"}}\r\n', NULL, '2019-09-14 13:14:22', '2021-05-21 01:49:51'),
(9, 0, 109, 'Flutterwave', 'Flutterwave', '663a36c2c34d61715091138.png', 1, '{\"public_key\":{\"title\":\"Public Key\",\"global\":true,\"value\":\"----------------\"},\"secret_key\":{\"title\":\"Secret Key\",\"global\":true,\"value\":\"-----------------------\"},\"encryption_key\":{\"title\":\"Encryption Key\",\"global\":true,\"value\":\"------------------\"}}', '{\"BIF\":\"BIF\",\"CAD\":\"CAD\",\"CDF\":\"CDF\",\"CVE\":\"CVE\",\"EUR\":\"EUR\",\"GBP\":\"GBP\",\"GHS\":\"GHS\",\"GMD\":\"GMD\",\"GNF\":\"GNF\",\"KES\":\"KES\",\"LRD\":\"LRD\",\"MWK\":\"MWK\",\"MZN\":\"MZN\",\"NGN\":\"NGN\",\"RWF\":\"RWF\",\"SLL\":\"SLL\",\"STD\":\"STD\",\"TZS\":\"TZS\",\"UGX\":\"UGX\",\"USD\":\"USD\",\"XAF\":\"XAF\",\"XOF\":\"XOF\",\"ZMK\":\"ZMK\",\"ZMW\":\"ZMW\",\"ZWD\":\"ZWD\"}', 0, NULL, NULL, '2019-09-14 13:14:22', '2021-06-05 11:37:45'),
(10, 0, 110, 'RazorPay', 'Razorpay', '663a393a527831715091770.png', 1, '{\"key_id\":{\"title\":\"Key Id\",\"global\":true,\"value\":\"rzp_test_kiOtejPbRZU90E\"},\"key_secret\":{\"title\":\"Key Secret \",\"global\":true,\"value\":\"osRDebzEqbsE1kbyQJ4y0re7\"}}', '{\"INR\":\"INR\"}', 0, NULL, NULL, '2019-09-14 13:14:22', '2021-05-21 02:51:32'),
(11, 0, 111, 'Stripe Storefront', 'StripeJs', '663a3995417171715091861.png', 1, '{\"secret_key\":{\"title\":\"Secret Key\",\"global\":true,\"value\":\"sk_test_51I6GGiCGv1sRiQlEi5v1or9eR0HVbuzdMd2rW4n3DxC8UKfz66R4X6n4yYkzvI2LeAIuRU9H99ZpY7XCNFC9xMs500vBjZGkKG\"},\"publishable_key\":{\"title\":\"PUBLISHABLE KEY\",\"global\":true,\"value\":\"pk_test_51I6GGiCGv1sRiQlEOisPKrjBqQqqcFsw8mXNaZ2H2baN6R01NulFS7dKFji1NRRxuchoUTEDdB7ujKcyKYSVc0z500eth7otOM\"}}', '{\"USD\":\"USD\",\"AUD\":\"AUD\",\"BRL\":\"BRL\",\"CAD\":\"CAD\",\"CHF\":\"CHF\",\"DKK\":\"DKK\",\"EUR\":\"EUR\",\"GBP\":\"GBP\",\"HKD\":\"HKD\",\"INR\":\"INR\",\"JPY\":\"JPY\",\"MXN\":\"MXN\",\"MYR\":\"MYR\",\"NOK\":\"NOK\",\"NZD\":\"NZD\",\"PLN\":\"PLN\",\"SEK\":\"SEK\",\"SGD\":\"SGD\"}', 0, NULL, NULL, '2019-09-14 13:14:22', '2021-05-21 00:53:10'),
(12, 0, 112, 'Instamojo', 'Instamojo', '663a384d54a111715091533.png', 1, '{\"api_key\":{\"title\":\"API KEY\",\"global\":true,\"value\":\"test_2241633c3bc44a3de84a3b33969\"},\"auth_token\":{\"title\":\"Auth Token\",\"global\":true,\"value\":\"test_279f083f7bebefd35217feef22d\"},\"salt\":{\"title\":\"Salt\",\"global\":true,\"value\":\"19d38908eeff4f58b2ddda2c6d86ca25\"}}', '{\"INR\":\"INR\"}', 0, NULL, NULL, '2019-09-14 13:14:22', '2021-05-21 02:56:20'),
(13, 0, 501, 'Blockchain', 'Blockchain', '663a35efd0c311715090927.png', 1, '{\"api_key\":{\"title\":\"API Key\",\"global\":true,\"value\":\"55529946-05ca-48ff-8710-f279d86b1cc5\"},\"xpub_code\":{\"title\":\"XPUB CODE\",\"global\":true,\"value\":\"xpub6CKQ3xxWyBoFAF83izZCSFUorptEU9AF8TezhtWeMU5oefjX3sFSBw62Lr9iHXPkXmDQJJiHZeTRtD9Vzt8grAYRhvbz4nEvBu3QKELVzFK\"}}', '{\"BTC\":\"BTC\"}', 1, NULL, NULL, '2019-09-14 13:14:22', '2022-03-21 07:41:56'),
(14, 0, 503, 'CoinPayments', 'Coinpayments', '663a36a8d8e1d1715091112.png', 1, '{\"public_key\":{\"title\":\"Public Key\",\"global\":true,\"value\":\"---------------\"},\"private_key\":{\"title\":\"Private Key\",\"global\":true,\"value\":\"------------\"},\"merchant_id\":{\"title\":\"Merchant ID\",\"global\":true,\"value\":\"93a1e014c4ad60a7980b4a7239673cb4\"}}', '{\"BTC\":\"Bitcoin\",\"BTC.LN\":\"Bitcoin (Lightning Network)\",\"LTC\":\"Litecoin\",\"CPS\":\"CPS Coin\",\"VLX\":\"Velas\",\"APL\":\"Apollo\",\"AYA\":\"Aryacoin\",\"BAD\":\"Badcoin\",\"BCD\":\"Bitcoin Diamond\",\"BCH\":\"Bitcoin Cash\",\"BCN\":\"Bytecoin\",\"BEAM\":\"BEAM\",\"BITB\":\"Bean Cash\",\"BLK\":\"BlackCoin\",\"BSV\":\"Bitcoin SV\",\"BTAD\":\"Bitcoin Adult\",\"BTG\":\"Bitcoin Gold\",\"BTT\":\"BitTorrent\",\"CLOAK\":\"CloakCoin\",\"CLUB\":\"ClubCoin\",\"CRW\":\"Crown\",\"CRYP\":\"CrypticCoin\",\"CRYT\":\"CryTrExCoin\",\"CURE\":\"CureCoin\",\"DASH\":\"DASH\",\"DCR\":\"Decred\",\"DEV\":\"DeviantCoin\",\"DGB\":\"DigiByte\",\"DOGE\":\"Dogecoin\",\"EBST\":\"eBoost\",\"EOS\":\"EOS\",\"ETC\":\"Ether Classic\",\"ETH\":\"Ethereum\",\"ETN\":\"Electroneum\",\"EUNO\":\"EUNO\",\"EXP\":\"EXP\",\"Expanse\":\"Expanse\",\"FLASH\":\"FLASH\",\"GAME\":\"GameCredits\",\"GLC\":\"Goldcoin\",\"GRS\":\"Groestlcoin\",\"KMD\":\"Komodo\",\"LOKI\":\"LOKI\",\"LSK\":\"LSK\",\"MAID\":\"MaidSafeCoin\",\"MUE\":\"MonetaryUnit\",\"NAV\":\"NAV Coin\",\"NEO\":\"NEO\",\"NMC\":\"Namecoin\",\"NVST\":\"NVO Token\",\"NXT\":\"NXT\",\"OMNI\":\"OMNI\",\"PINK\":\"PinkCoin\",\"PIVX\":\"PIVX\",\"POT\":\"PotCoin\",\"PPC\":\"Peercoin\",\"PROC\":\"ProCurrency\",\"PURA\":\"PURA\",\"QTUM\":\"QTUM\",\"RES\":\"Resistance\",\"RVN\":\"Ravencoin\",\"RVR\":\"RevolutionVR\",\"SBD\":\"Steem Dollars\",\"SMART\":\"SmartCash\",\"SOXAX\":\"SOXAX\",\"STEEM\":\"STEEM\",\"STRAT\":\"STRAT\",\"SYS\":\"Syscoin\",\"TPAY\":\"TokenPay\",\"TRIGGERS\":\"Triggers\",\"TRX\":\" TRON\",\"UBQ\":\"Ubiq\",\"UNIT\":\"UniversalCurrency\",\"USDT\":\"Tether USD (Omni Layer)\",\"USDT.BEP20\":\"Tether USD (BSC Chain)\",\"USDT.ERC20\":\"Tether USD (ERC20)\",\"USDT.TRC20\":\"Tether USD (Tron/TRC20)\",\"VTC\":\"Vertcoin\",\"WAVES\":\"Waves\",\"XCP\":\"Counterparty\",\"XEM\":\"NEM\",\"XMR\":\"Monero\",\"XSN\":\"Stakenet\",\"XSR\":\"SucreCoin\",\"XVG\":\"VERGE\",\"XZC\":\"ZCoin\",\"ZEC\":\"ZCash\",\"ZEN\":\"Horizen\"}', 1, NULL, NULL, '2019-09-14 13:14:22', '2021-05-21 02:07:14'),
(15, 0, 504, 'CoinPayments Fiat', 'CoinpaymentsFiat', '663a36b7b841a1715091127.png', 1, '{\"merchant_id\":{\"title\":\"Merchant ID\",\"global\":true,\"value\":\"6515561\"}}', '{\"USD\":\"USD\",\"AUD\":\"AUD\",\"BRL\":\"BRL\",\"CAD\":\"CAD\",\"CHF\":\"CHF\",\"CLP\":\"CLP\",\"CNY\":\"CNY\",\"DKK\":\"DKK\",\"EUR\":\"EUR\",\"GBP\":\"GBP\",\"HKD\":\"HKD\",\"INR\":\"INR\",\"ISK\":\"ISK\",\"JPY\":\"JPY\",\"KRW\":\"KRW\",\"NZD\":\"NZD\",\"PLN\":\"PLN\",\"RUB\":\"RUB\",\"SEK\":\"SEK\",\"SGD\":\"SGD\",\"THB\":\"THB\",\"TWD\":\"TWD\"}', 0, NULL, NULL, '2019-09-14 13:14:22', '2021-05-21 02:07:44'),
(16, 0, 505, 'Coingate', 'Coingate', '663a368e753381715091086.png', 1, '{\"api_key\":{\"title\":\"API Key\",\"global\":true,\"value\":\"6354mwVCEw5kHzRJ6thbGo-N\"}}', '{\"USD\":\"USD\",\"EUR\":\"EUR\"}', 0, NULL, NULL, '2019-09-14 13:14:22', '2022-03-30 09:24:57'),
(17, 0, 506, 'Coinbase Commerce', 'CoinbaseCommerce', '663a367e46ae51715091070.png', 1, '{\"api_key\":{\"title\":\"API Key\",\"global\":true,\"value\":\"c47cd7df-d8e8-424b-a20a\"},\"secret\":{\"title\":\"Webhook Shared Secret\",\"global\":true,\"value\":\"55871878-2c32-4f64-ab66\"}}', '{\"USD\":\"USD\",\"EUR\":\"EUR\",\"JPY\":\"JPY\",\"GBP\":\"GBP\",\"AUD\":\"AUD\",\"CAD\":\"CAD\",\"CHF\":\"CHF\",\"CNY\":\"CNY\",\"SEK\":\"SEK\",\"NZD\":\"NZD\",\"MXN\":\"MXN\",\"SGD\":\"SGD\",\"HKD\":\"HKD\",\"NOK\":\"NOK\",\"KRW\":\"KRW\",\"TRY\":\"TRY\",\"RUB\":\"RUB\",\"INR\":\"INR\",\"BRL\":\"BRL\",\"ZAR\":\"ZAR\",\"AED\":\"AED\",\"AFN\":\"AFN\",\"ALL\":\"ALL\",\"AMD\":\"AMD\",\"ANG\":\"ANG\",\"AOA\":\"AOA\",\"ARS\":\"ARS\",\"AWG\":\"AWG\",\"AZN\":\"AZN\",\"BAM\":\"BAM\",\"BBD\":\"BBD\",\"BDT\":\"BDT\",\"BGN\":\"BGN\",\"BHD\":\"BHD\",\"BIF\":\"BIF\",\"BMD\":\"BMD\",\"BND\":\"BND\",\"BOB\":\"BOB\",\"BSD\":\"BSD\",\"BTN\":\"BTN\",\"BWP\":\"BWP\",\"BYN\":\"BYN\",\"BZD\":\"BZD\",\"CDF\":\"CDF\",\"CLF\":\"CLF\",\"CLP\":\"CLP\",\"COP\":\"COP\",\"CRC\":\"CRC\",\"CUC\":\"CUC\",\"CUP\":\"CUP\",\"CVE\":\"CVE\",\"CZK\":\"CZK\",\"DJF\":\"DJF\",\"DKK\":\"DKK\",\"DOP\":\"DOP\",\"DZD\":\"DZD\",\"EGP\":\"EGP\",\"ERN\":\"ERN\",\"ETB\":\"ETB\",\"FJD\":\"FJD\",\"FKP\":\"FKP\",\"GEL\":\"GEL\",\"GGP\":\"GGP\",\"GHS\":\"GHS\",\"GIP\":\"GIP\",\"GMD\":\"GMD\",\"GNF\":\"GNF\",\"GTQ\":\"GTQ\",\"GYD\":\"GYD\",\"HNL\":\"HNL\",\"HRK\":\"HRK\",\"HTG\":\"HTG\",\"HUF\":\"HUF\",\"IDR\":\"IDR\",\"ILS\":\"ILS\",\"IMP\":\"IMP\",\"IQD\":\"IQD\",\"IRR\":\"IRR\",\"ISK\":\"ISK\",\"JEP\":\"JEP\",\"JMD\":\"JMD\",\"JOD\":\"JOD\",\"KES\":\"KES\",\"KGS\":\"KGS\",\"KHR\":\"KHR\",\"KMF\":\"KMF\",\"KPW\":\"KPW\",\"KWD\":\"KWD\",\"KYD\":\"KYD\",\"KZT\":\"KZT\",\"LAK\":\"LAK\",\"LBP\":\"LBP\",\"LKR\":\"LKR\",\"LRD\":\"LRD\",\"LSL\":\"LSL\",\"LYD\":\"LYD\",\"MAD\":\"MAD\",\"MDL\":\"MDL\",\"MGA\":\"MGA\",\"MKD\":\"MKD\",\"MMK\":\"MMK\",\"MNT\":\"MNT\",\"MOP\":\"MOP\",\"MRO\":\"MRO\",\"MUR\":\"MUR\",\"MVR\":\"MVR\",\"MWK\":\"MWK\",\"MYR\":\"MYR\",\"MZN\":\"MZN\",\"NAD\":\"NAD\",\"NGN\":\"NGN\",\"NIO\":\"NIO\",\"NPR\":\"NPR\",\"OMR\":\"OMR\",\"PAB\":\"PAB\",\"PEN\":\"PEN\",\"PGK\":\"PGK\",\"PHP\":\"PHP\",\"PKR\":\"PKR\",\"PLN\":\"PLN\",\"PYG\":\"PYG\",\"QAR\":\"QAR\",\"RON\":\"RON\",\"RSD\":\"RSD\",\"RWF\":\"RWF\",\"SAR\":\"SAR\",\"SBD\":\"SBD\",\"SCR\":\"SCR\",\"SDG\":\"SDG\",\"SHP\":\"SHP\",\"SLL\":\"SLL\",\"SOS\":\"SOS\",\"SRD\":\"SRD\",\"SSP\":\"SSP\",\"STD\":\"STD\",\"SVC\":\"SVC\",\"SYP\":\"SYP\",\"SZL\":\"SZL\",\"THB\":\"THB\",\"TJS\":\"TJS\",\"TMT\":\"TMT\",\"TND\":\"TND\",\"TOP\":\"TOP\",\"TTD\":\"TTD\",\"TWD\":\"TWD\",\"TZS\":\"TZS\",\"UAH\":\"UAH\",\"UGX\":\"UGX\",\"UYU\":\"UYU\",\"UZS\":\"UZS\",\"VEF\":\"VEF\",\"VND\":\"VND\",\"VUV\":\"VUV\",\"WST\":\"WST\",\"XAF\":\"XAF\",\"XAG\":\"XAG\",\"XAU\":\"XAU\",\"XCD\":\"XCD\",\"XDR\":\"XDR\",\"XOF\":\"XOF\",\"XPD\":\"XPD\",\"XPF\":\"XPF\",\"XPT\":\"XPT\",\"YER\":\"YER\",\"ZMW\":\"ZMW\",\"ZWL\":\"ZWL\"}\r\n\r\n', 0, '{\"endpoint\":{\"title\": \"Webhook Endpoint\",\"value\":\"ipn.CoinbaseCommerce\"}}', NULL, '2019-09-14 13:14:22', '2021-05-21 02:02:47'),
(18, 0, 113, 'Paypal Express', 'PaypalSdk', '663a38ed101a61715091693.png', 1, '{\"clientId\":{\"title\":\"Paypal Client ID\",\"global\":true,\"value\":\"Ae0-tixtSV7DvLwIh3Bmu7JvHrjh5EfGdXr_cEklKAVjjezRZ747BxKILiBdzlKKyp-W8W_T7CKH1Ken\"},\"clientSecret\":{\"title\":\"Client Secret\",\"global\":true,\"value\":\"EOhbvHZgFNO21soQJT1L9Q00M3rK6PIEsdiTgXRBt2gtGtxwRer5JvKnVUGNU5oE63fFnjnYY7hq3HBA\"}}', '{\"AUD\":\"AUD\",\"BRL\":\"BRL\",\"CAD\":\"CAD\",\"CZK\":\"CZK\",\"DKK\":\"DKK\",\"EUR\":\"EUR\",\"HKD\":\"HKD\",\"HUF\":\"HUF\",\"INR\":\"INR\",\"ILS\":\"ILS\",\"JPY\":\"JPY\",\"MYR\":\"MYR\",\"MXN\":\"MXN\",\"TWD\":\"TWD\",\"NZD\":\"NZD\",\"NOK\":\"NOK\",\"PHP\":\"PHP\",\"PLN\":\"PLN\",\"GBP\":\"GBP\",\"RUB\":\"RUB\",\"SGD\":\"SGD\",\"SEK\":\"SEK\",\"CHF\":\"CHF\",\"THB\":\"THB\",\"USD\":\"$\"}', 0, NULL, NULL, '2019-09-14 13:14:22', '2021-05-20 23:01:08'),
(19, 0, 114, 'Stripe Checkout', 'StripeV3', '663a39afb519f1715091887.png', 1, '{\"secret_key\":{\"title\":\"Secret Key\",\"global\":true,\"value\":\"sk_test_51I6GGiCGv1sRiQlEi5v1or9eR0HVbuzdMd2rW4n3DxC8UKfz66R4X6n4yYkzvI2LeAIuRU9H99ZpY7XCNFC9xMs500vBjZGkKG\"},\"publishable_key\":{\"title\":\"PUBLISHABLE KEY\",\"global\":true,\"value\":\"pk_test_51I6GGiCGv1sRiQlEOisPKrjBqQqqcFsw8mXNaZ2H2baN6R01NulFS7dKFji1NRRxuchoUTEDdB7ujKcyKYSVc0z500eth7otOM\"},\"end_point\":{\"title\":\"End Point Secret\",\"global\":true,\"value\":\"whsec_lUmit1gtxwKTveLnSe88xCSDdnPOt8g5\"}}', '{\"USD\":\"USD\",\"AUD\":\"AUD\",\"BRL\":\"BRL\",\"CAD\":\"CAD\",\"CHF\":\"CHF\",\"DKK\":\"DKK\",\"EUR\":\"EUR\",\"GBP\":\"GBP\",\"HKD\":\"HKD\",\"INR\":\"INR\",\"JPY\":\"JPY\",\"MXN\":\"MXN\",\"MYR\":\"MYR\",\"NOK\":\"NOK\",\"NZD\":\"NZD\",\"PLN\":\"PLN\",\"SEK\":\"SEK\",\"SGD\":\"SGD\"}', 0, '{\"webhook\":{\"title\": \"Webhook Endpoint\",\"value\":\"ipn.StripeV3\"}}', NULL, '2019-09-14 13:14:22', '2021-05-21 00:58:38'),
(20, 0, 115, 'Mollie', 'Mollie', '663a387ec69371715091582.png', 1, '{\"mollie_email\":{\"title\":\"Mollie Email \",\"global\":true,\"value\":\"vi@gmail.com\"},\"api_key\":{\"title\":\"API KEY\",\"global\":true,\"value\":\"test_cucfwKTWfft9s337qsVfn5CC4vNkrn\"}}', '{\"AED\":\"AED\",\"AUD\":\"AUD\",\"BGN\":\"BGN\",\"BRL\":\"BRL\",\"CAD\":\"CAD\",\"CHF\":\"CHF\",\"CZK\":\"CZK\",\"DKK\":\"DKK\",\"EUR\":\"EUR\",\"GBP\":\"GBP\",\"HKD\":\"HKD\",\"HRK\":\"HRK\",\"HUF\":\"HUF\",\"ILS\":\"ILS\",\"ISK\":\"ISK\",\"JPY\":\"JPY\",\"MXN\":\"MXN\",\"MYR\":\"MYR\",\"NOK\":\"NOK\",\"NZD\":\"NZD\",\"PHP\":\"PHP\",\"PLN\":\"PLN\",\"RON\":\"RON\",\"RUB\":\"RUB\",\"SEK\":\"SEK\",\"SGD\":\"SGD\",\"THB\":\"THB\",\"TWD\":\"TWD\",\"USD\":\"USD\",\"ZAR\":\"ZAR\"}', 0, NULL, NULL, '2019-09-14 13:14:22', '2021-05-21 02:44:45'),
(21, 0, 119, 'Mercado Pago', 'MercadoPago', '663a386c714a91715091564.png', 1, '{\"access_token\":{\"title\":\"Access Token\",\"global\":true,\"value\":\"APP_USR-7924565816849832-082312-21941521997fab717db925cf1ea2c190-1071840315\"}}', '{\"USD\":\"USD\",\"CAD\":\"CAD\",\"CHF\":\"CHF\",\"DKK\":\"DKK\",\"EUR\":\"EUR\",\"GBP\":\"GBP\",\"NOK\":\"NOK\",\"PLN\":\"PLN\",\"SEK\":\"SEK\",\"AUD\":\"AUD\",\"NZD\":\"NZD\"}', 0, NULL, NULL, NULL, '2022-09-14 07:41:14'),
(22, 0, 120, 'Authorize.net', 'Authorize', '663a35b9ca5991715090873.png', 1, '{\"login_id\":{\"title\":\"Login ID\",\"global\":true,\"value\":\"59e4P9DBcZv\"},\"transaction_key\":{\"title\":\"Transaction Key\",\"global\":true,\"value\":\"47x47TJyLw2E7DbR\"}}', '{\"USD\":\"USD\",\"CAD\":\"CAD\",\"CHF\":\"CHF\",\"DKK\":\"DKK\",\"EUR\":\"EUR\",\"GBP\":\"GBP\",\"NOK\":\"NOK\",\"PLN\":\"PLN\",\"SEK\":\"SEK\",\"AUD\":\"AUD\",\"NZD\":\"NZD\"}', 0, NULL, NULL, NULL, '2022-08-28 09:33:06'),
(23, 0, 121, 'NMI', 'NMI', '663a3897754cf1715091607.png', 1, '{\"api_key\":{\"title\":\"API Key\",\"global\":true,\"value\":\"2F822Rw39fx762MaV7Yy86jXGTC7sCDy\"}}', '{\"AED\":\"AED\",\"ARS\":\"ARS\",\"AUD\":\"AUD\",\"BOB\":\"BOB\",\"BRL\":\"BRL\",\"CAD\":\"CAD\",\"CHF\":\"CHF\",\"CLP\":\"CLP\",\"CNY\":\"CNY\",\"COP\":\"COP\",\"DKK\":\"DKK\",\"EUR\":\"EUR\",\"GBP\":\"GBP\",\"HKD\":\"HKD\",\"IDR\":\"IDR\",\"ILS\":\"ILS\",\"INR\":\"INR\",\"JPY\":\"JPY\",\"KRW\":\"KRW\",\"MXN\":\"MXN\",\"MYR\":\"MYR\",\"NOK\":\"NOK\",\"NZD\":\"NZD\",\"PEN\":\"PEN\",\"PHP\":\"PHP\",\"PLN\":\"PLN\",\"PYG\":\"PYG\",\"RUB\":\"RUB\",\"SEC\":\"SEC\",\"SGD\":\"SGD\",\"THB\":\"THB\",\"TRY\":\"TRY\",\"TWD\":\"TWD\",\"USD\":\"USD\",\"ZAR\":\"ZAR\"}', 0, NULL, NULL, NULL, '2022-08-28 10:32:31'),
(25, 0, 122, 'Two Checkout', 'TwoCheckout', '663a39b8e64b91715091896.png', 1, '{\"merchant_code\":{\"title\":\"Merchant Code\",\"global\":true,\"value\":\"----\"},\"secret_key\":{\"title\":\"Secret Key\",\"global\":true,\"value\":\"-----\"}}', '{\"AED\":\"AED\",\"AFN\":\"AFN\",\"ALL\":\"ALL\",\"ARS\":\"ARS\",\"AUD\":\"AUD\",\"AZN\":\"AZN\",\"BBD\":\"BBD\",\"BDT\":\"BDT\",\"BGN\":\"BGN\",\"BHD\":\"BHD\",\"BMD\":\"BMD\",\"BND\":\"BND\",\"BOB\":\"BOB\",\"BRL\":\"BRL\",\"BSD\":\"BSD\",\"BWP\":\"BWP\",\"BYN\":\"BYN\",\"BZD\":\"BZD\",\"CAD\":\"CAD\",\"CHF\":\"CHF\",\"CLP\":\"CLP\",\"CNY\":\"CNY\",\"COP\":\"COP\",\"CRC\":\"CRC\",\"CZK\":\"CZK\",\"DKK\":\"DKK\",\"DOP\":\"DOP\",\"DZD\":\"DZD\",\"EGP\":\"EGP\",\"EUR\":\"EUR\",\"FJD\":\"FJD\",\"GBP\":\"GBP\",\"GTQ\":\"GTQ\",\"HKD\":\"HKD\",\"HNL\":\"HNL\",\"HRK\":\"HRK\",\"HTG\":\"HTG\",\"HUF\":\"HUF\",\"IDR\":\"IDR\",\"ILS\":\"ILS\",\"INR\":\"INR\",\"JMD\":\"JMD\",\"JOD\":\"JOD\",\"JPY\":\"JPY\",\"KES\":\"KES\",\"KRW\":\"KRW\",\"KWD\":\"KWD\",\"KZT\":\"KZT\",\"LAK\":\"LAK\",\"LBP\":\"LBP\",\"LRD\":\"LRD\",\"MAD\":\"MAD\",\"MDL\":\"MDL\",\"MMK\":\"MMK\",\"MOP\":\"MOP\",\"MRU\":\"MRU\",\"MUR\":\"MUR\",\"MVR\":\"MVR\",\"MXN\":\"MXN\",\"MYR\":\"MYR\",\"NAD\":\"NAD\",\"NGN\":\"NGN\",\"NIO\":\"NIO\",\"NOK\":\"NOK\",\"NPR\":\"NPR\",\"NZD\":\"NZD\",\"OMR\":\"OMR\",\"PAB\":\"PAB\",\"PEN\":\"PEN\",\"PGK\":\"PGK\",\"PHP\":\"PHP\",\"PKR\":\"PKR\",\"PLN\":\"PLN\",\"PYG\":\"PYG\",\"QAR\":\"QAR\",\"RON\":\"RON\",\"RSD\":\"RSD\",\"RUB\":\"RUB\",\"SAR\":\"SAR\",\"SBD\":\"SBD\",\"SCR\":\"SCR\",\"SEK\":\"SEK\",\"SGD\":\"SGD\",\"SVC\":\"SVC\",\"SYP\":\"SYP\",\"THB\":\"THB\",\"TND\":\"TND\",\"TOP\":\"TOP\",\"TRY\":\"TRY\",\"TTD\":\"TTD\",\"TWD\":\"TWD\",\"UAH\":\"UAH\",\"USD\":\"USD\",\"UYU\":\"UYU\",\"VEF\":\"VEF\",\"VND\":\"VND\",\"VUV\":\"VUV\",\"WST\":\"WST\",\"XCD\":\"XCD\",\"XOF\":\"XOF\",\"YER\":\"YER\",\"ZAR\":\"ZAR\"}', 0, NULL, NULL, NULL, '2022-08-28 10:32:31'),
(26, 0, 116, 'Cashmaal', 'Cashmaal', '663a361b16bd11715090971.png', 1, '{\"web_id\":{\"title\":\"Web Id\",\"global\":true,\"value\":\"3748\"},\"ipn_key\":{\"title\":\"IPN Key\",\"global\":true,\"value\":\"546254628759524554647987\"}}', '{\"PKR\":\"PKR\",\"USD\":\"USD\"}', 0, '{\"webhook\":{\"title\": \"IPN URL\",\"value\":\"ipn.Cashmaal\"}}', NULL, NULL, '2021-06-22 08:05:04'),
(157, 0, 507, 'BTCPay', 'BTCPay', '663a35cd25a8d1715090893.png', 1, '{\"store_id\":{\"title\":\"Store Id\",\"global\":true,\"value\":\"-------\"},\"api_key\":{\"title\":\"Api Key\",\"global\":true,\"value\":\"------\"},\"server_name\":{\"title\":\"Server Name\",\"global\":true,\"value\":\"https:\\/\\/yourbtcpaserver.lndyn.com\"},\"secret_code\":{\"title\":\"Secret Code\",\"global\":true,\"value\":\"----------\"}}', '{\"BTC\":\"Bitcoin\",\"LTC\":\"Litecoin\"}', 1, '{\"webhook\":{\"title\": \"IPN URL\",\"value\":\"ipn.BTCPay\"}}', NULL, NULL, NULL),
(158, 0, 508, 'Now payments hosted', 'NowPaymentsHosted', '663a38b8d57a81715091640.png', 1, '{\"api_key\":{\"title\":\"API Key\",\"global\":true,\"value\":\"-------------------\"},\"secret_key\":{\"title\":\"Secret Key\",\"global\":true,\"value\":\"--------------\"}}', '{\"BTG\":\"BTG\",\"ETH\":\"ETH\",\"XMR\":\"XMR\",\"ZEC\":\"ZEC\",\"XVG\":\"XVG\",\"ADA\":\"ADA\",\"LTC\":\"LTC\",\"BCH\":\"BCH\",\"QTUM\":\"QTUM\",\"DASH\":\"DASH\",\"XLM\":\"XLM\",\"XRP\":\"XRP\",\"XEM\":\"XEM\",\"DGB\":\"DGB\",\"LSK\":\"LSK\",\"DOGE\":\"DOGE\",\"TRX\":\"TRX\",\"KMD\":\"KMD\",\"REP\":\"REP\",\"BAT\":\"BAT\",\"ARK\":\"ARK\",\"WAVES\":\"WAVES\",\"BNB\":\"BNB\",\"XZC\":\"XZC\",\"NANO\":\"NANO\",\"TUSD\":\"TUSD\",\"VET\":\"VET\",\"ZEN\":\"ZEN\",\"GRS\":\"GRS\",\"FUN\":\"FUN\",\"NEO\":\"NEO\",\"GAS\":\"GAS\",\"PAX\":\"PAX\",\"USDC\":\"USDC\",\"ONT\":\"ONT\",\"XTZ\":\"XTZ\",\"LINK\":\"LINK\",\"RVN\":\"RVN\",\"BNBMAINNET\":\"BNBMAINNET\",\"ZIL\":\"ZIL\",\"BCD\":\"BCD\",\"USDT\":\"USDT\",\"USDTERC20\":\"USDTERC20\",\"CRO\":\"CRO\",\"DAI\":\"DAI\",\"HT\":\"HT\",\"WABI\":\"WABI\",\"BUSD\":\"BUSD\",\"ALGO\":\"ALGO\",\"USDTTRC20\":\"USDTTRC20\",\"GT\":\"GT\",\"STPT\":\"STPT\",\"AVA\":\"AVA\",\"SXP\":\"SXP\",\"UNI\":\"UNI\",\"OKB\":\"OKB\",\"BTC\":\"BTC\"}', 1, '', NULL, NULL, '2023-02-14 15:42:09'),
(159, 0, 509, 'Now payments checkout', 'NowPaymentsCheckout', '663a38a59d2541715091621.png', 1, '{\"api_key\":{\"title\":\"API Key\",\"global\":true,\"value\":\"-------------------\"},\"secret_key\":{\"title\":\"Secret Key\",\"global\":true,\"value\":\"--------------\"}}', '{\"USD\":\"USD\",\"EUR\":\"EUR\"}', 1, '', NULL, NULL, '2023-02-14 15:42:09'),
(160, 0, 510, 'Binance', 'Binance', '663a35db4fd621715090907.png', 1, '{\"api_key\":{\"title\":\"API Key\",\"global\":true,\"value\":\"tsu3tjiq0oqfbtmlbevoeraxhfbp3brejnm9txhjxcp4to29ujvakvfl1ibsn3ja\"},\"secret_key\":{\"title\":\"Secret Key\",\"global\":true,\"value\":\"jzngq4t04ltw8d4iqpi7admfl8tvnpehxnmi34id1zvfaenbwwvsvw7llw3zdko8\"},\"merchant_id\":{\"title\":\"Merchant ID\",\"global\":true,\"value\":\"231129033\"}}', '{\"BTC\":\"Bitcoin\",\"USD\":\"USD\",\"BNB\":\"BNB\"}', 1, '{\"cron\":{\"title\": \"Cron Job URL\",\"value\":\"ipn.Binance\"}}', NULL, NULL, '2023-02-14 05:08:04'),
(161, 0, 124, 'SslCommerz', 'SslCommerz', '663a397a70c571715091834.png', 1, '{\"store_id\": {\"title\": \"Store ID\",\"global\": true,\"value\": \"---------\"},\"store_password\": {\"title\": \"Store Password\",\"global\": true,\"value\": \"----------\"}}', '{\"BDT\":\"BDT\",\"USD\":\"USD\",\"EUR\":\"EUR\",\"SGD\":\"SGD\",\"INR\":\"INR\",\"MYR\":\"MYR\"}', 0, NULL, NULL, NULL, '2023-05-06 07:43:01'),
(162, 0, 125, 'Aamarpay', 'Aamarpay', '663a34d5d1dfc1715090645.png', 1, '{\"store_id\": {\"title\": \"Store ID\",\"global\": true,\"value\": \"---------\"},\"signature_key\": {\"title\": \"Signature Key\",\"global\": true,\"value\": \"----------\"}}', '{\"BDT\":\"BDT\"}', 0, NULL, NULL, NULL, '2023-05-06 07:43:01');

-- --------------------------------------------------------

--
-- Table structure for table `gateway_currencies`
--

CREATE TABLE `gateway_currencies` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(40) DEFAULT NULL,
  `currency` varchar(40) DEFAULT NULL,
  `symbol` varchar(40) DEFAULT NULL,
  `method_code` int(11) DEFAULT NULL,
  `gateway_alias` varchar(40) DEFAULT NULL,
  `min_amount` decimal(28,8) NOT NULL DEFAULT 0.00000000,
  `max_amount` decimal(28,8) NOT NULL DEFAULT 0.00000000,
  `percent_charge` decimal(5,2) NOT NULL DEFAULT 0.00,
  `fixed_charge` decimal(28,8) NOT NULL DEFAULT 0.00000000,
  `rate` decimal(28,8) NOT NULL DEFAULT 0.00000000,
  `gateway_parameter` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `general_settings`
--

CREATE TABLE `general_settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `site_name` varchar(40) DEFAULT NULL,
  `cur_text` varchar(40) DEFAULT NULL COMMENT 'currency text',
  `cur_sym` varchar(40) DEFAULT NULL COMMENT 'currency symbol',
  `email_from` varchar(40) DEFAULT NULL,
  `email_from_name` varchar(255) DEFAULT NULL,
  `charge_cap` decimal(28,8) NOT NULL DEFAULT 0.00000000,
  `fixed_charge` decimal(28,8) NOT NULL DEFAULT 0.00000000,
  `percent_charge` decimal(5,2) NOT NULL DEFAULT 0.00,
  `email_template` text DEFAULT NULL,
  `sms_template` varchar(255) DEFAULT NULL,
  `sms_from` varchar(255) DEFAULT NULL,
  `push_title` varchar(255) DEFAULT NULL,
  `push_template` varchar(255) DEFAULT NULL,
  `base_color` varchar(40) DEFAULT NULL,
  `secondary_color` varchar(40) DEFAULT NULL,
  `mail_config` text DEFAULT NULL COMMENT 'email configuration',
  `sms_config` text DEFAULT NULL,
  `firebase_config` text DEFAULT NULL,
  `global_shortcodes` text DEFAULT NULL,
  `ev` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'email verification, 0 - dont check, 1 - check',
  `kv` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `en` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'email notification, 0 - dont send, 1 - send',
  `sv` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'mobile verication, 0 - dont check, 1 - check',
  `sn` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'sms notification, 0 - dont send, 1 - send',
  `pn` tinyint(1) NOT NULL DEFAULT 1,
  `force_ssl` tinyint(1) NOT NULL DEFAULT 0,
  `maintenance_mode` tinyint(1) NOT NULL DEFAULT 0,
  `secure_password` tinyint(1) NOT NULL DEFAULT 0,
  `agree` tinyint(1) NOT NULL DEFAULT 0,
  `multi_language` tinyint(1) NOT NULL DEFAULT 1,
  `registration` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0: Off	, 1: On',
  `active_template` varchar(40) DEFAULT NULL,
  `socialite_credentials` text DEFAULT NULL,
  `system_customized` tinyint(1) NOT NULL DEFAULT 0,
  `paginate_number` int(11) NOT NULL DEFAULT 0,
  `available_version` varchar(40) DEFAULT NULL,
  `currency_format` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1=>Both\r\n2=>Text Only\r\n3=>Symbol Only',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `general_settings`
--

INSERT INTO `general_settings` (`id`, `site_name`, `cur_text`, `cur_sym`, `email_from`, `email_from_name`, `charge_cap`, `fixed_charge`, `percent_charge`, `email_template`, `sms_template`, `sms_from`, `push_title`, `push_template`, `base_color`, `secondary_color`, `mail_config`, `sms_config`, `firebase_config`, `global_shortcodes`, `ev`, `kv`, `en`, `sv`, `sn`, `pn`, `force_ssl`, `maintenance_mode`, `secure_password`, `agree`, `multi_language`, `registration`, `active_template`, `socialite_credentials`, `system_customized`, `paginate_number`, `available_version`, `currency_format`, `created_at`, `updated_at`) VALUES
(1, 'Middlemen', 'USD', '$', 'no-reply@viserlab.com', '{{site_name}}', 100.00000000, 2.00000000, 10.00, '<html>\n<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\">\n<meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\">\n<meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n<title>\n</title>\n<style type=\"text/css\">\n	.ReadMsgBody {\n		width: 100%;\n		background-color: #ffffff;\n	}\n	.ExternalClass {\n		width: 100%;\n		background-color: #ffffff;\n	}\n	.ExternalClass,\n	.ExternalClass p,\n	.ExternalClass span,\n	.ExternalClass font,\n	.ExternalClass td,\n	.ExternalClass div {\n		line-height: 100%;\n	}\n	html {\n		width: 100%;\n	}\n	body {\n		-webkit-text-size-adjust: none;\n		-ms-text-size-adjust: none;\n		margin: 0;\n		padding: 0;\n	}\n	table {\n		border-spacing: 0;\n		table-layout: fixed;\n		margin: 0 auto;\n		border-collapse: collapse;\n	}\n	table table table {\n		table-layout: auto;\n	}\n	.yshortcuts a {\n		border-bottom: none !important;\n	}\n	img:hover {\n		opacity: 0.9 !important;\n	}\n	a {\n		color: #0087ff;\n		text-decoration: none;\n	}\n	.textbutton a {\n		font-family: \"open sans\", arial, sans-serif !important;\n	}\n	.btn-link a {\n		color: #ffffff !important;\n	}\n	@media only screen and (max-width: 480px) {\n		body {\n			width: auto !important;\n		}\n		*[class=\"table-inner\"] {\n			width: 90% !important;\n			text-align: center !important;\n		}\n		*[class=\"table-full\"] {\n			width: 100% !important;\n			text-align: center !important;\n		} /* image */\n		img[class=\"img1\"] {\n			width: 100% !important;\n			height: auto !important;\n		}\n	}\n\n</style>\n<table bgcolor=\"#030442\" width=\"100%\" border=\"0\" align=\"center\" cellpadding=\"0\" cellspacing=\"0\">\n	<tbody>\n		<tr>\n			<td height=\"50\">\n			</td>\n		</tr>\n		<tr>\n			<td align=\"center\" style=\"text-align:center;vertical-align:top;font-size:0;\">\n				<table align=\"center\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">\n					<tbody>\n						<tr>\n							<td align=\"center\" width=\"600\">\n								<table class=\"table-inner\" width=\"95%\" border=\"0\" align=\"center\" cellpadding=\"0\" cellspacing=\"0\">\n									<tbody>\n										<tr>\n											<td bgcolor=\"#0087ff\" style=\"border-top-left-radius:6px; border-top-right-radius:6px;text-align:center;vertical-align:top;font-size:0;\" align=\"center\">\n												<table width=\"90%\" border=\"0\" align=\"center\" cellpadding=\"0\" cellspacing=\"0\">\n													<tbody>\n														<tr>\n															<td height=\"20\">\n															</td>\n														</tr>\n														<tr>\n															<td align=\"center\" style=\"font-family: Open sans, Arial, sans-serif; color:#FFFFFF; font-size:16px; font-weight: bold;\">\n															This is a System Generated Email</td>\n														</tr>\n														<tr>\n															<td height=\"20\">\n															</td>\n														</tr>\n													</tbody>\n												</table>\n											</td>\n										</tr>\n									</tbody>\n								</table>\n								<table class=\"table-inner\" width=\"95%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\n									<tbody>\n										<tr>\n											<td bgcolor=\"#FFFFFF\" align=\"center\" style=\"text-align:center;vertical-align:top;font-size:0;\">\n												<table align=\"center\" width=\"90%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\n													<tbody>\n														<tr>\n															<td height=\"35\">\n															</td>\n														</tr>\n														<tr>\n															<td align=\"center\" style=\"vertical-align:top;font-size:0;\">\n																<a href=\"#\">\n																	<img style=\"display:block; line-height:0px; font-size:0px; border:0px; width: 240px;\" width=\"240px\" src=\"https://viserlab.com/assets/images/logoIcon/logo-dark.png\" alt=\"img\">\n																</a>\n															</td>\n														</tr>\n														<tr>\n															<td height=\"40\"></td>\n														</tr>\n														<tr>\n															<td align=\"center\" style=\"font-family: Open Sans, Arial, sans-serif; font-size: 22px;color:#414a51;font-weight: bold;\">\n															Hello {{fullname}} ({{username}}) </td>\n														</tr>\n														<tr>\n															<td align=\"center\" style=\"text-align:center;vertical-align:top;font-size:0;\">\n																<table width=\"40\" border=\"0\" align=\"center\" cellpadding=\"0\" cellspacing=\"0\">\n																	<tbody>\n																		<tr>\n																			<td height=\"20\" style=\" border-bottom:3px solid #0087ff;\">\n																			</td>\n																		</tr>\n																	</tbody>\n																</table>\n															</td>\n														</tr>\n														<tr>\n															<td height=\"30\"></td>\n														</tr>\n														<tr>\n															<td align=\"left\" style=\"font-family: Open sans, Arial, sans-serif; color:#7f8c8d; font-size:16px; line-height: 28px;\">\n															{{message}}</td>\n														</tr>\n														<tr>\n															<td height=\"60\"></td>\n														</tr>\n													</tbody>\n												</table>\n											</td>\n										</tr>\n										<tr>\n											<td height=\"45\" align=\"center\" bgcolor=\"#f4f4f4\" style=\"border-bottom-left-radius:6px;border-bottom-right-radius:6px;\">\n												<table align=\"center\" width=\"90%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\n													<tbody>\n														<tr>\n															<td height=\"10\"></td>\n														</tr>\n														<tr>\n															<td class=\"preference-link\" align=\"center\" style=\"font-family: Open sans, Arial, sans-serif; color:#95a5a6; font-size:14px;\">\n																© 2023 <a href=\"#\">{{site_name}}</a> &nbsp;. All Rights Reserved. </td>\n														</tr>\n														<tr>\n															<td height=\"10\"></td>\n														</tr>\n													</tbody>\n												</table>\n											</td>\n										</tr>\n									</tbody>\n								</table>\n							</td>\n						</tr>\n					</tbody>\n				</table>\n			</td>\n		</tr>\n		<tr>\n			<td height=\"60\"></td>\n		</tr>\n	</tbody>\n</table>\n</html>\n', 'hi {{fullname}} ({{username}}), {{message}}', 'ViserAdmin', '{{site_name}}', 'hi {{fullname}} ({{username}}), {{message}}', '4bea76', '12205f', '{\"name\":\"php\"}', '{\"name\":\"nexmo\",\"clickatell\":{\"api_key\":\"----------------\"},\"infobip\":{\"username\":\"------------8888888\",\"password\":\"-----------------\"},\"message_bird\":{\"api_key\":\"-------------------\"},\"nexmo\":{\"api_key\":\"----------------------\",\"api_secret\":\"----------------------\"},\"sms_broadcast\":{\"username\":\"----------------------\",\"password\":\"-----------------------------\"},\"twilio\":{\"account_sid\":\"-----------------------\",\"auth_token\":\"---------------------------\",\"from\":\"----------------------\"},\"text_magic\":{\"username\":\"-----------------------\",\"apiv2_key\":\"-------------------------------\"},\"custom\":{\"method\":\"get\",\"url\":\"https:\\/\\/hostname\\/demo-api-v1\",\"headers\":{\"name\":[\"api_key\"],\"value\":[\"test_api 555\"]},\"body\":{\"name\":[\"from_number\"],\"value\":[\"5657545757\"]}}}', '{\"apiKey\":\"----------\",\"authDomain\":\"---------\",\"projectId\":\"--------\",\"storageBucket\":\"-------\",\"messagingSenderId\":\"--------\",\"appId\":\"--------\",\"measurementId\":\"------\",\"serverKey\":\"--------\"}', '{\n    \"site_name\":\"Name of your site\",\n    \"site_currency\":\"Currency of your site\",\n    \"currency_symbol\":\"Symbol of currency\"\n}', 0, 1, 1, 0, 0, 1, 0, 0, 0, 1, 1, 1, 'basic', '{\"google\":{\"client_id\":\"------------\",\"client_secret\":\"-------------\",\"status\":1},\"facebook\":{\"client_id\":\"------\",\"client_secret\":\"------\",\"status\":1},\"linkedin\":{\"client_id\":\"-----\",\"client_secret\":\"-----\",\"status\":1}}', 0, 20, '4.0', 1, NULL, '2025-11-08 11:42:44');

-- --------------------------------------------------------

--
-- Table structure for table `languages`
--

CREATE TABLE `languages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(40) NOT NULL,
  `code` varchar(40) NOT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0: not default language, 1: default language',
  `image` varchar(40) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `languages`
--

INSERT INTO `languages` (`id`, `name`, `code`, `is_default`, `image`, `created_at`, `updated_at`) VALUES
(1, 'English', 'en', 1, NULL, '2020-07-06 03:47:55', '2023-01-21 20:33:28');

-- --------------------------------------------------------

--
-- Table structure for table `listings`
--

CREATE TABLE `listings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `listing_number` varchar(40) NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `category_id` bigint(20) UNSIGNED DEFAULT NULL,
  `listing_category_id` bigint(20) UNSIGNED DEFAULT NULL,
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
  `highest_bidder_id` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
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
  `monthly_visitors` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `monthly_page_views` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `traffic_sources` varchar(500) DEFAULT NULL COMMENT 'JSON: organic, paid, social, etc.',
  `monetization_methods` varchar(500) DEFAULT NULL COMMENT 'JSON: ads, affiliate, products, etc.',
  `assets_included` text DEFAULT NULL COMMENT 'JSON: domain, content, email list, etc.',
  `is_verified` tinyint(1) NOT NULL DEFAULT 0,
  `requires_verification` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Does this listing require domain verification',
  `revenue_verified` tinyint(1) NOT NULL DEFAULT 0,
  `traffic_verified` tinyint(1) NOT NULL DEFAULT 0,
  `verification_notes` text DEFAULT NULL,
  `auction_duration_days` int(11) DEFAULT NULL COMMENT 'Stored duration for auction start after approval',
  `status` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '0:draft, 1:pending_approval, 2:active, 3:sold, 4:expired, 5:cancelled, 6:rejected',
  `rejection_reason` text DEFAULT NULL,
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `featured_until` timestamp NULL DEFAULT NULL,
  `auction_start` timestamp NULL DEFAULT NULL,
  `auction_end` timestamp NULL DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `sold_at` timestamp NULL DEFAULT NULL,
  `winner_id` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `final_price` decimal(28,8) NOT NULL DEFAULT 0.00000000,
  `escrow_id` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `view_count` int(11) NOT NULL DEFAULT 0,
  `watchlist_count` int(11) NOT NULL DEFAULT 0,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `meta_keywords` varchar(500) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `listing_categories`
--

CREATE TABLE `listing_categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `icon` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `business_type` enum('domain','website','social_media_account','mobile_app','desktop_app') NOT NULL,
  `parent_id` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `listing_categories`
--

INSERT INTO `listing_categories` (`id`, `name`, `slug`, `icon`, `description`, `business_type`, `parent_id`, `status`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 'Premium Domains', 'premium-domains', 'las la-globe', 'High-value domain names', 'domain', 0, 1, 1, '2025-12-01 10:23:40', '2025-12-01 10:23:40'),
(2, 'Brandable Domains', 'brandable-domains', 'las la-tag', 'Short, memorable domain names', 'domain', 0, 1, 2, '2025-12-01 10:23:40', '2025-12-01 10:23:40'),
(3, 'Keyword Domains', 'keyword-domains', 'las la-key', 'SEO-friendly keyword domains', 'domain', 0, 1, 3, '2025-12-01 10:23:40', '2025-12-01 10:23:40'),
(4, 'E-commerce Websites', 'ecommerce-websites', 'las la-shopping-cart', 'Online stores and shops', 'website', 0, 1, 4, '2025-12-01 10:23:40', '2025-12-01 10:23:40'),
(5, 'Content Websites', 'content-websites', 'las la-newspaper', 'Blogs and content sites', 'website', 0, 1, 5, '2025-12-01 10:23:40', '2025-12-01 10:23:40'),
(6, 'SaaS Businesses', 'saas-businesses', 'las la-cloud', 'Software as a Service', 'website', 0, 1, 6, '2025-12-01 10:23:40', '2025-12-01 10:23:40'),
(7, 'Affiliate Websites', 'affiliate-websites', 'las la-link', 'Affiliate marketing sites', 'website', 0, 1, 7, '2025-12-01 10:23:40', '2025-12-01 10:23:40'),
(8, 'Instagram Accounts', 'instagram-accounts', 'lab la-instagram', 'Instagram social media accounts', 'social_media_account', 0, 1, 8, '2025-12-01 10:23:40', '2025-12-01 10:23:40'),
(9, 'YouTube Channels', 'youtube-channels', 'lab la-youtube', 'YouTube channels', 'social_media_account', 0, 1, 9, '2025-12-01 10:23:40', '2025-12-01 10:23:40'),
(10, 'TikTok Accounts', 'tiktok-accounts', 'lab la-tiktok', 'TikTok social media accounts', 'social_media_account', 0, 1, 10, '2025-12-01 10:23:40', '2025-12-01 10:23:40'),
(11, 'Twitter/X Accounts', 'twitter-accounts', 'lab la-twitter', 'Twitter/X social media accounts', 'social_media_account', 0, 1, 11, '2025-12-01 10:23:40', '2025-12-01 10:23:40'),
(12, 'iOS Apps', 'ios-apps', 'lab la-apple', 'iPhone and iPad applications', 'mobile_app', 0, 1, 12, '2025-12-01 10:23:40', '2025-12-01 10:23:40'),
(13, 'Android Apps', 'android-apps', 'lab la-android', 'Android applications', 'mobile_app', 0, 1, 13, '2025-12-01 10:23:40', '2025-12-01 10:23:40'),
(14, 'Cross-Platform Apps', 'cross-platform-apps', 'las la-mobile-alt', 'iOS and Android apps', 'mobile_app', 0, 1, 14, '2025-12-01 10:23:40', '2025-12-01 10:23:40'),
(15, 'Windows Apps', 'windows-apps', 'lab la-windows', 'Windows desktop applications', 'desktop_app', 0, 1, 15, '2025-12-01 10:23:40', '2025-12-01 10:23:40'),
(16, 'Mac Apps', 'mac-apps', 'lab la-apple', 'macOS desktop applications', 'desktop_app', 0, 1, 16, '2025-12-01 10:23:40', '2025-12-01 10:23:40'),
(17, 'Cross-Platform Desktop', 'cross-platform-desktop', 'las la-desktop', 'Multi-platform desktop apps', 'desktop_app', 0, 1, 17, '2025-12-01 10:23:40', '2025-12-01 10:23:40');

-- --------------------------------------------------------

--
-- Table structure for table `listing_images`
--

CREATE TABLE `listing_images` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `listing_id` bigint(20) UNSIGNED NOT NULL,
  `image` varchar(255) NOT NULL,
  `caption` varchar(255) DEFAULT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `listing_metrics`
--

CREATE TABLE `listing_metrics` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `listing_id` bigint(20) UNSIGNED NOT NULL,
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
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `listing_questions`
--

CREATE TABLE `listing_questions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `listing_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `question` text NOT NULL,
  `answer` text DEFAULT NULL,
  `answered_at` timestamp NULL DEFAULT NULL,
  `is_public` tinyint(1) NOT NULL DEFAULT 1,
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `status` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '0:pending, 1:answered, 2:hidden',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `listing_views`
--

CREATE TABLE `listing_views` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `listing_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `ip_address` varchar(50) DEFAULT NULL,
  `user_agent` varchar(500) DEFAULT NULL,
  `referrer` varchar(500) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `marketplace_settings`
--

CREATE TABLE `marketplace_settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `key` varchar(100) NOT NULL,
  `value` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `marketplace_settings`
--

INSERT INTO `marketplace_settings` (`id`, `key`, `value`, `created_at`, `updated_at`) VALUES
(1, 'allow_auctions', '1', '2025-12-01 10:23:27', '2025-12-01 10:23:27'),
(2, 'allow_fixed_price', '1', '2025-12-01 10:23:27', '2025-12-01 10:23:27'),
(3, 'allow_domain_listings', '1', '2025-12-01 10:23:27', '2025-12-01 10:23:27'),
(4, 'allow_website_listings', '1', '2025-12-01 10:23:27', '2025-12-01 10:23:27'),
(5, 'allow_social_media_listings', '1', '2025-12-01 10:23:27', '2025-12-01 10:23:27'),
(6, 'allow_mobile_app_listings', '1', '2025-12-01 10:23:27', '2025-12-01 10:23:27'),
(7, 'allow_desktop_app_listings', '1', '2025-12-01 10:23:27', '2025-12-01 10:23:27'),
(8, 'max_auction_days', '30', '2025-12-01 10:23:27', '2025-12-01 10:23:27'),
(9, 'min_auction_days', '1', '2025-12-01 10:23:27', '2025-12-01 10:23:27'),
(10, 'require_domain_verification', '1', '2025-12-01 10:23:27', '2025-12-01 10:23:27'),
(11, 'require_website_verification', '1', '2025-12-01 10:23:27', '2025-12-01 10:23:27'),
(12, 'domain_verification_methods', '[\"txt_file\",\"dns_record\"]', '2025-12-01 10:23:27', '2025-12-01 10:23:27'),
(13, 'listing_approval_required', '1', '2025-12-01 10:23:27', '2025-12-01 10:23:27'),
(14, 'max_images_per_listing', '10', '2025-12-01 10:23:27', '2025-12-01 10:23:27'),
(15, 'min_listing_description', '100', '2025-12-01 10:23:27', '2025-12-01 10:23:27'),
(16, 'featured_listing_fee', '0', '2025-12-01 10:23:27', '2025-12-01 10:23:27'),
(17, 'listing_fee_percentage', '0', '2025-12-01 10:23:27', '2025-12-01 10:23:27'),
(18, 'escrow_fee_percentage', '5', '2025-12-01 10:23:27', '2025-12-01 10:23:27'),
(19, 'auto_extend_auction_minutes', '10', '2025-12-01 10:23:27', '2025-12-01 10:23:27'),
(20, 'bid_extension_threshold_minutes', '5', '2025-12-01 10:23:27', '2025-12-01 10:23:27');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `sender_id` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `admin_id` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `conversation_id` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `message` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `milestones`
--

CREATE TABLE `milestones` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `escrow_id` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `user_id` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `note` varchar(255) DEFAULT NULL,
  `amount` decimal(28,8) NOT NULL DEFAULT 0.00000000,
  `payment_status` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `status` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notification_logs`
--

CREATE TABLE `notification_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `sender` varchar(40) DEFAULT NULL,
  `sent_from` varchar(40) DEFAULT NULL,
  `sent_to` varchar(40) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `notification_type` varchar(40) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `user_read` tinyint(4) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notification_templates`
--

CREATE TABLE `notification_templates` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `act` varchar(40) DEFAULT NULL,
  `name` varchar(40) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `push_title` varchar(255) DEFAULT NULL,
  `email_body` text DEFAULT NULL,
  `sms_body` text DEFAULT NULL,
  `push_body` text DEFAULT NULL,
  `shortcodes` text DEFAULT NULL,
  `email_status` tinyint(1) NOT NULL DEFAULT 1,
  `email_sent_from_name` varchar(40) DEFAULT NULL,
  `email_sent_from_address` varchar(40) DEFAULT NULL,
  `sms_status` tinyint(1) NOT NULL DEFAULT 1,
  `sms_sent_from` varchar(40) DEFAULT NULL,
  `push_status` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notification_templates`
--

INSERT INTO `notification_templates` (`id`, `act`, `name`, `subject`, `push_title`, `email_body`, `sms_body`, `push_body`, `shortcodes`, `email_status`, `email_sent_from_name`, `email_sent_from_address`, `sms_status`, `sms_sent_from`, `push_status`, `created_at`, `updated_at`) VALUES
(1, 'BAL_ADD', 'Balance - Added', 'Your Account has been Credited', '{{site_name}} - Balance Added', '<div><div style=\"font-family: Montserrat, sans-serif;\">{{amount}} {{site_currency}} has been added to your account .</div><div style=\"font-family: Montserrat, sans-serif;\"><br></div><div style=\"font-family: Montserrat, sans-serif;\">Transaction Number : {{trx}}</div><div style=\"font-family: Montserrat, sans-serif;\"><br></div><span style=\"color: rgb(33, 37, 41); font-family: Montserrat, sans-serif;\">Your Current Balance is :&nbsp;</span><font style=\"font-family: Montserrat, sans-serif;\"><span style=\"font-weight: bolder;\">{{post_balance}}&nbsp; {{site_currency}}&nbsp;</span></font><br></div><div><font style=\"font-family: Montserrat, sans-serif;\"><span style=\"font-weight: bolder;\"><br></span></font></div><div>Admin note:&nbsp;<span style=\"color: rgb(33, 37, 41); font-size: 12px; font-weight: 600; white-space: nowrap; text-align: var(--bs-body-text-align);\">{{remark}}</span></div>', '{{amount}} {{site_currency}} credited in your account. Your Current Balance {{post_balance}} {{site_currency}} . Transaction: #{{trx}}. Admin note is \"{{remark}}\"', NULL, '{\"trx\":\"Transaction number for the action\",\"amount\":\"Amount inserted by the admin\",\"remark\":\"Remark inserted by the admin\",\"post_balance\":\"Balance of the user after this transaction\"}', 1, NULL, NULL, 0, NULL, 0, '2021-11-03 12:00:00', '2022-04-03 02:18:28'),
(2, 'BAL_SUB', 'Balance - Subtracted', 'Your Account has been Debited', '{{site_name}} - Balance Subtracted', '<div style=\"font-family: Montserrat, sans-serif;\">{{amount}} {{site_currency}} has been subtracted from your account .</div><div style=\"font-family: Montserrat, sans-serif;\"><br></div><div style=\"font-family: Montserrat, sans-serif;\">Transaction Number : {{trx}}</div><div style=\"font-family: Montserrat, sans-serif;\"><br></div><span style=\"color: rgb(33, 37, 41); font-family: Montserrat, sans-serif;\">Your Current Balance is :&nbsp;</span><font style=\"font-family: Montserrat, sans-serif;\"><span style=\"font-weight: bolder;\">{{post_balance}}&nbsp; {{site_currency}}</span></font><br><div><font style=\"font-family: Montserrat, sans-serif;\"><span style=\"font-weight: bolder;\"><br></span></font></div><div>Admin Note: {{remark}}</div>', '{{amount}} {{site_currency}} debited from your account. Your Current Balance {{post_balance}} {{site_currency}} . Transaction: #{{trx}}. Admin Note is {{remark}}', NULL, '{\"trx\":\"Transaction number for the action\",\"amount\":\"Amount inserted by the admin\",\"remark\":\"Remark inserted by the admin\",\"post_balance\":\"Balance of the user after this transaction\"}', 1, NULL, NULL, 1, NULL, 0, '2021-11-03 12:00:00', '2022-04-03 02:24:11'),
(3, 'DEPOSIT_COMPLETE', 'Deposit - Automated - Successful', 'Deposit Completed Successfully', '{{site_name}} - Deposit successful', '<div>Your deposit of&nbsp;<span style=\"font-weight: bolder;\">{{amount}} {{site_currency}}</span>&nbsp;is via&nbsp;&nbsp;<span style=\"font-weight: bolder;\">{{method_name}}&nbsp;</span>has been completed Successfully.<span style=\"font-weight: bolder;\"><br></span></div><div><span style=\"font-weight: bolder;\"><br></span></div><div><span style=\"font-weight: bolder;\">Details of your Deposit :<br></span></div><div><br></div><div>Amount : {{amount}} {{site_currency}}</div><div>Charge:&nbsp;<font color=\"#000000\">{{charge}} {{site_currency}}</font></div><div><br></div><div>Conversion Rate : 1 {{site_currency}} = {{rate}} {{method_currency}}</div><div>Received : {{method_amount}} {{method_currency}}<br></div><div>Paid via :&nbsp; {{method_name}}</div><div><br></div><div>Transaction Number : {{trx}}</div><div><font size=\"5\"><span style=\"font-weight: bolder;\"><br></span></font></div><div><font size=\"5\">Your current Balance is&nbsp;<span style=\"font-weight: bolder;\">{{post_balance}} {{site_currency}}</span></font></div><div><br style=\"font-family: Montserrat, sans-serif;\"></div>', '{{amount}} {{site_currency}} Deposit successfully by {{method_name}}', NULL, '{\"trx\":\"Transaction number for the deposit\",\"amount\":\"Amount inserted by the user\",\"charge\":\"Gateway charge set by the admin\",\"rate\":\"Conversion rate between base currency and method currency\",\"method_name\":\"Name of the deposit method\",\"method_currency\":\"Currency of the deposit method\",\"method_amount\":\"Amount after conversion between base currency and method currency\",\"post_balance\":\"Balance of the user after this transaction\"}', 1, NULL, NULL, 1, NULL, 0, '2021-11-03 12:00:00', '2022-04-03 02:25:43'),
(4, 'DEPOSIT_APPROVE', 'Deposit - Manual - Approved', 'Your Deposit is Approved', '{{site_name}} - Deposit Request Approved', '<div style=\"font-family: Montserrat, sans-serif;\">Your deposit request of&nbsp;<span style=\"font-weight: bolder;\">{{amount}} {{site_currency}}</span>&nbsp;is via&nbsp;&nbsp;<span style=\"font-weight: bolder;\">{{method_name}}&nbsp;</span>is Approved .<span style=\"font-weight: bolder;\"><br></span></div><div style=\"font-family: Montserrat, sans-serif;\"><span style=\"font-weight: bolder;\"><br></span></div><div style=\"font-family: Montserrat, sans-serif;\"><span style=\"font-weight: bolder;\">Details of your Deposit :<br></span></div><div style=\"font-family: Montserrat, sans-serif;\"><br></div><div style=\"font-family: Montserrat, sans-serif;\">Amount : {{amount}} {{site_currency}}</div><div style=\"font-family: Montserrat, sans-serif;\">Charge:&nbsp;<font color=\"#FF0000\">{{charge}} {{site_currency}}</font></div><div style=\"font-family: Montserrat, sans-serif;\"><br></div><div style=\"font-family: Montserrat, sans-serif;\">Conversion Rate : 1 {{site_currency}} = {{rate}} {{method_currency}}</div><div style=\"font-family: Montserrat, sans-serif;\">Received : {{method_amount}} {{method_currency}}<br></div><div style=\"font-family: Montserrat, sans-serif;\">Paid via :&nbsp; {{method_name}}</div><div style=\"font-family: Montserrat, sans-serif;\"><br></div><div style=\"font-family: Montserrat, sans-serif;\">Transaction Number : {{trx}}</div><div style=\"font-family: Montserrat, sans-serif;\"><font size=\"5\"><span style=\"font-weight: bolder;\"><br></span></font></div><div style=\"font-family: Montserrat, sans-serif;\"><font size=\"5\">Your current Balance is&nbsp;<span style=\"font-weight: bolder;\">{{post_balance}} {{site_currency}}</span></font></div><div style=\"font-family: Montserrat, sans-serif;\"><br></div><div style=\"font-family: Montserrat, sans-serif;\"><br></div>', 'Admin Approve Your {{amount}} {{site_currency}} payment request by {{method_name}} transaction : {{trx}}', NULL, '{\"trx\":\"Transaction number for the deposit\",\"amount\":\"Amount inserted by the user\",\"charge\":\"Gateway charge set by the admin\",\"rate\":\"Conversion rate between base currency and method currency\",\"method_name\":\"Name of the deposit method\",\"method_currency\":\"Currency of the deposit method\",\"method_amount\":\"Amount after conversion between base currency and method currency\",\"post_balance\":\"Balance of the user after this transaction\"}', 1, NULL, NULL, 1, NULL, 0, '2021-11-03 12:00:00', '2022-04-03 02:26:07'),
(5, 'DEPOSIT_REJECT', 'Deposit - Manual - Rejected', 'Your Deposit Request is Rejected', '{{site_name}} - Deposit Request Rejected', '<div style=\"font-family: Montserrat, sans-serif;\">Your deposit request of&nbsp;<span style=\"font-weight: bolder;\">{{amount}} {{site_currency}}</span>&nbsp;is via&nbsp;&nbsp;<span style=\"font-weight: bolder;\">{{method_name}} has been rejected</span>.<span style=\"font-weight: bolder;\"><br></span></div><div><br></div><div><br></div><div style=\"font-family: Montserrat, sans-serif;\">Conversion Rate : 1 {{site_currency}} = {{rate}} {{method_currency}}</div><div style=\"font-family: Montserrat, sans-serif;\">Received : {{method_amount}} {{method_currency}}<br></div><div style=\"font-family: Montserrat, sans-serif;\">Paid via :&nbsp; {{method_name}}</div><div style=\"font-family: Montserrat, sans-serif;\">Charge: {{charge}}</div><div style=\"font-family: Montserrat, sans-serif;\"><br></div><div style=\"font-family: Montserrat, sans-serif;\"><br></div><div style=\"font-family: Montserrat, sans-serif;\">Transaction Number was : {{trx}}</div><div style=\"font-family: Montserrat, sans-serif;\"><br></div><div style=\"font-family: Montserrat, sans-serif;\">if you have any queries, feel free to contact us.<br></div><br style=\"font-family: Montserrat, sans-serif;\"><div style=\"font-family: Montserrat, sans-serif;\"><br><br></div><span style=\"color: rgb(33, 37, 41); font-family: Montserrat, sans-serif;\">{{rejection_message}}</span><br>', 'Admin Rejected Your {{amount}} {{site_currency}} payment request by {{method_name}}\r\n\r\n{{rejection_message}}', NULL, '{\"trx\":\"Transaction number for the deposit\",\"amount\":\"Amount inserted by the user\",\"charge\":\"Gateway charge set by the admin\",\"rate\":\"Conversion rate between base currency and method currency\",\"method_name\":\"Name of the deposit method\",\"method_currency\":\"Currency of the deposit method\",\"method_amount\":\"Amount after conversion between base currency and method currency\",\"rejection_message\":\"Rejection message by the admin\"}', 1, NULL, NULL, 1, NULL, 0, '2021-11-03 12:00:00', '2022-04-05 03:45:27'),
(6, 'DEPOSIT_REQUEST', 'Deposit - Manual - Requested', 'Deposit Request Submitted Successfully', NULL, '<div>Your deposit request of&nbsp;<span style=\"font-weight: bolder;\">{{amount}} {{site_currency}}</span>&nbsp;is via&nbsp;&nbsp;<span style=\"font-weight: bolder;\">{{method_name}}&nbsp;</span>submitted successfully<span style=\"font-weight: bolder;\">&nbsp;.<br></span></div><div><span style=\"font-weight: bolder;\"><br></span></div><div><span style=\"font-weight: bolder;\">Details of your Deposit :<br></span></div><div><br></div><div>Amount : {{amount}} {{site_currency}}</div><div>Charge:&nbsp;<font color=\"#FF0000\">{{charge}} {{site_currency}}</font></div><div><br></div><div>Conversion Rate : 1 {{site_currency}} = {{rate}} {{method_currency}}</div><div>Payable : {{method_amount}} {{method_currency}}<br></div><div>Pay via :&nbsp; {{method_name}}</div><div><br></div><div>Transaction Number : {{trx}}</div><div><br></div><div><br style=\"font-family: Montserrat, sans-serif;\"></div>', '{{amount}} {{site_currency}} Deposit requested by {{method_name}}. Charge: {{charge}} . Trx: {{trx}}', NULL, '{\"trx\":\"Transaction number for the deposit\",\"amount\":\"Amount inserted by the user\",\"charge\":\"Gateway charge set by the admin\",\"rate\":\"Conversion rate between base currency and method currency\",\"method_name\":\"Name of the deposit method\",\"method_currency\":\"Currency of the deposit method\",\"method_amount\":\"Amount after conversion between base currency and method currency\"}', 1, NULL, NULL, 1, NULL, 0, '2021-11-03 12:00:00', '2022-04-03 02:29:19'),
(7, 'PASS_RESET_CODE', 'Password - Reset - Code', 'Password Reset', '{{site_name}} Password Reset Code', '<div style=\"font-family: Montserrat, sans-serif;\">We have received a request to reset the password for your account on&nbsp;<span style=\"font-weight: bolder;\">{{time}} .<br></span></div><div style=\"font-family: Montserrat, sans-serif;\">Requested From IP:&nbsp;<span style=\"font-weight: bolder;\">{{ip}}</span>&nbsp;using&nbsp;<span style=\"font-weight: bolder;\">{{browser}}</span>&nbsp;on&nbsp;<span style=\"font-weight: bolder;\">{{operating_system}}&nbsp;</span>.</div><div style=\"font-family: Montserrat, sans-serif;\"><br></div><br style=\"font-family: Montserrat, sans-serif;\"><div style=\"font-family: Montserrat, sans-serif;\"><div>Your account recovery code is:&nbsp;&nbsp;&nbsp;<font size=\"6\"><span style=\"font-weight: bolder;\">{{code}}</span></font></div><div><br></div></div><div style=\"font-family: Montserrat, sans-serif;\"><br></div><div style=\"font-family: Montserrat, sans-serif;\"><font size=\"4\" color=\"#CC0000\">If you do not wish to reset your password, please disregard this message.&nbsp;</font><br></div><div><font size=\"4\" color=\"#CC0000\"><br></font></div>', 'Your account recovery code is: {{code}}', NULL, '{\"code\":\"Verification code for password reset\",\"ip\":\"IP address of the user\",\"browser\":\"Browser of the user\",\"operating_system\":\"Operating system of the user\",\"time\":\"Time of the request\"}', 1, NULL, NULL, 0, NULL, 0, '2021-11-03 12:00:00', '2022-03-20 20:47:05'),
(8, 'PASS_RESET_DONE', 'Password - Reset - Confirmation', 'You have reset your password', NULL, '<p style=\"font-family: Montserrat, sans-serif;\">You have successfully reset your password.</p><p style=\"font-family: Montserrat, sans-serif;\">You changed from&nbsp; IP:&nbsp;<span style=\"font-weight: bolder;\">{{ip}}</span>&nbsp;using&nbsp;<span style=\"font-weight: bolder;\">{{browser}}</span>&nbsp;on&nbsp;<span style=\"font-weight: bolder;\">{{operating_system}}&nbsp;</span>&nbsp;on&nbsp;<span style=\"font-weight: bolder;\">{{time}}</span></p><p style=\"font-family: Montserrat, sans-serif;\"><span style=\"font-weight: bolder;\"><br></span></p><p style=\"font-family: Montserrat, sans-serif;\"><span style=\"font-weight: bolder;\"><font color=\"#ff0000\">If you did not change that, please contact us as soon as possible.</font></span></p>', 'Your password has been changed successfully', NULL, '{\"ip\":\"IP address of the user\",\"browser\":\"Browser of the user\",\"operating_system\":\"Operating system of the user\",\"time\":\"Time of the request\"}', 1, NULL, NULL, 1, NULL, 0, '2021-11-03 12:00:00', '2022-04-05 03:46:35'),
(9, 'ADMIN_SUPPORT_REPLY', 'Support - Reply', 'Reply Support Ticket', '{{site_name}} - Support Ticket Replied', '<div><p><span data-mce-style=\"font-size: 11pt;\" style=\"font-size: 11pt;\"><span style=\"font-weight: bolder;\">A member from our support team has replied to the following ticket:</span></span></p><p><span style=\"font-weight: bolder;\"><span data-mce-style=\"font-size: 11pt;\" style=\"font-size: 11pt;\"><span style=\"font-weight: bolder;\"><br></span></span></span></p><p><span style=\"font-weight: bolder;\">[Ticket#{{ticket_id}}] {{ticket_subject}}<br><br>Click here to reply:&nbsp; {{link}}</span></p><p>----------------------------------------------</p><p>Here is the reply :<br></p><p>{{reply}}<br></p></div><div><br style=\"font-family: Montserrat, sans-serif;\"></div>', 'Your Ticket#{{ticket_id}} :  {{ticket_subject}} has been replied.', NULL, '{\"ticket_id\":\"ID of the support ticket\",\"ticket_subject\":\"Subject  of the support ticket\",\"reply\":\"Reply made by the admin\",\"link\":\"URL to view the support ticket\"}', 1, NULL, NULL, 1, NULL, 0, '2021-11-03 12:00:00', '2022-03-20 20:47:51'),
(10, 'EVER_CODE', 'Verification - Email', 'Please verify your email address', NULL, '<br><div><div style=\"font-family: Montserrat, sans-serif;\">Thanks For joining us.<br></div><div style=\"font-family: Montserrat, sans-serif;\">Please use the below code to verify your email address.<br></div><div style=\"font-family: Montserrat, sans-serif;\"><br></div><div style=\"font-family: Montserrat, sans-serif;\">Your email verification code is:<font size=\"6\"><span style=\"font-weight: bolder;\">&nbsp;{{code}}</span></font></div></div>', '---', NULL, '{\"code\":\"Email verification code\"}', 1, NULL, NULL, 0, NULL, 0, '2021-11-03 12:00:00', '2022-04-03 02:32:07'),
(11, 'SVER_CODE', 'Verification - SMS', 'Verify Your Mobile Number', NULL, '---', 'Your phone verification code is: {{code}}', NULL, '{\"code\":\"SMS Verification Code\"}', 0, NULL, NULL, 1, NULL, 0, '2021-11-03 12:00:00', '2022-03-20 19:24:37'),
(12, 'WITHDRAW_APPROVE', 'Withdraw - Approved', 'Withdraw Request has been Processed and your money is sent', '{{site_name}} - Withdrawal Request Approved', '<div style=\"font-family: Montserrat, sans-serif;\">Your withdraw request of&nbsp;<span style=\"font-weight: bolder;\">{{amount}} {{site_currency}}</span>&nbsp; via&nbsp;&nbsp;<span style=\"font-weight: bolder;\">{{method_name}}&nbsp;</span>has been Processed Successfully.<span style=\"font-weight: bolder;\"><br></span></div><div style=\"font-family: Montserrat, sans-serif;\"><span style=\"font-weight: bolder;\"><br></span></div><div style=\"font-family: Montserrat, sans-serif;\"><span style=\"font-weight: bolder;\">Details of your withdraw:<br></span></div><div style=\"font-family: Montserrat, sans-serif;\"><br></div><div style=\"font-family: Montserrat, sans-serif;\">Amount : {{amount}} {{site_currency}}</div><div style=\"font-family: Montserrat, sans-serif;\">Charge:&nbsp;<font color=\"#FF0000\">{{charge}} {{site_currency}}</font></div><div style=\"font-family: Montserrat, sans-serif;\"><br></div><div style=\"font-family: Montserrat, sans-serif;\">Conversion Rate : 1 {{site_currency}} = {{rate}} {{method_currency}}</div><div style=\"font-family: Montserrat, sans-serif;\">You will get: {{method_amount}} {{method_currency}}<br></div><div style=\"font-family: Montserrat, sans-serif;\">Via :&nbsp; {{method_name}}</div><div style=\"font-family: Montserrat, sans-serif;\"><br></div><div style=\"font-family: Montserrat, sans-serif;\">Transaction Number : {{trx}}</div><div style=\"font-family: Montserrat, sans-serif;\"><br></div><div style=\"font-family: Montserrat, sans-serif;\">-----</div><div style=\"font-family: Montserrat, sans-serif;\"><br></div><div style=\"font-family: Montserrat, sans-serif;\"><font size=\"4\">Details of Processed Payment :</font></div><div style=\"font-family: Montserrat, sans-serif;\"><font size=\"4\"><span style=\"font-weight: bolder;\">{{admin_details}}</span></font></div>', 'Admin Approve Your {{amount}} {{site_currency}} withdraw request by {{method_name}}. Transaction {{trx}}', NULL, '{\"trx\":\"Transaction number for the withdraw\",\"amount\":\"Amount requested by the user\",\"charge\":\"Gateway charge set by the admin\",\"rate\":\"Conversion rate between base currency and method currency\",\"method_name\":\"Name of the withdraw method\",\"method_currency\":\"Currency of the withdraw method\",\"method_amount\":\"Amount after conversion between base currency and method currency\",\"admin_details\":\"Details provided by the admin\"}', 1, NULL, NULL, 1, NULL, 0, '2021-11-03 12:00:00', '2022-03-20 20:50:16'),
(13, 'WITHDRAW_REJECT', 'Withdraw - Rejected', 'Withdraw Request has been Rejected and your money is refunded to your account', '{{site_name}} - Withdrawal Request Rejected', '<div style=\"font-family: Montserrat, sans-serif;\">Your withdraw request of&nbsp;<span style=\"font-weight: bolder;\">{{amount}} {{site_currency}}</span>&nbsp; via&nbsp;&nbsp;<span style=\"font-weight: bolder;\">{{method_name}}&nbsp;</span>has been Rejected.<span style=\"font-weight: bolder;\"><br></span></div><div style=\"font-family: Montserrat, sans-serif;\"><span style=\"font-weight: bolder;\"><br></span></div><div style=\"font-family: Montserrat, sans-serif;\"><span style=\"font-weight: bolder;\">Details of your withdraw:<br></span></div><div style=\"font-family: Montserrat, sans-serif;\"><br></div><div style=\"font-family: Montserrat, sans-serif;\">Amount : {{amount}} {{site_currency}}</div><div style=\"font-family: Montserrat, sans-serif;\">Charge:&nbsp;<font color=\"#FF0000\">{{charge}} {{site_currency}}</font></div><div style=\"font-family: Montserrat, sans-serif;\"><br></div><div style=\"font-family: Montserrat, sans-serif;\">Conversion Rate : 1 {{site_currency}} = {{rate}} {{method_currency}}</div><div style=\"font-family: Montserrat, sans-serif;\">You should get: {{method_amount}} {{method_currency}}<br></div><div style=\"font-family: Montserrat, sans-serif;\">Via :&nbsp; {{method_name}}</div><div style=\"font-family: Montserrat, sans-serif;\"><br></div><div style=\"font-family: Montserrat, sans-serif;\">Transaction Number : {{trx}}</div><div style=\"font-family: Montserrat, sans-serif;\"><br></div><div style=\"font-family: Montserrat, sans-serif;\"><br></div><div style=\"font-family: Montserrat, sans-serif;\">----</div><div style=\"font-family: Montserrat, sans-serif;\"><font size=\"3\"><br></font></div><div style=\"font-family: Montserrat, sans-serif;\"><font size=\"3\">{{amount}} {{site_currency}} has been&nbsp;<span style=\"font-weight: bolder;\">refunded&nbsp;</span>to your account and your current Balance is&nbsp;<span style=\"font-weight: bolder;\">{{post_balance}}</span><span style=\"font-weight: bolder;\">&nbsp;{{site_currency}}</span></font></div><div style=\"font-family: Montserrat, sans-serif;\"><br></div><div style=\"font-family: Montserrat, sans-serif;\">-----</div><div style=\"font-family: Montserrat, sans-serif;\"><br></div><div style=\"font-family: Montserrat, sans-serif;\"><font size=\"4\">Details of Rejection :</font></div><div style=\"font-family: Montserrat, sans-serif;\"><font size=\"4\"><span style=\"font-weight: bolder;\">{{admin_details}}</span></font></div><div style=\"font-family: Montserrat, sans-serif;\"><br></div><div style=\"font-family: Montserrat, sans-serif;\"><br><br><br><br><br></div><div></div><div></div>', 'Admin Rejected Your {{amount}} {{site_currency}} withdraw request. Your Main Balance {{post_balance}}  {{method_name}} , Transaction {{trx}}', NULL, '{\"trx\":\"Transaction number for the withdraw\",\"amount\":\"Amount requested by the user\",\"charge\":\"Gateway charge set by the admin\",\"rate\":\"Conversion rate between base currency and method currency\",\"method_name\":\"Name of the withdraw method\",\"method_currency\":\"Currency of the withdraw method\",\"method_amount\":\"Amount after conversion between base currency and method currency\",\"post_balance\":\"Balance of the user after fter this action\",\"admin_details\":\"Rejection message by the admin\"}', 1, NULL, NULL, 1, NULL, 0, '2021-11-03 12:00:00', '2022-03-20 20:57:46'),
(14, 'WITHDRAW_REQUEST', 'Withdraw - Requested', 'Withdraw Request Submitted Successfully', '{{site_name}} - Requested for withdrawal', '<div style=\"font-family: Montserrat, sans-serif;\">Your withdraw request of&nbsp;<span style=\"font-weight: bolder;\">{{amount}} {{site_currency}}</span>&nbsp; via&nbsp;&nbsp;<span style=\"font-weight: bolder;\">{{method_name}}&nbsp;</span>has been submitted Successfully.<span style=\"font-weight: bolder;\"><br></span></div><div style=\"font-family: Montserrat, sans-serif;\"><span style=\"font-weight: bolder;\"><br></span></div><div style=\"font-family: Montserrat, sans-serif;\"><span style=\"font-weight: bolder;\">Details of your withdraw:<br></span></div><div style=\"font-family: Montserrat, sans-serif;\"><br></div><div style=\"font-family: Montserrat, sans-serif;\">Amount : {{amount}} {{site_currency}}</div><div style=\"font-family: Montserrat, sans-serif;\">Charge:&nbsp;<font color=\"#FF0000\">{{charge}} {{site_currency}}</font></div><div style=\"font-family: Montserrat, sans-serif;\"><br></div><div style=\"font-family: Montserrat, sans-serif;\">Conversion Rate : 1 {{site_currency}} = {{rate}} {{method_currency}}</div><div style=\"font-family: Montserrat, sans-serif;\">You will get: {{method_amount}} {{method_currency}}<br></div><div style=\"font-family: Montserrat, sans-serif;\">Via :&nbsp; {{method_name}}</div><div style=\"font-family: Montserrat, sans-serif;\"><br></div><div style=\"font-family: Montserrat, sans-serif;\">Transaction Number : {{trx}}</div><div style=\"font-family: Montserrat, sans-serif;\"><br></div><div style=\"font-family: Montserrat, sans-serif;\"><br></div><div style=\"font-family: Montserrat, sans-serif;\"><font size=\"5\">Your current Balance is&nbsp;<span style=\"font-weight: bolder;\">{{post_balance}} {{site_currency}}</span></font></div><div style=\"font-family: Montserrat, sans-serif;\"><br></div><div style=\"font-family: Montserrat, sans-serif;\"><br><br><br></div>', '{{amount}} {{site_currency}} withdraw requested by {{method_name}}. You will get {{method_amount}} {{method_currency}} Trx: {{trx}}', NULL, '{\"trx\":\"Transaction number for the withdraw\",\"amount\":\"Amount requested by the user\",\"charge\":\"Gateway charge set by the admin\",\"rate\":\"Conversion rate between base currency and method currency\",\"method_name\":\"Name of the withdraw method\",\"method_currency\":\"Currency of the withdraw method\",\"method_amount\":\"Amount after conversion between base currency and method currency\",\"post_balance\":\"Balance of the user after fter this transaction\"}', 1, NULL, NULL, 1, NULL, 0, '2021-11-03 12:00:00', '2022-03-21 04:39:03'),
(15, 'DEFAULT', 'Default Template', '{{subject}}', '{{subject}}', '{{message}}', '{{message}}', NULL, '{\"subject\":\"Subject\",\"message\":\"Message\"}', 1, NULL, NULL, 1, NULL, 0, '2019-09-14 13:14:22', '2021-11-04 09:38:55'),
(16, 'KYC_APPROVE', 'KYC Approved', 'KYC has been approved', '{{site_name}} - KYC Approved', NULL, NULL, NULL, '[]', 1, NULL, NULL, 1, NULL, 0, NULL, NULL),
(17, 'KYC_REJECT', 'KYC Rejected', 'KYC has been rejected', '{{site_name}} - KYC Rejected', NULL, NULL, NULL, '{\"reason\":\"Rejection Reason\"}', 1, NULL, NULL, 1, NULL, 0, NULL, NULL),
(18, 'INVITATION_LINK', ' Invitation Link', 'You are invited to join with escrow', NULL, 'You are invited to this escrow site.&nbsp;<div>Please <a href=\"{{link}}\" title=\"\" target=\"_blank\">register now</a></div>', 'You are invited to this escrow site. please visit this site to register {{link}', NULL, '{\"link\":\"Registration link\"}', 1, NULL, NULL, 1, NULL, 0, NULL, NULL),
(19, 'ESCROW_CANCELLED', 'Escrow Cancelled', 'Escrow Cancelled', NULL, 'Your escrow <b>\"{{title}}\" </b>has been canceled by the <b>{{canceller}}</b>.<div>The escrow amount was {{amount}} {{site_currency}} and the funded amount was {{total_fund}} {{site_currency}}</div>', 'Your escrow \"{{title}}\" has been canceled by the {{canceller}}.\r\nThe escrow amount was {{amount}} {{site_currency}} and the funded amount was {{total_fund}} {{site_currency}}', NULL, '{\"title\":\"Title of the escrow\",\"amount\":\"Amount of the escrow\",\"canceller\":\"Who cancelled the escrow\",\"total_fund\":\"How many amount was funded to the escrow\"}', 1, NULL, NULL, 1, NULL, 0, NULL, '2022-04-03 22:38:00'),
(20, 'ESCROW_ACCEPTED', 'Escrow Accepted', 'Escrow Accepted', NULL, '<span style=\"color: rgb(33, 37, 41);\">Your escrow&nbsp;</span><span style=\"font-weight: bolder; color: rgb(33, 37, 41);\">\"{{title}}\"&nbsp;</span><span style=\"color: rgb(33, 37, 41);\">has been accepted by the&nbsp;</span><span style=\"font-weight: bolder; color: rgb(33, 37, 41);\">{{accepter}}</span><span style=\"color: rgb(33, 37, 41);\">.</span><div>The escrow amount was {{amount}} {{site_currency}} and the funded amount was {{total_fund}} {{site_currency}}</div>', 'Your escrow \"{{title}}\" has been accepted by the {{accepter}}.\r\nThe escrow amount was {{amount}} {{site_currency}} and the funded amount was {{total_fund}} {{site_currency}}', NULL, '{\"title\":\"Title of the escrow\",\"amount\":\"Amount of the escrow\",\"accepter\":\"Who accpet the escrow\",\"total_fund\":\"How many amount was funded to the escrow\"}', 1, NULL, NULL, 1, NULL, 0, NULL, '2022-04-03 22:41:14'),
(21, 'ESCROW_PAYMENT_DISPATCHED', 'Escrow Payment Dispatched', 'Escrow Payment Dispatched', NULL, '<span style=\"color: rgb(33, 37, 41);\">Your escrow&nbsp;</span><span style=\"color: rgb(33, 37, 41); font-weight: bolder;\">\"{{title}}\"&nbsp;</span><span style=\"color: rgb(33, 37, 41);\">has been dispatched by the buyer.</span><div>The escrow amount was {{amount}} {{site_currency}} and the charge was {{charge}} {{site_currency}}.</div><div>We have cut {{seller_charge}} {{site_currency}} from your account after got paid. The transaction number is {{trx}} and your current balance is {{post_balance}} {{site_currency}}</div>', 'Your escrow \"{{title}}\" has been dispatched by the buyer.\r\nThe escrow amount was {{amount}} {{site_currency}} and the charge was {{charge}} {{site_currency}}.\r\nWe have cut {{seller_charge}} {{site_currency}} from your account after got paid. The transaction number is {{trx}} and your current balance is {{post_balance}} {{site_currency}}', NULL, '{\"title\":\"Title of the escrow\",\"amount\":\"Amount of the escrow\",\"charge\":\"Total charge of the escrow\",\"seller_charge\":\"Amount of the seller charge\",\"trx\":\"Transaction number\",\"post_balance\":\"Seller balance after transaction\"}', 1, NULL, NULL, 1, NULL, 0, NULL, '2022-04-03 22:52:19'),
(22, 'ESCROW_DISPUTED', 'Escrow Disputed', 'Escrow Disputed', NULL, '<span style=\"color: rgb(33, 37, 41);\">Your escrow&nbsp;</span><span style=\"color: rgb(33, 37, 41); font-weight: bolder;\">\"{{title}}\"&nbsp;</span><span style=\"color: rgb(33, 37, 41);\">has been disputed by the <b>{{disputer}}</b>.&nbsp;</span><span style=\"color: rgb(33, 37, 41); font-size: 1rem;\">The escrow amount was {{amount}} {{site_currency}}.</span><div><span style=\"color: rgb(33, 37, 41); font-size: 1rem;\">{{total_fund}} {{site_currency}} was funded to the escrow.</span></div><div><span style=\"color: rgb(33, 37, 41); font-size: 1rem;\">The reason is : \"{{dispute_note}}\"</span></div><div><span style=\"color: rgb(33, 37, 41); font-size: 1rem;\">Our staff will join you by chat. Please wait for admin action.</span></div>', 'Your escrow \"{{title}}\" has been disputed by the {{disputer}}. The escrow amount was {{amount}} {{site_currency}}.\r\n{{total_fund}} {{site_currency}} was funded to the escrow.\r\nThe reason is : \"{{dispute_note}}\"\r\nOur staff will join you by chat. Please wait for admin action.', NULL, '{\"title\":\"Title of the escrow\",\"amount\":\"Amount of the escrow\",\"disputer\":\"Who dispute the escrow\",\"total_fund\":\"How many amount funded to the escrow\",\"dispute_note\":\"Dispute note\"}', 1, NULL, NULL, 1, NULL, 0, NULL, '2022-04-03 23:00:26'),
(23, 'ESCROW_ADMIN_ACTION', 'Escrow Admin Action', 'Escrow Admin Action', NULL, '<span style=\"color: rgb(33, 37, 41);\">Your escrow&nbsp;</span><span style=\"color: rgb(33, 37, 41); font-weight: bolder;\">\"{{title}}\"&nbsp;</span><span style=\"color: rgb(33, 37, 41);\">was disputed and the admin has taken an action. Admin decided to give {{buyer_amount}} {{site_currency}} to buyer and {{seller_amount}} {{site_currency}} to seller.</span><br><div><span style=\"color: rgb(33, 37, 41);\">System has cut the {{charge}} {{site_currency}} as charge. Your current balance is {{post_balance}} {{site_currency}}. The transaction number is #{{trx}}.</span></div>', 'Your escrow \"{{title}}\" was disputed and the admin has taken an action. Admin decided to give {{buyer_amount}} {{site_currency}} to buyer and {{seller_amount}} {{site_currency}} to seller.\r\nSystem has cut the {{charge}} {{site_currency}} as charge. Your current balance is {{post_balance}} {{site_currency}}. The transaction number is #{{trx}}.', NULL, '{\"title\":\"Title of the escrow\",\"amount\":\"Amount of the escrow\",\"total_fund\":\"How many amount funded to the escrow\",\"seller_amount\":\"How many amount seller will get\",\"buyer_amount\":\"How many amount buyer will get\",\"charge\":\"How many charge will cut by admin\",\"trx\":\"Transaction number\",\"post_balance\":\"Balance after transaction\"}', 1, NULL, NULL, 1, NULL, 0, NULL, '2022-04-03 23:08:38'),
(24, 'LISTING_SUBMITTED', 'Listing Submitted for Review', 'Your Listing Has Been Submitted', '{{site_name}} - Listing Submitted', '<div style=\"font-family: Montserrat, sans-serif;\"><p>Your listing <strong>\"{{listing_title}}\"</strong> has been submitted for review.</p><p>Listing Number: <strong>{{listing_number}}</strong></p><p>Business Type: {{business_type}}</p><p>Asking Price: {{asking_price}} {{site_currency}}</p><p>Our team will review your listing and get back to you within 24-48 hours.</p></div>', 'Your listing \"{{listing_title}}\" ({{listing_number}}) has been submitted for review. We will notify you once approved.', NULL, '{\"listing_title\":\"Title of the listing\",\"listing_number\":\"Unique listing number\",\"business_type\":\"Type of business\",\"asking_price\":\"Asking price of the listing\"}', 1, NULL, NULL, 1, NULL, 0, NULL, NULL),
(25, 'LISTING_APPROVED', 'Listing Approved', 'Your Listing Has Been Approved', '{{site_name}} - Listing Approved', '<div style=\"font-family: Montserrat, sans-serif;\"><p>Great news! Your listing <strong>\"{{listing_title}}\"</strong> has been approved and is now live on our marketplace.</p><p>Listing Number: <strong>{{listing_number}}</strong></p><p>View your listing: <a href=\"{{listing_url}}\">{{listing_url}}</a></p><p>Tips to increase visibility:</p><ul><li>Share your listing on social media</li><li>Respond promptly to inquiries</li><li>Keep your listing information up to date</li></ul></div>', 'Your listing \"{{listing_title}}\" has been approved and is now live! View it at {{listing_url}}', NULL, '{\"listing_title\":\"Title of the listing\",\"listing_number\":\"Unique listing number\",\"listing_url\":\"URL to view the listing\"}', 1, NULL, NULL, 1, NULL, 0, NULL, NULL),
(26, 'LISTING_REJECTED', 'Listing Rejected', 'Your Listing Has Been Rejected', '{{site_name}} - Listing Rejected', '<div style=\"font-family: Montserrat, sans-serif;\"><p>Unfortunately, your listing <strong>\"{{listing_title}}\"</strong> has been rejected.</p><p>Listing Number: <strong>{{listing_number}}</strong></p><p><strong>Reason:</strong> {{rejection_reason}}</p><p>You can edit your listing and resubmit it for review. If you have any questions, please contact our support team.</p></div>', 'Your listing \"{{listing_title}}\" has been rejected. Reason: {{rejection_reason}}', NULL, '{\"listing_title\":\"Title of the listing\",\"listing_number\":\"Unique listing number\",\"rejection_reason\":\"Reason for rejection\"}', 1, NULL, NULL, 1, NULL, 0, NULL, NULL),
(27, 'BID_PLACED', 'New Bid on Listing', 'New Bid Received on Your Listing', '{{site_name}} - New Bid Received', '<div style=\"font-family: Montserrat, sans-serif;\"><p>A new bid has been placed on your listing <strong>\"{{listing_title}}\"</strong>!</p><p>Bid Amount: <strong>{{bid_amount}} {{site_currency}}</strong></p><p>Bidder: {{bidder_username}}</p><p>Total Bids: {{total_bids}}</p><p>Current Highest Bid: {{current_bid}} {{site_currency}}</p><p>Auction Ends: {{auction_end}}</p><p><a href=\"{{listing_url}}\">View your listing</a></p></div>', 'New bid of {{bid_amount}} {{site_currency}} on your listing \"{{listing_title}}\". Current highest bid: {{current_bid}} {{site_currency}}', NULL, '{\"listing_title\":\"Title of the listing\",\"bid_amount\":\"Amount of the bid\",\"bidder_username\":\"Username of the bidder\",\"total_bids\":\"Total number of bids\",\"current_bid\":\"Current highest bid\",\"auction_end\":\"Auction end date/time\",\"listing_url\":\"URL to view the listing\"}', 1, NULL, NULL, 1, NULL, 0, NULL, NULL),
(28, 'BID_OUTBID', 'You Have Been Outbid', 'You Have Been Outbid!', '{{site_name}} - You Have Been Outbid', '<div style=\"font-family: Montserrat, sans-serif;\"><p>You have been outbid on <strong>\"{{listing_title}}\"</strong>!</p><p>Your Bid: {{your_bid}} {{site_currency}}</p><p>New Highest Bid: <strong>{{current_bid}} {{site_currency}}</strong></p><p>Minimum Next Bid: {{minimum_bid}} {{site_currency}}</p><p>Auction Ends: {{auction_end}}</p><p><a href=\"{{listing_url}}\">Place a new bid now</a> to stay in the running!</p></div>', 'You have been outbid on \"{{listing_title}}\"! New highest bid: {{current_bid}} {{site_currency}}. Place a new bid at {{listing_url}}', NULL, '{\"listing_title\":\"Title of the listing\",\"your_bid\":\"Your previous bid amount\",\"current_bid\":\"Current highest bid\",\"minimum_bid\":\"Minimum next bid amount\",\"auction_end\":\"Auction end date/time\",\"listing_url\":\"URL to view the listing\"}', 1, NULL, NULL, 1, NULL, 0, NULL, NULL),
(29, 'AUCTION_WON', 'Congratulations! You Won the Auction', 'You Won the Auction!', '{{site_name}} - Auction Won', '<div style=\"font-family: Montserrat, sans-serif;\"><p>Congratulations! You have won the auction for <strong>\"{{listing_title}}\"</strong>!</p><p>Winning Bid: <strong>{{winning_bid}} {{site_currency}}</strong></p><p>Seller: {{seller_username}}</p><p>An escrow has been created for this transaction. Please complete the payment within 48 hours to secure your purchase.</p><p><a href=\"{{escrow_url}}\">Complete Payment Now</a></p><p>If you have any questions, please contact the seller or our support team.</p></div>', 'Congratulations! You won the auction for \"{{listing_title}}\" with a bid of {{winning_bid}} {{site_currency}}. Complete payment at {{escrow_url}}', NULL, '{\"listing_title\":\"Title of the listing\",\"winning_bid\":\"Winning bid amount\",\"seller_username\":\"Seller username\",\"escrow_url\":\"URL to complete escrow payment\"}', 1, NULL, NULL, 1, NULL, 0, NULL, NULL),
(30, 'AUCTION_SOLD', 'Your Listing Has Been Sold', 'Your Auction Has Ended - Item Sold!', '{{site_name}} - Auction Sold', '<div style=\"font-family: Montserrat, sans-serif;\"><p>Great news! Your listing <strong>\"{{listing_title}}\"</strong> has been sold!</p><p>Winning Bid: <strong>{{winning_bid}} {{site_currency}}</strong></p><p>Winner: {{winner_username}}</p><p>Total Bids Received: {{total_bids}}</p><p>An escrow has been created. Once the buyer completes payment, you will be notified to transfer the assets.</p><p><a href=\"{{escrow_url}}\">View Escrow Details</a></p></div>', 'Your listing \"{{listing_title}}\" sold for {{winning_bid}} {{site_currency}}! Buyer: {{winner_username}}. View escrow at {{escrow_url}}', NULL, '{\"listing_title\":\"Title of the listing\",\"winning_bid\":\"Winning bid amount\",\"winner_username\":\"Winner username\",\"total_bids\":\"Total number of bids\",\"escrow_url\":\"URL to view escrow\"}', 1, NULL, NULL, 1, NULL, 0, NULL, NULL),
(31, 'AUCTION_LOST', 'Auction Ended - You Did Not Win', 'Auction Ended', '{{site_name}} - Auction Ended', '<div style=\"font-family: Montserrat, sans-serif;\"><p>The auction for <strong>\"{{listing_title}}\"</strong> has ended.</p><p>Unfortunately, you did not win this auction.</p><p>Your Highest Bid: {{your_bid}} {{site_currency}}</p><p>Winning Bid: {{winning_bid}} {{site_currency}}</p><p>Browse our marketplace for similar listings.</p><p><a href=\"{{marketplace_url}}\">Browse Marketplace</a></p></div>', 'The auction for \"{{listing_title}}\" has ended. Winning bid was {{winning_bid}} {{site_currency}}. Browse more listings at {{marketplace_url}}', NULL, '{\"listing_title\":\"Title of the listing\",\"your_bid\":\"Your highest bid\",\"winning_bid\":\"Winning bid amount\",\"marketplace_url\":\"URL to marketplace\"}', 1, NULL, NULL, 1, NULL, 0, NULL, NULL),
(32, 'AUCTION_ENDING_SOON', 'Auction Ending Soon', 'Auction Ending Soon!', '{{site_name}} - Auction Ending Soon', '<div style=\"font-family: Montserrat, sans-serif;\"><p>The auction for <strong>\"{{listing_title}}\"</strong> is ending soon!</p><p>Time Remaining: <strong>{{time_remaining}}</strong></p><p>Current Highest Bid: {{current_bid}} {{site_currency}}</p><p>Your Status: {{your_status}}</p><p><a href=\"{{listing_url}}\">View Auction</a></p></div>', 'Auction for \"{{listing_title}}\" ends in {{time_remaining}}! Current bid: {{current_bid}} {{site_currency}}. View at {{listing_url}}', NULL, '{\"listing_title\":\"Title of the listing\",\"time_remaining\":\"Time until auction ends\",\"current_bid\":\"Current highest bid\",\"your_status\":\"User bid status\",\"listing_url\":\"URL to view the listing\"}', 1, NULL, NULL, 1, NULL, 0, NULL, NULL),
(33, 'OFFER_RECEIVED', 'New Offer Received', 'New Offer on Your Listing', '{{site_name}} - New Offer Received', '<div style=\"font-family: Montserrat, sans-serif;\"><p>You have received a new offer on your listing <strong>\"{{listing_title}}\"</strong>!</p><p>Offer Amount: <strong>{{offer_amount}} {{site_currency}}</strong></p><p>From: {{buyer_username}}</p><p>Message: {{offer_message}}</p><p>Offer Expires: {{expires_at}}</p><p>You can accept, reject, or counter this offer.</p><p><a href=\"{{offer_url}}\">Respond to Offer</a></p></div>', 'New offer of {{offer_amount}} {{site_currency}} on \"{{listing_title}}\" from {{buyer_username}}. Respond at {{offer_url}}', NULL, '{\"listing_title\":\"Title of the listing\",\"offer_amount\":\"Offer amount\",\"buyer_username\":\"Buyer username\",\"offer_message\":\"Message from buyer\",\"expires_at\":\"Offer expiration date\",\"offer_url\":\"URL to respond to offer\"}', 1, NULL, NULL, 1, NULL, 0, NULL, NULL),
(34, 'OFFER_ACCEPTED', 'Your Offer Has Been Accepted', 'Offer Accepted!', '{{site_name}} - Offer Accepted', '<div style=\"font-family: Montserrat, sans-serif;\"><p>Great news! Your offer on <strong>\"{{listing_title}}\"</strong> has been accepted!</p><p>Accepted Amount: <strong>{{offer_amount}} {{site_currency}}</strong></p><p>Seller: {{seller_username}}</p><p>An escrow has been created for this transaction. Please complete the payment to proceed with the purchase.</p><p><a href=\"{{escrow_url}}\">Complete Payment Now</a></p></div>', 'Your offer of {{offer_amount}} {{site_currency}} on \"{{listing_title}}\" was accepted! Complete payment at {{escrow_url}}', NULL, '{\"listing_title\":\"Title of the listing\",\"offer_amount\":\"Accepted offer amount\",\"seller_username\":\"Seller username\",\"escrow_url\":\"URL to complete escrow payment\"}', 1, NULL, NULL, 1, NULL, 0, NULL, NULL),
(35, 'OFFER_REJECTED', 'Your Offer Has Been Rejected', 'Offer Rejected', '{{site_name}} - Offer Rejected', '<div style=\"font-family: Montserrat, sans-serif;\"><p>Unfortunately, your offer on <strong>\"{{listing_title}}\"</strong> has been rejected.</p><p>Your Offer: {{offer_amount}} {{site_currency}}</p><p>Asking Price: {{asking_price}} {{site_currency}}</p><p>Rejection Reason: {{rejection_reason}}</p><p>You can submit a new offer or browse other listings.</p><p><a href=\"{{listing_url}}\">View Listing</a></p></div>', 'Your offer of {{offer_amount}} {{site_currency}} on \"{{listing_title}}\" was rejected. Reason: {{rejection_reason}}', NULL, '{\"listing_title\":\"Title of the listing\",\"offer_amount\":\"Your offer amount\",\"asking_price\":\"Listing asking price\",\"rejection_reason\":\"Reason for rejection\",\"listing_url\":\"URL to view the listing\"}', 1, NULL, NULL, 1, NULL, 0, NULL, NULL),
(36, 'OFFER_COUNTERED', 'Seller Has Countered Your Offer', 'Counter Offer Received', '{{site_name}} - Counter Offer', '<div style=\"font-family: Montserrat, sans-serif;\"><p>The seller has countered your offer on <strong>\"{{listing_title}}\"</strong>!</p><p>Your Original Offer: {{original_offer}} {{site_currency}}</p><p>Counter Offer: <strong>{{counter_amount}} {{site_currency}}</strong></p><p>Seller Message: {{counter_message}}</p><p>Counter Expires: {{expires_at}}</p><p>You can accept, reject, or make a new offer.</p><p><a href=\"{{offer_url}}\">Respond to Counter Offer</a></p></div>', 'Counter offer of {{counter_amount}} {{site_currency}} on \"{{listing_title}}\". Your offer was {{original_offer}} {{site_currency}}. Respond at {{offer_url}}', NULL, '{\"listing_title\":\"Title of the listing\",\"original_offer\":\"Your original offer\",\"counter_amount\":\"Counter offer amount\",\"counter_message\":\"Message from seller\",\"expires_at\":\"Counter offer expiration\",\"offer_url\":\"URL to respond\"}', 1, NULL, NULL, 1, NULL, 0, NULL, NULL),
(37, 'LISTING_SOLD_FIXED', 'Your Listing Has Been Sold', 'Your Listing Has Been Sold!', '{{site_name}} - Listing Sold', '<div style=\"font-family: Montserrat, sans-serif;\"><p>Congratulations! Your listing <strong>\"{{listing_title}}\"</strong> has been sold!</p><p>Sale Price: <strong>{{sale_price}} {{site_currency}}</strong></p><p>Buyer: {{buyer_username}}</p><p>An escrow has been created. Once the buyer completes payment, you will receive instructions to transfer the assets.</p><p><a href=\"{{escrow_url}}\">View Escrow Details</a></p></div>', 'Your listing \"{{listing_title}}\" sold for {{sale_price}} {{site_currency}}! Buyer: {{buyer_username}}. View escrow at {{escrow_url}}', NULL, '{\"listing_title\":\"Title of the listing\",\"sale_price\":\"Final sale price\",\"buyer_username\":\"Buyer username\",\"escrow_url\":\"URL to view escrow\"}', 1, NULL, NULL, 1, NULL, 0, NULL, NULL),
(38, 'WATCHLIST_PRICE_DROP', 'Price Drop on Watched Listing', 'Price Dropped on a Watched Listing', '{{site_name}} - Price Drop Alert', '<div style=\"font-family: Montserrat, sans-serif;\"><p>Good news! A listing you are watching has dropped in price!</p><p>Listing: <strong>\"{{listing_title}}\"</strong></p><p>Old Price: <s>{{old_price}} {{site_currency}}</s></p><p>New Price: <strong>{{new_price}} {{site_currency}}</strong></p><p>Savings: {{savings}} {{site_currency}} ({{discount_percent}}% off)</p><p><a href=\"{{listing_url}}\">View Listing</a></p></div>', 'Price drop alert! \"{{listing_title}}\" reduced from {{old_price}} to {{new_price}} {{site_currency}}. View at {{listing_url}}', NULL, '{\"listing_title\":\"Title of the listing\",\"old_price\":\"Previous price\",\"new_price\":\"New price\",\"savings\":\"Amount saved\",\"discount_percent\":\"Discount percentage\",\"listing_url\":\"URL to view the listing\"}', 1, NULL, NULL, 1, NULL, 0, NULL, NULL),
(39, 'NEW_QUESTION', 'New Question on Your Listing', 'Someone Asked a Question', '{{site_name}} - New Question', '<div style=\"font-family: Montserrat, sans-serif;\"><p>Someone has asked a question about your listing <strong>\"{{listing_title}}\"</strong>!</p><p>From: {{asker_username}}</p><p>Question: {{question}}</p><p>Please respond promptly to increase buyer confidence.</p><p><a href=\"{{listing_url}}\">Answer Question</a></p></div>', 'New question on \"{{listing_title}}\" from {{asker_username}}: \"{{question}}\". Answer at {{listing_url}}', NULL, '{\"listing_title\":\"Title of the listing\",\"asker_username\":\"Username who asked\",\"question\":\"The question text\",\"listing_url\":\"URL to view the listing\"}', 1, NULL, NULL, 1, NULL, 0, NULL, NULL),
(40, 'QUESTION_ANSWERED', 'Your Question Has Been Answered', 'Question Answered', '{{site_name}} - Question Answered', '<div style=\"font-family: Montserrat, sans-serif;\"><p>The seller has answered your question about <strong>\"{{listing_title}}\"</strong>!</p><p>Your Question: {{question}}</p><p>Answer: {{answer}}</p><p><a href=\"{{listing_url}}\">View Listing</a></p></div>', 'Your question on \"{{listing_title}}\" has been answered. View at {{listing_url}}', NULL, '{\"listing_title\":\"Title of the listing\",\"question\":\"Your question\",\"answer\":\"Seller answer\",\"listing_url\":\"URL to view the listing\"}', 1, NULL, NULL, 1, NULL, 0, NULL, NULL),
(41, 'NEW_REVIEW', 'New Review Received', 'You Received a New Review', '{{site_name}} - New Review', '<div style=\"font-family: Montserrat, sans-serif;\"><p>You have received a new review!</p><p>From: {{reviewer_username}}</p><p>Rating: {{rating}} / 5 stars</p><p>Review: {{review_text}}</p><p>Transaction: {{listing_title}}</p><p><a href=\"{{profile_url}}\">View Your Profile</a></p></div>', 'New {{rating}}-star review from {{reviewer_username}} for \"{{listing_title}}\". View at {{profile_url}}', NULL, '{\"reviewer_username\":\"Username who left review\",\"rating\":\"Star rating\",\"review_text\":\"Review content\",\"listing_title\":\"Related listing title\",\"profile_url\":\"URL to your profile\"}', 1, NULL, NULL, 1, NULL, 0, NULL, NULL),
(42, 'LISTING_EXPIRING', 'Your Listing is Expiring Soon', 'Listing Expiring Soon', '{{site_name}} - Listing Expiring', '<div style=\"font-family: Montserrat, sans-serif;\"><p>Your listing <strong>\"{{listing_title}}\"</strong> is expiring soon!</p><p>Expires In: {{time_remaining}}</p><p>Views: {{view_count}}</p><p>Watchers: {{watchlist_count}}</p><p>Renew or edit your listing to keep it active.</p><p><a href=\"{{listing_url}}\">Manage Listing</a></p></div>', 'Your listing \"{{listing_title}}\" expires in {{time_remaining}}. Renew at {{listing_url}}', NULL, '{\"listing_title\":\"Title of the listing\",\"time_remaining\":\"Time until expiration\",\"view_count\":\"Number of views\",\"watchlist_count\":\"Number of watchers\",\"listing_url\":\"URL to manage listing\"}', 1, NULL, NULL, 1, NULL, 0, NULL, NULL),
(43, 'BUY_NOW_PURCHASE', 'Buy Now Purchase Completed', 'Item Purchased via Buy Now', '{{site_name}} - Buy Now Purchase', '<div style=\"font-family: Montserrat, sans-serif;\"><p>Your listing <strong>\"{{listing_title}}\"</strong> has been purchased using Buy Now!</p><p>Sale Price: <strong>{{sale_price}} {{site_currency}}</strong></p><p>Buyer: {{buyer_username}}</p><p>An escrow has been created. The buyer is completing payment.</p><p><a href=\"{{escrow_url}}\">View Escrow Details</a></p></div>', 'Buy Now! \"{{listing_title}}\" purchased for {{sale_price}} {{site_currency}} by {{buyer_username}}. View escrow at {{escrow_url}}', NULL, '{\"listing_title\":\"Title of the listing\",\"sale_price\":\"Buy now price\",\"buyer_username\":\"Buyer username\",\"escrow_url\":\"URL to view escrow\"}', 1, NULL, NULL, 1, NULL, 0, NULL, NULL);
INSERT INTO `notification_templates` (`id`, `act`, `name`, `subject`, `push_title`, `email_body`, `sms_body`, `push_body`, `shortcodes`, `email_status`, `email_sent_from_name`, `email_sent_from_address`, `sms_status`, `sms_sent_from`, `push_status`, `created_at`, `updated_at`) VALUES
(44, 'ESCROW_PAYMENT_RECEIVED', 'Payment Received - Transfer Assets', 'Buyer Payment Received', '{{site_name}} - Payment Received', '<div style=\"font-family: Montserrat, sans-serif;\"><p>The buyer has completed payment for <strong>\"{{listing_title}}\"</strong>!</p><p>Amount: <strong>{{amount}} {{site_currency}}</strong></p><p>Buyer: {{buyer_username}}</p><p>Please transfer the assets to the buyer within the agreed timeframe. Once the buyer confirms receipt, the funds will be released to you.</p><p><a href=\"{{escrow_url}}\">View Escrow Details</a></p></div>', 'Payment of {{amount}} {{site_currency}} received for \"{{listing_title}}\". Please transfer assets. View escrow at {{escrow_url}}', NULL, '{\"listing_title\":\"Title of the listing\",\"amount\":\"Payment amount\",\"buyer_username\":\"Buyer username\",\"escrow_url\":\"URL to view escrow\"}', 1, NULL, NULL, 1, NULL, 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `offers`
--

CREATE TABLE `offers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `offer_number` varchar(40) NOT NULL,
  `listing_id` bigint(20) UNSIGNED NOT NULL,
  `buyer_id` bigint(20) UNSIGNED NOT NULL,
  `seller_id` bigint(20) UNSIGNED NOT NULL,
  `amount` decimal(28,8) NOT NULL,
  `message` text DEFAULT NULL,
  `status` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '0:pending, 1:accepted, 2:rejected, 3:countered, 4:expired, 5:cancelled, 6:completed',
  `counter_amount` decimal(28,8) NOT NULL DEFAULT 0.00000000,
  `counter_message` text DEFAULT NULL,
  `countered_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `responded_at` timestamp NULL DEFAULT NULL,
  `escrow_id` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pages`
--

CREATE TABLE `pages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(40) DEFAULT NULL,
  `slug` varchar(40) DEFAULT NULL,
  `tempname` varchar(40) DEFAULT NULL COMMENT 'template name',
  `secs` text DEFAULT NULL,
  `seo_content` text DEFAULT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pages`
--

INSERT INTO `pages` (`id`, `name`, `slug`, `tempname`, `secs`, `seo_content`, `is_default`, `created_at`, `updated_at`) VALUES
(1, 'HOME', '/', 'templates.basic.', '[\"marketplace_hero\",\"marketplace_stats\",\"marketplace_featured\",\"marketplace_ending\",\"marketplace_popular\",\"marketplace_new\",\"marketplace_domains\",\"marketplace_websites\",\"marketplace_apps\",\"marketplace_social\",\"marketplace_cta\",\"about\",\"feature\",\"how_works\",\"faq\",\"testimonial\",\"blog\",\"partner\",\"subscribe\"]', NULL, 1, '2020-07-11 06:23:58', '2025-12-01 10:23:23'),
(4, 'Blog', 'blogs', 'templates.basic.', NULL, NULL, 1, '2020-10-22 01:14:43', '2023-01-01 03:48:58'),
(5, 'Contact', 'contact', 'templates.basic.', NULL, NULL, 1, '2020-10-22 01:14:53', '2020-10-22 01:14:53'),
(19, 'About', 'about-us', 'templates.basic.', '[\"about\",\"partner\"]', NULL, 0, '2022-11-28 09:23:56', '2023-01-21 15:09:33');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(40) NOT NULL,
  `token` varchar(40) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `listing_id` bigint(20) UNSIGNED NOT NULL,
  `escrow_id` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `reviewer_id` bigint(20) UNSIGNED NOT NULL,
  `reviewed_user_id` bigint(20) UNSIGNED NOT NULL,
  `review_type` enum('buyer_review','seller_review') NOT NULL,
  `overall_rating` tinyint(3) UNSIGNED NOT NULL,
  `communication_rating` tinyint(3) UNSIGNED DEFAULT NULL,
  `accuracy_rating` tinyint(3) UNSIGNED DEFAULT NULL COMMENT 'As described rating',
  `timeliness_rating` tinyint(3) UNSIGNED DEFAULT NULL,
  `review` text NOT NULL,
  `seller_response` text DEFAULT NULL,
  `responded_at` timestamp NULL DEFAULT NULL,
  `status` tinyint(3) UNSIGNED NOT NULL DEFAULT 1 COMMENT '0:pending, 1:approved, 2:hidden',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `saved_searches`
--

CREATE TABLE `saved_searches` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `filters` text NOT NULL COMMENT 'JSON encoded search filters',
  `email_alerts` tinyint(1) NOT NULL DEFAULT 1,
  `alert_frequency` enum('instant','daily','weekly') NOT NULL DEFAULT 'daily',
  `last_alerted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `subscribers`
--

CREATE TABLE `subscribers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `email` varchar(40) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `support_attachments`
--

CREATE TABLE `support_attachments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `support_message_id` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `attachment` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `support_messages`
--

CREATE TABLE `support_messages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `support_ticket_id` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `admin_id` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `message` longtext NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `support_tickets`
--

CREATE TABLE `support_tickets` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(11) DEFAULT 0,
  `name` varchar(40) DEFAULT NULL,
  `email` varchar(40) DEFAULT NULL,
  `ticket` varchar(40) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `status` tinyint(1) NOT NULL COMMENT '0: Open, 1: Answered, 2: Replied, 3: Closed',
  `priority` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1 = Low, 2 = medium, 3 = heigh',
  `last_reply` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `amount` decimal(28,8) NOT NULL DEFAULT 0.00000000,
  `charge` decimal(28,8) NOT NULL DEFAULT 0.00000000,
  `post_balance` decimal(28,8) NOT NULL DEFAULT 0.00000000,
  `trx_type` varchar(40) DEFAULT NULL,
  `trx` varchar(40) DEFAULT NULL,
  `details` varchar(255) DEFAULT NULL,
  `remark` varchar(40) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `update_logs`
--

CREATE TABLE `update_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `version` varchar(40) DEFAULT NULL,
  `update_log` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `firstname` varchar(40) DEFAULT NULL,
  `lastname` varchar(40) DEFAULT NULL,
  `username` varchar(40) DEFAULT NULL,
  `email` varchar(40) NOT NULL,
  `dial_code` varchar(40) DEFAULT NULL,
  `country_code` varchar(40) DEFAULT NULL,
  `mobile` varchar(40) DEFAULT NULL,
  `ref_by` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `balance` decimal(28,8) NOT NULL DEFAULT 0.00000000,
  `password` varchar(255) NOT NULL,
  `country_name` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `state` varchar(255) DEFAULT NULL,
  `zip` varchar(255) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL COMMENT 'contains full address',
  `bio` text DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `company_name` varchar(255) DEFAULT NULL,
  `is_verified_seller` tinyint(1) NOT NULL DEFAULT 0,
  `seller_verified_at` timestamp NULL DEFAULT NULL,
  `total_listings` int(11) NOT NULL DEFAULT 0,
  `total_sales` int(11) NOT NULL DEFAULT 0,
  `total_sales_value` decimal(28,8) NOT NULL DEFAULT 0.00000000,
  `total_purchases` int(11) NOT NULL DEFAULT 0,
  `avg_rating` decimal(3,2) NOT NULL DEFAULT 0.00,
  `total_reviews` int(11) NOT NULL DEFAULT 0,
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '0: banned, 1: active',
  `kyc_data` text DEFAULT NULL,
  `kyc_rejection_reason` varchar(255) DEFAULT NULL,
  `kv` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0: KYC Unverified, 2: KYC pending, 1: KYC verified	',
  `ev` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0: email unverified, 1: email verified',
  `sv` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0: sms unverified, 1: sms verified',
  `profile_complete` tinyint(4) NOT NULL DEFAULT 0,
  `ver_code` varchar(40) DEFAULT NULL COMMENT 'stores verification code',
  `ver_code_send_at` datetime DEFAULT NULL COMMENT 'verification send time',
  `ts` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0: 2fa off, 1: 2fa on',
  `tv` tinyint(1) NOT NULL DEFAULT 1 COMMENT '0: 2fa unverified, 1: 2fa verified',
  `tsc` varchar(255) DEFAULT NULL,
  `ban_reason` varchar(255) DEFAULT NULL,
  `remember_token` varchar(255) DEFAULT NULL,
  `provider` varchar(255) DEFAULT NULL,
  `provider_id` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_logins`
--

CREATE TABLE `user_logins` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `user_ip` varchar(40) DEFAULT NULL,
  `city` varchar(40) DEFAULT NULL,
  `country` varchar(40) DEFAULT NULL,
  `country_code` varchar(40) DEFAULT NULL,
  `longitude` varchar(40) DEFAULT NULL,
  `latitude` varchar(40) DEFAULT NULL,
  `browser` varchar(40) DEFAULT NULL,
  `os` varchar(40) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `watchlist`
--

CREATE TABLE `watchlist` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `listing_id` bigint(20) UNSIGNED NOT NULL,
  `notify_bid` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Notify on new bids',
  `notify_price_change` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Notify on price changes',
  `notify_ending` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Notify when auction ending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `withdrawals`
--

CREATE TABLE `withdrawals` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `method_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `amount` decimal(28,8) NOT NULL DEFAULT 0.00000000,
  `currency` varchar(40) NOT NULL,
  `rate` decimal(28,8) NOT NULL DEFAULT 0.00000000,
  `charge` decimal(28,8) NOT NULL DEFAULT 0.00000000,
  `trx` varchar(40) NOT NULL,
  `final_amount` decimal(28,8) NOT NULL DEFAULT 0.00000000,
  `after_charge` decimal(28,8) NOT NULL DEFAULT 0.00000000,
  `withdraw_information` text DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1=>success, 2=>pending, 3=>cancel,  ',
  `admin_feedback` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `withdraw_methods`
--

CREATE TABLE `withdraw_methods` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `form_id` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `name` varchar(40) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `min_limit` decimal(28,8) DEFAULT 0.00000000,
  `max_limit` decimal(28,8) NOT NULL DEFAULT 0.00000000,
  `fixed_charge` decimal(28,8) DEFAULT 0.00000000,
  `rate` decimal(28,8) DEFAULT 0.00000000,
  `percent_charge` decimal(5,2) DEFAULT NULL,
  `currency` varchar(40) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`,`username`);

--
-- Indexes for table `admin_notifications`
--
ALTER TABLE `admin_notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `admin_password_resets`
--
ALTER TABLE `admin_password_resets`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `bids`
--
ALTER TABLE `bids`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `bid_number` (`bid_number`),
  ADD KEY `listing_id` (`listing_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `status` (`status`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `conversations`
--
ALTER TABLE `conversations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `deposits`
--
ALTER TABLE `deposits`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `device_tokens`
--
ALTER TABLE `device_tokens`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `domain_verifications`
--
ALTER TABLE `domain_verifications`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `listing_id` (`listing_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `domain` (`domain`),
  ADD KEY `verification_token` (`verification_token`);

--
-- Indexes for table `escrows`
--
ALTER TABLE `escrows`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `escrow_charges`
--
ALTER TABLE `escrow_charges`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `extensions`
--
ALTER TABLE `extensions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `forms`
--
ALTER TABLE `forms`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `frontends`
--
ALTER TABLE `frontends`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `gateways`
--
ALTER TABLE `gateways`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `gateway_currencies`
--
ALTER TABLE `gateway_currencies`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `general_settings`
--
ALTER TABLE `general_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `languages`
--
ALTER TABLE `languages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `listings`
--
ALTER TABLE `listings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `listing_number` (`listing_number`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `listing_category_id` (`listing_category_id`),
  ADD KEY `business_type` (`business_type`),
  ADD KEY `sale_type` (`sale_type`),
  ADD KEY `status` (`status`),
  ADD KEY `is_featured` (`is_featured`),
  ADD KEY `auction_end` (`auction_end`);

--
-- Indexes for table `listing_categories`
--
ALTER TABLE `listing_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `listing_images`
--
ALTER TABLE `listing_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `listing_id` (`listing_id`);

--
-- Indexes for table `listing_metrics`
--
ALTER TABLE `listing_metrics`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `listing_period` (`listing_id`,`period_date`,`period_type`),
  ADD KEY `listing_id` (`listing_id`,`period_date`);

--
-- Indexes for table `listing_questions`
--
ALTER TABLE `listing_questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `listing_id` (`listing_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `listing_views`
--
ALTER TABLE `listing_views`
  ADD PRIMARY KEY (`id`),
  ADD KEY `listing_id` (`listing_id`),
  ADD KEY `listing_created` (`listing_id`,`created_at`);

--
-- Indexes for table `marketplace_settings`
--
ALTER TABLE `marketplace_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `key` (`key`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `milestones`
--
ALTER TABLE `milestones`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notification_logs`
--
ALTER TABLE `notification_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notification_templates`
--
ALTER TABLE `notification_templates`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `offers`
--
ALTER TABLE `offers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `offer_number` (`offer_number`),
  ADD KEY `listing_id` (`listing_id`),
  ADD KEY `buyer_id` (`buyer_id`),
  ADD KEY `seller_id` (`seller_id`),
  ADD KEY `status` (`status`);

--
-- Indexes for table `pages`
--
ALTER TABLE `pages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_review` (`listing_id`,`reviewer_id`,`review_type`),
  ADD KEY `listing_id` (`listing_id`),
  ADD KEY `reviewer_id` (`reviewer_id`),
  ADD KEY `reviewed_user_id` (`reviewed_user_id`);

--
-- Indexes for table `saved_searches`
--
ALTER TABLE `saved_searches`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `subscribers`
--
ALTER TABLE `subscribers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `support_attachments`
--
ALTER TABLE `support_attachments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `support_messages`
--
ALTER TABLE `support_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `support_tickets`
--
ALTER TABLE `support_tickets`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `update_logs`
--
ALTER TABLE `update_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`,`email`);

--
-- Indexes for table `user_logins`
--
ALTER TABLE `user_logins`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `watchlist`
--
ALTER TABLE `watchlist`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_listing` (`user_id`,`listing_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `listing_id` (`listing_id`);

--
-- Indexes for table `withdrawals`
--
ALTER TABLE `withdrawals`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `withdraw_methods`
--
ALTER TABLE `withdraw_methods`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `admin_notifications`
--
ALTER TABLE `admin_notifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `admin_password_resets`
--
ALTER TABLE `admin_password_resets`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bids`
--
ALTER TABLE `bids`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `conversations`
--
ALTER TABLE `conversations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `deposits`
--
ALTER TABLE `deposits`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `device_tokens`
--
ALTER TABLE `device_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `domain_verifications`
--
ALTER TABLE `domain_verifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `escrows`
--
ALTER TABLE `escrows`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `escrow_charges`
--
ALTER TABLE `escrow_charges`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `extensions`
--
ALTER TABLE `extensions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `forms`
--
ALTER TABLE `forms`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `frontends`
--
ALTER TABLE `frontends`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=566;

--
-- AUTO_INCREMENT for table `gateways`
--
ALTER TABLE `gateways`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=163;

--
-- AUTO_INCREMENT for table `gateway_currencies`
--
ALTER TABLE `gateway_currencies`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `general_settings`
--
ALTER TABLE `general_settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `languages`
--
ALTER TABLE `languages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `listings`
--
ALTER TABLE `listings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `listing_categories`
--
ALTER TABLE `listing_categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `listing_images`
--
ALTER TABLE `listing_images`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `listing_metrics`
--
ALTER TABLE `listing_metrics`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `listing_questions`
--
ALTER TABLE `listing_questions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `listing_views`
--
ALTER TABLE `listing_views`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `marketplace_settings`
--
ALTER TABLE `marketplace_settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `milestones`
--
ALTER TABLE `milestones`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notification_logs`
--
ALTER TABLE `notification_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notification_templates`
--
ALTER TABLE `notification_templates`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `offers`
--
ALTER TABLE `offers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pages`
--
ALTER TABLE `pages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `saved_searches`
--
ALTER TABLE `saved_searches`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `subscribers`
--
ALTER TABLE `subscribers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `support_attachments`
--
ALTER TABLE `support_attachments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `support_messages`
--
ALTER TABLE `support_messages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `support_tickets`
--
ALTER TABLE `support_tickets`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `update_logs`
--
ALTER TABLE `update_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_logins`
--
ALTER TABLE `user_logins`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `watchlist`
--
ALTER TABLE `watchlist`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `withdrawals`
--
ALTER TABLE `withdrawals`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `withdraw_methods`
--
ALTER TABLE `withdraw_methods`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
