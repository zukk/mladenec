<?
require($_SERVER["DOCUMENT_ROOT"].
"/bitrix/modules/main/include/prolog_before.php");
CModule::IncludeModule("iblock");
CModule::IncludeModule("catalog");
if ($_GET["clear"] == "Y"){
	unset($_SESSION["YML"]);
}
if ($_SESSION["YML"]){
?>
<table border="1">
<tr>
	<th>���</th>
	<th>��������</th>
	<th>��������</th>
</tr>
 <? 
	//$section = $_SESSION["YML"]["SelsctSection"];
	
	$arSelect = array("ID", "NAME", "DESCRIPTION", "IBLOCK_SECTION_ID", "CODE", "DETAIL_TEXT", "PROPERTY_IMG255");
	$arFilter = array(
		"ID" =>	$_SESSION["YML"]["Elements"],
		//"SECTION_ID" => $_SESSION["YML"]["SelsctSection"],
		"IBLOCK_ID" => 7,
		"ACTIVE"=>"Y", 
		"INCLUDE_SUBSECTIONS" => "Y"
	);
	$dbElementList = CIBlockElement::GetList(Array("PROPERTY_BARND"=>"ASC"), $arFilter);
	while( $obElement= $dbElementList->GetNextElement()){
		$arElement = $obElement->GetFields();
		$arElementProp = $obElement->GetProperties();
	$dbForFullName = CIBlockSection::GetByID($arElement['IBLOCK_SECTION_ID'] );
	if ($arForFullName = $dbForFullName->GetNext()){
		$Item["Name"] = $arForFullName['NAME']." ".$arElement['NAME'];
		$dbBrand = CIBlockSection::GetByID($arForFullName['IBLOCK_SECTION_ID']);
		$Brand = $dbBrand->GetNext();	
	}
	?>
	
	<tr>
		<td>	
			<input type="checkbox" id="<?=$arElement['ID']?>" name="Element[]" value="<?=$arElement['ID']?>" checked="checked"/>
			<label for="<?=$arElement['ID']?>"><?=$Brand['NAME']?> | <?=$Item["Name"]?></label>
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
		</td>
	</tr>
	<?}?>
	</table>
	<?		
	}else{
	echo "��� ���������� ���������.<br/>";
	}?>
<a href="xml.php">�������� ��� ������</a><br/>
<a href="create.php">���������</a><br/>
<form method="GET">
	<input type="hidden" name="clear" value="Y"/>
	<button type="submit">��������</button>
</form>
