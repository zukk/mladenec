<?
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
ini_set('memory_limit', '150M');
//$APPLICATION->SetTitle("Ќовинки");
?><div class="center_LColonum_Y">
	<?
	$APPLICATION->IncludeComponent("bitrix:breadcrumb", "catalog", Array(
		"START_FROM" => "0", // Ќомер пункта, начина€ с которого будет построена навигационна€ цепочка
		"PATH" => "", // ѕуть, дл€ которого будет построена навигационна€ цепочка (по умолчанию, текущий путь)
		"SITE_ID" => "-", // Cайт (устанавливаетс€ в случае многосайтовой версии, когда DOCUMENT_ROOT у сайтов разный)
			), false
	);
	?>

	<?
	if (!isset($_SESSION['sort']) || isset($_REQUEST['sort'])) {
		$_SESSION['sorts'] = isset($_REQUEST['sort']) ? $_REQUEST['sort'] : "NAME:asc";
	}
	$sort = explode(':', $_SESSION['sorts']);
	
	$brand_id = isset($_REQUEST["p"]["PROPERTY_BRAND"]) ? $_REQUEST["p"]["PROPERTY_BRAND"] : 0;

	$arParams = array(
		"IBLOCK_TYPE" => "catalog",
		"IBLOCK_ID" => "7",
		"SECTION_CODE" => "",
		"ELEMENT_SORT_FIELD" => $sort[0],
		"ELEMENT_SORT_ORDER" => $sort[1],
		"FILTER_NAME" => "arrFilter",
		"INCLUDE_SUBSECTIONS" => "Y",
		"SHOW_ALL_WO_SECTION" => "Y",
		"PAGE_ELEMENT_COUNT" => "24",
		"LINE_ELEMENT_COUNT" => "4",
		"PROPERTY_CODE" => array(0=>"",1=>"RATINGSUM",2=>"RATING",3=>"IMG255",),
		"SECTION_URL" => "/catalog/_SECTION_/#SECTION_ID#.html",
		"DETAIL_URL" => "/product/_ELEMENT_/#SECTION_ID#.#ID#.html",
		"BASKET_URL" => "/personal/basket.php",
		"ACTION_VARIABLE" => "action",
		"PRODUCT_ID_VARIABLE" => "id",
		"SECTION_ID_VARIABLE" => "SECTION_ID",
		"AJAX_MODE" => "Y",
		"AJAX_OPTION_SHADOW" => "N",
		"AJAX_OPTION_JUMP" => "N",
		"AJAX_OPTION_STYLE" => "N",
		"AJAX_OPTION_HISTORY" => "N",
		"CACHE_TYPE" => "A",
		"CACHE_TIME" => "3600",
		"META_KEYWORDS" => "-",
		"META_DESCRIPTION" => "-",
		"DISPLAY_PANEL" => "N",
		"DISPLAY_COMPARE" => "N",
		"SET_STATUS_404" => "N",
		"CACHE_FILTER" => "N",
		"PRICE_CODE" => array(0=>"BASE",),
		"USE_PRICE_COUNT" => "N",
		"SHOW_PRICE_COUNT" => "1",
		"PRICE_VAT_INCLUDE" => "Y",
		"DISPLAY_TOP_PAGER" => "Y",
		"DISPLAY_BOTTOM_PAGER" => "Y",
		"PAGER_TITLE" => "“овары",
		"PAGER_SHOW_ALWAYS" => "Y",
		"PAGER_TEMPLATE" => "nav_goods",
		"PAGER_DESC_NUMBERING" => "N",
		"PAGER_DESC_NUMBERING_CACHE_TIME" => "36000",
		"AJAX_OPTION_ADDITIONAL" => "",
		"ONLY_HOT" => "Y",
		"BRAND_FILTER" => $brand_id
	);
	$APPLICATION->IncludeComponent("targeting:catalog.hot", "hot", $arParams, false);	

	?>
</div><? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>