CREATE TABLE `blocklinks` (
`id`  int(11) NOT NULL AUTO_INCREMENT ,
`link`  varchar(255) NULL ,
PRIMARY KEY (`id`)
);

CREATE TABLE `blocklinksanchor` (
`id_url`  int(11) NULL ,
`title`  varchar(255) NULL ,
`url`  varchar(255) NULL
)
;
