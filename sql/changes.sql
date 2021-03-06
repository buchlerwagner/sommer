# noinspection SqlNoDataSourceInspectionForFile
-- 2022-03-04
ALTER TABLE `product_variants` ADD COLUMN `pv_no_cash` TINYINT(1) NULL DEFAULT 0 AFTER `pv_max_sale`;
ALTER TABLE `products` ADD COLUMN `prod_no_cash` TINYINT(1) NULL DEFAULT 0 AFTER `prod_archived`;
ALTER TABLE `cart` ADD COLUMN `cart_coupon_id` INT(11) NULL DEFAULT 0 AFTER `cart_refunded`;

ALTER TABLE `cart` CHANGE COLUMN `cart_subtotal` `cart_subtotal` DOUBLE NULL DEFAULT 0  COMMENT '' AFTER `cart_order_status`;
ALTER TABLE `cart` CHANGE COLUMN `cart_shipping_fee` `cart_shipping_fee` DOUBLE NULL DEFAULT 0  COMMENT '' AFTER `cart_packaging_fee`;
ALTER TABLE `cart` CHANGE COLUMN `cart_payment_fee` `cart_payment_fee` DOUBLE NULL DEFAULT 0  COMMENT '' AFTER `cart_shipping_fee`;
ALTER TABLE `cart` CHANGE COLUMN `cart_discount` `cart_discount` DOUBLE NULL DEFAULT 0  COMMENT '' AFTER `cart_payment_fee`;
ALTER TABLE `cart` CHANGE COLUMN `cart_total` `cart_total` DOUBLE NULL DEFAULT 0  COMMENT '' AFTER `cart_discount`;

CREATE TABLE `loyalty_rules` (
     `lr_id` int(11) NOT NULL AUTO_INCREMENT,
     `lr_shop_id` int(11) DEFAULT NULL,
     `lr_valid_from` date DEFAULT NULL,
     `lr_min_order_limit` float DEFAULT 0,
     `lr_discount` float DEFAULT 0,
     `lr_only_paid` tinyint(1) DEFAULT 0,
     `lr_only_finished` tinyint(1) DEFAULT 0,
     PRIMARY KEY (`lr_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;

CREATE TABLE `automatic_coupons` (
     `ac_id` int(11) NOT NULL AUTO_INCREMENT,
     `ac_shop_id` int(11) DEFAULT NULL,
     `ac_code_length` tinyint(1) DEFAULT 6,
     `ac_expiry_days` int(11) DEFAULT 7,
     `ac_min_sale_limit` float DEFAULT 0,
     `ac_min_order_limit` float DEFAULT 0,
     `ac_discount_value` float DEFAULT 0,
     `ac_discount_percent` float DEFAULT 0,
     `ac_include_discounted_products` tinyint(1) DEFAULT 0,
     `ac_multiple_usage` tinyint(1) DEFAULT 0,
     `ac_enabled` tinyint(1) DEFAULT 0,
     PRIMARY KEY (`ac_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;

CREATE TABLE `coupons` (
   `c_id` int(11) NOT NULL AUTO_INCREMENT,
   `c_shop_id` int(11) DEFAULT NULL,
   `c_code` varchar(10) DEFAULT NULL,
   `c_created` datetime DEFAULT NULL,
   `c_expiry` date DEFAULT NULL,
   `c_min_order_limit` float DEFAULT 0,
   `c_discount_value` float DEFAULT 0,
   `c_discount_percent` float DEFAULT 0,
   `c_include_discounted_products` tinyint(1) DEFAULT 0,
   `c_multiple_usage` tinyint(1) DEFAULT 0,
   `c_enabled` tinyint(1) DEFAULT 1,
   PRIMARY KEY (`c_id`),
   UNIQUE KEY `code` (`c_code`,`c_shop_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4;


CREATE TABLE `coupon_usage` (
    `cu_id` int(11) NOT NULL AUTO_INCREMENT,
    `cu_c_id` int(11) DEFAULT NULL,
    `cu_us_id` int(11) DEFAULT 0,
    `cu_cart_id` int(11) DEFAULT 0,
    `cu_timestamp` timestamp NULL DEFAULT NULL,
    `cu_value` float DEFAULT 0,
    `cu_currency` char(3) DEFAULT 'HUF',
    PRIMARY KEY (`cu_id`),
    UNIQUE KEY `uq` (`cu_cart_id`,`cu_c_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4;







ALTER TABLE `cart` ADD COLUMN `cart_refunded` FLOAT NULL DEFAULT NULL AFTER `cart_invoice_filename`;
ALTER TABLE `payment_providers` CHANGE COLUMN `pp_shopid` `pp_merchant_id` VARCHAR(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL  COMMENT '' AFTER `pp_provider`;

-- 2022-02-21
CREATE TABLE `invoice_providers` (
     `iv_id` int(11) NOT NULL AUTO_INCREMENT,
     `iv_shop_id` int(11) DEFAULT 0,
     `iv_name` varchar(255) DEFAULT NULL,
     `iv_provider` varchar(100) DEFAULT NULL,
     `iv_user_name` varchar(128) DEFAULT NULL,
     `iv_password` varchar(128) DEFAULT NULL,
     `iv_api_key` varchar(128) DEFAULT NULL,
     `iv_test_mode` varchar(128) DEFAULT NULL,
     `iv_enabled` tinyint(1) DEFAULT 0,
     `iv_manual` tinyint(1) DEFAULT 0,
     PRIMARY KEY (`iv_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;

ALTER TABLE `cart` ADD COLUMN `cart_invoice_number` VARCHAR(128) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `cart_custom_interval`;
ALTER TABLE `cart` ADD COLUMN `cart_invoice_provider` INT(11) NULL DEFAULT 0 AFTER `cart_invoice_number`;
ALTER TABLE `cart` ADD COLUMN `cart_invoice_filename` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `cart_invoice_provider`;

ALTER TABLE `cart_items` ADD COLUMN `citem_local_consumption` TINYINT(1) NULL DEFAULT 0 AFTER `citem_url`;
ALTER TABLE `payment_modes` ADD COLUMN `pm_limit_max` FLOAT NULL DEFAULT 0 AFTER `pm_order`;

ALTER TABLE `cart` DROP COLUMN `cart_local_consumption` ;






-- 2022-02-02

ALTER TABLE `products` ADD COLUMN `prod_vat_local` DOUBLE NULL DEFAULT 5 AFTER `prod_price_discount`;
ALTER TABLE `products` ADD COLUMN `prod_vat_deliver` DOUBLE NULL DEFAULT 18 AFTER `prod_vat_local`;
ALTER TABLE `products` ADD COLUMN `prod_in_store_only` TINYINT(1) NULL DEFAULT 0 AFTER `prod_available`;

ALTER TABLE `product_categories` ADD COLUMN `cat_takeover_days` VARCHAR(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL AFTER `cat_date_end`;
ALTER TABLE `product_categories` ADD COLUMN `cat_only_in_stores` TINYINT(1) NULL DEFAULT 0 AFTER `cat_takeover_days`;
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

CREATE TABLE `stores` (
      `st_id` int(11) NOT NULL AUTO_INCREMENT,
      `st_shop_id` int(11) DEFAULT 0,
      `st_code` varchar(10) DEFAULT NULL,
      `st_name` varchar(255) DEFAULT NULL,
      `st_virtual` tinyint(1) DEFAULT 0,
      PRIMARY KEY (`st_id`),
      UNIQUE KEY `code` (`st_code`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4;

INSERT INTO `access_functions` (`af_page`, `af_key`, `af_name`) VALUES
    ('orders','orders-phone','Telefonos rendel??sfelv??tel'),
    ('orders','orders-store','Bolti rendel??sfelv??tel');

INSERT INTO `templates` (`mt_id`, `mt_shop_id`, `mt_type`, `mt_key`, `mt_language`, `mt_subject`, `mt_body`, `mt_keywords`, `mt_description`, `mt_template`) VALUES
    (179,1,'MAIL','payment','hu','Online fizet??s ??rtes??t??: {{ orderNumber }}','<div>Kedves <b>{{ firstName }}</b>!</div><div><br></div>K??sz??nj??k megrendel??s??t.<b><br></b><div><br></div><div>{{ order }}<br></div><div><br></div><div>??z al??bbi linkre kattintva megtekintheti rendel??s??t:<br></div><div><a href="{{ link }}" target="_blank">{{ link }}</a><br></div>','domain|firstName|lastName|email|link|order|orderNumber|transactionId|authCode|paymentStatus','Online fizet??s ??rtes??t??',NULL);

ALTER TABLE `gallery` ADD COLUMN `g_folder` INT(11) NULL DEFAULT 0 AFTER `g_main`;
UPDATE gallery SET g_folder = 1;

CREATE VIEW `daily_orders` AS select `cart`.`cart_shop_id` AS `shopId`,`product_categories`.`cat_id` AS `categoryId`,`product_categories`.`cat_title` AS `categoryName`,`products`.`prod_id` AS `productId`,`products`.`prod_name` AS `productName`,ifnull(`product_variants`.`pv_id`,0) AS `variantId`,`product_variants`.`pv_name` AS `variantName`,`cart_items`.`citem_quantity` AS `quantity`,`cart_items`.`citem_pack_unit` AS `unit`,`cart`.`cart_store_id` AS `orderOrigin`,`shipping_modes`.`sm_type` AS `shippingType`,`shipping_modes`.`sm_code` AS `shippingCode`,`stores`.`st_name` AS `shippingStoreName`,`cart`.`cart_shipping_date` AS `shippingDate` from ((((((`cart_items` left join `cart` on(`cart`.`cart_id` = `cart_items`.`citem_cart_id`)) left join `products` on(`products`.`prod_id` = `cart_items`.`citem_prod_id`)) left join `product_variants` on(`product_variants`.`pv_id` = `cart_items`.`citem_prod_variant`)) left join `product_categories` on(`product_categories`.`cat_id` = `products`.`prod_cat_id`)) left join `shipping_modes` on(`shipping_modes`.`sm_id` = `cart`.`cart_sm_id`)) left join `stores` on(`stores`.`st_code` = `shipping_modes`.`sm_code`)) where `cart`.`cart_status` = 'ORDERED' and (`cart`.`cart_order_status` = 'NEW' or `cart`.`cart_order_status` = 'PROCESSING') order by `product_categories`.`cat_id`,`products`.`prod_id`;
CREATE VIEW `daily_sales` AS select `cart`.`cart_id` AS `cartId`,`cart`.`cart_order_number` AS `orderNumber`,`cart`.`cart_order_status` AS `orderStatus`,`cart`.`cart_shop_id` AS `shopId`,`product_categories`.`cat_id` AS `categoryId`,`product_categories`.`cat_title` AS `categoryName`,`products`.`prod_id` AS `productId`,`products`.`prod_name` AS `productName`,ifnull(`product_variants`.`pv_id`,0) AS `variantId`,`product_variants`.`pv_name` AS `variantName`,`cart_items`.`citem_quantity` AS `quantity`,`cart_items`.`citem_pack_unit` AS `unit`,`cart`.`cart_local_consumption` AS `isLocalConsumption`,if(`cart`.`cart_local_consumption`,ifnull(`product_variants`.`pv_vat_local`,`products`.`prod_vat_local`),ifnull(`product_variants`.`pv_vat_deliver`,`products`.`prod_vat_deliver`)) AS `vatKey`,round(`cart_items`.`citem_price` * `cart_items`.`citem_quantity` * (if(`cart`.`cart_local_consumption`,ifnull(`product_variants`.`pv_vat_local`,`products`.`prod_vat_local`),ifnull(`product_variants`.`pv_vat_deliver`,`products`.`prod_vat_deliver`)) / 100),0) AS `vat`,round(`cart_items`.`citem_price` * `cart_items`.`citem_quantity` / (if(`cart`.`cart_local_consumption`,ifnull(`product_variants`.`pv_vat_local`,`products`.`prod_vat_local`),ifnull(`product_variants`.`pv_vat_deliver`,`products`.`prod_vat_deliver`)) / 100 + 1),0) AS `netTotal`,`cart_items`.`citem_price` * `cart_items`.`citem_quantity` AS `grossTotal`,`cart`.`cart_currency` AS `currency`,`cart`.`cart_store_id` AS `orderOrigin`,`s2`.`st_name` AS `originStoreName`,`shipping_modes`.`sm_type` AS `shippingType`,`shipping_modes`.`sm_code` AS `shippingCode`,`s1`.`st_name` AS `shippingStoreName`,`cart`.`cart_us_id` AS `sellerId`,`cart`.`cart_ordered` AS `orderDate`,`cart`.`cart_paid` AS `isPaid` from (((((((`cart_items` left join `cart` on(`cart`.`cart_id` = `cart_items`.`citem_cart_id`)) left join `products` on(`products`.`prod_id` = `cart_items`.`citem_prod_id`)) left join `product_variants` on(`product_variants`.`pv_id` = `cart_items`.`citem_prod_variant`)) left join `product_categories` on(`product_categories`.`cat_id` = `products`.`prod_cat_id`)) left join `shipping_modes` on(`shipping_modes`.`sm_id` = `cart`.`cart_sm_id`)) left join `stores` `s1` on(`s1`.`st_code` = `shipping_modes`.`sm_code`)) left join `stores` `s2` on(`s2`.`st_code` = `cart`.`cart_store_id`)) where `cart`.`cart_status` = 'ORDERED' order by `cart`.`cart_ordered`;
CREATE VIEW `orders` AS select `cart`.`cart_id` AS `cartId`,`cart`.`cart_order_number` AS `orderNumber`,`cart`.`cart_shop_id` AS `shopId`,`product_categories`.`cat_id` AS `categoryId`,`product_categories`.`cat_title` AS `categoryName`,`products`.`prod_id` AS `productId`,`products`.`prod_name` AS `productName`,ifnull(`product_variants`.`pv_id`,0) AS `variantId`,`product_variants`.`pv_name` AS `variantName`,`cart_items`.`citem_quantity` AS `quantity`,`cart_items`.`citem_pack_unit` AS `unit`,`cart`.`cart_store_id` AS `orderOrigin`,`shipping_modes`.`sm_type` AS `shippingType`,`shipping_modes`.`sm_code` AS `shippingCode`,`stores`.`st_name` AS `shippingStoreName`,`cart`.`cart_shipping_date` AS `shippingDate`,`cart`.`cart_ordered` AS `orderDate`,`cart`.`cart_remarks` AS `remarks`,concat(`users`.`us_lastname`,' ',`users`.`us_firstname`) AS `customerName`,`users`.`us_phone` AS `customerPhone` from (((((((`cart_items` left join `cart` on(`cart`.`cart_id` = `cart_items`.`citem_cart_id`)) left join `products` on(`products`.`prod_id` = `cart_items`.`citem_prod_id`)) left join `product_variants` on(`product_variants`.`pv_id` = `cart_items`.`citem_prod_variant`)) left join `product_categories` on(`product_categories`.`cat_id` = `products`.`prod_cat_id`)) left join `shipping_modes` on(`shipping_modes`.`sm_id` = `cart`.`cart_sm_id`)) left join `stores` on(`stores`.`st_code` = `shipping_modes`.`sm_code`)) left join `users` on(`users`.`us_id` = `cart`.`cart_us_id`)) where `cart`.`cart_status` = 'ORDERED' and (`cart`.`cart_order_status` = 'NEW' or `cart`.`cart_order_status` = 'PROCESSING') order by `cart`.`cart_ordered`,`cart`.`cart_id`;




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
