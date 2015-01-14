<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Отзывы");
?><div class="center_LColonum_Y">
<?if (is_set($_GET[strIMessage]) and ($_GET[strIMessage]!="")){?>
<div style="text-align:center; font-weight:bold; background: none repeat scroll 0 0 #ebc772;
	border-radius: 5px 5px 5px 5px;
    margin: 10px 0;
    padding: 5px 5px;">
	Ваше сообщение принято и будет рассмотрено в ближайшее время.
</div>
<?}?>
<a href="#review" style="font-size: 18px; font-weight: bold;">Оставить отзыв</a>
<?$APPLICATION->IncludeComponent("bitrix:news", "review", array(
	"IBLOCK_TYPE" => "comments",
	"IBLOCK_ID" => "80",
	"NEWS_COUNT" => "5",
	"USE_SEARCH" => "N",
	"USE_RSS" => "N",
	"USE_RATING" => "N",
	"USE_CATEGORIES" => "N",
	"USE_REVIEW" => "N",
	"USE_FILTER" => "N",
	"SORT_BY1" => "SORT",
	"SORT_ORDER1" => "ASC",
	"SORT_BY2" => "ACTIVE_FROM",
	"SORT_ORDER2" => "DESC",
	"CHECK_DATES" => "Y",
	"SEF_MODE" => "Y",
	"SEF_FOLDER" => "/about/review/",
	"AJAX_MODE" => "N",
	"AJAX_OPTION_SHADOW" => "Y",
	"AJAX_OPTION_JUMP" => "N",
	"AJAX_OPTION_STYLE" => "Y",
	"AJAX_OPTION_HISTORY" => "N",
	"CACHE_TYPE" => "A",
	"CACHE_TIME" => "3600",
	"CACHE_FILTER" => "N",
	"DISPLAY_PANEL" => "N",
	"SET_TITLE" => "N",
	"SET_STATUS_404" => "N",
	"INCLUDE_IBLOCK_INTO_CHAIN" => "Y",
	"ADD_SECTIONS_CHAIN" => "Y",
	"USE_PERMISSIONS" => "N",
	"PREVIEW_TRUNCATE_LEN" => "",
	"LIST_ACTIVE_DATE_FORMAT" => "d.m.Y",
	"LIST_FIELD_CODE" => array(
		0 => "",
		1 => "",
	),
	"LIST_PROPERTY_CODE" => array(
		0 => "ANSWER",
		1 => "",
	),
	"HIDE_LINK_WHEN_NO_DETAIL" => "N",
	"DISPLAY_NAME" => "Y",
	"META_KEYWORDS" => "-",
	"META_DESCRIPTION" => "-",
	"BROWSER_TITLE" => "-",
	"DETAIL_ACTIVE_DATE_FORMAT" => "d.m.Y",
	"DETAIL_FIELD_CODE" => array(
		0 => "",
		1 => "",
	),
	"DETAIL_PROPERTY_CODE" => array(
		0 => "ANSWER",
		1 => "",
	),
	"DETAIL_DISPLAY_TOP_PAGER" => "N",
	"DETAIL_DISPLAY_BOTTOM_PAGER" => "Y",
	"DETAIL_PAGER_TITLE" => "отзывы",
	"DETAIL_PAGER_TEMPLATE" => "nav_goods",
	"DETAIL_PAGER_SHOW_ALL" => "Y",
	"DISPLAY_TOP_PAGER" => "N",
	"DISPLAY_BOTTOM_PAGER" => "Y",
	"PAGER_TITLE" => "отзывы",
	"PAGER_SHOW_ALWAYS" => "Y",
	"PAGER_TEMPLATE" => "nav_goods",
	"PAGER_DESC_NUMBERING" => "N",
	"PAGER_DESC_NUMBERING_CACHE_TIME" => "36000",
	"PAGER_SHOW_ALL" => "Y",
	"DISPLAY_DATE" => "Y",
	"DISPLAY_PICTURE" => "N",
	"DISPLAY_PREVIEW_TEXT" => "Y",
	"AJAX_OPTION_ADDITIONAL" => "",
	"SEF_URL_TEMPLATES" => array(
		"news" => "",
		"section" => "",
		"detail" => "#ELEMENT_ID#/",
		"search" => "search/",
		"rss" => "rss/",
		"rss_section" => "#SECTION_ID#/rss/",
	)
	),
	false
);?> 
<a name="review"></a>
<br />
 
<br />

 <? $APPLICATION->IncludeComponent("review:iblock.element.add.form", "review", array(
	"IBLOCK_TYPE" => "comments",
	"IBLOCK_ID" => "80",
	"STATUS_NEW" => "ANY",
	"LIST_URL" => "",
	"USE_CAPTCHA" => "Y",
	"USER_MESSAGE_EDIT" => "",
	"USER_MESSAGE_ADD" => "",
	"DEFAULT_INPUT_SIZE" => "30",
	"RESIZE_IMAGES" => "N",
	"PROPERTY_CODES" => array(
		0 => "NAME",
		1 => "PREVIEW_TEXT",
		2 => "676",
		3 => "678",
		4 => "675",
		5 => "737",
		6 => "677",
	),
	"PROPERTY_CODES_REQUIRED" => array(
		0 => "NAME",
		1 => "PREVIEW_TEXT",
		2 => "676",
		3 => "678",
		4 => "675",
		5 => "737",
	),
	"GROUPS" => array(
		0 => "2",
	),
	"STATUS" => "ANY",
	"ELEMENT_ASSOC" => "CREATED_BY",
	"MAX_USER_ENTRIES" => "100000",
	"MAX_LEVELS" => "100000",
	"LEVEL_LAST" => "Y",
	"MAX_FILE_SIZE" => "0",
	"PREVIEW_TEXT_USE_HTML_EDITOR" => "N",
	"DETAIL_TEXT_USE_HTML_EDITOR" => "N",
	"SEF_MODE" => "N",
	"SEF_FOLDER" => "/about/review/",
	"CUSTOM_TITLE_NAME" => "Заголовок сообщения",
	"CUSTOM_TITLE_TAGS" => "",
	"CUSTOM_TITLE_DATE_ACTIVE_FROM" => "",
	"CUSTOM_TITLE_DATE_ACTIVE_TO" => "",
	"CUSTOM_TITLE_IBLOCK_SECTION" => "",
	"CUSTOM_TITLE_PREVIEW_TEXT" => "Текст сообщения",
	"CUSTOM_TITLE_PREVIEW_PICTURE" => "",
	"CUSTOM_TITLE_DETAIL_TEXT" => "",
	"CUSTOM_TITLE_DETAIL_PICTURE" => ""
	),
	false
);?> 
</div>
 <?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>