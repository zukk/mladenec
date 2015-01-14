<?
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
$APPLICATION->SetTitle("������");
?><?$APPLICATION->IncludeComponent(
    "bitrix:voting.current",
    "",
    Array(),
    false
);?><?require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");?>