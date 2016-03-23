ALTER TABLE `z_coupon`
ADD COLUMN `order_id`  int(11) NULL AFTER `user_id`;

ALTER TABLE `z_config`
ADD COLUMN `emails`  text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `use_ozon_delivery`;