<? require($_SERVER["DOCUMENT_ROOT"]."/odinc/auth.php");?>
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

ob_start();
CModule::IncludeModule("sale");
CModule::IncludeModule("iblock");
CModule::IncludeModule("catalog");
$fp = fopen('php://input', 'r');
$report = date("H:i:s")."<br>";

$counter = 0;

if(!$USER->IsAuthorized() ) $USER->Authorize(5);
//������������ ��� 1�
//$fp = fopen("order.txt", "r");

while (!feof($fp)):
	$p = trim(fgets($fp));
	echo $p."<br>";
	if($p=="�����"){
		$arrOrder = array();
	}
	elseif($p=="�����������"){
	
		echo "END";
		//print_r($arrOrder);
			
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
	   //$PROPS_ID = $arr[0];
	   $PROPS_ID = $arrOrder["USER_PROPS_ID"];
	   
	   if($PROPS_ID < 0) {
	   	$arFieldsNewOrder = array(
									"NAME" => 'odinc',
									"USER_ID" => IntVal($arrOrder["USER_ID"]),
									"PERSON_TYPE_ID" => 1 //��� �����������, ���� �������, ��� ��� ������������ �� ��������� ������ ���� "������"
								  );
	   	$PROPS_ID = IntVal(CSaleOrderUserProps::Add($arFieldsNewOrder));
	   	unset($arFieldsNewOrder);
	   }
	   
	   $ORDER_ID = $arrOrder["ORDER_ID"];
	   $arFields["COMMENTS"] = $PROPS_ID."�".$arrOrder["TIME1"]."-".$arrOrder["TIME2"];
		
	   
	   CSaleOrder::Update($arrOrder["ORDER_ID"], $arFields); //��������� ����� �������� ������
	   
	   
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
        /*if($ar_sales["PRICE"]>0) {
          $group = 12;
        	$status_info = '��� ������ &laquo;����������&raquo;<ul><li class="head">�������� ��������� ������� �� �������:</li><li>&laquo;�������&raquo; '.FormatCurrency((15000 - $ar_sales["PRICE"]), "RUB"). '</li><li>&laquo;����������&raquo; '.FormatCurrency((30000 - $ar_sales["PRICE"]), "RUB"). '</li></ul>';
        }
        if($ar_sales["PRICE"]>=15000) {
          $group = 8;
        	$status_info = '��� ������ &laquo;�������&raquo;<ul><li class="head">�������� ��������� ������� �� �������:</li><li>&laquo;����������&raquo; '.FormatCurrency((30000 - $ar_sales["PRICE"]), "RUB"). '</li></ul>';
        }
        if($ar_sales["PRICE"]>=30000) {
          $group = 7;
        	$status_info = '��� ������ &laquo;����������&raquo;';
        }*/

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
	   
	  //--��������� ���������� � �������
		$db_vals = CSaleOrderPropsValue::GetList(array("SORT" => "ASC"),	array("ORDER_ID" => $ORDER_ID,	"CODE" => "MAN_ID"));
		if ($arVals = $db_vals->Fetch()) {
			CSaleOrderPropsValue::Update($arVals['ID'], array("VALUE"=>$arrOrder["MAN_ID"]));
		}
		else {
			CSaleOrderPropsValue::Add(array("ORDER_ID" => $ORDER_ID, "ORDER_PROPS_ID" => "24", "CODE"=>"MAN_ID", "NAME"=>"������", "VALUE" => $arrOrder["MAN_ID"]));
		}
		//--��������� ������ � ������� ��������
		$db_vals = CSaleOrderPropsValue::GetList(array("SORT" => "ASC"),	array("ORDER_ID" => $ORDER_ID,	"CODE" => "TIME"));
		if ($arVals = $db_vals->Fetch()) {
			//CSaleOrderPropsValue::Delete($arVals['ID']);
		}
		$db_vals = CSaleOrderPropsValue::GetList(array("SORT" => "ASC"),	array("ORDER_ID" => $ORDER_ID,	"CODE" => "TIME_CURRENT"));
		if ($arVals = $db_vals->Fetch()) {
			CSaleOrderPropsValue::Update($arVals['ID'], array("VALUE"=>$arrOrder["TIME1"]."-".$arrOrder["TIME2"]));
		}
		else {
			CSaleOrderPropsValue::Add(array("ORDER_ID" => $ORDER_ID, "ORDER_PROPS_ID" => "77", "CODE"=>"TIME_CURRENT", "NAME"=>"���� ��������", "VALUE" => $arrOrder["TIME1"]."-".$arrOrder["TIME2"]));
		}
	   //��������� �����
	   echo "��������� �����";
	   $arrAdres = array();
	   $arrAdres = explode("|", $arrOrder["ARDES"]);
   
	   //�����
	   if($arrAdres[0]!=""):
	   		$isCityId = false;
		   $db_vars = CSaleLocation::GetList(array(), array("COUNTRY_NAME_ORIG" => $arrAdres[0]), false, false, array());
		   
		   if ($vars = $db_vars->Fetch()) {
		   		echo "���������� ����� ������:" . $arrAdres[0];
			  $db_vals = CSaleOrderPropsValue::GetList(array("SORT" => "ASC"),	array("ORDER_ID" => $arrOrder["ORDER_ID"],	"CODE" => "CITY"));
			  if ($arVals = $db_vals->Fetch()) {
			  	
			  	CSaleOrderPropsValue::Update($arVals['ID'], array("VALUE"=>$vars["ID"]));
			  	
//			  	$arUserFields = array(
//									"USER_PROPS_ID" => $PROPS_ID,
//									"ORDER_PROPS_ID" => $ORDER_ID,
//									"NAME" => $vars['NAME'],
//									"VALUE" => $vars["ID"]
//								);
//
//								echo "��������� ������ � ��������: ";
//								var_dump(CSaleOrderUserPropsValue::Add($arUserFields));
				
				
			  	$isCityId = true;
			  }
			  else {
			  	CSaleOrderPropsValue::Add(array("ORDER_ID" => $arrOrder["ORDER_ID"], "ORDER_PROPS_ID" => "13", "CODE"=>"CITY", "NAME"=>"���������� �����", "VALUE" => $vars["ID"]));
//			  	$arUserFields = array(
//									"USER_PROPS_ID" => $PROPS_ID,
//									"ORDER_PROPS_ID" => $ORDER_ID,
//									"NAME" => $vars['NAME'],
//									"VALUE" => $vars["ID"]
//								);
//
//								echo "��������� ������ � ��������: ";
//								var_dump(CSaleOrderUserPropsValue::Add($arUserFields));
			  	$isCityId = true;
			  }
			  
			  unset($db_vals);
			  unset($arVals);
			  
			  if($isCityId) { //������ �� ���� ���������� ����� ��������, ���� ����� ��� � ���������
			  	$db_vals = CSaleOrderPropsValue::GetList(array("SORT" => "ASC"),	array("ORDER_ID" => $arrOrder["ORDER_ID"],	"CODE" => "CITY2"));
			  	if ($arVals = $db_vals->Fetch()) {
			  		CSaleOrderPropsValue::Update($arVals['ID'], array("VALUE"=>''));
			  	}
			  	unset($db_vals);
			  	unset($arVals);
			  	
			  }
			  
		   } else {
		   	
		   		echo "���������� ����� �� ������:" . $arrAdres[0];
		   	
		   		 //������ �� ���� ����� ��������, ���� �� ����� ��� � ���������
		   		$db_vals = CSaleOrderPropsValue::GetList(array("SORT" => "ASC"),	array("ORDER_ID" => $arrOrder["ORDER_ID"],	"CODE" => "CITY"));
		   	
		   		if ($arVals = $db_vals->Fetch()) {
				  	CSaleOrderPropsValue::Delete($arVals['ID']);
		   		}
		   		
		   		unset($db_vals);
			  	unset($arVals);
		   		
		  
			  $db_vals = CSaleOrderPropsValue::GetList(array("SORT" => "ASC"),	array("ORDER_ID" => $arrOrder["ORDER_ID"],	"CODE" => "CITY2"));
			  if ($arVals = $db_vals->Fetch()) CSaleOrderPropsValue::Update($arVals['ID'], array("VALUE"=>$arrAdres[0]));
			  else CSaleOrderPropsValue::Add(array("ORDER_ID" => $arrOrder["ORDER_ID"], "ORDER_PROPS_ID" => "29", "CODE"=>"CITY2", "NAME"=>"�����", "VALUE" => $arrAdres[0]));
			  
			   $code_prop = 29; $name_prop = "�����"; $value_prop = $arrAdres[0];
			   $result = mysql_query("SELECT ID FROM b_sale_user_props_value WHERE USER_PROPS_ID='$PROPS_ID' AND ORDER_PROPS_ID='$code_prop'");
			   if($row = mysql_fetch_array($result)):
					mysql_query("UPDATE b_sale_user_props_value SET VALUE='$value_prop' WHERE ID='$row[ID]'") or die("ERROR");
			   else:
					mysql_query("INSERT into b_sale_user_props_value (USER_PROPS_ID, ORDER_PROPS_ID, NAME, VALUE) VALUES ($PROPS_ID, $code_prop, '$name_prop', '$value_prop')");
			   endif;
			  
		   }
		  
	   endif;  
	   
	   //die();
	   
	   if($arrAdres[1]!=""):
	   
		  $db_vals = CSaleOrderPropsValue::GetList(array("SORT" => "ASC"),	array("ORDER_ID" => $arrOrder["ORDER_ID"],	"CODE" => "STREET"));
		  if ($arVals = $db_vals->Fetch()) CSaleOrderPropsValue::Update($arVals['ID'], array("VALUE"=>$arrAdres[1]));
		  else CSaleOrderPropsValue::Add(array("ORDER_ID" => $arrOrder["ORDER_ID"], "ORDER_PROPS_ID" => "14", "CODE"=>"STREET", "NAME"=>"�����", "VALUE" => $arrAdres[1]));
		  
		   $code_prop = 14; $name_prop = "�����"; $value_prop = $arrAdres[1];
		   $result = mysql_query("SELECT * FROM b_sale_user_props_value WHERE USER_PROPS_ID='$PROPS_ID' AND ORDER_PROPS_ID='$code_prop'");
		   if($row = mysql_fetch_array($result)):
				mysql_query("UPDATE b_sale_user_props_value SET VALUE='$value_prop' WHERE ID='$row[ID]'") or die("ERROR");
		   else:
				mysql_query("INSERT into b_sale_user_props_value (USER_PROPS_ID, ORDER_PROPS_ID, NAME, VALUE) VALUES ($PROPS_ID, $code_prop, '$name_prop', '$value_prop')");
		   endif;
		  
		  
	   endif; 
		
	   if($arrAdres[2]!=""):
	   
		  $db_vals = CSaleOrderPropsValue::GetList(array("SORT" => "ASC"),	array("ORDER_ID" => $arrOrder["ORDER_ID"],	"CODE" => "HOME"));
		  if ($arVals = $db_vals->Fetch()) CSaleOrderPropsValue::Update($arVals['ID'], array("VALUE"=>$arrAdres[2]));	
		  else CSaleOrderPropsValue::Add(array("ORDER_ID" => $arrOrder["ORDER_ID"], "ORDER_PROPS_ID" => "15", "CODE"=>"HOME", "NAME"=>"���", "VALUE" => $arrAdres[2]));
		  
			   $code_prop = 15; $name_prop = "���"; $value_prop = $arrAdres[2];
			   $result = mysql_query("SELECT * FROM b_sale_user_props_value WHERE USER_PROPS_ID='$PROPS_ID' AND ORDER_PROPS_ID='$code_prop'");
			   if($row = mysql_fetch_array($result)):
					mysql_query("UPDATE b_sale_user_props_value SET VALUE='$value_prop' WHERE ID='$row[ID]'") or die("ERROR");
			   else:
					mysql_query("INSERT into b_sale_user_props_value (USER_PROPS_ID, ORDER_PROPS_ID, NAME, VALUE) VALUES ($PROPS_ID, $code_prop, '$name_prop', '$value_prop')");
			   endif;
		  
	   endif; 
	   
	   if($arrAdres[3]!=""):
	   
		  $db_vals = CSaleOrderPropsValue::GetList(array("SORT" => "ASC"),	array("ORDER_ID" => $arrOrder["ORDER_ID"],	"CODE" => "CORP"));
		  if ($arVals = $db_vals->Fetch()) CSaleOrderPropsValue::Update($arVals['ID'], array("VALUE"=>$arrAdres[3]));	
		  else CSaleOrderPropsValue::Add(array("ORDER_ID" => $arrOrder["ORDER_ID"], "ORDER_PROPS_ID" => "16", "CODE"=>"CORP", "NAME"=>"������", "VALUE" => $arrAdres[3]));
		  
			   $code_prop = 16; $name_prop = "������"; $value_prop = $arrAdres[3];
			   $result = mysql_query("SELECT * FROM b_sale_user_props_value WHERE USER_PROPS_ID='$PROPS_ID' AND ORDER_PROPS_ID='$code_prop'");
			   if($row = mysql_fetch_array($result)):
					mysql_query("UPDATE b_sale_user_props_value SET VALUE='$value_prop' WHERE ID='$row[ID]'") or die("ERROR");
			   else:
					mysql_query("INSERT into b_sale_user_props_value (USER_PROPS_ID, ORDER_PROPS_ID, NAME, VALUE) VALUES ($PROPS_ID, $code_prop, '$name_prop', '$value_prop')");
			   endif;
		  
	   endif; 
	   
	   if($arrAdres[4]!=""):
	   
		  $db_vals = CSaleOrderPropsValue::GetList(array("SORT" => "ASC"),	array("ORDER_ID" => $arrOrder["ORDER_ID"],	"CODE" => "PODYEZD"));
		  if ($arVals = $db_vals->Fetch()) CSaleOrderPropsValue::Update($arVals['ID'], array("VALUE"=>$arrAdres[4]));	
		  else CSaleOrderPropsValue::Add(array("ORDER_ID" => $arrOrder["ORDER_ID"], "ORDER_PROPS_ID" => "17", "CODE"=>"PODYEZD", "NAME"=>"�������", "VALUE" => $arrAdres[4]));
		  
			   $code_prop = 17; $name_prop = "�������"; $value_prop = $arrAdres[4];
			   $result = mysql_query("SELECT * FROM b_sale_user_props_value WHERE USER_PROPS_ID='$PROPS_ID' AND ORDER_PROPS_ID='$code_prop'");
			   if($row = mysql_fetch_array($result)):
					mysql_query("UPDATE b_sale_user_props_value SET VALUE='$value_prop' WHERE ID='$row[ID]'") or die("ERROR");
			   else:
					mysql_query("INSERT into b_sale_user_props_value (USER_PROPS_ID, ORDER_PROPS_ID, NAME, VALUE) VALUES ($PROPS_ID, $code_prop, '$name_prop', '$value_prop')");
			   endif;
		  
	   endif; 
	   
	   if($arrAdres[5]!=""):
	   
		  $db_vals = CSaleOrderPropsValue::GetList(array("SORT" => "ASC"),	array("ORDER_ID" => $arrOrder["ORDER_ID"],	"CODE" => "STAGE"));
		  if ($arVals = $db_vals->Fetch()) CSaleOrderPropsValue::Update($arVals['ID'], array("VALUE"=>$arrAdres[5]));	
		  else CSaleOrderPropsValue::Add(array("ORDER_ID" => $arrOrder["ORDER_ID"], "ORDER_PROPS_ID" => "18", "CODE"=>"STAGE", "NAME"=>"����", "VALUE" => $arrAdres[5]));
		  
			   $code_prop = 18; $name_prop = "����"; $value_prop = $arrAdres[5];
			   $result = mysql_query("SELECT * FROM b_sale_user_props_value WHERE USER_PROPS_ID='$PROPS_ID' AND ORDER_PROPS_ID='$code_prop'");
			   if($row = mysql_fetch_array($result)):
					mysql_query("UPDATE b_sale_user_props_value SET VALUE='$value_prop' WHERE ID='$row[ID]'") or die("ERROR");
			   else:
					mysql_query("INSERT into b_sale_user_props_value (USER_PROPS_ID, ORDER_PROPS_ID, NAME, VALUE) VALUES ($PROPS_ID, $code_prop, '$name_prop', '$value_prop')");
			   endif;
		  
	   endif; 
	   
	   if($arrAdres[6]!=""):
	   
		  $db_vals = CSaleOrderPropsValue::GetList(array("SORT" => "ASC"),	array("ORDER_ID" => $arrOrder["ORDER_ID"],	"CODE" => "HOMEPHONE"));
		  if ($arVals = $db_vals->Fetch()) CSaleOrderPropsValue::Update($arVals['ID'], array("VALUE"=>$arrAdres[6]));
		  else CSaleOrderPropsValue::Add(array("ORDER_ID" => $arrOrder["ORDER_ID"], "ORDER_PROPS_ID" => "20", "CODE"=>"HOMEPHONE", "NAME"=>"�������", "VALUE" => $arrAdres[6]));
		  
			   $code_prop = 20; $name_prop = "�������"; $value_prop = $arrAdres[6];
			   $result = mysql_query("SELECT * FROM b_sale_user_props_value WHERE USER_PROPS_ID='$PROPS_ID' AND ORDER_PROPS_ID='$code_prop'");
			   if($row = mysql_fetch_array($result)):
					mysql_query("UPDATE b_sale_user_props_value SET VALUE='$value_prop' WHERE ID='$row[ID]'") or die("ERROR");
			   else:
					mysql_query("INSERT into b_sale_user_props_value (USER_PROPS_ID, ORDER_PROPS_ID, NAME, VALUE) VALUES ($PROPS_ID, $code_prop, '$name_prop', '$value_prop')");
			   endif;
		  
	   endif; 
	   
	   if($arrAdres[7]!=""):
	   
		  $db_vals = CSaleOrderPropsValue::GetList(array("SORT" => "ASC"),	array("ORDER_ID" => $arrOrder["ORDER_ID"],	"CODE" => "APP"));
		  if ($arVals = $db_vals->Fetch()) CSaleOrderPropsValue::Update($arVals['ID'], array("VALUE"=>$arrAdres[7]));	
		  else CSaleOrderPropsValue::Add(array("ORDER_ID" => $arrOrder["ORDER_ID"], "ORDER_PROPS_ID" => "19", "CODE"=>"APP", "NAME"=>"��������", "VALUE" => $arrAdres[7]));
		  
			   $code_prop = 19; $name_prop = "��������"; $value_prop = $arrAdres[7];
			   $result = mysql_query("SELECT * FROM b_sale_user_props_value WHERE USER_PROPS_ID='$PROPS_ID' AND ORDER_PROPS_ID='$code_prop'");
			   if($row = mysql_fetch_array($result)):
					mysql_query("UPDATE b_sale_user_props_value SET VALUE='$value_prop' WHERE ID='$row[ID]'") or die("ERROR");
			   else:
					mysql_query("INSERT into b_sale_user_props_value (USER_PROPS_ID, ORDER_PROPS_ID, NAME, VALUE) VALUES ($PROPS_ID, $code_prop, '$name_prop', '$value_prop')");
			   endif;
		  
	   endif; 
	   
	   if($arrAdres[8]!=""):
	   
		  $db_vals = CSaleOrderPropsValue::GetList(array("SORT" => "ASC"),	array("ORDER_ID" => $arrOrder["ORDER_ID"],	"CODE" => "MKAD"));
		  if ($arVals = $db_vals->Fetch()) CSaleOrderPropsValue::Update($arVals['ID'], array("VALUE"=>$arrAdres[8]));
		  else CSaleOrderPropsValue::Add(array("ORDER_ID" => $arrOrder["ORDER_ID"], "ORDER_PROPS_ID" => "12", "CODE"=>"MKAD", "NAME"=>"���������� �� ���� (��)", "VALUE" => $arrAdres[8]));
		  
			   $code_prop = 12; $name_prop = "���������� �� ���� (��)"; $value_prop = $arrAdres[8];
			   $result = mysql_query("SELECT * FROM b_sale_user_props_value WHERE USER_PROPS_ID='$PROPS_ID' AND ORDER_PROPS_ID='$code_prop'");
			   if($row = mysql_fetch_array($result)):
					mysql_query("UPDATE b_sale_user_props_value SET VALUE='$value_prop' WHERE ID='$row[ID]'") or die("ERROR");
			   else:
					mysql_query("INSERT into b_sale_user_props_value (USER_PROPS_ID, ORDER_PROPS_ID, NAME, VALUE) VALUES ($PROPS_ID, $code_prop, '$name_prop', '$value_prop')");
			   endif;
		  
	   endif; 
	   
	   if($arrAdres[9]!=""):
	   
		  $db_vals = CSaleOrderPropsValue::GetList(array("SORT" => "ASC"),	array("ORDER_ID" => $arrOrder["ORDER_ID"],	"CODE" => "COMMENT"));
		  if ($arVals = $db_vals->Fetch()) CSaleOrderPropsValue::Update($arVals['ID'], array("VALUE"=>$arrAdres[9]));
		  else CSaleOrderPropsValue::Add(array("ORDER_ID" => $arrOrder["ORDER_ID"], "ORDER_PROPS_ID" => "21", "CODE"=>"COMMENT", "NAME"=>"����������� � ��������", "VALUE" => $arrAdres[9]));
		  
			   $code_prop = 21; $name_prop = "����������� � ��������"; $value_prop = $arrAdres[9];
			   $result = mysql_query("SELECT * FROM b_sale_user_props_value WHERE USER_PROPS_ID='$PROPS_ID' AND ORDER_PROPS_ID='$code_prop'");
			   if($row = mysql_fetch_array($result)):
					mysql_query("UPDATE b_sale_user_props_value SET VALUE='$value_prop' WHERE ID='$row[ID]'") or die("ERROR");
			   else:
					mysql_query("INSERT into b_sale_user_props_value (USER_PROPS_ID, ORDER_PROPS_ID, NAME, VALUE) VALUES ($PROPS_ID, $code_prop, '$name_prop', '$value_prop')");
			   endif;
		  
	   endif; 
	   
	   //������� ������� �����, ���� ����� ���������...� ����������
	   
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
	else {
		$arr = array();
		$arr = explode("�", $p);
		//echo count($arr)." ";
		if(count($arr)>3):
		
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
		$arrOrder["ARDES"]        = $arr[6];
		$arrOrder["MANAGER_ID"]   = $arr[7];
		$arrOrder["MAN_ID"]       = $arr[8];
		$arrOrder["TIME1"]        = $arr[9];
		$arrOrder["TIME2"]        = $arr[10];
		$arrOrder["PRICE_DELIVERY"] = $arr[11];
		$arrOrder["USER_PROPS_ID"]	= $arr[12];
			
			//$dbOrderProps = CSaleOrderPropsValue::GetOrderProps($arrOrder["ORDER_ID"]);
			//while ($arOrderProps = $dbOrderProps->GetNext())
			//{
				//pre($arOrderProps);
				
			//}
//			print_r($arrOrder);
			//print_r($arr);
			
	else:
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
			
			
		endif;
		
	}
		
endwhile;
fclose($fp1);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
?>