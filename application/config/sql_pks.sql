-- Временный пароль:

-- Salt: hbdgfwiu
-- Password: 1239811
-- Hash: hbdgfwiu02ac0da6641aa1cecb294da04c853852

-- 69RqOhuxbb8ddd55c04fa09872f2e98a3a86dfb2

----------------

-- Добавить товары в акцию
INSERT INTO `z_action_good` (`action_id`, `good_id`, `show`)  
    SELECT 191693, `z_good`.`id`,1 FROM `z_good` WHERE `section_id` IN (31869,31870,32743,32744,32745,32746,32747,32748,32749,32750,32751,32752,32753,32765,32766,59941,64446,64447,64448,64449,64450,64451,64452,64453,64454,64455,64456,64457,64458,64459,64460,64461,64462,64463,64464,64465,64466,64467,64468,64469,64470,64471,64472,64473,64474,64475,64476,64477,64478,70062,70063,70569,78101,78159)

----------------
/* Новые заказы за период */
SELECT count( DISTINCT `z_user`.`id`) FROM `z_user`
WHERE EXISTS 
    ( 
    SELECT `z_order`.`id` 
    FROM `z_order` 
    WHERE `z_order`.`user_id` = `z_user`.`id` 
    AND `z_order`.`created` >= '2013-08-26 00:00:00' 
    AND `z_order`.`created` < '2013-09-02 00:00:00'

    )
AND NOT EXISTS 
    (
    SELECT `z_order`.`id` 
    FROM `z_order` 
    WHERE `z_order`.`user_id` = `z_user`.`id` 
    AND `z_order`.`created` < '2013-08-26 00:00:00'
    )

--- Подсчет накопленного по акции {{{

 -- Заполняем
INSERT INTO `tempa_pk` 
SELECT `z_user`.`id` as `uid`, , `z_user`.`name`, `z_user`.`email`, SUM(`z_order_good`.`price` * `z_order_good`.`quantity`) as `sum`,0,0
FROM `z_user`,`z_order`,`z_order_good`
WHERE
	`z_user`.`id` = `z_order`.`user_id`
	AND `z_order`.`created` > '2013-09-06'
	AND `z_order`.`status` = 'F'
	AND `z_order`.`id` = `z_order_good`.`order_id`
	AND `z_order_good`.`good_id` IN ( 
		SELECT `z_action_good`.`good_id` FROM `z_action_good` WHERE `z_action_good`.`action_id` = '191395'
	)
GROUP BY `z_user`.`id`;

-- заполняем поле from_order
INSERT INTO `tempa_pk` (`uid`, `from_order`) SELECT `user_id`, `from_order` FROM `z_action_user` WHERE `action_id` = 191395 
ON DUPLICATE KEY UPDATE `from_order` = VALUES (`from_order`);

-- Заполняем конечную сумму у тех, кто уже брал подарок
INSERT INTO `tempa_pk` (`uid`, `final_sum`)  
    SELECT  `z_user`.`id` AS  `uid` , SUM(  `z_order_good`.`price` *  `z_order_good`.`quantity` ) AS  `sum` 
        FROM  `z_user` ,  `z_order` ,  `z_order_good` ,  `z_action_user` 
        WHERE  `z_user`.`id` =  `z_order`.`user_id` 
            AND  `z_order`.`created` >  '2013-09-06'
            AND  `z_order`.`status` =  'F'
            AND  `z_order`.`id` =  `z_order_good`.`order_id` 
            AND  `z_action_user`.`user_id` =  `z_user`.`id` 
            AND  `z_action_user`.`action_id` =  '191395'
            AND  `z_order`.`id` >  `z_action_user`.`from_order` 
            AND  `z_order_good`.`good_id` 
            IN (
                SELECT  `z_action_good`.`good_id` 
                FROM  `z_action_good` 
                WHERE  `z_action_good`.`action_id` =  '191395'
            )
        GROUP BY  `z_user`.`id`
ON DUPLICATE KEY UPDATE `final_sum` = VALUES (`final_sum`);

-- Заполняем окончательную сумму у тех, кто не брал еще подарок
UPDATE `tempa_pk` SET `final_sum` = `sum` WHERE `final_sum` = 0 AND `from_order` = 0;

-- Подсчитываем общую стоимость проданных товаров за период
SELECT  SUM(`z_order_good`.`price` * `z_order_good`.`quantity`) as `sum`
FROM `z_order`,`z_order_good`
WHERE
	
	`z_order`.`created` > '2013-09-06'
	AND `z_order`.`status` = 'F'
	AND `z_order`.`id` = `z_order_good`.`order_id`
	AND `z_order_good`.`good_id` IN ( 
		SELECT `z_action_good`.`good_id` FROM `z_action_good` WHERE `z_action_good`.`action_id` = '191395'
	)
-- }}}

-- Подсчитать сумму, накопленную по накопительной акции БЕЗ учета уже полученных подарков (т.е. вообще все)
SELECT SUM(`z_order_good`.`price` * `z_order_good`.`quantity`) FROM `z_order_good`, `z_action_good`, `z_order` 
	WHERE 
	`z_order_good`.`good_id` =  `z_action_good`.`good_id` 
   	AND `z_action_good`.`action_id` = '191395'
	AND `z_order_good`.`order_id` = `z_order`.`id`
	AND `z_order`.`user_id` = '68183'
	AND `z_order`.`status` = 'F'
        AND `z-order`.`created` >= '2013-09-06'

-- SMS по месяцам
SELECT COUNT(  `id` ) AS  `cnt` , MONTH( FROM_UNIXTIME(  `z_sms`.`created_ts` ) ) AS  `month` 
FROM  `z_sms` WHERE  `order_id` !=0 GROUP BY  `month` LIMIT 0 , 30

-- Подсчитать количество зарегистрированных пользователей, по годамыы
SELECT count(`id`) as `cnt`, YEAR(FROM_UNIXTIME(`z_user`.`created`)) as `yr` FROM `z_user`  GROUP BY `yr`

-- Обновление количества отзывов в товарах
-- Запрос "тяжелый" а лимит использовать некорректно, поэтому отбираем по кол-ву существующих отзывов
UPDATE `z_good` SET `z_good`.`review_qty` = (
	SELECT count(`z_good_review`.`id`) FROM `z_good_review` WHERE `z_good_review`.`good_id` = `z_good`.`id`
) WHERE `z_good`.`review_qty` <= 1 AND `z_good`.`review_qty` >0
-- И в группах
UPDATE `z_group` SET `z_group`.`review_qty` = (
	SELECT SUM(`z_good`.`review_qty`) FROM `z_good` WHERE `z_group`.`good_id` = `z_good`.`id`
) WHERE `z_group`.`review_qty` = 0



-- SMS рассылка
-- Этап 1: Формируем пул для отправки
UPDATE `anumber` SET `do_send` = 1 WHERE `last_buy` <= '2013-12-04' AND `last_buy` >= '2011-12-04'

-- Этап 2: Отправлять будем пачками по 10000, формируем пачку:
UPDATE `anumber` SET `sent` = 1 WHERE `sent` = 0 AND `do_send` = 1 LIMIT 10000

-- Этап 3: Вставляем данные в таблицу для робота - отправщика

INSERT INTO `z_sms` (`user_id`, `phone`,`text`,`created_ts`)  
    SELECT `anumber`.`user_id`, `anumber`.`phone`, 'Скидки на ВСЁ и ВСЕМ! Суперакция только до 12.12. www.mladenec.ru',  UNIX_TIMESTAMP() 
        FROM  `anumber`
        WHERE  `anumber`.`sent` =  1

-- Сколько неразослано
SELECT count(`z_sms`.`id`) FROM `z_sms` WHERE  `z_sms`.`sent_ts` =  0

-- Этап 4: Отправленные помечаем
UPDATE `anumber` SET `sent` = 2 WHERE `sent` = 1

-- Если еще есть неразосланные то: Гоу Ту этап 2

-- Выборка накоплений по накопительной
SELECT
     `z_user`.`id`,
     `z_user`.`email`,
     `z_user`.`name`,
     `z_action_user`.`sum` as `nakopleno`,
     `z_user`.`sum` as `vsego`,
FROM `z_user`, `z_action_user`,`z_order`
WHERE `action_id` = 191574
      AND `z_user`.`id` = `z_action_user`.`user_id`
      AND `z_user`.`id` = `z_order`.`user_id`
      AND `z_order`.`created` >= '2014-05-22'
      AND `z_order`.`status` = 'F'
      AND `z_action_user`.`sum` > 0
GROUP BY `z_user`.`id`
ORDER BY `z_action_user`.`sum` DESC

-- 2014-06-03 Обновление накоплений по накопительной акции
UPDATE `z_order_good` SET `action_id` = 191574 WHERE `good_id` IN (205401, 205128, 205329, 205329, 205129, 205759, 205402)

INSERT INTO `z_action_user` (`action_id`, `user_id`, `from_order`)
  SELECT 191574, `z_order`.`user_id`, MAX(  `z_order`.`id` ) as `from_order`
    FROM  `z_order` ,  `z_order_good`
    WHERE  `z_order`.`id` =  `z_order_good`.`order_id`
      AND  `z_order_good`.`action_id` =191574
      AND  `z_order_good`.`quantity` > 0
      AND  `z_order`.`status` =  'F'
      AND  `z_order`.`created` >  '2014-03-15'
      AND  `z_order`.`created` <  '2014-06-01'
    GROUP BY  `z_order`.`user_id`
ON DUPLICATE KEY UPDATE `from_order` = VALUES (`from_order`);

INSERT INTO `z_action_user` (`action_id`, `user_id`, `sum`)
  SELECT 191574, `z_order`.`user_id`, SUM(  `z_order`.`price` ) as `sum`
    FROM  `z_order` ,  `z_action_user`
    WHERE  `z_order`.`user_id` =  `z_action_user`.`user_id`
      AND  `z_action_user`.`action_id` =191574
      AND  `z_order`.`id` >  `z_action_user`.`from_order`
      AND  `z_order`.`status` =  'F'
      AND  `z_order`.`created` >  '2014-03-15'
      AND  `z_order`.`created` <  '2014-06-01'
    GROUP BY  `z_order`.`user_id`
ON DUPLICATE KEY UPDATE `sum` = VALUES (`sum`);

ALTER TABLE `z_section` ADD `buy_button_type` tinyint NOT NULL, DEFAULT '0', COMMENT='';

INSERT INTO `z_sms` (`phone`,`user_id`,`text`,`created_ts`)
    SELECT DISTINCT  `z_user`.`phone` ,  `z_user`.`id` as `user_id`, 'Скидки на подгузники Goon, Moony, Huggies до 15%! www.mladenec.ru',1402303083
    FROM  `z_user` ,  `z_order` 
    WHERE  `z_user`.`phone` LIKE  '+79%'
        AND  `z_user`.`sub` =1
        AND  `z_user`.`id` =  `z_order`.`user_id` 
        AND  `z_order`.`created` >  '2013-12-01'
        AND  `z_order`.`delivery_type` =2
        AND  `z_user`.`id` > 94000


ALTER TABLE `z_config` ADD `mail_feedback` varchar(256) COLLATE 'utf8_general_ci' NOT NULL AFTER `mail_fransh`, COMMENT=''

-- Подарки пользователя
SELECT `z_order_good`.*, `z_order`.`created`, `z_order`.`changed`,`z_order`.`status_time`,
`z_order`.`status`, `z_order`.`price`, `z_good`.`group_name`,`z_good`.`name` FROM `z_order`, `z_order_good`,`z_good`
WHERE `z_order`.`user_id` = 100611
AND `z_order`.`created` > '2014-03-15'
AND `z_order`.`id` = `z_order_good`.`order_id`
AND `z_order_good`.`price` = 0.00
AND `z_good`.`id` = `z_order_good`.`good_id`
ORDER BY `z_order`.`id` DESC
LIMIT 50

-- Отвезенные подарки за период
SELECT `z_order_good`.*, `z_order`.`user_id`, `z_order`.`created`, `z_order`.`changed`,`z_order`.`status_time`,
`z_order`.`status`, `z_order`.`price`, `z_good`.`group_name`,`z_good`.`name` FROM `z_order`, `z_order_good`,`z_good`
WHERE `z_order`.`created` > '2014-05-22 11:00:00'
AND `z_order`.`created` < '2014-05-22 12:00:00'
AND `z_order`.`id` = `z_order_good`.`order_id`
AND `z_order_good`.`price` = 0.00
AND `z_order_good`.`action_id` = 191574
AND `z_order_good`.`quantity` > 0
AND `z_order`.`status` = 'F'
AND `z_good`.`id` = `z_order_good`.`good_id` 
ORDER BY `z_order`.`created` ASC

-- Адреса регистраций с 19 июня
SELECT `z_user`.`id`, `z_user`.`phone`, `z_user`.`phone2`, `z_user`.`email`, `z_user_address`.* 
FROM `z_user`,`z_user_address` 
WHERE `z_user`.`created` >= 1403136000
AND `z_user_address`.`user_id` = `z_user`.`id`

-- Задания для демона
DROP TABLE IF EXISTS `z_daemon_quest`;
CREATE TABLE `z_daemon_quest` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `action` varchar(64) NOT NULL,
  `params` varchar(2048) NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` tinyint(4) NOT NULL DEFAULT '0',
  `done_ts` int(11) NOT NULL,
  `delay` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `z_action` ADD `sync_1c` tinyint NOT NULL DEFAULT '0' AFTER `link_comment`, COMMENT='';

-- Набор товаров
ALTER TABLE `z_action` DROP `subtype`, ADD `good_set_id` int unsigned NOT NULL AFTER `to`, COMMENT='';

-- Флаг зомби
ALTER TABLE `z_good` ADD `zombie` tinyint(1) unsigned NOT NULL DEFAULT '0', COMMENT='';

-- Статусы СМС
ALTER TABLE `z_sms` ADD `status` tinyint unsigned NOT NULL DEFAULT '0' AFTER `sent_ts`, COMMENT='';
ALTER TABLE `z_sms` ADD `priority` tinyint NOT NULL DEFAULT '0' AFTER `status`, COMMENT='';
-- ALTER TABLE `z_sms` CHANGE `created` `created` timestamp NOT NULL AFTER `text`, COMMENT=''; FIX NEEDED НЕПОНЯТНЫЙ ЗАПРОС, нет поля created в таблице!

-- Набор товаров
DROP TABLE IF EXISTS `z_set`;
CREATE TABLE `z_set` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` tinytext NOT NULL,
  `autoapply` tinyint(4) NOT NULL DEFAULT '0',
  `lock` int(11) NOT NULL DEFAULT '0',
  `cnt` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `z_set` (`id`, `name`, `autoapply`, `lock`, `cnt`) VALUES
(26,	'Тестовый 1',	0,	0,	0),
(27,	'Тестовый 2',	0,	0,	0),
(28,	'Тестовый 3',	0,	0,	0);

-- Товары в наборе
CREATE TABLE `z_set_good` (
  `set_id` int unsigned NOT NULL,
  `good_id` int unsigned NOT NULL
) COMMENT='' ENGINE='InnoDB' COLLATE 'utf8_general_ci';

-- PRIMARY Индекс, заодно и уникальный, т.к. NULL быть не должно!
ALTER TABLE `z_set_good` ADD PRIMARY KEY `set_id_good_id` (`set_id`, `good_id`);

CREATE TABLE `z_set_rule` (
  `set_id` int unsigned NOT NULL,
  `type` tinyint NOT NULL,
  `val` int unsigned NOT NULL,
) COMMENT='' ENGINE='InnoDB' COLLATE 'utf8_general_ci';

ALTER TABLE `z_set_rule` ADD PRIMARY KEY `set_id_type_val` (`set_id`, `type`, `val`);

ALTER TABLE  `z_action` CHANGE  `good_set_id`  `set_id` INT( 10 ) UNSIGNED NOT NULL;
UPDATE `z_action` SET `set_id` = `id` WHERE `id` > '191500'

INSERT INTO `z_set` (`id`, `name`, `autoapply`)
SELECT `id`,`name`,0
FROM `z_action`
WHERE `id` > '191500' OR `allowed` = '1'

INSERT IGNORE INTO `z_set_rule` (`set_id`, `type`, `val`)
SELECT `action_id`, 9, `good_id`
FROM `z_action_good`
WHERE `action_id` > '191500'

INSERT IGNORE INTO `z_set_good` (`set_id`, `good_id`)
SELECT `action_id`,`good_id`
FROM `z_action_good`
WHERE `action_id` > '191500'

ALTER TABLE `z_set` ADD `cnt_shown` int(11) NOT NULL DEFAULT '0', COMMENT='';

CREATE TABLE `new_orders` (
  `id` int unsigned NOT NULL,
  `user_id` int unsigned NOT NULL,
  `created` timestamp NOT NULL,
  `price` decimal(12,2) NOT NULL
) COMMENT='' ENGINE='InnoDB' COLLATE 'utf8_general_ci';

INSERT INTO `new_orders` (`id`,`user_id`,`created`,`price`)
    SELECT MIN(`z_order`.`id`), `z_order`.`user_id`, `z_order`.`created`, `z_order`.`price` 
        FROM `z_order`,`z_user` 
        WHERE `z_user`.`id` = `z_order`.`user_id` 
            AND `z_user`.`created` > 1388534400 
            AND `z_order`.`status` = 'F'
        GROUP BY `z_order`.`user_id`;

SELECT DATE_FORMAT(`new_orders`.`created`,'%Y-%m') as `dt`,`parent_section`.`name`,`z_section`.`name`, SUM(`z_order_good`.`price`) as `pr`
FROM `new_orders`, `z_order_good`, `z_good`,`z_section`,`z_section` as `parent_section`
WHERE `z_good`.`id` = `z_order_good`.`good_id`
    AND `new_orders`.`id` = `z_order_good`.`order_id`
    AND `z_section`.`id` = `z_good`.`section_id`
    AND `z_section`.`parent_id` = `parent_section`.`id`
GROUP BY `dt`,`z_good`.`section_id`
ORDER BY `dt`,`z_section`.`parent_id`

--- Ввод в строй наборов

ALTER TABLE `z_action` ADD `vitrina_active_id` int unsigned NOT NULL DEFAULT '0' AFTER `vitrina_active`, COMMENT='';

-- 09 09 2014 - Events
CREATE TABLE `z_event` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `type` int(11) NOT NULL,
  `item_id` int(10) unsigned NOT NULL,
  `message` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 11 09 2014 - Выгрузка акций из 1C
ALTER TABLE `z_action`
DROP `set_id`,
DROP `target_set_id`,
DROP `sync_1c`,
DROP `condition_type`,
COMMENT='';

-- 13 09 2014 - Акции АБ
CREATE TABLE `z_action_good_b` (
  `action_id` int(10) unsigned NOT NULL,
  `good_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`action_id`,`good_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Отображать товары в акциях - перенос флага в акцию
ALTER TABLE `z_action` ADD `show_goods` tinyint(1) NOT NULL DEFAULT '0' AFTER `show`, COMMENT='';

-- Переносим значения 
UPDATE `z_action`,`z_action_good` SET `show_goods` = 1
WHERE `z_action`.`id` = `z_action_good`.`action_id` AND `z_action_good`.`show` = 1 

-- 30 09 2014 #739  !!!!!!!!! НА БОЕВОМ НЕ ВЫПОЛНЯТЬ !!!!!!!!!!!!!
DELETE FROM `z_good_good` WHERE NOT EXISTS (SELECT `id` FROM `z_good` WHERE `z_good`.`id` = `z_good_good`.`min_good_id`)

--- 13.10.2014 Группы акций:
INSERT INTO `z_action_group` (`id`,`active`,`name`,`banner`,`preview`,`text`,`show`,`main`,`show_wow`,`show_actions`,`vitrina_show`,`order`,
`show_gifticon`,  `cart_icon`, `cart_icon_text`,`show_goods`, `incoming_link`,`link_comment`,`sync_1c`,`visible_goods`);
SELECT `id`,`active`,`name`,`banner`,`preview`,`text`,`show`,`main`,`show_wow`,`show_actions`,`vitrina_show`,`order`,
`show_gifticon`,  `cart_icon`, `cart_icon_text`,`show_goods`, `incoming_link`,`link_comment`,`sync_1c`,`visible_goods` FROM `z_action` 
WHERE `allowed` = 1 AND `parent_id` = 0;

ALTER TABLE `z_action` ADD `group_id` int(11) unsigned NOT NULL AFTER `id`, COMMENT='';

UPDATE `z_action` SET `group_id` = `id` WHERE `parent_id` = 0;
UPDATE `z_action` SET `group_id` = `parent_id`  WHERE `parent_id` != 0;

-- Action presents recount
SELECT `z_order_good`.`order_id`, count(`z_order_good`.`good_id`) as `cnt` FROM `z_order_good`
JOIN `z_action_good` ON `z_action_good`.`good_id` = `z_order_good`.`good_id`
 WHERE `z_action_good`.`action_id` = 191984
AND `z_order_good`.`order_id` > 501639
GROUP BY `z_order_good`.`order_id`
HAVING `cnt` >= 2
ORDER BY  `z_order_good`.`order_id` ASC

-- 03.12.2014 - уведомления о сломанных СМС
ALTER TABLE `z_config` ADD `mail_sms_warning` varchar(255) COLLATE 'utf8_general_ci' NOT NULL AFTER `mail_error`, COMMENT='';


-- Таблица цен
ALTER TABLE  `z_price` 
ADD  `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP , 
ADD  `doc_id` INT NOT NULL DEFAULT  '0'

ALTER TABLE `z_price`
ADD UNIQUE `good_id_status_id_doc_id` (`good_id`, `status_id`, `doc_id`),
DROP INDEX `good_id`;

-- подбор теговых
SELECT `z_section`.`id`,`z_section`.`name`,`z_section`.`translit`,`z_brand`.`id`,`z_brand`.`name`,`z_filter`.`id`,`z_filter_value`.`id`, `z_filter_value`.`name`, 
CONCAT (`z_section`.`name`,'-',`z_brand`.`name`,'-',`z_filter_value`.`name`) as `con`,
COUNT(`good_id`) as `count`
FROM `z_section`, `z_brand`, `z_filter_value`, `z_good`, `z_good_filter`,`z_filter`
WHERE 
`z_section`.`id` = `z_good`.`section_id` 
AND `z_section`.`vitrina` = 'mladenec'
AND `z_brand`.`id` = `z_good`.`brand_id` 
AND `z_filter_value`.`id` = `z_good_filter`.`value_id`
AND `z_filter_value`.`filter_id` = `z_filter`.`id`
AND (
    `z_filter`.`name` LIKE 'По виду'
    OR `z_filter`.`name` LIKE 'По Виду'
    OR `z_filter`.`name` LIKE 'Большой тип'
)
AND `z_good`.`id` = `z_good_filter`.`good_id`
AND `z_good`.`show` = 1
AND `z_good`.`qty` != 0
GROUP BY `con`

-- Выгрузка теговых
SELECT ' ' as `old_url`, `code`, `title`, `description`,`keywords`,`text` FROM `z_tag` WHERE `code` LIKE 'catalog/%' ORDER BY `z_tag`.`id`  DESC

ALTER TABLE `z_good`
ADD `price_lk` decimal(18,2) NULL DEFAULT '0.00' AFTER `price`,
ADD `price_sale` decimal(11,2) NULL DEFAULT '0.00' AFTER `price_buy`,
ADD `price_ts` int NULL DEFAULT '0' AFTER `price_sale`,
ADD `price_sale_from` int NULL DEFAULT '0' AFTER `price_ts`,
ADD `price_sale_to` int NULL DEFAULT '0' AFTER `price_sale_from`,
ADD `price_doc_id` int(11) NULL DEFAULT '0' AFTER `price_sale_to`,
ADD `price_sale_doc_id` int(11) NULL DEFAULT '0' AFTER `price_doc_id`,
COMMENT='';


ALTER TABLE `z_good`
CHANGE `id1c` `id1c` int(10) unsigned NULL AFTER `code1c`,
COMMENT='';

UPDATE `z_good` SET `id1c` = NULL WHERE `id1c` = 0;

ALTER TABLE `z_good` 
ADD UNIQUE `id1c` (`id1c`),
DROP INDEX `id1c`;