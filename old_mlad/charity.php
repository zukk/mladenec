<?php
require($_SERVER["DOCUMENT_ROOT"] .
    "/bitrix/modules/main/include/prolog_before.php");
//--------������������������� 

if ($_POST['summ']) {
    $charity = $_POST['summ'];

    if (CModule::IncludeModule("sale")) {
        $arFields = array(
            "PRODUCT_ID" => $GLOBALS['CHARITY_ID'],
            "PRODUCT_PRICE_ID" => 0,
            "PRICE" => 1.00,
            "CURRENCY" => "RUB",
            "QUANTITY" => $charity,
            "LID" => "s1",
            "NAME" => "������� ����� ���������",
            "CALLBACK_FUNC" => "MyBasketCallback",
            //"MODULE" => "my_module",
            "NOTES" => "",
            "ORDER_CALLBACK_FUNC" => "MyBasketOrderCallback",
            "DETAIL_PAGE_URL" => "/charity/darya_selezneva.php"
        );

        CSaleBasket::Add($arFields);
    }
    unset($_POST['summ']);
}
//========�������������������
header("Location: http://mladenec-shop.ru/personal/basket.php");
?>