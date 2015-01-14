<? require($_SERVER["DOCUMENT_ROOT"]."/odinc/auth.php");?>
<? require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");?>
<?
CModule::IncludeModule("sale");
CModule::IncludeModule("iblock");

$statuses = array('N', 'D', 'F', 'S', 'X');
$statuses = array_flip($statuses);
$STATUS_ID = (isset($_GET['status']) && isset($statuses[$_GET['status']])) ? strtoupper($_GET['status']) : reset($statuses);
$arFilter = Array("STATUS_ID" => $STATUS_ID);

$db_sales = CSaleOrder::GetList(array("ID" => "ASC"), $arFilter);
while ($ar_sales = $db_sales->Fetch())
{
	   //pre($ar_sales);
	   
	   $dbOrderProps = CSaleOrderPropsValue::GetOrderProps($ar_sales["ID"]);
	   $arProps = array();
	   $arProps["STREET"]="";
	   while ($arOrderProps = $dbOrderProps->GetNext()):
	   
	   		//pre($arOrderProps);
			if($arOrderProps["TYPE"] == "SELECT"):
			
				//pre($arOrderProps);
				//echo $arOrderProps["ID"];
				$arVal = CSaleOrderPropsVariant::GetByValue($arOrderProps["ORDER_PROPS_ID"], $arOrderProps["VALUE"]);//;
				$val = htmlspecialcharsEx($arVal["NAME"]);
			elseif($arOrderProps["TYPE"] == "LOCATION"):
				$arLocs = CSaleLocation::GetByID($arOrderProps["VALUE"], LANGUAGE_ID);
				$val = $arLocs["COUNTRY_NAME_ORIG"];
				$arProps["STREET"] = $arLocs["CITY_NAME_ORIG"];
			elseif($arOrderProps["CODE"]=="STREET" && $arProps["STREET"]==""):
				$arProps["STREET"] = $arOrderProps["VALUE"];
			else:
				$val = $arOrderProps["VALUE"];
			endif;
	   		$arProps[$arOrderProps["CODE"]] = $val;
	   endwhile;
	   //pre($arProps);
	   
	   echo "ÇÀÊÀÇ/r/n";
	   echo "ÇÀÐÅÃÈÑÒÐÈÐÎÂÀÍ: [$arProps[DELIV], $arProps[TIME], $ar_sales[PRICE_DELIVERY]][$arProps[LASTNAME] $arProps[NAME] $arProps[SNAME], $arProps[PHONE1] $arProps[PHONE2] $arProps[PHONE3]]/r/n";
	   echo "ÄÎÑÒÀÂÊÀ: $arProps[DELIV]©$arProps[TIME]©$arProps[CITY]|$arProps[STREET]|$arProps[HOME]|$arProps[CORP]|$arProps[PODYEZD]|$arProps[STAGE]|$arProps[HOMEPHONE]|$arProps[APP]|$arProps[MKAD]|$arProps[COMMENT]/r/n";
	   echo "ÂÑÅ ÀÄÐÅÑÀ ÊËÈÅÍÒÀ: /r/n";
	   echo "ÊÎÄ ÀÄÐÅÑÀ: 1/r/n";
	   echo "ÎÏÈÑÀÍÈÅ: $ar_sales[DATE_INSERT]©$ar_sales[ID]©$ar_sales[USER_ID]©$ar_sales[STATUS_ID]©$ar_sales[DISCOUNT_VALUE]/r/n";
	   
	   
	   
	   
		$dbBasketItems = CSaleBasket::GetList(
        array(
                "NAME" => "ASC",
                "ID" => "ASC"
            ),
        array(
                "LID" => SITE_ID,
                "ORDER_ID" => $ar_sales["ID"]
            ),
        false,
        false,
        array("ID", "PRODUCT_ID", "QUANTITY")
    	);
	   while ($arItems = $dbBasketItems->Fetch())
		{
			$res = CIBlockElement::GetByID($arItems["PRODUCT_ID"]);
			if($ar_res = $res->GetNext())
			  echo $ar_res['CODE']."©".$arItems["QUANTITY"]."/r/n";
		}
	   
	   echo "ÊÎÍÅÖ ÇÀÊÀÇÀ/r/n";
	   
	   //CSaleOrder::StatusOrder($ar_sales["ID"], "S");
	   
}




?>
<? require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>