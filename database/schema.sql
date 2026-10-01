-- ============================================================
-- Soft4dz Platform — Database Schema
-- Engine: InnoDB | Charset: utf8mb4_unicode_ci
-- ============================================================

CREATE DATABASE IF NOT EXISTS `soft4dz`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `soft4dz`;

-- ============================================================
-- USERS & AUTH
-- ============================================================

CREATE TABLE `users` (
    `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name`          VARCHAR(150) NOT NULL,
    `email`         VARCHAR(191) NOT NULL UNIQUE,
    `phone`         VARCHAR(30) DEFAULT NULL,
    `password_hash` VARCHAR(255) NULL,
    `role`          ENUM('customer','vendor','admin') NOT NULL DEFAULT 'customer',
    `status`        ENUM('active','inactive','banned') NOT NULL DEFAULT 'active',
    `avatar`        VARCHAR(255) DEFAULT NULL,
    `email_verified_at` DATETIME DEFAULT NULL,
    `remember_token`    VARCHAR(100) DEFAULT NULL,
    `oauth_provider`    VARCHAR(20) DEFAULT NULL,
    `oauth_id`          VARCHAR(191) DEFAULT NULL,
    `last_login`        DATETIME DEFAULT NULL,
    `created_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_email` (`email`),
    INDEX `idx_role_status` (`role`, `status`),
    UNIQUE INDEX `idx_oauth` (`oauth_provider`, `oauth_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `password_resets` (
    `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `email`      VARCHAR(191) NOT NULL,
    `token`      VARCHAR(100) NOT NULL,
    `expires_at` DATETIME NOT NULL,
    `used`       TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_email_token` (`email`, `token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- PRODUCTS & CATALOG
-- ============================================================

CREATE TABLE `categories` (
    `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name`        VARCHAR(100) NOT NULL,
    `slug`        VARCHAR(120) NOT NULL UNIQUE,
    `description` TEXT DEFAULT NULL,
    `icon`        VARCHAR(100) DEFAULT NULL,
    `image`       VARCHAR(255) DEFAULT NULL,
    `parent_id`   INT UNSIGNED DEFAULT NULL,
    `sort_order`  SMALLINT NOT NULL DEFAULT 0,
    `is_active`   TINYINT(1) NOT NULL DEFAULT 1,
    `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`parent_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `products` (
    `id`              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `category_id`     INT UNSIGNED DEFAULT NULL,
    `vendor_id`       INT UNSIGNED DEFAULT NULL,
    `name`            VARCHAR(255) NOT NULL,
    `slug`            VARCHAR(280) NOT NULL UNIQUE,
    `short_desc`      VARCHAR(500) DEFAULT NULL,
    `description`     LONGTEXT DEFAULT NULL,
    `type`            ENUM('subscription','saas','gift_card','software','account','service','top_up') NOT NULL DEFAULT 'software',
    `price`           DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `sale_price`      DECIMAL(10,2) DEFAULT NULL,
    `discount_percent` DECIMAL(5,2) DEFAULT NULL COMMENT 'Promo % — sale_price dérivé du price',
    `currency`        VARCHAR(10) NOT NULL DEFAULT 'DZD',
    `image`           VARCHAR(255) DEFAULT NULL,
    `gallery`         JSON DEFAULT NULL,
    `status`          ENUM('draft','active','inactive','out_of_stock') NOT NULL DEFAULT 'draft',
    `featured`        TINYINT(1) NOT NULL DEFAULT 0,
    `stock`           INT DEFAULT NULL COMMENT 'NULL = unlimited',
    `sales_count`     INT UNSIGNED NOT NULL DEFAULT 0,
    `views_count`     INT UNSIGNED NOT NULL DEFAULT 0,
    `rating_avg`      DECIMAL(3,2) NOT NULL DEFAULT 0.00,
    `rating_count`    INT UNSIGNED NOT NULL DEFAULT 0,
    `meta_title`      VARCHAR(255) DEFAULT NULL,
    `meta_desc`       VARCHAR(500) DEFAULT NULL,
    `delivery_type`   ENUM('instant','manual','download','access_link') NOT NULL DEFAULT 'instant',
    `delivery_data`   LONGTEXT DEFAULT NULL COMMENT 'JSON: codes, links, accounts',
    `attributes`      JSON DEFAULT NULL,
    `created_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`vendor_id`)   REFERENCES `users`(`id`) ON DELETE SET NULL,
    INDEX `idx_slug` (`slug`),
    INDEX `idx_status_featured` (`status`, `featured`),
    FULLTEXT INDEX `ft_name_desc` (`name`, `short_desc`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `product_keys` (
    `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `product_id` INT UNSIGNED NOT NULL,
    `key_value`  TEXT NOT NULL,
    `is_used`    TINYINT(1) NOT NULL DEFAULT 0,
    `order_id`   INT UNSIGNED DEFAULT NULL,
    `used_at`    DATETIME DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
    INDEX `idx_product_used` (`product_id`, `is_used`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `product_reviews` (
    `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `product_id` INT UNSIGNED NOT NULL,
    `user_id`    INT UNSIGNED NOT NULL,
    `rating`     TINYINT NOT NULL DEFAULT 5,
    `title`      VARCHAR(200) DEFAULT NULL,
    `body`       TEXT DEFAULT NULL,
    `status`     ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`)    REFERENCES `users`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `uq_user_product` (`user_id`, `product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- SUBSCRIPTIONS & PLANS
-- ============================================================

CREATE TABLE `subscription_plans` (
    `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `product_id`  INT UNSIGNED DEFAULT NULL,
    `name`        VARCHAR(100) NOT NULL,
    `slug`        VARCHAR(120) NOT NULL UNIQUE,
    `description` TEXT DEFAULT NULL,
    `price`       DECIMAL(10,2) NOT NULL,
    `billing_cycle` ENUM('monthly','quarterly','annual','lifetime') NOT NULL DEFAULT 'monthly',
    `features`    JSON DEFAULT NULL,
    `is_popular`  TINYINT(1) NOT NULL DEFAULT 0,
    `is_active`   TINYINT(1) NOT NULL DEFAULT 1,
    `sort_order`  SMALLINT NOT NULL DEFAULT 0,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `subscriptions` (
    `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id`     INT UNSIGNED NOT NULL,
    `plan_id`     INT UNSIGNED NOT NULL,
    `order_id`    INT UNSIGNED DEFAULT NULL,
    `status`      ENUM('active','paused','cancelled','expired') NOT NULL DEFAULT 'active',
    `starts_at`   DATETIME NOT NULL,
    `ends_at`     DATETIME DEFAULT NULL,
    `renewed_at`  DATETIME DEFAULT NULL,
    `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`plan_id`) REFERENCES `subscription_plans`(`id`) ON DELETE RESTRICT,
    INDEX `idx_user_status` (`user_id`, `status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- CART & ORDERS
-- ============================================================

CREATE TABLE `cart_items` (
    `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id`    INT UNSIGNED NOT NULL,
    `product_id` INT UNSIGNED NOT NULL,
    `quantity`   SMALLINT UNSIGNED NOT NULL DEFAULT 1,
    `price`      DECIMAL(10,2) NOT NULL,
    `options`    JSON DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`)    REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `uq_user_product` (`user_id`, `product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `coupons` (
    `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `code`         VARCHAR(50) NOT NULL UNIQUE,
    `type`         ENUM('percent','fixed') NOT NULL DEFAULT 'percent',
    `value`        DECIMAL(10,2) NOT NULL,
    `min_amount`   DECIMAL(10,2) DEFAULT NULL,
    `max_uses`     INT DEFAULT NULL,
    `used_count`   INT NOT NULL DEFAULT 0,
    `expires_at`   DATETIME DEFAULT NULL,
    `is_active`    TINYINT(1) NOT NULL DEFAULT 1,
    `created_at`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `orders` (
    `id`              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `order_number`    VARCHAR(30) NOT NULL UNIQUE,
    `user_id`         INT UNSIGNED NOT NULL,
    `status`          ENUM('pending','paid','processing','completed','cancelled','refunded') NOT NULL DEFAULT 'pending',
    `subtotal`        DECIMAL(10,2) NOT NULL,
    `discount`        DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `tax`             DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `total`           DECIMAL(10,2) NOT NULL,
    `currency`        VARCHAR(10) NOT NULL DEFAULT 'DZD',
    `coupon_id`       INT UNSIGNED DEFAULT NULL,
    `payment_method`  ENUM('cib','edahabia','bank_transfer','free','chargily') NOT NULL DEFAULT 'bank_transfer',
    `payment_status`  ENUM('unpaid','pending','paid','failed','refunded') NOT NULL DEFAULT 'unpaid',
    `payment_ref`     VARCHAR(200) DEFAULT NULL,
    `payment_proof`   VARCHAR(255) DEFAULT NULL,
    `notes`           TEXT DEFAULT NULL,
    `paid_at`         DATETIME DEFAULT NULL,
    `created_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`)   REFERENCES `users`(`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`coupon_id`) REFERENCES `coupons`(`id`) ON DELETE SET NULL,
    INDEX `idx_user_status` (`user_id`, `status`),
    INDEX `idx_payment_status` (`payment_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `order_items` (
    `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `order_id`    INT UNSIGNED NOT NULL,
    `product_id`  INT UNSIGNED DEFAULT NULL,
    `product_name` VARCHAR(255) NOT NULL,
    `quantity`    SMALLINT UNSIGNED NOT NULL DEFAULT 1,
    `price`       DECIMAL(10,2) NOT NULL,
    `subtotal`    DECIMAL(10,2) NOT NULL,
    `delivery_data` LONGTEXT DEFAULT NULL COMMENT 'Delivered keys/links',
    `delivered`   TINYINT(1) NOT NULL DEFAULT 0,
    `delivered_at` DATETIME DEFAULT NULL,
    FOREIGN KEY (`order_id`)   REFERENCES `orders`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- SERVICES & PROJECT REQUESTS
-- ============================================================

CREATE TABLE `service_requests` (
    `id`             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id`        INT UNSIGNED DEFAULT NULL,
    `name`           VARCHAR(150) NOT NULL,
    `email`          VARCHAR(191) NOT NULL,
    `phone`          VARCHAR(30) DEFAULT NULL,
    `company`        VARCHAR(200) DEFAULT NULL,
    `service_type`   ENUM('web_app','mobile_app','saas','ecommerce','api','consulting','other') NOT NULL,
    `title`          VARCHAR(300) NOT NULL,
    `description`    LONGTEXT NOT NULL,
    `budget_range`   VARCHAR(50) DEFAULT NULL,
    `deadline`       DATE DEFAULT NULL,
    `attachments`    JSON DEFAULT NULL,
    `status`         ENUM('new','reviewing','quoted','accepted','in_progress','completed','rejected') NOT NULL DEFAULT 'new',
    `quoted_amount`  DECIMAL(10,2) DEFAULT NULL,
    `admin_notes`    TEXT DEFAULT NULL,
    `created_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- SUPPORT TICKETS
-- ============================================================

CREATE TABLE `tickets` (
    `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `ticket_no`   VARCHAR(20) NOT NULL UNIQUE,
    `user_id`     INT UNSIGNED NOT NULL,
    `order_id`    INT UNSIGNED DEFAULT NULL,
    `subject`     VARCHAR(300) NOT NULL,
    `body`        LONGTEXT NOT NULL,
    `priority`    ENUM('low','normal','high','urgent') NOT NULL DEFAULT 'normal',
    `status`      ENUM('open','replied','resolved','closed') NOT NULL DEFAULT 'open',
    `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`)  REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE SET NULL,
    INDEX `idx_user_status` (`user_id`, `status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `ticket_replies` (
    `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `ticket_id`  INT UNSIGNED NOT NULL,
    `user_id`    INT UNSIGNED NOT NULL,
    `body`       LONGTEXT NOT NULL,
    `is_staff`   TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`ticket_id`) REFERENCES `tickets`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`)   REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- BLOG
-- ============================================================

CREATE TABLE `blog_posts` (
    `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `author_id`   INT UNSIGNED NOT NULL,
    `title`       VARCHAR(300) NOT NULL,
    `slug`        VARCHAR(320) NOT NULL UNIQUE,
    `excerpt`     TEXT DEFAULT NULL,
    `body`        LONGTEXT NOT NULL,
    `image`       VARCHAR(255) DEFAULT NULL,
    `status`      ENUM('draft','published') NOT NULL DEFAULT 'draft',
    `featured`    TINYINT(1) NOT NULL DEFAULT 0,
    `tags`        JSON DEFAULT NULL,
    `views_count` INT UNSIGNED NOT NULL DEFAULT 0,
    `meta_title`  VARCHAR(255) DEFAULT NULL,
    `meta_desc`   VARCHAR(500) DEFAULT NULL,
    `published_at` DATETIME DEFAULT NULL,
    `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`author_id`) REFERENCES `users`(`id`) ON DELETE RESTRICT,
    FULLTEXT INDEX `ft_title_body` (`title`, `excerpt`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- NOTIFICATIONS & SETTINGS
-- ============================================================

CREATE TABLE `notifications` (
    `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id`    INT UNSIGNED NOT NULL,
    `type`       VARCHAR(80) NOT NULL,
    `title`      VARCHAR(255) NOT NULL,
    `body`       TEXT DEFAULT NULL,
    `url`        VARCHAR(500) DEFAULT NULL,
    `is_read`    TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_user_read` (`user_id`, `is_read`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `settings` (
    `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `key`        VARCHAR(100) NOT NULL UNIQUE,
    `value`      LONGTEXT DEFAULT NULL,
    `type`       ENUM('text','json','boolean','number') NOT NULL DEFAULT 'text',
    `group`      VARCHAR(50) NOT NULL DEFAULT 'general',
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- VENDOR (MARKETPLACE — FUTURE READY)
-- ============================================================

CREATE TABLE `vendor_profiles` (
    `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id`     INT UNSIGNED NOT NULL UNIQUE,
    `store_name`  VARCHAR(200) NOT NULL,
    `store_slug`  VARCHAR(220) NOT NULL UNIQUE,
    `description` TEXT DEFAULT NULL,
    `logo`        VARCHAR(255) DEFAULT NULL,
    `banner`      VARCHAR(255) DEFAULT NULL,
    `commission`  DECIMAL(5,2) NOT NULL DEFAULT 15.00 COMMENT 'Platform commission %',
    `status`      ENUM('pending','active','suspended') NOT NULL DEFAULT 'pending',
    `balance`     DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- SEED DATA
-- ============================================================

-- Admin user (mot de passe par défaut : changez-le immédiatement après la première connexion)
INSERT INTO `users` (`name`, `email`, `password_hash`, `role`, `status`, `email_verified_at`) VALUES
('Admin Soft4dz', 'admin@soft4dz.com', '$argon2id$v=19$m=65536,t=4,p=1$ajRoMWwuRi9iVTIxUzliLg$42kbbvsY082gOAaPfkiViBnZQnlHP8d6ROt9GjcK2QQ', 'admin', 'active', NOW());

-- Categories
INSERT INTO `categories` (`name`, `slug`, `description`, `icon`, `sort_order`) VALUES
('Logiciels', 'logiciels', 'Applications et logiciels professionnels', 'bi-box-seam', 1),
('Abonnements', 'abonnements', 'Services par abonnement mensuel ou annuel', 'bi-calendar-check', 2),
('Cartes Cadeaux', 'cartes-cadeaux', 'Gift cards pour les plateformes populaires', 'bi-gift', 3),
('Comptes Premium', 'comptes-premium', 'Comptes prémium partagés ou dédiés', 'bi-person-badge', 4),
('SaaS Tools', 'saas-tools', 'Outils SaaS pour les professionnels', 'bi-tools', 5),
('Sécurité', 'securite', 'VPN, antivirus et outils de sécurité', 'bi-shield-check', 6);

-- Sample products
INSERT INTO `products` (`category_id`, `name`, `slug`, `short_desc`, `description`, `type`, `price`, `sale_price`, `status`, `featured`, `delivery_type`) VALUES
(2, 'Netflix Premium 1 Mois', 'netflix-premium-1-mois', 'Compte Netflix 4K Ultra HD partagé valable 1 mois', '<p>Profitez de Netflix Premium avec streaming 4K Ultra HD.</p>', 'account', 1500.00, 1200.00, 'active', 1, 'instant'),
(2, 'Spotify Premium 3 Mois', 'spotify-premium-3-mois', 'Accès Spotify Premium sans publicité pendant 3 mois', '<p>Musique illimitée sans publicité, hors ligne.</p>', 'subscription', 2500.00, NULL, 'active', 1, 'instant'),
(3, 'Google Play 1000 DZD', 'google-play-1000-dzd', 'Carte cadeau Google Play de 1000 DZD', '<p>Rechargez votre compte Google Play facilement.</p>', 'gift_card', 1200.00, NULL, 'active', 0, 'instant'),
(1, 'Microsoft Office 2024', 'microsoft-office-2024', 'Clé de licence Microsoft Office 2024 Pro Plus', '<p>Suite Office complète avec Word, Excel, PowerPoint.</p>', 'software', 8500.00, 7500.00, 'active', 1, 'instant'),
(5, 'Canva Pro 1 An', 'canva-pro-1-an', 'Accès Canva Pro avec toutes les fonctionnalités premium', '<p>Design professionnel avec Canva Pro.</p>', 'subscription', 5000.00, 4200.00, 'active', 1, 'instant'),
(6, 'NordVPN 1 An', 'nordvpn-1-an', 'VPN premium NordVPN pour 1 an, 6 appareils', '<p>Naviguez en toute sécurité avec NordVPN.</p>', 'subscription', 6000.00, NULL, 'active', 0, 'instant');

-- Settings
INSERT INTO `settings` (`key`, `value`, `type`, `group`) VALUES
('site_name', 'Soft4dz', 'text', 'general'),
('site_tagline', 'Votre marketplace digital en Algérie', 'text', 'general'),
('site_email', 'contact@soft4dz.com', 'text', 'general'),
('site_phone', '+213 XX XX XX XX', 'text', 'general'),
('currency', 'DZD', 'text', 'general'),
('tax_rate', '19', 'number', 'financial'),
('bank_account', '{"bank":"CPA","rib":"00123456789012345678901","name":"Soft4dz SARL"}', 'json', 'payment'),
('ai_enabled', '1', 'boolean', 'features'),
('maintenance_mode', '0', 'boolean', 'general');
