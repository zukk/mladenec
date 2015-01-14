<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
die();
CModule::IncludeModule("iblock");
CModule::IncludeModule("catalog");


$arFilter = Array('IBLOCK_ID'=>7, 'INCLUDE_SUBSECTIONS'=>'Y', 'ACTIVE'=>'Y');
$db_list = CIBlockElement::GetList(Array($by=>$order), $arFilter);
$bs = new CIBlockElement;
$i=0;
while($ar_result = $db_list->GetNext()){
	$i ++;
	$res = $bs->Update($ar_result["ID"], array('ACTIVE'=>0), false, false);
	
}
echo "Проставлено {$i} товаров";
die();



$bs = new CIBlockElement;
$p = "67120chicco©0©3015|0|2894.4|0©N";
$arr = explode("©", $p);
$PRODUCT_ID = 56013;
					$arFilter = Array('IBLOCK_ID'=>"7", 'CODE'=>$arr[0]);
					$arFields = Array("ACTIVE" => ($arr[3] == "Y")  ?  'Y' : 0);
					print_r($arFields);
					$res = $bs->Update($PRODUCT_ID, $arFields, false, false);
					var_dump($res);
					die();
die();
CModule::IncludeModule("catalog");


	$arSelect = Array("ID", "CODE");
	$db_list = CIBlockElement::GetList(Array($by=>$order), $arFilter, false, false, $arSelect);
	while($ar_result = $db_list->GetNext()) {
		$BRANDS[$ar_result["CODE"]] = $ar_result["ID"];
	}
	$arFilter = Array('IBLOCK_ID'=>"7");
	$arSelect = Array("ID", "CODE", "NAME");
	$db_list = CIBlockElement::GetList(Array($by=>$order), $arFilter, false, false, $arSelect);
	while($ar_result = $db_list->GetNext()) {
		$ITEMS[$ar_result["CODE"]]["ID"] = $ar_result["ID"];
		$ITEMS[$ar_result["CODE"]]["NAME"] = $ar_result["NAME"];
	}
	$bs = new CIBlockElement;		
	$p = '202040©2 с 5 мес.©4083©529©264|261.4|256.1|248.2©0©0©0©0©©0©0©0©0©1©529©6©1©Y';
	$arr = explode("©", $p);


	$PROP = array();

	$PROP["BRAND"] = isset($BRANDS[$arr[2]]) ? $BRANDS[$arr[2]] : 0;
							
	$PROP["INSIGHT"] = $arr[5];
	$PROP["BIG"] = $arr[6]; // Крупногабаридка, разница в скидках
	$PROP["NEW"] = $arr[7];
	$PROP["DISCOUNT"] = $arr[8];
	$PROP["BEST"] = $arr[10];
	$PROP["OFF"] = $arr[11];
	$PROP["RECOMMENDED"] = $arr[12];
	$PROP["LITRES"] = $arr[13];
	$PROP["AGE"] = $arr[14];
	$PROP["BOX"] = $arr[16];
						
	$arFields = Array(
	  "ACTIVE" => $arr[18],
	  "IBLOCK_ID" => "7",
	  "NAME" => $arr[1],
	  "CODE" => $arr[0],
	  "SORT" => $arr[19],
	 );
							
	$arFields["IBLOCK_SECTION"] = $arFields["IBLOCK_SECTION_ID"] = isset($SECTIONS[$arr[3]]) ? $SECTIONS[$arr[3]] : 0;
	
	if(isset($ITEMS[$arr[0]])):
		$PRODUCT_ID = $ITEMS[$arr[0]]["ID"];
		$NAME = $ITEMS[$arr[0]]["ID"];
		if($NAME!=$arFields["NAME"]) {
			$PROP["_modify_item"] = 12171;
			$PROP["_supervisor"] = 0;
		}
		unset($ITEMS[$arr[0]]);

		if($res = $bs->Update($PRODUCT_ID, $arFields, false, false)) {
			foreach($PROP as $code=>$val):
				CIBlockElement::SetPropertyValues($PRODUCT_ID, $arFields["IBLOCK_ID"], $val, $code);
			endforeach;
		}
		else {

		}
	
	else:
		$PROP["_new_item"] = 12170;						
		$arFields["PROPERTY_VALUES"] = $PROP;
		$PRODUCT_ID = $bs->Add($arFields, false, false);
		
	endif;
	
$PRICE_TYPE_IDs = array();
$prices = explode('|', $arr[4]);

$PRICE_TYPE_IDs[1] =  $prices[0]; //$PROP["BASE"] 		
$PRICE_TYPE_IDs[5] =  $prices[1]; //$PROP["SILVER"] 	
$PRICE_TYPE_IDs[4] =  $prices[2]; //$PROP["GOLD"] 
$PRICE_TYPE_IDs[6] =  $prices[3]; //$PROP["PLATINUM"]
	
$ITEM_QUANTITY = ($arr[5]>0) ? 9999999 : 0;

foreach ($PRICE_TYPE_IDs as $PRICE_TYPE_ID=>$PRICE) {
	$arFields = Array(
					"PRODUCT_ID" => $PRODUCT_ID,
					"CATALOG_GROUP_ID" => $PRICE_TYPE_ID,
					"PRICE" => $PRICE,
					"CURRENCY" => "RUB",
					);
					
	//--Поищем в массиве				
	if(isset($ITEM_PRICES[$PRODUCT_ID][$PRICE_TYPE_ID])) {
    	CPrice::Update($ITEM_PRICES[$PRODUCT_ID][$PRICE_TYPE_ID], $arFields);
    	unset($ITEM_PRICES[$PRODUCT_ID][$PRICE_TYPE_ID]);
	}
	//--Убедимся еще раз что такого элемента нет
	else {
		$res = CPrice::GetList(
		        array(),
		        array(
	                "PRODUCT_ID" => $PRODUCT_ID,
	                "CATALOG_GROUP_ID" => $PRICE_TYPE_ID
		            	),
            false, 
            false,
        		Array("ID")
		    );
    
		if ($arr_price = $res->Fetch())
		{
			CPrice::Update($arr_price["ID"], $arFields);
		}
		else
		{
			CPrice::Add($arFields);
		}
	}
}				
die();
die();













$PRODUCT_ID = 51794;
$ITEM_QUANTITY = 8;
$ar_res = CCatalogProduct::GetByID(
 $PRODUCT_ID
);
print_r($ar_res);
if((CCatalogProduct::Update(array("ID"=>$PRODUCT_ID, "QUANTITY"=>$ITEM_QUANTITY))))
{
	echo 'add';
	CCatalogProduct::Add(array("ID"=>$PRODUCT_ID, "QUANTITY"=>$ITEM_QUANTITY));
}
echo 123;

CModule::IncludeModule("catalog");
						$arFields = Array(
						  "IBLOCK_ID" => "7",
						 );

						$arFilter = Array('IBLOCK_ID'=>$arFields["IBLOCK_ID"], 'PROPERTY__new_item'=>false);
						$db_list = CIBlockElement::GetList(Array($by=>$order), $arFilter, false, false, array('ID'));
						while($ar_result = $db_list->GetNext())
						{
							print_r($ar_result);
							
							$PROP = array();
							$PROP["_new_item"] = 12170;
							foreach($PROP as $code=>$val):
								CIBlockElement::SetPropertyValues($ar_result['ID'], $arFields["IBLOCK_ID"], $val, $code);
							endforeach;
						}
						
						die();
				$res = CPrice::GetList(
				        array(),
				        array(
//			                "CATALOG_GROUP_ID" => array(1,5, 4, 6)
				            	),
		            false, 
		            false,
    						Array("PRODUCT_ID", "ID", "CATALOG_GROUP_ID")
				    );
				while ($arr_price = $res->Fetch())
				{
					/*if(!in_array($arr_price["PRODUCT_ID"], $ITEMS)) {
						CPrice::Delete($arr_price["ID"]);
						echo 'del';
					}*/
					print_r($arr_price);
				}
?>