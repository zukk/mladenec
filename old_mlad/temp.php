<?
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
$APPLICATION->SetTitle("����� ��������");
?><?$APPLICATION->IncludeComponent("bitrix:search.title", "SearchFormByTitle", array(
        "NUM_CATEGORIES" => "1",
        "TOP_COUNT" => "5",
        "ORDER" => "date",
        "USE_LANGUAGE_GUESS" => "Y",
        "CHECK_DATES" => "N",
        "SHOW_OTHERS" => "N",
        "PAGE" => "#SITE_DIR#search/index.php",
        "CATEGORY_0_TITLE" => "�����",
        "CATEGORY_0" => array(
            0 => "no",
        ),
        "SHOW_INPUT" => "Y",
        "INPUT_ID" => "title-search-input",
        "CONTAINER_ID" => "title-search"
    ),
    false
);?><?require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");?>