<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Акции");
?>
<p><?$APPLICATION->IncludeComponent("bitrix:catalog.section", "actions", array(
	"IBLOCK_TYPE" => "catalog",
	"IBLOCK_ID" => "75",
	"SECTION_ID" => "",
	"SECTION_CODE" => "",
	"ELEMENT_SORT_FIELD" => "active_from",
	"ELEMENT_SORT_ORDER" => "desc",
	"FILTER_NAME" => "arrFilter",
	"INCLUDE_SUBSECTIONS" => "Y",
	"SHOW_ALL_WO_SECTION" => "Y",
	"PAGE_ELEMENT_COUNT" => "30",
	"LINE_ELEMENT_COUNT" => "1",
	"PROPERTY_CODE" => array(
		0 => "",
		1 => "",
	),
	"SECTION_URL" => "",
	"DETAIL_URL" => "",
	"BASKET_URL" => "/personal/basket.php",
	"ACTION_VARIABLE" => "action",
	"PRODUCT_ID_VARIABLE" => "id",
	"SECTION_ID_VARIABLE" => "SECTION_ID",
	"AJAX_MODE" => "N",
	"AJAX_OPTION_SHADOW" => "Y",
	"AJAX_OPTION_JUMP" => "N",
	"AJAX_OPTION_STYLE" => "Y",
	"AJAX_OPTION_HISTORY" => "N",
	"CACHE_TYPE" => "A",
	"CACHE_TIME" => "3600",
	"META_KEYWORDS" => "-",
	"META_DESCRIPTION" => "-",
	"DISPLAY_PANEL" => "N",
	"DISPLAY_COMPARE" => "N",
	"SET_TITLE" => "N",
	"SET_STATUS_404" => "N",
	"CACHE_FILTER" => "N",
	"PRICE_CODE" => array(
	),
	"USE_PRICE_COUNT" => "N",
	"SHOW_PRICE_COUNT" => "1",
	"PRICE_VAT_INCLUDE" => "Y",
	"DISPLAY_TOP_PAGER" => "N",
	"DISPLAY_BOTTOM_PAGER" => "Y",
	"PAGER_TITLE" => "акции",
	"PAGER_SHOW_ALWAYS" => "Y",
	"PAGER_TEMPLATE" => "nav_goods",
	"PAGER_DESC_NUMBERING" => "N",
	"PAGER_DESC_NUMBERING_CACHE_TIME" => "36000",
	"AJAX_OPTION_ADDITIONAL" => ""
	),
	false
);?></p>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>