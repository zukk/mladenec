<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
CModule::IncludeModule("sale");
CModule::IncludeModule("iblock");
CModule::IncludeModule("catalog");
$PROPS_ID = 44287;
$ORDER_ID = 56384;

$db_vars = CSaleLocation::GetList(array(), array("COUNTRY_NAME_ORIG" => '������ �'), false, false, array());
if ($vars = $db_vars->Fetch()) {
	print_r($vars);
}
die();


//$db_props = CSaleOrderPropsValue::GetOrderProps($ORDER_ID);
//while ($arProps = $db_props->Fetch())
//{
//	print_r($arProps);
//}
//die();
//$db_vals = CSaleOrderPropsValue::GetList(array("SORT" => "ASC"),	array("ORDER_ID" => $ORDER_ID,	"CODE" => "CITY"));
//			  if ($arVals = $db_vals->Fetch()) {
//			  	print_r($arVals);
//			  }
//die();
//  
//  die();

//$db_sales = CSaleOrderUserProps::GetList(
//        array("DATE_UPDATE" => "DESC"),
//        array("ID" => $PROPS_ID)
//    );
//
//while ($ar_sales = $db_sales->Fetch())
//{
//   print_r($ar_sales);
//}
//
//die();
//$dbUserPropsValues = CSaleOrderUserPropsValue::GetList(
//						array("SORT" => "ASC"),
//						array(
//						"USER_PROPS_ID" => $PROPS_ID,
//						//"ORDER_PROPS_ID" => 56384
//						),
//						false,
//						false,
//						array("VALUE", "PROP_TYPE", "VARIANT_NAME", "SORT", "ORDER_PROPS_ID")
//					);
//				while ($arUserPropsValues = $dbUserPropsValues->GetNext())
//				{
//					print_r($arUserPropsValues);
//				}
//				die();
//$db_propVals = CSaleOrderUserPropsValue::GetList(($b="SORT"), ($o="ASC"), Array("USER_PROPS_ID"=>$PROPS_ID));
//while ($arPropVals = $db_propVals->Fetch())
//{
//   print_r($arPropVals);
//}
//die();
//$orderValues = array();
//$db_vals = CSaleOrderPropsValue::GetList(array("SORT" => "ASC"),	array("ORDER_ID" => $ORDER_ID));
//  while ($arVals = $db_vals->Fetch()) {
//  	$orderValues[$arVals["NAME"]] = $arVals["VALUE"];
//  }
//die();
CSaleOrderUserProps::ClearEmpty();
die();
$arOrderFilter = array("PERSON_TYPE_ID" => 1, "USER_PROPS"=>"Y");
   $dbOrderProperties = CSaleOrderProps::GetList(
					array("SORT" => "ASC"),
					$arOrderFilter,
					false,
					false,
					array()
				);

			//CSaleOrderUserPropsValue::DeleteAll($PROPS_ID);
			$arFieldsNewOrder = array(
									"NAME" => 'odinc',
									"USER_ID" => 1,
									"PERSON_TYPE_ID" => 1 //��� �����������, ���� �������, ��� ��� ������������ �� ��������� ������ ���� "������"
								  );
			$PROPS_ID = CSaleOrderUserProps::Add($arFieldsNewOrder);
			var_dump($PROPS_ID);
			while ($arOrderProperties = $dbOrderProperties->Fetch()) {
				print_r($arOrderProperties);
				$arFields = array(
									"USER_PROPS_ID" => $PROPS_ID,
									"ORDER_PROPS_ID" => $ORDER_ID,
									"NAME" => $arOrderProperties["NAME"],
									"VALUE" => $orderValues[$arOrderProperties["NAME"]]
								);
				print_r($arFields);
				var_dump(CSaleOrderUserPropsValue::Add($arFields));
			}
?>