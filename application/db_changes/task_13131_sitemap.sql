CREATE TABLE `z_seostatistics` (
`id`  int(11) NOT NULL AUTO_INCREMENT ,
`products_count`  int(11) NULL ,
`prod_missing_title`  int(11) NULL ,
`prod_missing_desc`  int(11) NULL ,
`prod_missing_keywords`  int(11) NULL ,
`categories_count`  int(11) NULL ,
`categories_missing_title`  int(11) NULL ,
`categories_missing_desc`  int(11) NULL ,
`categories_missing_keywords`  int(11) NULL ,
`tags_count`  int(11) NULL ,
`tags_missing_title`  int(11) NULL ,
`tags_missing_desc`  int(11) NULL ,
`tags_missing_keywords`  int(11) NULL ,
`date`  datetime NULL ,
PRIMARY KEY (`id`));

