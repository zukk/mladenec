-- phpMyAdmin SQL Dump
-- version 4.2.5
-- http://www.phpmyadmin.net
--
-- Хост: localhost
-- Время создания: Авг 27 2014 г., 10:09
-- Версия сервера: 5.5.35-0+wheezy1
-- Версия PHP: 5.4.17-1~dotdeb.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- База данных: `zukk`
--

-- --------------------------------------------------------

--
-- Структура таблицы `search_words`
--

DROP TABLE IF EXISTS `search_words`;
CREATE TABLE IF NOT EXISTS `search_words` (
`id` int(10) NOT NULL,
  `name` varchar(255) NOT NULL,
  `status` int(1) NOT NULL DEFAULT '0',
  `is_error` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=30 ;

-- --------------------------------------------------------

--
-- Структура таблицы `search_words_brands`
--

DROP TABLE IF EXISTS `search_words_brands`;
CREATE TABLE IF NOT EXISTS `search_words_brands` (
  `id` int(10) NOT NULL,
  `brand_id` int(10) NOT NULL,
  `word_id` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `search_words_stat`
--

DROP TABLE IF EXISTS `search_words_stat`;
CREATE TABLE IF NOT EXISTS `search_words_stat` (
`id` int(10) NOT NULL,
  `word_id` int(10) NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_error` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `search_words`
--
ALTER TABLE `search_words`
 ADD PRIMARY KEY (`id`), ADD KEY `status` (`status`), ADD KEY `is_error` (`is_error`);

--
-- Indexes for table `search_words_stat`
--
ALTER TABLE `search_words_stat`
 ADD PRIMARY KEY (`id`), ADD KEY `word_id` (`word_id`,`time`), ADD KEY `is_error` (`is_error`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `search_words`
--
ALTER TABLE `search_words`
MODIFY `id` int(10) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;
--
-- AUTO_INCREMENT for table `search_words_stat`
--
ALTER TABLE `search_words_stat`
MODIFY `id` int(10) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;




-----------------------------------------------
-- Удалить primary у z_good_img, затем
ALTER TABLE z_good_img DROP PRIMARY KEY;
ALTER TABLE `z_good_img` ADD `id` INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY ;
ALTER TABLE `z_good_img` ADD INDEX(`file_id`);
ALTER TABLE `z_good_img` ADD INDEX(`good_id`);








-----------------------------------------------
--- Телефоны (705 ЗАДАЧА). Это уже есть на боевом и на тестовом!

DROP TABLE z_config;
CREATE TABLE IF NOT EXISTS `z_config` (
  `id` int(11) NOT NULL DEFAULT '1',
  `phone` varchar(255) NOT NULL,
  `menu` text NOT NULL,
  `logo_id` int(10) unsigned NOT NULL DEFAULT '0',
  `seo_index` text NOT NULL,
  `mail_return` varchar(255) NOT NULL,
  `mail_comment` varchar(255) NOT NULL,
  `mail_order` varchar(255) NOT NULL,
  `mail_review` varchar(255) NOT NULL,
  `mail_partner` varchar(255) NOT NULL,
  `mail_good` varchar(2048) NOT NULL,
  `mail_action` varchar(255) NOT NULL,
  `mail_present` varchar(255) NOT NULL,
  `mail_presents` varchar(255) NOT NULL,
  `mail_error` varchar(255) NOT NULL,
  `mail_payment` text NOT NULL,
  `sms_present` varchar(255) NOT NULL,
  `mail_fransh` text NOT NULL,
  `mail_feedback` varchar(255) NOT NULL,
  `accept_cards` enum('','payture','rbs') NOT NULL DEFAULT '',
  `actions_header` varchar(255) NOT NULL,
  `actions_subheader` varchar(512) NOT NULL,
  `mail_contest` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `z_config`
--

INSERT INTO `z_config` (`id`, `phone`, `menu`, `logo_id`, `seo_index`, `mail_return`, `mail_comment`, `mail_order`, `mail_review`, `mail_partner`, `mail_good`, `mail_action`, `mail_present`, `mail_presents`, `mail_error`, `mail_payment`, `sms_present`, `mail_fransh`, `mail_feedback`, `accept_cards`, `actions_header`, `actions_subheader`, `mail_contest`) VALUES
(1, '<div class="calltracking">8 (800) 555-699-4</div><br /><div class="calltracking">8 (495) 662-999-4</div>', '<p><a href="/about/">О магазине</a>⋅\n<a href="/delivery/">Доставка и оплата</a>⋅\n<a href="/contacts/">Контакты</a></p>', 2909591, '<p>Подарите малышам время, а себе — возможность радоваться качественной покупкой!</p><p>Чтобы малыш был всегда веселым и жизнерадостным, ему нужен тщательный уход от заботливых родителей. В наше время, когда многие имеют свободный доступ к Сети Интернет, приобретать вещи и любые товары для ребенка стало менее проблематично — нужно всего лишь стать покупателем <nobr>интернет-магазина</nobr>. А сэкономленное время на поход в реальный магазин можно с радостью подарить своему бесценному сокровищу — подрастающему карапузу!</p><p>Детский <nobr>интернет-магазин</nobr> Младенец.RU — это широкий каталог с товарами разного предназначения. Каждую вещь вы можете детально рассмотреть через экран монитора и уже через короткое время получить заветную покупку, доставленную прямо на дом (Москва, Мытищи, любой город России). Продажа проводится по доступным ценам, а сам заказ выполняется в довольно короткие сроки.</p><p><nobr>Детские-интернет-магазины</nobr> — это выгода для родителей, у которых растут маленькие шалуны. Подрастающие крохи, которые вынуждены с мамой посещать детский магазин, будут безумно рады от новинки — возможности помочь ей выбрать товары онлайн! При этом они ничего не смогут испортить или разбить, как это случается в обычных супермаркетах.</p><p>Качественный товар и доступные цены — особенность нашего <nobr>интернет-магазина</nobr></p><p>Виртуальные полки нашего магазина постоянно пополняются новинками в области детских товаров. Это новые подгузники, игрушки, вещи для сна и прогулки, питание для малышей и другая недавно выпущенная в свет продукция из разных уголков мира, которую вы свободно можете купить через Инет. Также мы поставляем на рынок России различные японские товары, которые сейчас так популярны у наших потребителей. Мы заботимся о том, чтобы каждая покупка оставалась для вас недорогой и приятной, поэтому ставим минимальные цены на подобный товар, который поступает в наши <nobr>интернет-магазины</nobr> всегда в достаточном количестве.</p><p>Коллекция миниатюрной одежды порадует родителей, у которых родился недоношенный ребенок. Также они смогут подобрать своему малышу его первые подгузники, принадлежность для купания и качественную колыбельку. Нестандартный товар, такой как подгузники для кошек и собак, также присутствует в нашем широком ассортименте.</p><p>Каждый товар, представленный в online каталоге Младенец.RU, имеет все сертификаты качества и является полностью безопасным для ребенка. Посещая наш сайт, где можно найти продукцию из разных ценовых сегментов, вы будете удивлены от отличного качества обслуживания. А также от того, какой дешевый у нас товар!</p>', 'executive@mladenec.ru, a.sergeev@mladenec.ru, v.nikiforova@mladenec.ru', 'return@mladenec-shop.ru, executive@mladenec.ru, a.sergeev@mladenec.ru, v.nikiforova@mladenec.ru', 'zakaz@mladenec.ru', 'o.melnikova@jpbaby.ru, a.sergeev@mladenec.ru, v.nikiforova@mladenec.ru', 'o.melnikova@jpbaby.ru,o.osipova@mladenec.ru', 'y.shabolina@mladenec.ru, n.burdina@mladenec.ru, e.muradyanc@mladenec.ru, k.puchkov@mladenec.ru, m.semenova@mladenec.ru, o.melnikova@jpbaby.ru, executive@mladenec.ru, magazin1@mladenec.ru, magazin2@mladenec.ru, magazin3@mladenec.ru, magazin4@mladenec.ru, magazin5@mladenec.ru, magazin6@mladenec.ru, mrabral@gmail.com, v.nikiforova@mladenec.ru', 'e.muradyanc@mladenec.ru, spirin1234@mail.ru, puchkovk@gmail.com, a.demenkov@mladenec.ru, mrabral@gmail.com, a.sergeev@mladenec.ru,  executive@mladenec.ru, v.nikiforova@mladenec.ru, a.karcev@mladenec.ru', 'e.muradyanc@mladenec.ru, k.kuzyayev@mladenec.ru, e.savkina@mladenec.ru,  spirin1234@mail.ru, a.sergeev@mladenec.ru, v.nikiforova@mladenec.ru', '', 'zukker@gmail.com, executive@mladenec.ru, elena-1104@yandex.ru, k.puchkov@mladenec.ru, n.burdina@mladenec.ru, unknown_box@mail.ru, den_zorn@mail.ru', 'zukker@gmail.com, e.muradyanc@mladenec.ru, n.burdina@mladenec.ru', '+79100197934, 79261621607', 'm.zukk@ya.ru, magazin1@mladenec.ru, magazin2@mladenec.ru, magazin3@mladenec.ru, magazin4@mladenec.ru, magazin5@mladenec.ru, n.lisichkina@mladenec.ru, dunaevskaya.du@mail.ru, mladenec.po@mail.ru, mlad.pp@mail.ru, k-smo@yandex.ru, mlad.pp@mail.ru, kmladenec@mail.ru, mladenec.fdr@yandex.ru, sitdikov.rinat@mail.ru, maxitana@mail.ru, kss-80@mail.ru, Moshkova-0805@yandex.ru, mladenecleninsky@gmail.com, bezrukovs@gmail.com, mladenec@list.ru, olmamedova@yandex.ru, Troninaania.a@yandex.ru     ', 'request@mladenec.ru, e.muradyanc@mladenec.ru', 'rbs', 'WOW-акции', '<p>Скидки и подарки</p>', '');

DELETE FROM z_menu WHERE id = 8;
INSERT INTO `z_menu` (`id`, `link`, `parent_id`, `name`, `description`, `text`, `menu`, `show`, `sort`) VALUES
(8, 'contacts', 1, 'Контакты', '', '<h1><span class="Apple-style-span" style="color: rgb(255, 102, 0); font-size: 24px;">Контакты </span></h1>\n<h4 style="font-weight: bold;"><strong><span style="font-size: 14pt; font-family: ''Times New Roman'', serif;"></span></strong></h4>\n<h4>ООО «ТД Младенец.РУ»</h4>\n<h4></h4>\n<h4>ОГРН: 1125018000667</h4>\n<p>\n	Оформить заказ на сайте Младенец.РУ Вы можете круглосуточно.\n</p>\n<p>\n	<br>\n	<b style="font-weight: bold;">Прием заказов по многоканальным телефонам</b><strong> <div class="calltracking">8-800-555-699-4</div> и <div class="calltracking">(495) 662-999-4</div>:</strong>\n</p>\n<p>\n	<strong> С понедельника по пятницу с 9.00 до 22.00 </strong><br>\n	<strong>В субботу с 9.00 до 21.00<br>\n	В воскресенье с 10.00 до 21.00\n	</strong><br>\n	а также по телефонам: <div class="calltracking">(495) 236-72-92</div> или  \n	<a href="Skype:mladenecshop"><img src="http://www.mladenec-shop.ru/upload/6/4/3/9/e8mqoos6.png" style="width: 32px; height: 32px;"></a><a href="Skype:mladenecshop">mladenecshop</a>\n</p>\n<p>\n	<a class="toggler abbr" rel="return_form"><img src="/upload/mediafiles/0/7/a/7/1959.png"></a>\n</p>\n<p class="form hide" id="feedback">\n	форма обратной связи\n</p>\n<p>\n	<b style="font-weight: bold;"> Служба доставки работает:</b><br>\n	 С понедельника по воскресенье с 14.00 до 23.00.\n</p>\n<p>\n	Адрес для почтовых отправлений: 107139, Москва, Орликов переулок, д.ЗБ, оф.301\n</p>\n<p>\n	<a name="offline"></a>\n</p>\n<h2><span style="color: rgb(241, 101, 34); font-weight: bold;">Сетевые магазины</span></h2>\n<p>\n	<small>\n	</small>\n</p>\n<iframe scrolling="no" marginheight="0" marginwidth="0" src="https://maps.google.ru/maps/ms?msa=0&msid=204981909577177882090.0004c2e6a32ff94cc21fc&hl=ru&ie=UTF8&t=m&source=embed&ll=55.748758,37.63916&spn=1.082106,2.334595&z=8&output=embed" frameborder="0" height="450" width="710">\n</iframe>\n<small>Просмотреть <a href="https://maps.google.ru/maps/ms?msa=0&msid=204981909577177882090.0004c2e6a32ff94cc21fc&hl=ru&ie=UTF8&t=m&source=embed&ll=55.748758,37.63916&spn=1.082106,2.334595&z=8" style="color: rgb(0, 0, 255); text-align: left;">Наши магазины</a> на карте большего размера</small>\n<br>\n<br>\n<table class="table1">\n<thead>\n<tr>\n	<th scope="col" abbr="Starter">\n		№\n	</th>\n	<th scope="col">\n		Адрес\n	</th>\n	<th scope="col">\n		Телефон\n	</th>\n	<th scope="col">\n		Время работы\n	</th>\n</tr>\n</thead>\n<tfoot>\n</tfoot>\n<tbody>\n<tr>\n	<td width="43">\n		1.\n	</td>\n	<td style="text-align: left;" align="left" width="294">\n		<a href="http://www.mladenec-shop.ru/contacts/dybenko.php" title="подробнее" target="_blank"><u>г. Москва, ул. Дыбенко, д.36, корп.1</u></a>\n	</td>\n	<td width="164">\n		Тел.: <div class="calltracking">+7 (499) 975-89-13</div>\n	</td>\n	<td width="155">\n		Пн-Вс с 10:00 до 21:00\n	</td>\n</tr>\n<tr>\n</tr>\n<tr>\n	<td>\n		2.\n	</td>\n	<td style="text-align: left;" align="left">\n		<a href="http://www.mladenec-shop.ru/contacts/mag16.php"><u>г. Ивантеевка, ул. Трудовая, д.22, М.О. <u></u></u></a><u><u></u></u>\n	</td>\n	<td>\n		Тел.: <div class="calltracking">+7 (925) 380-35-83</div>\n	</td>\n	<td>\n		Пн-Вс с 10:00 до 20:30\n	</td>\n</tr>\n<tr>\n	<td>\n		3.\n	</td>\n	<td style="text-align: left;" align="left">\n		<a href="http://www.mladenec-shop.ru/contacts/krasnogorsk.php" target="_blank"><u>г. Красногорск, Красногорский бульвар, дом 5, М.О. <u></u></u></a><u><u></u></u>\n	</td>\n	<td style="text-align: left;">\n		  Тел.: <div class="calltracking">+7 (499) 714-37-57</div>\n	</td>\n	<td>\n		Пн-Вс с 10:00 до 20:30\n	</td>\n</tr>\n<tr>\n	<td>\n		4.\n	</td>\n	<td style="text-align: left;" align="left">\n		<a href="http://www.mladenec-shop.ru/contacts/mag6.php" target="_blank"><u>г. Мытищи, ул. Шараповская, д.1 корп.2, М.О.</u></a><u><a href="http://www.mladenec-shop.ru/contacts/mag6.php"></a><u><u></u></u></u>\n	</td>\n	<td>\n		Тел.: <div class="calltracking">+7 (910) 019-79-58</div>\n	</td>\n	<td>\n		Пн-Вс с 10:00 до 20:30\n	</td>\n</tr>\n<tr>\n	<td>\n		5.\n	</td>\n	<td style="text-align: left;" align="left">\n		<a href="http://www.mladenec-shop.ru/contacts/mag7.php" target="_blank"><u>г. Подольск, ул. Мраморная, д.10, М.О. </u></a><u><a href="http://www.mladenec-shop.ru/contacts/mag7.php"></a><u><u></u></u></u>\n	</td>\n	<td>\n		Тел.: <div class="calltracking">+7 (4967) 55-58-58</div>\n	</td>\n	<td>\n		Пн-Вс с 9:00 до 20:00\n	</td>\n</tr>\n<tr>\n	<td>\n		6.\n	</td>\n	<td style="text-align: left;" align="left">\n		<a href="http://www.mladenec-shop.ru/contacts/mag8.php" target="_blank"><u>Ленинский район, М.О.,п.Развилка, д.45</u> </a>\n	</td>\n	<td>\n		Тел.: <div class="calltracking">+7 (498) 708-76-29</div>\n	</td>\n	<td>\n		Пн-Вс с 10:00 до 20:30\n	</td>\n</tr>\n<tr>\n	<td>\n		7.\n	</td>\n	<td style="text-align: left;" align="left">\n		<u><a href="http://www.mladenec-shop.ru/contacts/leninskiy.php" target="_blank">г. Химки, Ленинский проспект, д.1, к.1 М.О. </a><a href="http://www.mladenec-shop.ru/contacts/leninskiy.php"></a><u><u></u></u></u>\n	</td>\n	<td>\n		Тел.: <div class="calltracking">+7 (498) 655-90-72</div>\n	</td>\n	<td>\n		Пн-Вс с 10:00 до 20:30\n	</td>\n</tr>\n<tr>\n	<td>\n		8.\n	</td>\n	<td style="text-align: left;" align="left">\n		<u><a href="http://www.mladenec-shop.ru/contacts/mag15.php" target="_blank">г.Щелково ст. Чкаловская, ул. Ленина, д.1 М.О.</a></u><u><br>\n		</u>\n	</td>\n	<td>\n		Тел.: <div class="calltracking">+7 (916) 369-22-21</div>\n	</td>\n	<td>\n		Пн-Вс с 10:00 до 20:30\n	</td>\n</tr>\n<tr>\n	<td>\n		9.\n	</td>\n	<td style="text-align: left;" align="left">\n		<u><a href="http://www.mladenec-shop.ru/contacts/mag10.php" target="_blank">г. Юбилейный, ул. Лесная, д.14,М.О.</a></u><u><br>\n		</u>\n	</td>\n	<td>\n		Тел.: <div class="calltracking">+7 (910) 019-79-60</div>\n		<br>\n		Тел.: <div class="calltracking">+7 (495) 515-93-80</div>\n		<br>\n	</td>\n	<td>\n		Пн-Вс с 10:00 до 20:30\n	</td>\n</tr>\n</tbody>\n</table>', 1, 1, 8);


-----------------------------------------------
----- Отложенная оплата

ALTER TABLE `z_order` CHANGE `status` `status` ENUM('D','F','N','S','X','C','DC') CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT 'N';


-----------------------------------------------
----- Статистика
--
-- Структура таблицы `z_stat`
--

CREATE TABLE IF NOT EXISTS `z_stat` (
`id` int(10) NOT NULL,
  `sdate` date NOT NULL,
  `new` int(10) NOT NULL,
  `new_card` int(10) NOT NULL,
  `sum` decimal(18,2) NOT NULL,
  `sum_card` decimal(18,2) NOT NULL,
  `complete` int(10) NOT NULL,
  `complete_card` int(10) NOT NULL,
  `complete_sum` decimal(18,2) NOT NULL,
  `complete_sum_card` decimal(18,2) NOT NULL,
  `cancel` int(10) NOT NULL,
  `cancel_card` int(10) NOT NULL,
  `cancel_sum` decimal(18,2) NOT NULL,
  `cancel_sum_card` decimal(18,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `z_stat`
--
ALTER TABLE `z_stat`
 ADD PRIMARY KEY (`id`), ADD KEY `sdate` (`sdate`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `z_stat`
--
ALTER TABLE `z_stat`
MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;


CREATE TABLE IF NOT EXISTS `z_stat_monthly` (
`id` int(10) NOT NULL,
  `sdate` date NOT NULL,
  `new` int(10) NOT NULL,
  `new_card` int(10) NOT NULL,
  `sum` decimal(18,2) NOT NULL,
  `sum_card` decimal(18,2) NOT NULL,
  `complete` int(10) NOT NULL,
  `complete_card` int(10) NOT NULL,
  `complete_sum` decimal(18,2) NOT NULL,
  `complete_sum_card` decimal(18,2) NOT NULL,
  `cancel` int(10) NOT NULL,
  `cancel_card` int(10) NOT NULL,
  `cancel_sum` decimal(18,2) NOT NULL,
  `cancel_sum_card` decimal(18,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `z_stat_monthly`
--
ALTER TABLE `z_stat_monthly`
 ADD PRIMARY KEY (`id`), ADD KEY `sdate` (`sdate`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `z_stat_monthly`
--
ALTER TABLE `z_stat_monthly`
MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;



-- Озон

--
-- Структура таблицы `z_ozon`
--

CREATE TABLE IF NOT EXISTS `z_ozon` (
`id` int(10) NOT NULL,
  `type` int(3) NOT NULL,
  `id_item` int(10) NOT NULL,
  `scount` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `z_ozon`
--
ALTER TABLE `z_ozon`
 ADD PRIMARY KEY (`id`), ADD KEY `type` (`type`,`id_item`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `z_ozon`
--
ALTER TABLE `z_ozon`
MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;


-- yml

ALTER TABLE `z_section` ADD `export_type` INT(2) NOT NULL ;

-- Обязательно поставить тип выгрузки у одежды
UPDATE `z_section` SET `export_type` = '1' WHERE `z_section`.`id` = 29690;


---
--- Карточка нестле
ALTER TABLE `z_good_prop` ADD `view_type` INT(1) NOT NULL DEFAULT '1' ;
ALTER TABLE `z_good_prop` ADD `img380` INT(12) NOT NULL AFTER `img500`;


ALTER TABLE `z_good_prop` ADD `img380x560` INT(10) NOT NULL , ADD `img173x255` INT(10) NOT NULL ;
ALTER TABLE `z_good_img` CHANGE `size` `size` VARCHAR(255) NOT NULL;

---------

ALTER TABLE `z_good_prop` CHANGE `ozon_item_type_id` `to_ozon` INT(10) UNSIGNED NOT NULL DEFAULT '1';





--
-- Структура таблицы `security_errors`
--

CREATE TABLE IF NOT EXISTS `z_security_errors` (
`id` int(12) NOT NULL,
  `document_uri` varchar(255) NOT NULL,
  `referrer` varchar(255) NOT NULL,
  `violated_directive` varchar(255) NOT NULL,
  `original_policy` varchar(255) NOT NULL,
  `blocked_uri` varchar(255) NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `security_errors`
--
ALTER TABLE `z_security_errors`
 ADD PRIMARY KEY (`id`), ADD KEY `time` (`time`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `security_errors`
--
ALTER TABLE `z_security_errors`
MODIFY `id` int(12) NOT NULL AUTO_INCREMENT;



--
-- Структура таблицы `z_seo`
--

CREATE TABLE IF NOT EXISTS `z_seo` (
`id` int(12) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `item_id` int(12) NOT NULL,
  `type` int(3) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `z_seo`
--
ALTER TABLE `z_seo`
 ADD PRIMARY KEY (`id`), ADD KEY `item_id` (`item_id`,`type`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `z_seo`
--
ALTER TABLE `z_seo`
MODIFY `id` int(12) NOT NULL AUTO_INCREMENT;
ALTER TABLE `z_seo` ADD `keywords` VARCHAR(255) NOT NULL AFTER `description`;

ALTER TABLE `z_slider_banner` ADD `allow` BOOLEAN NOT NULL AFTER `active`, ADD INDEX (`allow`) ;
update `z_slider_banner` set allow = 1;


-------------------

ALTER TABLE `z_warn` ADD `timemark` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP , ADD `notified` INT(5) NOT NULL , ADD INDEX (`timemark`) ;
update `z_warn` set timemark = NOW() - INTERVAL 1 MONTH;
ALTER TABLE `z_warn` ADD INDEX(`notified`);


-------------------
ALTER TABLE `z_section` ADD `image93` INT(10) NOT NULL AFTER `image`, ADD INDEX (`image93`) ;