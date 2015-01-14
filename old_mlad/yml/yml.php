<?php
require($_SERVER["DOCUMENT_ROOT"].
"/bitrix/modules/main/include/prolog_before.php");
CModule::IncludeModule("iblock");
CModule::IncludeModule("catalog");
 
if(is_set($_POST["SelsctSection"])){			//находим раздел из которого выгружаем.
	 $arParentSection;
	 $i = 0;
	 $s= array(); 
	 $dbSection = CIBlockSection::GetByID(intval($_POST["SelsctSection"]));
	 if($arSection = $dbSection->GetNext()){
		$s = $arSection;
			
			$dbParentSection = CIBlockSection::GetByID($arSection["IBLOCK_SECTION_ID"]); //находим родителя. 
			if($arParentSection[$i] = $dbParentSection->GetNext()){			
				$i++;
			}
			while($arParentSection[$i-1]["DEPTH_LEVEL"]!="1"){             //получаем список категорий

				$dbParentSection = CIBlockSection::GetByID($arParentSection[$i-1]["IBLOCK_SECTION_ID"]); 

				if($arParentSection[$i] = $dbParentSection->GetNext()){
					$i++;
				}
			}
		$arParentSection = array_reverse($arParentSection);
		
		$arParentSection[] = $s;
		$conter = count($arParentSection);
		$category = array();
		/* if(!(is_set($_SESSION["YML"]["category"])))
		{        
		
			foreach($arParentSection as $categorys)
			{
				if ($categorys["DEPTH_LEVEL"]==1){
					$category[] = "<category id=\"".$categorys['ID']."\">".$categorys['NAME']."</category>";
					$categoryList[] = $categorys["ID"];  
				}else{
					$category[] = "<category id=\"".$categorys['ID']."\" parentId=\"".$categorys['IBLOCK_SECTION_ID']."\">".$categorys['NAME']."</category>";
					$categoryList[] =$categorys["ID"]; 
				}
			}
		}else 
		{ */
			//Добавляем  в сессию категори.
			foreach($arParentSection as $categorys)
			{ 
				if (!in_array($categorys["ID"], $_SESSION["YML"]["categoryList"])){
					if ($categorys["DEPTH_LEVEL"]==1){
						if (!in_array("<category id=\"".$categorys['ID']."\">".strip_tags(parserDescription(html_entity_decode(HTMLToTxt($categorys['NAME'])), ENT_QUOTES, cp1251))."</category>", $_SESSION["YML"]["category"])){
							$_SESSION["YML"]["category"][] = "<category id=\"".$categorys['ID']."\">".strip_tags(parserDescription(html_entity_decode(HTMLToTxt($categorys['NAME'])), ENT_QUOTES, cp1251))."</category>";
							$_SESSION["YML"]["categoryList"][] = $categorys["ID"];  
						}
					}else{
						if (!in_array("<category id=".$categorys['ID']." parentId=\"".$categorys['IBLOCK_SECTION_ID']."\">".strip_tags(parserDescription(html_entity_decode(HTMLToTxt($categorys['NAME'])), ENT_QUOTES, cp1251))."</category>", $_SESSION["YML"]["category"])){
							$_SESSION["YML"]["category"][] = "<category id=\"".$categorys['ID']."\" parentId=\"".$categorys['IBLOCK_SECTION_ID']."\">".strip_tags(parserDescription(html_entity_decode(HTMLToTxt($categorys['NAME'])), ENT_QUOTES, cp1251))."</category>";
							$_SESSION["YML"]["categoryList"][] = $categorys["ID"]; 
						}
					}
				}
		
			}
		/* } */

	 }

 
 $i=0;
 $offer = array(); //собираем массив элементов
	if(is_set($_POST['Element'])) {
		if (is_set($_SESSION["YML"]["Elements"])){
				$ElementIDList = array_merge_recursive($_POST['Element'], $_SESSION["YML"]["Elements"]);
			}else{
				$ElementIDList = $_POST['Element'];
			}
		$arFilter = array(
			"ID" => $ElementIDList,
			"IBLOCK_ID" => 7,
			"IBLOCK_SECTION_ID" => $_POST["SelsctSection"],
		);	
		
		$arSelect = array(
			"ID", "NAME", "IBLOCK_SECTION_ID", "DETAIL_PAGE_URL", "DETAIL_TEXT", "PROPERTY_CHPU"
		);
		$dbElement = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
		$dbElement->SetUrlTemplates("/product/_ELEMENT_/#SECTION_ID#.#ID#.html");
		while ($objElement = $dbElement->GetNextElement()){
			$arElement = $objElement->GetFields();
			$propElement = $objElement->GetProperties();
			$res = CIBlockSection::GetByID($arElement["IBLOCK_SECTION_ID"]);
			if($ar_res = $res->GetNext()){
				$name = $ar_res['NAME'];
				$dbBrand = CIBlockSection::GetByID($ar_res['IBLOCK_SECTION_ID']);
				$Brand = $dbBrand->GetNext();
				//$dbparentSection = CIBlockSection::GetByID();
				//$parentSection = $dbparentSection->GetNext();
			  }			
			$Price =  CPrice::GetBasePrice($arElement["ID"]);												 //
			$arElement["PRICE"] = $Price["PRICE"];															 //Получаем цену
			$arElement["DETAIL_PAGE_URL"] = "http://".$_SERVER['SERVER_NAME'].$arElement["DETAIL_PAGE_URL"]; //форируем урл на детальную странцу
			$arElement["NAME"] = $name." ". $arElement["NAME"];				//формируем имя
			if(is_array($propElement["IMG255"]["VALUE"])){					//массив картинок
				foreach ($propElement["IMG255"]["VALUE"] as $Img255){
					$arElement["IMG"][]= CFile::GetPath($Img255);
				}
			}
		$offer[]="<offer id=\"".$arElement['ID']."\" available=\"true\" bid=\"1\">\n<url>".$arElement["DETAIL_PAGE_URL"]."</url>\n<price>".$arElement["PRICE"] ."</price>\n<currencyId>RUR</currencyId>\n<categoryId>".$Brand["IBLOCK_SECTION_ID"]."</categoryId>\n<picture>http://".$_SERVER['SERVER_NAME'].parserImg($arElement["IMG"][0])."</picture>\n<delivery>true</delivery>\n<local_delivery_cost>350</local_delivery_cost>\n<name>".strip_tags(parserDescription(html_entity_decode(HTMLToTxt($arElement["NAME"])), ENT_QUOTES, cp1251))."</name>\n<vendor>".strip_tags(parserDescription(html_entity_decode(HTMLToTxt($Brand["NAME"])), ENT_QUOTES, cp1251))."</vendor>\n<description>".
		strip_tags(parserDescription(html_entity_decode(HTMLToTxt($arElement["DETAIL_TEXT"])), ENT_QUOTES, cp1251))."</description>\n<sales_notes></sales_notes>\n</offer>\n";
		
		//$trans = get_html_translation_table(HTML_SPECIALCHARS, ENT_QUOTES);
		//echo strip_tags(html_entity_decode(parserDescription(HTMLToTxt($arElement["DETAIL_TEXT"])), ENT_QUOTES, cp1251));
		$i++;
		//echo parserImg($arElement["IMG"][0])."<br/>";
		}
		echo "Добавлено элементов:".$i."<br/>";
	}
	
}
/* if (!is_set($_SESSION["YML"]["category"]))
	$_SESSION["YML"]["category"] = $category;
else 
	$_SESSION["YML"]["category"] = array_merge_recursive( $_SESSION["YML"]["category"], $category); */
$_SESSION["YML"]["units"] = $offer;

//$_SESSION["YML"]["categoryList"] = $categoryList;
$_SESSION["YML"]["SelsctSection"] = $_POST["SelsctSection"];

$_SESSION["YML"]["Elements"] = $ElementIDList;


?>


<a href="addlist.php" target="new">Что уже добавлено?(откроется в новой вкладке)</a><br />
<a href="xml.php">Добавить еще товары</a><br/>
<a href="create.php">Выгрузить</a>
<?
function parserDescription($desc)
{
	$patternsq[0] = "/&laquo;/";
	$patternsq[1] = "/&raquo;/";
	$result = preg_replace( $patternsq, "\"" , $desc);
	$result = preg_replace( "/&nbsp;/", " " , $result);
	$patterns= array ("/&ndash;/", "/&mdash;/");
	$result = preg_replace( $patterns, "—" , $result);
	$patterns= array ("/&/");	
	$result = preg_replace( $patterns, "&amp; " , $result);
	$patterns= array ("/\"/");
	$result = preg_replace( $patterns, "&quot;" , $result);
	return $result;
}
function parserImg($img){
	$result = str_replace(' ', '%20', $img);
	return $result;
}

?>