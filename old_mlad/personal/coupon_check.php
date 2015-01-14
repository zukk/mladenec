<?php
require($_SERVER["DOCUMENT_ROOT"].
"/bitrix/modules/main/include/prolog_before.php");
CModule::IncludeModule("iblock");
CModule::IncludeModule("catalog");
CModule::IncludeModule("sale");
global $USER;
if (!($_SESSION["COUPON"]))
{
	$arSelect = Array("ID", "NAME", "PROPERTY_DESCOUNT_ID", "PROPERTY_USER_ID", "ACTIVE", "PROPERTY_ACTIVATION_DATE", "PROPERTY_MULTYUSE");
	$arFilter = Array("IBLOCK_ID"=> 1382, "NAME"=> trim($_POST["coupon"]));
	$res = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
	if($arRes = $res->GetNext())
	{
		
		
		if ($arRes["ACTIVE"] == "Y")
		{		
			if ($arRes["PROPERTY_MULTYUSE_VALUE"]=="Многоразовый")
				$active = "Y";
			else
				$active = "N";
		/* echo $active;
		p($arRes); die();	 */
			$el = new CIBlockElement;
			$PROP = array(
				"2076"=> $arRes["PROPERTY_DESCOUNT_ID_VALUE"], 
				"2077" => $USER->GetID(), 
				"2075"=>date("d.m.Y"), 
				"2082"=> $arRes["PROPERTY_MULTYUSE_VALUE"]
			);
			$arLoadProductArray = Array(
				"PROPERTY_VALUES"=> $PROP,
				"NAME"           => $arRes["NAME"],
				"ACTIVE"         => $active
			);
			$ELEMENT_ID = $arRes["ID"];  // изменяем элемент с кодом (ID) 2
			
			$el->Update($ELEMENT_ID, $arLoadProductArray);
					
			$res = CIBlockElement::GetList(array(), array("ID" => $arRes["PROPERTY_DESCOUNT_ID_VALUE"], "IBLOCK_ID" => 1383), array("ID", "NAME", "PROPERTY_TYPE", "PROPERTY_DESCOUNT", "PROPERTY_PRODUCT", "PROPERTY_COUPON_ITEM"));
			if($arDescount =  $res->GetNext())
			{
				$arProduct = explode(", ", $arDescount["PROPERTY_PRODUCT_VALUE"]); //массив элементов которые идут со скидкой
				$type = $arDescount["PROPERTY_TYPE_VALUE"] ? "pct" : "summ"; //тип скидки (поцент/цена)
				$descount = $arDescount["PROPERTY_DESCOUNT_VALUE"]; //наминал скидки.
				$name = $arDescount['NAME'];
				$coupon_id = $arDescount["PROPERTY_COUPON_ITEM_VALUE"]; 
			}
		//	работа с корзиной.	
			
			$_SESSION["COUPON"][] = array("value" => trim($_POST["coupon"]), "id" => $ELEMENT_ID, "Descount" => array("descount" =>$descount, "type" => $type, "product" => $arProduct, "name" => $name,  "id" => intval($coupon_id)));
			//echo "Купон активирован";
		
		}else{
			$_SESSION["COUPON_MSG"] = "Купон с таким номером уже использован."; 			
		}
		

	}else{
		$_SESSION["COUPON_MSG"] = "Купон с таким номером отсутсвует.";
	}
}
header("location:".$_SERVER["HTTP_REFERER"]);
?>