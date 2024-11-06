ALTER TABLE `payment_transactions` ADD COLUMN `pt_provider_transactionid` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL AFTER `pt_transactionid`;
