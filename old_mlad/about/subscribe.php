<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Подписка на новости");
?><?$APPLICATION->IncludeComponent("bitrix:subscribe.form", ".default", array(
	"USE_PERSONALIZATION" => "Y",
	"SHOW_HIDDEN" => "N",
	"PAGE" => "#SITE_DIR#about/subscribe1.php",
	"CACHE_TYPE" => "A",
	"CACHE_TIME" => "3600"
	),
	false
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>