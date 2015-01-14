<?
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
$APPLICATION->SetTitle("������");
?>
<?$APPLICATION->IncludeComponent("mail:iblock.element.add.form", ".default", array(
        "IBLOCK_TYPE" => "info",
        "IBLOCK_ID" => "1374",
        "STATUS_NEW" => "N",
        "LIST_URL" => "",
        "USE_CAPTCHA" => "N",
        "USER_MESSAGE_EDIT" => "",
        "USER_MESSAGE_ADD" => "",
        "DEFAULT_INPUT_SIZE" => "30",
        "RESIZE_IMAGES" => "N",
        "PROPERTY_CODES" => array(),
        "PROPERTY_CODES_REQUIRED" => array(),
        "GROUPS" => array(),
        "STATUS" => "ANY",
        "ELEMENT_ASSOC" => "CREATED_BY",
        "MAX_USER_ENTRIES" => "100000",
        "MAX_LEVELS" => "100000",
        "LEVEL_LAST" => "Y",
        "MAX_FILE_SIZE" => "0",
        "PREVIEW_TEXT_USE_HTML_EDITOR" => "N",
        "DETAIL_TEXT_USE_HTML_EDITOR" => "N",
        "SEF_MODE" => "N",
        "SEF_FOLDER" => "/",
        "CUSTOM_TITLE_NAME" => "",
        "CUSTOM_TITLE_TAGS" => "",
        "CUSTOM_TITLE_DATE_ACTIVE_FROM" => "",
        "CUSTOM_TITLE_DATE_ACTIVE_TO" => "",
        "CUSTOM_TITLE_IBLOCK_SECTION" => "",
        "CUSTOM_TITLE_PREVIEW_TEXT" => "",
        "CUSTOM_TITLE_PREVIEW_PICTURE" => "",
        "CUSTOM_TITLE_DETAIL_TEXT" => "",
        "CUSTOM_TITLE_DETAIL_PICTURE" => ""
    ),
    false
);?>


<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>