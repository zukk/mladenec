<? require($_SERVER["DOCUMENT_ROOT"]."/odinc/auth.php");?>
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
CModule::IncludeModule("iblock");

			$arSelect = Array("ID", "NAME", "CODE", "IBLOCK_SECTION_ID", "CATALOG_GROUP_1", "PROPERTY_BRAND");
			$arFilter = Array("IBLOCK_ID"=>"7", "ACTIVE_DATE"=>"Y", "ACTIVE"=>"Y");
			$res = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
			while($ob = $res->GetNextElement()):
			  $arFields = $ob->GetFields();
			  
			  $ITEM_CODE = $arFields["CODE"];
			  $ITEM_PRICE = $arFields["CATALOG_PRICE_1"];
			  $ITEM_BRAND = $arFields["PROPERTY_BRAND_VALUE"];
			  
			  //pre($arFields);
			  $res_catalog = CIBlockSection::GetByID($arFields["IBLOCK_SECTION_ID"]);
			  $ar_catalog = $res_catalog->GetNext();
			  //pre($ar_catalog);
			  //�������� ��� ��������� � ��������� (�� ���� �������)
			  $IBLOCK_CODE = $ar_catalog["CODE"];
			  		//echo "ITEM_QUANTITY=$ITEM_QUANTITY<br>";
					//echo "ITEM_BRAND=$ITEM_BRAND<br>";
					//�������� ������ �� �������� � ��������� � ���������� �����
			  		$resFilters = CIBlockElement::GetList(Array(), Array("CODE"=>$ITEM_CODE, "IBLOCK_TYPE"=>"prop"), false, false, Array("ID", "IBLOCK_ID"));
			  		while($obFilters = $resFilters->GetNextElement()):
						$arFilters = $obFilters->GetFields();
						//pre($arFilters);
						CIBlockElement::SetPropertyValues($arFilters["ID"], $arFilters["IBLOCK_ID"], $ITEM_PRICE, "PRICE");
						CIBlockElement::SetPropertyValues($arFilters["ID"], $arFilters["IBLOCK_ID"], $ITEM_BRAND, "BRAND");

					endwhile;
			endwhile;

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
?>