-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: 127.0.0.1    Database: nnaco_sa
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `admin_audit_logs`
--

DROP TABLE IF EXISTS `admin_audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admin_audit_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `admin_id` int(11) DEFAULT NULL,
  `action` varchar(50) NOT NULL,
  `target_type` varchar(50) NOT NULL,
  `target_id` varchar(100) NOT NULL,
  `details` varchar(255) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_admin_id` (`admin_id`),
  KEY `idx_target` (`target_type`,`target_id`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin_audit_logs`
--

LOCK TABLES `admin_audit_logs` WRITE;
/*!40000 ALTER TABLE `admin_audit_logs` DISABLE KEYS */;
INSERT INTO `admin_audit_logs` VALUES (5,1,'view_conversation','chat_conversation','conv_023713a091e11b6f7ff1','success','::1','2026-10-23 05:02:12'),(9,1,'view_conversation','chat_conversation','conv_023713a091e11b6f7ff1','success','::1','2026-10-25 10:56:18');
/*!40000 ALTER TABLE `admin_audit_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ai_chat_history`
--

DROP TABLE IF EXISTS `ai_chat_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ai_chat_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `conversation_id` varchar(64) DEFAULT NULL,
  `session_id` varchar(64) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `message` text NOT NULL,
  `response` text NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `model_id` varchar(100) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'completed',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `idx_conversation_id` (`conversation_id`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `ai_chat_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=61 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ai_chat_history`
--

LOCK TABLES `ai_chat_history` WRITE;
/*!40000 ALTER TABLE `ai_chat_history` DISABLE KEYS */;
INSERT INTO `ai_chat_history` VALUES (10,'conv_023713a091e11b6f7ff1','fvpjoknb3v3cssa0f0jb42cgqv',12,'saya ingin mencari gelang untuk hadiah ulang tahun','Maaf, terjadi gangguan jaringan ke server otak AI saya. (Detail: cURL Error: Operation timed out after 45016 milliseconds with 0 bytes received)','::1','meta/llama-3.2-11b-vision-instruct','failed','2026-10-23 04:45:02'),(11,'conv_023713a091e11b6f7ff1','fvpjoknb3v3cssa0f0jb42cgqv',12,'saya ingin mencari cincin','Maaf, terjadi gangguan jaringan ke server otak AI saya. (Detail: cURL Error: Operation timed out after 45011 milliseconds with 0 bytes received)','::1','meta/llama-3.2-11b-vision-instruct','failed','2026-10-23 04:46:44'),(12,'conv_e9bc95bfa7b4b00b56ac','7d44qck4evasj2jub4vu5aq84q',NULL,'saya ingin mencari cincin','Hai! Selamat datang di NNACO SA 😊 Saya NNACO Assistant, siap membantu Anda mencari cincin yang cocok.\n\nBerikut pilihan cincin yang tersedia saat ini:\n\n**1. Cincin Resin Aesthetic** — Rp 75.000\n- Kategori: Cincin\n- Stok: 29 unit\n- Deskripsi: Cincin buatan tangan dihiasi kelopak bunga kering asli.\n\n**2. cincin bunga** — Rp 100.000\n- Kategori: Cincin\n- Stok: 11 unit\n- Deskripsi: Cincin bunga dengan tampilan estetik.\n\nSemua cincin kami merupakan koleksi aksesoris handmade dengan hiasan unik, cocok untuk berbagai acara dan gaya.\n\nAda juga kategori lain seperti Kalung, Gelang, dan Anting jika Anda ingin melihat koleksi lengkapnya.\n\nApakah ada cincin tertentu yang Anda sukai, atau ingin saya bantu rekomendasikan sesuai kebutuhan Anda? Untuk melakukan pemesanan, Anda dapat login terlebih dahulu ke akun Anda. 🛍️','::1','minimaxai/minimax-m3.1-flash','completed','2026-10-23 04:58:13'),(17,'conv_023713a091e11b6f7ff1','fvpjoknb3v3cssa0f0jb42cgqv',12,'saya ingin mencari gelang untuk hadiah','Halo, Fadillah! Terima kasih sudah mampir di NNACO SA 😊\n\nUntuk hadiah, pilihan gelang yang kami rekomendasikan adalah:\n\n**💎 Gelang Mutiara Soft**\n- Harga: **Rp 120.000**\n- Stok: 18 unit\n- Deskripsi: Gelang mutiara lembut dengan tali peregangan super kuat, nyaman dipakai dan tampilannya elegan — cocok banget untuk hadiah.\n\nKalau ingin pilihan hadiah lain, kami juga punya:\n- **Kalung Titanium Rose Gold** – Rp 150.000 (anti karat, kilau rose gold menawan)\n- **Cincin Resin Aesthetic** – Rp 75.000 (dihiasi kelopak bunga kering asli)\n\n💡 **Tips:** Kode kupon **WELCOME50** aktif dengan potongan **50%** untuk pembelian minimal Rp 50.000, berlaku sampai 1 Januari 2027. Jadi gelang Mutiara Soft bisa jadi **Rp 60.000** setelah diskon — hemat Rp 60.000!\n\nMau saya bantu siapkan gelang mana untuk keranjang belanja Anda? Tinggal sebutkan jenis hadiahnya, untuk siapa, dan budget-nya ya. 🎁','::1','minimaxai/minimax-m3.1-flash','completed','2026-10-23 05:00:35'),(18,'conv_023713a091e11b6f7ff1','fvpjoknb3v3cssa0f0jb42cgqv',12,'siapa saya','Anda adalah **CUSTOMER (pelanggan toko NNACO SA)** bernama **Fadillah**, dengan email **sitinurfadillahhh07@gmail.com**, dan saat ini sedang login di akunnya. 😊\n\nAda yang bisa saya bantu seputar produk, stok, promo, atau pesanan Anda?','::1','minimaxai/minimax-m3.1-flash','completed','2026-10-23 05:00:47'),(19,'conv_023713a091e11b6f7ff1','fvpjoknb3v3cssa0f0jb42cgqv',12,'siapa anda','Saya adalah **NNACO Assistant**, AI Customer Service & Asisten Toko resmi dari **NNACO SA**, siap membantu Anda seputar produk aksesoris, stok, promo, dan pesanan. 😊','::1','minimaxai/minimax-m3.1-flash','completed','2026-10-23 05:00:56'),(20,'conv_023713a091e11b6f7ff1','fvpjoknb3v3cssa0f0jb42cgqv',12,'jam operasional toko ini dari jam berapa sampai jam berapa?','Maaf, terjadi gangguan jaringan ke server otak AI saya. (Detail: HTTP 429: Server provider gagal merespons dengan benar.)','::1','minimaxai/minimax-m3.1-flash','failed','2026-10-23 05:01:19'),(21,'conv_023713a091e11b6f7ff1','b7nbgmga2pbrmqbd4090o10u3m',1,'siapa saya','Anda adalah **ADMIN (Administrator Sistem / Toko NNACO SA)** bernama **Admin Utama**, dengan alamat email **admin@nnacosa.com**. Saat ini Anda sedang login di akun Admin Anda. 🛍️\n\nSebagai Administrator, Anda memiliki akses ke pengelolaan data toko NNACO SA, termasuk manajemen pesanan, stok barang, data pengguna, hingga promo dan kupon aktif. Ada yang bisa saya bantu regarding pengelolaan toko? 😊','::1','minimaxai/minimax-m3.1-flash','completed','2026-10-23 07:06:28'),(42,'conv_8cc98085b864d47a2e85','b25oecktdaqm5ra9ft9mn2kqf3',1,'siapa anda','Halo! 👋 Saya adalah **NNACO Assistant**, AI Customer Service & Asisten Toko resmi dari **toko aksesoris NNACO SA**.\n\nSaya siap membantu Anda seputar:\n- 🛍️ **Produk aksesoris** handmade (kalung, cincin, gelang, dan anting)\n- 📦 **Stok barang** yang tersedia\n- 🎁 **Promo & kupon diskon**\n- 🧾 **Pesanan, status pengiriman, dan transaksi toko**\n\nAda yang bisa saya bantu hari ini? 😊','::1','minimaxai/minimax-m3.1-flash','completed','2026-10-25 12:01:24'),(43,'conv_8cc98085b864d47a2e85','b25oecktdaqm5ra9ft9mn2kqf3',1,'siapa saya','Selamat datang, **Admin Utama**! 👋\n\nAnda adalah **ADMIN (Administrator Sistem / Toko NNACO SA)** dengan email **admin@nnacosa.com**, dan saat ini Anda sedang login di akun Anda.\n\nSebagai Administrator, Anda memiliki akses penuh untuk mengelola data toko NNACO SA, termasuk:\n\n- 📦 **Manajemen pesanan** (masuk, diproses, dikirim, selesai)\n- 💰 **Data keuangan & pemasukan toko**\n- 🏷️ **Stok barang masuk/keluar dan inventori produk**\n- 🎁 **Kupon diskon & promo**\n- 👥 **Data pengguna terdaftar**\n\nSilakan sampaikan apa yang ingin dikelola atau ditanyakan, Admin Utama. Saya siap membantu! 😊','::1','minimaxai/minimax-m3.1-flash','completed','2026-10-25 12:14:25'),(44,'conv_8cc98085b864d47a2e85','b25oecktdaqm5ra9ft9mn2kqf3',1,'ada berapa jumlah pengguna','Total pengguna yang terdaftar di database sistem NNACO SA adalah **8 pengguna**, dengan rincian:\n\n- **1 Admin** (Administrator Sistem/Toko)\n- **5 Customer** (Pelanggan)\n- **1 Operator**\n- **1 Owner**\n\nJika ada yang ingin ditanyakan lebih lanjut seputar pengguna, stok, promo, atau pesanan, silakan sampaikan ya! 😊','::1','minimaxai/minimax-m3.1-flash','completed','2026-10-25 12:15:10'),(45,'conv_07d3623e707772b7e960','43md8lsvr90cf558hoc1kkt9re',1,'sebutkan user dengan role customer','Maaf, terjadi gangguan jaringan ke server otak AI saya. (Detail: cURL Error: Operation timed out after 30010 milliseconds with 0 bytes received)','::1','minimaxai/minimax-m3.1-flash','failed','2026-11-06 02:16:04'),(46,'conv_3ffac2ac9e18f2ab07c8','06e39q9fl0acef5rav6als0koj',51,'saya ingin mencari gelang yang cocok untuk hadiah ulang tahun','Maaf, terjadi gangguan jaringan ke server otak AI saya. (Detail: cURL Error: Operation timed out after 30013 milliseconds with 0 bytes received)','::1','minimaxai/minimax-m3.1-flash','failed','2026-11-10 04:46:52'),(47,'conv_3ffac2ac9e18f2ab07c8','06e39q9fl0acef5rav6als0koj',51,'hi','Maaf, terjadi gangguan jaringan ke server otak AI saya. (Detail: cURL Error: Operation timed out after 30012 milliseconds with 0 bytes received)','::1','minimaxai/minimax-m3.1-flash','failed','2026-11-10 04:47:33'),(48,'conv_3ffac2ac9e18f2ab07c8','06e39q9fl0acef5rav6als0koj',51,'hi','Maaf, terjadi gangguan jaringan ke server otak AI saya. (Detail: cURL Error: Operation timed out after 30009 milliseconds with 0 bytes received)','::1','minimaxai/minimax-m3.1-flash','failed','2026-11-10 04:57:16'),(49,'conv_test_1794287299','alsvkun1jbsec06vk636p1satt',NULL,'saya ingin mencari gelang yang cocok untuk hadiah ulang tahun','Halo! NNACO SA punya beberapa pilihan gelang yang cantik dan cocok untuk hadiah ulang tahun. 😊\n\nSaat ini, kami memiliki:\n*   **Gelang Mutiara Soft:** Harga Rp 120.000. Deskripsinya adalah gelang mutiara lembut dengan tali peregangan super kuat. (Stok: 18 unit)\n\nKategori gelang kami juga mencakup gelang mutiara dan kristal.\n\nApakah Anda ingin mengetahui detail lebih lanjut mengenai **Gelang Mutiara Soft**, atau mungkin Anda tertarik melihat produk aksesoris lain seperti **Cincin** atau **Kalung** yang juga cocok dijadikan hadiah?\n\nJika Anda sudah menemukan yang cocok, silakan beritahu saya ya, nanti saya bantu proses pemesanan! ✨','::1','nvidia/nemotron-3.5-bolt-e4b','completed','2026-11-10 05:08:22'),(50,'conv_test_1794287299','cfp8biivpcev14acun0m06kvhm',NULL,'hi','Halo! 😊 Saya NNACO Assistant, AI Customer Service & Asisten Toko resmi dari toko aksesoris NNACO SA.\n\nAda yang bisa saya bantu hari ini? Apakah Anda ingin melihat koleksi produk, informasi promo, cek jam operasional, atau ada pertanyaan lain seputar NNACO SA? ✨','::1','nvidia/nemotron-3.5-bolt-e4b','completed','2026-11-10 05:08:55'),(55,'conv_3ffac2ac9e18f2ab07c8','06e39q9fl0acef5rav6als0koj',51,'hi','Halo Fadillahhh! 😊 Saya NNACO Assistant, AI Customer Service & Asisten Toko resmi dari toko aksesoris NNACO SA.\n\nAda yang bisa saya bantu hari ini? Apakah Anda ingin melihat produk, mengecek status pesanan, atau ada pertanyaan lain seputar NNACO SA? ✨','::1','nvidia/nemotron-3.5-bolt-e4b','completed','2026-11-10 05:10:35'),(56,'conv_3ffac2ac9e18f2ab07c8','06e39q9fl0acef5rav6als0koj',51,'saya ingin mencari gelang','Tentu, NNACO SA menyediakan berbagai gelang cantik!\n\nKami memiliki **Gelang Mutiara Soft** yang terbuat dari mutiara lembut dengan tali peregangan super kuat.\n\n*   **Kategori:** Gelang\n*   **Harga:** Rp 120.000\n*   **Stok Tersedia:** 18 unit\n*   **Deskripsi:** Gelang mutiara lembut dengan tali peregangan super kuat.\n\nApakah Anda ingin melihat detail lebih lanjut, atau mungkin ingin mengetahui kategori produk lain seperti Cincin atau Kalung? 😊','::1','nvidia/nemotron-3.5-bolt-e4b','completed','2026-11-10 05:10:51'),(57,'conv_3ffac2ac9e18f2ab07c8','06e39q9fl0acef5rav6als0koj',51,'siapa saya','Anda adalah CUSTOMER (pelanggan toko NNACO SA) bernama \'Fadillahhh\' (email: fadillahhh@gmail.com) yang saat ini sedang login di akun Anda.\n\nAda yang bisa saya bantu terkait produk aksesoris, pesanan, atau informasi toko NNACO SA lainnya? 😊','::1','nvidia/nemotron-3.5-bolt-e4b','completed','2026-11-10 05:10:58'),(58,'conv_9f7af01af571343c0357','8dm7a8j62tigvffn229210km54',NULL,'hi','Halo! 👋 Saya NNACO Assistant, AI Customer Service & Asisten Toko resmi dari toko aksesoris NNACO SA.\n\nAda yang bisa saya bantu hari ini? Silakan tanyakan seputar produk aksesoris (Kalung, Cincin, Gelang, Anting), stok, promo, jam operasional, atau informasi lainnya! 😊','::1','nvidia/nemotron-3.5-bolt-e4b','completed','2026-11-15 01:04:31'),(59,'conv_9f7af01af571343c0357','o7ngc9vtdq2j7q9grh0tt1vtm9',56,'saya ingin membeli aksesoris untuk hadiah ulang tahun','Halo abii! Selamat datang di NNACO SA. 😊\n\nKami punya banyak aksesoris *handmade* cantik yang cocok untuk hadiah ulang tahun, seperti:\n*   **Gelang Mutiara Soft** (Kategori: Gelang) - Rp 120.000\n*   **Cincin Bunga Daisy** (Kategori: Cincin) - Rp 100.000\n*   **Kalung Titanium Rose Gold** (Kategori: Kalung) - Rp 150.000\n*   **Cincin Resin Aesthetic** (Kategori: Cincin) - Rp 75.000\n\nApakah ada jenis aksesoris tertentu yang Anda minati (Kalung, Cincin, Gelang, atau Anting)? Atau, jika Anda sudah tahu yang diinginkan, saya bisa bantu cek ketersediaan stoknya.\n\nUntuk mempermudah pembelian, Anda bisa melihat detail produk di website kami. Jika ingin saya bantu cek stok atau memberikan informasi lebih lanjut, beri tahu saya ya! ✨','::1','nvidia/nemotron-3.5-bolt-e4b','completed','2026-11-15 03:14:50'),(60,'conv_9f7af01af571343c0357','o7ngc9vtdq2j7q9grh0tt1vtm9',56,'harga di bawah 200.000','Tersedia beberapa aksesoris di NNACO SA dengan harga di bawah Rp 200.000. Berikut daftarnya:\n\n*   **Cincin Bunga Daisy:** Rp 100.000 (Stok: 10 unit)\n*   **Cincin Resin Aesthetic:** Rp 75.000 (Stok: 29 unit)\n*   **Gelang Mutiara Soft:** Rp 120.000 (Stok: 16 unit)\n\nApakah ada produk spesifik yang ingin Anda ketahui lebih lanjut, atau ingin saya bantu cek ketersediaan stok/memasukkannya ke keranjang? 😊','::1','nvidia/nemotron-3.5-bolt-e4b','completed','2026-11-15 03:15:50');
/*!40000 ALTER TABLE `ai_chat_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ai_provider_configs`
--

DROP TABLE IF EXISTS `ai_provider_configs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ai_provider_configs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `config_name` varchar(100) NOT NULL,
  `provider` varchar(50) NOT NULL,
  `base_url` varchar(255) NOT NULL,
  `model_id` varchar(100) NOT NULL,
  `encrypted_api_key` text DEFAULT NULL,
  `parameters_json` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 0,
  `last_tested_at` datetime DEFAULT NULL,
  `last_test_status` varchar(50) DEFAULT NULL,
  `last_test_latency_ms` int(11) DEFAULT NULL,
  `last_test_message` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_provider` (`provider`),
  KEY `idx_active` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ai_provider_configs`
--

LOCK TABLES `ai_provider_configs` WRITE;
/*!40000 ALTER TABLE `ai_provider_configs` DISABLE KEYS */;
INSERT INTO `ai_provider_configs` VALUES (1,'NVIDIA NIM (Nemotron 3.5 Bolt)','openai_compatible','https://integrate.api.nvidia.com/v1/chat/completions','nvidia/nemotron-3.5-bolt-e4b','NULL','{\"timeout\":15,\"max_tokens\":800,\"temperature\":0.5}',1,'2026-11-10 12:07:10','CONNECTED',486,'Koneksi berhasil! Provider merespons dalam 486 ms dengan model nvidia/nemotron-3.5-bolt-e4b.','2026-10-21 12:43:25','2026-11-14 14:26:26'),(2,'OpenAI Official (GPT-4o Mini)','openai','https://api.openai.com/v1/chat/completions','gpt-4o-mini',NULL,'{\"timeout\":30,\"max_tokens\":500,\"temperature\":0.7}',0,NULL,'UNTESTED',NULL,NULL,'2026-10-21 12:43:25','2026-10-21 12:43:25');
/*!40000 ALTER TABLE `ai_provider_configs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ai_recommendations`
--

DROP TABLE IF EXISTS `ai_recommendations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ai_recommendations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `recommended_product_id` int(11) NOT NULL,
  `reason` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `recommended_product_id` (`recommended_product_id`),
  CONSTRAINT `ai_recommendations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ai_recommendations_ibfk_2` FOREIGN KEY (`recommended_product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ai_recommendations`
--

LOCK TABLES `ai_recommendations` WRITE;
/*!40000 ALTER TABLE `ai_recommendations` DISABLE KEYS */;
/*!40000 ALTER TABLE `ai_recommendations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `auth_provider_configs`
--

DROP TABLE IF EXISTS `auth_provider_configs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `auth_provider_configs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `provider_type` varchar(50) NOT NULL,
  `encrypted_credentials` text DEFAULT NULL,
  `settings_json` text DEFAULT NULL,
  `is_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `last_tested_at` datetime DEFAULT NULL,
  `last_test_status` varchar(50) DEFAULT NULL,
  `last_test_latency_ms` int(11) DEFAULT NULL,
  `last_test_message` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `provider_type` (`provider_type`),
  KEY `idx_provider_type` (`provider_type`),
  KEY `idx_enabled` (`is_enabled`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `auth_provider_configs`
--

LOCK TABLES `auth_provider_configs` WRITE;
/*!40000 ALTER TABLE `auth_provider_configs` DISABLE KEYS */;
INSERT INTO `auth_provider_configs` VALUES (1,'whatsapp_fonnte','NULL','{\"api_url\":\"https:\\/\\/api.fonnte.com\\/send\",\"country_code\":\"62\"}',1,NULL,'UNTESTED',NULL,NULL,'2026-10-21 12:43:25','2026-11-14 14:26:26'),(2,'email_smtp',NULL,'{\"host\":\"127.0.0.1\",\"port\":1025,\"username\":\"\",\"encryption\":\"none\",\"from_email\":\"test@nnacosa.com\",\"from_name\":\"NNACOSA TEST\",\"timeout\":10}',0,NULL,'UNTESTED',NULL,NULL,'2026-10-21 12:43:25','2026-11-14 14:26:26');
/*!40000 ALTER TABLE `auth_provider_configs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cart_items`
--

DROP TABLE IF EXISTS `cart_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cart_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cart_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `cart_id` (`cart_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `cart_items_ibfk_1` FOREIGN KEY (`cart_id`) REFERENCES `carts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cart_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cart_items`
--

LOCK TABLES `cart_items` WRITE;
/*!40000 ALTER TABLE `cart_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `cart_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `carts`
--

DROP TABLE IF EXISTS `carts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `carts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `carts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `carts`
--

LOCK TABLES `carts` WRITE;
/*!40000 ALTER TABLE `carts` DISABLE KEYS */;
INSERT INTO `carts` VALUES (1,2,'2026-09-15 08:30:10'),(2,4,'2026-09-15 09:31:46'),(3,5,'2026-10-16 07:37:25'),(4,12,'2026-10-16 14:20:16'),(5,17,'2026-10-17 00:54:21'),(6,1,'2026-10-17 01:20:48'),(7,51,'2026-11-10 04:44:38'),(8,55,'2026-11-14 14:40:32'),(9,56,'2026-11-15 03:12:53');
/*!40000 ALTER TABLE `carts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (1,'Kalung','Koleksi kalung eksklusif handmade'),(2,'Cincin','Cincin aesthetic dengan hiasan unik'),(3,'Gelang','Gelang mutiara dan kristal'),(4,'Anting','Anting elegan untuk segala acara');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `coupons`
--

DROP TABLE IF EXISTS `coupons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `coupons` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL,
  `discount_type` enum('percentage','fixed') NOT NULL DEFAULT 'percentage',
  `discount_value` decimal(10,2) NOT NULL,
  `min_purchase` decimal(10,2) DEFAULT 0.00,
  `valid_until` date NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `coupons`
--

LOCK TABLES `coupons` WRITE;
/*!40000 ALTER TABLE `coupons` DISABLE KEYS */;
INSERT INTO `coupons` VALUES (1,'WELCOME50','percentage',50.00,50000.00,'2027-01-01',1);
/*!40000 ALTER TABLE `coupons` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `email_otps`
--

DROP TABLE IF EXISTS `email_otps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `email_otps` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(150) NOT NULL,
  `otp_hash` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `attempts` int(11) NOT NULL DEFAULT 0,
  `is_used` tinyint(1) NOT NULL DEFAULT 0,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_email` (`email`),
  KEY `idx_status` (`is_used`,`expires_at`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `email_otps`
--

LOCK TABLES `email_otps` WRITE;
/*!40000 ALTER TABLE `email_otps` DISABLE KEYS */;
INSERT INTO `email_otps` VALUES (1,'admin@example.com','$2y$10$FtsbNEFXPmgYOK.1zDQuQ.8PSaIB7.WPmVUa0vA6w6t5uXE2ehtmW','2026-10-21 20:00:49',1,0,'127.0.0.1','2026-10-21 12:55:49','2026-10-21 12:55:57');
/*!40000 ALTER TABLE `email_otps` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_items`
--

LOCK TABLES `order_items` WRITE;
/*!40000 ALTER TABLE `order_items` DISABLE KEYS */;
INSERT INTO `order_items` VALUES (1,1,4,1,100000.00),(2,2,3,1,120000.00),(3,3,4,1,100000.00),(4,4,4,1,100000.00),(7,7,3,1,120000.00),(11,11,2,1,75000.00),(14,14,1,1,150000.00),(16,16,4,1,100000.00),(24,24,4,1,100000.00),(27,27,3,1,120000.00),(28,28,3,1,120000.00);
/*!40000 ALTER TABLE `order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_notification_logs`
--

DROP TABLE IF EXISTS `order_notification_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_notification_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `old_status` varchar(50) NOT NULL,
  `new_status` varchar(50) NOT NULL,
  `recipient_email` varchar(150) NOT NULL,
  `recipient_name` varchar(100) DEFAULT NULL,
  `event_key` varchar(100) NOT NULL,
  `notification_status` enum('PENDING','SENDING','SENT','FAILED','REVIEW_REQUIRED') NOT NULL DEFAULT 'PENDING',
  `whatsapp_status` enum('NOT_SENT','SENT','FAILED') NOT NULL DEFAULT 'NOT_SENT',
  `whatsapp_phone` varchar(30) DEFAULT NULL,
  `whatsapp_sent_at` datetime DEFAULT NULL,
  `whatsapp_error` text DEFAULT NULL,
  `attempt_count` int(11) NOT NULL DEFAULT 0,
  `max_attempts` int(11) NOT NULL DEFAULT 5,
  `last_error` text DEFAULT NULL,
  `sent_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_event_key` (`event_key`),
  KEY `idx_notif_status_created` (`notification_status`,`created_at`),
  KEY `idx_order_id` (`order_id`),
  CONSTRAINT `fk_notif_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=120 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_notification_logs`
--

LOCK TABLES `order_notification_logs` WRITE;
/*!40000 ALTER TABLE `order_notification_logs` DISABLE KEYS */;
INSERT INTO `order_notification_logs` VALUES (23,7,'processing','shipped','sitinurfadillahhh07@gmail.com','Fadillah','ord_7_processing_to_shipped_1792160569_c8d1e52f901e','SENT','NOT_SENT',NULL,NULL,NULL,3,5,NULL,'2026-10-16 21:33:43','2026-10-16 14:22:49','2026-10-16 14:33:43'),(24,2,'shipped','completed','customer@gmail.com','Customer Cantik','ord_2_shipped_to_completed_1792160623_ced559e150c4','SENT','NOT_SENT',NULL,NULL,NULL,1,5,NULL,'2026-10-16 21:33:43','2026-10-16 14:23:43','2026-10-16 14:33:43'),(25,7,'shipped','completed','sitinurfadillahhh07@gmail.com','Fadillah','ord_7_shipped_to_completed_1792160693_e8cf4560ef5d','SENT','NOT_SENT',NULL,NULL,NULL,1,5,NULL,'2026-10-16 21:33:44','2026-10-16 14:24:53','2026-10-16 14:33:44'),(37,7,'completed','shipped','sitinurfadillahhh07@gmail.com','Fadillah','ord_7_completed_to_shipped_1792160915_64efe99bb38e','SENT','SENT','6281572199062','2026-10-16 21:52:32',NULL,1,5,NULL,'2026-10-16 21:33:44','2026-10-16 14:28:35','2026-10-16 14:52:32'),(49,7,'shipped','completed','sitinurfadillahhh07@gmail.com','Fadillah','ord_7_shipped_to_completed_1792161370_c371826ccac5','SENT','NOT_SENT',NULL,NULL,NULL,0,5,NULL,'2026-10-16 21:36:10','2026-10-16 14:36:10','2026-10-16 14:36:10'),(50,7,'completed','shipped','sitinurfadillahhh07@gmail.com','Fadillah','ord_7_completed_to_shipped_1792161434_a77626bf0a09','SENT','SENT','6281572199062','2026-10-16 21:53:54',NULL,0,5,NULL,'2026-10-16 21:37:14','2026-10-16 14:37:14','2026-10-16 14:53:54'),(62,3,'processing','shipped','customer@gmail.com','Customer Cantik','ord_3_processing_to_shipped_1792162616_f666e944ae9a','SENT','FAILED','',NULL,'Nomor WhatsApp customer tidak valid atau belum diisi.',0,5,NULL,'2026-10-16 21:56:56','2026-10-16 14:56:56','2026-10-16 14:56:56'),(63,11,'processing','shipped','ttaalnabila@gmail.com','Talita','ord_11_processing_to_shipped_1792198535_ae9af5d814f1','FAILED','FAILED','',NULL,'Nomor WhatsApp customer tidak valid atau belum diisi.',2,5,'SMTP Error: Could not connect to SMTP host. Failed to connect to server',NULL,'2026-10-17 00:55:35','2026-10-17 00:56:06'),(75,14,'processing','shipped','ttaalnabila@gmail.com','Talita','ord_14_processing_to_shipped_1792199212_e26e5790c455','SENT','SENT','6281953319613','2026-10-21 20:01:48',NULL,0,5,NULL,'2026-10-17 08:06:52','2026-10-17 01:06:52','2026-10-21 13:01:48'),(109,16,'processing','shipped','sitinurfadillahhh07@gmail.com','Fadillah','ord_16_processing_to_shipped_1792738449_43caeb2e23bb','PENDING','SENT','6281572199062','2026-10-23 13:54:10',NULL,0,5,NULL,NULL,'2026-10-23 06:54:09','2026-10-23 06:54:10'),(110,4,'processing','shipped','yudhistira@gmail.com','Yudhistira','ord_4_processing_to_shipped_1792738528_eceeb96ef6e6','PENDING','SENT','6285864394008','2026-10-23 13:55:29',NULL,0,5,NULL,NULL,'2026-10-23 06:55:28','2026-10-23 06:55:29'),(111,4,'shipped','completed','yudhistira@gmail.com','Yudhistira','ord_4_shipped_to_completed_1792738555_e534889a5858','PENDING','SENT','6281953319613','2026-10-25 18:04:41',NULL,0,5,NULL,NULL,'2026-10-23 06:55:55','2026-10-25 11:04:41'),(112,16,'shipped','completed','sitinurfadillahhh07@gmail.com','Fadillah','ord_16_shipped_to_completed_1792926789_c32001286e29','PENDING','SENT','6281953319613','2026-10-25 18:33:05',NULL,0,5,NULL,NULL,'2026-10-25 11:13:09','2026-10-25 11:33:05'),(113,24,'processing','cancelled','fadillahhh@gmail.com','Fadillahhh','ord_24_processing_to_cancelled_1794666838_03ec653835eb','PENDING','SENT','6282120821083','2026-11-14 21:33:59',NULL,0,5,NULL,NULL,'2026-11-14 14:33:58','2026-11-14 14:33:59'),(114,16,'completed','cancelled','sitinurfadillahhh07@gmail.com','Fadillah','ord_16_completed_to_cancelled_1794666846_66d1a31417ba','PENDING','SENT','6281572199062','2026-11-14 21:34:06',NULL,0,5,NULL,NULL,'2026-11-14 14:34:06','2026-11-14 14:34:06'),(115,24,'cancelled','completed','fadillahhh@gmail.com','Fadillahhh','ord_24_cancelled_to_completed_1794666905_a3a9e27b83c0','PENDING','SENT','6282120821083','2026-11-14 21:35:05',NULL,0,5,NULL,NULL,'2026-11-14 14:35:05','2026-11-14 14:35:05'),(116,16,'cancelled','completed','sitinurfadillahhh07@gmail.com','Fadillah','ord_16_cancelled_to_completed_1794666915_aa35918f6f6e','PENDING','SENT','6281572199062','2026-11-14 21:35:15',NULL,0,5,NULL,NULL,'2026-11-14 14:35:15','2026-11-14 14:35:15'),(117,7,'shipped','completed','sitinurfadillahhh07@gmail.com','Fadillah','ord_7_shipped_to_completed_1794666926_06116d05db08','PENDING','SENT','6281572199062','2026-11-14 21:35:26',NULL,0,5,NULL,NULL,'2026-11-14 14:35:26','2026-11-14 14:35:26'),(118,27,'processing','shipped','adill@gmail.com','adill','ord_27_processing_to_shipped_1794667362_0355ecd53b90','PENDING','SENT','6282120821083','2026-11-14 21:42:42',NULL,0,5,NULL,NULL,'2026-11-14 14:42:42','2026-11-14 14:42:42'),(119,27,'shipped','completed','adill@gmail.com','adill','ord_27_shipped_to_completed_1794667608_4491af95e084','PENDING','SENT','6282120821083','2026-11-14 21:46:48',NULL,0,5,NULL,NULL,'2026-11-14 14:46:48','2026-11-14 14:46:48');
/*!40000 ALTER TABLE `order_notification_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `phone` varchar(25) DEFAULT NULL,
  `coupon_id` int(11) DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','processing','shipped','completed','cancelled') NOT NULL DEFAULT 'pending',
  `shipping_address` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `coupon_id` (`coupon_id`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
INSERT INTO `orders` VALUES (1,4,NULL,NULL,100000.00,'completed','','2026-09-15 09:31:54'),(2,2,NULL,NULL,120000.00,'completed','xjxbcjdccbjdbdscsdjcbddbjd','2026-09-15 09:36:55'),(3,2,NULL,NULL,100000.00,'shipped','cirebon','2026-09-22 13:20:47'),(4,5,NULL,NULL,100000.00,'completed','Cirebon','2026-10-16 07:37:38'),(7,12,NULL,NULL,120000.00,'completed','Kuningan','2026-10-16 14:20:30'),(11,17,NULL,NULL,75000.00,'shipped','cangkring','2026-10-17 00:54:39'),(14,17,'6281953319613',NULL,150000.00,'shipped','Cungkring','2026-10-17 01:06:25'),(16,12,'6281572199062',NULL,100000.00,'completed','beber','2026-10-17 01:45:19'),(24,51,'6282120821083',NULL,100000.00,'completed','Beber, Cirebon','2026-11-10 04:44:59'),(27,55,'6282120821083',NULL,120000.00,'completed','Beber, Cirebon','2026-11-14 14:40:51'),(28,56,'6282120821083',NULL,120000.00,'processing','Beber','2026-11-15 03:13:17');
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_proof` varchar(255) DEFAULT NULL,
  `status` enum('pending','verified','failed') NOT NULL DEFAULT 'pending',
  `payment_date` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payments`
--

LOCK TABLES `payments` WRITE;
/*!40000 ALTER TABLE `payments` DISABLE KEYS */;
INSERT INTO `payments` VALUES (1,1,'Mandiri',0.00,'1789464746_2f0b0d6a27112f25095e48a4af413c93.jpg','pending',NULL),(2,3,'Cash',0.00,'cash_transaction','pending',NULL),(3,4,'Cash',0.00,'cash_transaction','pending',NULL),(4,7,'Cash',0.00,'cash_transaction','pending',NULL),(5,11,'Cash',0.00,'cash_transaction','pending',NULL),(6,14,'Cash',0.00,'cash_transaction','pending',NULL),(7,16,'Cash',0.00,'cash_transaction','pending',NULL),(8,24,'Cash',0.00,'cash_transaction','pending',NULL),(9,27,'Cash',0.00,'cash_transaction','pending',NULL),(10,28,'Cash',0.00,'cash_transaction','pending',NULL);
/*!40000 ALTER TABLE `payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_images`
--

DROP TABLE IF EXISTS `product_images`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `image_url` varchar(255) NOT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_images`
--

LOCK TABLES `product_images` WRITE;
/*!40000 ALTER TABLE `product_images` DISABLE KEYS */;
INSERT INTO `product_images` VALUES (1,1,'1789467600_cc86db76f95152a3f042aefd970f7a6c.jpg',1),(2,2,'1789467586_f0683573070163b783185f6eccfdb638.jpg',1),(3,3,'1789467567_de5ab4f2dbe171bfe348fd13b94f84ae.jpg',1),(4,4,'1789462772_2f0b0d6a27112f25095e48a4af413c93.jpg',1);
/*!40000 ALTER TABLE `product_images` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (1,1,'Kalung Titanium Rose Gold','Kalung anti karat dengan kilau rose gold menawan.',150000.00,48,'2026-09-15 08:30:10'),(2,2,'Cincin Resin Aesthetic','Cincin buatan tangan dihiasi kelopak bunga kering asli.',75000.00,29,'2026-09-15 08:30:10'),(3,3,'Gelang Mutiara Soft','Gelang mutiara lembut dengan tali peregangan super kuat.',120000.00,16,'2026-09-15 08:30:10'),(4,2,'Cincin Bunga Daisy','Cincin ',100000.00,10,'2026-09-15 08:59:32');
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reviews`
--

DROP TABLE IF EXISTS `reviews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reviews` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `product_id` (`product_id`),
  KEY `order_id` (`order_id`),
  CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reviews_ibfk_3` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reviews`
--

LOCK TABLES `reviews` WRITE;
/*!40000 ALTER TABLE `reviews` DISABLE KEYS */;
INSERT INTO `reviews` VALUES (4,17,1,14,5,'Kalungnya sangat cantik dan kilaunya mewah! Rantai titaniumnya halus di kulit dan tidak menyebabkan alergi. Suka banget!','2026-10-25 11:41:21'),(5,17,2,11,5,'Desain cincin resin unik dengan bunga kering asli di dalamnya. Sangat estetik dan nyaman dipakai sehari-hari.','2026-10-25 11:41:21'),(6,2,3,2,5,'Gelangnya super cantik dan mutiaranya halus! Packaging rapi seperti kado mewah dengan pita satin eksklusif.','2026-10-25 11:41:21'),(7,12,3,7,5,'Mutiaranya berkilau lembut dan talinya elastis kuat, pas sekali di pergelangan tangan. Pengiriman kilat!','2026-10-25 11:41:21'),(8,12,4,16,5,'Cincin bunga daisynya manis sekali, detail ukirannya rapi dan sangat anggun saat dipakai di jari.','2026-10-25 11:41:21'),(9,5,4,4,5,'Beli untuk hadiah pasangan dan dia suka banget! Pelayanan ramah dan kotaknya sangat mewah.','2026-10-25 11:41:21');
/*!40000 ALTER TABLE `reviews` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'customer'),(2,'admin'),(3,'operator'),(4,'owner');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `shipments`
--

DROP TABLE IF EXISTS `shipments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `shipments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `tracking_number` varchar(100) DEFAULT NULL,
  `courier` varchar(50) DEFAULT NULL,
  `status` enum('packing','on_delivery','delivered') NOT NULL DEFAULT 'packing',
  `shipped_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  CONSTRAINT `shipments_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `shipments`
--

LOCK TABLES `shipments` WRITE;
/*!40000 ALTER TABLE `shipments` DISABLE KEYS */;
/*!40000 ALTER TABLE `shipments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `system_settings`
--

DROP TABLE IF EXISTS `system_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_group` varchar(50) NOT NULL DEFAULT 'general',
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `value_type` varchar(20) NOT NULL DEFAULT 'string',
  `is_secret` tinyint(1) NOT NULL DEFAULT 0,
  `updated_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`),
  KEY `idx_group` (`setting_group`),
  KEY `idx_key` (`setting_key`)
) ENGINE=InnoDB AUTO_INCREMENT=88 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `system_settings`
--

LOCK TABLES `system_settings` WRITE;
/*!40000 ALTER TABLE `system_settings` DISABLE KEYS */;
INSERT INTO `system_settings` VALUES (1,'otp','otp_length','6','integer',0,NULL,'2026-10-21 12:43:25','2026-11-14 14:26:26'),(2,'otp','otp_expiry_minutes','5','integer',0,NULL,'2026-10-21 12:43:25','2026-11-14 14:26:26'),(3,'otp','otp_max_attempts','5','integer',0,NULL,'2026-10-21 12:43:25','2026-11-14 14:26:26'),(4,'otp','otp_cooldown_seconds','60','integer',0,NULL,'2026-10-21 12:43:25','2026-11-14 14:26:26'),(5,'otp','otp_max_requests_phone_hour','5','integer',0,NULL,'2026-10-21 12:43:25','2026-10-21 12:43:25'),(6,'otp','otp_max_requests_ip_hour','15','integer',0,NULL,'2026-10-21 12:43:25','2026-10-21 12:43:25'),(7,'otp','otp_allowed_channels','whatsapp','string',0,NULL,'2026-10-21 12:43:25','2026-10-21 12:43:25'),(8,'otp','otp_auto_register','1','boolean',0,NULL,'2026-10-21 12:43:25','2026-10-21 12:43:25'),(9,'otp','otp_login_required','0','boolean',0,NULL,'2026-10-21 12:43:25','2026-10-21 12:43:25'),(10,'otp','otp_wa_template','Halo! Kode verifikasi WhatsApp Anda adalah: {otp}. Kode berlaku selama {expiry} menit. Jangan bagikan kode ini kepada siapa pun.','string',0,NULL,'2026-10-21 12:43:25','2026-10-21 13:03:45'),(11,'otp','otp_email_template','<p>Halo <strong>{name}</strong>,</p><p>Kode verifikasi OTP Anda untuk {app_name} adalah: <strong style=\"font-size:20px;letter-spacing:3px;\">{otp}</strong>.</p><p>Kode ini berlaku selama {expiry} menit. Jangan berikan kode ini kepada pihak mana pun.</p>','string',0,NULL,'2026-10-21 12:43:25','2026-10-21 12:43:25');
/*!40000 ALTER TABLE `system_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `is_phone_verified` tinyint(1) DEFAULT 0,
  `password` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `phone` (`phone`),
  KEY `role_id` (`role_id`),
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=57 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,2,'Admin Utama','admin@nnacosa.com',NULL,0,'$2y$10$euH7fVNXbS4T.BbFqhSdyeaHASqlBNdvg92zvxzEl8fh88JqZ7P86','2026-09-15 08:30:10'),(2,1,'Customer Cantik','customer@gmail.com',NULL,0,'$2y$10$euH7fVNXbS4T.BbFqhSdyeaHASqlBNdvg92zvxzEl8fh88JqZ7P86','2026-09-15 08:30:10'),(3,3,'Operator Toko','operator@nnacosa.com',NULL,0,'$2y$10$euH7fVNXbS4T.BbFqhSdyeaHASqlBNdvg92zvxzEl8fh88JqZ7P86','2026-09-15 08:39:00'),(4,4,'Owner NNACO','owner@nnacosa.com',NULL,0,'$2y$10$euH7fVNXbS4T.BbFqhSdyeaHASqlBNdvg92zvxzEl8fh88JqZ7P86','2026-09-15 08:39:00'),(5,1,'Yudhistira','yudhistira@gmail.com','6285864394008',0,'$2y$10$4ezXyV2Hu5l40KIlnFEgD.a8L9NpTlVqpTq5PHntyKJBxli8CHE2.','2026-10-16 07:36:47'),(12,1,'Fadillah','sitinurfadillahhh07@gmail.com','6281572199062',1,'$2y$10$5ly5V/Q4v8BZYj0.eFfsDuEtIaRbjfO2OQzaI3/kaPPRJ2qDzt2mi','2026-10-16 14:19:26'),(17,1,'Talita','ttaalnabila@gmail.com','6281953319613',1,'$2y$10$I0lZ452eqtQfLTGm.WHaNOc3TR6tU527TE3t8w0N7/iXswnRo891G','2026-10-17 00:53:59'),(38,1,'Ubai','ubai@gmail.com','6283871775404',1,'$2y$10$pt7HEHE.2LfMS2wlz5HsP.hWmPQH2ZYDp8nc0xs1RSNxRMmEivX9.','2026-10-23 06:32:22'),(51,1,'Fadillahhh','fadillahhh@gmail.com','8123456789',1,'$2y$10$pdX9UluX.TqOAOgtEcbwp.8UMs59YrJO.YSucNswFrBLZp9ND3502','2026-11-10 04:43:45'),(55,1,'adill','adill@gmail.com','628262848367',1,'$2y$10$xtZLerKFmxC092n8bOcwJ.Cc52cscn/t4QcXieQISwGDnyHhSXIlC','2026-11-14 14:39:35'),(56,1,'abii','abii@gmail.com','6282120821083',1,'$2y$10$KiIVeEjCt846dw3M5J1TcO208RQ0JKEy3G0E9NuP0F2TdGKC5vhAW','2026-11-15 03:12:38');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `whatsapp_otps`
--

DROP TABLE IF EXISTS `whatsapp_otps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `whatsapp_otps` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `phone` varchar(20) NOT NULL,
  `otp_hash` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `attempts` int(11) NOT NULL DEFAULT 0,
  `is_used` tinyint(1) NOT NULL DEFAULT 0,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_phone_created` (`phone`,`created_at`),
  KEY `idx_phone_active` (`phone`,`is_used`,`expires_at`),
  KEY `idx_ip_created` (`ip_address`,`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=114 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `whatsapp_otps`
--

LOCK TABLES `whatsapp_otps` WRITE;
/*!40000 ALTER TABLE `whatsapp_otps` DISABLE KEYS */;
INSERT INTO `whatsapp_otps` VALUES (23,'6281572199062','$2y$10$/hs/8cjehFQZONMLpDY70OIxeax8UtuGJXFqqIxD1zgoRAgwu5/iq','2026-10-16 20:51:32',0,0,'::1','2026-10-16 13:46:32','2026-10-16 13:46:32'),(24,'6281572199062','$2y$10$/VD.7/mh3sYNeps7OlfFze.xfwRxPrjgLmfSvr0eiM/hY2FsB84PG','2026-10-16 20:56:24',0,0,'::1','2026-10-16 13:51:24','2026-10-16 13:51:24'),(25,'6282120821083','$2y$10$D9BldZoVJNq6NwkorT3xTek20DRzFCWu4.vDdUk6bj240.3UlSp2K','2026-10-16 20:57:07',0,0,'::1','2026-10-16 13:52:07','2026-10-16 13:52:07'),(90,'6283871715404','$2y$10$84.QsKrKQIR0SCc.mk5A6OpfFLJCAf.K4bJDL7oFS/jLdrmTB9dyO','2026-10-23 13:01:19',0,0,'::1','2026-10-23 05:56:19','2026-10-23 05:56:19'),(91,'6283871715404','$2y$10$HIyLyGBr8EWWisXe4zxUxub06EscFG5wb/wiNQ5x3mlG/uF9SqQWS','2026-10-23 13:02:25',0,0,'::1','2026-10-23 05:57:25','2026-10-23 05:57:25'),(92,'6281572199062','$2y$10$6kEz595M1aRLIdM1UoRTgOsMSgp7yPJTcTBNoHKwlClpZzQNsApqK','2026-10-23 13:20:04',0,0,'127.0.0.1','2026-10-23 06:15:04','2026-10-23 06:15:04'),(95,'6283871775404','$2y$10$dfggFupsOoa3Fml22mbX7uAk4gSzN.yBAxGzZPRvjKSq/DGH0Z5Lu','2026-10-23 13:36:28',0,1,'::1','2026-10-23 06:31:28','2026-10-23 06:32:22'),(103,'6282120821083','$2y$10$MEkR8GAZ/oiRB4oOJFUPGeUAcAG1Ixx7EIg2bffTlk1YqZUHg.8aq','2026-11-10 11:35:51',0,0,'::1','2026-11-10 04:30:51','2026-11-10 04:30:51'),(104,'6282120821083','$2y$10$y3Hwi3ppqWG1BHoYl7q/de7VUAEyap2/yPre5033sN0iitMnpgfIq','2026-11-10 11:48:10',0,1,'::1','2026-11-10 04:43:10','2026-11-10 04:43:45'),(107,'6281299998888','$2y$10$oa4C.4lh6rioDptr4Y.GROj3FBRRozaLy/9MeS5OJmwlR2vuyPfoy','2026-11-14 21:31:26',1,1,'127.0.0.1','2026-11-14 14:26:26','2026-11-14 14:26:26'),(108,'6282120821083','$2y$10$w3wLBQOp1HKiMH6A2g/WPOQgOjiXFJwF3blC.eqWEOlX4meRnJHd2','2026-11-14 21:43:36',0,1,'::1','2026-11-14 14:38:36','2026-11-14 14:39:35'),(109,'6282120821082','$2y$10$QNo95vMmnGumtJACuzSAsO1ClSCcDy9U/3TbD8osZUd7z2X1jrhL2','2026-11-14 21:55:13',0,0,'::1','2026-11-14 14:50:13','2026-11-14 14:50:13'),(112,'6282120821083','$2y$10$6z3VJ/c0bt9dnr4m0OOkoeoSme4NGAxB0IXSLhRS14TzX3zNsT2/i','2026-11-15 10:13:22',0,0,'::1','2026-11-15 03:08:22','2026-11-15 03:08:22'),(113,'6282120821083','$2y$10$dn1p5QcV7NGvl.uQlaLXi.af.WCj3MPNPZzsTTZWP/vKclyrmDROO','2026-11-15 10:17:01',0,1,'::1','2026-11-15 03:12:01','2026-11-15 03:12:38');
/*!40000 ALTER TABLE `whatsapp_otps` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wishlist`
--

DROP TABLE IF EXISTS `wishlist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wishlist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `wishlist_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `wishlist_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wishlist`
--

LOCK TABLES `wishlist` WRITE;
/*!40000 ALTER TABLE `wishlist` DISABLE KEYS */;
INSERT INTO `wishlist` VALUES (1,2,1,'2026-09-15 08:30:10'),(2,51,4,'2026-11-10 04:44:35');
/*!40000 ALTER TABLE `wishlist` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-11-16 10:41:14

