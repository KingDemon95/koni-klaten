-- MySQL dump 10.13  Distrib 8.4.13, for Linux (x86_64)
--
-- Host: localhost    Database: laravel
-- ------------------------------------------------------
-- Server version	8.4.13

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
-- Table structure for table `agendas`
--

DROP TABLE IF EXISTS `agendas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `agendas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `cabang_olahraga_id` bigint unsigned DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `published_by` bigint unsigned DEFAULT NULL,
  `judul` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(220) COLLATE utf8mb4_unicode_ci NOT NULL,
  `jenis` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'kegiatan',
  `deskripsi` text COLLATE utf8mb4_unicode_ci,
  `tanggal_mulai` date NOT NULL,
  `tanggal_selesai` date DEFAULT NULL,
  `waktu_mulai` time DEFAULT NULL,
  `waktu_selesai` time DEFAULT NULL,
  `lokasi` varchar(180) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `poster_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `catatan_revisi` text COLLATE utf8mb4_unicode_ci,
  `published_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `agendas_slug_unique` (`slug`),
  KEY `agendas_created_by_foreign` (`created_by`),
  KEY `agendas_updated_by_foreign` (`updated_by`),
  KEY `agendas_published_by_foreign` (`published_by`),
  KEY `agendas_status_tanggal_mulai_index` (`status`,`tanggal_mulai`),
  KEY `agendas_cabang_olahraga_id_status_index` (`cabang_olahraga_id`,`status`),
  KEY `agendas_jenis_index` (`jenis`),
  KEY `agendas_tanggal_mulai_index` (`tanggal_mulai`),
  KEY `agendas_status_index` (`status`),
  CONSTRAINT `agendas_cabang_olahraga_id_foreign` FOREIGN KEY (`cabang_olahraga_id`) REFERENCES `cabang_olahraga` (`id`) ON DELETE SET NULL,
  CONSTRAINT `agendas_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `agendas_published_by_foreign` FOREIGN KEY (`published_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `agendas_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `agendas`
--

LOCK TABLES `agendas` WRITE;
/*!40000 ALTER TABLE `agendas` DISABLE KEYS */;
INSERT INTO `agendas` VALUES (1,3,9,6,6,'lomba','lomba','agenda','lomba sepak bola koni klaten','2026-11-01','2026-11-01','03:50:00','15:50:00','koni klaten','agenda/dGbtZIIaXLeXAcHISko3ua56bx5Q4F1CK6tU4TYz.webp','published',NULL,'2026-11-11 15:51:20','2026-11-11 15:50:48','2026-11-11 15:51:20');
/*!40000 ALTER TABLE `agendas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `atlets`
--

DROP TABLE IF EXISTS `atlets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `atlets` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `cabang_olahraga_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `nama_lengkap` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `jenis_kelamin` varchar(1) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tempat_lahir` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `alamat` text COLLATE utf8mb4_unicode_ci,
  `no_telepon` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `foto_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'aktif',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `atlets_user_id_unique` (`user_id`),
  KEY `atlets_cabang_olahraga_id_foreign` (`cabang_olahraga_id`),
  KEY `atlets_status_index` (`status`),
  CONSTRAINT `atlets_cabang_olahraga_id_foreign` FOREIGN KEY (`cabang_olahraga_id`) REFERENCES `cabang_olahraga` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `atlets_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `atlets`
--

LOCK TABLES `atlets` WRITE;
/*!40000 ALTER TABLE `atlets` DISABLE KEYS */;
INSERT INTO `atlets` VALUES (2,2,4,'Naila Fath','P','klaten','2026-11-04','klaten','08123456789','fath@contoh.com','atlet/G7UJ3ERjKlqIVuuY81AbjQuNuvv7QlBrQEIUBSDQ.png','aktif','2026-11-08 06:34:44','2026-11-08 10:25:49'),(3,3,11,'Josh','L','klaten','2026-07-09','korut','08123456789','sepak2@koniklaten.id','atlet/tqxFKlWqjTZnLHSKa9eZaN1L72A48s1kpvuzPzvK.jpg','aktif','2026-11-09 02:46:37','2026-11-09 02:53:36'),(4,2,NULL,'sapa','L','klaten','2026-05-06','klaten','08123456789','aha@koniklaten.id','atlet/OT2NEJ6oNanPoYZM3jIeaOPn3rNVaW0ArJ1ESwov.webp','aktif','2026-11-09 04:16:57','2026-11-09 04:16:57'),(5,3,12,'Mars','L','unknown','2025-05-08','unknown','08123456789','sepak4@koniklaten.id','atlet/awnQHGKAgqu5yKYnic5Exsf5dHgksKpOBkbyNwed.jpg','aktif','2026-11-09 04:21:55','2026-11-09 06:29:58'),(6,4,14,'Jeonghan','L','Jawa','2025-11-01','Jakardah','0238897126','water2@koniklaten.id','atlet/28YBtt37UZw4YxjDvfeioMnTHEWkfHg2egQRaLO5.jpg','aktif','2026-11-11 16:42:12','2026-11-11 16:42:12');
/*!40000 ALTER TABLE `atlets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `audit_logs`
--

DROP TABLE IF EXISTS `audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `audit_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `actor_id` bigint unsigned DEFAULT NULL,
  `actor_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `actor_role` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `event` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject_type` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject_id` bigint unsigned DEFAULT NULL,
  `subject_label` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cabang_olahraga_id` bigint unsigned DEFAULT NULL,
  `old_values` json DEFAULT NULL,
  `new_values` json DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `method` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `route_name` varchar(160) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `audit_logs_created_at_index` (`created_at`),
  KEY `audit_logs_actor_id_created_at_index` (`actor_id`,`created_at`),
  KEY `audit_logs_cabang_olahraga_id_created_at_index` (`cabang_olahraga_id`,`created_at`),
  KEY `audit_logs_subject_type_subject_id_index` (`subject_type`,`subject_id`),
  KEY `audit_logs_event_index` (`event`),
  CONSTRAINT `audit_logs_actor_id_foreign` FOREIGN KEY (`actor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `audit_logs_cabang_olahraga_id_foreign` FOREIGN KEY (`cabang_olahraga_id`) REFERENCES `cabang_olahraga` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_logs`
--

LOCK TABLES `audit_logs` WRITE;
/*!40000 ALTER TABLE `audit_logs` DISABLE KEYS */;
INSERT INTO `audit_logs` VALUES (1,17,'Ketua KONI','ketua_koni','logout',NULL,NULL,'Sesi pengguna',NULL,NULL,NULL,'172.18.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36','POST','logout','2026-11-19 21:53:11',NULL),(2,1,'Administrator KONI','admin_koni','login',NULL,NULL,'Sesi pengguna',NULL,NULL,NULL,'172.18.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36','POST','login.store','2026-11-19 21:53:18',NULL);
/*!40000 ALTER TABLE `audit_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cabang_olahraga`
--

DROP TABLE IF EXISTS `cabang_olahraga`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cabang_olahraga` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nama` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `singkatan` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deskripsi` text COLLATE utf8mb4_unicode_ci,
  `logo_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nama_ketua` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telepon` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alamat` text COLLATE utf8mb4_unicode_ci,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'aktif',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cabang_olahraga_nama_unique` (`nama`),
  KEY `cabang_olahraga_status_index` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cabang_olahraga`
--

LOCK TABLES `cabang_olahraga` WRITE;
/*!40000 ALTER TABLE `cabang_olahraga` DISABLE KEYS */;
INSERT INTO `cabang_olahraga` VALUES (2,'Bulutangkis','PBSI',NULL,'cabor/8jqiKoW8sY8Wafww00UlBA6LnxeOoM2VQojjzBNP.jpg','Kakanda','bulutangkis@koniklaten.com','08123456789','klaten','aktif','2026-11-08 06:30:32','2026-11-08 06:30:32'),(3,'Sepak Bola','PSSI','Sepak bola adalah permainan olahraga beregu yang dimainkan oleh dua tim, di mana masing-masing tim beranggotakan 11 pemain','cabor/wqo3gT6SbvKFRUQBwOlwb9BDY0pUGa1EfBAfcniI.webp','Charles','sepak@koniklaten.id','08987654321','Korea Utara','aktif','2026-11-09 02:45:01','2026-11-09 02:45:01'),(4,'Water Sport','Akuatik','Watersport atau olahraga air adalah berbagai aktivitas rekreasi dan olahraga yang dilakukan di atas, di dalam, atau di bawah permukaan air. Aktivitas ini memadukan keseruan, tantangan adrenalin, serta kebugaran fisik, dan biasanya dilakukan di pantai, danau, sungai, maupun kolam renang.','cabor/GHXPGZI66xWOGULtqLUFGCXe9DCwqSLKLWn7kv8p.jpg','DK','water@koniklaten.id','0378945612','mana aja','aktif','2026-11-11 16:37:19','2026-11-11 16:37:19');
/*!40000 ALTER TABLE `cabang_olahraga` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
INSERT INTO `cache` VALUES ('laravel-cache-ffd27b38eed2f113fe5e71f3fb504533','i:1;',1795100058),('laravel-cache-ffd27b38eed2f113fe5e71f3fb504533:timer','i:1795100058;',1795100058),('laravel-cache-spatie.permission.cache','a:3:{s:5:\"alias\";a:0:{}s:11:\"permissions\";a:0:{}s:5:\"roles\";a:0:{}}',1795186421);
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`),
  KEY `failed_jobs_connection_queue_failed_at_index` (`connection`,`queue`,`failed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inventory_categories`
--

DROP TABLE IF EXISTS `inventory_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `inventory_categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `cabang_olahraga_id` bigint unsigned DEFAULT NULL,
  `nama` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `deskripsi` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `inventory_categories_scope_name_unique` (`cabang_olahraga_id`,`nama`),
  KEY `inventory_categories_created_by_foreign` (`created_by`),
  KEY `inventory_categories_cabang_olahraga_id_index` (`cabang_olahraga_id`),
  CONSTRAINT `inventory_categories_cabang_olahraga_id_foreign` FOREIGN KEY (`cabang_olahraga_id`) REFERENCES `cabang_olahraga` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `inventory_categories_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventory_categories`
--

LOCK TABLES `inventory_categories` WRITE;
/*!40000 ALTER TABLE `inventory_categories` DISABLE KEYS */;
/*!40000 ALTER TABLE `inventory_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inventory_items`
--

DROP TABLE IF EXISTS `inventory_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `inventory_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `kode_barang` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `inventory_category_id` bigint unsigned NOT NULL,
  `cabang_olahraga_id` bigint unsigned DEFAULT NULL,
  `nama_barang` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `deskripsi` text COLLATE utf8mb4_unicode_ci,
  `jumlah` int unsigned NOT NULL DEFAULT '1',
  `satuan` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'unit',
  `kondisi` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'baik',
  `lokasi` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tanggal_perolehan` date DEFAULT NULL,
  `harga_perolehan` decimal(15,2) DEFAULT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'aktif',
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `inventory_items_kode_barang_unique` (`kode_barang`),
  KEY `inventory_items_created_by_foreign` (`created_by`),
  KEY `inventory_items_updated_by_foreign` (`updated_by`),
  KEY `inventory_items_cabang_olahraga_id_status_index` (`cabang_olahraga_id`,`status`),
  KEY `inventory_items_inventory_category_id_kondisi_index` (`inventory_category_id`,`kondisi`),
  KEY `inventory_items_kondisi_index` (`kondisi`),
  KEY `inventory_items_status_index` (`status`),
  CONSTRAINT `inventory_items_cabang_olahraga_id_foreign` FOREIGN KEY (`cabang_olahraga_id`) REFERENCES `cabang_olahraga` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `inventory_items_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `inventory_items_inventory_category_id_foreign` FOREIGN KEY (`inventory_category_id`) REFERENCES `inventory_categories` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `inventory_items_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventory_items`
--

LOCK TABLES `inventory_items` WRITE;
/*!40000 ALTER TABLE `inventory_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `inventory_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inventory_loans`
--

DROP TABLE IF EXISTS `inventory_loans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `inventory_loans` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `inventory_item_id` bigint unsigned NOT NULL,
  `peminjam` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kontak_peminjam` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `unit_peminjam` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jumlah` int unsigned NOT NULL,
  `tanggal_pinjam` date NOT NULL,
  `batas_pengembalian` date NOT NULL,
  `tanggal_dikembalikan` timestamp NULL DEFAULT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'dipinjam',
  `kondisi_pengembalian` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `catatan` text COLLATE utf8mb4_unicode_ci,
  `catatan_pengembalian` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint unsigned DEFAULT NULL,
  `returned_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `inventory_loans_created_by_foreign` (`created_by`),
  KEY `inventory_loans_returned_by_foreign` (`returned_by`),
  KEY `inventory_loans_inventory_item_id_status_index` (`inventory_item_id`,`status`),
  KEY `inventory_loans_status_batas_pengembalian_index` (`status`,`batas_pengembalian`),
  KEY `inventory_loans_status_index` (`status`),
  CONSTRAINT `inventory_loans_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `inventory_loans_inventory_item_id_foreign` FOREIGN KEY (`inventory_item_id`) REFERENCES `inventory_items` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `inventory_loans_returned_by_foreign` FOREIGN KEY (`returned_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventory_loans`
--

LOCK TABLES `inventory_loans` WRITE;
/*!40000 ALTER TABLE `inventory_loans` DISABLE KEYS */;
/*!40000 ALTER TABLE `inventory_loans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inventory_movements`
--

DROP TABLE IF EXISTS `inventory_movements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `inventory_movements` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `inventory_item_id` bigint unsigned NOT NULL,
  `lokasi_asal` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `lokasi_tujuan` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `jumlah` int unsigned NOT NULL DEFAULT '1',
  `tanggal_mutasi` timestamp NOT NULL,
  `catatan` text COLLATE utf8mb4_unicode_ci,
  `moved_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `inventory_movements_moved_by_foreign` (`moved_by`),
  KEY `inventory_movements_inventory_item_id_tanggal_mutasi_index` (`inventory_item_id`,`tanggal_mutasi`),
  CONSTRAINT `inventory_movements_inventory_item_id_foreign` FOREIGN KEY (`inventory_item_id`) REFERENCES `inventory_items` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `inventory_movements_moved_by_foreign` FOREIGN KEY (`moved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inventory_movements`
--

LOCK TABLES `inventory_movements` WRITE;
/*!40000 ALTER TABLE `inventory_movements` DISABLE KEYS */;
/*!40000 ALTER TABLE `inventory_movements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `izin_latihans`
--

DROP TABLE IF EXISTS `izin_latihans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `izin_latihans` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `cabang_olahraga_id` bigint unsigned NOT NULL,
  `jenis_pemohon` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_pemohon` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tujuan` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `keperluan` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `tanggal_mulai` date NOT NULL,
  `tanggal_selesai` date DEFAULT NULL,
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_original_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'diajukan',
  `catatan_admin` text COLLATE utf8mb4_unicode_ci,
  `diproses_oleh` bigint unsigned DEFAULT NULL,
  `diproses_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `izin_latihans_cabang_olahraga_id_created_at_index` (`cabang_olahraga_id`,`created_at`),
  KEY `izin_latihans_user_id_created_at_index` (`user_id`,`created_at`),
  KEY `izin_latihans_status_index` (`status`),
  KEY `izin_latihans_diproses_oleh_foreign` (`diproses_oleh`),
  CONSTRAINT `izin_latihans_cabang_olahraga_id_foreign` FOREIGN KEY (`cabang_olahraga_id`) REFERENCES `cabang_olahraga` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `izin_latihans_diproses_oleh_foreign` FOREIGN KEY (`diproses_oleh`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `izin_latihans_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `izin_latihans`
--

LOCK TABLES `izin_latihans` WRITE;
/*!40000 ALTER TABLE `izin_latihans` DISABLE KEYS */;
INSERT INTO `izin_latihans` VALUES (1,12,3,'atlet','Mars','Admin Cabor Sepak Bola','Sakit','2026-11-11','2026-11-11','izin-latihan/atlet/5/aVkI67RK2czVMYU2EG2VSCrvjOpowhSaFbQj8E8j.docx','Surat Tugas.docx','disetujui',NULL,9,'2026-11-11 16:37:57','2026-11-11 15:36:52','2026-11-11 16:37:57'),(2,10,3,'pelatih','Charles','Admin Cabor','Sakit','2026-11-11','2026-11-11','izin-latihan/pelatih/3/DIoUL5wIk8jsHMjx1pV3LidVG2MRFYye1gub2J5G.docx','Surat Tugas.docx','ditolak','jangan',9,'2026-11-11 16:37:53','2026-11-11 15:37:31','2026-11-11 16:37:53');
/*!40000 ALTER TABLE `izin_latihans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` smallint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2026_11_08_042603_add_two_factor_columns_to_users_table',2),(5,'2026_11_08_042604_create_passkeys_table',2),(6,'2026_11_08_042618_create_permission_tables',2),(7,'2026_11_08_054237_create_cabang_olahragas_table',3),(8,'2026_11_08_061751_create_atlets_table',4),(9,'2026_11_08_070905_create_pelatihs_table',5),(10,'2026_11_08_073326_create_penguruses_table',6),(11,'2026_11_08_085400_add_cabang_olahraga_and_is_active_to_users_table',7),(12,'2026_11_11_000001_create_agendas_table',8),(13,'2026_11_11_000002_create_surats_table',9),(14,'2026_11_11_000003_create_prestasis_table',10),(15,'2026_11_11_000004_create_izin_latihans_table',10),(16,'2026_11_11_000005_add_jenis_medali_to_prestasis_table',11),(17,'2026_11_11_000006_create_publikasis_table',12),(18,'2026_11_11_000007_create_profil_koni_table',13),(19,'2026_11_11_000008_add_decision_and_account_fields',14),(20,'2026_11_11_000009_create_notifications_table',15),(21,'2026_11_19_000001_add_decision_by_role_to_surats_table',16),(22,'2026_11_19_000002_create_inventory_categories_table',17),(23,'2026_11_19_000003_create_inventory_items_table',17),(24,'2026_11_19_000004_create_inventory_movements_table',17),(25,'2026_11_19_000005_create_inventory_loans_table',17),(26,'2026_11_19_000006_create_audit_logs_table',18);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `model_has_permissions`
--

DROP TABLE IF EXISTS `model_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `model_has_permissions`
--

LOCK TABLES `model_has_permissions` WRITE;
/*!40000 ALTER TABLE `model_has_permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `model_has_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `model_has_roles`
--

DROP TABLE IF EXISTS `model_has_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_roles` (
  `role_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `model_has_roles`
--

LOCK TABLES `model_has_roles` WRITE;
/*!40000 ALTER TABLE `model_has_roles` DISABLE KEYS */;
INSERT INTO `model_has_roles` VALUES (4,'App\\Models\\User',1),(5,'App\\Models\\User',4),(3,'App\\Models\\User',6),(2,'App\\Models\\User',7),(6,'App\\Models\\User',8),(2,'App\\Models\\User',9),(6,'App\\Models\\User',10),(5,'App\\Models\\User',11),(5,'App\\Models\\User',12),(2,'App\\Models\\User',13),(5,'App\\Models\\User',14),(6,'App\\Models\\User',15),(3,'App\\Models\\User',16),(7,'App\\Models\\User',17),(8,'App\\Models\\User',18);
/*!40000 ALTER TABLE `model_has_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_id` bigint unsigned NOT NULL,
  `data` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`),
  KEY `notifications_owner_read_index` (`notifiable_type`,`notifiable_id`,`read_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
INSERT INTO `notifications` VALUES ('140690ff-610a-44eb-ba5c-096421295826','App\\Notifications\\WorkflowNotification','App\\Models\\User',17,'{\"title\":\"Surat menunggu persetujuan Ketua KONI\",\"message\":\"Staff KONI meneruskan surat \\\"gor\\\". Ketua atau Sekretaris KONI dapat memberikan keputusan secara mandiri.\",\"url\":\"\\/admin\\/persuratan\\/2\",\"category\":\"persuratan\"}','2026-11-19 21:41:55','2026-11-19 21:41:08','2026-11-19 21:41:55'),('361d1fc9-586b-4b14-9e03-d594b9d798ab','App\\Notifications\\WorkflowNotification','App\\Models\\User',6,'{\"title\":\"Surat baru menunggu pemeriksaan\",\"message\":\"Cabang Sepak Bola mengajukan surat \\\"gor\\\".\",\"url\":\"\\/staff\\/surat\\/2\",\"category\":\"persuratan\"}','2026-11-19 21:41:04','2026-11-19 21:40:47','2026-11-19 21:41:04'),('3f7b84ea-b874-4a4a-8f72-00b28c0b410b','App\\Notifications\\WorkflowNotification','App\\Models\\User',1,'{\"title\":\"Surat menunggu keputusan\",\"message\":\"Staff KONI meneruskan surat \\\"gor\\\". Ketua atau Sekretaris KONI dapat memberikan keputusan secara mandiri.\",\"url\":\"\\/admin\\/persuratan\\/2\",\"category\":\"persuratan\"}','2026-11-19 21:53:48','2026-11-19 21:41:08','2026-11-19 21:53:48'),('84e76b89-9b8d-47fe-9896-c04848da9aca','App\\Notifications\\WorkflowNotification','App\\Models\\User',18,'{\"title\":\"Surat menunggu persetujuan Sekretaris KONI\",\"message\":\"Staff KONI meneruskan surat \\\"gor\\\". Ketua atau Sekretaris KONI dapat memberikan keputusan secara mandiri.\",\"url\":\"\\/sekretaris\\/surat\\/2\",\"category\":\"persuratan\"}','2026-11-19 21:41:37','2026-11-19 21:41:08','2026-11-19 21:41:37'),('d933f152-6418-4fa2-b043-2c8cce656610','App\\Notifications\\WorkflowNotification','App\\Models\\User',16,'{\"title\":\"Surat baru menunggu pemeriksaan\",\"message\":\"Cabang Sepak Bola mengajukan surat \\\"gor\\\".\",\"url\":\"\\/staff\\/surat\\/2\",\"category\":\"persuratan\"}',NULL,'2026-11-19 21:40:47','2026-11-19 21:40:47');
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `passkeys`
--

DROP TABLE IF EXISTS `passkeys`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `passkeys` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `credential_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
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

--
-- Dumping data for table `passkeys`
--

LOCK TABLES `passkeys` WRITE;
/*!40000 ALTER TABLE `passkeys` DISABLE KEYS */;
/*!40000 ALTER TABLE `passkeys` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pelatihs`
--

DROP TABLE IF EXISTS `pelatihs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pelatihs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `cabang_olahraga_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `nama_lengkap` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `jenis_kelamin` varchar(1) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tempat_lahir` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `alamat` text COLLATE utf8mb4_unicode_ci,
  `no_telepon` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pendidikan_terakhir` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `spesialisasi` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sertifikasi` text COLLATE utf8mb4_unicode_ci,
  `pengalaman_tahun` tinyint unsigned DEFAULT NULL,
  `foto_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'aktif',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pelatihs_user_id_unique` (`user_id`),
  KEY `pelatihs_cabang_olahraga_id_foreign` (`cabang_olahraga_id`),
  KEY `pelatihs_status_index` (`status`),
  CONSTRAINT `pelatihs_cabang_olahraga_id_foreign` FOREIGN KEY (`cabang_olahraga_id`) REFERENCES `cabang_olahraga` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `pelatihs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pelatihs`
--

LOCK TABLES `pelatihs` WRITE;
/*!40000 ALTER TABLE `pelatihs` DISABLE KEYS */;
INSERT INTO `pelatihs` VALUES (2,2,8,'kakandaa','L','klaten','2026-09-30','klaten','08123456789','bulu@contoh.com','S1','teknik','ada',1,'pelatih/rhAHojm39dEZ5MBEh4x4OByY7RK64OfwBeWM2ODr.png','aktif','2026-11-08 07:20:19','2026-11-09 02:50:21'),(3,3,10,'Charles','L','klaten','2023-02-14','korsel','08123456789','sepak1@koniklaten.id','S2','jago','best liar',12,'pelatih/FM965vXF0QwgcmE3jZSkBwhVs6ynqdSPQ9Jamjo4.webp','aktif','2026-11-09 02:48:11','2026-11-09 06:38:51'),(4,3,NULL,'Vernon','L','California','2025-07-04','LA','08123456789','sepak3@koniklaten.id','S1','jago','ada',7,'pelatih/BAJzzW0j9agmLQqfLEROaRQCkfh2TgCyZAlxj8AY.jpg','aktif','2026-11-09 04:19:57','2026-11-09 04:19:57'),(5,4,15,'Wonu','L','Solo','2022-08-02','Yogyakarta','351619135161','water1@koniklaten.id','S1','terbang','Internasional',5,'pelatih/7h5hwiaFUHAnnPHCUkv7kCEapc03U2kN3aexrtEU.jpg','aktif','2026-11-11 16:44:52','2026-11-11 16:44:53');
/*!40000 ALTER TABLE `pelatihs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pengurus`
--

DROP TABLE IF EXISTS `pengurus`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pengurus` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `cabang_olahraga_id` bigint unsigned DEFAULT NULL,
  `nama_lengkap` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `jabatan` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `bidang` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jenis_kelamin` varchar(1) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `periode_mulai` smallint unsigned DEFAULT NULL,
  `periode_selesai` smallint unsigned DEFAULT NULL,
  `no_telepon` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `foto_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `urutan` smallint unsigned NOT NULL DEFAULT '0',
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'aktif',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pengurus_user_id_unique` (`user_id`),
  KEY `pengurus_cabang_olahraga_id_foreign` (`cabang_olahraga_id`),
  KEY `pengurus_status_index` (`status`),
  CONSTRAINT `pengurus_cabang_olahraga_id_foreign` FOREIGN KEY (`cabang_olahraga_id`) REFERENCES `cabang_olahraga` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `pengurus_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pengurus`
--

LOCK TABLES `pengurus` WRITE;
/*!40000 ALTER TABLE `pengurus` DISABLE KEYS */;
INSERT INTO `pengurus` VALUES (1,NULL,NULL,'Parwanto','Ketua Umum','UMUM','L',2023,2028,'08123456789','ffath@contoh.com','pengurus/4rxXm0n6fE0jjuORX5fFpoR76eS0tssXZ3vgOMOb.jpg',1,'aktif','2026-11-08 08:29:40','2026-11-08 08:29:40'),(2,NULL,2,'kakanda','Ketua Umum','UMUM','L',2025,2029,'08123456789','fath@contoh.com','pengurus/t6suwWxa54cmIfaeljI63nKeFdubVuCqotvHTUWF.png',1,'aktif','2026-11-08 08:30:44','2026-11-08 08:30:44'),(3,NULL,2,'sapa','Sekertaris','pembinaan prestasi','L',2025,2029,'08978654321',NULL,'pengurus/JykLsWQ0L1TOZFEIgOXKJBzd3YW72TZKSRRFRDC7.jpg',2,'aktif','2026-11-09 05:28:50','2026-11-09 05:28:50'),(4,16,NULL,'Mingyu','Bendahara','UMUM','L',2025,2029,'211516135164','bend@koniklaten.id','pengurus/6XDMOfcqO8BDM9j9arJYKkDH7KlU1MJ26SN59i5L.png',3,'aktif','2026-11-11 16:48:41','2026-11-11 16:49:25');
/*!40000 ALTER TABLE `pengurus` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `prestasis`
--

DROP TABLE IF EXISTS `prestasis`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `prestasis` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `atlet_id` bigint unsigned DEFAULT NULL,
  `pelatih_id` bigint unsigned DEFAULT NULL,
  `cabang_olahraga_id` bigint unsigned NOT NULL,
  `verified_by` bigint unsigned DEFAULT NULL,
  `nama_kejuaraan` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tingkat` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kategori` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hasil` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `penyelenggara` varchar(180) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tanggal_prestasi` date NOT NULL,
  `keterangan` text COLLATE utf8mb4_unicode_ci,
  `bukti_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `bukti_original_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'menunggu_verifikasi',
  `catatan_verifikasi` text COLLATE utf8mb4_unicode_ci,
  `verified_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `jenis_medali` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `prestasis_pelatih_id_foreign` (`pelatih_id`),
  KEY `prestasis_verified_by_foreign` (`verified_by`),
  KEY `prestasis_cabang_olahraga_id_status_index` (`cabang_olahraga_id`,`status`),
  KEY `prestasis_atlet_id_status_index` (`atlet_id`,`status`),
  KEY `prestasis_tingkat_index` (`tingkat`),
  KEY `prestasis_tanggal_prestasi_index` (`tanggal_prestasi`),
  KEY `prestasis_status_index` (`status`),
  KEY `prestasis_jenis_medali_index` (`jenis_medali`),
  CONSTRAINT `prestasis_atlet_id_foreign` FOREIGN KEY (`atlet_id`) REFERENCES `atlets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `prestasis_cabang_olahraga_id_foreign` FOREIGN KEY (`cabang_olahraga_id`) REFERENCES `cabang_olahraga` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `prestasis_pelatih_id_foreign` FOREIGN KEY (`pelatih_id`) REFERENCES `pelatihs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `prestasis_verified_by_foreign` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `prestasis`
--

LOCK TABLES `prestasis` WRITE;
/*!40000 ALTER TABLE `prestasis` DISABLE KEYS */;
INSERT INTO `prestasis` VALUES (1,5,NULL,3,9,'sepak bola','lokal','10','juara 1','koni','2026-11-01',NULL,'prestasi/atlet/5/Erc23AyjuWCMBk7avzYXD3RlUSrmegu26XA5ygx2.pdf','Proposal Kegiatan Magang Sapuangin.pdf','ditolak','jangan','2026-11-11 16:38:42','2026-11-11 15:45:52','2026-11-11 16:38:42','emas'),(2,NULL,3,3,9,'sepak bola','lokal','10','Juara 1','koni','2026-11-01',NULL,'prestasi/pelatih/3/SZ671rADsOZyO7fNTSZEExa6MgEJWrJV3zmKiVkw.pdf','Proposal Kegiatan Magang Sapuangin.pdf','terverifikasi',NULL,'2026-11-11 15:47:05','2026-11-11 15:46:47','2026-11-11 15:47:05','emas');
/*!40000 ALTER TABLE `prestasis` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `profil_koni`
--

DROP TABLE IF EXISTS `profil_koni`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `profil_koni` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nama_organisasi` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_singkat` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tagline` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deskripsi` text COLLATE utf8mb4_unicode_ci,
  `sejarah` longtext COLLATE utf8mb4_unicode_ci,
  `visi` text COLLATE utf8mb4_unicode_ci,
  `misi` text COLLATE utf8mb4_unicode_ci,
  `alamat` text COLLATE utf8mb4_unicode_ci,
  `telepon` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `website` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `instagram` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `facebook` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `youtube` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logo_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `profil_koni_updated_by_foreign` (`updated_by`),
  CONSTRAINT `profil_koni_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `profil_koni`
--

LOCK TABLES `profil_koni` WRITE;
/*!40000 ALTER TABLE `profil_koni` DISABLE KEYS */;
INSERT INTO `profil_koni` VALUES (1,'Komite Olahraga Nasional Indonesia Kabupaten Klaten','KONI Klaten','Bersama membangun prestasi olahraga Kabupaten Klaten.','Komite Olahraga Nasional Indonesia (KONI) Kabupaten Klaten merupakan wadah koordinasi pembinaan dan pengembangan olahraga prestasi di tingkat kabupaten.',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-11-11 06:31:00','2026-11-11 06:31:00');
/*!40000 ALTER TABLE `profil_koni` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `publikasis`
--

DROP TABLE IF EXISTS `publikasis`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `publikasis` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `published_by` bigint unsigned DEFAULT NULL,
  `judul` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(220) COLLATE utf8mb4_unicode_ci NOT NULL,
  `jenis` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kategori` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ringkasan` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `konten` longtext COLLATE utf8mb4_unicode_ci,
  `media_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `media_original_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `thumbnail_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `thumbnail_original_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `published_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `publikasis_slug_unique` (`slug`),
  KEY `publikasis_created_by_foreign` (`created_by`),
  KEY `publikasis_updated_by_foreign` (`updated_by`),
  KEY `publikasis_published_by_foreign` (`published_by`),
  KEY `publikasis_jenis_status_published_at_index` (`jenis`,`status`,`published_at`),
  KEY `publikasis_jenis_index` (`jenis`),
  KEY `publikasis_kategori_index` (`kategori`),
  KEY `publikasis_status_index` (`status`),
  CONSTRAINT `publikasis_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `publikasis_published_by_foreign` FOREIGN KEY (`published_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `publikasis_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `publikasis`
--

LOCK TABLES `publikasis` WRITE;
/*!40000 ALTER TABLE `publikasis` DISABLE KEYS */;
/*!40000 ALTER TABLE `publikasis` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_has_permissions`
--

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

--
-- Dumping data for table `role_has_permissions`
--

LOCK TABLES `role_has_permissions` WRITE;
/*!40000 ALTER TABLE `role_has_permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `role_has_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'atlet_pelatih','web','2026-11-08 04:32:22','2026-11-08 04:32:22'),(2,'admin_cabor','web','2026-11-08 04:32:22','2026-11-08 04:32:22'),(3,'staff_koni','web','2026-11-08 04:32:22','2026-11-08 04:32:22'),(4,'admin_koni','web','2026-11-08 04:32:22','2026-11-08 04:32:22'),(5,'atlet','web','2026-11-08 08:55:31','2026-11-08 08:55:31'),(6,'pelatih','web','2026-11-08 08:55:31','2026-11-08 08:55:31'),(7,'ketua_koni','web','2026-11-19 18:27:49','2026-11-19 18:27:49'),(8,'sekretaris_koni','web','2026-11-19 18:27:49','2026-11-19 18:27:49');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES ('ptX5nq7g04IkPHulVbTOVlehn1qABOCS6GefZNDd',1,'172.18.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36','eyJfdG9rZW4iOiJYTVd3U1Z4RU5jMXltd0Q4a0Z3SWs1d1RnOHRNTmVVam4wRzhlakJnIiwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119LCJfcHJldmlvdXMiOnsidXJsIjoiaHR0cDpcL1wvbG9jYWxob3N0XC9hZG1pblwvcGVuZ2F0dXJhbiIsInJvdXRlIjoiYWRtaW4uc2V0dGluZ3MifSwibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiOjEsInBhc3N3b3JkX2hhc2hfd2ViIjoiOWI1MTNkMmRhZWZmYjliNTU0Nzk1OGNmOGI5NmIxODU5YzhlMTJkOTFlM2ZjOTE2MTRkZDI5ZDVlODEwODg1ZSJ9',1795100061);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `surat_activities`
--

DROP TABLE IF EXISTS `surat_activities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `surat_activities` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `surat_id` bigint unsigned NOT NULL,
  `actor_id` bigint unsigned DEFAULT NULL,
  `action` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status_from` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status_to` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `catatan` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `surat_activities_actor_id_foreign` (`actor_id`),
  KEY `surat_activities_surat_id_created_at_index` (`surat_id`,`created_at`),
  CONSTRAINT `surat_activities_actor_id_foreign` FOREIGN KEY (`actor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `surat_activities_surat_id_foreign` FOREIGN KEY (`surat_id`) REFERENCES `surats` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `surat_activities`
--

LOCK TABLES `surat_activities` WRITE;
/*!40000 ALTER TABLE `surat_activities` DISABLE KEYS */;
INSERT INTO `surat_activities` VALUES (1,1,9,'surat_dibuat',NULL,'draft','Surat dibuat sebagai draft.','2026-11-11 15:41:59','2026-11-11 15:41:59'),(2,1,9,'surat_diajukan','draft','diajukan','Surat diajukan kepada Staff KONI.','2026-11-11 15:42:07','2026-11-11 15:42:07'),(3,1,6,'surat_diteruskan_admin','diajukan','diteruskan_admin','Surat diteruskan kepada Admin KONI.','2026-11-11 15:42:33','2026-11-11 15:42:33'),(4,1,1,'keputusan_admin','diteruskan_admin','menunggu_penyampaian','Keputusan Admin KONI: diterima.','2026-11-11 15:43:01','2026-11-11 15:43:01'),(5,1,6,'hasil_disampaikan','menunggu_penyampaian','diterima','Staff KONI menyampaikan hasil keputusan kepada Admin Cabor.','2026-11-11 15:43:19','2026-11-11 15:43:19'),(6,2,9,'surat_dibuat',NULL,'draft','Surat dibuat sebagai draft.','2026-11-19 21:40:41','2026-11-19 21:40:41'),(7,2,9,'surat_diajukan','draft','diajukan','Surat diajukan kepada Staff KONI.','2026-11-19 21:40:47','2026-11-19 21:40:47'),(8,2,6,'surat_diteruskan_admin','diajukan','diteruskan_admin','Surat diteruskan kepada pimpinan KONI.','2026-11-19 21:41:08','2026-11-19 21:41:08');
/*!40000 ALTER TABLE `surat_activities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `surats`
--

DROP TABLE IF EXISTS `surats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `surats` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `cabang_olahraga_id` bigint unsigned DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `staff_id` bigint unsigned DEFAULT NULL,
  `admin_koni_id` bigint unsigned DEFAULT NULL,
  `decision_by_role` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `perihal` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `jenis_surat` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'permohonan',
  `nomor_surat` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tanggal_surat` date NOT NULL,
  `ringkasan` text COLLATE utf8mb4_unicode_ci,
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_original_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `staff_attachment_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `staff_attachment_original_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `admin_response_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `admin_response_original_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `keputusan_admin` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `catatan_staff` text COLLATE utf8mb4_unicode_ci,
  `catatan_admin` text COLLATE utf8mb4_unicode_ci,
  `diajukan_at` timestamp NULL DEFAULT NULL,
  `diteruskan_at` timestamp NULL DEFAULT NULL,
  `diputuskan_at` timestamp NULL DEFAULT NULL,
  `disampaikan_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `surats_created_by_foreign` (`created_by`),
  KEY `surats_staff_id_foreign` (`staff_id`),
  KEY `surats_admin_koni_id_foreign` (`admin_koni_id`),
  KEY `surats_cabang_olahraga_id_status_index` (`cabang_olahraga_id`,`status`),
  KEY `surats_status_created_at_index` (`status`,`created_at`),
  KEY `surats_status_index` (`status`),
  CONSTRAINT `surats_admin_koni_id_foreign` FOREIGN KEY (`admin_koni_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `surats_cabang_olahraga_id_foreign` FOREIGN KEY (`cabang_olahraga_id`) REFERENCES `cabang_olahraga` (`id`) ON DELETE SET NULL,
  CONSTRAINT `surats_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `surats_staff_id_foreign` FOREIGN KEY (`staff_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `surats`
--

LOCK TABLES `surats` WRITE;
/*!40000 ALTER TABLE `surats` DISABLE KEYS */;
INSERT INTO `surats` VALUES (1,3,9,6,1,NULL,'ajuan anggaran','permohonan',NULL,'2026-11-11','anggaran','surat/original/DsOXXrTnskdZO59Ebrj0BbZzQIMELDunEdi9u8ps.pdf','ToR Kegiatan Magang UMKLA.pdf',NULL,NULL,NULL,NULL,'diterima','diterima',NULL,NULL,'2026-11-11 15:42:07','2026-11-11 15:42:33','2026-11-11 15:43:01','2026-11-11 15:43:19','2026-11-11 15:41:59','2026-11-11 15:43:19'),(2,3,9,6,NULL,NULL,'gor','permohonan','12','2026-11-19','12','surat/original/QNlUtF0Kh3rGVgt2oocWWGfDKsGCz9m4LU9uCDkB.png','download.png',NULL,NULL,NULL,NULL,'diteruskan_admin',NULL,NULL,NULL,'2026-11-19 21:40:47','2026-11-19 21:41:08',NULL,NULL,'2026-11-19 21:40:41','2026-11-19 21:41:08');
/*!40000 ALTER TABLE `surats` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `template_surats`
--

DROP TABLE IF EXISTS `template_surats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `template_surats` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `judul` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `deskripsi` text COLLATE utf8mb4_unicode_ci,
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_original_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `uploaded_by` bigint unsigned DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `template_surats_uploaded_by_foreign` (`uploaded_by`),
  KEY `template_surats_is_active_index` (`is_active`),
  CONSTRAINT `template_surats_uploaded_by_foreign` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `template_surats`
--

LOCK TABLES `template_surats` WRITE;
/*!40000 ALTER TABLE `template_surats` DISABLE KEYS */;
/*!40000 ALTER TABLE `template_surats` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `two_factor_secret` text COLLATE utf8mb4_unicode_ci,
  `two_factor_recovery_codes` text COLLATE utf8mb4_unicode_ci,
  `two_factor_confirmed_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `cabang_olahraga_id` bigint unsigned DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_cabang_olahraga_id_foreign` (`cabang_olahraga_id`),
  CONSTRAINT `users_cabang_olahraga_id_foreign` FOREIGN KEY (`cabang_olahraga_id`) REFERENCES `cabang_olahraga` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Administrator KONI','admin@koniklaten.local',NULL,'$2y$12$B3XTS38gU7UXo9VjrqO5geCjL9U/iOtl7fgKe30uErBv3Aq..AEqi',NULL,NULL,NULL,'WAT50RUAawvxBcLqp8whC76Umcb0ESYwvZbagbMmTt8bbLeHMPyCWtQTJt0a','2026-11-08 05:16:16','2026-11-08 05:16:16',NULL,1),(4,'Naila Fath','fath@contoh.com',NULL,'$2y$12$1o4O8xKRgiCdVub.XwKxf./9NofOg2QaoI.kNobFryURihl4z4ZfK',NULL,NULL,NULL,NULL,'2026-11-08 10:25:49','2026-11-08 10:25:49',NULL,1),(6,'staf koni','staf@koniklaten.id',NULL,'$2y$12$j8wIQvxWChLUDOikq5FF7OimVi1iTWGxQva4L/wdPbKIn65z0xgY2',NULL,NULL,NULL,NULL,'2026-11-09 00:43:43','2026-11-09 05:43:48',NULL,1),(7,'kakanda','ffath@contoh.com',NULL,'$2y$12$Q.zKL5Upr2LIbCjlanL1ZeNLb2cR2Mh8CAHYLe.YYyISdxiYt0HxG',NULL,NULL,NULL,NULL,'2026-11-09 02:37:04','2026-11-09 02:37:04',2,1),(8,'kakanda','bulu@contoh.com',NULL,'$2y$12$lfK6PCUpdJ/C6hr8mpJkvu9q5hAmRgjdnLv87Eum3LFbkbYkiLklG',NULL,NULL,NULL,NULL,'2026-11-09 02:50:21','2026-11-09 02:50:21',NULL,1),(9,'charles','sepak@koniklaten.id',NULL,'$2y$12$UFpzT6T9g3r3uurgKot5g.mIqv12vffyvGH4x6OQPTuTI4NuOwfH6',NULL,NULL,NULL,NULL,'2026-11-09 02:50:58','2026-11-09 02:50:58',3,1),(10,'charles','sepak1@koniklaten.id',NULL,'$2y$12$GEWTc6kjBoZ/24zdc/pAue5HNPNtPxIlxjmHwv9NqrGBcfR/PpKKG',NULL,NULL,NULL,NULL,'2026-11-09 02:53:03','2026-11-09 02:53:03',NULL,1),(11,'josh','sepak2@koniklaten.id',NULL,'$2y$12$o/fkt.5RmX4nAKoYmd5cPOXfvsLHXysTr0Sc6WJ98ECrIjLlngx.K',NULL,NULL,NULL,NULL,'2026-11-09 02:53:36','2026-11-09 02:53:36',NULL,1),(12,'Mars','sepak4@koniklaten.id',NULL,'$2y$12$4ipl/auVzfAeaLegQyBEpuMVkBBrZS7TlZlHKI2gRI8bII4VRudT2',NULL,NULL,NULL,NULL,'2026-11-09 06:29:58','2026-11-09 06:29:58',NULL,1),(13,'DK','water@koniklaten.id',NULL,'$2y$12$LCck1gVYe7a.f8R9uZvvXesfeISh8BqnqMcOBlmf8MwTqejTdtT2S',NULL,NULL,NULL,NULL,'2026-11-11 16:37:19','2026-11-11 16:37:19',4,1),(14,'Jeonghan','water2@koniklaten.id',NULL,'$2y$12$lm0Vr8c2ot5p8c6gO3d/cuj99DD9b2Td2rxuAcXPZ42ChpB8LCBjO',NULL,NULL,NULL,NULL,'2026-11-11 16:42:12','2026-11-11 16:42:12',NULL,1),(15,'Wonu','water1@koniklaten.id',NULL,'$2y$12$zZ36mBb3c/ChiR865lm2b.tIyvQDdwaisGcgAQxq2B/RybpsWjKc.',NULL,NULL,NULL,NULL,'2026-11-11 16:44:53','2026-11-11 16:44:53',NULL,1),(16,'Mingyu','bend@koniklaten.id',NULL,'$2y$12$fslYhvInPetd4zLTM3o9xOgUd.IKruM1dMXEJVLHliwOM8QSnqwua',NULL,NULL,NULL,NULL,'2026-11-11 16:48:41','2026-11-11 16:48:41',NULL,1),(17,'Ketua KONI','ketua@koniklaten.id',NULL,'$2y$12$W0NI8b5skZr.hWUyuWvUsetcV87PXXgWG6.ayy2DWMoUtFeBgFpXa',NULL,NULL,NULL,NULL,'2026-11-19 18:43:34','2026-11-19 18:43:34',NULL,1),(18,'Sekertaris KONI','sekertaris@koniklaten.id',NULL,'$2y$12$okjutE8/mjMRAsM1Fiz.TORL6hvwNoQFw.nNXISrmDYjcWQt3lrqy',NULL,NULL,NULL,NULL,'2026-11-19 18:44:16','2026-11-19 18:44:16',NULL,1);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-11-20  2:33:41
