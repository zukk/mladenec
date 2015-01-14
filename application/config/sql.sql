-- мкад
INSERT INTO z_zone (id, name, poly) VALUES
(2,'Внутри МКаД','37.843109 55.774567,37.843307 55.770175,37.842990 55.765782,37.842897 55.763204,37.842232 55.747672,37.841445 55.739224,37.840565 55.730579,37.839383 55.721782,37.838842 55.716961,37.838357 55.714526,37.837443 55.712140,37.832449 55.702566,37.830231 55.698467,37.829428 55.694271,37.829297 55.693187,37.829337 55.692128,37.829633 55.689743,37.831425 55.685214,37.834695 55.676732,37.837985 55.667708,37.839308 55.663127,37.839754 55.660837,37.839686 55.658546,37.838001 55.654643,37.836729 55.652668,37.835114 55.650789,37.829967 55.647124,37.824733 55.643846,37.813574 55.636482,37.803083 55.629521,37.798500 55.626461,37.793573 55.623401,37.781873 55.617532,37.770280 55.610989,37.758789 55.604703,37.753294 55.601817,37.747199 55.599077,37.736949 55.594763,37.721045 55.588129,37.709769 55.583882,37.696047 55.578764,37.683215 55.574212,37.679442 55.573088,37.675839 55.572449,37.668463 55.571805,37.649948 55.572767,37.632520 55.573749,37.619243 55.574579,37.600828 55.575721,37.597166 55.575970,37.593590 55.576365,37.586609 55.577616,37.571670 55.581167,37.557370 55.584580,37.534541 55.590027,37.527732 55.591660,37.519619 55.593647,37.511850 55.596022,37.509017 55.597463,37.506356 55.599001,37.501462 55.602806,37.493577 55.609639,37.485854 55.616283,37.482047 55.619638,37.477812 55.623066,37.466881 55.632617,37.458640 55.639496,37.450135 55.646802,37.441221 55.654269,37.432752 55.661729,37.425058 55.671459,37.418497 55.680179,37.416118 55.683968,37.414426 55.687999,37.413447 55.689894,37.411953 55.691887,37.407591 55.695338,37.397934 55.702470,37.388512 55.709739,37.385866 55.713393,37.383220 55.718354,37.379681 55.725427,37.374736 55.734992,37.369732 55.745294,37.369081 55.747607,37.368858 55.749919,37.368843 55.754980,37.369062 55.763022,37.369253 55.771476,37.369567 55.782358,37.369761 55.784150,37.370642 55.785846,37.372576 55.789623,37.378620 55.796031,37.383306 55.800779,37.385563 55.803516,37.387262 55.806252,37.388886 55.810472,37.390039 55.814788,37.393018 55.824216,37.395176 55.832257,37.395930 55.834542,37.395912 55.836827,37.394502 55.841299,37.392541 55.843911,37.391867 55.845823,37.391708 55.847047,37.392151 55.848271,37.392949 55.850767,37.397368 55.858756,37.400295 55.862596,37.404251 55.866388,37.407317 55.868456,37.410899 55.870524,37.417289 55.873212,37.429480 55.877061,37.442885 55.881200,37.446879 55.882075,37.451474 55.882661,37.459548 55.882868,37.463027 55.882987,37.467107 55.883348,37.474237 55.885130,37.482190 55.886934,37.489799 55.889221,37.502718 55.894963,37.518757 55.902046,37.523531 55.903993,37.527018 55.905217,37.530304 55.906037,37.533590 55.906664,37.536427 55.907074,37.543733 55.907942,37.559674 55.909488,37.575411 55.911105,37.580036 55.910948,37.583889 55.910598,37.587227 55.910128,37.590565 55.909416,37.606894 55.904848,37.614231 55.902795,37.621825 55.901080,37.633083 55.899007,37.642774 55.897460,37.652979 55.896443,37.658971 55.895901,37.664878 55.895528,37.681432 55.895081,37.691826 55.894749,37.697157 55.894272,37.704606 55.892580,37.711154 55.889947,37.723462 55.883656,37.736122 55.877348,37.744705 55.872951,37.757536 55.866327,37.773962 55.857880,37.780642 55.854454,37.792647 55.848268,37.808290 55.840215,37.816422 55.836007,37.829941 55.828936,37.832309 55.827224,37.834334 55.825367,37.837354 55.821411,37.838168 55.818971,37.838467 55.816483,37.839237 55.811506,37.839786 55.802607,37.840976 55.793965,37.843109 55.774567')

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