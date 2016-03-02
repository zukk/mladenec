CREATE TABLE `blocklinks` (
`id`  int(11) NOT NULL AUTO_INCREMENT ,
`link`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL ,
PRIMARY KEY (`id`)
);

CREATE TABLE `blocklinksanchor` (
`url_id`  int(11) NULL ,
`title`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL ,
`url`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL
)
;
