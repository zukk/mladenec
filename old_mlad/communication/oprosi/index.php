<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Опросы");
?> 
<h1><font color="#f16522">Опросы.</font></h1>
 
<p><?$APPLICATION->IncludeComponent(
	"bitrix:voting.current",
	"",
	Array(
		"CHANNEL_SID" => "OPROS",
		"VOTE_ID" => "",
		"VOTE_ALL_RESULTS" => "N",
		"CACHE_TYPE" => "A",
		"CACHE_TIME" => "3600",
		"CACHE_NOTES" => "",
		"AJAX_MODE" => "N",
		"AJAX_OPTION_SHADOW" => "Y",
		"AJAX_OPTION_JUMP" => "N",
		"AJAX_OPTION_STYLE" => "Y",
		"AJAX_OPTION_HISTORY" => "N",
		"AJAX_OPTION_ADDITIONAL" => ""
	)
);?></p>
 <?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>