<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
CModule::IncludeModule("sale");
$rsUsers = CUser::GetList(($by = "id"), ($order = "desc"), Array("UF_VALID" => false)); // �������� �������������   ,
$i = 0;
while ($arUsers = $rsUsers->GetNext()):
    $i++;
    $arGroups = CUser::GetUserGroup($arUsers["ID"]);
    //echo "<pre>"; print_r($arGroups); echo "</pre>";

    if (in_array(8, $arGroups)) $g = "gold";
    elseif (in_array(7, $arGroups)) $g = "plat";
    //pre($arUsers);


    $db_sales = CSaleOrderUserProps::GetList(
        array("DATE_UPDATE" => "DESC"),
        array("USER_ID" => $arUsers["ID"])
    );
    $adres = "";
    $street = "";
    $i = 0;
    while ($ar_sales = $db_sales->Fetch()) {
        $i++;
        //pre($ar_sales);

        $db_propVals = CSaleOrderUserPropsValue::GetList(($b = "SORT"), ($o = "ASC"), Array("USER_PROPS_ID" => $ar_sales["ID"]));
        while ($arPropVals = $db_propVals->Fetch()) {
            // pre($arPropVals);

            if ($arPropVals["TYPE"] == "LOCATION"):

                $arLocs = CSaleLocation::GetByID($arPropVals["VALUE"], LANGUAGE_ID);
                $city = $arLocs["COUNTRY_NAME_ORIG"];
                $street = $arLocs["CITY_NAME_ORIG"];

            elseif ($arPropVals["CODE"] == "STREET" && $street == ""):
                $street = $arPropVals["VALUE"];
            elseif ($arPropVals["CODE"] == "HOME"):
                $home = $arPropVals["VALUE"];
            elseif ($arPropVals["CODE"] == "CORP"):
                $corp = $arPropVals["VALUE"];
            elseif ($arPropVals["CODE"] == "PODYEZD"):
                $podyezd = $arPropVals["VALUE"];
            elseif ($arPropVals["CODE"] == "STAGE"):
                $stage = $arPropVals["VALUE"];
            elseif ($arPropVals["CODE"] == "HOMEPHONE"):
                $homephone = $arPropVals["VALUE"];
            elseif ($arPropVals["CODE"] == "APP"):
                $app = $arPropVals["VALUE"];
            elseif ($arPropVals["CODE"] == "MKAD"):
                $mkad = $arPropVals["VALUE"];
            elseif ($arPropVals["CODE"] == "COMMENT"):
                $comment = $arPropVals["VALUE"];
            endif;

        }
        if ($i <= 4) $adres .= "$city|$street|$home|$corp|$podyezd|$stage|$homephone|$app|$mkad|$comment&";
    }


    $name = $arUsers["LAST_NAME"] . " " . $arUsers["NAME"] . " " . $arUsers["SECOND_NAME"];
    $name = trim($name);
    echo $arUsers["ID"] . "�" . $name . "�";
    echo $adres;
    echo "�$arUsers[PERSONAL_PHONE]�$arUsers[EMAIL]�$g\r\n";
endwhile;

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");?>