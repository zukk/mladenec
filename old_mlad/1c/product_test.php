<?
			require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
			CModule::IncludeModule("sale");
			CModule::IncludeModule("catalog");
			CModule::IncludeModule("iblock");
							
			$arFilter = Array('IBLOCK_ID'=>"7", "INCLUDE_SUBSECTIONS"=>"Y");
			$arSelect = Array("ID", "CODE", "ACTIVE");
			$db_list = CIBlockElement::GetList(Array($by=>$order), $arFilter, false, false, $arSelect);
			while($ar_result = $db_list->GetNext()) {
				echo $ar_result["CODE"] . '©'.$ar_result['ACTIVE']. "\n";
			}
			
			
			
			die();
			//--осталось отфильтровать по статусу и все ок.!
			$arFilter = Array(
			   "USER_ID" => 27789,
			   "STATUS_ID" => 'F',
			   );
			$arGroupBy = array("SUM"=>"PRICE");
			$db_sales = CSaleOrder::GetList(array("DATE_INSERT" => "ASC"), $arFilter, $arGroupBy);
			$user_groups = array(14=>'stan', 12=>'silv', 8=>'gold', 7=>'plat');
			while ($ar_sales = $db_sales->Fetch())
			{
			  $group = 14;
			  
        if($ar_sales["PRICE"]>0) {
          $group = 12;
        }
        if($ar_sales["PRICE"]>=15000) {
          $group = 8;
        }
        if($ar_sales["PRICE"]>=30000) {
          $group = 7;
        }

        $user_groups_unset = $user_groups;
      	unset($user_groups_unset[$group]);
      	
      	$arGroups = CUser::GetUserGroup($arFilter["USER_ID"]);
      	$arGroups[] = $group;
        $arGroups = array_unique($arGroups);
        $arGroups = array_diff($arGroups, array_keys($user_groups_unset));
        
				$user = new CUser;
				
			  $fields = array("GROUP_ID" => $arGroups);
         print_r($fields);
				//$user->Update($arFilter["USER_ID"], $fields);
			}
			
			die();
			$p = "gots62222©до 5 кг (80 шт.) Размер NB©1044©48©1334|100|50|10©1©0©0©0©©0©0©0©0©1©27©1©1©Y";
			//echo $p;
			$report .= "$p \n";
			$arr = explode("©", $p);
			
			$bs = new CIBlockElement;
			$PROP = array();
			
			$arFilter = Array('IBLOCK_ID'=>"11", 'CODE'=>$arr[2]);
			$db_list = CIBlockElement::GetList(Array($by=>$order), $arFilter);
			if($ar_result = $db_list->GetNext()) {
				$PROP["BRAND"] = $ar_result["ID"];
			}
									
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
			

								
			$parents_id = array_shift(explode(',', $arr[15]));
			$arFilter = Array('IBLOCK_ID'=>"6", 'CODE'=>$parents_id);
			$db_list = CIBlockElement::GetList(Array($by=>$order), $arFilter);
			if($ar_result = $db_list->GetNext()) {
				$PROP["ITEM"] = $ar_result["ID"];
			}
									
			$arFields = Array(
			  "ACTIVE" => $arr[18],
			  "IBLOCK_ID" => "7",
			  "NAME" => $arr[1],
			  "CODE" => $arr[0],
			 );
									
			$arFilter = Array('IBLOCK_ID'=>"7", 'CODE' => $arr[3]);
			$db_list = CIBlockSection::GetList(Array(), $arFilter);
			if($ar_result = $db_list->GetNext()) {
				$arFields["IBLOCK_SECTION_ID"] = $ar_result["ID"];
				//echo "<pre>"; print_r($ar_result); echo "</pre>";
			}
		
			$arFilter = Array('IBLOCK_ID'=>$arFields["IBLOCK_ID"], 'CODE'=>$arr[0]);
			$res = CIBlockElement::GetList(Array($by=>$order), $arFilter);
			
			if($ob = $res->GetNextElement()):
			
				$ar_res = $ob->GetFields();
				$res = $bs->Update($ar_res["ID"], $arFields);
				$PRODUCT_ID = $ar_res["ID"];
				foreach($PROP as $code=>$val):
					CIBlockElement::SetPropertyValues($PRODUCT_ID, $arFields["IBLOCK_ID"], $val, $code);
				endforeach;
				
			else:
			
				$arFields["PROPERTY_VALUES"] = $PROP;
				$PRODUCT_ID = $bs->Add($arFields);
				
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
	
				$res = CPrice::GetList(
				        array(),
				        array(
				                "PRODUCT_ID" => $PRODUCT_ID,
				                "CATALOG_GROUP_ID" => $PRICE_TYPE_ID
				            )
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
		if($arr[17]=="" || !isset($arr[17])) $arr[17] = 0;
		
		if(CCatalogProduct::Add(array("ID"=>$PRODUCT_ID, "QUANTITY"=>$ITEM_QUANTITY, "WEIGHT"=>$arr[17])) ) echo "OK";
			
?>