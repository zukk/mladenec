<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

function p($arr){
	echo "<pre>";
		print_r($arr);
	echo "</pre>";
}
CModule::IncludeModule("sale");
CModule::IncludeModule("iblock");
CModule::IncludeModule("catalog");


?>