<?
$arUrlRewrite = array(
    array(
        "CONDITION" => "#^/product/.+/([0-9+]+).([0-9+]+).html(.*)#",
        "RULE" => "SECTION_ID=$1&ID=$2",
        "ID" => "mod:catalog.element",
        "PATH" => "/catalog/element.php",
    ),
    array(
        "CONDITION" => "#^/catalog/.+/([0-9+]+).html(.*)#",
        "RULE" => "SECTION_ID=$1",
        "ID" => "mod:catalog.section",
        "PATH" => "/catalog/index.php",
    ),
    array(
        "CONDITION" => "#^/site_map/([0-9+]+).html(.*)#",
        "RULE" => "SECTION_ID=$1",
        "ID" => "mod:main.map",
        "PATH" => "/site_map/index.php",
    ),
    array(
        "CONDITION" => "#^/brand/.+/([0-9+]+).html(.*)#",
        "RULE" => "ID=$1",
        "ID" => "bitrix:catalog.section",
        "PATH" => "/promo/brand.php",
    ),
    array(
        "CONDITION" => "#^/type/.+/([0-9+]+).html(.*)#",
        "RULE" => "ID=$1",
        "ID" => "bitrix:catalog.section",
        "PATH" => "/promo/index.php",
    ),
    array(
        "CONDITION" => "#^/tag/(.+).html(.*)#",
        "RULE" => "ELEMENT_CODE=$1",
        "ID" => "",
        "PATH" => "/promo/tags.php",
    ),
    array(
        "CONDITION" => "#^/about/article/#",
        "RULE" => "",
        "ID" => "bitrix:news",
        "PATH" => "/review/index.php",
    ),
    array(
        "CONDITION" => "#^/about/article/#",
        "RULE" => "",
        "ID" => "bitrix:news",
        "PATH" => "/about/article/review/index.php",
    ),
    array(
        "CONDITION" => "#^/about/article/#",
        "RULE" => "",
        "ID" => "bitrix:news",
        "PATH" => "/about/article/index.php",
    ),
    array(
        "CONDITION" => "#^/about/review/#",
        "RULE" => "",
        "ID" => "bitrix:news",
        "PATH" => "/about/review/index.php",
    ),
    array(
        "CONDITION" => "#^/about/news/#",
        "RULE" => "",
        "ID" => "bitrix:news",
        "PATH" => "/news/index.php",
    ),
    array(
        "CONDITION" => "#^/about/news/#",
        "RULE" => "",
        "ID" => "bitrix:news",
        "PATH" => "/about/news/index.php",
    ),
);

?>