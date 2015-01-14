<?require($_SERVER["DOCUMENT_ROOT"]."/odinc/auth.php");?>
<? require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
//error_reporting(E_ALL);
ini_set('display_errors','On');
ob_start();
CModule::IncludeModule("sale");
CModule::IncludeModule("iblock");
CModule::IncludeModule("catalog");
$fp = fopen('php://input', 'r');
$report = date("H:i:s")."<br>";

$counter = 0;

if(!$USER->IsAuthorized()) $USER->Authorize(5);

	//--�������� ��� �������� ������ "[1] ����� ��������" �������� � ������ ������������
	
	$arrSaleOrderProps = array();

	$arFilter = array('PERSON_TYPE_ID' => 1, 'PROPS_GROUP_ID'=>1, 'USER_PROPS'=>'Y',/* '!CODE'=>'ADRESNAM'*/);
	$dbProperties = CSaleOrderProps::GetList(
			array(),
			$arFilter,
			false,
			false,
			array("ID", "NAME", "CODE")
		);
  	   while ($arProperties = $dbProperties->GetNext()){
  	   	$arrSaleOrderProps[$arProperties['CODE']]['ID'] = $arProperties['ID'];
  	   	$arrSaleOrderProps[$arProperties['CODE']]['CODE'] = $arProperties['CODE'];
  	   	$arrSaleOrderProps[$arProperties['CODE']]['NAME'] = $arProperties['NAME'];
  	   }

  	   unset($dbProperties);
  	   unset($arProperties);
  	   unset($arFilter);
  	   
  	   
  	   
//������������ ��� 1�
//$fp = fopen("order.txt", "r");
while (!feof($fp)):
	$p = trim(fgets($fp));
	writeReportLog($p, 'minus_order');
	//echo $p."\n<br>";
	if($p=="�����"){
		$arrOrder = array();
	}
	elseif($p=="�����������"){	
		//echo "END";		
	
		$arFields = array(
		  "USER_ID" => $arrOrder["USER_ID"],
		  "EMP_PAYED_ID" => $arrOrder["MANAGER_ID"],
		  "EMP_STATUS_ID" => $arrOrder["MANAGER_ID"],
		  "STATUS_ID" => $arrOrder["ORDER_STATUS"],
		  "DATE_ALLOW_DELIVERY" => $arrOrder["DATE_DELIV"],
		  "ALLOW_DELIVERY" => "Y",
		  "EMP_ALLOW_DELIVERY_ID"=> $arrOrder["MANAGER_ID"],
		  "DISCOUNT_VALUE" => $arrOrder["DISCOUNT"],
		  "PRICE" => $arrOrder["ORDER_SUM"],
		  "PRICE_DELIVERY" => $arrOrder["PRICE_DELIVERY"],
		  //"COMMENTS" => $arrOrder["TIME1"]."-".$arrOrder["TIME2"],
	   );
		
	   $arOrder = CSaleOrder::GetByID($arrOrder["ORDER_ID"]);
	   $arr = explode("�", $arOrder["COMMENTS"]);

	   if($arrOrder["USER_PROPS_ID"] < 0 || (CSaleOrderUserProps::GetByID($arrOrder["USER_PROPS_ID"]) == false)) {
	   	$arFieldsNewOrder = array(
									"NAME" => 'odinc '.Date("Y-m-d H:i"),
									"USER_ID" => IntVal($arrOrder["USER_ID"]),
									"PERSON_TYPE_ID" => 1 //��� �����������, ���� �������, ��� ��� ������������ �� ��������� ������ ���� "������"
								  );
	   	$arrOrder["USER_PROPS_ID"] = IntVal(CSaleOrderUserProps::Add($arFieldsNewOrder));
	   	$arrOrder["ADDRESS"]['ADRESNAM'] = $arFieldsNewOrder['NAME'];

	   	unset($arFieldsNewOrder);
	   }
	   else {
	   		$arr = CSaleOrderUserProps::GetByID($arrOrder["USER_PROPS_ID"]);
	   		$arrOrder["ADDRESS"]['ADRESNAM'] = $arr['NAME'];
	   }

	   
	   $arFields["COMMENTS"] = $arrOrder["USER_PROPS_ID"]."�".$arrOrder["TIME1"]."-".$arrOrder["TIME2"];
	   
	   CSaleOrder::Update($arrOrder["ORDER_ID"], $arFields); //��������� ����� �������� ������
	   var_dump(isUserGroup(8, $arrOrder["USER_ID"]));
	   if(isUserGroup(8, $arrOrder["USER_ID"]) === false) {// �������� �� ��������� �� ������ ������� ������ ���.
	   
	   		echo  $arrOrder["USER_ID"] . ' �� ����� ������� ������� ������';
		   //--�������� ����� �� ������� ������ �������
	        $status_info = '��� ������ &laquo;�������&raquo;<span class="text">�������� ��������� ������� �� ������� <span>&laquo;������� ������&raquo; '.FormatCurrency((40000 - $ar_sales["PRICE"]), "RUB"). '</span></span>';
	        	$arFilter = Array(
				   "USER_ID" => $arrOrder["USER_ID"],
				   "STATUS_ID" => 'F',
				   );
				$arGroupBy = array("SUM"=>"PRICE");
				$db_sales = CSaleOrder::GetList(array("DATE_INSERT" => "ASC"), $arFilter, $arGroupBy);
				$user_groups = array(14=>'stan', 12=>'silv', 8=>'gold', 7=>'plat');
				if ($ar_sales = $db_sales->Fetch())
				{
					$group = 14;
					$status_info = '��� ������ &laquo;�������&raquo;<span class="text">�������� ��������� ������� �� ������� <span>&laquo;������� ������&raquo; '.FormatCurrency((40000 - $ar_sales["PRICE"]), "RUB"). '</span></span>';
					if($ar_sales["PRICE"]>=20000) {
						$group = 8;
						$status_info = '��� ������ &laquo;������� ������&raquo;';
					}
	       
	        $user_groups_unset = $user_groups;
	      	unset($user_groups_unset[$group]);
	      	$systemGroup = array_search(2, $user_groups);
			unset($user_groups_unset[$systemGroup]);
	      	
	      	$arGroups = CUser::GetUserGroup($arFilter["USER_ID"]);
	      	$arGroups[] = $group;
	        $arGroups = array_unique($arGroups);
	        $arGroups = array_diff($arGroups, array_keys($user_groups_unset));
	        
					
				  $fields = array("GROUP_ID" => $arGroups, "UF_ORDERS_SUMM"=>$ar_sales["PRICE"], "UF_STATUS_INFO"=>$status_info, "UF_VALID"  => false);
					$user = new CUser;
					$user->Update($arFilter["USER_ID"], $fields);
				}
	   }
	   else {
					$fields = array("UF_STATUS_INFO"=>'��� ������ &laquo;������� ������&raquo;');
					$user = new CUser;
					$user->Update($arFilter["USER_ID"], $fields);
	   		}
	   		
	   		print_r($arFilter);
	   		print_r($fields);
	   		
	  //--��������� ���������� � �������
		$db_vals = CSaleOrderPropsValue::GetList(array("SORT" => "ASC"),	array("ORDER_ID" => $arrOrder["ORDER_ID"],	"CODE" => "MAN_ID"));
		if ($arVals = $db_vals->Fetch()) {
			CSaleOrderPropsValue::Update($arVals['ID'], array("VALUE"=>$arrOrder["MAN_ID"]));
		}
		else {
			CSaleOrderPropsValue::Add(array("ORDER_ID" => $arrOrder["ORDER_ID"], "ORDER_PROPS_ID" => "24", "CODE"=>"MAN_ID", "NAME"=>"������", "VALUE" => $arrOrder["MAN_ID"]));
		}
		//--��������� ������ � ������� ��������
		$db_vals = CSaleOrderPropsValue::GetList(array("SORT" => "ASC"),	array("ORDER_ID" => $arrOrder["ORDER_ID"],	"CODE" => "TIME"));
		if ($arVals = $db_vals->Fetch()) {
			//CSaleOrderPropsValue::Delete($arVals['ID']);
		}
		$db_vals = CSaleOrderPropsValue::GetList(array("SORT" => "ASC"),	array("ORDER_ID" => $arrOrder["ORDER_ID"],	"CODE" => "TIME_CURRENT"));
		if ($arVals = $db_vals->Fetch()) {
			CSaleOrderPropsValue::Update($arVals['ID'], array("VALUE"=>$arrOrder["TIME1"]."-".$arrOrder["TIME2"]));
		}
		else {
			CSaleOrderPropsValue::Add(array("ORDER_ID" => $arrOrder["ORDER_ID"], "ORDER_PROPS_ID" => "77", "CODE"=>"TIME_CURRENT", "NAME"=>"���� ��������", "VALUE" => $arrOrder["TIME1"]."-".$arrOrder["TIME2"]));
		}
		
		
		
	   //��������� �����
	   echo "��������� �����";
	   //var_dump($arrOrder['USER_PROPS_ID']);
	   
	   $arrOrder["ADDRESS"]['CITY2'] = trim($arrOrder["ADDRESS"]['CITY2']);
	   $arrOrder["ADDRESS"]['STREET'] = trim($arrOrder["ADDRESS"]['STREET']);
	   echo $arrOrder["ADDRESS"]["MKAD"];
	   //var_dump($arrOrder);
	   if(($arrOrder['USER_PROPS_ID'] > 0) && !empty($arrOrder["ADDRESS"]['CITY2']) && !empty($arrOrder["ADDRESS"]['STREET'])) 
	   {
			//echo "TROLOLO";
		   foreach ($arrSaleOrderProps as $arrSaleOrderProp) 
		   {

		   		$value_prop = $arrOrder['ADDRESS'][$arrSaleOrderProp['CODE']];
		   		$arSaleOrderPropsValue = array("ORDER_ID" => $arrOrder["ORDER_ID"], "ORDER_PROPS_ID" => $arrSaleOrderProp['ID'], "CODE"=>$arrSaleOrderProp['CODE'], "NAME"=>$arrSaleOrderProp['NAME'], "VALUE" => $value_prop);
		   		$arSaleOrderPropsValues[$arrSaleOrderProp['CODE']] = $arSaleOrderPropsValue;
				
		   		//--�������/������� ����������� � ������� ������������
		   		$result = mysql_query("SELECT `USER_PROPS_ID` FROM b_sale_user_props_value WHERE USER_PROPS_ID='{$arrOrder['USER_PROPS_ID']}' AND ORDER_PROPS_ID='{$arSaleOrderPropsValue['ORDER_PROPS_ID']}'");
		   		
				if($row = mysql_fetch_array($result)) 
				{
					mysql_query("UPDATE b_sale_user_props_value SET VALUE='$value_prop' WHERE USER_PROPS_ID='{$arrOrder['USER_PROPS_ID']}' AND ORDER_PROPS_ID='{$arSaleOrderPropsValue['ORDER_PROPS_ID']}'") or die("ERROR");
					echo "�������� ".$arrSaleOrderProp['CODE']." ���������" ;
				}else{
					mysql_query("INSERT into b_sale_user_props_value (USER_PROPS_ID, ORDER_PROPS_ID, NAME, VALUE) VALUES ('{$arrOrder['USER_PROPS_ID']}', '{$arSaleOrderPropsValue['ORDER_PROPS_ID']}', '{$arSaleOrderPropsValue['NAME']}', '{$value_prop}')");
					echo "�������� ".$arrSaleOrderProp['CODE']." ���������" ;
				}
		   }
	   } else {
	   		echo "\n����� �� ��������";
	   }
	   	//--������� ����������� � �������� � ������
	    $objSaleOrderPropsValue = CSaleOrderPropsValue::GetList(array("SORT" => "ASC"),	array("ORDER_ID" => $arrOrder["ORDER_ID"],	"CODE" => array_keys($arrSaleOrderProps)));

	    while ($arSaleOrderPropsValue = $objSaleOrderPropsValue->Fetch()) {
			$value_prop = $arSaleOrderPropsValues[$arSaleOrderPropsValue['CODE']]['VALUE'];
			
			if($value_prop) {
				CSaleOrderPropsValue::Update($arSaleOrderPropsValue['ID'], array("VALUE"=>$value_prop));
			} else {
				echo "�� ����� ���� ������";
				 var_dump($arSaleOrderPropsValue['CODE']);
			}
			unset($arSaleOrderPropsValues[$arSaleOrderPropsValue['CODE']]);
		}
		
		unset($arrSaleOrderProps, $objSaleOrderPropsValue);
		
		//--������� ����������� ����������� � �������� � �����
		foreach ($arSaleOrderPropsValues as $key=>$arSaleOrderPropsValue) {
			unset($arSaleOrderPropsValue['ID']);
			CSaleOrderPropsValue::Add($arSaleOrderPropsValue);
			unset($arSaleOrderPropsValue['CODE']);
			CSaleOrderUserPropsValue::Add($arSaleOrderPropsValue);
			
			unset($arSaleOrderPropsValues[$key]);
		}
		
		unset($arSaleOrderPropsValues);
  
	   //������������ ������
	   $basket_user = CSaleBasket::GetBasketUserID();
	   CSaleBasket::DeleteAll($basket_user);
	   $allGoods = array();
	   foreach($arrOrder["ITEMS"] as $key=>$val):
	   		$allGoods[] = $val["PRODUCT_ID"];
	   		//pre($val);
			$dbBasketItems = CSaleBasket::GetList(
					array(),
					array("ORDER_ID" => $arrOrder["ORDER_ID"], "PRODUCT_ID" => $val["PRODUCT_ID"]),
					false,
					false,
					array()
				);
			if ($arItems = $dbBasketItems->Fetch())
			{
				//pre($arItems);
				$arFields = array(
				   "QUANTITY" => $val["QUANT"],
				   "PRICE" => $val["PRICE"],
				   "PRODUCT_PRICE_ID" => $val["PRODUCT_PRICE_ID"],
				   "NOTES" => $val["NOTES"],
				);
				//echo "UPDATE";
				CSaleBasket::Update($arItems["ID"], $arFields);
			} else {
					
				  $res = CIBlockElement::GetByID($val["PRODUCT_ID"]);
				  $ar_res = $res->GetNext();
				  //--�������� �������� ��������� ������
				  $resSect = CIBlockSection::GetByID($ar_res["IBLOCK_SECTION_ID"]);
				  if($ar_res_sect = $resSect->GetNext()) {
						$ar_res["NAME"] = $ar_res_sect['NAME'] . ' ' . $ar_res["NAME"];
				  }
					
				  $arFieldsB = array(
					"PRODUCT_ID" => $val["PRODUCT_ID"],
					"PRODUCT_PRICE_ID" => $val["PRODUCT_PRICE_ID"],
					"NOTES" => $val["NOTES"],
					"PRICE" => $val["PRICE"],
					"CURRENCY" => "RUB",
					"QUANTITY" => $val["QUANT"],
					"LID" => LANG,
					"DELAY" => "N",
					"CAN_BUY" => "Y",
					"NAME" => $ar_res["NAME"],
					"MODULE" => "catalog",
					//"CALLBACK_FUNC" => "CatalogBasketOrderCallback",
					"DETAIL_PAGE_URL" => $ar_res["DETAIL_PAGE_URL"],
				  );
				  
					 
				CSaleBasket::Add($arFieldsB);
				CSaleBasket::OrderBasket($arrOrder["ORDER_ID"]);
				
			}
			
	   
	   endforeach;
	   //������� ������, ������� ��� � ��������
	   //pre($allGoods);
	   
		$dbBasketItems = CSaleBasket::GetList(
				array(
						"NAME" => "ASC",
						"ID" => "ASC"
					),
				array(
						"LID" => SITE_ID,
						"ORDER_ID" => $arrOrder["ORDER_ID"],
					),
				false,
				false,
				array("ID", "CALLBACK_FUNC", "MODULE", 
					  "PRODUCT_ID", "QUANTITY", "DELAY", 
					  "CAN_BUY", "PRICE", "WEIGHT")
			);
		while ($arItems = $dbBasketItems->Fetch())
		{
		
			//pre($arItems);
			if(!in_array($arItems["PRODUCT_ID"], $allGoods)) CSaleBasket::Delete($arItems["ID"]);
		}
	   
	   
	   
	}
	elseif(preg_match('/^�����(.+)[^\:]*[\:](.+)/', $p, $addrTmpArray)) {
		//--������� ������ ������
		$addrTmpArray2 = explode("|", $addrTmpArray[2]);
		$addrTmpArray2 = array_map('trim', $addrTmpArray2);
		
		$arrOrder['USER_PROPS_ID'] = round(trim($addrTmpArray[1]));
		
		unset($addrTmpArray);
		
		$arrOrder["ADDRESS"] = array();
		
		$arrOrder["ADDRESS"]['CITY2'] = $addrTmpArray2[0];
		$arrOrder["ADDRESS"]['STREET'] = $addrTmpArray2[1];
		
		//--������� ���������� �� ���� � ��������
		$coordTmpArray = explode('�', $addrTmpArray2[2]);
		$coordTmpArray = array_map('trim', $coordTmpArray);
	
		$arrOrder["ADDRESS"]['HOME'] = $coordTmpArray[0];
		$arrOrder["ADDRESS"]['ADDRESS_IS_CORRECT'] = $coordTmpArray[1];
		$arrOrder["ADDRESS"]['COORDINATES'] = $coordTmpArray[2];	
		$arrOrder["ADDRESS"]['PODYEZD'] = $coordTmpArray[3];
		
		unset($coordTmpArray);
		
		
		$arrOrder["ADDRESS"]['LIFT'] = $addrTmpArray2[3];
		$arrOrder["ADDRESS"]['STAGE'] = $addrTmpArray2[4];
		$arrOrder["ADDRESS"]['HOMEPHONE'] = $addrTmpArray2[5];
		$arrOrder["ADDRESS"]['APP'] = $addrTmpArray2[6];
		$arrOrder["ADDRESS"]['MKAD'] = $addrTmpArray2[7];
		$arrOrder["ADDRESS"]['COMMENT'] = $addrTmpArray2[8];
	
		unset($addrTmpArray2);
		unset($addrTmpArray);
		
	}else {
		$arr = array();
		$arr = explode("�", $p);
		//echo count($arr)." ";
		if(count($arr)>3){
			
			if($arr[8]) {
				$arFilter = Array('IBLOCK_ID'=>81, 'CODE'=>$arr[8]);
				$db_list = CIBlockElement::GetList(Array($by=>$order), $arFilter);
				
				if($ar_result = $db_list->GetNext()){
					$arr[8] = $ar_result["ID"];
				}
			}
			
			$arrOrder["DATE_DELIV"]   = $arr[0];
			$arrOrder["ORDER_ID"]     = $arr[1];
			$arrOrder["USER_ID"]      = $arr[2];
			$arrOrder["ORDER_STATUS"] = $arr[3];
			$arrOrder["DISCOUNT"]     = $arr[4];
			$arrOrder["ORDER_SUM"]    = $arr[5];
			$arrOrder["MANAGER_ID"]   = $arr[7];
			$arrOrder["MAN_ID"]       = $arr[8];
			$arrOrder["TIME1"]        = $arr[9];
			$arrOrder["TIME2"]        = $arr[10];
			$arrOrder["PRICE_DELIVERY"]=$arr[11];

		}else{
			$item = array();
			$item["CODE"]  = $arr[0];
			$item["QUANT"] = $arr[1];
			$item["PRICE"] = $arr[2];
			//$item["NOTES"] = '�������';
			if($item["CODE"]=="systMKAD"){
				$item["PRODUCT_PRICE_ID"] = 100000;
			}
			if(substr($item["CODE"], 0, 8)=="systDOST"){
				$item["PRODUCT_PRICE_ID"] = 100001;
			}
			
			$arSelect = Array("ID", "NAME", "DATE_ACTIVE_FROM");
			$arFilter = Array("IBLOCK_ID"=>"7", "ACTIVE_DATE"=>"Y", "ACTIVE"=>"Y", "CODE"=>$arr[0]);
			$res = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
			if($ob = $res->GetNextElement())
			{
			  $arFields = $ob->GetFields();
			  $item["PRODUCT_ID"] = $arFields["ID"];
			  $arrOrder["ITEMS"][] = $item;
			}			
		}
	}
		
endwhile;
fclose($fp1);
writeReportLog(ob_get_contents(), 'users_orders');
echo "������ ������� ���������";
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
?>