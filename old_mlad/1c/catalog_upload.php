<? require($_SERVER["DOCUMENT_ROOT"]."/odinc/auth.php");?>
<?
$time = time();
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

function SetEnumValues($prop_id=0, $enum_values=array())
{

	$update_enums = array();
	$property_enums = CIBlockPropertyEnum::GetList(Array("DEF"=>"DESC", "SORT"=>"ASC"), Array("PROPERTY_ID"=>$prop_id));
	while($enum_fields = $property_enums->GetNext()) {
		$update_enums[$enum_fields['EXTERNAL_ID']] = $enum_fields;
	}

	foreach ($enum_values as $enum_value) {
	  $ibpenum = new CIBlockPropertyEnum;
		$arr = explode('|', $enum_value);
		$arFields = Array(
			'PROPERTY_ID'=>$prop_id,
			'XML_ID'=>$arr[0], 
			'VALUE'=>$arr[1], 
			'SORT'=>$arr[2]
		);
		if(isset($update_enums[$arr[0]]))	{
			$ibpenum->Update($update_enums[$arr[0]]["ID"], $arFields);
			unset($update_enums[$arr[0]]);
		}
		else {
			$PropID = $ibpenum->Add($arFields);
		}
	}
	
  $ibpenum = new CIBlockPropertyEnum;
	foreach($update_enums as $del_enum)	{
		$ibpenum->Delete($del_enum["ID"], $arFields);
	}
}
function printTime($line=0)
{
	global $time;
	echo 'Время: '.$line.'-'.(time()-$time).'<br />';
}

CModule::IncludeModule("sale");
CModule::IncludeModule("catalog");
ob_start();



if (empty($_REQUEST['action']) || !in_array($_REQUEST['action'], array('product_light', 'product', 'catalog', 'manufacturers', 'clients', 'clients_new', 'orders', 'delete', 'delete_new', 'filter_cat', 'filter_cat_val', 'filter_val', 'filter_goods', 'catalog_new', 'product_new', 'users'))) exit('Wrong action.');

$fp = fopen('php://input', 'r');
$report = date("H:i:s")."<br>";

$counter = 0;


CModule::IncludeModule("iblock");

switch($_REQUEST['action']){
			
		case "catalog": //ПРОТОКОЛ №1 Импорт кталога
//		$fp = fopen("group.txt", "r");
		
/*//	$arFilter["CODE"] = 408;
	$rsSections = CIBlockSection::GetList($arSort, $arFilter);
	while($arSection = $rsSections->GetNext())
	{
		$arSection["PICTURE"] = CFile::GetFileArray($arSection["PICTURE"]);

		$arResult["SECTIONS"][]=$arSection;
		$arFilter["SECTION_ID"][] = $arSection["ID"];
		$DB->StartTransaction();	{
			if(!CIBlockSection::Delete($arSection["ID"]))	{
				$strWarning .= 'Error.';		$DB->Rollback();
			}
				else		{
					$DB->Commit();
				}
		}
	}*/
				while (!feof($fp)):

						$p = trim(fgets($fp));
						//$p = "CODE@catalog name@parent_id@Sort@Y@FiltersID[,,,,]@BrandsID[,,,,]";
						$report .= "$p \n";
						$arr = explode("©", $p);
						$arFields = Array(
						  "ACTIVE" => ($arr[4] == "Y")  ?  'Y' : false,
						  "IBLOCK_ID" => "7",
						  "IBLOCK_SECTION_ID" => "0",
						  "NAME" => $arr[1],
						  "CODE" => $arr[0],
						  "SORT" => $arr[3]*1000,
						  "UF_FILTERS" => $arr[5],
						  "UF_BRANDS" => $arr[6],
						 );
						
						if($arr[0]) {	
							$arFilter = Array('IBLOCK_ID'=>$arFields["IBLOCK_ID"], 'CODE'=>$arr[0]);
							$db_list = CIBlockSection::GetList(Array($by=>$order), $arFilter);
	
							$bs = new CIBlockSection;
							
							if($ar_result = $db_list->GetNext()) {
								if($arr[2]) {
									$section_list = CIBlockSection::GetList(Array($by=>$order), Array("CODE"=>$arr[2]));
									if($section_result = $section_list->GetNext()) {
										$arFields["IBLOCK_SECTION_ID"] = $section_result["ID"];
										//$arFields["SORT"]= $arFields["SORT"] * ($section_result["DEPTH_LEVEL"]+1000);
									}
								}
								$res = $bs->Update($ar_result["ID"], $arFields, false, false);
								if ($bs->LAST_ERROR)
									echo $bs->LAST_ERROR;
								else
									echo "ok \n\r";
							}
							 else {
								if($arr[2]) {
									$section_list = CIBlockSection::GetList(Array($by=>$order), Array("CODE"=>$arr[2]));
									if($section_result = $section_list->GetNext()) {
										$arFields["IBLOCK_SECTION_ID"] = $section_result["ID"];
										//$arFields["SORT"] 						 = $arFields["SORT"] * ($section_result["DEPTH_LEVEL"]+1000);
									}
								}
								$ID = $bs->Add($arFields, false, false);
							}  
						} 
						//--Удалим все объекты и массивы
						unset($p);
						unset($arr);
						unset($arFields);
						unset($db_list);
						unset($ar_result);
						unset($bs);
						$report .= "OK \n";

				endwhile;
				
				$bs = new CIBlockSection;
				CIBlockSection::ReSort(7);
				unset($bs);
				
				break;
				
		case "manufacturers":
		
				while (!feof($fp)):
				//echo "!!!";
						$p = trim(fgets($fp));
						$report .= "$p \n";
						//$p = "1253©Sun Herbal фито*©Y©SORT";
						$arr = explode("©", $p);
						
						$arFields = Array(
						  "ACTIVE" => ($arr[2] == "Y")  ?  'Y' : false,
						  "IBLOCK_ID" => "11",
						  "NAME" => $arr[1],
						  "CODE" => $arr[0],
						  "SORT" => $arr[3],
						 );
						
						
						$arFilter = Array('IBLOCK_ID'=>11, 'CODE'=>$arr[0]);
						$db_list = CIBlockElement::GetList(Array($by=>$order), $arFilter);
						$bs = new CIBlockElement;
						
						if($ar_result = $db_list->GetNext()):
							//echo "UPDATE"; 
							$res = $bs->Update($ar_result["ID"], $arFields, false, false);
						else:
							//echo "ADD"; pre($arFields);
							$ID = $bs->Add($arFields);
						endif;
						
				endwhile;
		
		break;
		case "product":
				$BRANDS = array();
				$ITEMS	= array();
				$ITEM_PRICES	= array();
				$SECTIONS = array();
				$arFilter = Array('IBLOCK_ID'=>"11");
				$arSelect = Array("ID", "CODE");
				$db_list = CIBlockElement::GetList(Array($by=>$order), $arFilter, false, false, $arSelect);
				while($ar_result = $db_list->GetNext()) {
					$BRANDS[$ar_result["CODE"]] = $ar_result["ID"];
				}

				$arFilter = Array('IBLOCK_ID'=>"7");
				$db_list = CIBlockSection::GetList(Array(), $arFilter);
				while($ar_result = $db_list->GetNext()) {
					$SECTIONS[$ar_result["CODE"]] = $ar_result["ID"];
				}


				$arFilter = Array('IBLOCK_ID'=>"7");
				$arSelect = Array("ID", "CODE", "NAME");
				$db_list = CIBlockElement::GetList(Array($by=>$order), $arFilter, false, false, $arSelect);
				while($ar_result = $db_list->GetNext()) {
					$ITEMS[$ar_result["CODE"]]["ID"] = $ar_result["ID"];
					$ITEMS[$ar_result["CODE"]]["NAME"] = $ar_result["NAME"];
				}
				
				//--Закешируем цены и удалим ненужные
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
					$ITEM_PRICES[$arr_price["PRODUCT_ID"]][$arr_price["CATALOG_GROUP_ID"]] = $arr_price["ID"];
				}

				$bs = new CIBlockElement;

				while (!feof($fp)):
						$p = trim(fgets($fp, 256));
						
						/*$to = 0;
						$from = 500;
						$counter++;
	
						if(($counter < $to)){
								continue;
						}
						if(($counter >= $to+$from)) {
							break;
							die();
						}*/
						
						//$p = "goskboy4040©Трусики Goon Гун для мальчиков 12-20 кг. 40 шт. (BIG)©1044©48©929©1©0©0©0©ART-PODARKA©0©0©0©0©6©29©12©Y";
						//2genki©до 5 кг (84 шт.) Размер NB©1029©448©889|871.2|862.3|844.5©0©0©0©0©©0©0©0©0©1©448©1©1©Y
						//""+СокрЛП(ИдПодГрупп)+"©"+ИмПодГрупп+"©"+ИдРод+"©"+Спр.Сортировка+"©"+Активность+"©"+СтрокаФильтров1+"©"+Бренды;
						$report .= "$p \n";
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
						  "ACTIVE" => ($arr[18] == "Y")  ?  'Y' : false,
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
					if($arr[17]=="" || !isset($arr[17])) $arr[17] = 0;
					
					if(CCatalogProduct::Add(array("ID"=>$PRODUCT_ID, "QUANTITY"=>$ITEM_QUANTITY, "WEIGHT"=>$arr[17])) )
					{
						
					}
					unset($arFields);
					unset($res);
					unset($PROP);
					unset($PRICE_TYPE_IDs);
					unset($prices);
					unset($arr_price);

				endwhile;
				CIBlockSection::ReSort(7);
		unset($BRANDS);
		unset($SECTIONS);
		unset($ITEM_PRICES);
		unset($ITEMS);
		break;

		case "product_light": //Обновление только цен и остатков
			ob_start();
			$bugs = array();
			while (!feof($fp)):
	
					$p = trim(fgets($fp));
					
					//$p = "артикул©наличие©цена базовая|цена сереб|цена зол|цена плат©активность";
				
					$arr = explode("©", $p);
					$arFilter = Array('IBLOCK_ID'=>"7", 'CODE'=>$arr[0]);
					$arFields = Array("ACTIVE" => ($arr[3] == "Y")  ?  'Y' : 0);

											 
					$PRICE_TYPE_IDs = array();
					$prices = explode('|', $arr[2]);
 					$PRICE_TYPE_IDs[1] =  $prices[0]; //$PROP["BASE"] 		
					$PRICE_TYPE_IDs[5] =  $prices[1]; //$PROP["SILVER"] 	
					$PRICE_TYPE_IDs[4] =  $prices[2]; //$PROP["GOLD"] 
					$PRICE_TYPE_IDs[6] =  $prices[3]; //$PROP["PLATINUM"]
					$res = CIBlockElement::GetList(Array($by=>$order), $arFilter);
					if($ob = $res->GetNextElement()) {
						$ar_res = $ob->GetFields();
						print_r($ar_res);
						$el = new CIBlockElement;
						$PRODUCT_ID = $ar_res["ID"];
						$ITEM_QUANTITY = ($arr[1]>0) ? 9999999 : 0;
						
						$el->Update($PRODUCT_ID, $arFields, false, false);
						$PROP = array();
						$PROP["INSIGHT"] = $ITEM_QUANTITY;
						foreach($PROP as $code=>$val):
									CIBlockElement::SetPropertyValues($PRODUCT_ID, $arFilter["IBLOCK_ID"], $val, $code);
								endforeach;
						unset($arFields);						
						
						CCatalogProduct::Add(array("ID"=>$PRODUCT_ID, "QUANTITY"=>$ITEM_QUANTITY));
//						if(!(CCatalogProduct::Update(array("ID"=>$PRODUCT_ID, "QUANTITY"=>$ITEM_QUANTITY))))
//						{
//							CCatalogProduct::Add(array("ID"=>$PRODUCT_ID, "QUANTITY"=>$ITEM_QUANTITY));
//						}
						foreach ($PRICE_TYPE_IDs as $PRICE_TYPE_ID=>$PRICE) {
							if($PRICE>0) {
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
						
						print_r($arFields);
					}
					else {
						$bugs[] = $arr[0];
					}
				
			endwhile;
			
			print_r($bugs);
			
			writeReportLog(ob_get_contents(), 'p_light');
			echo implode('@', $bugs);
			die();
		break;	
		case "delete":// Деактивирование элементов Цены
		
				while (!feof($fp)):
		
						$p = trim(fgets($fp));
						
						//$p = "CODE©CODE_CATALOG©NAME1111©DESCRIPTION";
						
						$arFilter = Array('IBLOCK_ID'=>"7", 'CODE'=>"$p");
						$res = CIBlockElement::GetList(Array($by=>$order), $arFilter);
						if($ob = $res->GetNextElement()) {
							$ar_res = $ob->GetFields();
							$el = new CIBlockElement;
							$el->Update($ar_res["ID"], Array("ACTIVE"=>false));
						}
						
						
				endwhile;
		
		break;

		case "filter_cat_val": //Добавление и обновление фильтров и их значений
			
			while (!feof($fp)) {
	
				$p = trim(fgets($fp));
				$arr = explode("©", $p);
				
				$enum_values = array();
				preg_match_all('|\[(.*)\]|U', $arr[3], $enum_values);
				$enum_values = isset($enum_values[1]) ? $enum_values[1] : array();
				
				$res = CIBlock::GetList(Array(), Array("CODE"=>"$arr[0]"));
				if($ar_res = $res->Fetch()) {
					$properties = CIBlockProperty::GetList(Array("sort"=>"asc", "name"=>"asc"), Array("IBLOCK_ID"=>$ar_res['ID']));
							
					if($prop_fields = $properties->GetNext()) {
						SetEnumValues($prop_fields['ID'], $enum_values);
					}
				}
				else {
					$ib = new CIBlock;
					$arFields = Array(
					  "ACTIVE" => "Y",
					  "NAME" => $arr[2],
					  "CODE" => $arr[0],
					  "IBLOCK_TYPE_ID" => "prop",
					  "SITE_ID" => Array("s1"),
					  "SORT" => "500",
					);
					
					$ID = $ib->Add($arFields);
					CIBlock::SetPermission($ID, Array("6"=>"X", "2"=>"R"));
					
					$arFields = Array(
					  "NAME" => "Значения",
					  "ACTIVE" => "Y",
					  "SORT" => "100",
					  "CODE" => "VALUE",
					  "PROPERTY_TYPE" => "L",
					  "IBLOCK_ID" => $ID,
					  "MULTIPLE" => "Y",
					  );
						
						$ibp = new CIBlockProperty;
						if(!($prop_id=$ibp->Add($arFields))) { echo $ibp->LAST_ERROR; break; }
				
						SetEnumValues($prop_id, $enum_values);
					}
			}
			break;
			
		case "filter_goods":
//				$fp = fopen("filters.txt", "r"); 
				while (!feof($fp)){
				
					echo "GO";
					$p = trim(fgets($fp));
					$arr = explode("©", $p);
					if(empty($arr[0]) || empty($arr[1])) continue;
					$enum_select = trim($arr[2]) ? explode('|', trim($arr[2])) : false;
					$update_enums = array();
					$arSelect = Array("ID");
					$res = CIBlock::GetList(Array(), Array('TYPE'=>'prop', 'SITE_ID'=>'s1', 'ACTIVE'=>'Y', "CODE"=>$arr[1]));
					if($ar_res = $res->Fetch())	{
						$IBID = $ar_res['ID']; //ID ИНФОБЛОКА!!!
						$ITEMID = 0;
						if($enum_select && count($enum_select) && $enum_select[0]) {
							$property_enums = CIBlockPropertyEnum::GetList(Array("DEF"=>"DESC", "SORT"=>"ASC"), Array("EXTERNAL_ID"=>$enum_select));
							$update_enums = array();
							while($enum_fields = $property_enums->GetNext()) {
								$update_enums[] = $enum_fields['ID'];
							}
						}

						$arFilter = Array("IBLOCK_ID"=>$IBID, "CODE"=>$arr[0]);

						$res = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
						if($ob = $res->GetNextElement()) {
							$ar_res = $ob->GetFields();
						  $ITEMID = $ar_res["ID"];
						}
						else {
							$arFields = Array(
								  "ACTIVE" => "Y",
								  "IBLOCK_ID" => $IBID,
								  "NAME" => $arr[3] ? $arr[3] : 12345,
								  "CODE" => $arr[0],
								 );
								 $el = new CIBlockElement;
								 $ITEMID = $el->Add($arFields, false, false);
								 unset($el);
						}
						if($ITEMID) {
							CIBlockElement::SetPropertyValueCode($ITEMID, "VALUE", $update_enums);
						}
					}
					unset($res);
					unset($ob);
					unset($arFilter);
					unset($arFields);
					unset($ar_res);
					unset($property_enums);
				}
		break;
		
		case "users": 
				
				while (!feof($fp)):
					$id = trim(fgets($fp));
					
					$rsUsers = CUser::GetList(($by="id"), ($order="desc"), Array("XML_ID"=>$id)); // выбираем пользователей
					
					if($arUsers=$rsUsers->GetNext()):
						//$id = 1;
							$user = new CUser;
							$fields = Array(
							  "UF_VALID"  => "Y",
							  );
							if($user->Update($arUsers["ID"], $fields)) echo "Пользователь ID=".$arUsers["ID"]." валиден - OK /r\n";
					endif;
							
							
				endwhile;
		
		break;
		
		
		default:
			$report.= "Не верно указан параметр action \n";
}

echo '<strong>Время выполнения скрипта: '.(time()-$time).' секунд.</strong>';
//$report = str_replace("\n", "<br>", $report);
//echo $report;
//$fp1 = fopen($_REQUEST['action']."_log.htm", "w");
//fwrite($fp1, "$report");
//fclose($fp1);
//die();
?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>