<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Подписка на новости");
?>Если вы хотите регулярно получать наши новости, укажите свою электронную почту, выберите интересующие вас разделы, затем нажмите кнопку &laquo;Подписаться&raquo;. 
<br />
 <?$APPLICATION->IncludeComponent(
	"bitrix:subscribe.form",
	".default",
	Array(
		"USE_PERSONALIZATION" => "Y",
		"PAGE" => "#SITE_DIR#about/subscribe1.php",
		"SHOW_HIDDEN" => "N",
		"CACHE_TYPE" => "A",
		"CACHE_TIME" => "3600"
	)
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>