<?php
require($_SERVER["DOCUMENT_ROOT"].
"/bitrix/modules/main/include/prolog_before.php");
CModule::IncludeModule("iblock");
CModule::IncludeModule("catalog");
?>
<style>
	input[type="submit"]{box-shadow: 2px 2px 3px black;}
</style>
<script type="text/javascript" src="/script/jquery-1.6.2.min.js"></script>
<script type="text/javascript" src="/ozon/ozon.js"></script>
<a href="/ozon/xml.php"> сбросить </a> | <a name="up"></a><a href="#down">Вниз</a>
<form>
	<div id="section">
	    <input type="hidden" name="lvl[0]" value="1" />
		<select name="section">
			<?if(CModule::IncludeModule("iblock")){
			$arFilter = array(
			"IBLOCK_ID" => "7",
			"DEPTH_LEVEL"=> "1"			
			);
			$arSelect = array ("ID", "NAME", "DEPTH_LEVEL");
				$dbSection = CIBlockSection::GetList(array(), $arFilter, false, $arSelect);
				while ($arSection = $dbSection->GetNext()){?>
					<option value="<?=$arSection["ID"]?>"><?=$arSection["NAME"]?></option>
				<?}				
			}?>
		</select>
	</div>
<input type="hidden" name="element_list" value="go"/>
<input type="submit" value="Отобразить товары"/>
 </form>

<form action="" method="post" id="sectionX" name="section">
 
 <?if($_GET["element_list"]== "go"){?>
 <table border="1">
<tr>
	<th>Имя</th>
	<th>Картинка</th>
	<th>Описание</th>
	<th>Наличие на складе</th>
</tr>
 <? 
	$r=0;
	$section = $_GET["section"];	
	$arSelect = array("ID", "NAME", "DESCRIPTION", "IBLOCK_SECTION_ID", "CODE", "DETAIL_TEXT", "PROPERTY_IMG500", "PROPERTY_CHPU", "DETAIL_PAGE_URL");
	$dbElementList = CIBlockElement::GetList(Array("PROPERTY_BARND"=>"ASC"), Array("SECTION_ID" =>$section, "IBLOCK_ID" => 7, "ACTIVE"=>"Y", "INCLUDE_SUBSECTIONS" => "Y"));
	$dbElementList->SetUrlTemplates("/product/_ELEMENT_/#SECTION_ID#.#ID#.html");
	while( $obElement= $dbElementList->GetNextElement()){
		$arElement = $obElement->GetFields();
		$arElementProp = $obElement->GetProperties();
		$arElSel = CCatalogProduct::GetByID($arElement["ID"]);
	$dbForFullName = CIBlockSection::GetByID($arElement['IBLOCK_SECTION_ID'] );
	if ($arForFullName = $dbForFullName->GetNext()){
		$Item["Name"] = $arForFullName['NAME']." ".$arElement['NAME'];
		$dbBrand = CIBlockSection::GetByID($arForFullName['IBLOCK_SECTION_ID']);
		$Brand = $dbBrand->GetNext();	
	}
	?>
	
	<tr <?if($arElSel["QUANTITY"] == 0 or !(is_array($arElementProp['IMG500']['VALUE']))){?> style="background:#ff7575;"<?}else{ $r++;}?>>
		<td>	
			<input class="elem_cb" type="checkbox" id="<?=$arElement['ID']?>" name="Element[]" value="<?=$arElement['ID']?>"  <?if($arElSel["QUANTITY"] != 0 and is_array($arElementProp['IMG500']['VALUE']) ){?> checked="checked"<?}?>/>
			<label for="<?=$arElement['ID']?>"><?=$Brand['NAME']?> | <?=$Item["Name"]?></label>
			<a href="/bitrix/admin/iblock_element_edit.php?WF=Y&ID=<?=$arElement["ID"]?>&type=catalog&lang=ru&IBLOCK_ID=7&find_section_section=<?=$arElement['IBLOCK_SECTION_ID']?>">Редактировать</a> | <a href="<?=$arElement['DETAIL_PAGE_URL']?>">ссылка на карточку</a>
		</td>
		<td>
			<?if (is_array($arElementProp['IMG500']['VALUE'])){?>
				<span style="color: green;">Есть</span>
			<?}else{?>
			  <span style='color: red;'><b>Нет фото</b></span>
			<?}?>		
		</td>
		<td>
			<?if ($arElement["DETAIL_TEXT"] != ""){?>
				<span style="color: green;">Есть</span>
			<?}else{
				echo "<span style='color: red;'><b>Нет</b></span>";
			};?>
		</td>
		
			<?if ($arElSel["QUANTITY"] > 0){?>
			<td style="background: green;">
				<span style="color: white;">Есть</span>
			<?}else{?>
			<td style="background: red;">
				<span style='color: white;'><b>Нет</b></span>
			<?}?>
		</td>
	</tr>
	<?}?>
	</table>
<?}?>

<input type="hidden" name="SelsctSection" value="<?=$section?>" />
<span id="unselect" style="text-align: center; display: block; border: 1px solid red; width: 130px; cursor:pointer; background: red; color: #fff; padding: 5px; margin:10px 0px; border-radius: 10px; border: 1px solid #ccc; box-shadow: 2px 3px 4px black;">Отключить все</span>
<span id="selectall" style="text-align: center; display: none; border: 1px solid green; width: 130px; cursor:pointer; background: green; color:#fff; padding: 5px; margin:10px 0px; border-radius: 10px; border: 1px solid #ccc; box-shadow: 2px 3px 4px black;" >Включить все</span>
<input type="hidden" name="start"/>
<?/*<input type="text" placeholder="Номер секции в озоне" />*/?>
<select name = "ozonSection" >
	<option value="12475938">Десерты</option>
	<option value="12469061">Детская вода и напитки - Детская вода</option>
	<option value="12469064">Детская вода и напитки - Детский чай</option>
	<option value="12469068">Детская вода и напитки - Соки и напитки для детей</option>
	<option value="12469071">Детские каши - Безмолочные</option>
	<option value="12469074">Детские каши - Молочные</option>
	<option value="12469077">Детское печенье, хлебцы, гематоген - Гематоген</option>
	<option value="12469080">Детское печенье, хлебцы, гематоген - Печенье</option>
	<option value="12469083">Детское печенье, хлебцы, гематоген - Хлебцы</option>
	<option value="12469086">Детское пюре - Мясные пюре</option>
	<option value="12469089">Детское пюре - Овощные пюре</option>
	<option value="12469092">Детское пюре - Фруктовые пюре</option>
	<option value="12469095">Заменители материнского молока и сухие смеси - Безмолочные</option>
	<option value="12469098">Заменители материнского молока и сухие смеси - Молочные</opion>
	<option value="12469109">Молочная продукция - Йогурт</option>
	<option value="12469106">Молочная продукция - Кефир</option>
	<option value="12469119">Молочная продукция - Кисломолочные смеси</option>
	<option value="12469112">Молочная продукция - Коктейли</option>
	<option value="12469103">Молочная продукция - Молоко</option>
	<option value="12473934">Молочная продукция - Сливки</option>
	<option value="12469965">Молочная продукция - Творог</option>
</select>
<input type="submit" value="Start"/>
<br/>
К выгружке готово <?=$r?> элементов.
<br/>
<a name="down"></a><a href="#up">Вверх</a>
</form>
<?if (isset($_POST["start"])){
$header_XML = <<<XML
<?xml version='1.0' encoding="UTF-8" ?>
<!DOCTYPE Items SYSTEM "http://merchants.ozon.ru/xml/MerchantItems.dtd">
<Items>
XML;
//$header_XML.="";
$file = fopen('ozon.xml', 'w+');


if(CModule::IncludeModule("iblock")){
$Select = array("ID", "NAME", "DESCRIPTION", "IBLOCK_SECTION_ID", "CODE", "DETAIL_TEXT", "PROPERTY_IMG500");
$dbElement = CIBlockElement::GetList(array(), array("IBLOCK_ID" => "7", "SECTION_ID" => $_POST["SelsctSection"], "INCLUDE_SUBSECTIONS"=>"Y", "ID"=>$_POST["Element"]), false, false);

echo $header_XML;
$item = array();
$i=0;
while ($objElement = $dbElement->GetNextElement()){
$arProp = $objElement->GetProperties();
$arElement = $objElement->GetFields();
$arElPrice =  CPrice::GetBasePrice($arElement["ID"]);
	$header_XML.= "<Item>
		<Images>";
		foreach ($arProp[IMG500][VALUE] as $img500){
			$header_XML .= "<Picture url=\"http://".$_SERVER['SERVER_NAME']."".parserImg(CFile::GetPath($img500))."\" />";
			}
		$header_XML .="</Images>
		<StateID>1733000</StateID>
		<ItemTypeID>".$_POST["ozonSection"]."</ItemTypeID>
		";

		$dbSection = CIBlockSection::GetByID($arElement['IBLOCK_SECTION_ID']);
		if ($arSection = $dbSection->GetNext()){
			$Item["Name"] = $arSection['NAME']." ".$arElement['NAME'];
		}
		$header_XML.="<Name><![CDATA[ ".iconv("cp1251", "UTF-8", strip_tags(parserDescription(html_entity_decode(HTMLToTxt($Item["Name"])), ENT_QUOTES, cp1251)))."]]></Name>
		<Descript><![CDATA[ ".iconv("cp1251", "UTF-8//IGNORE", strip_tags(parserDescription(html_entity_decode(HTMLToTxt($arElement["DETAIL_TEXT"]))), ENT_QUOTES))."]]></Descript>
		<Article>".iconv("cp1251", "UTF-8", strip_tags(parserDescription(html_entity_decode(HTMLToTxt($arElement["CODE"])), ENT_QUOTES, cp1251)))."</Article>
		<Price>".$arElPrice[PRICE]."</Price>
		<Qty>100</Qty><ItemAvailabilityID>3719000</ItemAvailabilityID></Item>";
	$i++;
	//if ($i>20) {break;}
}
$header_XML.="</Items>";
}
	//$writr = iconv("cp1251", "UTF-8", $writr);
	$writr = fwrite($file, $header_XML);
	if($writr){ echo "файл записан Элементов выгружено: ".$i."<br />";
		echo "<a href='ozon.xml' target='new'>ПОсмотреть содержание</a>";
	}
	
	}
//lib
	
function parserDescription($desc){
	$patternsq[0] = "/&laquo;/";
	$patternsq[1] = "/&raquo;/";
	$result = preg_replace( $patternsq, "\"" , $desc);
	$result = preg_replace( "/&nbsp;/", " " , $result);
	$patterns= array ("/&ndash;/", "/&mdash;/");
	$result = preg_replace( $patterns, " - " , $result);	
	$result = preg_replace( "/°С /", "С", $result);
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
Выгружено <?=$i?> элементов.	


