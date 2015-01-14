<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Промо страница");
?><?$APPLICATION->IncludeComponent("bitrix:catalog.element", "promo_by_type", Array(
	"IBLOCK_TYPE" => "promo",	// Тип инфо-блока
	"IBLOCK_ID" => "1370",	// Инфо-блок
	"ELEMENT_ID" => $_REQUEST["ID"],	// ID элемента
	"ELEMENT_CODE" => "",	// Код элемента
	"SECTION_ID" => $_REQUEST["SECTION_ID"],	// ID раздела
	"SECTION_CODE" => "",	// Код раздела
	"PROPERTY_CODE" => array(	// Свойства
		0 => "BRAND_LINKING",
		1 => "PRODUCT_LINKING",
		2 => "",
	),
	"SECTION_URL" => "",	// URL, ведущий на страницу с содержимым раздела
	"DETAIL_URL" => "",	// URL, ведущий на страницу с содержимым элемента раздела
	"BASKET_URL" => "/personal/basket.php",	// URL, ведущий на страницу с корзиной покупателя
	"ACTION_VARIABLE" => "action",	// Название переменной, в которой передается действие
	"PRODUCT_ID_VARIABLE" => "id",	// Название переменной, в которой передается код товара для покупки
	"PRODUCT_QUANTITY_VARIABLE" => "quantity",	// Название переменной, в которой передается количество товара
	"PRODUCT_PROPS_VARIABLE" => "prop",	// Название переменной, в которой передаются характеристики товара
	"SECTION_ID_VARIABLE" => "SECTION_ID",	// Название переменной, в которой передается код группы
	"CACHE_TYPE" => "A",	// Тип кеширования
	"CACHE_TIME" => "36000000",	// Время кеширования (сек.)
	"CACHE_GROUPS" => "Y",	// Учитывать права доступа
	"META_KEYWORDS" => "-",	// Установить ключевые слова страницы из свойства
	"META_DESCRIPTION" => "-",	// Установить описание страницы из свойства
	"BROWSER_TITLE" => "-",	// Установить заголовок окна браузера из свойства
	"SET_TITLE" => "Y",	// Устанавливать заголовок страницы
	"SET_STATUS_404" => "N",	// Устанавливать статус 404, если не найдены элемент или раздел
	"ADD_SECTIONS_CHAIN" => "Y",	// Включать раздел в цепочку навигации
	"PRICE_CODE" => "",	// Тип цены
	"USE_PRICE_COUNT" => "N",	// Использовать вывод цен с диапазонами
	"SHOW_PRICE_COUNT" => "1",	// Выводить цены для количества
	"PRICE_VAT_INCLUDE" => "Y",	// Включать НДС в цену
	"PRICE_VAT_SHOW_VALUE" => "N",	// Отображать значение НДС
	"PRODUCT_PROPERTIES" => "",	// Характеристики товара
	"USE_PRODUCT_QUANTITY" => "N",	// Разрешить указание количества товара
	"LINK_IBLOCK_TYPE" => "",	// Тип инфо-блока, элементы которого связаны с текущим элементом
	"LINK_IBLOCK_ID" => "",	// ID инфо-блока, элементы которого связаны с текущим элементом
	"LINK_PROPERTY_SID" => "",	// Свойство в котором хранится связь
	"LINK_ELEMENTS_URL" => "link.php?PARENT_ELEMENT_ID=#ELEMENT_ID#",	// URL на страницу где будет показан список связанных элементов
	),
	false
);?> <?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>