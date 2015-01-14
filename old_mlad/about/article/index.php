<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Статьи");
?> 
<h1> 
  <p style="padding: 5px 0px; margin: 0px; line-height: 1.5; text-align: left; color: rgb(102, 102, 102); font-family: Tahoma,Geneva; font-size: 16px;"><font color="#FF6600" class="Apple-style-span"><b>Статьи</b></font></p>
 </h1>
 <?$APPLICATION->IncludeComponent(
	"bitrix:news",
	"news",
	Array(
		"DISPLAY_DATE" => "Y",
		"DISPLAY_PICTURE" => "Y",
		"DISPLAY_PREVIEW_TEXT" => "Y",
		"SEF_MODE" => "Y",
		"AJAX_MODE" => "N",
		"IBLOCK_TYPE" => "articles",
		"IBLOCK_ID" => "9",
		"NEWS_COUNT" => "10",
		"USE_SEARCH" => "N",
		"USE_RSS" => "N",
		"USE_RATING" => "N",
		"USE_CATEGORIES" => "N",
		"USE_REVIEW" => "N",
		"USE_FILTER" => "N",
		"SORT_BY1" => "ACTIVE_FROM",
		"SORT_ORDER1" => "DESC",
		"SORT_BY2" => "SORT",
		"SORT_ORDER2" => "ASC",
		"CHECK_DATES" => "Y",
		"PREVIEW_TRUNCATE_LEN" => "",
		"LIST_ACTIVE_DATE_FORMAT" => "d.m.Y",
		"LIST_FIELD_CODE" => array(),
		"LIST_PROPERTY_CODE" => array(),
		"HIDE_LINK_WHEN_NO_DETAIL" => "N",
		"DISPLAY_NAME" => "Y",
		"META_KEYWORDS" => "PROMO_TAGS_IDS",
		"META_DESCRIPTION" => "PROMO_TAGS_IDS",
		"BROWSER_TITLE" => "NAME",
		"DETAIL_ACTIVE_DATE_FORMAT" => "d.m.Y",
		"DETAIL_FIELD_CODE" => array(),
		"DETAIL_PROPERTY_CODE" => array(),
		"DETAIL_DISPLAY_TOP_PAGER" => "N",
		"DETAIL_DISPLAY_BOTTOM_PAGER" => "Y",
		"DETAIL_PAGER_TITLE" => "страницы",
		"DETAIL_PAGER_TEMPLATE" => "nav_goods",
		"DETAIL_PAGER_SHOW_ALL" => "Y",
		"SET_TITLE" => "Y",
		"SET_STATUS_404" => "N",
		"INCLUDE_IBLOCK_INTO_CHAIN" => "Y",
		"ADD_SECTIONS_CHAIN" => "Y",
		"USE_PERMISSIONS" => "N",
		"CACHE_TYPE" => "A",
		"CACHE_TIME" => "3600",
		"CACHE_FILTER" => "N",
		"CACHE_GROUPS" => "Y",
		"DISPLAY_TOP_PAGER" => "N",
		"DISPLAY_BOTTOM_PAGER" => "Y",
		"PAGER_TITLE" => "страницы",
		"PAGER_SHOW_ALWAYS" => "Y",
		"PAGER_TEMPLATE" => "nav_goods",
		"PAGER_DESC_NUMBERING" => "N",
		"PAGER_DESC_NUMBERING_CACHE_TIME" => "36000",
		"PAGER_SHOW_ALL" => "Y",
		"SEF_FOLDER" => "/about/article/",
		"SEF_URL_TEMPLATES" => Array(
			"detail" => "#ELEMENT_ID#/"
		),
		"AJAX_OPTION_SHADOW" => "Y",
		"AJAX_OPTION_JUMP" => "N",
		"AJAX_OPTION_STYLE" => "Y",
		"AJAX_OPTION_HISTORY" => "N",
		"VARIABLE_ALIASES" => Array(
			"detail" => Array(),
		)
	)
);?> <?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>