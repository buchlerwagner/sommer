# noinspection SqlNoDataSourceInspectionForFile
-- 2022-02-02

ALTER TABLE `products` ADD COLUMN `prod_vat_local` DOUBLE NULL DEFAULT 5 AFTER `prod_price_discount`;
ALTER TABLE `products` ADD COLUMN `prod_vat_deliver` DOUBLE NULL DEFAULT 18 AFTER `prod_vat_local`;
ALTER TABLE `products` ADD COLUMN `prod_in_store_only` TINYINT(1) NULL DEFAULT 0 AFTER `prod_available`;

ALTER TABLE `product_categories` ADD COLUMN `cat_takeover_days` VARCHAR(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL AFTER `cat_date_end`;
ALTER TABLE `product_categories` ADD COLUMN `cat_only_in_stores` TINYINT(1) NULL DEFAULT 0 AFTER `cat_takover_days`;
ALTER TABLE `product_categories` ADD COLUMN `cat_express` TINYINT(1) NULL DEFAULT 0 AFTER `cat_only_in_stores`;

ALTER TABLE `product_variants` ADD COLUMN `pv_vat_local` DOUBLE NULL DEFAULT 5 AFTER `pv_price_discount`;
ALTER TABLE `product_variants` ADD COLUMN `pv_vat_deliver` DOUBLE NULL DEFAULT 18 AFTER `pv_vat_local`;

ALTER TABLE `hosts` ADD COLUMN `host_store_id` INT(11) NULL DEFAULT 0 AFTER `host_shop_id`;

ALTER TABLE `cart` ADD COLUMN `cart_store_id` VARCHAR(10) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `cart_shop_id`;
ALTER TABLE `cart` ADD COLUMN `cart_created_by` INT(11) NULL DEFAULT 0 AFTER `cart_store_id`;

ALTER TABLE `packagings` ADD COLUMN `pkg_vat` DOUBLE NULL DEFAULT 27 AFTER `pkg_price`;
ALTER TABLE `shipping_modes` ADD COLUMN `sm_vat` DOUBLE NULL DEFAULT 27 AFTER `sm_price`;
ALTER TABLE `payment_modes` ADD COLUMN `pm_vat` DOUBLE NULL DEFAULT 27 AFTER `pm_price`;
ALTER TABLE `shipping_modes` ADD COLUMN `sm_code` VARCHAR(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL AFTER `sm_shop_id`;

ALTER TABLE `cart` ADD COLUMN `cart_order_type` TINYINT(1) NULL DEFAULT 0 AFTER `cart_order_number`;
ALTER TABLE `cart` ADD COLUMN `cart_local_consumption` TINYINT(1) NULL DEFAULT 0 AFTER `cart_custom_interval`;

CREATE  INDEX `store` USING BTREE ON `cart` (`cart_store_id`);
UPDATE cart SET cart_store_id = 'W';
UPDATE cart SET cart_paid = 1, cart_order_status = 'FINISHED' WHERE cart_status = 'ORDERED';

ALTER TABLE `payment_modes` ADD COLUMN `pm_pp_id` INT(11) NULL DEFAULT 0 AFTER `pm_shop_id`;
ALTER TABLE `payment_modes` ADD COLUMN `pm_logo` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL AFTER `pm_email_text`;

CREATE TABLE `payment_providers` (
     `pp_id` int(11) NOT NULL AUTO_INCREMENT,
     `pp_shop_id` int(11) DEFAULT NULL,
     `pp_name` varchar(255) DEFAULT NULL,
     `pp_provider` varchar(100) DEFAULT NULL,
     `pp_shopid` varchar(128) DEFAULT NULL,
     `pp_password` varchar(255) DEFAULT NULL,
     `pp_currency` char(3) DEFAULT 'HUF',
     `pp_test_mode` tinyint(1) DEFAULT 0,
     `pp_url_frontend` varchar(255) DEFAULT NULL,
     `pp_url_return` varchar(255) DEFAULT NULL,
     `pp_url_backend` varchar(255) DEFAULT NULL,
     `pp_private_key` varchar(255) DEFAULT NULL,
     PRIMARY KEY (`pp_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;

CREATE TABLE `payment_transactions` (
    `pt_id` int(11) NOT NULL AUTO_INCREMENT,
    `pt_shop_id` int(11) DEFAULT NULL,
    `pt_cart_id` int(11) DEFAULT NULL,
    `pt_pp_id` int(11) DEFAULT NULL,
    `pt_created` timestamp NULL DEFAULT NULL,
    `pt_status` enum('OK','FAILED','CANCELED','TIMEOUT','ERROR','PENDING','VOIDED') DEFAULT NULL,
    `pt_ip` varchar(30) DEFAULT NULL,
    `pt_language` char(2) DEFAULT 'HU',
    `pt_transactionid` varchar(255) DEFAULT NULL,
    `pt_amount` double DEFAULT 0,
    `pt_refunded` float DEFAULT 0,
    `pt_currency` char(3) DEFAULT 'HUF',
    `pt_auth_code` varchar(50) DEFAULT NULL,
    `pt_status_code` varchar(20) DEFAULT '0',
    `pt_response` mediumtext DEFAULT NULL,
    `pt_message` text DEFAULT NULL,
    `pt_expiry` timestamp NULL DEFAULT NULL,
    `pt_notification_sent` tinyint(1) DEFAULT 0,
    PRIMARY KEY (`pt_id`),
    KEY `transactionID` (`pt_transactionid`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4;


INSERT INTO `access_functions` (`af_page`, `af_key`, `af_name`) VALUES
    ('orders','orders-phone','Telefonos rendelésfelvétel'),
    ('orders','orders-store','Bolti rendelésfelvétel');

INSERT INTO `templates` (`mt_id`, `mt_shop_id`, `mt_type`, `mt_key`, `mt_language`, `mt_subject`, `mt_body`, `mt_keywords`, `mt_description`, `mt_template`) VALUES
    (179,1,'MAIL','payment','hu','Online fizetés értesítő: {{ orderNumber }}','<div>Kedves <b>{{ firstName }}</b>!</div><div><br></div>Köszönjük megrendelését.<b><br></b><div><br></div><div>{{ order }}<br></div><div><br></div><div>Ąz alábbi linkre kattintva megtekintheti rendelését:<br></div><div><a href="{{ link }}" target="_blank">{{ link }}</a><br></div>','domain|firstName|lastName|email|link|order|orderNumber|transactionId|authCode|paymentStatus','Online fizetés értesítő',NULL);








ALTER TABLE `product_categories` ADD COLUMN `cat_stop_sale` TINYINT(1) NULL DEFAULT 0 AFTER `cat_tags`;
ALTER TABLE `product_categories` ADD COLUMN `cat_limit_sale` TINYINT(1) NULL DEFAULT 0 AFTER `cat_stop_sale`;
ALTER TABLE `product_categories` ADD COLUMN `cat_limit_sale_text` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL AFTER `cat_limit_sale`;
ALTER TABLE `product_categories` ADD COLUMN `cat_date_start` DATE NULL DEFAULT NULL AFTER `cat_limit_sale_text`;
ALTER TABLE `product_categories` ADD COLUMN `cat_date_end` DATE NULL DEFAULT NULL AFTER `cat_date_start`;



ALTER TABLE `product_categories` ADD COLUMN `cat_stop_sale` TINYINT(1) NULL DEFAULT 0 AFTER `cat_tags`;


ALTER TABLE `shipping_modes` ADD COLUMN `sm_select_date` TINYINT(1) NULL DEFAULT 0 AFTER `sm_intervals`;
ALTER TABLE `cart` ADD COLUMN `cart_shipping_date` DATE NULL DEFAULT NULL AFTER `cart_remarks`;
ALTER TABLE `products` ADD COLUMN `prod_earliest_takeover` TIME NULL DEFAULT NULL AFTER `prod_max_sale`;


CREATE TABLE `holidays` (
    `h_id` int(11) NOT NULL AUTO_INCREMENT,
    `h_shop_id` int(11) DEFAULT NULL,
    `h_date` date DEFAULT NULL,
    PRIMARY KEY (`h_id`),
    UNIQUE KEY `date` (`h_date`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4;





UPDATE `templates` SET `mt_key` = 'order-new' WHERE `mt_id` = 172;
ALTER TABLE `shipping_modes` ADD COLUMN `sm_type` INT(11) NULL DEFAULT 0 AFTER `sm_shop_id`;
ALTER TABLE `cart` CHANGE COLUMN `cart_order_status` `cart_order_status` ENUM('NEW','PROCESSING','RECEIVABLE','DELIVERING','FINISHED','RATED','CLOSED') CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT 'NEW'  COMMENT '' AFTER `cart_status`;





ALTER TABLE `shipping_modes` ADD COLUMN `sm_day_diff` INT(11) NULL DEFAULT 0 AFTER `sm_order`;
ALTER TABLE `shipping_modes` ADD COLUMN `sm_intervals` TINYINT(1) NULL DEFAULT 0 AFTER `sm_day_diff`;
ALTER TABLE `shipping_modes` ADD COLUMN `sm_custom_interval` TINYINT(1) NULL DEFAULT 0 AFTER `sm_intervals`;
ALTER TABLE `shipping_modes` ADD COLUMN `sm_custom_text` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL AFTER `sm_custom_interval`;
ALTER TABLE `cart` ADD COLUMN `cart_si_id` INT(11) NULL DEFAULT NULL AFTER `cart_sm_id`;
ALTER TABLE `cart` ADD COLUMN `cart_custom_interval` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `cart_remarks`;

CREATE TABLE `shipping_intervals` (
  `si_id` int(11) NOT NULL AUTO_INCREMENT,
  `si_sm_id` int(11) DEFAULT NULL,
  `si_shop_id` int(11) DEFAULT NULL,
  `si_time_start` time DEFAULT NULL,
  `si_time_end` time DEFAULT NULL,
  PRIMARY KEY (`si_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4;





ALTER TABLE `products` ADD COLUMN `prod_pkg_id` INT(11) NULL DEFAULT 0 AFTER `prod_pack_unit`;
ALTER TABLE `product_variants` ADD COLUMN `pv_pkg_id` INT(11) NULL DEFAULT 0 AFTER `pv_pack_quantity`;
ALTER TABLE `cart` ADD COLUMN `cart_packaging_fee` DOUBLE NULL DEFAULT 0 AFTER `cart_subtotal`;
ALTER TABLE `products` ADD COLUMN `prod_pack_pcs_unit` INT(11) NULL DEFAULT 0 AFTER `prod_pack_quantity`;
ALTER TABLE `product_variants` ADD COLUMN `pv_pack_pcs_unit` INT(11) NULL DEFAULT 0 AFTER `pv_pack_unit`;



ALTER TABLE `cart` ADD COLUMN `cart_order_number` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `cart_key`;
ALTER TABLE `hosts` ADD COLUMN `host_maintenance` TINYINT(1) NULL DEFAULT 0 AFTER `host_auth_error`;
ALTER TABLE `cart` ADD COLUMN `cart_invoice_type` INT(11) NULL DEFAULT 0 AFTER `cart_deleted`;
ALTER TABLE `cart` ADD COLUMN `cart_remarks` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL AFTER `cart_invoice_type`;


ALTER TABLE `hosts` ADD COLUMN `host_share_session` TINYINT(1) NULL DEFAULT 0 AFTER `host_smtp_pwd`;
ALTER TABLE `hosts` ADD COLUMN `host_protect` TINYINT(1) NULL DEFAULT 0 AFTER `host_share_session`;
ALTER TABLE `hosts` ADD COLUMN `host_auth_user` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL AFTER `host_protect`;
ALTER TABLE `hosts` ADD COLUMN `host_auth_password` BLOB NULL AFTER `host_auth_user`;
ALTER TABLE `hosts` ADD COLUMN `host_auth_realm` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL AFTER `host_auth_password`;
ALTER TABLE `hosts` ADD COLUMN `host_auth_error` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL AFTER `host_auth_realm`;


ALTER TABLE `cart_items` CHANGE COLUMN `citem_pack_unit` `citem_pack_unit` VARCHAR(128) NULL DEFAULT 0  COMMENT '' AFTER `citem_quantity`;
ALTER TABLE `cart_items` CHANGE COLUMN `citem_weight_unit` `citem_weight_unit` VARCHAR(128) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL  COMMENT '' AFTER `citem_weight`;
ALTER TABLE `shipping_modes` ADD COLUMN `sm_free_limit` DOUBLE NULL DEFAULT 0 AFTER `sm_price`;
ALTER TABLE `shipping_modes` ADD COLUMN `sm_default` TINYINT(1) NULL DEFAULT 0 AFTER `sm_enabled`;
ALTER TABLE `payment_modes` ADD COLUMN `pm_default` TINYINT(1) NULL DEFAULT 0 AFTER `pm_enabled`;
ALTER TABLE `users` CHANGE COLUMN `us_email` `us_email` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL  COMMENT '' AFTER `us_role`;
ALTER TABLE `users` ADD COLUMN `us_email2` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `us_email`;
ALTER TABLE `users` ADD COLUMN `us_invoice_type` TINYINT(1) NULL DEFAULT 0 AFTER `us_address`;

ALTER TABLE `hosts` ADD COLUMN `host_smtp_host` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL AFTER `host_production`;
ALTER TABLE `hosts` ADD COLUMN `host_smtp_port` INT NULL DEFAULT 2525 AFTER `host_smtp_host`;
ALTER TABLE `hosts` ADD COLUMN `host_smtp_ssl` ENUM('TLS','SSL') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL AFTER `host_smtp_port`;
ALTER TABLE `hosts` ADD COLUMN `host_smtp_user` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL AFTER `host_smtp_ssl`;
ALTER TABLE `hosts` ADD COLUMN `host_smtp_pwd` BLOB NULL COMMENT '' AFTER `host_smtp_user`;







ALTER TABLE `product_categories` ADD COLUMN `cat_smart` TINYINT(1) NULL DEFAULT 0 AFTER `cat_page_img`;
ALTER TABLE `product_categories` ADD COLUMN `cat_tags` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL AFTER `cat_smart`;



ALTER TABLE `hosts` CHANGE COLUMN `host_theme` `host_theme` ENUM('none','mimity','bellaria') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL  COMMENT '' AFTER `host_application`;
ALTER TABLE `products` ADD COLUMN `prod_intro` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL AFTER `prod_language`;
