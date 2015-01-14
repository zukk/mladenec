<? require($_SERVER["DOCUMENT_ROOT"]."/odinc/auth.php");?>
<? require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");?>
<?
CModule::IncludeModule("sale");
CModule::IncludeModule("iblock");
ob_start();
$statuses = array('N', 'D', 'F', 'S', 'X');
$statuses_flip = array_flip($statuses);
$STATUS_ID = (isset($_GET['status']) && isset($statuses_flip[$_GET['status']])) ? strtoupper($_GET['status']) : reset($statuses);
$arFilter = Array("STATUS_ID" => $STATUS_ID);
$db_sales = CSaleOrder::GetList(array("ID" => "ASC"), $arFilter, false, array("nTopCount" => 2500));

while ($ar_sales = $db_sales->Fetch())
{

	   $dbOrderProps = CSaleOrderPropsValue::GetOrderProps($ar_sales["ID"]);
	   $arProps = array();
	   
	   $bOrderRepeat = false; //--Óñòàíàâëèâàåòñÿ, åñëè â çàêàçå ïðèñóòñòâóþò ñèñòåìíûå ýëåìåíòû (ïîâòîðíàÿ âûãðóçêà çàêàçà)
	   $city = '';
	   $street = '';
	   $home = '';
	   $corp = '';
	   $address_is_correct = '';
	   $podyezd = '';
	   $stage = '';
	   $homephone = '';
	   $app = '';
	   $mkad = '';
	   $comment = '';
	   $delivery_adres =""; 
	   while ($arOrderProps = $dbOrderProps->GetNext()):
			$val = "";
			$city_location = false;
			 //echo "<pre>"; print_r ($arOrderProps); echo	"</pre>";
			if($arOrderProps["TYPE"] == "SELECT"):
				$arVal = CSaleOrderPropsVariant::GetByValue($arOrderProps["ORDER_PROPS_ID"], $arOrderProps["VALUE"]);//;
				$val = htmlspecialcharsEx($arVal["NAME"]);
			elseif($arOrderProps["TYPE"] == "LOCATION"):
				if($arOrderProps["VALUE"]!=25088):
					$arLocs = CSaleLocation::GetByID($arOrderProps["VALUE"], LANGUAGE_ID);
					$val = $arLocs["COUNTRY_NAME_ORIG"];
					$arProps["STREET"] = $arLocs["CITY_NAME_ORIG"];
				endif;
			elseif($arOrderProps["CODE"]=="STREET"):
				$arProps["STREET"] = $arOrderProps["VALUE"];
			else:
				$val = $arOrderProps["VALUE"];
			endif;


			if($arOrderProps["CODE"]!="STREET") {
	   			$arProps[$arOrderProps["CODE"]] = $val;
			}
	   endwhile;

	   if(!isset($arProps["CITY"]) || $arProps["CITY"] == "" || $arProps["CITY2"]!="") $arProps["CITY"] = $arProps["CITY2"];
	   
    $arProps["COMMENT"] = str_replace("\r\n"," ",$arProps["COMMENT"]);
    $arProps["COMMENT"] = str_replace("\n"," ",$arProps["COMMENT"]); 
    $ardes_order = "$arProps[CITY]|$arProps[STREET]|$arProps[HOME]|$arProps[CORP]|$arProps[PODYEZD]|$arProps[STAGE]|$arProps[HOMEPHONE]|$arProps[APP]|$arProps[MKAD]|$arProps[COMMENT]";
	$delivery_adres = "$arProps[CITY]|$arProps[STREET]|$arProps[HOME]";
	$ar_sales["DATE_INSERT"] = str_replace(" ", "|", $ar_sales["DATE_INSERT"]);    
	echo "ÇÀÊÀÇ\r\n";
    echo "ÇÀÐÅÃÈÑÒÐÈÐÎÂÀÍ: [$arProps[DELIV], $arProps[TIME], $ar_sales[PRICE_DELIVERY]][$arProps[CALL], $arProps[LASTNAME] $arProps[NAME] $arProps[SNAME], $arProps[PHONE1] $arProps[PHONE2] $arProps[PHONE3]]\r\n";
   // echo "ÄÎÑÒÀÂÊÀ: $arProps[DELIV]©$arProps[TIME]©$ardes_order\r\n";
    echo "ÎÏÈÑÀÍÈÅ: $ar_sales[DATE_INSERT]©$ar_sales[ID]©$ar_sales[USER_ID]©$ar_sales[STATUS_ID]©$arProps[USER_STATUS]\r\n";
	
	$db_adr = CSaleOrderUserProps::GetList(
      array("DATE_UPDATE" => "DESC"),
      array("USER_ID" => $ar_sales["USER_ID"])
    );
    
	$i = 0; $adres_index = 0;
	$adres = array();
	while ($ar_adr = $db_adr->Fetch())
	{
	   //$i++;
	   	$adres = "";
	   	$street = "";
	   	$city = "";
	   	$address_is_correct = "";
	   	$coord = "";	   	
	   $i = $ar_adr['ID'];
	   if($adres_index==0){
	   	$adres_index = $i;
	   }
	   
		$db_propVals = CSaleOrderUserPropsValue::GetList(($b="SORT"), ($o="ASC"), Array("USER_PROPS_ID"=>$ar_adr["ID"]));
		while ($arPropVals = $db_propVals->Fetch())
		{
			if($arPropVals["TYPE"]=="LOCATION"):			
				$arLocs = CSaleLocation::GetByID($arPropVals["VALUE"], LANGUAGE_ID);
				$city = $arLocs["COUNTRY_NAME_ORIG"];
				$street = $arLocs["CITY_NAME_ORIG"];
			elseif($arPropVals["CODE"]=="STREET" && $street==""):
				$street = $arPropVals["VALUE"];
			elseif($arPropVals["CODE"]=="HOME"):
				$home = $arPropVals["VALUE"];
			elseif($arPropVals["CODE"]=="CORP"):
				$corp = $arPropVals["VALUE"];
			elseif($arPropVals["CODE"]=="ADDRESS_IS_CORRECT"):
				$address_is_correct = $arPropVals["VALUE"];
			elseif($arPropVals["CODE"]=="PODYEZD"):
				$podyezd = $arPropVals["VALUE"];
			elseif($arPropVals["CODE"]=="STAGE"):
				$stage = $arPropVals["VALUE"];
			elseif($arPropVals["CODE"]=="HOMEPHONE"):
				$homephone = $arPropVals["VALUE"];
			elseif($arPropVals["CODE"]=="APP"):
				$app = $arPropVals["VALUE"];
			elseif($arPropVals["CODE"]=="MKAD"):
				$mkad = $arPropVals["VALUE"];
			elseif($arPropVals["CODE"]=="COMMENT"):
				$comment = $arPropVals["VALUE"];
			elseif($arPropVals["CODE"]=="CITY2"):
				$city = $arPropVals["VALUE"];
			elseif($arPropVals["CODE"]=="COORDINATES"):
				$coord = $arPropVals["VALUE"];
			elseif($arPropVals["CODE"]=="ADRESNAM"):
				$AdresName = $arPropVals["VALUE"];
			elseif($arPropVals["CODE"]=="LIFT"):
				$lift = $arPropVals["VALUE"];
			endif;

		}
			
			  
	  $comment = str_replace("\r\n"," ",$comment);
	  $comment = str_replace("\n"," ",$comment);
	  
	  $ar_sales["USER_DESCRIPTION"] = str_replace(array("\r\n", "\n"), ' ', $ar_sales["USER_DESCRIPTION"]);
		
	  $adres0 = "$city|$street|$home"."©$address_is_correct"."©$coord"."©$podyezd|$lift|$stage|$homephone|$app|$mkad|$comment";
	  echo "ÀÄÐÅÑ{$i}: $adres0\r\n";
		
	  $check_adres = "$city|$street|$home";
		
	  if ($delivery_adres == $check_adres){
	  	$delivery_adres_print = $adres0;
		$adres_index = $i;	
	  }

	}

	   echo "ÊÎÄ ÀÄÐÅÑÀ: $adres_index\r\n";
	   echo "ÄÎÑÒÀÂÊÀ: $arProps[DELIV]©$arProps[TIME]©$delivery_adres_print\r\n";
	   echo "ÊÎÌÌÅÍÒÀÐÈÉ: {$ar_sales["USER_DESCRIPTION"]}\r\n";
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
			if($ar_res = $res->GetNext()) {
			  echo $ar_res['CODE']."©".$arItems["QUANTITY"]."\r\n";
			  if(substr($ar_res['CODE'], 0, 4) == 'syst') {
			  	$bOrderRepeat = true;
			  }
			}
		}
		

		if($bOrderRepeat == false) {
		    $DELIVERY_ITEM = "systDOSTLimit1";
		    
		    $ar_sales["PRICE"] -= $ar_sales['PRICE_DELIVERY'];
		    
		    if($ar_sales["PRICE"]>=500) {
		    		$DELIVERY_ITEM = "systDOSTLimit2";
		    }
		    if($ar_sales["PRICE"]>=1000) {
		    		$DELIVERY_ITEM = "systDOSTLimit3";
		    }
		    if($ar_sales["PRICE"]>=1500) {
		    		$DELIVERY_ITEM = "";
		    }
			if($arProps["MKAD"]>0){
				  echo "systMKAD©".$arProps["MKAD"]."\r\n";
				  if($DELIVERY_ITEM) {
				  	echo $DELIVERY_ITEM."©1\r\n";
				  }
			}
			else {
		        if($DELIVERY_ITEM) {
		  	   	echo $DELIVERY_ITEM."©1\r\n";
				  }
			}
		}

	   echo "ÊÎÍÅÖ ÇÀÊÀÇÀ\r\n";
	   
	   //CSaleOrder::StatusOrder($ar_sales["ID"], "S");
	   
}
echo "ÊÎÍÅÖ ÔÀÉËÀ";
header("Accept-Ranges: bytes");
header("Content-Length: ".ob_get_length());
header("Content-Type: text/html; charset=windows-1251");
ob_end_flush();
?>