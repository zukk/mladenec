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
<script type="text/javascript" src="/yml/ozon.js"></script>
<a href="/yml/xml.php"> �������� </a>
<form>
	<div id="section">
	    <input type="hidden" name="lvl[0]" value="1" />
		<select name="section" class="kat">
		<option value="0">������� ���������</option>
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
<input type="submit" />
 </form>

 <form action="yml.php" method="post" id="sectionX" name="section">
 
 <?if($_GET["element_list"]== "go"){?>
 <table border="1">
<tr>
	<th>���</th>
	<th>��������</th>
	<th>��������</th>	<th>������� �� ������</th>
</tr>
 <? 
	$section = $_GET["section"];	
	$arSelect = array("ID", "NAME", "DESCRIPTION", "IBLOCK_SECTION_ID", "CODE", "DETAIL_TEXT", "PROPERTY_IMG255");
	$dbElementList = CIBlockElement::GetList(Array("PROPERTY_BARND"=>"ASC"), Array("SECTION_ID" =>$section, "IBLOCK_ID" => 7, "ACTIVE"=>"Y", "INCLUDE_SUBSECTIONS" => "Y"));
	$dbElementList->SetUrlTemplates("/product/_ELEMENT_/#SECTION_ID#.#ID#.html");	while( $obElement= $dbElementList->GetNextElement()){
		$arElement = $obElement->GetFields();
		$arElementProp = $obElement->GetProperties();		$arElSel = CCatalogProduct::GetByID($arElement["ID"]);
	$dbForFullName = CIBlockSection::GetByID($arElement['IBLOCK_SECTION_ID'] );
	if ($arForFullName = $dbForFullName->GetNext()){
		$Item["Name"] = $arForFullName['NAME']." ".$arElement['NAME'];
		$dbBrand = CIBlockSection::GetByID($arForFullName['IBLOCK_SECTION_ID']);
		$Brand = $dbBrand->GetNext();	
	}
	?>
	
	<tr <?if($arElSel["QUANTITY"] == 0){?> style="background:#ff7575;"<?}?>>
		<td>	
			<input class="elem_cb" type="checkbox" id="<?=$arElement['ID']?>" name="Element[]" value="<?=$arElement['ID']?>" <?if($arElSel["QUANTITY"] > 0){?> checked="checked"<?}?>/>			<label for="<?=$arElement['ID']?>"><?=$Brand['NAME']?> | <?=$Item["Name"]?></label>			<a href="http://www.mladenec-shop.ru/bitrix/admin/iblock_element_edit.php?WF=Y&ID=<?=$arElement["ID"]?>&type=catalog&lang=ru&IBLOCK_ID=7&find_section_section=<?=$arElement['IBLOCK_SECTION_ID']?>">�������������</a> | <a href="<?=$arElement['DETAIL_PAGE_URL']?>">������ �� ��������</a>
		</td>
		<td>
			<?if (is_array($arElementProp['IMG255']['VALUE'])){?>
				<span style="color: green;">����</span>
			<?}else{?>
			  <span style='color: red;'><b>��� ����</b></span>
			<?}?>		
		</td>
		<td>
			<?if ($arElement["DETAIL_TEXT"] != ""){?>
				<span style="color: green;">����</span>
			<?}else{
				echo "<span style='color: red;'><b>���</b></span>";
			};?>
			</td>			<?if ($arElSel["QUANTITY"] > 0){?>			<td style="background: green;">				<span style="color: white;">����</span>			<?}else{?>			<td style="background: red;">				<span style='color: white;'><b>���</b></span>			<?}?>		</td>			
	</tr>
	<?}?>
	</table>
<?}?>

<input type="hidden" name="SelsctSection" value="<?=$section?>" />
<span id="unselect" style="text-align: center; display: block; border: 1px solid red; width: 130px; cursor:pointer; background: red; color: #fff; padding: 5px; margin:10px 0px; border-radius: 10px; border: 1px solid #ccc; box-shadow: 2px 3px 4px black;">��������� ���</span>
<span id="selectall" style="text-align: center; display: none; border: 1px solid green; width: 130px; cursor:pointer; background: green; color:#fff; padding: 5px; margin:10px 0px; border-radius: 10px; border: 1px solid #ccc; box-shadow: 2px 3px 4px black;" >�������� ���</span>
<input type="hidden" name="start"/>
<input type="submit" value="Add to YML"/>
</form>

<a href="/yml/addlist.php" target="new">��� ��� ���������?(��������� � ����� �������)</a><br /><a href="/yml/create.php">���������</a><br />
<a href="/yml/price/YML.xml" id="show_file" target="new">����� � �������</a>
<?php
	$FileList = scandir('price', 1);
	$i=0;
?>
<br />



