<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("ќформление заказа");
?><div class="cont_fool">
<?$APPLICATION->IncludeComponent("mod:sale.order.full", "order", array(
	"ALLOW_PAY_FROM_ACCOUNT" => "Y",
	"SHOW_MENU" => "Y",
	"COUNT_DELIVERY_TAX" => "N",
	"COUNT_DISCOUNT_4_ALL_QUANTITY" => "N",
	"ONLY_FULL_PAY_FROM_ACCOUNT" => "N",
	"PATH_TO_BASKET" => "basket.php",
	"PATH_TO_PERSONAL" => "index.php",
	"PATH_TO_AUTH" => "/auth/",
	"PATH_TO_PAYMENT" => "payment.php",
	"USE_AJAX_LOCATIONS" => "Y",
	"SHOW_AJAX_DELIVERY_LINK" => "Y",
	"SET_TITLE" => "Y",
	"PRICE_VAT_INCLUDE" => "Y",
	"PRICE_VAT_SHOW_VALUE" => "Y"
	),
	false
);?>

</div>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>