<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
$APPLICATION->SetPageProperty("title", "Младенец.ру | Детский интернет магазин, детские товары,  товары для новорожденных в интернет магазине для детей в Москве.");
$APPLICATION->SetPageProperty("NOT_SHOW_NAV_CHAIN", "Y");
$APPLICATION->SetTitle("Статьи");
?> <?$APPLICATION->IncludeComponent(
    "mod:info",
    ".default",
    Array(
        "ELEMENT_ID" => "",
        "ELEMENT_NAME" => "�����",
        "CACHE_TYPE" => "A",
        "CACHE_TIME" => "3600",
        "CACHE_NOTES" => ""
    )
);?> <? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>