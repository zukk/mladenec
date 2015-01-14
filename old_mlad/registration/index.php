<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("–егистраци€");
//”важаемые дамы и господа, регистраци€ приостановлена на период тестировани€.

?>
<div class="center_LColonum_Y">
<p>
<?$APPLICATION->IncludeComponent("bitrix:main.register", "register", array(
	"SHOW_FIELDS" => array(
		0 => "NAME",
		1 => "SECOND_NAME",
		2 => "LAST_NAME",
		3 => "PERSONAL_PHONE",
	),
	"REQUIRED_FIELDS" => array(
		0 => "NAME",
		1 => "PERSONAL_PHONE",
	),
	"SEF_MODE" => "N",
	"SEF_FOLDER" => "/registration/",
	"AUTH" => "Y",
	"USE_BACKURL" => "Y",
	"SUCCESS_PAGE" => "/registration/why.php",
	"SET_TITLE" => "Y",
	"USER_PROPERTY" => array(
	),
	"USER_PROPERTY_NAME" => ""
	),
	false
);?></p>
</div>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>