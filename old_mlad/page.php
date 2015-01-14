<?require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
function p($arr)
{
    echo "<pre>";
    print_r($arr);
    echo "</pre>";
}

CModule::IncludeModule("sale");
CModule::IncludeModule("iblock");
CModule::IncludeModule("catalog");
?>
<html>
<head>
    <title>
        �������� ������������ ����� ��� ����.
    </title>
    <style>
        body {
            font: 12px/18px Arila;
        }

        #itog {
            fint-size: 18px;
        }
    </style>
</head>
<body>
<h1>�������� ������������ ����� ��� ����.</h1>

<?php
$sum_kupons = 0;
$arSelect = Array("ID", "NAME", "PROPERTY_DESCOUNT_ID", "PROPERTY_USER_ID", "DATE_CREATE");
$arFilter = Array("IBLOCK_ID" => 1382, "ACTIVE" => "N", "PROPERTY_DESCOUNT_ID" => 155279, "!=PROPERTY_USER_ID" => "");
$res = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
while ($ob = $res->GetNextElement()) {
    if ($arFields["PROPERTY_USER_ID_VALUE"] != "1")
        $sum_kupons++;

    $arFields = $ob->GetFields();
    if ($arFields["PROPERTY_USER_ID_VALUE"] != "") {
        //p($arFields);
        $arUsers[] = $arFields["PROPERTY_USER_ID_VALUE"];
    }

}
$summ_users = 0;
$order_summ = 0;
$price_summ = 0;
foreach ($arUsers as $user)
{
if ($user != 1) {
    $arFilter = Array("USER_ID" => $user, ">=DATE_INSERT" => "26.03.2012");

    $summ_users++;
    ?>
    <div style="border: 2px solid green; width: 600px;">
        <h3><?= $user ?></h3>

        <ul>

            <?
            $db_order = CSaleOrder::GetList(array("DATE_INSERT" => "ASC"), $arFilter);
            while ($ar_order = $db_order->GetNext()) {
                $order_summ++;
                echo "<li>";
                //p($ar_order);
                echo "<h4>" . $ar_order["ID"] . " ����: " . $ar_order["DATE_INSERT"] . "</h4>";
                //������ ������.
                $dbBasketItems = CSaleBasket::GetList(
                    array(
                        "NAME" => "ASC",
                        "ID" => "ASC"
                    ),
                    array(
                        //"FUSER_ID" => CSaleBasket::GetBasketUserID(),
                        "LID" => s1,
                        "ORDER_ID" => $ar_order["ID"]
                    ),
                    false,
                    false
                //array("ID", "PRODUCT_ID", "QUANTITY", "PRICE")
                );;
                $sum = 0;
                while ($arItems = $dbBasketItems->Fetch()) {
                    if ($arItems["PRODUCT_ID"] != "154447") {
                        $sum += intval($arItems["PRICE"]);
                        $price_summ += $sum;
                    }
                    echo "<p>" . $arItems["NAME"] . "<br />" . $arItems["PRICE"] . "</p>";
                    //p($arItems);
                }?>
                <div style="border: 1px solid red; font-weight: bold; font-size: 16px; width: 200px;"><?= $sum ?></div>
                <?echo "</li>";
            }

            ?>

        </ul>
    </div>
<? } ?>
<p>
    <?
    }
    echo "����� ������������ �������: " . $sum_kupons . "<br />";
    echo "������������� �������������� �����: " . $summ_users . "<br />";
    echo "����� ������� ����� �������� ������: " . $order_summ . "<br />";
    echo "����� ����� �������: " . $price_summ . "<br />";
    $prognoz = intval(($sum_kupons / $summ_users) * $price_summ);
    echo "������� (������������ ������� / �������������� �����) * ����� ����� �������: " . $prognoz . "<br />";
    ?>
</p>
</body>
</html>