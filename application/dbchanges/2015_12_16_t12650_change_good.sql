ALTER TABLE `z_good` ADD COLUMN `wiki_cat_id` int(11) NOT NULL;
ALTER TABLE `wiki_categories` ADD INDEX `category_id` (`category_id`) ;
ALTER TABLE `z_section` DROP COLUMN `wikimart_cat_id`;
ALTER TABLE `z_menu` ADD COLUMN `show_menu` tinyint(4) NOT NULL AFTER `sort`;