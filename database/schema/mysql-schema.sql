/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `appointments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `appointments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `blood_center_id` bigint unsigned NOT NULL,
  `scheduled_at` datetime NOT NULL,
  `status` enum('pending','confirmed','completed','cancelled') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `confirmed_at` timestamp NULL DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `rescheduled_at` timestamp NULL DEFAULT NULL,
  `handled_by` bigint unsigned DEFAULT NULL,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `appointments_user_id_foreign` (`user_id`),
  KEY `appointments_blood_center_id_foreign` (`blood_center_id`),
  KEY `appointments_handled_by_foreign` (`handled_by`),
  CONSTRAINT `appointments_blood_center_id_foreign` FOREIGN KEY (`blood_center_id`) REFERENCES `blood_centers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `appointments_handled_by_foreign` FOREIGN KEY (`handled_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `appointments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `articles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `articles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `summary` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `body` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `author_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `source_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `source_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `attachment_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `attachment_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `attachment_mime` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_featured` tinyint(1) NOT NULL DEFAULT '0',
  `reading_time_minutes` smallint unsigned NOT NULL DEFAULT '1',
  `meta_description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `status` enum('draft','published','archived') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `published_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `articles_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `audit_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `actor_id` bigint unsigned DEFAULT NULL,
  `blood_center_id` bigint unsigned DEFAULT NULL,
  `action` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject_id` bigint unsigned DEFAULT NULL,
  `metadata` json DEFAULT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `occurred_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `previous_hash` char(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `record_hash` char(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `audit_logs_record_hash_unique` (`record_hash`),
  KEY `audit_logs_actor_id_foreign` (`actor_id`),
  KEY `audit_logs_blood_center_id_foreign` (`blood_center_id`),
  KEY `audit_logs_subject_type_subject_id_index` (`subject_type`,`subject_id`),
  KEY `audit_logs_action_index` (`action`),
  KEY `audit_logs_occurred_at_index` (`occurred_at`),
  CONSTRAINT `audit_logs_actor_id_foreign` FOREIGN KEY (`actor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `audit_logs_blood_center_id_foreign` FOREIGN KEY (`blood_center_id`) REFERENCES `blood_centers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `badges`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `badges` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `icon` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `donation_threshold` int unsigned NOT NULL DEFAULT '1',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `badges_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `blood_centers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `blood_centers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `city` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `opening_hours` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `services` json DEFAULT NULL,
  `capacity_label` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estimated_wait_minutes` smallint unsigned DEFAULT NULL,
  `center_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `blood_centers_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `blood_inventory`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `blood_inventory` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `blood_center_id` bigint unsigned NOT NULL,
  `blood_group` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `available_units` int unsigned NOT NULL DEFAULT '0',
  `reserved_units` int unsigned NOT NULL DEFAULT '0',
  `minimum_threshold` int unsigned NOT NULL DEFAULT '5',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `blood_inventory_blood_center_id_blood_group_unique` (`blood_center_id`,`blood_group`),
  CONSTRAINT `blood_inventory_blood_center_id_foreign` FOREIGN KEY (`blood_center_id`) REFERENCES `blood_centers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `blood_units`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `blood_units` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `unit_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `donation_id` bigint unsigned NOT NULL,
  `donor_id` bigint unsigned NOT NULL,
  `blood_center_id` bigint unsigned NOT NULL,
  `blood_group` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `collection_date` date NOT NULL,
  `expiry_date` date NOT NULL,
  `status` enum('collected','testing','available','reserved','transferred','used','rejected','expired','discarded') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'collected',
  `current_location` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `handled_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `blood_units_unit_number_unique` (`unit_number`),
  UNIQUE KEY `blood_units_donation_id_unique` (`donation_id`),
  KEY `blood_units_donation_id_foreign` (`donation_id`),
  KEY `blood_units_donor_id_foreign` (`donor_id`),
  KEY `blood_units_blood_center_id_foreign` (`blood_center_id`),
  KEY `blood_units_handled_by_foreign` (`handled_by`),
  CONSTRAINT `blood_units_blood_center_id_foreign` FOREIGN KEY (`blood_center_id`) REFERENCES `blood_centers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `blood_units_donation_id_foreign` FOREIGN KEY (`donation_id`) REFERENCES `donations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `blood_units_donor_id_foreign` FOREIGN KEY (`donor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `blood_units_handled_by_foreign` FOREIGN KEY (`handled_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `campaigns`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `campaigns` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `blood_center_id` bigint unsigned NOT NULL,
  `location` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('upcoming','ongoing','completed','cancelled') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'upcoming',
  `campaign_type` enum('standard','emergency') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'standard',
  `target_blood_group` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `low_stock_alert_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `campaigns_blood_center_id_foreign` (`blood_center_id`),
  KEY `campaigns_low_stock_alert_id_foreign` (`low_stock_alert_id`),
  CONSTRAINT `campaigns_blood_center_id_foreign` FOREIGN KEY (`blood_center_id`) REFERENCES `blood_centers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `campaigns_low_stock_alert_id_foreign` FOREIGN KEY (`low_stock_alert_id`) REFERENCES `low_stock_alerts` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `center_staff`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `center_staff` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `blood_center_id` bigint unsigned NOT NULL,
  `position` enum('center_manager','center_staff') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'center_staff',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `center_staff_user_id_blood_center_id_unique` (`user_id`,`blood_center_id`),
  KEY `center_staff_blood_center_id_foreign` (`blood_center_id`),
  CONSTRAINT `center_staff_blood_center_id_foreign` FOREIGN KEY (`blood_center_id`) REFERENCES `blood_centers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `center_staff_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `deferrals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `deferrals` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `type` enum('temporary','permanent') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `reason` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `starts_at` date NOT NULL,
  `ends_at` date DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `lifted_at` timestamp NULL DEFAULT NULL,
  `lifted_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `deferrals_user_id_foreign` (`user_id`),
  KEY `deferrals_created_by_foreign` (`created_by`),
  KEY `deferrals_lifted_by_foreign` (`lifted_by`),
  CONSTRAINT `deferrals_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `deferrals_lifted_by_foreign` FOREIGN KEY (`lifted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `deferrals_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `donations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `donations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `blood_center_id` bigint unsigned NOT NULL,
  `recorded_by` bigint unsigned DEFAULT NULL,
  `appointment_id` bigint unsigned DEFAULT NULL,
  `donation_type` enum('appointment','walk_in') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'appointment',
  `blood_group` enum('A+','A-','B+','B-','AB+','AB-','O+','O-') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `blood_group_verified` tinyint(1) NOT NULL DEFAULT '0',
  `volume_ml` int NOT NULL,
  `donation_date` date NOT NULL,
  `status` enum('completed','failed') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'completed',
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `donations_appointment_id_unique` (`appointment_id`),
  KEY `donations_user_id_foreign` (`user_id`),
  KEY `donations_blood_center_id_foreign` (`blood_center_id`),
  KEY `donations_appointment_id_foreign` (`appointment_id`),
  KEY `donations_recorded_by_foreign` (`recorded_by`),
  CONSTRAINT `donations_appointment_id_foreign` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `donations_blood_center_id_foreign` FOREIGN KEY (`blood_center_id`) REFERENCES `blood_centers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `donations_recorded_by_foreign` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `donations_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `donor_badges`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `donor_badges` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `badge_id` bigint unsigned NOT NULL,
  `awarded_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `donor_badges_user_id_badge_id_unique` (`user_id`,`badge_id`),
  KEY `donor_badges_badge_id_foreign` (`badge_id`),
  CONSTRAINT `donor_badges_badge_id_foreign` FOREIGN KEY (`badge_id`) REFERENCES `badges` (`id`) ON DELETE CASCADE,
  CONSTRAINT `donor_badges_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `donor_profiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `donor_profiles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `preferred_center_id` bigint unsigned DEFAULT NULL,
  `donor_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `blood_group_status` enum('unknown','user_selected','staff_verified') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'unknown',
  `blood_group_verified` tinyint(1) NOT NULL DEFAULT '0',
  `blood_group_verified_at` timestamp NULL DEFAULT NULL,
  `blood_group_verified_by` bigint unsigned DEFAULT NULL,
  `next_eligible_donation_date` date DEFAULT NULL,
  `eligibility_status` enum('eligible','not_yet_eligible','temporarily_deferred','permanently_deferred') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'eligible',
  `last_eligibility_checked_at` timestamp NULL DEFAULT NULL,
  `eligibility_notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `emergency_contact_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `emergency_contact_phone` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `push_notifications_enabled` tinyint(1) NOT NULL DEFAULT '1',
  `sms_reminders_enabled` tinyint(1) NOT NULL DEFAULT '1',
  `share_anonymized_data` tinyint(1) NOT NULL DEFAULT '0',
  `language` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'English',
  `total_donations` int unsigned NOT NULL DEFAULT '0',
  `loyalty_points` int unsigned NOT NULL DEFAULT '0',
  `loyalty_tier` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `donor_profiles_user_id_unique` (`user_id`),
  UNIQUE KEY `donor_profiles_donor_id_unique` (`donor_id`),
  KEY `donor_profiles_blood_group_verified_by_foreign` (`blood_group_verified_by`),
  KEY `donor_profiles_preferred_center_id_foreign` (`preferred_center_id`),
  CONSTRAINT `donor_profiles_blood_group_verified_by_foreign` FOREIGN KEY (`blood_group_verified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `donor_profiles_preferred_center_id_foreign` FOREIGN KEY (`preferred_center_id`) REFERENCES `blood_centers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `donor_profiles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `donor_rewards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `donor_rewards` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `reward_id` bigint unsigned NOT NULL,
  `status` enum('earned','redeemed') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'earned',
  `awarded_at` timestamp NOT NULL,
  `redeemed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `donor_rewards_user_id_reward_id_unique` (`user_id`,`reward_id`),
  KEY `donor_rewards_reward_id_foreign` (`reward_id`),
  CONSTRAINT `donor_rewards_reward_id_foreign` FOREIGN KEY (`reward_id`) REFERENCES `rewards` (`id`) ON DELETE CASCADE,
  CONSTRAINT `donor_rewards_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `eligibility_records`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `eligibility_records` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `checked_by` bigint unsigned DEFAULT NULL,
  `status` enum('eligible','not_yet_eligible','temporarily_deferred','permanently_deferred') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `age` tinyint unsigned DEFAULT NULL,
  `weight_kg` decimal(5,2) DEFAULT NULL,
  `answers` json DEFAULT NULL,
  `next_eligible_donation_date` date DEFAULT NULL,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `eligibility_records_user_id_foreign` (`user_id`),
  KEY `eligibility_records_checked_by_foreign` (`checked_by`),
  CONSTRAINT `eligibility_records_checked_by_foreign` FOREIGN KEY (`checked_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `eligibility_records_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `f_c_m_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `f_c_m_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `device_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'android',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `f_c_m_tokens_token_unique` (`token`),
  KEY `f_c_m_tokens_user_id_foreign` (`user_id`),
  CONSTRAINT `f_c_m_tokens_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `inventory_adjustments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `inventory_adjustments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `blood_center_id` bigint unsigned NOT NULL,
  `blood_unit_id` bigint unsigned DEFAULT NULL,
  `adjusted_by` bigint unsigned DEFAULT NULL,
  `blood_group` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity_delta` int NOT NULL,
  `reason` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `inventory_adjustments_blood_center_id_foreign` (`blood_center_id`),
  KEY `inventory_adjustments_blood_unit_id_foreign` (`blood_unit_id`),
  KEY `inventory_adjustments_adjusted_by_foreign` (`adjusted_by`),
  CONSTRAINT `inventory_adjustments_adjusted_by_foreign` FOREIGN KEY (`adjusted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `inventory_adjustments_blood_center_id_foreign` FOREIGN KEY (`blood_center_id`) REFERENCES `blood_centers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `inventory_adjustments_blood_unit_id_foreign` FOREIGN KEY (`blood_unit_id`) REFERENCES `blood_units` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `leaderboards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `leaderboards` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `period` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'all_time',
  `donation_count` int unsigned NOT NULL DEFAULT '0',
  `rank` int unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `leaderboards_user_id_period_unique` (`user_id`,`period`),
  CONSTRAINT `leaderboards_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `low_stock_alerts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `low_stock_alerts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `blood_center_id` bigint unsigned NOT NULL,
  `blood_group` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `available_units` int unsigned NOT NULL,
  `minimum_threshold` int unsigned NOT NULL,
  `status` enum('open','notified','campaign_created','resolved') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'open',
  `resolved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `low_stock_alerts_blood_center_id_foreign` (`blood_center_id`),
  CONSTRAINT `low_stock_alerts_blood_center_id_foreign` FOREIGN KEY (`blood_center_id`) REFERENCES `blood_centers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `model_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `model_has_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_roles` (
  `role_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `passkeys`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `passkeys` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `credential_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `credential` json NOT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `passkeys_credential_id_unique` (`credential_id`),
  KEY `passkeys_user_id_index` (`user_id`),
  CONSTRAINT `passkeys_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  KEY `personal_access_tokens_expires_at_index` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `rewards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rewards` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `donation_threshold` int unsigned NOT NULL DEFAULT '1',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `rewards_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `role_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `role_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sms_reminder_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sms_reminder_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `appointment_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `reminder_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `provider` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `provider_message_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `error` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `sent_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sms_reminder_logs_appointment_id_reminder_key_unique` (`appointment_id`,`reminder_key`),
  KEY `sms_reminder_logs_user_id_status_index` (`user_id`,`status`),
  KEY `sms_reminder_logs_reminder_key_status_index` (`reminder_key`,`status`),
  CONSTRAINT `sms_reminder_logs_appointment_id_foreign` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `sms_reminder_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `user_notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_notifications` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `body` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'general',
  `action_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `data` json DEFAULT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `sent_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_notifications_user_id_read_at_index` (`user_id`,`read_at`),
  KEY `user_notifications_user_id_created_at_index` (`user_id`,`created_at`),
  CONSTRAINT `user_notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `two_factor_secret` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `two_factor_recovery_codes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `two_factor_confirmed_at` timestamp NULL DEFAULT NULL,
  `phone` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `blood_group` enum('A+','A-','B+','B-','AB+','AB-','O+','O-') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gender` enum('male','female','other') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `region` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `last_donation` date DEFAULT NULL,
  `address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `profile_photo_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` enum('donor','admin','staff') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'donor',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `locale` varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'en',
  `remember_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `firebase_uid` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `firebase_provider` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_firebase_uid_unique` (`firebase_uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (1,'0001_01_01_000000_create_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (2,'0001_01_01_000001_create_cache_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (3,'0001_01_01_000002_create_jobs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (4,'2024_01_01_000000_create_passkeys_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (5,'2025_08_14_170933_add_two_factor_columns_to_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (6,'2026_03_16_055922_create_blood_centers_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (7,'2026_03_16_055939_create_appointments_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (8,'2026_03_16_055939_create_campaigns_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (9,'2026_03_16_055939_create_donations_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (10,'2026_03_16_060304_create_personal_access_tokens_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (11,'2026_03_16_081830_create_f_c_m_tokens_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (12,'2026_03_16_082509_align_users_table_with_flutter_specs',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (13,'2026_06_12_105702_create_permission_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (14,'2026_06_12_110000_create_donor_profiles_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (15,'2026_06_12_110100_create_center_staff_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (16,'2026_06_12_120000_add_workflow_fields_to_appointments_and_donations',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (17,'2026_06_13_090000_create_eligibility_and_loyalty_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (18,'2026_06_13_100000_create_inventory_alerts_and_reports_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (19,'2026_06_22_130000_add_mobile_app_support_fields',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (20,'2026_06_22_140000_create_user_notifications_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (21,'2026_06_22_150000_add_profile_photo_path_to_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (22,'2026_06_30_130000_add_firebase_auth_fields_to_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (23,'2026_07_01_150000_allow_nullable_user_email_for_mobile_phone_registration',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (24,'2026_07_09_160000_extend_articles_for_news_management',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (25,'2026_07_27_080000_create_sms_reminder_logs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (26,'2026_08_01_092943_add_locale_to_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (27,'2026_08_01_092944_backfill_user_locales_from_donor_profiles',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (28,'2026_08_01_131334_create_audit_logs_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (29,'2026_08_01_132337_add_workflow_uniqueness_constraints',3);
