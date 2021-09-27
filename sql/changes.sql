ALTER TABLE `product_categories` ADD COLUMN `cat_smart` TINYINT(1) NULL DEFAULT 0 AFTER `cat_page_img`;
ALTER TABLE `product_categories` ADD COLUMN `cat_tags` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL AFTER `cat_smart`;



ALTER TABLE `hosts` CHANGE COLUMN `host_theme` `host_theme` ENUM('none','mimity','bellaria') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL  COMMENT '' AFTER `host_application`;
ALTER TABLE `products` ADD COLUMN `prod_intro` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL AFTER `prod_language`;
