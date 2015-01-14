<?php
require($_SERVER["DOCUMENT_ROOT"].
"/bitrix/modules/main/include/prolog_before.php");
CModule::IncludeModule("iblock");
CModule::IncludeModule("catalog");
$YML= array();
$IBLOCK_ID= 7;
/////

//Формируем список категорий
$sections = CIBlockSection::GetList(array(),array(
	'IBLOCK_ID'=>$IBLOCK_ID,
	'ACTIVE'=>'Y',
	'GLOBAL_ACTIVE'=>'Y',
	'<=DEPTH_LEVEL'=>2
));
while($section = $sections->GetNext()) {
	if ($section["DEPTH_LEVEL"] == 1) {
		$YML["category"][] = "<category id=\"" . $section['ID'] . "\">" . strip_tags(parserDescription(html_entity_decode(HTMLToTxt($section['NAME'])), ENT_QUOTES, cp1251)) . "</category>\n";
	} else {
		$YML["category"][] = "<category id=\"" . $section['ID'] . "\" parentId=\"" . $section['IBLOCK_SECTION_ID'] . "\">" . strip_tags(parserDescription(html_entity_decode(HTMLToTxt($section['NAME'])), ENT_QUOTES, cp1251)) . "</category>\n";
	}
}

//Формируем список элементов
$arFilter = array(
	'IBLOCK_ID' => $IBLOCK_ID,
	'ACTIVE'=>'Y',
	'GLOBAL_ACTIVE'=>'Y',
	'IBLOCK_ACTIVE' => 'Y',
	'ACTIVE_DATE' => 'Y',
	'>CATALOG_QUANTITY'=>0,
	'PROPERTY_TO_WIKIMART'=>'12444',
	'>CATALOG_PRICE_1'=>'0'
);

$arSelect = array(
	"ID", "NAME", "IBLOCK_SECTION_ID", "DETAIL_PAGE_URL", "DETAIL_TEXT", "PROPERTY_CHPU"
);
$elements = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
$elements->SetUrlTemplates("/product/_ELEMENT_/#SECTION_ID#.#ID#.html");
while ($element = $elements->GetNextElement()) {
	$arElement = $element->GetFields();
	$propElement = $element->GetProperties();
	$res = CIBlockSection::GetByID($arElement["IBLOCK_SECTION_ID"]);
	if ($ar_res = $res->GetNext()) {
		$name = $ar_res['NAME'];
		$dbBrand = CIBlockSection::GetByID($ar_res['IBLOCK_SECTION_ID']);
		$Brand = $dbBrand->GetNext();
	}
	$Price = CPrice::GetBasePrice($arElement["ID"]);			
	$arElement["PRICE"] = $Price["PRICE"];				//Получаем цену
	$arElement["DETAIL_PAGE_URL"] = "http://" . $_SERVER['SERVER_NAME'] . $arElement["DETAIL_PAGE_URL"]; //форируем урл на детальную странцу
	$arElement["NAME"] = $name . " " . $arElement["NAME"];	//формируем имя
	if (is_array($propElement["IMG255"]["VALUE"])) {	 //массив картинок
		foreach ($propElement["IMG255"]["VALUE"] as $Img255) {
			$arElement["IMG"][] = CFile::GetPath($Img255);
		}
	}
	$YML["offers"][] = "
		<offer id=\"" . $arElement['ID'] . "\" available=\"true\" bid=\"1\">
			<url>" . $arElement["DETAIL_PAGE_URL"] . "</url>
			<price>" . $arElement["PRICE"] . "</price>
			<currencyId>RUR</currencyId>
			<categoryId>" . $Brand["IBLOCK_SECTION_ID"] . "</categoryId>
			".($arElement["IMG"][0]!='' ? "<picture>http://" . $_SERVER['SERVER_NAME'] . parserImg($arElement["IMG"][0]) . "</picture>" : '')."
			<delivery>true</delivery>
			<local_delivery_cost>350</local_delivery_cost>
			<name>" . strip_tags(parserDescription(html_entity_decode(HTMLToTxt($arElement["NAME"])), ENT_QUOTES, cp1251)) . "</name>
			<vendor>" . strip_tags(parserDescription(html_entity_decode(HTMLToTxt($Brand["NAME"])), ENT_QUOTES, cp1251)) . "</vendor>
			<description>"
				.strip_tags(parserDescription(html_entity_decode(HTMLToTxt($arElement["DETAIL_TEXT"])), ENT_QUOTES, cp1251))
			."</description>
		</offer>\n";

	
}

createYML($YML);



//Вспомогательные функции
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
function createYML($YML){
	$fileName = "wikiYML.xml";
	$file = fopen($fileName, 'w+');
	
	$data = date("Y-m-d H:i");
	
	$XML_header = '<?xml version="1.0" encoding="windows-1251"?>'
	.'<!DOCTYPE yml_catalog SYSTEM "shops.dtd">';
	$Xml_Body = "<yml_catalog date=\"".$data."\">
		<shop>
			<name>mladenec.ru</name>
				<company>ООО \"младенец.ру\"</company>
				<url>http://www.mladenec-shop.ru/</url>
				<platform>\"1С-Битрикс: Управление сайтом\"</platform>
				<version>10.0.7</version>
				<agency></agency>
				<email>parfenov.a@mladenec.ru</email>

				<currencies>
						<currency id=\"RUR\" rate=\"1\"/>
						<currency id=\"USD\" rate=\"CBRF\" plus=\"1\"/>
				</currencies>
	<categories>";
	foreach ($YML["category"] as $section){
	$Xml_Body .= $section;
	}
	$Xml_Body .= "</categories>\n<local_delivery_cost>350</local_delivery_cost>\n<offers>";

	foreach ($YML["offers"] as $offer){
	$Xml_Body .= $offer;
	}
	$Xml_Body .= "</offers>\n</shop>
	</yml_catalog>";

	$XML = $XML_header. $Xml_Body;
	fwrite($file, $XML);
	fclose($file);
}