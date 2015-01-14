<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("�������");
?><?$APPLICATION->IncludeComponent("mod:sale.basket.basket", "basket", array(
	"COUNT_DISCOUNT_4_ALL_QUANTITY" => "N",
	"COLUMNS_LIST" => array(
		0 => "NAME",
		1 => "PRICE",
		2 => "QUANTITY",
		3 => "DELETE",
	),
	"PATH_TO_ORDER" => "/personal/order_data.php",
	"HIDE_COUPON" => "Y",
	"QUANTITY_FLOAT" => "N",
	"PRICE_VAT_SHOW_VALUE" => "N",
	"SET_TITLE" => "Y"
	),
	false
);?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>