-- MySQL dump 10.13  Distrib 8.0.31, for Linux (x86_64)
--
-- Host: localhost    Database: studip_52
-- ------------------------------------------------------
-- Server version	8.0.31-0ubuntu0.22.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `Institute`
--

DROP TABLE IF EXISTS `Institute`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `Institute` (
  `Institut_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `Name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `fakultaets_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `Strasse` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `Plz` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'http://www.studip.de',
  `telefon` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `fax` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `type` tinyint unsigned NOT NULL DEFAULT '0',
  `mkdate` int unsigned NOT NULL DEFAULT '0',
  `chdate` int unsigned NOT NULL DEFAULT '0',
  `lit_plugin_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `srienabled` tinyint unsigned NOT NULL DEFAULT '0',
  `lock_rule` varchar(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  PRIMARY KEY (`Institut_id`),
  KEY `fakultaets_id` (`fakultaets_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `abschluss`
--

DROP TABLE IF EXISTS `abschluss`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `abschluss` (
  `abschluss_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `name_kurz` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `beschreibung` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `author_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `editor_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `mkdate` int unsigned DEFAULT NULL,
  `chdate` int unsigned DEFAULT NULL,
  PRIMARY KEY (`abschluss_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `activities`
--

DROP TABLE IF EXISTS `activities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `activities` (
  `id` int NOT NULL AUTO_INCREMENT,
  `object_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `context` enum('system','course','institute','user') CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `context_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `provider` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `actor_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `actor_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `verb` enum('answered','attempted','attended','completed','created','deleted','edited','experienced','failed','imported','interacted','passed','shared','sent','voided') CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT 'experienced',
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `object_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `mkdate` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `context_id` (`context_id`),
  KEY `mkdate` (`mkdate`),
  KEY `object_id` (`object_id`(32)),
  KEY `context_query` (`context`,`context_id`,`mkdate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `admission_condition`
--

DROP TABLE IF EXISTS `admission_condition`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `admission_condition` (
  `rule_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `filter_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `conditiongroup_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `mkdate` int unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`rule_id`,`filter_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `admission_conditiongroup`
--

DROP TABLE IF EXISTS `admission_conditiongroup`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `admission_conditiongroup` (
  `conditiongroup_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `quota` int NOT NULL,
  PRIMARY KEY (`conditiongroup_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `admission_seminar_user`
--

DROP TABLE IF EXISTS `admission_seminar_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `admission_seminar_user` (
  `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `seminar_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `status` enum('awaiting','accepted') CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `mkdate` int unsigned NOT NULL DEFAULT '0',
  `chdate` int unsigned DEFAULT NULL,
  `position` int DEFAULT NULL,
  `comment` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `visible` enum('yes','no','unknown') CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT 'unknown',
  PRIMARY KEY (`user_id`,`seminar_id`),
  KEY `seminar_id` (`seminar_id`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `admissionfactor`
--

DROP TABLE IF EXISTS `admissionfactor`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `admissionfactor` (
  `list_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `factor` float NOT NULL DEFAULT '1',
  `owner_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `mkdate` int unsigned NOT NULL DEFAULT '0',
  `chdate` int unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`list_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `admissionrule_compat`
--

DROP TABLE IF EXISTS `admissionrule_compat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `admissionrule_compat` (
  `rule_type` varchar(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `compat_rule_type` varchar(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `mkdate` int unsigned NOT NULL DEFAULT '0',
  `chdate` int unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`rule_type`,`compat_rule_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `admissionrules`
--

DROP TABLE IF EXISTS `admissionrules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `admissionrules` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ruletype` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `active` tinyint unsigned NOT NULL DEFAULT '0',
  `mkdate` int unsigned NOT NULL DEFAULT '0',
  `path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ruletype` (`ruletype`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `api_consumer_permissions`
--

DROP TABLE IF EXISTS `api_consumer_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `api_consumer_permissions` (
  `route_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `consumer_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `method` char(6) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `granted` tinyint unsigned NOT NULL DEFAULT '0',
  UNIQUE KEY `route_id` (`route_id`,`consumer_id`,`method`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `api_consumers`
--

DROP TABLE IF EXISTS `api_consumers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `api_consumers` (
  `consumer_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `consumer_type` enum('http','studip','oauth') CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT 'studip',
  `auth_key` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `auth_secret` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `active` tinyint unsigned NOT NULL DEFAULT '0',
  `system` tinyint unsigned NOT NULL DEFAULT '0',
  `type` enum('website','mobile','desktop') CHARACTER SET latin1 COLLATE latin1_bin DEFAULT 'website',
  `title` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `callback` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `commercial` tinyint(1) DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `priority` int unsigned NOT NULL DEFAULT '0',
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `mkdate` int unsigned NOT NULL,
  `chdate` int unsigned NOT NULL,
  PRIMARY KEY (`consumer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `api_oauth_user_mapping`
--

DROP TABLE IF EXISTS `api_oauth_user_mapping`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `api_oauth_user_mapping` (
  `oauth_id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `mkdate` int unsigned NOT NULL,
  PRIMARY KEY (`oauth_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `api_user_permissions`
--

DROP TABLE IF EXISTS `api_user_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `api_user_permissions` (
  `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `consumer_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `granted` tinyint unsigned NOT NULL DEFAULT '0',
  `mkdate` int unsigned NOT NULL,
  `chdate` int unsigned NOT NULL,
  PRIMARY KEY (`user_id`,`consumer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `archiv`
--

DROP TABLE IF EXISTS `archiv`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `archiv` (
  `seminar_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `untertitel` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `beschreibung` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_time` int unsigned NOT NULL DEFAULT '0',
  `semester` varchar(16) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `heimat_inst_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `institute` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `dozenten` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `fakultaet` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `dump` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `archiv_file_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `archiv_protected_file_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `mkdate` int unsigned NOT NULL DEFAULT '0',
  `forumdump` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `wikidump` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `studienbereiche` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `VeranstaltungsNummer` varchar(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  PRIMARY KEY (`seminar_id`),
  KEY `heimat_inst_id` (`heimat_inst_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `archiv_user`
--

DROP TABLE IF EXISTS `archiv_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `archiv_user` (
  `seminar_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `status` enum('user','autor','tutor','dozent') CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT 'user',
  PRIMARY KEY (`seminar_id`,`user_id`),
  KEY `user_id` (`user_id`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `auth_extern`
--

DROP TABLE IF EXISTS `auth_extern`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `auth_extern` (
  `studip_user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `external_user_id` varchar(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `external_user_name` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `external_user_password` varchar(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `external_user_token` varchar(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `external_user_token_valid_until` int NOT NULL DEFAULT '0',
  `external_user_category` varchar(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `external_user_system_type` varchar(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `external_user_type` smallint NOT NULL DEFAULT '0',
  PRIMARY KEY (`studip_user_id`,`external_user_system_type`,`external_user_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `auth_user_md5`
--

DROP TABLE IF EXISTS `auth_user_md5`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `auth_user_md5` (
  `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `username` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `password` varbinary(64) NOT NULL DEFAULT '',
  `perms` enum('user','autor','tutor','dozent','admin','root') CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT 'user',
  `Vorname` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Nachname` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Email` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `validation_key` varchar(10) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `auth_plugin` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'standard',
  `locked` tinyint unsigned NOT NULL DEFAULT '0',
  `lock_comment` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `locked_by` varchar(32) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `visible` enum('global','always','yes','unknown','no','never') CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT 'unknown',
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `k_username` (`username`),
  KEY `perms` (`perms`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `auto_insert_sem`
--

DROP TABLE IF EXISTS `auto_insert_sem`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `auto_insert_sem` (
  `seminar_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `status` enum('autor','tutor','dozent') CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT 'autor',
  `domain_id` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`seminar_id`,`status`,`domain_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `auto_insert_user`
--

DROP TABLE IF EXISTS `auto_insert_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `auto_insert_user` (
  `seminar_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `mkdate` int unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`seminar_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `aux_lock_rules`
--

DROP TABLE IF EXISTS `aux_lock_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `aux_lock_rules` (
  `lock_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `attributes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `sorting` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `mkdate` int unsigned DEFAULT NULL,
  `chdate` int unsigned DEFAULT NULL,
  PRIMARY KEY (`lock_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `banner_ads`
--

DROP TABLE IF EXISTS `banner_ads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `banner_ads` (
  `ad_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `banner_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alttext` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `target_type` enum('url','seminar','inst','user','none') CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT 'url',
  `target` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `startdate` int unsigned NOT NULL DEFAULT '0',
  `enddate` int unsigned NOT NULL DEFAULT '0',
  `priority` int unsigned NOT NULL DEFAULT '0',
  `views` int unsigned NOT NULL DEFAULT '0',
  `clicks` int unsigned NOT NULL DEFAULT '0',
  `mkdate` int unsigned NOT NULL DEFAULT '0',
  `chdate` int unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`ad_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `banner_roles`
--

DROP TABLE IF EXISTS `banner_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `banner_roles` (
  `ad_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `roleid` int NOT NULL,
  PRIMARY KEY (`ad_id`,`roleid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `blubber_comments`
--

DROP TABLE IF EXISTS `blubber_comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `blubber_comments` (
  `comment_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `thread_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `external_contact` tinyint unsigned NOT NULL DEFAULT '0',
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `network` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `chdate` int unsigned DEFAULT NULL,
  `mkdate` int unsigned DEFAULT NULL,
  PRIMARY KEY (`comment_id`),
  KEY `thread_id` (`thread_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `blubber_events_queue`
--

DROP TABLE IF EXISTS `blubber_events_queue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `blubber_events_queue` (
  `event_type` varchar(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `item_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `mkdate` int unsigned NOT NULL,
  PRIMARY KEY (`event_type`,`item_id`,`mkdate`),
  KEY `item_id` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `blubber_follower`
--

DROP TABLE IF EXISTS `blubber_follower`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `blubber_follower` (
  `studip_user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `external_contact_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `left_follows_right` tinyint unsigned NOT NULL DEFAULT '0',
  KEY `studip_user_id` (`studip_user_id`),
  KEY `external_contact_id` (`external_contact_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `blubber_mentions`
--

DROP TABLE IF EXISTS `blubber_mentions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `blubber_mentions` (
  `mention_id` int unsigned NOT NULL AUTO_INCREMENT,
  `thread_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `external_contact` tinyint unsigned NOT NULL DEFAULT '0',
  `mkdate` int unsigned NOT NULL,
  PRIMARY KEY (`mention_id`),
  UNIQUE KEY `unique_users_per_topic` (`thread_id`,`user_id`,`external_contact`),
  KEY `topic_id` (`thread_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `blubber_tags`
--

DROP TABLE IF EXISTS `blubber_tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `blubber_tags` (
  `topic_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `tag` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`topic_id`,`tag`),
  KEY `tag` (`tag`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `blubber_threads`
--

DROP TABLE IF EXISTS `blubber_threads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `blubber_threads` (
  `thread_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `context_type` enum('public','private','course','institute') CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT 'public',
  `context_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `external_contact` tinyint unsigned NOT NULL DEFAULT '0',
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `display_class` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `visible_in_stream` tinyint unsigned NOT NULL DEFAULT '0',
  `commentable` tinyint unsigned NOT NULL DEFAULT '0',
  `metadata` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `chdate` int unsigned DEFAULT NULL,
  `mkdate` int unsigned DEFAULT NULL,
  PRIMARY KEY (`thread_id`),
  KEY `context_type` (`context_type`),
  KEY `context_id` (`context_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `blubber_threads_followstates`
--

DROP TABLE IF EXISTS `blubber_threads_followstates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `blubber_threads_followstates` (
  `thread_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `state` enum('followed','unfollowed') CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT 'unfollowed',
  `mkdate` int unsigned DEFAULT NULL,
  PRIMARY KEY (`thread_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `cache_key` varchar(255) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `content` mediumblob NOT NULL,
  `expires` int unsigned NOT NULL,
  PRIMARY KEY (`cache_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cache_operations`
--

DROP TABLE IF EXISTS `cache_operations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_operations` (
  `cache_key` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `operation` char(6) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `parameters` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `mkdate` int unsigned NOT NULL,
  `chdate` int unsigned NOT NULL,
  PRIMARY KEY (`cache_key`(200),`operation`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cache_types`
--

DROP TABLE IF EXISTS `cache_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_types` (
  `cache_id` int NOT NULL AUTO_INCREMENT,
  `class_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `chdate` int DEFAULT NULL,
  `mkdate` int DEFAULT NULL,
  PRIMARY KEY (`cache_id`),
  UNIQUE KEY `class_name` (`class_name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `calendar_event`
--

DROP TABLE IF EXISTS `calendar_event`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `calendar_event` (
  `range_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `event_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `group_status` tinyint unsigned NOT NULL DEFAULT '0',
  `mkdate` int unsigned NOT NULL,
  `chdate` int unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`range_id`,`event_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `calendar_user`
--

DROP TABLE IF EXISTS `calendar_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `calendar_user` (
  `owner_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `permission` int NOT NULL,
  `mkdate` int unsigned NOT NULL,
  `chdate` int unsigned NOT NULL,
  PRIMARY KEY (`owner_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `clipboard_items`
--

DROP TABLE IF EXISTS `clipboard_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `clipboard_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `clipboard_id` int NOT NULL,
  `range_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `range_type` varchar(64) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT 'SimpleORMap',
  `mkdate` int unsigned NOT NULL DEFAULT '0',
  `chdate` int unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `clipboard_id` (`clipboard_id`),
  KEY `range` (`range_id`,`range_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `clipboards`
--

DROP TABLE IF EXISTS `clipboards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `clipboards` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `name` varchar(256) NOT NULL DEFAULT '',
  `handler` varchar(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT 'Clipboard',
  `allowed_item_class` varchar(64) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT 'StudipItem',
  `mkdate` int unsigned NOT NULL DEFAULT '0',
  `chdate` int unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `colour_values`
--

DROP TABLE IF EXISTS `colour_values`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `colour_values` (
  `colour_id` varchar(128) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `description` varchar(256) NOT NULL DEFAULT '',
  `value` varchar(8) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT 'ffffffff',
  `mkdate` int unsigned NOT NULL DEFAULT '0',
  `chdate` int unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`colour_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `comments`
--

DROP TABLE IF EXISTS `comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `comments` (
  `comment_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `object_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `mkdate` int unsigned NOT NULL DEFAULT '0',
  `chdate` int unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`comment_id`),
  KEY `object_id` (`object_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `conditionaladmissions`
--

DROP TABLE IF EXISTS `conditionaladmissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `conditionaladmissions` (
  `rule_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `start_time` int unsigned NOT NULL DEFAULT '0',
  `end_time` int unsigned NOT NULL DEFAULT '0',
  `mkdate` int unsigned NOT NULL DEFAULT '0',
  `conditions_stopped` tinyint unsigned NOT NULL DEFAULT '0',
  `chdate` int unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`rule_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `config`
--

DROP TABLE IF EXISTS `config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `config` (
  `field` varchar(255) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('boolean','integer','string','array','i18n') CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT 'string',
  `range` enum('global','range','user','course','institute') CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT 'global',
  `section` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `mkdate` int unsigned NOT NULL DEFAULT '0',
  `chdate` int unsigned NOT NULL DEFAULT '0',
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`field`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `config_values`
--

DROP TABLE IF EXISTS `config_values`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `config_values` (
  `field` varchar(255) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `range_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `mkdate` int unsigned NOT NULL DEFAULT '0',
  `chdate` int unsigned NOT NULL DEFAULT '0',
  `comment` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`field`,`range_id`),
  KEY `field` (`field`,`value`(10)),
  KEY `range_id` (`range_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `consultation_blocks`
--

DROP TABLE IF EXISTS `consultation_blocks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `consultation_blocks` (
  `block_id` int unsigned NOT NULL AUTO_INCREMENT,
  `range_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `range_type` enum('user','course','institute') CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `start` int unsigned NOT NULL,
  `end` int unsigned NOT NULL,
  `room` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `calendar_events` tinyint unsigned NOT NULL DEFAULT '0' COMMENT 'Create events for slots',
  `show_participants` tinyint unsigned NOT NULL DEFAULT '0',
  `require_reason` enum('no','optional','yes') CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT 'optional',
  `confirmation_text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `size` tinyint unsigned NOT NULL DEFAULT '1' COMMENT 'How many people may book a slot',
  `mkdate` int unsigned NOT NULL,
  `chdate` int unsigned NOT NULL,
  PRIMARY KEY (`block_id`),
  KEY `range` (`range_id`,`range_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `consultation_bookings`
--

DROP TABLE IF EXISTS `consultation_bookings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `consultation_bookings` (
  `booking_id` int unsigned NOT NULL AUTO_INCREMENT,
  `slot_id` int unsigned NOT NULL,
  `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `student_event_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `mkdate` int unsigned NOT NULL,
  `chdate` int unsigned NOT NULL,
  PRIMARY KEY (`booking_id`),
  KEY `block_id` (`slot_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `consultation_events`
--

DROP TABLE IF EXISTS `consultation_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `consultation_events` (
  `slot_id` int unsigned NOT NULL,
  `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `event_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `mkdate` int unsigned NOT NULL,
  PRIMARY KEY (`slot_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `consultation_responsibilities`
--

DROP TABLE IF EXISTS `consultation_responsibilities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `consultation_responsibilities` (
  `block_id` int unsigned NOT NULL,
  `range_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `range_type` enum('user','institute','statusgroup') CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `mkdate` int unsigned NOT NULL,
  PRIMARY KEY (`block_id`,`range_id`,`range_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `consultation_slots`
--

DROP TABLE IF EXISTS `consultation_slots`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `consultation_slots` (
  `slot_id` int unsigned NOT NULL AUTO_INCREMENT,
  `block_id` int unsigned NOT NULL,
  `start_time` int unsigned NOT NULL,
  `end_time` int unsigned NOT NULL,
  `note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `mkdate` int unsigned NOT NULL,
  `chdate` int unsigned NOT NULL,
  PRIMARY KEY (`slot_id`),
  KEY `block_id` (`block_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `contact`
--

DROP TABLE IF EXISTS `contact`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `contact` (
  `owner_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `mkdate` int unsigned DEFAULT NULL,
  PRIMARY KEY (`owner_id`,`user_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `content_terms_of_use_entries`
--

DROP TABLE IF EXISTS `content_terms_of_use_entries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `content_terms_of_use_entries` (
  `id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `position` int unsigned NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `student_description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `download_condition` tinyint NOT NULL,
  `icon` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `is_default` tinyint unsigned NOT NULL DEFAULT '0',
  `mkdate` int unsigned NOT NULL,
  `chdate` int unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `coursememberadmissions`
--

DROP TABLE IF EXISTS `coursememberadmissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `coursememberadmissions` (
  `rule_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_time` int unsigned NOT NULL DEFAULT '0',
  `end_time` int unsigned NOT NULL DEFAULT '0',
  `courses` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `modus` tinyint(1) NOT NULL DEFAULT '0',
  `mkdate` int unsigned NOT NULL DEFAULT '0',
  `chdate` int unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`rule_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `courseset_factorlist`
--

DROP TABLE IF EXISTS `courseset_factorlist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `courseset_factorlist` (
  `set_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `factorlist_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `mkdate` int unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`set_id`,`factorlist_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `courseset_institute`
--

DROP TABLE IF EXISTS `courseset_institute`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `courseset_institute` (
  `set_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `institute_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `mkdate` int unsigned DEFAULT NULL,
  `chdate` int unsigned DEFAULT NULL,
  PRIMARY KEY (`set_id`,`institute_id`),
  KEY `institute_id` (`institute_id`,`set_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `courseset_rule`
--

DROP TABLE IF EXISTS `courseset_rule`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `courseset_rule` (
  `set_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `rule_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mkdate` int unsigned DEFAULT NULL,
  PRIMARY KEY (`set_id`,`rule_id`),
  KEY `type` (`set_id`,`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `coursesets`
--

DROP TABLE IF EXISTS `coursesets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `coursesets` (
  `set_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `infotext` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `algorithm` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `algorithm_run` tinyint unsigned NOT NULL DEFAULT '0',
  `private` tinyint unsigned NOT NULL DEFAULT '0',
  `mkdate` int unsigned NOT NULL DEFAULT '0',
  `chdate` int unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`set_id`),
  KEY `set_user` (`user_id`,`set_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `coursewizardsteps`
--

DROP TABLE IF EXISTS `coursewizardsteps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `coursewizardsteps` (
  `id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `classname` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `number` tinyint(1) NOT NULL,
  `enabled` tinyint unsigned NOT NULL DEFAULT '1',
  `mkdate` int unsigned NOT NULL DEFAULT '0',
  `chdate` int unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `classname` (`classname`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cronjobs_logs`
--

DROP TABLE IF EXISTS `cronjobs_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cronjobs_logs` (
  `log_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `schedule_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `scheduled` int unsigned NOT NULL,
  `executed` int unsigned NOT NULL,
  `exception` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `output` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `duration` float NOT NULL,
  PRIMARY KEY (`log_id`),
  KEY `schedule_id` (`schedule_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cronjobs_schedules`
--

DROP TABLE IF EXISTS `cronjobs_schedules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cronjobs_schedules` (
  `schedule_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `task_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `active` tinyint unsigned NOT NULL DEFAULT '0',
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` varchar(4096) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `parameters` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `priority` enum('low','normal','high') CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT 'normal',
  `type` enum('periodic','once') CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT 'periodic',
  `minute` tinyint DEFAULT NULL,
  `hour` tinyint DEFAULT NULL,
  `day` tinyint DEFAULT NULL,
  `month` tinyint DEFAULT NULL,
  `day_of_week` tinyint unsigned DEFAULT NULL,
  `next_execution` int unsigned NOT NULL DEFAULT '0',
  `last_execution` int unsigned DEFAULT NULL,
  `last_result` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `execution_count` bigint unsigned NOT NULL DEFAULT '0',
  `mkdate` int unsigned NOT NULL,
  `chdate` int unsigned NOT NULL,
  PRIMARY KEY (`schedule_id`),
  KEY `task_id` (`task_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cronjobs_tasks`
--

DROP TABLE IF EXISTS `cronjobs_tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cronjobs_tasks` (
  `task_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `filename` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `class` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `active` tinyint unsigned NOT NULL DEFAULT '0',
  `execution_count` bigint unsigned NOT NULL DEFAULT '0',
  `assigned_count` int unsigned NOT NULL DEFAULT '0',
  `mkdate` int unsigned DEFAULT NULL,
  `chdate` int unsigned DEFAULT NULL,
  PRIMARY KEY (`task_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cw_block_comments`
--

DROP TABLE IF EXISTS `cw_block_comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cw_block_comments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `block_id` int NOT NULL,
  `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `comment` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `mkdate` int NOT NULL,
  `chdate` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `index_block_id` (`block_id`),
  KEY `index_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cw_block_feedbacks`
--

DROP TABLE IF EXISTS `cw_block_feedbacks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cw_block_feedbacks` (
  `id` int NOT NULL AUTO_INCREMENT,
  `block_id` int NOT NULL,
  `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `feedback` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `mkdate` int NOT NULL,
  `chdate` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `index_block_id` (`block_id`),
  KEY `index_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cw_blocks`
--

DROP TABLE IF EXISTS `cw_blocks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cw_blocks` (
  `id` int NOT NULL AUTO_INCREMENT,
  `container_id` int NOT NULL,
  `owner_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `editor_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `edit_blocker_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `position` int NOT NULL,
  `block_type` varchar(255) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `visible` tinyint(1) NOT NULL,
  `payload` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `mkdate` int NOT NULL,
  `chdate` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `index_container_id` (`container_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cw_bookmarks`
--

DROP TABLE IF EXISTS `cw_bookmarks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cw_bookmarks` (
  `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `element_id` int NOT NULL,
  `mkdate` int NOT NULL,
  `chdate` int NOT NULL,
  PRIMARY KEY (`user_id`,`element_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cw_containers`
--

DROP TABLE IF EXISTS `cw_containers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cw_containers` (
  `id` int NOT NULL AUTO_INCREMENT,
  `structural_element_id` int NOT NULL,
  `owner_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `editor_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `edit_blocker_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `position` int NOT NULL,
  `site` int NOT NULL,
  `container_type` varchar(255) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `visible` tinyint(1) NOT NULL,
  `payload` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `mkdate` int NOT NULL,
  `chdate` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `index_structural_element_id` (`structural_element_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cw_public_links`
--

DROP TABLE IF EXISTS `cw_public_links`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cw_public_links` (
  `id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `structural_element_id` int NOT NULL,
  `password` varbinary(64) NOT NULL,
  `expire_date` int NOT NULL,
  `mkdate` int NOT NULL,
  `chdate` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `index_user_id` (`user_id`),
  KEY `index_structural_element_id` (`structural_element_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cw_structural_element_comments`
--

DROP TABLE IF EXISTS `cw_structural_element_comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cw_structural_element_comments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `structural_element_id` int NOT NULL,
  `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `comment` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `mkdate` int NOT NULL,
  `chdate` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `index_structural_element_id` (`structural_element_id`),
  KEY `index_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cw_structural_element_feedbacks`
--

DROP TABLE IF EXISTS `cw_structural_element_feedbacks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cw_structural_element_feedbacks` (
  `id` int NOT NULL AUTO_INCREMENT,
  `structural_element_id` int NOT NULL,
  `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `feedback` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `mkdate` int NOT NULL,
  `chdate` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `index_structural_element_id` (`structural_element_id`),
  KEY `index_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cw_structural_elements`
--

DROP TABLE IF EXISTS `cw_structural_elements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cw_structural_elements` (
  `id` int NOT NULL AUTO_INCREMENT,
  `parent_id` int DEFAULT NULL,
  `is_link` tinyint(1) NOT NULL,
  `target_id` int DEFAULT NULL,
  `range_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `range_type` enum('course','user') CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `owner_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `editor_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `edit_blocker_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `position` int NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `image_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `purpose` enum('content','draft','task','template','oer','other','portfolio') CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `payload` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `public` tinyint(1) NOT NULL,
  `release_date` int NOT NULL,
  `withdraw_date` int NOT NULL,
  `read_approval` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `write_approval` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `copy_approval` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `external_relations` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `mkdate` int NOT NULL,
  `chdate` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `index_parent_id` (`parent_id`),
  KEY `index_range_id` (`range_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cw_task_feedbacks`
--

DROP TABLE IF EXISTS `cw_task_feedbacks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cw_task_feedbacks` (
  `id` int NOT NULL AUTO_INCREMENT,
  `task_id` int NOT NULL,
  `lecturer_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `content` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `mkdate` int NOT NULL,
  `chdate` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `index_task_id` (`task_id`),
  KEY `index_lecturer_id` (`lecturer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cw_task_groups`
--

DROP TABLE IF EXISTS `cw_task_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cw_task_groups` (
  `id` int NOT NULL AUTO_INCREMENT,
  `seminar_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `lecturer_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `target_id` int NOT NULL,
  `task_template_id` int NOT NULL,
  `solver_may_add_blocks` tinyint(1) NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mkdate` int NOT NULL,
  `chdate` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `index_seminar_id` (`seminar_id`),
  KEY `index_lecturer_id` (`lecturer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cw_tasks`
--

DROP TABLE IF EXISTS `cw_tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cw_tasks` (
  `id` int NOT NULL AUTO_INCREMENT,
  `task_group_id` int NOT NULL,
  `structural_element_id` int NOT NULL,
  `solver_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `solver_type` enum('autor','group') CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `submission_date` int NOT NULL,
  `submitted` tinyint(1) NOT NULL,
  `renewal` enum('pending','granted','declined') CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `renewal_date` int NOT NULL,
  `feedback_id` int DEFAULT NULL,
  `mkdate` int NOT NULL,
  `chdate` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `index_task_group_id` (`task_group_id`),
  KEY `index_structural_element_id` (`structural_element_id`),
  KEY `index_solver_id` (`solver_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cw_templates`
--

DROP TABLE IF EXISTS `cw_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cw_templates` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `purpose` enum('content','template','oer','portfolio','draft','other') CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `structure` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `mkdate` int NOT NULL,
  `chdate` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cw_user_data_fields`
--

DROP TABLE IF EXISTS `cw_user_data_fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cw_user_data_fields` (
  `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `block_id` int NOT NULL,
  `payload` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `mkdate` int NOT NULL,
  `chdate` int NOT NULL,
  PRIMARY KEY (`user_id`,`block_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cw_user_progresses`
--

DROP TABLE IF EXISTS `cw_user_progresses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cw_user_progresses` (
  `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `block_id` int NOT NULL,
  `grade` float NOT NULL,
  `mkdate` int NOT NULL,
  `chdate` int NOT NULL,
  PRIMARY KEY (`user_id`,`block_id`),
  KEY `block_id` (`block_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `datafields`
--

DROP TABLE IF EXISTS `datafields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `datafields` (
  `datafield_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `object_type` enum('sem','inst','user','userinstrole','usersemdata','roleinstdata','moduldeskriptor','modulteildeskriptor','studycourse') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `object_class` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `edit_perms` enum('user','autor','tutor','dozent','admin','root') CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `view_perms` enum('all','user','autor','tutor','dozent','admin','root') CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `institut_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `priority` tinyint unsigned NOT NULL DEFAULT '0',
  `mkdate` int unsigned DEFAULT NULL,
  `chdate` int unsigned DEFAULT NULL,
  `type` enum('bool','textline','textlinei18n','textarea','textareai18n','textmarkup','textmarkupi18n','selectbox','date','time','email','phone','radio','combo','link','selectboxmultiple') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'textline',
  `typeparam` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_required` tinyint unsigned NOT NULL DEFAULT '0',
  `default_value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `is_userfilter` tinyint unsigned NOT NULL DEFAULT '0',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `system` tinyint unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`datafield_id`),
  KEY `object_type` (`object_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `datafields_entries`
--

DROP TABLE IF EXISTS `datafields_entries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `datafields_entries` (
  `datafield_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `range_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `mkdate` int unsigned DEFAULT NULL,
  `chdate` int unsigned DEFAULT NULL,
  `sec_range_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `lang` varchar(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  PRIMARY KEY (`datafield_id`,`range_id`,`sec_range_id`,`lang`) USING BTREE,
  KEY `range_id` (`range_id`,`datafield_id`),
  KEY `datafield_id_2` (`datafield_id`,`sec_range_id`),
  KEY `datafields_contents` (`datafield_id`,`content`(32))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `deputies`
--

DROP TABLE IF EXISTS `deputies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `deputies` (
  `range_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `gruppe` tinyint NOT NULL DEFAULT '0',
  `notification` int NOT NULL DEFAULT '0',
  `edit_about` tinyint unsigned NOT NULL DEFAULT '0',
  `mkdate` int unsigned DEFAULT NULL,
  `chdate` int unsigned DEFAULT NULL,
  PRIMARY KEY (`range_id`,`user_id`),
  KEY `user_id` (`user_id`,`range_id`,`edit_about`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `etask_assignment_attempts`
--

DROP TABLE IF EXISTS `etask_assignment_attempts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `etask_assignment_attempts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `assignment_id` int NOT NULL,
  `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `start` int unsigned DEFAULT NULL,
  `end` int unsigned DEFAULT NULL,
  `options` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `mkdate` int unsigned DEFAULT NULL,
  `chdate` int unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `etask_assignment_ranges`
--

DROP TABLE IF EXISTS `etask_assignment_ranges`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `etask_assignment_ranges` (
  `id` int NOT NULL AUTO_INCREMENT,
  `assignment_id` int NOT NULL,
  `range_type` enum('course','global','group','institute','user') CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `range_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `options` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `mkdate` int unsigned DEFAULT NULL,
  `chdate` int unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `assignment_id` (`assignment_id`,`range_type`,`range_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `etask_assignments`
--

DROP TABLE IF EXISTS `etask_assignments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `etask_assignments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `test_id` int NOT NULL,
  `range_type` enum('course','global','group','institute','user') CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `range_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `type` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `start` int unsigned DEFAULT NULL,
  `end` int unsigned DEFAULT NULL,
  `active` tinyint unsigned NOT NULL,
  `options` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `mkdate` int unsigned DEFAULT NULL,
  `chdate` int unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `etask_responses`
--

DROP TABLE IF EXISTS `etask_responses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `etask_responses` (
  `id` int NOT NULL AUTO_INCREMENT,
  `assignment_id` int NOT NULL,
  `task_id` int NOT NULL,
  `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `response` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `state` tinyint(1) DEFAULT NULL,
  `points` float DEFAULT NULL,
  `feedback` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `grader_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `mkdate` int unsigned NOT NULL,
  `chdate` int unsigned NOT NULL,
  `options` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `etask_task_tags`
--

DROP TABLE IF EXISTS `etask_task_tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `etask_task_tags` (
  `task_id` int NOT NULL,
  `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `tag` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`task_id`,`user_id`,`tag`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `etask_tasks`
--

DROP TABLE IF EXISTS `etask_tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `etask_tasks` (
  `id` int NOT NULL AUTO_INCREMENT,
  `type` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `task` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `mkdate` int unsigned NOT NULL,
  `chdate` int unsigned NOT NULL,
  `options` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `etask_test_tags`
--

DROP TABLE IF EXISTS `etask_test_tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `etask_test_tags` (
  `test_id` int NOT NULL,
  `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `tag` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`test_id`,`user_id`,`tag`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `etask_test_tasks`
--

DROP TABLE IF EXISTS `etask_test_tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `etask_test_tasks` (
  `test_id` int NOT NULL,
  `task_id` int NOT NULL,
  `position` int NOT NULL,
  `points` float DEFAULT NULL,
  `options` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `mkdate` int unsigned DEFAULT NULL,
  `chdate` int unsigned DEFAULT NULL,
  PRIMARY KEY (`test_id`,`task_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `etask_tests`
--

DROP TABLE IF EXISTS `etask_tests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `etask_tests` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `mkdate` int unsigned NOT NULL,
  `chdate` int unsigned NOT NULL,
  `options` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `eval`
--

DROP TABLE IF EXISTS `eval`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `eval` (
  `eval_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `author_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `startdate` int unsigned DEFAULT NULL,
  `stopdate` int unsigned DEFAULT NULL,
  `timespan` int unsigned DEFAULT NULL,
  `mkdate` int unsigned NOT NULL DEFAULT '0',
  `chdate` int unsigned NOT NULL DEFAULT '0',
  `anonymous` tinyint unsigned NOT NULL DEFAULT '1',
  `visible` tinyint unsigned NOT NULL DEFAULT '1',
  `shared` tinyint unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`eval_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `eval_group_template`
--

DROP TABLE IF EXISTS `eval_group_template`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `eval_group_template` (
  `evalgroup_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `group_type` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'normal',
  PRIMARY KEY (`evalgroup_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `eval_range`
--

DROP TABLE IF EXISTS `eval_range`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `eval_range` (
  `eval_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `range_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  PRIMARY KEY (`eval_id`,`range_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `eval_templates`
--

DROP TABLE IF EXISTS `eval_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `eval_templates` (
  `template_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `institution_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `show_questions` tinyint unsigned NOT NULL DEFAULT '1',
  `show_total_stats` tinyint unsigned NOT NULL DEFAULT '1',
  `show_graphics` tinyint unsigned NOT NULL DEFAULT '1',
  `show_questionblock_headline` tinyint unsigned NOT NULL DEFAULT '1',
  `show_group_headline` tinyint unsigned NOT NULL DEFAULT '1',
  `polscale_gfx_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'bars',
  `likertscale_gfx_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'bars',
  `mchoice_scale_gfx_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'bars',
  `kurzbeschreibung` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`template_id`),
  KEY `user_id` (`user_id`,`institution_id`,`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `eval_templates_eval`
--

DROP TABLE IF EXISTS `eval_templates_eval`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `eval_templates_eval` (
  `eval_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `template_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  PRIMARY KEY (`eval_id`),
  KEY `eval_id` (`eval_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `eval_templates_user`
--

DROP TABLE IF EXISTS `eval_templates_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `eval_templates_user` (
  `eval_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `template_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  KEY `eval_id` (`eval_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `eval_user`
--

DROP TABLE IF EXISTS `eval_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `eval_user` (
  `eval_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  PRIMARY KEY (`eval_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `evalanswer`
--

DROP TABLE IF EXISTS `evalanswer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `evalanswer` (
  `evalanswer_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `parent_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `position` int NOT NULL DEFAULT '0',
  `text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` int NOT NULL DEFAULT '0',
  `rows` tinyint unsigned NOT NULL DEFAULT '0',
  `counter` int unsigned NOT NULL DEFAULT '0',
  `residual` tinyint unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`evalanswer_id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `evalanswer_user`
--

DROP TABLE IF EXISTS `evalanswer_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `evalanswer_user` (
  `evalanswer_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `evaldate` int unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`evalanswer_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `evalgroup`
--

DROP TABLE IF EXISTS `evalgroup`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `evalgroup` (
  `evalgroup_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `parent_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `position` int NOT NULL DEFAULT '0',
  `child_type` enum('EvaluationGroup','EvaluationQuestion') CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT 'EvaluationGroup',
  `mandatory` tinyint unsigned NOT NULL DEFAULT '0',
  `template_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  PRIMARY KEY (`evalgroup_id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `evalquestion`
--

DROP TABLE IF EXISTS `evalquestion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `evalquestion` (
  `evalquestion_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `parent_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `type` enum('likertskala','multiplechoice','polskala') CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT 'multiplechoice',
  `position` int NOT NULL DEFAULT '0',
  `text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `multiplechoice` tinyint unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`evalquestion_id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `event_data`
--

DROP TABLE IF EXISTS `event_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `event_data` (
  `event_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `author_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `editor_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `uid` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `start` int unsigned NOT NULL DEFAULT '0',
  `end` int unsigned NOT NULL DEFAULT '0',
  `summary` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `class` enum('PUBLIC','PRIVATE','CONFIDENTIAL') CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT 'PRIVATE',
  `categories` tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `category_intern` tinyint unsigned NOT NULL DEFAULT '0',
  `priority` tinyint unsigned NOT NULL DEFAULT '0',
  `location` tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `ts` int unsigned NOT NULL DEFAULT '0',
  `linterval` smallint unsigned DEFAULT NULL,
  `sinterval` smallint unsigned DEFAULT NULL,
  `wdays` varchar(7) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `month` tinyint unsigned DEFAULT NULL,
  `day` tinyint unsigned DEFAULT NULL,
  `rtype` enum('SINGLE','DAILY','WEEKLY','MONTHLY','YEARLY') CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT 'SINGLE',
  `duration` smallint unsigned NOT NULL DEFAULT '0',
  `count` smallint DEFAULT '0',
  `expire` int unsigned NOT NULL DEFAULT '0',
  `exceptions` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `mkdate` int unsigned NOT NULL DEFAULT '0',
  `chdate` int unsigned NOT NULL DEFAULT '0',
  `importdate` int unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`event_id`),
  UNIQUE KEY `uid` (`uid`),
  KEY `autor_id` (`author_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ex_termine`
--

DROP TABLE IF EXISTS `ex_termine`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ex_termine` (
  `termin_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `range_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `autor_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `content` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `date` int unsigned NOT NULL DEFAULT '0',
  `end_time` int unsigned NOT NULL DEFAULT '0',
  `mkdate` int unsigned NOT NULL DEFAULT '0',
  `chdate` int unsigned NOT NULL DEFAULT '0',
  `date_typ` tinyint NOT NULL DEFAULT '0',
  `raum` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `metadate_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `resource_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  PRIMARY KEY (`termin_id`),
  KEY `range_id` (`range_id`,`date`),
  KEY `metadate_id` (`metadate_id`,`date`),
  KEY `date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `extern_config`
--

DROP TABLE IF EXISTS `extern_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `extern_config` (
  `config_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `range_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `config_type` int NOT NULL DEFAULT '0',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `is_standard` tinyint unsigned NOT NULL DEFAULT '0',
  `config` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `mkdate` int unsigned NOT NULL DEFAULT '0',
  `chdate` int unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`config_id`,`range_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `external_users`
--

DROP TABLE IF EXISTS `external_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `external_users` (
  `external_contact_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `foreign_id` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `host_id` varchar(32) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `contact_type` varchar(16) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT 'anonymous',
  `name` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `avatar_url` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `data` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `chdate` int unsigned NOT NULL,
  `mkdate` int unsigned NOT NULL,
  PRIMARY KEY (`external_contact_id`),
  KEY `mail_identifier` (`foreign_id`),
  KEY `contact_type` (`contact_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fach`
--

DROP TABLE IF EXISTS `fach`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fach` (
  `fach_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_kurz` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `beschreibung` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `schlagworte` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `author_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `editor_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `mkdate` int unsigned NOT NULL DEFAULT '0',
  `chdate` int unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`fach_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `feedback`
--

DROP TABLE IF EXISTS `feedback`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `feedback` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `range_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `range_type` varchar(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `course_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `question` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `mode` int unsigned NOT NULL,
  `results_visible` tinyint unsigned NOT NULL,
  `commentable` tinyint unsigned NOT NULL,
  `mkdate` int unsigned NOT NULL,
  `chdate` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `idx_range` (`range_id`,`range_type`),
  KEY `course_id` (`course_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `feedback_entries`
--

DROP TABLE IF EXISTS `feedback_entries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `feedback_entries` (
  `id` int NOT NULL AUTO_INCREMENT,
  `feedback_id` int unsigned NOT NULL,
  `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `comment` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `rating` tinyint unsigned NOT NULL,
  `mkdate` int unsigned NOT NULL,
  `chdate` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `feedback_id` (`feedback_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `file_refs`
--

DROP TABLE IF EXISTS `file_refs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `file_refs` (
  `id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `file_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `folder_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `downloads` int unsigned NOT NULL DEFAULT '0',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `content_terms_of_use_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `mkdate` int unsigned NOT NULL DEFAULT '0',
  `chdate` int unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `file_id` (`file_id`),
  KEY `folder_id` (`folder_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `files`
--

DROP TABLE IF EXISTS `files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `files` (
  `id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `mime_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `filetype` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'StandardFile',
  `size` int unsigned NOT NULL,
  `metadata` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `author_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `mkdate` int unsigned NOT NULL,
  `chdate` int unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `files_search_attributes`
--

DROP TABLE IF EXISTS `files_search_attributes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `files_search_attributes` (
  `id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `file_ref_user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `file_ref_mkdate` int unsigned NOT NULL,
  `file_ref_chdate` int unsigned NOT NULL,
  `folder_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `folder_range_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `folder_range_type` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `folder_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `course_status` tinyint unsigned DEFAULT NULL,
  `semester_start` int unsigned DEFAULT NULL,
  `semester_end` int unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `folder_range_id` (`folder_range_id`),
  KEY `folder_range_type` (`folder_range_type`),
  KEY `semester_start` (`semester_start`),
  KEY `semester_end` (`semester_end`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `files_search_index`
--

DROP TABLE IF EXISTS `files_search_index`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `files_search_index` (
  `FTS_DOC_ID` bigint unsigned NOT NULL AUTO_INCREMENT,
  `file_ref_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `relevance` float NOT NULL,
  PRIMARY KEY (`FTS_DOC_ID`),
  KEY `file_ref_id` (`file_ref_id`),
  FULLTEXT KEY `text` (`text`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `folders`
--

DROP TABLE IF EXISTS `folders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `folders` (
  `id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `parent_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `range_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `range_type` varchar(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `folder_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `data_content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `mkdate` int unsigned NOT NULL,
  `chdate` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `range_id` (`range_id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `forum_abo_users`
--

DROP TABLE IF EXISTS `forum_abo_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `forum_abo_users` (
  `topic_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  PRIMARY KEY (`topic_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `forum_categories`
--

DROP TABLE IF EXISTS `forum_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `forum_categories` (
  `category_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `seminar_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `entry_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `pos` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`category_id`),
  KEY `seminar_id` (`seminar_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `forum_categories_entries`
--

DROP TABLE IF EXISTS `forum_categories_entries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `forum_categories_entries` (
  `category_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `topic_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `pos` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`category_id`,`topic_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `forum_entries`
--

DROP TABLE IF EXISTS `forum_entries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `forum_entries` (
  `topic_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `seminar_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `area` tinyint NOT NULL DEFAULT '0',
  `mkdate` int unsigned NOT NULL,
  `latest_chdate` int unsigned DEFAULT NULL,
  `chdate` int unsigned NOT NULL,
  `author` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `author_host` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `lft` int NOT NULL,
  `rgt` int NOT NULL,
  `depth` int NOT NULL,
  `anonymous` tinyint NOT NULL DEFAULT '0',
  `closed` tinyint unsigned NOT NULL DEFAULT '0',
  `sticky` tinyint unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`topic_id`),
  KEY `seminar_id` (`seminar_id`,`lft`),
  KEY `seminar_id_2` (`seminar_id`,`rgt`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `forum_entries_issues`
--

DROP TABLE IF EXISTS `forum_entries_issues`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `forum_entries_issues` (
  `topic_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `issue_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  PRIMARY KEY (`topic_id`,`issue_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `forum_favorites`
--

DROP TABLE IF EXISTS `forum_favorites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `forum_favorites` (
  `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `topic_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  PRIMARY KEY (`user_id`,`topic_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `forum_likes`
--

DROP TABLE IF EXISTS `forum_likes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `forum_likes` (
  `topic_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  PRIMARY KEY (`topic_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `forum_visits`
--

DROP TABLE IF EXISTS `forum_visits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `forum_visits` (
  `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `seminar_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `visitdate` int unsigned NOT NULL,
  `last_visitdate` int unsigned NOT NULL,
  PRIMARY KEY (`user_id`,`seminar_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `global_resource_locks`
--

DROP TABLE IF EXISTS `global_resource_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `global_resource_locks` (
  `lock_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `begin` int unsigned NOT NULL DEFAULT '0',
  `end` int unsigned NOT NULL DEFAULT '0',
  `type` varchar(15) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `mkdate` int unsigned NOT NULL DEFAULT '0',
  `chdate` int unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`lock_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `globalsearch_buzzwords`
--

DROP TABLE IF EXISTS `globalsearch_buzzwords`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `globalsearch_buzzwords` (
  `id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `rights` enum('user','autor','tutor','dozent','admin','root') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'user',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `buzzwords` varchar(2048) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `subtitle` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `url` varchar(2048) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `grading_definitions`
--

DROP TABLE IF EXISTS `grading_definitions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `grading_definitions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `course_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `item` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tool` varchar(64) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `category` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `position` int NOT NULL DEFAULT '0',
  `weight` float unsigned NOT NULL,
  `mkdate` int unsigned NOT NULL,
  `chdate` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `course_id` (`course_id`),
  KEY `tool` (`tool`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `grading_instances`
--

DROP TABLE IF EXISTS `grading_instances`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `grading_instances` (
  `definition_id` int NOT NULL AUTO_INCREMENT,
  `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `rawgrade` decimal(6,5) unsigned NOT NULL,
  `feedback` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mkdate` int unsigned NOT NULL,
  `chdate` int unsigned NOT NULL,
  PRIMARY KEY (`definition_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `help_content`
--

DROP TABLE IF EXISTS `help_content`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `help_content` (
  `global_content_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `content_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `language` char(2) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT 'de',
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `route` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `studip_version` varchar(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `position` tinyint NOT NULL DEFAULT '1',
  `custom` tinyint unsigned NOT NULL DEFAULT '0',
  `visible` tinyint unsigned NOT NULL DEFAULT '1',
  `author_email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `installation_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `mkdate` int unsigned NOT NULL,
  `chdate` int unsigned NOT NULL,
  `comment` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`content_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `help_tour_audiences`
--

DROP TABLE IF EXISTS `help_tour_audiences`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `help_tour_audiences` (
  `tour_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `range_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `type` enum('inst','sem','studiengang','abschluss','userdomain','tour') CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `mkdate` int unsigned DEFAULT NULL,
  `chdate` int unsigned DEFAULT NULL,
  PRIMARY KEY (`tour_id`,`range_id`,`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `help_tour_settings`
--

DROP TABLE IF EXISTS `help_tour_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `help_tour_settings` (
  `tour_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `active` tinyint unsigned NOT NULL,
  `access` enum('standard','link','autostart','autostart_once') CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `mkdate` int unsigned DEFAULT NULL,
  `chdate` int unsigned DEFAULT NULL,
  PRIMARY KEY (`tour_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `help_tour_steps`
--

DROP TABLE IF EXISTS `help_tour_steps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `help_tour_steps` (
  `tour_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `step` tinyint NOT NULL DEFAULT '1',
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `tip` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `orientation` enum('T','TL','TR','L','LT','LB','B','BL','BR','R','RT','RB') CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT 'B',
  `interactive` tinyint unsigned NOT NULL,
  `css_selector` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `route` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `action_prev` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `action_next` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `author_email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `mkdate` int unsigned NOT NULL DEFAULT '0',
  `chdate` int unsigned NOT NULL,
  PRIMARY KEY (`tour_id`,`step`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `help_tour_user`
--

DROP TABLE IF EXISTS `help_tour_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `help_tour_user` (
  `tour_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `step_nr` int NOT NULL,
  `completed` tinyint unsigned NOT NULL DEFAULT '0',
  `mkdate` int unsigned DEFAULT NULL,
  `chdate` int unsigned DEFAULT NULL,
  PRIMARY KEY (`tour_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `help_tours`
--

DROP TABLE IF EXISTS `help_tours`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `help_tours` (
  `global_tour_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `tour_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('tour','wizard') CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `roles` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `version` int unsigned NOT NULL DEFAULT '1',
  `language` char(2) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT 'de',
  `studip_version` varchar(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `installation_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'demo-installation',
  `author_email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `mkdate` int unsigned NOT NULL DEFAULT '0',
  `chdate` int unsigned NOT NULL,
  PRIMARY KEY (`tour_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `i18n`
--

DROP TABLE IF EXISTS `i18n`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `i18n` (
  `object_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `table` varchar(64) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `field` varchar(128) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `lang` varchar(5) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`object_id`,`table`,`field`,`lang`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `institute_plan_columns`
--

DROP TABLE IF EXISTS `institute_plan_columns`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `institute_plan_columns` (
  `range_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `column` int NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `visible` tinyint unsigned NOT NULL DEFAULT '1',
  `mkdate` int unsigned NOT NULL,
  `chdate` int unsigned NOT NULL,
  PRIMARY KEY (`range_id`,`column`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kategorien`
--

DROP TABLE IF EXISTS `kategorien`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kategorien` (
  `kategorie_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `range_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `mkdate` int unsigned NOT NULL DEFAULT '0',
  `chdate` int unsigned NOT NULL DEFAULT '0',
  `priority` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`kategorie_id`),
  KEY `priority` (`priority`),
  KEY `range_id` (`range_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `licenses`
--

DROP TABLE IF EXISTS `licenses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `licenses` (
  `identifier` varchar(64) NOT NULL COMMENT 'According to SPDX standard if able.',
  `name` varchar(128) DEFAULT NULL,
  `link` varchar(256) DEFAULT NULL,
  `default` tinyint(1) DEFAULT '0',
  `description` text,
  `twillo_licensekey` varchar(16) DEFAULT NULL,
  `twillo_cclicenseversion` varchar(8) DEFAULT NULL,
  `chdate` int DEFAULT NULL,
  `mkdate` int DEFAULT NULL,
  PRIMARY KEY (`identifier`),
  KEY `default` (`default`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `limitedadmissions`
--

DROP TABLE IF EXISTS `limitedadmissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `limitedadmissions` (
  `rule_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_time` int unsigned NOT NULL DEFAULT '0',
  `end_time` int unsigned NOT NULL DEFAULT '0',
  `maxnumber` int NOT NULL DEFAULT '0',
  `mkdate` int unsigned NOT NULL DEFAULT '0',
  `chdate` int unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`rule_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `lock_rules`
--

DROP TABLE IF EXISTS `lock_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lock_rules` (
  `lock_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `permission` enum('autor','tutor','dozent','admin','root') CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT 'dozent',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `attributes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `object_type` enum('sem','inst','user') CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT 'sem',
  `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `mkdate` int unsigned DEFAULT NULL,
  `chdate` int unsigned DEFAULT NULL,
  PRIMARY KEY (`lock_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `lockedadmissions`
--

DROP TABLE IF EXISTS `lockedadmissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lockedadmissions` (
  `rule_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `mkdate` int unsigned NOT NULL DEFAULT '0',
  `chdate` int unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`rule_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `log_actions`
--

DROP TABLE IF EXISTS `log_actions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `log_actions` (
  `action_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `name` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `description` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `info_template` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `active` tinyint unsigned NOT NULL DEFAULT '1',
  `expires` int unsigned NOT NULL DEFAULT '0',
  `filename` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `class` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` enum('core','plugin','file') CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `mkdate` int unsigned DEFAULT NULL,
  `chdate` int unsigned DEFAULT NULL,
  PRIMARY KEY (`action_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `log_events`
--

DROP TABLE IF EXISTS `log_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `log_events` (
  `event_id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `action_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `affected_range_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `coaffected_range_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `info` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `dbg_info` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `mkdate` int unsigned NOT NULL,
  PRIMARY KEY (`event_id`),
  KEY `action_id` (`action_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `loginbackgrounds`
--

DROP TABLE IF EXISTS `loginbackgrounds`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `loginbackgrounds` (
  `background_id` int NOT NULL AUTO_INCREMENT,
  `filename` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `mobile` tinyint unsigned NOT NULL DEFAULT '1',
  `desktop` tinyint unsigned NOT NULL DEFAULT '1',
  `in_release` tinyint unsigned NOT NULL DEFAULT '0',
  `mkdate` int unsigned DEFAULT NULL,
  `chdate` int unsigned DEFAULT NULL,
  PRIMARY KEY (`background_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `lti_data`
--

DROP TABLE IF EXISTS `lti_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lti_data` (
  `id` int NOT NULL AUTO_INCREMENT,
  `position` int NOT NULL DEFAULT '0',
  `course_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tool_id` int NOT NULL DEFAULT '0',
  `launch_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `mkdate` int unsigned NOT NULL DEFAULT '0',
  `chdate` int unsigned NOT NULL DEFAULT '0',
  `options` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `course_id` (`course_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `lti_grade`
--

DROP TABLE IF EXISTS `lti_grade`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lti_grade` (
  `link_id` int NOT NULL DEFAULT '0',
  `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `score` float NOT NULL DEFAULT '0',
  `mkdate` int unsigned NOT NULL DEFAULT '0',
  `chdate` int unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`link_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `lti_tool`
--

DROP TABLE IF EXISTS `lti_tool`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lti_tool` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `launch_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `consumer_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `consumer_secret` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `custom_parameters` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `allow_custom_url` tinyint unsigned NOT NULL DEFAULT '0',
  `deep_linking` tinyint unsigned NOT NULL DEFAULT '0',
  `send_lis_person` tinyint unsigned NOT NULL DEFAULT '0',
  `mkdate` int unsigned NOT NULL DEFAULT '0',
  `chdate` int unsigned NOT NULL DEFAULT '0',
  `oauth_signature_method` varchar(10) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT 'sha1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mail_queue_entries`
--

DROP TABLE IF EXISTS `mail_queue_entries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mail_queue_entries` (
  `mail_queue_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `mail` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `message_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `tries` int unsigned NOT NULL,
  `last_try` int unsigned NOT NULL DEFAULT '0',
  `mkdate` int unsigned NOT NULL,
  `chdate` int unsigned NOT NULL,
  PRIMARY KEY (`mail_queue_id`),
  KEY `message_id` (`message_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `media_cache`
--

DROP TABLE IF EXISTS `media_cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `media_cache` (
  `id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `type` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `chdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `expires` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `message`
--

DROP TABLE IF EXISTS `message`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `message` (
  `message_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `autor_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `subject` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `show_adressees` tinyint unsigned NOT NULL DEFAULT '0',
  `mkdate` int unsigned NOT NULL DEFAULT '0',
  `priority` enum('normal','high') CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT 'normal',
  PRIMARY KEY (`message_id`),
  KEY `autor_id` (`autor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `message_tags`
--

DROP TABLE IF EXISTS `message_tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `message_tags` (
  `message_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `tag` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `chdate` int unsigned NOT NULL,
  `mkdate` int unsigned NOT NULL,
  PRIMARY KEY (`message_id`,`user_id`,`tag`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `message_user`
--

DROP TABLE IF EXISTS `message_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `message_user` (
  `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `message_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `readed` tinyint unsigned NOT NULL DEFAULT '0',
  `deleted` tinyint unsigned NOT NULL DEFAULT '0',
  `snd_rec` enum('rec','snd') CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT 'rec',
  `answered` tinyint unsigned NOT NULL DEFAULT '0',
  `mkdate` int unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`message_id`,`snd_rec`,`user_id`),
  KEY `user_id` (`user_id`,`snd_rec`,`deleted`,`readed`,`mkdate`),
  KEY `user_id_2` (`user_id`,`snd_rec`,`deleted`,`mkdate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mvv_abschl_kategorie`
--

DROP TABLE IF EXISTS `mvv_abschl_kategorie`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mvv_abschl_kategorie` (
  `kategorie_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_kurz` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `beschreibung` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `position` int DEFAULT NULL,
  `author_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `editor_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `mkdate` int unsigned NOT NULL,
  `chdate` int unsigned NOT NULL,
  PRIMARY KEY (`kategorie_id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mvv_abschl_zuord`
--

DROP TABLE IF EXISTS `mvv_abschl_zuord`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mvv_abschl_zuord` (
  `abschluss_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `kategorie_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `position` int NOT NULL DEFAULT '9999',
  `author_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `editor_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `mkdate` int unsigned NOT NULL,
  `chdate` int unsigned NOT NULL,
  PRIMARY KEY (`abschluss_id`),
  KEY `kategorie_id` (`kategorie_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mvv_aufbaustudiengang`
--

DROP TABLE IF EXISTS `mvv_aufbaustudiengang`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mvv_aufbaustudiengang` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `grund_stg_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `aufbau_stg_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `typ` varchar(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `kommentar` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `author_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `editor_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `mkdate` int unsigned NOT NULL,
  `chdate` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `grund_stg_id` (`grund_stg_id`,`aufbau_stg_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mvv_contacts`
--

DROP TABLE IF EXISTS `mvv_contacts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mvv_contacts` (
  `contact_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `contact_status` enum('intern','extern','institution') CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `alt_mail` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `author_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `editor_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `mkdate` int unsigned NOT NULL,
  `chdate` int unsigned NOT NULL,
  PRIMARY KEY (`contact_id`),
  KEY `contact_status` (`contact_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mvv_contacts_ranges`
--

DROP TABLE IF EXISTS `mvv_contacts_ranges`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mvv_contacts_ranges` (
  `contact_range_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `contact_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `range_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `range_type` enum('Modul','Studiengang','StudiengangTeil') CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `type` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `position` int DEFAULT NULL,
  `author_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `editor_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `mkdate` int unsigned NOT NULL,
  `chdate` int unsigned NOT NULL,
  PRIMARY KEY (`contact_range_id`),
  KEY `range_id` (`range_id`),
  KEY `range_type` (`range_type`),
  KEY `type` (`type`),
  KEY `category_range` (`category`,`range_id`),
  KEY `contact_id` (`contact_id`,`range_id`,`category`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mvv_extern_contacts`
--

DROP TABLE IF EXISTS `mvv_extern_contacts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mvv_extern_contacts` (
  `extern_contact_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `vorname` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `homepage` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `mail` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tel` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `author_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `editor_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `mkdate` int unsigned NOT NULL,
  `chdate` int unsigned NOT NULL,
  PRIMARY KEY (`extern_contact_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mvv_fach_inst`
--

DROP TABLE IF EXISTS `mvv_fach_inst`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mvv_fach_inst` (
  `fach_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `institut_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `position` int NOT NULL,
  `author_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `editor_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `mkdate` int unsigned NOT NULL,
  `chdate` int unsigned NOT NULL,
  PRIMARY KEY (`fach_id`,`institut_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mvv_files`
--

DROP TABLE IF EXISTS `mvv_files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mvv_files` (
  `mvvfile_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `year` int DEFAULT NULL,
  `type` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `tags` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `extern_visible` tinyint unsigned DEFAULT NULL,
  `author_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `editor_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `mkdate` int unsigned NOT NULL,
  `chdate` int unsigned NOT NULL,
  PRIMARY KEY (`mvvfile_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mvv_files_filerefs`
--

DROP TABLE IF EXISTS `mvv_files_filerefs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mvv_files_filerefs` (
  `mvvfile_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `file_language` varchar(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `name` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `fileref_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `author_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `editor_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `mkdate` int unsigned NOT NULL,
  `chdate` int unsigned NOT NULL,
  PRIMARY KEY (`mvvfile_id`,`file_language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mvv_files_ranges`
--

DROP TABLE IF EXISTS `mvv_files_ranges`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mvv_files_ranges` (
  `mvvfile_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `range_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `range_type` varchar(32) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `position` int DEFAULT NULL,
  `author_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `editor_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `mkdate` int unsigned NOT NULL,
  `chdate` int unsigned NOT NULL,
  PRIMARY KEY (`mvvfile_id`,`range_id`),
  KEY `range_id` (`range_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mvv_lvgruppe`
--

DROP TABLE IF EXISTS `mvv_lvgruppe`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mvv_lvgruppe` (
  `lvgruppe_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `name` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `alttext` tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `author_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `editor_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `mkdate` int unsigned NOT NULL,
  `chdate` int unsigned NOT NULL,
  PRIMARY KEY (`lvgruppe_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mvv_lvgruppe_modulteil`
--

DROP TABLE IF EXISTS `mvv_lvgruppe_modulteil`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mvv_lvgruppe_modulteil` (
  `lvgruppe_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `modulteil_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `position` int NOT NULL DEFAULT '9999',
  `fn_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `author_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `editor_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `mkdate` int unsigned NOT NULL,
  `chdate` int unsigned NOT NULL,
  PRIMARY KEY (`lvgruppe_id`,`modulteil_id`),
  KEY `fn_id` (`fn_id`),
  KEY `modulteil_id` (`modulteil_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mvv_lvgruppe_seminar`
--

DROP TABLE IF EXISTS `mvv_lvgruppe_seminar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mvv_lvgruppe_seminar` (
  `lvgruppe_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `seminar_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `author_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `editor_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `mkdate` int unsigned NOT NULL,
  `chdate` int unsigned NOT NULL,
  PRIMARY KEY (`lvgruppe_id`,`seminar_id`),
  KEY `seminar_id` (`seminar_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mvv_modul`
--

DROP TABLE IF EXISTS `mvv_modul`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mvv_modul` (
  `modul_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `quelle` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `variante` varchar(32) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `flexnow_modul` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `code` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `start` varchar(32) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `end` varchar(32) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `beschlussdatum` int unsigned DEFAULT NULL,
  `fassung_nr` int DEFAULT NULL,
  `fassung_typ` varchar(32) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `version` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1',
  `dauer` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `kapazitaet` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `kp` double(5,2) DEFAULT NULL,
  `wl_selbst` int DEFAULT NULL,
  `wl_pruef` int DEFAULT NULL,
  `pruef_ebene` varchar(32) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `faktor_note` varchar(10) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '1',
  `stat` varchar(32) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `kommentar_status` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `verantwortlich` tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `author_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `editor_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `mkdate` int unsigned NOT NULL,
  `chdate` int unsigned NOT NULL,
  PRIMARY KEY (`modul_id`),
  KEY `stat` (`stat`),
  KEY `flexnow_modul` (`flexnow_modul`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mvv_modul_deskriptor`
--

DROP TABLE IF EXISTS `mvv_modul_deskriptor`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mvv_modul_deskriptor` (
  `deskriptor_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `modul_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `verantwortlich` tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `bezeichnung` tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `voraussetzung` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `kompetenzziele` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `inhalte` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `literatur` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `links` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `kommentar` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `turnus` tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `kommentar_kapazitaet` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `kommentar_sws` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `kommentar_wl_selbst` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `kommentar_wl_pruef` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `kommentar_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `pruef_vorleistung` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `pruef_leistung` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `pruef_wiederholung` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `ersatztext` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `author_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `editor_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `mkdate` int unsigned NOT NULL,
  `chdate` int unsigned NOT NULL,
  PRIMARY KEY (`deskriptor_id`),
  UNIQUE KEY `modul_id` (`modul_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mvv_modul_inst`
--

DROP TABLE IF EXISTS `mvv_modul_inst`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mvv_modul_inst` (
  `modul_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `institut_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `gruppe` varchar(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `position` int NOT NULL DEFAULT '9999',
  `author_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `editor_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `mkdate` int unsigned NOT NULL,
  `chdate` int unsigned NOT NULL,
  PRIMARY KEY (`modul_id`,`institut_id`),
  KEY `institut_id` (`institut_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mvv_modul_language`
--

DROP TABLE IF EXISTS `mvv_modul_language`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mvv_modul_language` (
  `modul_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `lang` varchar(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `position` int NOT NULL DEFAULT '9999',
  `author_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `editor_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `mkdate` int unsigned NOT NULL,
  `chdate` int unsigned NOT NULL,
  PRIMARY KEY (`modul_id`,`lang`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mvv_modulteil`
--

DROP TABLE IF EXISTS `mvv_modulteil`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mvv_modulteil` (
  `modulteil_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `modul_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `position` int NOT NULL DEFAULT '9999',
  `flexnow_modul` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nummer` varchar(20) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `num_bezeichnung` varchar(32) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `lernlehrform` varchar(32) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `semester` varchar(32) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `kapazitaet` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `kp` double(5,2) DEFAULT NULL,
  `sws` int DEFAULT NULL,
  `wl_praesenz` int DEFAULT NULL,
  `wl_bereitung` int DEFAULT NULL,
  `wl_selbst` int DEFAULT NULL,
  `wl_pruef` int DEFAULT NULL,
  `anteil_note` int DEFAULT NULL,
  `ausgleichbar` tinyint unsigned NOT NULL DEFAULT '0',
  `pflicht` tinyint unsigned NOT NULL DEFAULT '0',
  `author_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `editor_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `mkdate` int unsigned NOT NULL,
  `chdate` int unsigned NOT NULL,
  PRIMARY KEY (`modulteil_id`),
  KEY `modul_id` (`modul_id`),
  KEY `flexnow_modul` (`flexnow_modul`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mvv_modulteil_deskriptor`
--

DROP TABLE IF EXISTS `mvv_modulteil_deskriptor`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mvv_modulteil_deskriptor` (
  `deskriptor_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `modulteil_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `bezeichnung` tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `voraussetzung` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `kommentar` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `kommentar_kapazitaet` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `kommentar_wl_praesenz` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `kommentar_wl_bereitung` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `kommentar_wl_selbst` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `kommentar_wl_pruef` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `pruef_vorleistung` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `pruef_leistung` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `kommentar_pflicht` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `author_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `editor_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `mkdate` int unsigned NOT NULL,
  `chdate` int unsigned NOT NULL,
  PRIMARY KEY (`deskriptor_id`),
  KEY `modulteil_id` (`modulteil_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mvv_modulteil_language`
--

DROP TABLE IF EXISTS `mvv_modulteil_language`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mvv_modulteil_language` (
  `modulteil_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `lang` varchar(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `position` int NOT NULL DEFAULT '9999',
  `author_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `editor_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `mkdate` int unsigned NOT NULL,
  `chdate` int unsigned NOT NULL,
  PRIMARY KEY (`modulteil_id`,`lang`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mvv_modulteil_stgteilabschnitt`
--

DROP TABLE IF EXISTS `mvv_modulteil_stgteilabschnitt`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mvv_modulteil_stgteilabschnitt` (
  `modulteil_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `abschnitt_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `fachsemester` int NOT NULL,
  `differenzierung` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `author_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `editor_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `mkdate` int unsigned NOT NULL,
  `chdate` int unsigned NOT NULL,
  PRIMARY KEY (`modulteil_id`,`abschnitt_id`,`fachsemester`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mvv_ovl_conflicts`
--

DROP TABLE IF EXISTS `mvv_ovl_conflicts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mvv_ovl_conflicts` (
  `conflict_id` int unsigned NOT NULL AUTO_INCREMENT,
  `selection_id` int NOT NULL,
  `base_abschnitt_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `base_modulteil_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `base_course_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `base_metadate_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `comp_abschnitt_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `comp_modulteil_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `comp_course_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `comp_metadate_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `mkdate` int unsigned DEFAULT NULL,
  `chdate` int unsigned DEFAULT NULL,
  PRIMARY KEY (`conflict_id`),
  KEY `selection_id` (`selection_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mvv_ovl_excludes`
--

DROP TABLE IF EXISTS `mvv_ovl_excludes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mvv_ovl_excludes` (
  `selection_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `course_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `mkdate` int unsigned DEFAULT NULL,
  `chdate` int unsigned DEFAULT NULL,
  PRIMARY KEY (`selection_id`,`course_id`),
  KEY `course_id` (`course_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mvv_ovl_selections`
--

DROP TABLE IF EXISTS `mvv_ovl_selections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mvv_ovl_selections` (
  `id` int NOT NULL AUTO_INCREMENT,
  `selection_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `semester_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `base_version_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `comp_version_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `fachsems` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `semtypes` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `show_excluded` tinyint unsigned NOT NULL DEFAULT '0',
  `mkdate` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `selection_id` (`selection_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mvv_stg_stgteil`
--

DROP TABLE IF EXISTS `mvv_stg_stgteil`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mvv_stg_stgteil` (
  `studiengang_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `stgteil_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `stgteil_bez_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `position` int NOT NULL,
  `author_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `editor_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `mkdate` int unsigned NOT NULL,
  `chdate` int unsigned NOT NULL,
  PRIMARY KEY (`studiengang_id`,`stgteil_id`,`stgteil_bez_id`),
  KEY `stgteil_id` (`stgteil_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mvv_stgteil`
--

DROP TABLE IF EXISTS `mvv_stgteil`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mvv_stgteil` (
  `stgteil_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `fach_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `kp` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `semester` int DEFAULT NULL,
  `zusatz` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `author_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `editor_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `mkdate` int unsigned NOT NULL,
  `chdate` int unsigned NOT NULL,
  PRIMARY KEY (`stgteil_id`),
  KEY `fach_id` (`fach_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mvv_stgteil_bez`
--

DROP TABLE IF EXISTS `mvv_stgteil_bez`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mvv_stgteil_bez` (
  `stgteil_bez_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_kurz` varchar(20) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `position` int NOT NULL DEFAULT '9999',
  `author_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `editor_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `mkdate` int unsigned NOT NULL,
  `chdate` int unsigned NOT NULL,
  PRIMARY KEY (`stgteil_bez_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mvv_stgteilabschnitt`
--

DROP TABLE IF EXISTS `mvv_stgteilabschnitt`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mvv_stgteilabschnitt` (
  `abschnitt_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `version_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `position` int NOT NULL DEFAULT '9999',
  `name` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `kommentar` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `kp` double(5,2) DEFAULT NULL,
  `ueberschrift` tinytext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `author_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `editor_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `mkdate` int unsigned NOT NULL,
  `chdate` int unsigned NOT NULL,
  PRIMARY KEY (`abschnitt_id`),
  KEY `version_id` (`version_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mvv_stgteilabschnitt_modul`
--

DROP TABLE IF EXISTS `mvv_stgteilabschnitt_modul`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mvv_stgteilabschnitt_modul` (
  `abschnitt_modul_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `abschnitt_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `modul_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `flexnow_modul` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `modulcode` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `position` int NOT NULL DEFAULT '9999',
  `bezeichnung` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `author_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `editor_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `mkdate` int unsigned NOT NULL,
  `chdate` int unsigned NOT NULL,
  PRIMARY KEY (`abschnitt_modul_id`),
  UNIQUE KEY `abschnitt_id` (`abschnitt_id`,`modul_id`) USING BTREE,
  KEY `flexnow_modul` (`flexnow_modul`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mvv_stgteilversion`
--

DROP TABLE IF EXISTS `mvv_stgteilversion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mvv_stgteilversion` (
  `version_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `stgteil_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `start_sem` char(32) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `end_sem` char(32) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `code` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `beschlussdatum` int unsigned DEFAULT NULL,
  `fassung_nr` int DEFAULT NULL,
  `fassung_typ` varchar(32) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `beschreibung` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `stat` varchar(32) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `kommentar_status` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `author_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `editor_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `mkdate` int unsigned NOT NULL,
  `chdate` int unsigned NOT NULL,
  PRIMARY KEY (`version_id`),
  KEY `stgteil_id` (`stgteil_id`),
  KEY `stat` (`stat`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mvv_studiengang`
--

DROP TABLE IF EXISTS `mvv_studiengang`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mvv_studiengang` (
  `studiengang_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `abschluss_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `typ` enum('einfach','mehrfach') CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_kurz` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `beschreibung` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `institut_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `start` char(32) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `end` char(32) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `beschlussdatum` int unsigned DEFAULT NULL,
  `fassung_nr` int DEFAULT NULL,
  `fassung_typ` varchar(32) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `stat` varchar(32) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `kommentar_status` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `schlagworte` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `studienzeit` tinyint unsigned DEFAULT NULL,
  `studienplaetze` int unsigned DEFAULT NULL,
  `abschlussgrad` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `enroll` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `author_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `editor_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `mkdate` int unsigned NOT NULL,
  `chdate` int unsigned NOT NULL,
  PRIMARY KEY (`studiengang_id`),
  KEY `abschluss_id` (`abschluss_id`),
  KEY `institut_id` (`institut_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mvv_studycourse_language`
--

DROP TABLE IF EXISTS `mvv_studycourse_language`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mvv_studycourse_language` (
  `studiengang_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `lang` varchar(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `position` int NOT NULL DEFAULT '9999',
  `author_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `editor_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `mkdate` int unsigned NOT NULL,
  `chdate` int unsigned NOT NULL,
  PRIMARY KEY (`studiengang_id`,`lang`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mvv_studycourse_type`
--

DROP TABLE IF EXISTS `mvv_studycourse_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mvv_studycourse_type` (
  `studiengang_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `type` varchar(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `author_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `editor_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `mkdate` int unsigned NOT NULL,
  `chdate` int unsigned NOT NULL,
  PRIMARY KEY (`studiengang_id`,`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `news`
--

DROP TABLE IF EXISTS `news`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `news` (
  `news_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `topic` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `body` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `author` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `date` int unsigned NOT NULL DEFAULT '0',
  `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `expire` int unsigned NOT NULL DEFAULT '0',
  `allow_comments` tinyint unsigned NOT NULL DEFAULT '0',
  `prio` tinyint NOT NULL DEFAULT '0',
  `chdate` int unsigned NOT NULL DEFAULT '0',
  `chdate_uid` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `mkdate` int unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`news_id`),
  KEY `date` (`date`),
  KEY `chdate` (`chdate`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `news_range`
--

DROP TABLE IF EXISTS `news_range`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `news_range` (
  `news_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `range_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `mkdate` int unsigned DEFAULT NULL,
  `chdate` int unsigned DEFAULT NULL,
  PRIMARY KEY (`news_id`,`range_id`),
  KEY `range_id` (`range_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `news_roles`
--

DROP TABLE IF EXISTS `news_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `news_roles` (
  `news_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `roleid` int NOT NULL,
  PRIMARY KEY (`news_id`,`roleid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `news_rss_range`
--

DROP TABLE IF EXISTS `news_rss_range`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `news_rss_range` (
  `range_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `rss_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `range_type` enum('user','sem','inst','global') CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT 'user',
  PRIMARY KEY (`range_id`),
  KEY `rss_id` (`rss_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `oauth2_access_tokens`
--

DROP TABLE IF EXISTS `oauth2_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `oauth2_access_tokens` (
  `id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `client_id` bigint unsigned NOT NULL,
  `scopes` text COLLATE utf8mb4_unicode_ci,
  `revoked` tinyint(1) NOT NULL DEFAULT '0',
  `expires_at` int DEFAULT NULL,
  `mkdate` int NOT NULL,
  `chdate` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `oauth2_auth_codes`
--

DROP TABLE IF EXISTS `oauth2_auth_codes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `oauth2_auth_codes` (
  `id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `client_id` bigint unsigned NOT NULL,
  `scopes` text COLLATE utf8mb4_unicode_ci,
  `revoked` tinyint(1) NOT NULL DEFAULT '0',
  `expires_at` int DEFAULT NULL,
  `mkdate` int NOT NULL,
  `chdate` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `oauth2_clients`
--

DROP TABLE IF EXISTS `oauth2_clients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `oauth2_clients` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `secret` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `redirect` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `revoked` tinyint(1) NOT NULL DEFAULT '0',
  `description` text COLLATE utf8mb4_unicode_ci,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `homepage` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `admin_notes` text COLLATE utf8mb4_unicode_ci,
  `mkdate` int NOT NULL,
  `chdate` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `oauth2_refresh_tokens`
--

DROP TABLE IF EXISTS `oauth2_refresh_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `oauth2_refresh_tokens` (
  `id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `access_token_id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `revoked` tinyint(1) NOT NULL DEFAULT '0',
  `expires_at` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `access_token_id` (`access_token_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `oauth_consumer_registry`
--

DROP TABLE IF EXISTS `oauth_consumer_registry`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `oauth_consumer_registry` (
  `ocr_id` int NOT NULL AUTO_INCREMENT,
  `ocr_usa_id_ref` int DEFAULT NULL,
  `ocr_consumer_key` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `ocr_consumer_secret` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `ocr_signature_methods` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'HMAC-SHA1,PLAINTEXT',
  `ocr_server_uri` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ocr_server_uri_host` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ocr_server_uri_path` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `ocr_request_token_uri` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ocr_authorize_uri` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ocr_access_token_uri` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ocr_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ocr_id`),
  UNIQUE KEY `ocr_consumer_key` (`ocr_consumer_key`,`ocr_usa_id_ref`,`ocr_server_uri`),
  KEY `ocr_server_uri` (`ocr_server_uri`),
  KEY `ocr_server_uri_host` (`ocr_server_uri_host`,`ocr_server_uri_path`),
  KEY `ocr_usa_id_ref` (`ocr_usa_id_ref`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `oauth_consumer_token`
--

DROP TABLE IF EXISTS `oauth_consumer_token`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `oauth_consumer_token` (
  `oct_id` int NOT NULL AUTO_INCREMENT,
  `oct_ocr_id_ref` int NOT NULL,
  `oct_usa_id_ref` int NOT NULL,
  `oct_name` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
  `oct_token` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `oct_token_secret` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `oct_token_type` enum('request','authorized','access') CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `oct_token_ttl` datetime NOT NULL DEFAULT '9999-12-31 00:00:00',
  `oct_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`oct_id`),
  UNIQUE KEY `oct_ocr_id_ref` (`oct_ocr_id_ref`,`oct_token`),
  UNIQUE KEY `oct_usa_id_ref` (`oct_usa_id_ref`,`oct_ocr_id_ref`,`oct_token_type`,`oct_name`),
  KEY `oct_token_ttl` (`oct_token_ttl`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `oauth_log`
--

DROP TABLE IF EXISTS `oauth_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `oauth_log` (
  `olg_id` int NOT NULL AUTO_INCREMENT,
  `olg_osr_consumer_key` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `olg_ost_token` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `olg_ocr_consumer_key` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `olg_oct_token` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `olg_usa_id_ref` int DEFAULT NULL,
  `olg_received` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `olg_sent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `olg_base_string` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `olg_notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `olg_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `olg_remote_ip` bigint NOT NULL,
  PRIMARY KEY (`olg_id`),
  KEY `olg_osr_consumer_key` (`olg_osr_consumer_key`,`olg_id`),
  KEY `olg_ost_token` (`olg_ost_token`,`olg_id`),
  KEY `olg_ocr_consumer_key` (`olg_ocr_consumer_key`,`olg_id`),
  KEY `olg_oct_token` (`olg_oct_token`,`olg_id`),
  KEY `olg_usa_id_ref` (`olg_usa_id_ref`,`olg_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `oauth_server_nonce`
--

DROP TABLE IF EXISTS `oauth_server_nonce`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `oauth_server_nonce` (
  `osn_id` int NOT NULL AUTO_INCREMENT,
  `osn_consumer_key` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `osn_token` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `osn_timestamp` bigint NOT NULL,
  `osn_nonce` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  PRIMARY KEY (`osn_id`),
  UNIQUE KEY `osn_consumer_key` (`osn_consumer_key`,`osn_token`,`osn_timestamp`,`osn_nonce`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `oauth_server_registry`
--

DROP TABLE IF EXISTS `oauth_server_registry`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `oauth_server_registry` (
  `osr_id` int NOT NULL AUTO_INCREMENT,
  `osr_usa_id_ref` int DEFAULT NULL,
  `osr_consumer_key` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `osr_consumer_secret` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `osr_enabled` tinyint(1) NOT NULL DEFAULT '1',
  `osr_status` varchar(16) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `osr_requester_name` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `osr_requester_email` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `osr_callback_uri` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `osr_application_uri` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `osr_application_title` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `osr_application_descr` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `osr_application_notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `osr_application_type` varchar(20) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `osr_application_commercial` tinyint(1) NOT NULL DEFAULT '0',
  `osr_issue_date` datetime NOT NULL,
  `osr_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`osr_id`),
  UNIQUE KEY `osr_consumer_key` (`osr_consumer_key`),
  KEY `osr_usa_id_ref` (`osr_usa_id_ref`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `oauth_server_token`
--

DROP TABLE IF EXISTS `oauth_server_token`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `oauth_server_token` (
  `ost_id` int NOT NULL AUTO_INCREMENT,
  `ost_osr_id_ref` int NOT NULL,
  `ost_usa_id_ref` int NOT NULL,
  `ost_token` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `ost_token_secret` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `ost_token_type` enum('request','access') CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `ost_authorized` tinyint(1) NOT NULL DEFAULT '0',
  `ost_referrer_host` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `ost_token_ttl` datetime NOT NULL DEFAULT '9999-12-31 00:00:00',
  `ost_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `ost_verifier` char(10) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `ost_callback_url` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`ost_id`),
  UNIQUE KEY `ost_token` (`ost_token`),
  KEY `ost_osr_id_ref` (`ost_osr_id_ref`),
  KEY `ost_token_ttl` (`ost_token_ttl`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `object_contentmodules`
--

DROP TABLE IF EXISTS `object_contentmodules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `object_contentmodules` (
  `object_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `module_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `system_type` varchar(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `module_type` varchar(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `mkdate` int unsigned NOT NULL DEFAULT '0',
  `chdate` int unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`object_id`,`module_id`,`system_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `object_user_visits`
--

DROP TABLE IF EXISTS `object_user_visits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `object_user_visits` (
  `object_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `plugin_id` int NOT NULL,
  `visitdate` int unsigned NOT NULL DEFAULT '0',
  `last_visitdate` int unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`object_id`,`user_id`,`plugin_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `object_views`
--

DROP TABLE IF EXISTS `object_views`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `object_views` (
  `object_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `views` int unsigned NOT NULL DEFAULT '0',
  `chdate` int unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`object_id`),
  KEY `views` (`views`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `oer_abo`
--

DROP TABLE IF EXISTS `oer_abo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `oer_abo` (
  `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `material_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  UNIQUE KEY `user_id` (`user_id`,`material_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `oer_comments`
--

DROP TABLE IF EXISTS `oer_comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `oer_comments` (
  `comment_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `review_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `foreign_comment_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `comment` text NOT NULL,
  `host_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `chdate` bigint NOT NULL,
  `mkdate` bigint NOT NULL,
  PRIMARY KEY (`comment_id`),
  KEY `review_id` (`review_id`),
  KEY `foreign_comment_id` (`foreign_comment_id`),
  KEY `host_id` (`host_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `oer_downloadcounter`
--

DROP TABLE IF EXISTS `oer_downloadcounter`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `oer_downloadcounter` (
  `counter_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `material_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `longitude` double DEFAULT NULL,
  `latitude` double DEFAULT NULL,
  `mkdate` int DEFAULT NULL,
  PRIMARY KEY (`counter_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `oer_hosts`
--

DROP TABLE IF EXISTS `oer_hosts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `oer_hosts` (
  `host_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `sorm_class` varchar(50) NOT NULL DEFAULT 'OERHost',
  `name` varchar(64) NOT NULL,
  `url` varchar(200) NOT NULL,
  `public_key` text NOT NULL,
  `private_key` text,
  `active` tinyint NOT NULL DEFAULT '1',
  `index_server` tinyint NOT NULL DEFAULT '0',
  `allowed_as_index_server` tinyint NOT NULL DEFAULT '1',
  `last_updated` bigint NOT NULL,
  `chdate` bigint NOT NULL,
  `mkdate` bigint NOT NULL,
  PRIMARY KEY (`host_id`),
  UNIQUE KEY `url` (`url`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `oer_material`
--

DROP TABLE IF EXISTS `oer_material`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `oer_material` (
  `material_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `foreign_material_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `host_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `name` varchar(64) NOT NULL,
  `category` varchar(64) NOT NULL DEFAULT '',
  `draft` tinyint(1) NOT NULL DEFAULT '0',
  `filename` varchar(64) NOT NULL,
  `short_description` varchar(100) DEFAULT NULL,
  `description` text NOT NULL,
  `difficulty_start` tinyint NOT NULL DEFAULT '1',
  `difficulty_end` tinyint NOT NULL DEFAULT '12',
  `player_url` varchar(256) DEFAULT NULL,
  `tool` varchar(128) DEFAULT NULL,
  `content_type` varchar(256) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `front_image_content_type` varchar(64) DEFAULT NULL,
  `structure` text,
  `rating` double DEFAULT NULL,
  `license_identifier` varchar(64) NOT NULL DEFAULT 'CC BY SA 3.0',
  `uri` varchar(1000) NOT NULL DEFAULT '',
  `uri_hash` char(32) NOT NULL DEFAULT '',
  `published_id_on_twillo` varchar(50) DEFAULT NULL,
  `source_url` varchar(256) DEFAULT NULL,
  `data` text,
  `chdate` bigint NOT NULL,
  `mkdate` int NOT NULL,
  PRIMARY KEY (`material_id`),
  KEY `host_id` (`host_id`),
  KEY `category` (`category`),
  KEY `foreign_material_id` (`foreign_material_id`),
  KEY `license_identifier` (`license_identifier`),
  KEY `uri_hash` (`uri_hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `oer_material_users`
--

DROP TABLE IF EXISTS `oer_material_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `oer_material_users` (
  `material_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `external_contact` int NOT NULL DEFAULT '0',
  `position` int NOT NULL DEFAULT '1',
  `chdate` int NOT NULL,
  `mkdate` int NOT NULL,
  PRIMARY KEY (`material_id`,`user_id`,`external_contact`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `oer_reviews`
--

DROP TABLE IF EXISTS `oer_reviews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `oer_reviews` (
  `review_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `material_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `foreign_review_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `host_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `rating` int NOT NULL,
  `review` text NOT NULL,
  `chdate` int NOT NULL,
  `mkdate` int NOT NULL,
  PRIMARY KEY (`review_id`),
  UNIQUE KEY `unique_users` (`user_id`,`host_id`,`material_id`),
  KEY `material_id` (`material_id`),
  KEY `foreign_review_id` (`foreign_review_id`),
  KEY `user_id` (`user_id`),
  KEY `host_id` (`host_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `oer_tags`
--

DROP TABLE IF EXISTS `oer_tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `oer_tags` (
  `tag_hash` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `name` varchar(64) NOT NULL,
  PRIMARY KEY (`tag_hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `oer_tags_material`
--

DROP TABLE IF EXISTS `oer_tags_material`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `oer_tags_material` (
  `material_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `tag_hash` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  UNIQUE KEY `unique_tags` (`material_id`,`tag_hash`),
  KEY `tag_hash` (`tag_hash`),
  KEY `material_id` (`material_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `oer_user`
--

DROP TABLE IF EXISTS `oer_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `oer_user` (
  `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `foreign_user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `host_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `name` varchar(100) NOT NULL,
  `avatar` varchar(256) DEFAULT NULL,
  `description` text,
  `chdate` int NOT NULL,
  `mkdate` int NOT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `unique_users` (`foreign_user_id`,`host_id`),
  KEY `foreign_user_id` (`foreign_user_id`),
  KEY `host_id` (`host_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `opengraphdata`
--

DROP TABLE IF EXISTS `opengraphdata`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `opengraphdata` (
  `opengraph_id` int unsigned NOT NULL AUTO_INCREMENT,
  `hash` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `url` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_opengraph` tinyint unsigned DEFAULT NULL,
  `title` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `image` varchar(1024) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `type` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `data` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_update` int unsigned NOT NULL,
  `chdate` int unsigned NOT NULL,
  `mkdate` int unsigned NOT NULL,
  PRIMARY KEY (`opengraph_id`),
  UNIQUE KEY `hash` (`hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `participantrestrictedadmissions`
--

DROP TABLE IF EXISTS `participantrestrictedadmissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `participantrestrictedadmissions` (
  `rule_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `distribution_time` int unsigned NOT NULL DEFAULT '0',
  `mkdate` int unsigned NOT NULL DEFAULT '0',
  `chdate` int unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`rule_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `passwordadmissions`
--

DROP TABLE IF EXISTS `passwordadmissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `passwordadmissions` (
  `rule_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `start_time` int unsigned NOT NULL DEFAULT '0',
  `end_time` int unsigned NOT NULL DEFAULT '0',
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mkdate` int unsigned NOT NULL DEFAULT '0',
  `chdate` int unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`rule_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `personal_notifications`
--

DROP TABLE IF EXISTS `personal_notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal_notifications` (
  `personal_notification_id` int NOT NULL AUTO_INCREMENT,
  `url` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `avatar` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `dialog` tinyint NOT NULL DEFAULT '0',
  `html_id` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `mkdate` int unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`personal_notification_id`),
  KEY `html_id` (`html_id`),
  KEY `url` (`url`(256))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `personal_notifications_user`
--

DROP TABLE IF EXISTS `personal_notifications_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal_notifications_user` (
  `personal_notification_id` int unsigned NOT NULL,
  `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `seen` tinyint unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`personal_notification_id`,`user_id`),
  KEY `user_id` (`user_id`,`seen`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `plugin_assets`
--

DROP TABLE IF EXISTS `plugin_assets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `plugin_assets` (
  `asset_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `plugin_id` int unsigned NOT NULL,
  `type` enum('css','js') CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `filename` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `storagename` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `size` int unsigned DEFAULT NULL,
  `mkdate` int unsigned NOT NULL DEFAULT '0',
  `chdate` int unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`asset_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `plugins`
--

DROP TABLE IF EXISTS `plugins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `plugins` (
  `pluginid` int unsigned NOT NULL AUTO_INCREMENT,
  `pluginclassname` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `pluginpath` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `pluginname` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `plugintype` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `enabled` enum('yes','no') CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT 'no',
  `navigationpos` int unsigned NOT NULL DEFAULT '0',
  `dependentonid` int unsigned DEFAULT NULL,
  `automatic_update_url` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `automatic_update_secret` varchar(32) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  PRIMARY KEY (`pluginid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `plugins_activated`
--

DROP TABLE IF EXISTS `plugins_activated`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `plugins_activated` (
  `pluginid` int unsigned NOT NULL DEFAULT '0',
  `range_type` enum('user') CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT 'user',
  `range_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `state` tinyint unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`pluginid`,`range_type`,`range_id`),
  KEY `range` (`range_id`,`range_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `prefadmission_condition`
--

DROP TABLE IF EXISTS `prefadmission_condition`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `prefadmission_condition` (
  `rule_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `condition_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `chance` int NOT NULL DEFAULT '1',
  `mkdate` int unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`rule_id`,`condition_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `prefadmissions`
--

DROP TABLE IF EXISTS `prefadmissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `prefadmissions` (
  `rule_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `favor_semester` tinyint unsigned NOT NULL DEFAULT '0',
  `mkdate` int unsigned NOT NULL DEFAULT '0',
  `chdate` int unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`rule_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `priorities`
--

DROP TABLE IF EXISTS `priorities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `priorities` (
  `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `set_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `seminar_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `priority` int NOT NULL DEFAULT '0',
  `mkdate` int unsigned NOT NULL DEFAULT '0',
  `chdate` int unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`,`set_id`,`seminar_id`),
  KEY `user_rule_priority` (`user_id`,`priority`,`set_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `questionnaire_anonymous_answers`
--

DROP TABLE IF EXISTS `questionnaire_anonymous_answers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `questionnaire_anonymous_answers` (
  `anonymous_answer_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `questionnaire_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `chdate` int unsigned NOT NULL,
  `mkdate` int unsigned NOT NULL,
  PRIMARY KEY (`anonymous_answer_id`),
  UNIQUE KEY `questionnaire_id_user_id` (`questionnaire_id`,`user_id`),
  KEY `questionnaire_id` (`questionnaire_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `questionnaire_answers`
--

DROP TABLE IF EXISTS `questionnaire_answers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `questionnaire_answers` (
  `answer_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `question_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `answerdata` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `chdate` int unsigned NOT NULL,
  `mkdate` int unsigned NOT NULL,
  PRIMARY KEY (`answer_id`),
  KEY `question_id` (`question_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `questionnaire_assignments`
--

DROP TABLE IF EXISTS `questionnaire_assignments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `questionnaire_assignments` (
  `assignment_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `questionnaire_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `range_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `range_type` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `chdate` int unsigned NOT NULL,
  `mkdate` int unsigned NOT NULL,
  PRIMARY KEY (`assignment_id`),
  KEY `questionnaire_id` (`questionnaire_id`),
  KEY `range_id_range_type` (`range_id`,`range_type`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `questionnaire_questions`
--

DROP TABLE IF EXISTS `questionnaire_questions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `questionnaire_questions` (
  `question_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `questionnaire_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `etask_task_id` int NOT NULL,
  `position` int NOT NULL,
  `chdate` int unsigned NOT NULL,
  `mkdate` int unsigned NOT NULL,
  PRIMARY KEY (`question_id`),
  KEY `questionnaire_id` (`questionnaire_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `questionnaires`
--

DROP TABLE IF EXISTS `questionnaires`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `questionnaires` (
  `questionnaire_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `title` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `startdate` int unsigned DEFAULT NULL,
  `stopdate` int unsigned DEFAULT NULL,
  `visible` tinyint unsigned NOT NULL DEFAULT '0',
  `anonymous` tinyint unsigned NOT NULL DEFAULT '0',
  `resultvisibility` enum('always','never','afterending') CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT 'always',
  `editanswers` tinyint unsigned NOT NULL DEFAULT '1',
  `copyable` tinyint unsigned NOT NULL DEFAULT '0',
  `chdate` int unsigned NOT NULL,
  `mkdate` int unsigned NOT NULL,
  PRIMARY KEY (`questionnaire_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `range_tree`
--

DROP TABLE IF EXISTS `range_tree`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `range_tree` (
  `item_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `parent_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `level` int NOT NULL DEFAULT '0',
  `priority` int NOT NULL DEFAULT '0',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `studip_object` varchar(10) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `studip_object_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  PRIMARY KEY (`item_id`),
  KEY `parent_id` (`parent_id`),
  KEY `priority` (`priority`),
  KEY `studip_object_id` (`studip_object_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `resource_booking_intervals`
--

DROP TABLE IF EXISTS `resource_booking_intervals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `resource_booking_intervals` (
  `interval_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `resource_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `booking_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `begin` int unsigned NOT NULL DEFAULT '0',
  `end` int unsigned NOT NULL DEFAULT '0',
  `mkdate` int unsigned NOT NULL DEFAULT '0',
  `chdate` int unsigned NOT NULL DEFAULT '0',
  `takes_place` tinyint unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`interval_id`),
  KEY `resource_id` (`resource_id`,`takes_place`,`end`),
  KEY `booking_id` (`booking_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `resource_bookings`
--

DROP TABLE IF EXISTS `resource_bookings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `resource_bookings` (
  `id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `resource_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `range_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `begin` int unsigned NOT NULL DEFAULT '0',
  `end` int unsigned NOT NULL DEFAULT '0',
  `repeat_end` int unsigned DEFAULT NULL,
  `repeat_quantity` int DEFAULT NULL,
  `mkdate` int unsigned NOT NULL DEFAULT '0',
  `chdate` int unsigned NOT NULL DEFAULT '0',
  `internal_comment` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `preparation_time` int NOT NULL DEFAULT '0',
  `booking_type` tinyint NOT NULL DEFAULT '0',
  `booking_user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `repetition_interval` varchar(24) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `assign_user_id` (`range_id`),
  KEY `resource_id` (`resource_id`,`booking_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `resource_categories`
--

DROP TABLE IF EXISTS `resource_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `resource_categories` (
  `id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `system` tinyint unsigned NOT NULL DEFAULT '0',
  `iconnr` int DEFAULT '1',
  `class_name` varchar(60) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT 'Resource',
  `mkdate` int unsigned NOT NULL DEFAULT '0',
  `chdate` int unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `resource_category_properties`
--

DROP TABLE IF EXISTS `resource_category_properties`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `resource_category_properties` (
  `category_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `property_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `requestable` tinyint unsigned NOT NULL DEFAULT '0',
  `protected` tinyint unsigned NOT NULL DEFAULT '0',
  `system` tinyint unsigned NOT NULL DEFAULT '0',
  `form_text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `mkdate` int unsigned NOT NULL DEFAULT '0',
  `chdate` int unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`category_id`,`property_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `resource_permissions`
--

DROP TABLE IF EXISTS `resource_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `resource_permissions` (
  `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `resource_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `perms` varchar(10) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `mkdate` int unsigned NOT NULL DEFAULT '0',
  `chdate` int unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`,`resource_id`),
  KEY `resource_id` (`resource_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `resource_properties`
--

DROP TABLE IF EXISTS `resource_properties`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `resource_properties` (
  `resource_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `property_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `state` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `mkdate` int unsigned NOT NULL DEFAULT '0',
  `chdate` int unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`resource_id`,`property_id`),
  KEY `property_id` (`property_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `resource_property_definitions`
--

DROP TABLE IF EXISTS `resource_property_definitions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `resource_property_definitions` (
  `property_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `type` set('bool','text','num','select','user','institute','position','fileref','url','resource_ref_list') CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `options` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `system` tinyint unsigned NOT NULL DEFAULT '0',
  `info_label` tinyint NOT NULL DEFAULT '0',
  `display_name` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `searchable` tinyint unsigned NOT NULL DEFAULT '0',
  `range_search` tinyint unsigned NOT NULL DEFAULT '0',
  `write_permission_level` varchar(16) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT 'admin-global',
  `property_group_id` int DEFAULT NULL,
  `property_group_pos` tinyint DEFAULT NULL,
  `mkdate` int unsigned NOT NULL DEFAULT '0',
  `chdate` int unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`property_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `resource_property_groups`
--

DROP TABLE IF EXISTS `resource_property_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `resource_property_groups` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `position` tinyint NOT NULL DEFAULT '0',
  `mkdate` int unsigned NOT NULL DEFAULT '0',
  `chdate` int unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `resource_request_appointments`
--

DROP TABLE IF EXISTS `resource_request_appointments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `resource_request_appointments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `request_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `appointment_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `mkdate` int unsigned NOT NULL DEFAULT '0',
  `chdate` int unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `resource_request_properties`
--

DROP TABLE IF EXISTS `resource_request_properties`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `resource_request_properties` (
  `request_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `property_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `state` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `mkdate` int unsigned DEFAULT NULL,
  `chdate` int unsigned DEFAULT NULL,
  PRIMARY KEY (`request_id`,`property_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `resource_requests`
--

DROP TABLE IF EXISTS `resource_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `resource_requests` (
  `id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `course_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `termin_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `metadate_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `last_modified_by` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `resource_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `category_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT '',
  `comment` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `reply_comment` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `reply_recipients` enum('requester','lecturer') CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT 'requester',
  `closed` tinyint unsigned NOT NULL DEFAULT '0',
  `mkdate` int unsigned DEFAULT NULL,
  `chdate` int unsigned DEFAULT NULL,
  `begin` int unsigned NOT NULL DEFAULT '0',
  `end` int unsigned NOT NULL DEFAULT '0',
  `preparation_time` int NOT NULL DEFAULT '0',
  `marked` tinyint unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `termin_id` (`termin_id`),
  KEY `seminar_id` (`course_id`),
  KEY `user_id` (`user_id`),
  KEY `resource_id` (`resource_id`),
  KEY `category_id` (`category_id`),
  KEY `closed` (`closed`,`id`,`resource_id`),
  KEY `metadate_id` (`metadate_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `resource_temporary_permissions`
--

DROP TABLE IF EXISTS `resource_temporary_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `resource_temporary_permissions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `resource_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `begin` int unsigned NOT NULL DEFAULT '0',
  `end` int unsigned NOT NULL DEFAULT '0',
  `perms` varchar(10) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `mkdate` int unsigned NOT NULL DEFAULT '0',
  `chdate` int unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `resource_id` (`resource_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `resources`
--

DROP TABLE IF EXISTS `resources`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `resources` (
  `id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `parent_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `category_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `level` int DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `requestable` tinyint unsigned NOT NULL DEFAULT '0',
  `lockable` tinyint unsigned NOT NULL DEFAULT '1',
  `mkdate` int unsigned NOT NULL DEFAULT '0',
  `chdate` int unsigned NOT NULL DEFAULT '0',
  `sort_position` tinyint unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`),
  KEY `category_id` (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `roleid` int unsigned NOT NULL AUTO_INCREMENT,
  `rolename` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `system` enum('y','n') CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT 'n',
  PRIMARY KEY (`roleid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `roles_plugins`
--

DROP TABLE IF EXISTS `roles_plugins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles_plugins` (
  `roleid` int unsigned NOT NULL DEFAULT '0',
  `pluginid` int unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`roleid`,`pluginid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `roles_studipperms`
--

DROP TABLE IF EXISTS `roles_studipperms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles_studipperms` (
  `roleid` int unsigned NOT NULL DEFAULT '0',
  `permname` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`roleid`,`permname`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `roles_user`
--

DROP TABLE IF EXISTS `roles_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles_user` (
  `roleid` int unsigned NOT NULL DEFAULT '0',
  `userid` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `institut_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  PRIMARY KEY (`roleid`,`userid`,`institut_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `schedule`
--

DROP TABLE IF EXISTS `schedule`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `schedule` (
  `id` int NOT NULL AUTO_INCREMENT,
  `start` smallint NOT NULL COMMENT 'start hour and minutes',
  `end` smallint NOT NULL COMMENT 'end hour and minutes',
  `day` tinyint NOT NULL COMMENT 'day of week, 0-6',
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `color` tinyint DEFAULT NULL,
  `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `schedule_seminare`
--

DROP TABLE IF EXISTS `schedule_seminare`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `schedule_seminare` (
  `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `seminar_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `metadate_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `visible` tinyint unsigned NOT NULL DEFAULT '1',
  `color` tinyint DEFAULT NULL,
  PRIMARY KEY (`user_id`,`seminar_id`,`metadate_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `schema_version`
--

DROP TABLE IF EXISTS `schema_version`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `schema_version` (
  `domain` varchar(255) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `branch` varchar(64) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '0',
  `version` int unsigned NOT NULL,
  PRIMARY KEY (`domain`,`branch`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `scm`
--

DROP TABLE IF EXISTS `scm`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `scm` (
  `scm_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `range_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `tab_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `content` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `mkdate` int unsigned NOT NULL DEFAULT '0',
  `chdate` int unsigned NOT NULL DEFAULT '0',
  `position` int unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`scm_id`),
  KEY `chdate` (`chdate`),
  KEY `range_id` (`range_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sem_classes`
--

DROP TABLE IF EXISTS `sem_classes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sem_classes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `only_inst_user` tinyint unsigned NOT NULL,
  `default_read_level` int NOT NULL,
  `default_write_level` int NOT NULL,
  `bereiche` tinyint unsigned NOT NULL,
  `module` tinyint unsigned NOT NULL,
  `show_browse` tinyint unsigned NOT NULL,
  `write_access_nobody` tinyint unsigned NOT NULL,
  `topic_create_autor` tinyint unsigned NOT NULL,
  `visible` tinyint unsigned NOT NULL,
  `course_creation_forbidden` tinyint unsigned NOT NULL,
  `modules` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `create_description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `studygroup_mode` tinyint unsigned NOT NULL,
  `admission_prelim_default` tinyint NOT NULL DEFAULT '0',
  `admission_type_default` tinyint NOT NULL DEFAULT '0',
  `title_dozent` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title_dozent_plural` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title_tutor` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title_tutor_plural` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title_autor` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title_autor_plural` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `show_raumzeit` tinyint unsigned NOT NULL DEFAULT '1',
  `is_group` tinyint unsigned NOT NULL DEFAULT '0',
  `mkdate` int unsigned NOT NULL,
  `chdate` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sem_tree`
--

DROP TABLE IF EXISTS `sem_tree`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sem_tree` (
  `sem_tree_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `parent_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `priority` tinyint NOT NULL DEFAULT '0',
  `info` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `studip_object_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `type` tinyint unsigned NOT NULL,
  `mkdate` int unsigned DEFAULT NULL,
  `chdate` int unsigned DEFAULT NULL,
  PRIMARY KEY (`sem_tree_id`),
  KEY `parent_id` (`parent_id`),
  KEY `priority` (`priority`),
  KEY `studip_object_id` (`studip_object_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sem_types`
--

DROP TABLE IF EXISTS `sem_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sem_types` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `class` int NOT NULL,
  `mkdate` int unsigned NOT NULL,
  `chdate` int unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `semester_courses`
--

DROP TABLE IF EXISTS `semester_courses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `semester_courses` (
  `semester_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `course_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `mkdate` int NOT NULL DEFAULT '0',
  `chdate` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`semester_id`,`course_id`),
  KEY `course_id` (`course_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `semester_data`
--

DROP TABLE IF EXISTS `semester_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `semester_data` (
  `semester_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `semester_token` varchar(10) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `beginn` int unsigned DEFAULT NULL,
  `ende` int unsigned DEFAULT NULL,
  `sem_wechsel` int unsigned DEFAULT NULL,
  `vorles_beginn` int unsigned DEFAULT NULL,
  `vorles_ende` int unsigned DEFAULT NULL,
  `visible` tinyint unsigned NOT NULL DEFAULT '1',
  `external_id` varchar(50) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `mkdate` int unsigned DEFAULT NULL,
  `chdate` int unsigned DEFAULT NULL,
  PRIMARY KEY (`semester_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `semester_holiday`
--

DROP TABLE IF EXISTS `semester_holiday`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `semester_holiday` (
  `holiday_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `semester_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `beginn` int unsigned DEFAULT NULL,
  `ende` int unsigned NOT NULL DEFAULT '0',
  `mkdate` int unsigned DEFAULT NULL,
  `chdate` int unsigned DEFAULT NULL,
  PRIMARY KEY (`holiday_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `seminar_courseset`
--

DROP TABLE IF EXISTS `seminar_courseset`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `seminar_courseset` (
  `set_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `seminar_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `mkdate` int unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`set_id`,`seminar_id`),
  KEY `seminar_id` (`seminar_id`,`set_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `seminar_cycle_dates`
--

DROP TABLE IF EXISTS `seminar_cycle_dates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `seminar_cycle_dates` (
  `metadate_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `seminar_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `weekday` tinyint unsigned NOT NULL,
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `sws` decimal(2,1) NOT NULL DEFAULT '0.0',
  `cycle` tinyint unsigned NOT NULL DEFAULT '0',
  `week_offset` int NOT NULL DEFAULT '0',
  `end_offset` int DEFAULT NULL,
  `sorter` tinyint unsigned NOT NULL DEFAULT '0',
  `mkdate` int unsigned NOT NULL,
  `chdate` int unsigned NOT NULL,
  PRIMARY KEY (`metadate_id`),
  KEY `seminar_id` (`seminar_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `seminar_inst`
--

DROP TABLE IF EXISTS `seminar_inst`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `seminar_inst` (
  `seminar_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `institut_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  PRIMARY KEY (`seminar_id`,`institut_id`),
  KEY `institut_id` (`institut_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `seminar_sem_tree`
--

DROP TABLE IF EXISTS `seminar_sem_tree`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `seminar_sem_tree` (
  `seminar_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `sem_tree_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  PRIMARY KEY (`seminar_id`,`sem_tree_id`),
  KEY `sem_tree_id` (`sem_tree_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `seminar_user`
--

DROP TABLE IF EXISTS `seminar_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `seminar_user` (
  `Seminar_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `status` enum('user','autor','tutor','dozent') CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT 'user',
  `position` int NOT NULL DEFAULT '0',
  `gruppe` tinyint NOT NULL DEFAULT '0',
  `mkdate` int unsigned NOT NULL DEFAULT '0',
  `comment` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `visible` enum('yes','no','unknown') CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT 'unknown',
  `label` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `bind_calendar` tinyint unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`Seminar_id`,`user_id`),
  KEY `status` (`status`,`Seminar_id`),
  KEY `user_id` (`user_id`,`Seminar_id`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `seminar_user_notifications`
--

DROP TABLE IF EXISTS `seminar_user_notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `seminar_user_notifications` (
  `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `seminar_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `notification_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `chdate` int unsigned NOT NULL,
  `mkdate` int unsigned NOT NULL,
  PRIMARY KEY (`user_id`,`seminar_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `seminar_userdomains`
--

DROP TABLE IF EXISTS `seminar_userdomains`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `seminar_userdomains` (
  `seminar_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `userdomain_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  PRIMARY KEY (`seminar_id`,`userdomain_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `seminare`
--

DROP TABLE IF EXISTS `seminare`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `seminare` (
  `Seminar_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '0',
  `VeranstaltungsNummer` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Institut_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '0',
  `Name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `Untertitel` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` int unsigned NOT NULL DEFAULT '1',
  `Beschreibung` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `Ort` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Sonstiges` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `Lesezugriff` tinyint NOT NULL DEFAULT '0',
  `Schreibzugriff` tinyint NOT NULL DEFAULT '0',
  `start_time` int unsigned DEFAULT '0',
  `duration_time` int DEFAULT NULL,
  `art` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `teilnehmer` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `vorrausetzungen` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `lernorga` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `leistungsnachweis` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `mkdate` int unsigned NOT NULL DEFAULT '0',
  `chdate` int unsigned NOT NULL DEFAULT '0',
  `ects` varchar(32) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `admission_turnout` int DEFAULT NULL,
  `admission_binding` tinyint DEFAULT NULL,
  `admission_prelim` tinyint unsigned NOT NULL DEFAULT '0',
  `admission_prelim_txt` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `admission_disable_waitlist` tinyint unsigned NOT NULL DEFAULT '0',
  `visible` tinyint unsigned NOT NULL DEFAULT '1',
  `showscore` tinyint DEFAULT '0',
  `aux_lock_rule` varchar(32) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `aux_lock_rule_forced` tinyint NOT NULL DEFAULT '0',
  `lock_rule` varchar(32) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `admission_waitlist_max` int unsigned NOT NULL DEFAULT '0',
  `admission_disable_waitlist_move` tinyint unsigned NOT NULL DEFAULT '0',
  `completion` tinyint unsigned NOT NULL DEFAULT '0',
  `parent_course` char(32) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  PRIMARY KEY (`Seminar_id`),
  KEY `Institut_id` (`Institut_id`),
  KEY `visible` (`visible`),
  KEY `status` (`status`,`Seminar_id`),
  KEY `parent_course` (`parent_course`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `separable_room_parts`
--

DROP TABLE IF EXISTS `separable_room_parts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `separable_room_parts` (
  `separable_room_id` int NOT NULL,
  `room_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `mkdate` int unsigned NOT NULL DEFAULT '0',
  `chdate` int unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`separable_room_id`,`room_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `separable_rooms`
--

DROP TABLE IF EXISTS `separable_rooms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `separable_rooms` (
  `id` int NOT NULL AUTO_INCREMENT,
  `building_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `name` varchar(256) NOT NULL DEFAULT '',
  `mkdate` int unsigned NOT NULL DEFAULT '0',
  `chdate` int unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `session_data`
--

DROP TABLE IF EXISTS `session_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `session_data` (
  `sid` varchar(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `val` mediumblob NOT NULL,
  `changed` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`sid`),
  KEY `changed` (`changed`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `siteinfo_details`
--

DROP TABLE IF EXISTS `siteinfo_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `siteinfo_details` (
  `detail_id` smallint unsigned NOT NULL AUTO_INCREMENT,
  `rubric_id` smallint unsigned NOT NULL,
  `position` tinyint unsigned DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`detail_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `siteinfo_rubrics`
--

DROP TABLE IF EXISTS `siteinfo_rubrics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `siteinfo_rubrics` (
  `rubric_id` smallint unsigned NOT NULL AUTO_INCREMENT,
  `position` tinyint unsigned DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`rubric_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `smiley`
--

DROP TABLE IF EXISTS `smiley`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `smiley` (
  `smiley_id` int unsigned NOT NULL AUTO_INCREMENT,
  `smiley_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `smiley_width` int NOT NULL DEFAULT '0',
  `smiley_height` int NOT NULL DEFAULT '0',
  `short_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `smiley_counter` int unsigned NOT NULL DEFAULT '0',
  `short_counter` int unsigned NOT NULL DEFAULT '0',
  `fav_counter` int unsigned NOT NULL DEFAULT '0',
  `mkdate` int unsigned DEFAULT NULL,
  `chdate` int unsigned DEFAULT NULL,
  PRIMARY KEY (`smiley_id`),
  UNIQUE KEY `name` (`smiley_name`),
  KEY `short` (`short_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `statusgruppe_user`
--

DROP TABLE IF EXISTS `statusgruppe_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `statusgruppe_user` (
  `statusgruppe_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `position` int NOT NULL DEFAULT '0',
  `visible` tinyint unsigned NOT NULL DEFAULT '1',
  `inherit` tinyint unsigned NOT NULL DEFAULT '1',
  `mkdate` int unsigned DEFAULT NULL,
  PRIMARY KEY (`statusgruppe_id`,`user_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `statusgruppen`
--

DROP TABLE IF EXISTS `statusgruppen`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `statusgruppen` (
  `statusgruppe_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `description` text COLLATE utf8mb4_unicode_ci,
  `range_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `position` int NOT NULL DEFAULT '0',
  `size` int NOT NULL DEFAULT '0',
  `selfassign` tinyint unsigned NOT NULL DEFAULT '0',
  `selfassign_start` int unsigned NOT NULL DEFAULT '0',
  `selfassign_end` int unsigned NOT NULL DEFAULT '0',
  `mkdate` int unsigned NOT NULL DEFAULT '0',
  `chdate` int unsigned NOT NULL DEFAULT '0',
  `calendar_group` tinyint unsigned NOT NULL DEFAULT '0',
  `name_w` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name_m` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`statusgruppe_id`),
  KEY `range_id` (`range_id`),
  KEY `position` (`position`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `studygroup_invitations`
--

DROP TABLE IF EXISTS `studygroup_invitations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `studygroup_invitations` (
  `sem_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `mkdate` int unsigned NOT NULL,
  PRIMARY KEY (`sem_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `termin_related_groups`
--

DROP TABLE IF EXISTS `termin_related_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `termin_related_groups` (
  `termin_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `statusgruppe_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  PRIMARY KEY (`termin_id`,`statusgruppe_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `termin_related_persons`
--

DROP TABLE IF EXISTS `termin_related_persons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `termin_related_persons` (
  `range_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  PRIMARY KEY (`range_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `termine`
--

DROP TABLE IF EXISTS `termine`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `termine` (
  `termin_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `range_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `autor_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `content` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `date` int unsigned NOT NULL DEFAULT '0',
  `end_time` int unsigned NOT NULL DEFAULT '0',
  `mkdate` int unsigned NOT NULL DEFAULT '0',
  `chdate` int unsigned NOT NULL DEFAULT '0',
  `date_typ` tinyint NOT NULL DEFAULT '0',
  `raum` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `metadate_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  PRIMARY KEY (`termin_id`),
  KEY `metadate_id` (`metadate_id`,`date`),
  KEY `range_id` (`range_id`,`date`),
  KEY `date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `termsadmissions`
--

DROP TABLE IF EXISTS `termsadmissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `termsadmissions` (
  `rule_id` varchar(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `terms` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `mkdate` int NOT NULL DEFAULT '0',
  `chdate` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`rule_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `themen`
--

DROP TABLE IF EXISTS `themen`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `themen` (
  `issue_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `seminar_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `author_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `priority` smallint unsigned NOT NULL DEFAULT '0',
  `paper_related` tinyint unsigned NOT NULL DEFAULT '0',
  `mkdate` int unsigned NOT NULL DEFAULT '0',
  `chdate` int unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`issue_id`),
  KEY `seminar_id` (`seminar_id`,`priority`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `themen_termine`
--

DROP TABLE IF EXISTS `themen_termine`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `themen_termine` (
  `issue_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `termin_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  PRIMARY KEY (`issue_id`,`termin_id`),
  KEY `termin_id` (`termin_id`,`issue_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `timedadmissions`
--

DROP TABLE IF EXISTS `timedadmissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `timedadmissions` (
  `rule_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_time` int unsigned NOT NULL DEFAULT '0',
  `end_time` int unsigned NOT NULL DEFAULT '0',
  `mkdate` int unsigned NOT NULL DEFAULT '0',
  `chdate` int unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`rule_id`),
  KEY `start_time` (`start_time`),
  KEY `end_time` (`end_time`),
  KEY `start_end` (`start_time`,`end_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tools_activated`
--

DROP TABLE IF EXISTS `tools_activated`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tools_activated` (
  `range_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `range_type` enum('course','institute') CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `plugin_id` int unsigned NOT NULL,
  `position` tinyint unsigned NOT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `mkdate` int unsigned NOT NULL,
  `chdate` int unsigned NOT NULL,
  PRIMARY KEY (`range_id`,`plugin_id`),
  KEY `plugin_id` (`plugin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_factorlist`
--

DROP TABLE IF EXISTS `user_factorlist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_factorlist` (
  `list_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `mkdate` int unsigned DEFAULT NULL,
  PRIMARY KEY (`list_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_info`
--

DROP TABLE IF EXISTS `user_info`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_info` (
  `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `hobby` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `lebenslauf` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `publi` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `schwerp` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `Home` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `privatnr` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `privatcell` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `privadr` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `score` int unsigned NOT NULL DEFAULT '0',
  `geschlecht` tinyint NOT NULL DEFAULT '0',
  `mkdate` int unsigned NOT NULL DEFAULT '0',
  `chdate` int unsigned NOT NULL DEFAULT '0',
  `title_front` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `title_rear` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `preferred_language` varchar(20) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `smsforward_copy` tinyint unsigned NOT NULL DEFAULT '1',
  `smsforward_rec` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `email_forward` tinyint unsigned NOT NULL DEFAULT '0',
  `smiley_favorite` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `motto` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `lock_rule` varchar(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `oercampus_description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`user_id`),
  KEY `score` (`score`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_inst`
--

DROP TABLE IF EXISTS `user_inst`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_inst` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '0',
  `Institut_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '0',
  `inst_perms` enum('user','autor','tutor','dozent','admin') CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT 'user',
  `sprechzeiten` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `raum` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `Telefon` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `Fax` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `externdefault` tinyint unsigned NOT NULL DEFAULT '0',
  `priority` tinyint unsigned NOT NULL DEFAULT '0',
  `visible` tinyint unsigned NOT NULL DEFAULT '1',
  `mkdate` int unsigned DEFAULT NULL,
  `chdate` int unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_inst` (`Institut_id`,`user_id`),
  KEY `inst_perms` (`inst_perms`,`Institut_id`),
  KEY `user_id` (`user_id`,`inst_perms`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_online`
--

DROP TABLE IF EXISTS `user_online`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_online` (
  `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `last_lifesign` int unsigned NOT NULL,
  PRIMARY KEY (`user_id`),
  KEY `last_lifesign` (`last_lifesign`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_studiengang`
--

DROP TABLE IF EXISTS `user_studiengang`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_studiengang` (
  `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `fach_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `semester` tinyint DEFAULT '0',
  `abschluss_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '0',
  `version_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `mkdate` int unsigned DEFAULT NULL,
  `chdate` int unsigned DEFAULT NULL,
  PRIMARY KEY (`user_id`,`fach_id`,`abschluss_id`),
  KEY `studiengang_id` (`fach_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_token`
--

DROP TABLE IF EXISTS `user_token`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_token` (
  `token` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `expiration` int unsigned NOT NULL,
  `mkdate` int unsigned DEFAULT NULL,
  PRIMARY KEY (`token`),
  KEY `index_expiration` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_userdomains`
--

DROP TABLE IF EXISTS `user_userdomains`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_userdomains` (
  `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `userdomain_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  PRIMARY KEY (`user_id`,`userdomain_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_visibility`
--

DROP TABLE IF EXISTS `user_visibility`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_visibility` (
  `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `online` tinyint unsigned NOT NULL DEFAULT '1',
  `search` tinyint unsigned NOT NULL DEFAULT '1',
  `email` tinyint unsigned NOT NULL DEFAULT '1',
  `homepage` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `default_homepage_visibility` int NOT NULL DEFAULT '0',
  `mkdate` int unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_visibility_settings`
--

DROP TABLE IF EXISTS `user_visibility_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_visibility_settings` (
  `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `visibilityid` int NOT NULL AUTO_INCREMENT,
  `parent_id` int NOT NULL,
  `category` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `state` int DEFAULT NULL,
  `plugin` int DEFAULT NULL,
  `identifier` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`visibilityid`),
  KEY `parent_id` (`parent_id`),
  KEY `identifier` (`identifier`),
  KEY `userid` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `userdomains`
--

DROP TABLE IF EXISTS `userdomains`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `userdomains` (
  `userdomain_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `restricted_access` tinyint unsigned NOT NULL DEFAULT '1',
  `mkdate` int unsigned NOT NULL DEFAULT '0',
  `chdate` int unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`userdomain_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `userfilter`
--

DROP TABLE IF EXISTS `userfilter`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `userfilter` (
  `filter_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `mkdate` int unsigned NOT NULL DEFAULT '0',
  `chdate` int unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`filter_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `userfilter_fields`
--

DROP TABLE IF EXISTS `userfilter_fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `userfilter_fields` (
  `field_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `filter_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `type` varchar(255) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `value` varchar(255) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `compare_op` varchar(255) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `mkdate` int unsigned NOT NULL DEFAULT '0',
  `chdate` int unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`field_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `userlimits`
--

DROP TABLE IF EXISTS `userlimits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `userlimits` (
  `rule_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `maxnumber` int DEFAULT NULL,
  `mkdate` int unsigned DEFAULT NULL,
  `chdate` int unsigned DEFAULT NULL,
  PRIMARY KEY (`rule_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users_tfa`
--

DROP TABLE IF EXISTS `users_tfa`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users_tfa` (
  `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `secret` varchar(255) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `confirmed` tinyint unsigned NOT NULL DEFAULT '0',
  `type` enum('email','app') CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT 'email',
  `mkdate` int unsigned NOT NULL,
  `chdate` int unsigned NOT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users_tfa_tokens`
--

DROP TABLE IF EXISTS `users_tfa_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users_tfa_tokens` (
  `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `token` char(6) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `mkdate` int unsigned NOT NULL,
  PRIMARY KEY (`user_id`,`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `webservice_access_rules`
--

DROP TABLE IF EXISTS `webservice_access_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `webservice_access_rules` (
  `api_key` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `method` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `ip_range` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `type` enum('allow','deny') CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT 'allow',
  `id` int NOT NULL AUTO_INCREMENT,
  `mkdate` int unsigned DEFAULT NULL,
  `chdate` int unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `widget_default`
--

DROP TABLE IF EXISTS `widget_default`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `widget_default` (
  `pluginid` int NOT NULL,
  `col` tinyint(1) NOT NULL DEFAULT '0',
  `position` tinyint(1) NOT NULL DEFAULT '0',
  `perm` enum('user','autor','tutor','dozent','admin','root') CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT 'autor',
  PRIMARY KEY (`perm`,`pluginid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `widget_user`
--

DROP TABLE IF EXISTS `widget_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `widget_user` (
  `id` int NOT NULL AUTO_INCREMENT,
  `pluginid` int NOT NULL,
  `position` int NOT NULL DEFAULT '0',
  `range_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `col` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `range_id` (`range_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `wiki`
--

DROP TABLE IF EXISTS `wiki`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `wiki` (
  `range_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `keyword` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
  `body` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ancestor` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `chdate` int unsigned DEFAULT NULL,
  `version` int NOT NULL DEFAULT '0',
  `mkdate` int unsigned DEFAULT NULL,
  PRIMARY KEY (`range_id`,`keyword`,`version`),
  KEY `user_id` (`user_id`),
  KEY `chdate` (`chdate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `wiki_links`
--

DROP TABLE IF EXISTS `wiki_links`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `wiki_links` (
  `range_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `from_keyword` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
  `to_keyword` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
  PRIMARY KEY (`range_id`,`to_keyword`,`from_keyword`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `wiki_locks`
--

DROP TABLE IF EXISTS `wiki_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `wiki_locks` (
  `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `range_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `keyword` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
  `chdate` int unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`range_id`,`user_id`,`keyword`),
  KEY `user_id` (`user_id`),
  KEY `chdate` (`chdate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `wiki_page_config`
--

DROP TABLE IF EXISTS `wiki_page_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `wiki_page_config` (
  `range_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `keyword` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `read_restricted` tinyint unsigned NOT NULL DEFAULT '0',
  `edit_restricted` tinyint unsigned NOT NULL DEFAULT '0',
  `mkdate` int unsigned DEFAULT NULL,
  `chdate` int unsigned DEFAULT NULL,
  PRIMARY KEY (`range_id`,`keyword`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2022-11-21 15:24:29
