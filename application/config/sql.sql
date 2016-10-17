-- мкад
INSERT INTO z_zone (id, name, poly) VALUES
(2,'Внутри МКаД','37.843109 55.774567,37.843307 55.770175,37.842990 55.765782,37.842897 55.763204,37.842232 55.747672,37.841445 55.739224,37.840565 55.730579,37.839383 55.721782,37.838842 55.716961,37.838357 55.714526,37.837443 55.712140,37.832449 55.702566,37.830231 55.698467,37.829428 55.694271,37.829297 55.693187,37.829337 55.692128,37.829633 55.689743,37.831425 55.685214,37.834695 55.676732,37.837985 55.667708,37.839308 55.663127,37.839754 55.660837,37.839686 55.658546,37.838001 55.654643,37.836729 55.652668,37.835114 55.650789,37.829967 55.647124,37.824733 55.643846,37.813574 55.636482,37.803083 55.629521,37.798500 55.626461,37.793573 55.623401,37.781873 55.617532,37.770280 55.610989,37.758789 55.604703,37.753294 55.601817,37.747199 55.599077,37.736949 55.594763,37.721045 55.588129,37.709769 55.583882,37.696047 55.578764,37.683215 55.574212,37.679442 55.573088,37.675839 55.572449,37.668463 55.571805,37.649948 55.572767,37.632520 55.573749,37.619243 55.574579,37.600828 55.575721,37.597166 55.575970,37.593590 55.576365,37.586609 55.577616,37.571670 55.581167,37.557370 55.584580,37.534541 55.590027,37.527732 55.591660,37.519619 55.593647,37.511850 55.596022,37.509017 55.597463,37.506356 55.599001,37.501462 55.602806,37.493577 55.609639,37.485854 55.616283,37.482047 55.619638,37.477812 55.623066,37.466881 55.632617,37.458640 55.639496,37.450135 55.646802,37.441221 55.654269,37.432752 55.661729,37.425058 55.671459,37.418497 55.680179,37.416118 55.683968,37.414426 55.687999,37.413447 55.689894,37.411953 55.691887,37.407591 55.695338,37.397934 55.702470,37.388512 55.709739,37.385866 55.713393,37.383220 55.718354,37.379681 55.725427,37.374736 55.734992,37.369732 55.745294,37.369081 55.747607,37.368858 55.749919,37.368843 55.754980,37.369062 55.763022,37.369253 55.771476,37.369567 55.782358,37.369761 55.784150,37.370642 55.785846,37.372576 55.789623,37.378620 55.796031,37.383306 55.800779,37.385563 55.803516,37.387262 55.806252,37.388886 55.810472,37.390039 55.814788,37.393018 55.824216,37.395176 55.832257,37.395930 55.834542,37.395912 55.836827,37.394502 55.841299,37.392541 55.843911,37.391867 55.845823,37.391708 55.847047,37.392151 55.848271,37.392949 55.850767,37.397368 55.858756,37.400295 55.862596,37.404251 55.866388,37.407317 55.868456,37.410899 55.870524,37.417289 55.873212,37.429480 55.877061,37.442885 55.881200,37.446879 55.882075,37.451474 55.882661,37.459548 55.882868,37.463027 55.882987,37.467107 55.883348,37.474237 55.885130,37.482190 55.886934,37.489799 55.889221,37.502718 55.894963,37.518757 55.902046,37.523531 55.903993,37.527018 55.905217,37.530304 55.906037,37.533590 55.906664,37.536427 55.907074,37.543733 55.907942,37.559674 55.909488,37.575411 55.911105,37.580036 55.910948,37.583889 55.910598,37.587227 55.910128,37.590565 55.909416,37.606894 55.904848,37.614231 55.902795,37.621825 55.901080,37.633083 55.899007,37.642774 55.897460,37.652979 55.896443,37.658971 55.895901,37.664878 55.895528,37.681432 55.895081,37.691826 55.894749,37.697157 55.894272,37.704606 55.892580,37.711154 55.889947,37.723462 55.883656,37.736122 55.877348,37.744705 55.872951,37.757536 55.866327,37.773962 55.857880,37.780642 55.854454,37.792647 55.848268,37.808290 55.840215,37.816422 55.836007,37.829941 55.828936,37.832309 55.827224,37.834334 55.825367,37.837354 55.821411,37.838168 55.818971,37.838467 55.816483,37.839237 55.811506,37.839786 55.802607,37.840976 55.793965,37.843109 55.774567');

-- геометрия зоны в специальном поле
UPDATE z_zone SET `polygon` = GeomFromText(CONCAT('POLYGON((', poly, '))'));

# новинки по новому
UPDATE `z_good_prop` SET new_till = NULL;
INSERT INTO z_good_prop (id, new_till )
  SELECT  item_id, MIN(timestamp) FROM `z_history` WHERE `module` ='good' AND action = 'image 500 add' AND item_id > 0
  GROUP BY item_id
ON DUPLICATE KEY update new_till = VALUES(new_till);

DELETE FROM z_good_prop WHERE id NOT IN (SELECT id FROM z_good);

UPDATE z_good_prop SET new_till = DATE_ADD(new_till,INTERVAL 1 MONTH) WHERE `new_till` IS NOT NULL;
UPDATE z_good_prop SET new_till = NULL, new = 0 WHERE new_till < NOW();
UPDATE z_good_prop SET new = 1 WHERE new_till IS NOT NULL;
UPDATE z_good_prop SET new = 0 WHERE new_till IS NULL;
UPDATE z_good g, z_good_prop p SET g.new = p.new WHERE g.id = p.id;

# что с чем заказывают - пересчёт по всем доставленным заказам
DROP TABLE IF EXISTS z_good_good;

CREATE TABLE `z_good_good` (
  `max_good_id` INT UNSIGNED NOT NULL ,
  `min_good_id` INT UNSIGNED NOT NULL ,
  `qty` SMALLINT UNSIGNED NOT NULL DEFAULT  '0',
  PRIMARY KEY (  `max_good_id` ,  `min_good_id` )
) ENGINE = INNODB;

INSERT INTO z_good_good (max_good_id, min_good_id, qty)
  SELECT z.good_id as g1, z1.good_id as g2, 1
  FROM z_order_good z
    JOIN z_order_good z1 ON (z1.order_id = z.order_id)
    JOIN z_order o ON (o.id = z.order_id AND o.status = 'F')
    JOIN z_order o1 ON (o1.id = z1.order_id AND o1.status = 'F')
  WHERE z.good_id > z1.good_id
ON DUPLICATE KEY UPDATE qty = qty + 1
;

ALTER TABLE `z_good_good` ADD INDEX (  `min_good_id` ,  `qty` );

DELETE FROM `z_good_good` WHERE min_good_id NOT IN (SELECT id FROM z_good WHERE `show` = 1 AND section_id > 0 AND brand_id > 0 AND group_id > 0);
DELETE FROM `z_good_good` WHERE max_good_id NOT IN (SELECT id FROM z_good WHERE `show` = 1 AND section_id > 0 AND brand_id > 0 AND group_id > 0);

# товары в продвижение, для Красавцева
SELECT
  CONCAT(group_name, ' ', name) as name,
  CONCAT('/product/', translit, '/', group_id, '.', id, '.html') as url,
  section_id as category_slug,
  brand_id as class_slug,
  code as article,
  price,
  IF (`show` = 1 and qty != 0, 1, 0) as active
FROM z_good WHERE move = 1;

#сортировка отзывов
SET sql_mode = 'NO_UNSIGNED_SUBTRACTION';
UPDATe z_good_review SET priority = vote_ok - vote_no - FLOOR( DATEDIFF( NOW( ) , FROM_UNIXTIME( TIME ) ) / 100 );

UPDATE z_group SET good = 1 WHERE section_id IN (SELECT id FROM z_section WHERE parent_id = 29690);

#старые новинки
update  z_good g, z_good_prop gp set g.new = 0, gp.new_till = null where new_till is not null and new_till < now() and g.id = gp.id;

#настройки категорий
alter table z_section add column `settings` varchar(512) not null default '';
alter table z_section add column `img_menu` int(10) unsigned not null default 0;
UPDATE z_section SET settings = concat('{"per_page" :[12, 24, 48], "m":"0", "x":"', show_instock ,'", "buy":"', buy_button_type,'", "s":"rating", "sub":0}');
ALTER TABLE  `z_section` ADD  `text` TEXT NOT NULL;

#картинки для значений фильтров
ALTER TABLE z_filter_value ADD column `img` int(10) unsigned not null default 0;

#теговые страницы
alter table z_tag change column `code` `code` varchar(255) not null;
alter table z_tag add column `keywords` varchar(255) not null after `description`;
update z_tag set `code` = CONCAT('tag/', `code`, '.html');

alter table z_tag add column `section_id` int unsigned not null default 0;

# этот запрос надо делать после выполнения скрипта www/section_translit.php
ALTER TABLE  `z_section` ADD UNIQUE (
  `translit`
);

ALTER TABLE  `z_tag` ADD INDEX (  `goods_count` );

# анкеты для памперса

CREATE TABLE `pampers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `weight` varchar(24) DEFAULT NULL,
  `age` varchar(24) DEFAULT NULL,
  `index` varchar(6) DEFAULT NULL,
  `address` text,
  `phone` varchar(24) DEFAULT NULL,
  `email` varchar(24) DEFAULT NULL,
  `site` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;


ALTER table z_section
  add column title varchar(255) not null,
  add column keywords varchar(255) not null,
  add column description text not null;

alter table z_tag add column anchor varchar(255) not null;

# запросы для картинок
UPDATE z_good_prop SET img380 = 0;
INSERT INTO z_good_prop (id, img380)
  SELECT good_id, MIN(file_id) FROM `z_good_img`
  WHERE size = '380'
  GROUP BY good_id
ON DUPLICATE KEY UPDATE img380 = VALUES(img380);

UPDATE z_good_prop SET img255 = 0;
INSERT INTO z_good_prop (id, img255)
  SELECT good_id, MIN(file_id) FROM `z_good_img`
  WHERE size = '255'
  GROUP BY good_id
ON DUPLICATE KEY UPDATE img255 = VALUES(img255);

UPDATE z_good_prop SET img70 = 0;
INSERT INTO z_good_prop (id, img70)
  SELECT good_id, MIN(file_id) FROM `z_good_img`
  WHERE size = '70'
  GROUP BY good_id
ON DUPLICATE KEY UPDATE img70 = VALUES(img70);

UPDATE z_good_prop SET img380x560 = 0;
INSERT INTO z_good_prop (id, img380x560)
  SELECT good_id, MIN(file_id) FROM `z_good_img`
  WHERE size = '380x560'
  GROUP BY good_id
ON DUPLICATE KEY UPDATE img380x560 = VALUES(img380x560);

UPDATE z_good_prop SET img173x255 = 0;
INSERT INTO z_good_prop (id, img173x255)
  SELECT good_id, MIN(file_id) FROM `z_good_img`
  WHERE size = '173x255'
  GROUP BY good_id
ON DUPLICATE KEY UPDATE img173x255 = VALUES(img173x255);

#поле для размера товара
alter table z_good_prop add column size varchar(16) after weight;

#поиск дублей в одежде (товары без цвета, с более 1 цвета)
SELECT  g.id
FROM z_good g
  JOIN z_good_prop p ON ( g.id = p.id )
  JOIN z_group gr ON ( g.group_id = gr.id AND g.active = 1 )
  JOIN z_brand b ON ( g.brand_id = b.id AND b.active = 1 )
  LEFT JOIN z_hit h ON (g.id = h.good_id)
  LEFT JOIN z_good_filter gf ON ( g.id = gf.good_id )
  INNER JOIN z_section s ON ( g.section_id = s.id AND s.active = 1 )
WHERE g.show = 1 AND gr.good = 1 AND g.qty != 0 AND gf.filter_id = 1952
GROUP BY gr.id, gf.value_id


#вид плашками для всех
update z_section set settings =   replace(settings, '"m":"0"', '"m":"1"') where settings like '%"m":"0"%';

#запрос для проверки наличия всех размеров картинок
SELECT good_id,
  SUM(IF(size = '1600', 1, 0)) as _1600,
  SUM(IF(size = '255', 1, 0)) as _255,
  SUM(IF(size = '70', 1, 0)) as _70,
  SUM(IF(size = '380', 1, 0)) as _380,
  SUM(IF(size = '380x560', 1, 0)) as _380x560,
  SUM(IF(size = '173x255', 1, 0)) as _173x255
FROM `z_good_img`
GROUP BY good_id
HAVING (
  SUM(IF(size = '1600', 1, 0)) != SUM(IF(size = '255', 1, 0))
  OR SUM(IF(size = '70', 1, 0))  != SUM(IF(size = '380', 1, 0))
  OR SUM(IF(size = '380x560', 1, 0)) != SUM(IF(size = '173x255', 1, 0))
  OR SUM(IF(size = '1600', 1, 0)) != SUM(IF(size = '70', 1, 0))
  OR SUM(IF(size = '1600', 1, 0)) != SUM(IF(size = '380x560', 1, 0))
)

#таблица с редиректами - меняем структуру чтобы сделать один столбец с уникальными урлами
ALTER TABLE `tag_redirect` DROP PRIMARY KEY;
ALTER TABLE `tag_redirect` ADD  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;
ALTER TABLE `tag_redirect` CHANGE  `to`  `url` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
ALTER TABLE `tag_redirect` ADD  `to_id` INT UNSIGNED NOT NULL;
INSERT INTO tag_redirect(url, to_id) SELECT  `from`, id FROM `tag_redirect`;
ALTER TABLE `tag_redirect` DROP `from`;
ALTER TABLE  `tag_redirect` ADD UNIQUE (
  `url`
);

#запрос, проставляющий правильный флаг show
UPDATE z_good g, z_good_prop gp SET g.show = IF (_desc != 0 AND _graf != 0 AND g.active != 0  AND price > 0 AND zombie = 0, 1, 0) WHERE g.id = gp.id;

#запрос, проставляющий вид одежды одежде
UPDATE z_good_prop SET view_type = 4 WHERE id IN (
  select id from z_good WHERE section_id in (select id from z_section where parent_id = 29690)
);

#запрос для наведения порядка в данных заказа
ALTER TABLE z_order_data change ship_day `ship_time_text` varchar(16) NOT NULL;
UPDATE z_order_data, z_order SET ship_time_text = substr(comment, 2) where comment regexp '.[0-9]+-[0-9]+' and z_order.id = z_order_data.id;
ALTER TABLE z_order change `comment`  `manager` int unsigned NOT NULL;

#заказы в один клик = поле с типом заказа
ALTER TABLE  `z_order` ADD  `type` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT  '0';

#получить цвета одежды
CREATE TABLE `good_color` (
  `good_id` int(11) NOT NULL,
  `color` varchar(64) NOT NULL,

  PRIMARY KEY (`good_id`),
  KEY `color` (`color`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE good_color ADD group_id INT UNSIGNED NOT NULL DEFAULT 0;

INSERT INTO good_color (good_id, color, group_id)
  SELECT g.id, GROUP_CONCAT(gf.value_id-16294 ORDER BY gf.value_id ASC), g.group_id
  FROM z_good g
    INNER JOIN z_group gr on (gr.good = 1  and g.group_id = gr.id)
    LEFT JOIN z_good_filter gf ON ( g.id = gf.good_id and gf.filter_id = 1952)
  WHERE g.show = 1 AND g.qty != 0
  GROUP BY g.id;

#города и регионы для dpd
CREATE TABLE `dpd_city` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) DEFAULT NULL,
  `region_id` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `dpd_region` (
  `id` tinyint(3) unsigned NOT NULL,
  `name` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

#862
alter table z_tag add column query varchar(255) not null after params;

#опрос с купоном за ответы
alter table z_poll add column coupon mediumint unsigned not null  default 0;
alter table z_user add column autologin varchar(32) not null default '';
update z_user set autologin = md5(concat(id, email, password));

#текст в опросах
ALTER TABLE  `z_poll` ADD  `text` TEXT NOT NULL AFTER  `name`;

#921
ALTER TABLE  `z_section` ADD  `h1` VARCHAR( 255 ) NOT NULL AFTER  `export_type`;
UPDATE z_section SET h1 = name;

# число подарков в день
alter table z_action add per_day mediumint unsigned not null default 0;
ALTER TABLE  `z_order_good` ADD INDEX (  `action_id` );

#тарифная зона для регионов
ALTER TABLE  `dpd_city` ADD  `zone` TINYINT UNSIGNED NOT NULL DEFAULT  '0' AFTER  `name`;

#в адреса добавим дату использования и зону
ALTER TABLE  `z_user_address` ADD  `last_used` DATETIME NULL DEFAULT NULL ,
ADD  `zone_id` SMALLINT UNSIGNED NOT NULL DEFAULT  '0';

INSERT INTO z_user_address (id, last_used)
  select od.address_id, o.created  from z_order o, z_order_data od where address_id > 0 and o.id = od.id
  ORDER by o.id
ON DUPLICATE KEY UPDATE last_used = VALUES(last_used);

# удалить пустые адреса
DELETE FROM z_user_address WHERE house = '0' OR house = '';
ALTER TABLE  `z_user_address` CHANGE  `comment`  `comment` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;

ALTER TABLE `z_user_address` DROP `address`;

# проставить зону доставки всем адресам с координатами
CREATE TABLE addr_temp (id int not null primary key, zone_id tinyint not null default 0);

# для центральной зоны
INSERT INTO addr_temp
  SELECT a.id, IF (GISWithin( GeomFromText( CONCAT('Point(', REPLACE(a.latlong, ',', ' '), ')') ), z.polygon), 2, 0)
  FROM z_user_address a
    JOIN z_zone z ON (z.id = 2)
  WHERE a.latlong LIKE '%,%';

UPDATE z_user_address a , addr_temp at  SET a.zone_id = at.zone_id WHERE at.id = a.id;
UPDATE z_user_address SET mkad = 0 WHERE zone_id = 2;

# для зоны - заМКАД
INSERT INTO addr_temp
  SELECT a.id, IF (GISWithin( GeomFromText( CONCAT('Point(', REPLACE(a.latlong, ',', ' '), ')') ), z.polygon), 1, 0)
  FROM z_user_address a
    JOIN z_zone z ON (z.id = 1)
  WHERE a.latlong LIKE '%,%' AND a.zone_id = 0
ON DUPLICATE KEY UPDATE zone_id = VALUES(zone_id);

UPDATE z_user_address a , addr_temp at  SET a.zone_id = at.zone_id WHERE at.id = a.id;

#хиты продаж
ALTER TABLE  `z_hit` ADD  `sort` TINYINT UNSIGNED NOT NULL DEFAULT  '0';

update z_hit set sort = 1 where section_id = 28552 and sort = 0 limit 1;
update z_hit set sort = 2 where section_id = 28552 and sort = 0 limit 1;
update z_hit set sort = 3 where section_id = 28552 and sort = 0 limit 1;
update z_hit set sort = 4 where section_id = 28552 and sort = 0 limit 1;
update z_hit set sort = 5 where section_id = 28552 and sort = 0 limit 1;

update z_hit set sort = 1 where section_id = 28627 and sort = 0 limit 1;
update z_hit set sort = 2 where section_id = 28627 and sort = 0 limit 1;
update z_hit set sort = 3 where section_id = 28627 and sort = 0 limit 1;
update z_hit set sort = 4 where section_id = 28627 and sort = 0 limit 1;
update z_hit set sort = 5 where section_id = 28627 and sort = 0 limit 1;

update z_hit set sort = 1 where section_id = 28934 and sort = 0 limit 1;
update z_hit set sort = 2 where section_id = 28934 and sort = 0 limit 1;
update z_hit set sort = 3 where section_id = 28934 and sort = 0 limit 1;
update z_hit set sort = 4 where section_id = 28934 and sort = 0 limit 1;
update z_hit set sort = 5 where section_id = 28934 and sort = 0 limit 1;

update z_hit set sort = 1 where section_id = 29429 and sort = 0 limit 1;
update z_hit set sort = 2 where section_id = 29429 and sort = 0 limit 1;
update z_hit set sort = 3 where section_id = 29429 and sort = 0 limit 1;
update z_hit set sort = 4 where section_id = 29429 and sort = 0 limit 1;
update z_hit set sort = 5 where section_id = 29429 and sort = 0 limit 1;

update z_hit set sort = 1 where section_id = 29558 and sort = 0 limit 1;
update z_hit set sort = 2 where section_id = 29558 and sort = 0 limit 1;
update z_hit set sort = 3 where section_id = 29558 and sort = 0 limit 1;
update z_hit set sort = 4 where section_id = 29558 and sort = 0 limit 1;
update z_hit set sort = 5 where section_id = 29558 and sort = 0 limit 1;

update z_hit set sort = 1 where section_id = 29690 and sort = 0 limit 1;
update z_hit set sort = 2 where section_id = 29690 and sort = 0 limit 1;
update z_hit set sort = 3 where section_id = 29690 and sort = 0 limit 1;
update z_hit set sort = 4 where section_id = 29690 and sort = 0 limit 1;
update z_hit set sort = 5 where section_id = 29690 and sort = 0 limit 1;

update z_hit set sort = 1 where section_id = 29777 and sort = 0 limit 1;
update z_hit set sort = 2 where section_id = 29777 and sort = 0 limit 1;
update z_hit set sort = 3 where section_id = 29777 and sort = 0 limit 1;
update z_hit set sort = 4 where section_id = 29777 and sort = 0 limit 1;
update z_hit set sort = 5 where section_id = 29777 and sort = 0 limit 1;

update z_hit set sort = 1 where section_id = 29890 and sort = 0 limit 1;
update z_hit set sort = 2 where section_id = 29890 and sort = 0 limit 1;
update z_hit set sort = 3 where section_id = 29890 and sort = 0 limit 1;
update z_hit set sort = 4 where section_id = 29890 and sort = 0 limit 1;
update z_hit set sort = 5 where section_id = 29890 and sort = 0 limit 1;

update z_hit set sort = 1 where section_id = 32122 and sort = 0 limit 1;
update z_hit set sort = 2 where section_id = 32122 and sort = 0 limit 1;
update z_hit set sort = 3 where section_id = 32122 and sort = 0 limit 1;
update z_hit set sort = 4 where section_id = 32122 and sort = 0 limit 1;
update z_hit set sort = 5 where section_id = 32122 and sort = 0 limit 1;

update z_hit set sort = 1 where section_id = 83877 and sort = 0 limit 1;
update z_hit set sort = 2 where section_id = 83877 and sort = 0 limit 1;
update z_hit set sort = 3 where section_id = 83877 and sort = 0 limit 1;
update z_hit set sort = 4 where section_id = 83877 and sort = 0 limit 1;
update z_hit set sort = 5 where section_id = 83877 and sort = 0 limit 1;

# тарифы в городах
alter table dpd_city add column tariff mediumint unsigned not null default 0;

# слова для поиска
create table good_search (id int(11) not null auto_increment primary key, words text not null) charset=utf8;
truncate good_search;
insert into good_search select id, CONCAT(group_name, ' ', name) as words from z_good where `show` != 0;

CREATE TABLE `z_suggest` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `keyword` varchar(255) NOT NULL,
  `trigrams` varchar(255) NOT NULL,
  `freq` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

#катгория маркета
alter table z_section add market_category varchar(255) not null default '';

# запрос для импорта контактов в getresponse
SELECT DATE( FROM_UNIXTIME( u.created ) ) AS register_date, MD5( CONCAT(  'Каждый охотник желает знать где сидит фазан', email ) ) AS md5, u.email, us . *
FROM z_user u
  JOIN  `user_segment` us ON us.user_id = u.id
WHERE sub =1;

# терминалы доставки DPD (добавить функции из functions.sql)
CREATE TABLE dpd_terminal (
  id mediumint unsigned not null AUTO_INCREMENT,
  code varchar(8) not null,
  name varchar(128) not null,
  address varchar(255) not null,
  latlong varchar(64) not null,
  worktime varchar(255) not null,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

alter table  dpd_terminal add point Point after latlong;
update dpd_terminal set `point` = GeomFromText(CONCAT('Point(', REPLACE(latlong, ',', ' '), ')'));

alter table z_order add column can_pay boolean default 0;
update z_order set can_pay = 1 where pay_type = 8;

# новые флаги доставки
alter table z_order_data add column no_ring boolean default 0 after `call`, add column no_call boolean default 0 after `call`;

# пооле в заказе для 1с
ALTER TABLE  `z_order` ADD  `in1c` TINYINT( 1 ) UNSIGNED NOT NULL;
UPDATE z_order SET in1c = 1 where `status` != 0;

# новые статусы
ALTER TABLE  `z_order` CHANGE  `status`  `status` ENUM(  'D',  'F',  'N',  'S',  'X',  'C',  'R',  'T' ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT  'N';

# купоны со скидками
alter table z_coupon add column type tinyint unsigned not null default 0;
create table z_coupon_good (coupon_id int unsigned not null, good_id int unsigned not null, discount tinyint unsigned not null);
alter table z_coupon_good add primary key (coupon_id, good_id);

# заказы с января 15года по месяцам в радиусе 7 км от точки
select  MONTH(created), COUNT(o.id)
FROM z_order o JOIN z_order_data od ON (o.id = od.id)
WHERE created > '2015-01-01' AND latlong LIKE '%,%' AND
      geodist_pt(
          GEOMFROMTEXT( 'POINT(37.938657 55.966892)'),
          GEOMFROMTEXT( CONCAT('POINT(', REPLACE(od.latlong, ',', ' '), ')' ))
      )  < 7 GROUP BY 1;

# подсчёт числа отправленных СМС
SELECT SUM( CEIL( LENGTH( TEXT ) /66 ) )
FROM  `z_sms`
WHERE FROM_UNIXTIME( created_ts )
BETWEEN  '2015-04-13 00:00'
AND  '2015-04-20 00:00';

# исправление для Феодосии и терминалов
ALTER TABLE  `dpd_city` CHANGE  `id`  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE  `dpd_terminal` ADD  `city_id` BIGINT UNSIGNED NOT NULL AFTER  `code`;

# купон на фест
INSERT INTO z_coupon_good (good_id, discount, coupon_id) select id, 15,  80171 from z_good where id1c = '30000038';
INSERT INTO z_coupon_good (good_id, discount, coupon_id) select id, 15,  80171 from z_good where id1c = '30000042';
INSERT INTO z_coupon_good (good_id, discount, coupon_id) select id, 15,  80171 from z_good where id1c = '30000041';
INSERT INTO z_coupon_good (good_id, discount, coupon_id) select id, 20,  80171 from z_good where id1c = '50049655';
INSERT INTO z_coupon_good (good_id, discount, coupon_id) select id, 20,  80171 from z_good where id1c = '50049677';
INSERT INTO z_coupon_good (good_id, discount, coupon_id) select id, 20,  80171 from z_good where id1c = '50049675';
INSERT INTO z_coupon_good (good_id, discount, coupon_id) select id, 15,  80171 from z_good where id1c = '30000289';
INSERT INTO z_coupon_good (good_id, discount, coupon_id) select id, 20,  80171 from z_good where id1c = '50049660';
INSERT INTO z_coupon_good (good_id, discount, coupon_id) select id, 20,  80171 from z_good where id1c = '50049664';
INSERT INTO z_coupon_good (good_id, discount, coupon_id) select id, 20,  80171 from z_good where id1c = '50049669';
INSERT INTO z_coupon_good (good_id, discount, coupon_id) select id, 20,  80171 from z_good where id1c = '50049667';
INSERT INTO z_coupon_good (good_id, discount, coupon_id) select id, 20,  80171 from z_good where id1c = '50049662';
INSERT INTO z_coupon_good (good_id, discount, coupon_id) select id, 20,  80171 from z_good where id1c = '30003663';
INSERT INTO z_coupon_good (good_id, discount, coupon_id) select id, 20,  80171 from z_good where id1c = '30016247';
INSERT INTO z_coupon_good (good_id, discount, coupon_id) select id, 20,  80171 from z_good where id1c = '30003687';
INSERT INTO z_coupon_good (good_id, discount, coupon_id) select id, 50,  80171 from z_good where id1c = '30003729';
INSERT INTO z_coupon_good (good_id, discount, coupon_id) select id, 50,  80171 from z_good where id1c = '30019096';
INSERT INTO z_coupon_good (good_id, discount, coupon_id) select id, 50,  80171 from z_good where id1c = '30003734';
INSERT INTO z_coupon_good (good_id, discount, coupon_id) select id, 50,  80171 from z_good where id1c = '30019623';
INSERT INTO z_coupon_good (good_id, discount, coupon_id) select id, 50,  80171 from z_good where id1c = '50047699';
INSERT INTO z_coupon_good (good_id, discount, coupon_id) select id, 50,  80171 from z_good where id1c = '50047700';
INSERT INTO z_coupon_good (good_id, discount, coupon_id) select id, 50,  80171 from z_good where id1c = '50047762';
INSERT INTO z_coupon_good (good_id, discount, coupon_id) select id, 50,  80171 from z_good where id1c = '50047763';
INSERT INTO z_coupon_good (good_id, discount, coupon_id) select id, 50,  80171 from z_good where id1c = '50047703';
INSERT INTO z_coupon_good (good_id, discount, coupon_id) select id, 50,  80171 from z_good where id1c = '30019745';
INSERT INTO z_coupon_good (good_id, discount, coupon_id) select id, 50,  80171 from z_good where id1c = '48327507';

#соцсети
CREATE TABLE ulogin (id int not null AUTO_INCREMENT PRIMARY KEY , identity varchar(255) unique);
ALTER TABLE  `ulogin` ADD  `user_id` INT NOT NULL AFTER  `id` ;
ALTER TABLE  `ulogin` ADD  `network` varchar(255) NOT NULL AFTER  `id`;
ALTER TABLE  `ulogin` ADD INDEX (  `user_id` );

# запрос для анализа названий в одежде
select substr(group_name, 1, locate(' ',group_name)), group_name, count(*) from z_good where `show` = 1 and  section_id in (select id from z_section where parent_id = 29690) group by 1;

# удаление дубликатов контента
insert into z_good_text (id) select t1.id from z_good_text t join z_good_text t1 where t1.id > t.id and t1.name = t.name and t1.good_id = t.good_id on duplicate key update content = '';
delete from z_good_text where content = '';
alter table z_good_text add unique(good_id, name);

# запрос для получения необмеренных товаров (кроме кгт и одежды)
SELECT g.id1c, g.code, g.name, g.group_name, p.weight, p.size
FROM z_good g
  JOIN z_good_prop p ON ( p.id = g.id )
  JOIN z_group gr ON ( gr.id = g.group_id )
  JOIN z_section s ON ( s.id = g.section_id )
WHERE g.active =1
      AND s.active = 1
      AND gr.active = 1
      AND g.qty !=0
      AND g.big !=1
      AND g.price >0
      AND  g.`show` =1
      AND (
  p.weight =  '0.00'
  OR p.size IS NULL
  OR p.size =  '1x1x1'
) AND g.section_id NOT IN (
  SELECT id FROM z_section where parent_id = 29690
);

# флаг подтверждения адреса менеджером
ALTER TABLE z_user_address ADD COLUMN `approved` tinyint(1) not null default 0;

#пустые координаты в адресах где их нет
UPDaTE z_user_address SET latlong = '' WHERE latlong NOT LIKE '%,%';

#флаг отзвона проблемного безнала
ALTER TABLE z_order add column call_card TINYINT(1) unsigned not null default 0;

#добавление стран
CREATE TABLE `z_country` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  `translit` varchar(128) NOT NULL,
  `section_id` int(10) unsigned NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `sort` mediumint(8) unsigned NOT NULL,
  `code` int(10) unsigned NOT NULL,
  `description` text NOT NULL,
  `img225` int(10) NOT NULL,
  `search_words` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `z_good` ADD `country_id` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0' AFTER `brand_id`;

# Настройка метода поиска по сайту и рекоммендательного сервиса в конфиге
ALTER TABLE `z_config`
ADD `instant_search` enum('in site','findologic') NOT NULL,
ADD `rees46_enabled` tinyint(3) unsigned NOT NULL AFTER `instant_search`,
ADD `rees46_shop_id` varchar(32) NOT NULL AFTER `rees46_enabled`,
ADD `rees46_secret_key` varchar(32) NOT NULL AFTER `rees46_shop_id`;

#бегунок по весу
INSERT INTO z_filter(id, code, name, section_id) VALUES (100, 0, 'Вес', '29798');

INSERT INTO z_filter_value(code, name, filter_id) VALUES
  (50000, 0, 100),
  (50001, 1, 100),
  (50002, 2, 100),
  (50003, 3, 100),
  (50004, 4, 100),
  (50005, 5, 100),
  (50006, 6, 100),
  (50007, 7, 100),
  (50008, 8, 100),
  (50009, 9, 100),
  (50010, 10, 100),

  (50011, 11, 100),
  (50012, 12, 100),
  (50013, 13, 100),
  (50014, 14, 100),
  (50015, 15, 100),
  (50016, 16, 100),
  (50017, 17, 100),
  (50018, 18, 100),
  (50019, 19, 100),

  (50020, 20, 100),
  (50021, 21, 100),
  (50022, 22, 100),
  (50023, 23, 100),
  (50024, 24, 100),
  (50025, 25, 100),
  (50026, 26, 100),
  (50027, 27, 100),
  (50028, 28, 100),
  (50029, 29, 100),

  (50030, 30, 100),
  (50031, 31, 100),
  (50032, 32, 100),
  (50033, 33, 100),
  (50034, 34, 100),
  (50035, 35, 100)
;
UPDATE z_filter_value SET sort = name where filter_id = 100;

#число в пачке
UPDATE z_good SET xml_id = '0';
ALTER TABLE `z_good` CHANGE `xml_id` `per_pack` TINYINT UNSIGNED NOT NULL DEFAULT '0';

#поле "ждем малыша" и срок беременности
ALTER TABLE `z_user`
ADD `pregnant` tinyint unsigned NOT NULL DEFAULT '0',
ADD `pregnant_terms` int unsigned NOT NULL AFTER `pregnant`;

#данные для сегментации по детям
ALTER TABLE `user_segment`
ADD `childs` varchar(400) NOT NULL AFTER `buy_dress`,
ADD `has_boy` tinyint NOT NULL DEFAULT '0' AFTER `childs`,
ADD `has_girl` tinyint NOT NULL DEFAULT '0' AFTER `has_boy`,
ADD `child_birth_min` date NOT NULL AFTER `has_girl`,
ADD `child_birth_max` date NOT NULL AFTER `child_birth_min`,
ADD `pregnant` tinyint NOT NULL DEFAULT '0' AFTER `child_birth_max`,
ADD `pregnant_terms` tinyint NOT NULL DEFAULT '0' AFTER `pregnant`;

#связанные фильтры
ALTER TABLE z_filter ADD COLUMN 'bind_to' VARCHAR(16) NOT NULL;

#настройка числа товаров на странице категории
UPDATE z_section set settings = REPLACE(settings, '"12","24","48"', '"20","40","80"');

#correct_addr - int
ALTER TABLE `z_user_address` CHANGE `correct_addr` `correct_addr` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0';

#добавить индекс для ускорения работы запроса юзеров без детей
ALTER TABLE `z_user_child` ADD INDEX `user_id` (`user_id`);

#индекс по редиректам теговых страниц
ALTER TABLE `tag_redirect` ADD INDEX `to_id` (`to_id`);

#номер заказа в форме претензии
ALTER TABLE `z_return` ADD `order_num` varchar(64) COLLATE 'utf8_general_ci' NOT NULL AFTER `email`;

# запросы для еженедельной рассылки
DELETE FROM z_spam_user where spam_id = 267;
INSERT INTO z_spam_user (mail, spam_id)
  SELECT  u.email, 267
  FROM z_user u
    JOIN  `user_segment` us ON us.user_id = u.id
  WHERE sub =1 AND last_order between '2015-06-24' AND '2015-06-30';
UPDATE z_spam SET status = 3 where id = 267;

#много сеансов оплаты на один заказ
ALTER TABLE `z_payment` ADD `id` INT UNSIGNED NOT NULL FIRST;
UPDATE z_payment set id = order_id;
ALTER TABLE z_payment DROP PRIMARY KEY;
ALTER TABLE `z_payment` ADD PRIMARY KEY(`id`);
ALTER TABLE `z_payment` ADD INDEX(`order_id`);
ALTER TABLE `z_payment` CHANGE `id` `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE z_order ADD pay8 DECIMAL(18,2) DEFAULT 0 NOT NULL AFTER pay_type;
ALTER TABLE z_order ADD pay1 DECIMAL(18,2) DEFAULT 0 NOT NULL AFTER pay_type;
ALTER TABLE z_order ADD payed DECIMAL(18,2) DEFAULT 0 NOT NULL AFTER pay_type;

#run utils/pay8.php before this statements
UPDATE z_order set payment = '', pay1 = price + price_ship WHERE payment = '[]' and pay_type != 8;
UPDATE z_order set payment = '', pay1 = price + price_ship WHERE payment LIKE '[]' and pay_type IS NULL;
UPDATE z_order SET pay8 = payment WHERE pay_type = 8 AND payment > '';
UPDATE z_order SET payment = '' WHERE pay_type = 8 AND payment = pay8;

#опция включения доставки от Озона
ALTER TABLE `z_config`
ADD `use_ozon_delivery` tinyint(3) NOT NULL DEFAULT '0';

#статус получения скидки за инфу о детях
ALTER TABLE z_user ADD child_discount TINYINT DEFAULT 0 NOT NULL;

#расширение истории
ALTER TABLE `z_history` CHANGE `action` `action` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;

#запросы для исправления данных по накопительной акции в которой участвуют все товары
DELETE FROM z_action_user WHERE action_id = 192388;

INSERT INTO z_action_user (action_id, user_id, from_order, `sum`, qty )
  SELECT 192388, user_id, 0, SUM(price), count(*) FROM z_order WHERE created >= '2015-07-27' AND status = 'F' GROUP BY user_id
ON DUPLICATE KEY UPDATE sum = VALUES(sum), from_order = VALUES(from_order), qty = VALUES(qty);

INSERT INTO z_action_user (action_id, user_id, from_order)
  select 192388, user_id, MAX(order_id) from z_order_good join z_order on(z_order_good.order_id = z_order.id) where action_id = 192388 and z_order.status != 'X' group by user_id
ON DUPLICATE KEY UPDATE from_order = VALUES(from_order);

#и теперь считаем сумму для накопивших несколько раз (от момента получения подарка)
INSERT INTO z_action_user(action_id, user_id, sum)
SELECT au.action_id, au.user_id, sum(o.price)
FROM z_action_user au
  JOIN z_order o ON (o.user_id = au.user_id)
WHERE au.from_order > 0 AND au.action_id = 192388 AND au.user_id > 0
  AND o.status = 'F' AND o.id > au.from_order group by au.user_id
ON DUPLICATE KEY UPDATE sum = VALUES(sum);

#проверочный запрос
SELECT o.user_id, au.sum, sum(o.price) FROM z_action_user au
  INNER JOIN z_order o on (o.user_id = au.user_id)
WHERE action_id = 192388 AND o.id > au.from_order AND o.created >= '2015-07-27' AND o.status = 'F'
GROUP BY o.user_id;

#другой проверочный запрос для сверки суммы накоплений по акции
  SELECT * FROM (SELECT o.user_id, SUM(o.price) as `sum`  FROM `z_order` o WHERE created >= '2015-07-27' AND status = 'F' AND user_id !=0 GROUP BY user_id) as t
    JOIN z_action_user a ON (t.user_id = a.user_id and action_id = 192388) WHERE a.sum != t.sum and from_order = 0;

#еще запрос - считает сколько накопили после получения подарка
SELECT au.user_id, au.from_order, au.sum, sum(o.price)
FROM z_action_user au
  JOIN z_order o ON (o.user_id = au.user_id)
WHERE au.from_order > 0 AND au.action_id = 192388 and au.user_id > 0
      and o.status = 'F' and o.id > au.from_order group by au.user_id;

#озон терминалы в базе
CREATE TABLE `ozon_terminal` (
  `id` bigint(20) NOT NULL,
  `address` varchar(256) NOT NULL,
  `lat` varchar(32) NOT NULL,
  `lng` varchar(32) NOT NULL,
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_active` tinyint NOT NULL DEFAULT '0',
  `pay_cards` tinyint NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

#184
ALTER TABLE `z_config` CHANGE `rees46_enabled` `rr_enabled` TINYINT(3) UNSIGNED NOT NULL;
ALTER TABLE `z_config`
  DROP `rees46_shop_id`,
  DROP `rees46_secret_key`;

#НДС для товаров
ALTER TABLE `z_good` ADD `nds` tinyint NULL DEFAULT '0' AFTER `price_sale_doc_id`;

#штрих код озона для отправленного заказа
ALTER TABLE `z_order_data` ADD `ozon_barcode` varchar(32) COLLATE 'utf8_general_ci' NOT NULL;
#id терминала (способа доставки) озона
ALTER TABLE `z_order_data` ADD `ozon_status` int NOT NULL;

# Запрос для RR
SELECT o.id, DATE(o.created), og.good_id, o.user_id from z_order o join z_order_good og ON (og.order_id = o.id) WHERE og.price > 1 AND quantity > 0 AND status = 'F' AND created > '2015-03-01' ORDER BY o.id DESC;

#добавим id терминала в базу
ALTER TABLE `z_order_data` ADD `ozon_delivery_id` bigint NOT NULL AFTER `address_id`;

# запрос для получения товаров без картинки500
SELECT g.code, g.code1c, g.id1c, s.name, g.group_name, g.name FROM `z_good` g
  JOIN z_group gr ON (g.group_id = gr.id)
  JOIN z_section s ON (g.section_id = s.id)
  JOIN z_good_prop p ON (p.id = g.id) WHERE p.img500 = 0 and g.active != 0 and gr.active != 0 and g.qty != 0 and gr.id != 218974 and gr.id != 32801;

#запросы для подготовки данных для suggest- поиска
TRUNCATE good_search;

INSERT INTO good_search (id, words)
  SELECT g.id, CONCAT(g.group_name, ' ', g.name)
  FROM z_good g
    JOIN z_group gr ON (gr.id = g.group_id and gr.active = 1)
    JOIN z_section s ON (s.id = g.section_id and s.active = 1)
    JOIN z_brand b ON (b.id = g.brand_id and b.active = 1)
  WHERE g.active = 1;

TRUNCATE z_suggest; # потом запустить utils/suggest.php и потом реиндекс сфинкса

# фиксация времени отправки заказа для сверки аналитики
ALTER TABLE `z_order` CHANGE `created` `created` DATETIME NOT NULL;
ALTER TABLE `z_order` ADD `sent` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `created`;

# запрос для исправления странной ошибки с 0 в user_id - подозревается протокол обмена с 1с!
UPDATE z_order o, z_order_data od, z_user_address a SET o.user_id = a.user_id
WHERE o.user_id = 0 AND o.id = od.id AND od.address_id = a.id AND a.user_id > 0;

# данные о клиенте в заказе ($_SERVER)
ALTER TABLE `z_order_data` ADD `client_data` TEXT NOT NULL ;

#ключи в подарках для удаления дублей подарков
alter table z_action_present add column id int unsigned not null;
alter table z_action_present drop PRIMARY KEY;
alter table z_action_present add UNIQUE index (action_id, good_id, val);
alter table z_action_present change id id int not null auto_increment primary key;

#число sku в промокоде на которые дается скидка
alter table z_coupon add column max_sku mediumint unsigned default 0;

#запрос на получение суммы накоплений по акции (участвуют не все товары!) с мылом клиента
select o.user_id, u.email, sum(og.price * og.quantity)
from z_order_good og
  join z_order o on (o.id = og.order_id)
  join z_user u on (u.id = o.user_id)
where
  og.good_id IN (select good_id from z_action_good where action_id = 192452)
  and o.created > (select count_from FROM z_action where id = 192452)
  and o.created < (select count_to FROM z_action where id = 192452)
  and o.status = 'F' and o.user_id > 0
group by o.user_id
order by 3 desc;

# [207]
#добавим тип доставки ( дверь-терминал)
alter table z_order add ship_type tinyint unsigned not null default 0;

#добавим код фирмы доставки ('', 'ozon', 'dpd', 'yandex')
alter table z_order add ship_code varchar(8) not null default '';

#таблица терминалов
alter table dpd_terminal add column type varchar(8);

rename table dpd_terminal to terminal;
rename table dpd_region to region;
rename table dpd_city to city;

alter table terminal add column updated TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;
alter table terminal add column is_active BOOL DEFAULT 0;
alter table terminal add column pay_cards bool default 0;

# запрос для добавления поля с id города в адреса
alter table z_user_address add column city_id bigint unsigned after city;

# id отдельно, код отдельно - в терминалах
alter table terminal change code code varchar(32);

# не только озон, любая тр компания тут
alter table z_order_data change ozon_delivery_id terminal_id mediumint unsigned not  null default 0;
alter table z_order_data change ozon_barcode ship_barcode varchar(32) not null default '';
alter table z_order_data change ozon_status ship_status int(11) NOT NULL;
alter table z_order_data add ship_tariff varchar(16) NOT NULL default '';
alter table z_order_data add ship_days TINYINT unsigned NOT NULL default '0';

# новые поля в оповещениях
# не звонить в домофон
alter table z_order_data add no_domofon tinyint (1) unsigned NOT NULL default '0';
# привезти если не отвечаю на телефон
alter table z_order_data add `force` tinyint (1) unsigned NOT NULL default '0';
# сдача
alter table z_order_data add `change` int unsigned NOT NULL default '0';

# id города в данных заказа
alter table z_order_data add column city_id bigint unsigned after city;

# [/207]

# [redesign]
ALTER TABLE `z_user` ADD `avatar_file_id` int NOT NULL;
# [/redesign]

# команда на дамп каталога чтобы получить полное зеркало каталога на тесте
mysqldump -u bitrix_mladenec_ -p bitrix_mladenec_bak b_file tag_redirect z_brand z_filter z_filter_value z_good z_good_filter z_good_img z_good_prop z_good_tag z_group z_section z_tag z_tag_brand z_tag_filter_value z_tag_section z_tag_tree > catalog.dump.sql

#страница бренда
ALTER TABLE z_brand add `text` text not null default '' after `name`;
ALTER TABLE z_brand change `img225` img int not null default 0;
ALTER TABLE z_brand drop description, drop sort, drop section_id;
# теперь запустить utils/brand_translit.php
# и потом добавить уникальный ключ по транслиту:
alter table z_brand add unique(translit);

#данные об оригинале и оптимизации в таблице файлов
alter table b_file change TIMESTAMP_X TIMESTAMP_X timestamp not null default CURRENT_TIMESTAMP;
alter table b_file change DESCRIPTION item_id int not null default 0; # ссылка на объект для которого картинка
alter table b_file change ORIGINAL_NAME original int(18) not null default 0; # ссылка на ориганал если картинка - обработанный оригинал
alter table b_file add index (MODULE_ID, item_id);
alter table b_file drop HEIGHT, drop WIDTH, drop FILE_SIZE, drop CONTENT_TYPE;

# это оригиналы картинок
update b_file set original = 1 where item_id > 0 and module_id = 'Model_Good';
# добавляем ссылку на товар в таблицу файлов
update b_file f, z_good_img gi set f.module_id = 'Model_Good', f.item_id = gi.good_id where f.ID = gi.file_id;
# и теперь запускаем utils/original.php - он поищет оригиналы и проставит id в поле original
# а потом проставить оригиналам 0
update b_file set original = 0 where original = 1;

#246
alter table  z_user add qty int unsigned not null default 0, add last_order int unsigned not null default 0;

insert into z_user (id, qty, sum, last_order)
  select user_id, count(*) as qty, sum(price + price_ship) as sum, max(id) as last_order
  from z_order where status = 'F' and user_id > 0 group by user_id
on duplicate key update qty = values(qty), sum = values(sum), last_order = values(last_order);

# команда на дамп дамп данных о заказах и юзерах для проверки работы скрипта user_segments
mysqldump -u bitrix_mladenec_ -p bitrix_mladenec_bak z_user z_order_good user_segment z_user_child > users.dump.sql

#
SELECT DATE( FROM_UNIXTIME( u.created ) ) AS register_date, MD5( CONCAT(  'Каждый охотник желает знать где сидит фазан', email ) ) AS md5, u.email, us . *
FROM z_user u
JOIN  `user_segment` us ON us.user_id = u.id
WHERE sub =1;

#флаг показа меню в статике
ALTER TABLE `z_menu` ADD `show_menu` TINYINT(1) NOT NULL DEFAULT '1' AFTER `menu`;

# [260]
alter table z_zone_time add column `code` varchar (32) not null default '';

# [261] получить юзеров, которые могут претендовать на купон за заполнение детей.
SELECT * FROM `z_user` join user_segment s on s.user_id = z_user.id WHERE sub = 1 and child_discount = 0 and email like '%@%' and childs = 0 and s.last_visit > '2000-01-01';
# [261] добавим в купоне привяку к юзеру
ALTER TABLE z_coupon ADD user_id int not null default 0;

#[258] - таблица работы с гр
CREATE TABLE getresponse (user_id int not null default 0 primary key, uploaded datetime);

#[259] - счетчик скидки за детей
ALTER TABLE z_user ADD child_birth_discount TINYINT DEFAULT 0 NOT NULL;

#[268]
ALTER TABLE z_user ADD email_approved TINYINT DEFAULT 0 NOT NULL;

#[чек в заказе]
alter table z_order add `check` varchar(16), add check_time datetime default null;

#[озон в формате OZON XML]
create table ozon_types (id bigint, name varchar (255), path_name varchar(255), template_id bigint);
alter table ozon_types add primary key (id);
alter table z_good add column ozon_type_id bigint not null default 0;
alter table ozon_types default charset=utf8;
alter table ozon_types change column name name varchar(255) character set utf8 not null;
alter table ozon_types change column path_name path_name varchar(255) character set utf8 not null;

#источник трафика в заказе
alter table z_order_data add column source varchar(255)  character set utf8 not null;
# номер заказа пользователя
alter table z_order_data add column num MEDIUMINT default 0;

#проставить номера заказов
create table order_num (order_id int, user_id int, num mediumint);
insert into order_num (order_id, user_id, num) select id, user_id, 1 from z_order;
update order_num on1 set on1.num = (select count(*) from z_order where z_order.id < on1.order_id and z_order.user_id = on1.user_id) + 1;
update z_order_data, order_num set z_order_data.num = order_num.num where order_num.order_id = z_order_data.id;
drop table order_num;

#sales_notes в yml
alter table z_action add sales_notes varchar(255) not null default '';

#запросы на удаление редиректов с карточек с ненулевой активностью
UPDATE tag_redirect, z_good g SET url = concat(url, 'mazafaka')
WHERE url = concat('product/', g.translit, '/', g.group_id, '.', g.id, '.html') and g.active != 0 and to_id > 0;
DELETE FROM tag_redirect WHERE url like '%mazafaka';
