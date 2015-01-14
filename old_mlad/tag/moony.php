<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("moony");
?><?CModule::IncludeModule("iblock");
$SECTION_ID = 29859;

$res = CIBlockSection::GetByID($SECTION_ID);
	if($ar_res = $res->GetNext()) {
	echo $ar_res['NAME'];
			$arParams = array(
						"IBLOCK_TYPE" => "catalog",
						"IBLOCK_ID" => "7",
						"SECTION_ID" => $ar_res['ID'],
						"SECTION_CODE" => $ar_res['CODE'],
						"COUNT_ELEMENTS" => "N",
						"PAGER_TEMPLATE" => "nav_goods",
						"PAGE_ELEMENT_COUNT" => "40",
						"LINE_ELEMENT_COUNT" => "4",
						"PAGER_SHOW_ALL" => "N",
						//"TOP_DEPTH" => "3",
						"SECTION_URL" => "/catalog/_SECTION_/#SECTION_ID#.html",
						"DETAIL_URL" => "/product/_ELEMENT_/#SECTION_ID#.#ID#.html",
						"CACHE_TYPE" => "A",
						"CACHE_TIME" => "3600",
						"DISPLAY_PANEL" => "N",
						"INCLUDE_SUBSECTIONS" => "Y",
						"SET_TITLE" => "Y",
						"BY_LINK" => "N",
						"ADD_SECTIONS_CHAIN" => "Y",
						//"ELEMENT_SORT_FIELD"=> $sort[0],
						//"ELEMENT_SORT_ORDER"=> $sort[1],
	                    "BASKET_URL" => "/personal/basket.php",
	                    "ACTION_VARIABLE" => "action",
	                    "PRODUCT_ID_VARIABLE" => "id",
	                    "SECTION_ID_VARIABLE" => "SECTION_ID",
	                    "PROPERTY_CODE"=>array("BOX")
	);
	$APPLICATION->IncludeComponent("mod:catalog.section", "image", $arParams, false);
}
?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>