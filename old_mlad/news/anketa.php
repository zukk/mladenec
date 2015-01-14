<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Анкетирование");
?><?$APPLICATION->IncludeComponent(
	"bitrix:form",
	"",
	Array(
		"AJAX_MODE" => "Y",
		"SEF_MODE" => "N",
		"WEB_FORM_ID" => "1",
		"RESULT_ID" => $_REQUEST[RESULT_ID],
		"START_PAGE" => "new",
		"SHOW_LIST_PAGE" => "N",
		"SHOW_EDIT_PAGE" => "N",
		"SHOW_VIEW_PAGE" => "Y",
		"SUCCESS_URL" => "",
		"SHOW_ANSWER_VALUE" => "N",
		"SHOW_ADDITIONAL" => "N",
		"SHOW_STATUS" => "Y",
		"EDIT_ADDITIONAL" => "N",
		"EDIT_STATUS" => "Y",
		"NOT_SHOW_FILTER" => Array(),
		"NOT_SHOW_TABLE" => Array(),
		"CHAIN_ITEM_TEXT" => "",
		"CHAIN_ITEM_LINK" => "",
		"IGNORE_CUSTOM_TEMPLATE" => "N",
		"USE_EXTENDED_ERRORS" => "N",
		"CACHE_TYPE" => "A",
		"CACHE_TIME" => "3600",
		"AJAX_OPTION_SHADOW" => "N",
		"AJAX_OPTION_JUMP" => "N",
		"AJAX_OPTION_STYLE" => "Y",
		"AJAX_OPTION_HISTORY" => "Y",
		"VARIABLE_ALIASES" => Array(
			"action" => "action"
		)
	),
false
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>