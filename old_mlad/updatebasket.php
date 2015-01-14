<?require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

$APPLICATION->IncludeComponent(
    "mod:sale.basket.basket.small",
    "mini",
    Array(
        "PATH_TO_BASKET" => "/personal/basket.php",
        "PATH_TO_ORDER" => "/personal/order_data.php"
    ),
    false
);
?>


