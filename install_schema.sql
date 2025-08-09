-- Easy!Appointments Final Database Schema
-- Generated from all migrations combined (001-050+)
-- This creates the database in its final state without running migrations

SET FOREIGN_KEY_CHECKS = 0;

-- =============================================
-- Table: roles
-- =============================================
CREATE TABLE IF NOT EXISTS `roles` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `create_datetime` datetime DEFAULT NULL,
  `update_datetime` datetime DEFAULT NULL,
  `name` varchar(256) DEFAULT NULL,
  `slug` varchar(256) DEFAULT NULL,
  `is_admin` tinyint(4) DEFAULT NULL,
  `appointments` bigint(20) DEFAULT NULL,
  `customers` bigint(20) DEFAULT NULL,
  `services` bigint(20) DEFAULT NULL,
  `users` bigint(20) DEFAULT NULL,
  `system_settings` bigint(20) DEFAULT NULL,
  `user_settings` bigint(20) DEFAULT NULL,
  `blocked_periods` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- =============================================
-- Table: service_categories  
-- =============================================
CREATE TABLE IF NOT EXISTS `service_categories` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `create_datetime` datetime DEFAULT NULL,
  `update_datetime` datetime DEFAULT NULL,
  `name` varchar(256) DEFAULT NULL,
  `description` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- =============================================
-- Table: services
-- =============================================
CREATE TABLE IF NOT EXISTS `services` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `create_datetime` datetime DEFAULT NULL,
  `update_datetime` datetime DEFAULT NULL,
  `name` varchar(256) DEFAULT NULL,
  `duration` int(11) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `currency` varchar(32) DEFAULT NULL,
  `description` text,
  `location` text,
  `color` varchar(256) DEFAULT '#3fbd5e',
  `availabilities_type` varchar(32) DEFAULT 'flexible',
  `attendants_number` int(11) DEFAULT '1',
  `id_service_categories` bigint(20) UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_service_categories` (`id_service_categories`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- =============================================
-- Table: users
-- =============================================
CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `create_datetime` datetime DEFAULT NULL,
  `update_datetime` datetime DEFAULT NULL,
  `first_name` varchar(256) DEFAULT NULL,
  `last_name` varchar(256) DEFAULT NULL,
  `email` varchar(512) DEFAULT NULL,
  `mobile_number` varchar(128) DEFAULT NULL,
  `phone_number` varchar(128) DEFAULT NULL,
  `address` text,
  `city` varchar(256) DEFAULT NULL,
  `state` varchar(128) DEFAULT NULL,
  `zip_code` varchar(64) DEFAULT NULL,
  `notes` text,
  `timezone` varchar(256) DEFAULT 'UTC',
  `language` varchar(256) DEFAULT 'english',
  `nostr_pubkey` varchar(255) DEFAULT NULL,
  `id_roles` bigint(20) UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_roles` (`id_roles`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `users` ADD UNIQUE KEY `unique_nostr_pubkey` (`nostr_pubkey`);
CREATE INDEX `idx_nostr_pubkey` ON `users`(`nostr_pubkey`);

-- =============================================
-- Table: user_settings
-- =============================================
CREATE TABLE IF NOT EXISTS `user_settings` (
  `id_users` bigint(20) UNSIGNED NOT NULL,
  `username` varchar(256) DEFAULT NULL,
  `password` varchar(512) DEFAULT NULL,
  `salt` varchar(512) DEFAULT NULL,
  `working_plan` text,
  `working_plan_exceptions` text DEFAULT '{}',
  `notifications` tinyint(4) DEFAULT NULL,
  `google_sync` tinyint(4) DEFAULT NULL,
  `google_token` text,
  `google_calendar` varchar(128) DEFAULT NULL,
  `sync_past_days` int(11) DEFAULT '5',
  `sync_future_days` int(11) DEFAULT '5',
  `caldav_sync` tinyint(4) DEFAULT '0',
  `caldav_url` varchar(512) DEFAULT NULL,
  `caldav_username` varchar(256) DEFAULT NULL,
  `caldav_password` varchar(512) DEFAULT NULL,
  `caldav_calendar` varchar(512) DEFAULT NULL,
  `calendar_view` varchar(32) DEFAULT 'default',
  PRIMARY KEY (`id_users`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- =============================================
-- Table: appointments
-- =============================================
CREATE TABLE IF NOT EXISTS `appointments` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `create_datetime` datetime DEFAULT NULL,
  `update_datetime` datetime DEFAULT NULL,
  `book_datetime` datetime DEFAULT NULL,
  `start_datetime` datetime DEFAULT NULL,
  `end_datetime` datetime DEFAULT NULL,
  `notes` text,
  `hash` text,
  `is_unavailability` tinyint(4) DEFAULT '0',
  `id_users_provider` bigint(20) UNSIGNED DEFAULT NULL,
  `id_users_customer` bigint(20) UNSIGNED DEFAULT NULL,
  `id_services` bigint(20) UNSIGNED DEFAULT NULL,
  `id_google_calendar` text,
  `id_caldav_calendar` text,
  `location` text,
  `color` varchar(256) DEFAULT '#3fbd5e',
  `status` varchar(512) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_users_provider` (`id_users_provider`),
  KEY `id_users_customer` (`id_users_customer`),
  KEY `id_services` (`id_services`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- =============================================
-- Table: secretaries_providers
-- =============================================
CREATE TABLE IF NOT EXISTS `secretaries_providers` (
  `id_users_secretary` bigint(20) UNSIGNED NOT NULL,
  `id_users_provider` bigint(20) UNSIGNED NOT NULL,
  PRIMARY KEY (`id_users_secretary`,`id_users_provider`),
  KEY `id_users_secretary` (`id_users_secretary`),
  KEY `id_users_provider` (`id_users_provider`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- =============================================
-- Table: services_providers
-- =============================================
CREATE TABLE IF NOT EXISTS `services_providers` (
  `id_users` bigint(20) UNSIGNED NOT NULL,
  `id_services` bigint(20) UNSIGNED NOT NULL,
  PRIMARY KEY (`id_users`,`id_services`),
  KEY `id_users` (`id_users`),
  KEY `id_services` (`id_services`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- =============================================
-- Table: settings
-- =============================================
CREATE TABLE IF NOT EXISTS `settings` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `create_datetime` datetime DEFAULT NULL,
  `update_datetime` datetime DEFAULT NULL,
  `name` varchar(512) DEFAULT NULL,
  `value` longtext,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- =============================================
-- Table: consents
-- =============================================
CREATE TABLE IF NOT EXISTS `consents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `create_datetime` datetime DEFAULT NULL,
  `update_datetime` datetime DEFAULT NULL,
  `created` timestamp NULL DEFAULT NULL,
  `modified` timestamp NULL DEFAULT NULL,
  `first_name` varchar(256) DEFAULT NULL,
  `last_name` varchar(256) DEFAULT NULL,
  `email` varchar(512) DEFAULT NULL,
  `ip` varchar(256) DEFAULT NULL,
  `type` varchar(256) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- =============================================
-- Table: webhooks
-- =============================================
CREATE TABLE IF NOT EXISTS `webhooks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `create_datetime` datetime DEFAULT NULL,
  `update_datetime` datetime DEFAULT NULL,
  `name` varchar(256) DEFAULT NULL,
  `url` text,
  `actions` text,
  `secret_token` varchar(512) DEFAULT NULL,
  `is_ssl_verified` tinyint(4) DEFAULT '1',
  `notes` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- =============================================
-- Table: blocked_periods
-- =============================================
CREATE TABLE IF NOT EXISTS `blocked_periods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `create_datetime` datetime DEFAULT NULL,
  `update_datetime` datetime DEFAULT NULL,
  `name` varchar(256) DEFAULT NULL,
  `start_datetime` datetime DEFAULT NULL,
  `end_datetime` datetime DEFAULT NULL,
  `notes` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- =============================================
-- Foreign Key Constraints (Final Version from Migration 011)
-- These use the clean naming convention without prefixes
-- =============================================

-- Appointments foreign keys
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_users_customer` FOREIGN KEY (`id_users_customer`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `appointments_services` FOREIGN KEY (`id_services`) REFERENCES `services` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `appointments_users_provider` FOREIGN KEY (`id_users_provider`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- Secretaries-Providers foreign keys  
ALTER TABLE `secretaries_providers`
  ADD CONSTRAINT `secretaries_users_secretary` FOREIGN KEY (`id_users_secretary`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `secretaries_users_provider` FOREIGN KEY (`id_users_provider`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- Services foreign keys
ALTER TABLE `services`
  ADD CONSTRAINT `services_service_categories` FOREIGN KEY (`id_service_categories`) REFERENCES `service_categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- Services-Providers foreign keys
ALTER TABLE `services_providers`
  ADD CONSTRAINT `services_providers_users_provider` FOREIGN KEY (`id_users`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `services_providers_services` FOREIGN KEY (`id_services`) REFERENCES `services` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- Users foreign keys
ALTER TABLE `users`
  ADD CONSTRAINT `users_roles` FOREIGN KEY (`id_roles`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- User Settings foreign keys
ALTER TABLE `user_settings`
  ADD CONSTRAINT `user_settings_users` FOREIGN KEY (`id_users`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- =============================================
-- Insert admin-provider role for solo practices
-- =============================================
INSERT INTO `roles` (`name`, `slug`, `is_admin`, `appointments`, `customers`, `services`, `users`, `system_settings`, `user_settings`) 
VALUES ('Admin Provider', 'admin-provider', 1, 15, 15, 15, 15, 15, 15);

SET FOREIGN_KEY_CHECKS = 1;

-- =============================================
-- Set migration version to latest to prevent migration system from running
-- =============================================
-- This tells the migration system that we're already at the latest version
INSERT INTO `settings` (`name`, `value`) VALUES ('migration_version', '50') ON DUPLICATE KEY UPDATE `value` = '50';