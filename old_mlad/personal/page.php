<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Новая страница");
?>
<p><?$APPLICATION->IncludeComponent(
	"bitrix:sale.order.ajax",
	"",
	Array(
		"PATH_TO_BASKET" => "basket.php",
		"PATH_TO_PERSONAL" => "index.php",
		"PATH_TO_PAYMENT" => "payment.php",
		"PAY_FROM_ACCOUNT" => "N",
		"COUNT_DELIVERY_TAX" => "N",
		"COUNT_DISCOUNT_4_ALL_QUANTITY" => "N",
		"ONLY_FULL_PAY_FROM_ACCOUNT" => "N",
		"SET_TITLE" => "Y",
		"PROP_1" => Array()
	),
false
);?></p>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>