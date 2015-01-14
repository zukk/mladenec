<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Отписка от рассылки");

$APPLICATION->IncludeComponent("mod:subscribe.unsubscribe", "", array(
	"ASD_MAIL_ID" => $_REQUEST["mid"],
	"ASD_MAIL_MD5" => $_REQUEST["mhash"],
	"ASD_RUBRICS_ID" => $_REQUEST["rubrics_id"]
),false);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");