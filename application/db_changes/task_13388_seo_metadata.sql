CREATE TABLE `z_seotemplates` (
`id`  int(11) NOT NULL AUTO_INCREMENT ,
`title`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
`rule`  text NULL ,
`type`  enum('seo_title') CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT 'seo_title' ,
`active`  tinyint(1) NULL ,
PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `z_good`
ADD COLUMN `seo_auto`  tinyint(1) NULL DEFAULT 1 AFTER `ozon_type_id`;

ALTER TABLE `z_good`
ADD INDEX `seo_auto` (`seo_auto`) USING BTREE;