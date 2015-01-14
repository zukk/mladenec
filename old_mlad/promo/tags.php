<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
//$APPLICATION->SetTitle("Интернет магазин ");
?>
<?
$APPLICATION->IncludeComponent(
	"promo:iblock.elemets.list",
	".default",
	Array(
		"ELEMENT_ID" => $_REQUEST["ELEMENT_ID"],
		"ELEMENT_CODE" => $_REQUEST["ELEMENT_CODE"],
		"IBLOCK_TYPE" => "catalog",
		"IBLOCK_ID" => "7",
		"SEF_MODE" => "N",
		"DISPLAY_DETAIL_TEXT" => "Y"
	)
);?>
<?php /*?>
<div>Советы профессиноналов</div>

<div><?$APPLICATION->IncludeComponent(
	"promo:iblock.elemets.list",
	".default",
	Array(
		"SEF_MODE" => "N",
		"IBLOCK_TYPE" => "articles",
		"IBLOCK_ID" => "9",
		"ELEMENT_ID" => $_REQUEST["ELEMENT_ID"],
		"ELEMENT_CODE" => $_REQUEST["ELEMENT_CODE"],
		"DISPLAY_DETAIL_TEXT" => "N"
	)
);?></div>
<?php */ ?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>