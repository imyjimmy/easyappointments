-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: mgit-repo-server_appointments_mysql_1
-- Generation Time: Aug 11, 2025 at 02:28 AM
-- Server version: 8.0.43
-- PHP Version: 8.2.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `easyappointments`
--

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `id` int NOT NULL,
  `create_datetime` datetime DEFAULT NULL,
  `update_datetime` datetime DEFAULT NULL,
  `book_datetime` datetime DEFAULT NULL,
  `start_datetime` datetime DEFAULT NULL,
  `end_datetime` datetime DEFAULT NULL,
  `location` text COLLATE utf8mb4_unicode_ci,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `hash` text COLLATE utf8mb4_unicode_ci,
  `color` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT '#7cbae8',
  `status` varchar(512) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `is_unavailability` tinyint NOT NULL DEFAULT '0',
  `id_users_provider` int DEFAULT NULL,
  `id_users_customer` int DEFAULT NULL,
  `id_services` int DEFAULT NULL,
  `id_google_calendar` text COLLATE utf8mb4_unicode_ci,
  `id_caldav_calendar` text COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `blocked_periods`
--

CREATE TABLE `blocked_periods` (
  `id` int NOT NULL,
  `create_datetime` datetime DEFAULT NULL,
  `update_datetime` datetime DEFAULT NULL,
  `name` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `start_datetime` datetime DEFAULT NULL,
  `end_datetime` datetime DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `consents`
--

CREATE TABLE `consents` (
  `id` int NOT NULL,
  `create_datetime` datetime DEFAULT NULL,
  `update_datetime` datetime DEFAULT NULL,
  `created` timestamp NULL DEFAULT NULL,
  `modified` timestamp NULL DEFAULT NULL,
  `first_name` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_name` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(512) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ip` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `version` bigint NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`version`) VALUES
(62);

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int NOT NULL,
  `create_datetime` datetime DEFAULT NULL,
  `update_datetime` datetime DEFAULT NULL,
  `name` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `slug` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_admin` tinyint DEFAULT NULL,
  `appointments` int DEFAULT NULL,
  `customers` int DEFAULT NULL,
  `services` int DEFAULT NULL,
  `users` int DEFAULT NULL,
  `system_settings` int DEFAULT NULL,
  `user_settings` int DEFAULT NULL,
  `webhooks` int DEFAULT NULL,
  `blocked_periods` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `create_datetime`, `update_datetime`, `name`, `slug`, `is_admin`, `appointments`, `customers`, `services`, `users`, `system_settings`, `user_settings`, `webhooks`, `blocked_periods`) VALUES
(1, NULL, NULL, 'Administrator', 'admin', 1, 15, 15, 15, 15, 15, 15, 15, 15),
(2, NULL, NULL, 'Provider', 'provider', 0, 15, 15, 0, 0, 0, 15, 0, 0),
(3, NULL, NULL, 'Customer', 'customer', 0, 0, 0, 0, 0, 0, 0, 0, 0),
(4, NULL, NULL, 'Secretary', 'secretary', 0, 15, 15, 0, 0, 0, 15, 0, 0),
(5, NULL, NULL, 'Admin Provider', 'admin-provider', 1, 15, 15, 15, 15, 15, 15, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `secretaries_providers`
--

CREATE TABLE `secretaries_providers` (
  `id_users_secretary` int NOT NULL,
  `id_users_provider` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `id` int NOT NULL,
  `create_datetime` datetime DEFAULT NULL,
  `update_datetime` datetime DEFAULT NULL,
  `name` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `duration` int DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `currency` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `color` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT '#7cbae8',
  `location` text COLLATE utf8mb4_unicode_ci,
  `availabilities_type` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT 'flexible',
  `attendants_number` int DEFAULT '1',
  `is_private` tinyint DEFAULT '0',
  `id_service_categories` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id`, `create_datetime`, `update_datetime`, `name`, `duration`, `price`, `currency`, `description`, `color`, `location`, `availabilities_type`, `attendants_number`, `is_private`, `id_service_categories`) VALUES
(1, '2025-08-11 02:26:57', '2025-08-11 02:26:57', 'Service', 30, 0.00, '', NULL, '#7cbae8', NULL, 'flexible', 1, 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `services_providers`
--

CREATE TABLE `services_providers` (
  `id_users` int NOT NULL,
  `id_services` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `service_categories`
--

CREATE TABLE `service_categories` (
  `id` int NOT NULL,
  `create_datetime` datetime DEFAULT NULL,
  `update_datetime` datetime DEFAULT NULL,
  `name` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int NOT NULL,
  `create_datetime` datetime DEFAULT NULL,
  `update_datetime` datetime DEFAULT NULL,
  `name` varchar(512) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `value` longtext COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `create_datetime`, `update_datetime`, `name`, `value`) VALUES
(1, NULL, NULL, 'company_working_plan', '{\"monday\":{\"start\":\"09:00\",\"end\":\"18:00\",\"breaks\":[{\"start\":\"14:30\",\"end\":\"15:00\"}]},\"tuesday\":{\"start\":\"09:00\",\"end\":\"18:00\",\"breaks\":[{\"start\":\"14:30\",\"end\":\"15:00\"}]},\"wednesday\":{\"start\":\"09:00\",\"end\":\"18:00\",\"breaks\":[{\"start\":\"14:30\",\"end\":\"15:00\"}]},\"thursday\":{\"start\":\"09:00\",\"end\":\"18:00\",\"breaks\":[{\"start\":\"14:30\",\"end\":\"15:00\"}]},\"friday\":{\"start\":\"09:00\",\"end\":\"18:00\",\"breaks\":[{\"start\":\"14:30\",\"end\":\"15:00\"}]},\"saturday\":{\"start\":\"09:00\",\"end\":\"18:00\",\"breaks\":[{\"start\":\"14:30\",\"end\":\"15:00\"}]},\"sunday\":{\"start\":\"09:00\",\"end\":\"18:00\",\"breaks\":[{\"start\":\"14:30\",\"end\":\"15:00\"}]}}'),
(2, NULL, NULL, 'book_advance_timeout', '30'),
(3, NULL, NULL, 'google_analytics_code', ''),
(4, NULL, NULL, 'customer_notifications', '1'),
(5, NULL, NULL, 'date_format', 'DMY'),
(6, NULL, NULL, 'require_captcha', '0'),
(7, NULL, NULL, 'time_format', 'regular'),
(8, NULL, NULL, 'display_cookie_notice', '0'),
(9, NULL, NULL, 'cookie_notice_content', 'Cookie notice content.'),
(10, NULL, NULL, 'display_terms_and_conditions', '0'),
(11, NULL, NULL, 'terms_and_conditions_content', 'Terms and conditions content.'),
(12, NULL, NULL, 'display_privacy_policy', '0'),
(13, NULL, NULL, 'privacy_policy_content', 'Privacy policy content.'),
(14, NULL, NULL, 'first_weekday', 'sunday'),
(16, NULL, NULL, 'api_token', ''),
(17, NULL, NULL, 'display_any_provider', '1'),
(18, NULL, NULL, 'display_first_name', '1'),
(19, NULL, NULL, 'require_first_name', '1'),
(20, NULL, NULL, 'display_last_name', '1'),
(21, NULL, NULL, 'require_last_name', '1'),
(22, NULL, NULL, 'display_email', '1'),
(23, NULL, NULL, 'require_email', '1'),
(24, NULL, NULL, 'display_phone_number', '1'),
(25, NULL, NULL, 'require_phone_number', '1'),
(26, NULL, NULL, 'display_address', '1'),
(27, NULL, NULL, 'require_address', '0'),
(28, NULL, NULL, 'display_city', '1'),
(29, NULL, NULL, 'require_city', '0'),
(30, NULL, NULL, 'display_zip_code', '1'),
(31, NULL, NULL, 'require_zip_code', '0'),
(32, NULL, NULL, 'display_notes', '1'),
(33, NULL, NULL, 'require_notes', '0'),
(34, NULL, NULL, 'matomo_analytics_url', ''),
(35, NULL, NULL, 'display_delete_personal_information', '0'),
(36, NULL, NULL, 'disable_booking', '0'),
(37, NULL, NULL, 'disable_booking_message', '<p style=\"text-align: center;\">Thanks for stopping by!</p><p style=\"text-align: center;\">We are not accepting new appointments at the moment, please check back again later.</p>'),
(38, NULL, NULL, 'company_logo', ''),
(39, NULL, NULL, 'company_color', '#ffffff'),
(40, NULL, NULL, 'display_login_button', '1'),
(41, NULL, NULL, 'theme', 'default'),
(42, '2025-08-11 02:26:57', '2025-08-11 02:26:57', 'limit_customer_access', '0'),
(43, NULL, NULL, 'future_booking_limit', '90'),
(44, NULL, NULL, 'appointment_status_options', '[\"Booked\", \"Confirmed\", \"Rescheduled\", \"Cancelled\", \"Draft\"]'),
(45, NULL, NULL, 'display_custom_field_1', '0'),
(46, NULL, NULL, 'require_custom_field_1', '0'),
(47, NULL, NULL, 'label_custom_field_1', ''),
(48, NULL, NULL, 'display_custom_field_2', '0'),
(49, NULL, NULL, 'require_custom_field_2', '0'),
(50, NULL, NULL, 'label_custom_field_2', ''),
(51, NULL, NULL, 'display_custom_field_3', '0'),
(52, NULL, NULL, 'require_custom_field_3', '0'),
(53, NULL, NULL, 'label_custom_field_3', ''),
(54, NULL, NULL, 'display_custom_field_4', '0'),
(55, NULL, NULL, 'require_custom_field_4', '0'),
(56, NULL, NULL, 'label_custom_field_4', ''),
(57, NULL, NULL, 'display_custom_field_5', '0'),
(58, NULL, NULL, 'require_custom_field_5', '0'),
(59, NULL, NULL, 'label_custom_field_5', ''),
(60, NULL, NULL, 'matomo_analytics_site_id', '1'),
(61, NULL, NULL, 'default_language', 'english'),
(62, NULL, NULL, 'default_timezone', 'UTC'),
(63, '2025-08-11 02:26:57', '2025-08-11 02:26:57', 'ldap_is_active', '0'),
(64, '2025-08-11 02:26:57', '2025-08-11 02:26:57', 'ldap_host', ''),
(65, '2025-08-11 02:26:57', '2025-08-11 02:26:57', 'ldap_port', ''),
(66, '2025-08-11 02:26:57', '2025-08-11 02:26:57', 'ldap_user_dn', ''),
(67, '2025-08-11 02:26:57', '2025-08-11 02:26:57', 'ldap_password', ''),
(68, '2025-08-11 02:26:57', '2025-08-11 02:26:57', 'ldap_base_dn', ''),
(69, '2025-08-11 02:26:57', '2025-08-11 02:26:57', 'ldap_filter', '(&(objectClass=*)(|(cn={{KEYWORD}})(sn={{KEYWORD}})(mail={{KEYWORD}})(givenName={{KEYWORD}})(uid={{KEYWORD}})))'),
(70, '2025-08-11 02:26:57', '2025-08-11 02:26:57', 'ldap_field_mapping', '{\n    \"first_name\": \"givenname\",\n    \"last_name\": \"sn\",\n    \"email\": \"mail\",\n    \"phone_number\": \"telephonenumber\",\n    \"username\": \"cn\"\n}'),
(71, '2025-08-11 02:26:57', '2025-08-11 02:26:57', 'company_name', 'plebemr'),
(72, '2025-08-11 02:26:57', '2025-08-11 02:26:57', 'company_email', 'imyjimmy@gmail.com'),
(73, '2025-08-11 02:26:57', '2025-08-11 02:26:57', 'company_link', 'https://plebemr.com');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `create_datetime` datetime DEFAULT NULL,
  `update_datetime` datetime DEFAULT NULL,
  `first_name` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_name` varchar(512) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(512) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mobile_number` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone_number` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `state` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `zip_code` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `timezone` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT 'UTC',
  `language` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT 'english',
  `is_private` tinyint DEFAULT '0',
  `ldap_dn` text COLLATE utf8mb4_unicode_ci,
  `id_roles` int DEFAULT NULL,
  `nostr_pubkey` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `create_datetime`, `update_datetime`, `first_name`, `last_name`, `email`, `mobile_number`, `phone_number`, `address`, `city`, `state`, `zip_code`, `notes`, `timezone`, `language`, `is_private`, `ldap_dn`, `id_roles`, `nostr_pubkey`) VALUES
(1, '2025-08-11 02:26:57', '2025-08-11 02:26:57', 'Jimmy', 'Zhang', 'imyjimmy@gmail.com', NULL, '2532257825', NULL, NULL, NULL, NULL, NULL, 'UTC', 'english', 0, NULL, 5, NULL),
(2, '2025-08-11 02:26:57', '2025-08-11 02:26:57', 'James', 'Doe', 'james@example.org', NULL, '+1 (000) 000-0000', NULL, NULL, NULL, NULL, NULL, 'UTC', 'english', 0, NULL, 3, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_settings`
--

CREATE TABLE `user_settings` (
  `id_users` int NOT NULL,
  `username` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(512) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `salt` varchar(512) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `working_plan` text COLLATE utf8mb4_unicode_ci,
  `working_plan_exceptions` text COLLATE utf8mb4_unicode_ci,
  `notifications` tinyint DEFAULT NULL,
  `google_sync` tinyint DEFAULT NULL,
  `google_token` text COLLATE utf8mb4_unicode_ci,
  `google_calendar` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `caldav_sync` tinyint DEFAULT '0',
  `caldav_url` varchar(512) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `caldav_username` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `caldav_password` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sync_past_days` int DEFAULT '30',
  `sync_future_days` int DEFAULT '90',
  `calendar_view` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT 'default'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_settings`
--

INSERT INTO `user_settings` (`id_users`, `username`, `password`, `salt`, `working_plan`, `working_plan_exceptions`, `notifications`, `google_sync`, `google_token`, `google_calendar`, `caldav_sync`, `caldav_url`, `caldav_username`, `caldav_password`, `sync_past_days`, `sync_future_days`, `calendar_view`) VALUES
(1, 'imyjimmy', 'b7d7afe67a8fd478b28e112bbdb804512e9d932125b886c3ff9c296b93e49e14', '9f385f4a82085cd5a6cd6e14294074faae10a390c8bc40c4998d5f2d2d1f8535', NULL, NULL, 1, NULL, NULL, NULL, 0, NULL, NULL, NULL, 30, 90, 'default');

-- --------------------------------------------------------

--
-- Table structure for table `webhooks`
--

CREATE TABLE `webhooks` (
  `id` int NOT NULL,
  `create_datetime` datetime DEFAULT NULL,
  `update_datetime` datetime DEFAULT NULL,
  `name` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `url` text COLLATE utf8mb4_unicode_ci,
  `actions` text COLLATE utf8mb4_unicode_ci,
  `secret_header` varchar(256) COLLATE utf8mb4_unicode_ci DEFAULT 'X-Ea-Token',
  `secret_token` varchar(512) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_ssl_verified` tinyint NOT NULL DEFAULT '1',
  `notes` text COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_users_provider` (`id_users_provider`),
  ADD KEY `id_users_customer` (`id_users_customer`),
  ADD KEY `id_services` (`id_services`);

--
-- Indexes for table `blocked_periods`
--
ALTER TABLE `blocked_periods`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `consents`
--
ALTER TABLE `consents`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `secretaries_providers`
--
ALTER TABLE `secretaries_providers`
  ADD PRIMARY KEY (`id_users_secretary`,`id_users_provider`),
  ADD KEY `secretaries_users_provider` (`id_users_provider`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_service_categories` (`id_service_categories`);

--
-- Indexes for table `services_providers`
--
ALTER TABLE `services_providers`
  ADD PRIMARY KEY (`id_users`,`id_services`),
  ADD KEY `services_providers_services` (`id_services`);

--
-- Indexes for table `service_categories`
--
ALTER TABLE `service_categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_nostr_pubkey` (`nostr_pubkey`),
  ADD KEY `id_roles` (`id_roles`),
  ADD KEY `idx_nostr_pubkey` (`nostr_pubkey`);

--
-- Indexes for table `user_settings`
--
ALTER TABLE `user_settings`
  ADD PRIMARY KEY (`id_users`);

--
-- Indexes for table `webhooks`
--
ALTER TABLE `webhooks`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `blocked_periods`
--
ALTER TABLE `blocked_periods`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `consents`
--
ALTER TABLE `consents`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `service_categories`
--
ALTER TABLE `service_categories`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=74;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `webhooks`
--
ALTER TABLE `webhooks`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_services` FOREIGN KEY (`id_services`) REFERENCES `services` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `appointments_users_customer` FOREIGN KEY (`id_users_customer`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `appointments_users_provider` FOREIGN KEY (`id_users_provider`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `secretaries_providers`
--
ALTER TABLE `secretaries_providers`
  ADD CONSTRAINT `secretaries_users_provider` FOREIGN KEY (`id_users_provider`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `secretaries_users_secretary` FOREIGN KEY (`id_users_secretary`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `services`
--
ALTER TABLE `services`
  ADD CONSTRAINT `services_service_categories` FOREIGN KEY (`id_service_categories`) REFERENCES `service_categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `services_providers`
--
ALTER TABLE `services_providers`
  ADD CONSTRAINT `services_providers_services` FOREIGN KEY (`id_services`) REFERENCES `services` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `services_providers_users_provider` FOREIGN KEY (`id_users`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_roles` FOREIGN KEY (`id_roles`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `user_settings`
--
ALTER TABLE `user_settings`
  ADD CONSTRAINT `user_settings_users` FOREIGN KEY (`id_users`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
