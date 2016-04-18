ALTER TABLE `z_good`
ADD COLUMN `order_search`  int(11) NULL DEFAULT 0 AFTER `seo_auto`;

ALTER TABLE `goods_zukk`
ADD COLUMN `order_search`  int(11) NULL AFTER `main_section_id`;

