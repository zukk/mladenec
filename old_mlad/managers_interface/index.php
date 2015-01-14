<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
function noNullGet($prp) {
	return (isset($_GET['PROP'][$prp]) && $_GET['PROP'][$prp]>0) ? $_GET['PROP'][$prp] : 0;
}
CModule::IncludeModule("catalog");
$arFields = Array(
  "IBLOCK_ID" => "7",
 );
 
$BRANDS = array();
$arFilter = Array('IBLOCK_ID'=>"11");
$arSelect = Array("ID", "NAME");
$db_list = CIBlockElement::GetList(Array($by=>$order), $arFilter, false, false, $arSelect);
while($ar_result = $db_list->GetNext()) {
	$BRANDS[$ar_result["ID"]] = $ar_result["NAME"];
}
asort($BRANDS);
$currBrand = noNullGet('BRAND');
if(isset($_GET['PROP']['BRAND']) && empty($currBrand)) {
	unset($_GET['PROP']['BRAND']);
}

$arFilter = array('DEPTH_LEVEL'=>2);
$rsSections = CIBlockSection::GetList($arOrder, $arFilter, false);
while($arSection = $rsSections->GetNext()) {
	$SECTIONS[$arSection["ID"]] = $arSection["NAME"];
}

asort($SECTIONS);
$SECTION_ID = (isset($_GET['SECTION_ID']) && !empty($_GET['SECTION_ID'])) ? $_GET['SECTION_ID'] : false;
?>
<form>
<table>
	<tr>
		<td valign="top" class="field-name">Категория товара</td>
		<td colspan="2">
		<select name="SECTION_ID">
		<option value=''>(все)</option>
		<?php foreach ($SECTIONS as $sectionId=>$sectionName):?>
			<option value="<?php echo $sectionId;?>"<?php if($SECTION_ID==$sectionId):?> selected<?php endif;?>><?php echo $sectionName?></option>
		<?php endforeach;?>
		</select>
		</td>
	</tr>
	<tr>
		<td valign="top" class="field-name">Производитель</td>
		<td colspan="2">
		<select name="PROP[BRAND]">
		<option value=''>(все)</option>
		<?php foreach ($BRANDS as $brandId=>$brandName):?>
			<option value="<?php echo $brandId;?>"<?php if($currBrand==$brandId):?> selected<?php endif;?>><?php echo $brandName?></option>
		<?php endforeach;?>
		</select>
		</td>
	</tr>
	<tr>
		<td valign="top" class="field-name">Поле</td>
		<td>Да / Не важно | </td>
		<td>Нет (исключить из выборки) / Не исключать</td>
	</tr>
	<tr>
		<td valign="top" class="field-name">_новая карточка:</td>
		<td>
			<input type="checkbox" id="26a07ec500a8da95f17bd8ed52dd9398" value="12170" name="PROP[_new_item]"<?php if(isset($_GET['PROP']['_new_item'])) echo ' checked';?>></td>
		<td>
			<input type="checkbox" id="26a07ec500a8da95f17bd8ed52dd9398" value="12170" name="PROP_[_new_item]"<?php if(isset($_GET['PROP_']['_new_item'])) echo ' checked';?>>
		</td>
	</tr>
	<tr>
		<td valign="top" class="field-name">_изменённая карточка:</td>
		<td>
			<input type="checkbox" id="56d34cb27bc456643cdfcbcc58fe6b92" value="12171" name="PROP[_modify_item]"<?php if(isset($_GET['PROP']['_modify_item'])) echo ' checked';?>></td>
		<td>
			<input type="checkbox" id="56d34cb27bc456643cdfcbcc58fe6b92" value="12171" name="PROP_[_modify_item]"<?php if(isset($_GET['PROP_']['_modify_item'])) echo ' checked';?>>
		</td>
	</tr>
	<tr>
		<td valign="top" class="field-name">_описание:</td>
		<td>
			<input type="checkbox" id="66f37296a4ee57db707c0cb6b2b443ee" value="12172" name="PROP[_desc]"<?php if(isset($_GET['PROP']['_desc'])) echo ' checked';?>></td>
		<td>
			<input type="checkbox" id="66f37296a4ee57db707c0cb6b2b443ee" value="12172" name="PROP_[_desc]"<?php if(isset($_GET['PROP_']['_desc'])) echo ' checked';?>>
		</td>
	</tr>
	<tr>
		<td valign="top" class="field-name">_оптимизирована:</td>
		<td>
		<input type="checkbox" id="743486e548863e94819ab609558ce888" value="12173" name="PROP[_optim]"<?php if(isset($_GET['PROP']['_optim'])) echo ' checked';?>></td>
		<td>
		<input type="checkbox" id="743486e548863e94819ab609558ce888" value="12173" name="PROP_[_optim]"<?php if(isset($_GET['PROP_']['_optim'])) echo ' checked';?>>
		</td>
	</tr>
	<tr>
		<td valign="top" class="field-name">_графика:</td>
		<td>
			<input type="checkbox" id="1a61223696b5148d449ce452a7e61343" value="12174" name="PROP[_graf]"<?php if(isset($_GET['PROP']['_graf'])) echo ' checked';?>></td>
		<td>
			<input type="checkbox" id="1a61223696b5148d449ce452a7e61343" value="12174" name="PROP_[_graf]"<?php if(isset($_GET['PROP_']['_graf'])) echo ' checked';?>>
		</td>
	</tr>
	<tr>
		<td valign="top" class="field-name">_качественная графика:</td>
		<td>
			<input type="checkbox" id="c64978498e1448889a462bc00e3c1746" value="12175" name="PROP[_full_graf]"<?php if(isset($_GET['PROP']['_full_graf'])) echo ' checked';?>></td>
		<td>
			<input type="checkbox" id="c64978498e1448889a462bc00e3c1746" value="12175" name="PROP_[_full_graf]"<?php if(isset($_GET['PROP_']['_full_graf'])) echo ' checked';?>>
		</td>
	</tr>
	<tr>
		<td valign="top" class="field-name">_проверено супервизором:</td>
		<td>
		<input type="checkbox" id="5eccef373194243268773f2727b85bfd" value="12176" name="PROP[_supervisor]"<?php if(isset($_GET['PROP']['_supervisor'])) echo ' checked';?>></td>
		<td>
		<input type="checkbox" id="5eccef373194243268773f2727b85bfd" value="12176" name="PROP_[_supervisor]"<?php if(isset($_GET['PROP_']['_supervisor'])) echo ' checked';?>>
		</td>
	</tr>
	<tr>
		<td valign="top" class="field-name">Активна:</td>
		<td>
		<input type="checkbox" id="5eccef373194243268773f2727b85bfd" value="Y" name="PROP[ACTIVE]"<?php if(isset($_GET['PROP']['ACTIVE'])) echo ' checked';?>></td>
		<td>
		<input type="checkbox" id="5eccef373194243268773f2727b85bfd" value="N" name="PROP_[ACTIVE]"<?php if(isset($_GET['PROP_']['ACTIVE'])) echo ' checked';?>>
		</td>
	</tr>
	<tr>
		<td valign="top" class="field-name">Есть в наличии:</td>
		<td>
		<input type="checkbox" id="5eccef373194243268773f2727b85bfd" value="1" name="PROP[CATALOG_QUANTITY]"<?php if(isset($_GET['PROP']['CATALOG_QUANTITY'])) echo ' checked';?>></td>
		<td>
		<input type="checkbox" id="5eccef373194243268773f2727b85bfd" value="0" name="PROP_[CATALOG_QUANTITY]"<?php if(isset($_GET['PROP_']['CATALOG_QUANTITY'])) echo ' checked';?>>
		</td>
	</tr>
	<tr>
		<td valign="top" class="field-name">Перелинковка:</td>
		<td>
			<input type="checkbox" id="5eccef373194243268773f2727b85bfdxx" value="Y" name="PROP[TAG_PAGE_NAMES]"<?php if(isset($_GET['PROP']['TAG_PAGE_NAMES'])) echo ' checked';?>></td>
		<td>
			<input type="checkbox" id="5eccef373194243268773f2727b85bfdx" value="Y" name="PROP[_TAG_PAGE_NAMES]"<?php if(isset($_GET['PROP']['_TAG_PAGE_NAMES'])) echo ' checked';?>>
		</td>
	</tr>
</table>
<button type="submit">Искать</button>
</form>
<?php
$i= 0;
$PROP = array();
foreach (array('BRAND','_graf', '_modify_item', '_full_graf', '_new_item', '_desc', '_optim', '_supervisor', ) as $prp)  
{
	if(isset($_GET['PROP'][$prp])) 
	{
		$PROP['PROPERTY_' . $prp]  =  $_GET['PROP'][$prp];
	}
	if(isset($_GET['PROP_'][$prp])) 
	{
		$PROP['!PROPERTY_' . $prp]  =  $_GET['PROP_'][$prp];
		if(isset($_GET['PROP'][$prp])) 
		{
			unset($PROP['PROPERTY_' . $prp]);
		}
	}
}

if ($_GET['PROP']['TAG_PAGE_NAMES'] == "Y")
{
	$PROP['!PROPERTY_TAG_PAGE_NAMES'] = false;
}
if($_GET['PROP']['_TAG_PAGE_NAMES'] == "Y")
{
	$PROP['PROPERTY_TAG_PAGE_NAMES'] = false;
}

if(isset($_GET['PROP']['CATALOG_QUANTITY'])) {
	$PROP['>CATALOG_QUANTITY'] = 0;
}
if(isset($_GET['PROP_']['CATALOG_QUANTITY'])) {
	$PROP['<=CATALOG_QUANTITY'] = 0;
}
if(isset($_GET['PROP']['ACTIVE'])) {
	$PROP['ACTIVE'] = 'Y';
}
if(isset($_GET['PROP_']['ACTIVE'])) {
	$PROP['ACTIVE'] = 'N';
}
$items = array();
$section_names = array();
if(count($PROP)):

$arFilter = $PROP;
$arFilter['IBLOCK_ID']=$arFields["IBLOCK_ID"];
if($SECTION_ID>0) {
	$arFilter['SECTION_ID'] = $SECTION_ID;
	$arFilter['INCLUDE_SUBSECTIONS'] = 'Y';
}
$db_list = CIBlockElement::GetList(Array($by=>$order), $arFilter /* array("!PROPERTY_TAG_PAGE_NAMES" => false, "IBLOCK_ID" => 7) */, false, false, array('ID', 'IBLOCK_SECTION_ID', 'CODE', 'NAME', 'PROPERTY_TAG_PAGE_NAMES'));?>
<style>ul.items li {margin:5px;}</style>

	<?php while($ar_result = $db_list->GetNext()):?>
	<? $res = CIBlockSection::GetByID($ar_result['IBLOCK_SECTION_ID']);
	$child_section_name = '---';
	if($ar_child_section = $res->GetNext()) {
		$ar_result['CHILD_SECTION_NAME'] = $ar_child_section['NAME'];
		$res2 = CIBlockSection::GetByID($ar_child_section['IBLOCK_SECTION_ID']);
		if($ar_section = $res2->GetNext()) {
			$res3 = CIBlockSection::GetByID($ar_section['IBLOCK_SECTION_ID']);
			if($ar_section = $res3->GetNext()) {
				$section_names[$ar_section['ID']] = $ar_section['NAME'];
				$items[$ar_section['ID']][] = $ar_result;
			}
		}
	}
	?>
	<?php endwhile;?>
	<?php foreach ($items as $key=>$section):?>
	<h2><?php echo $section_names[$key]?> (<?php echo count($section)?>)</h2>
	<ul class="items">
		<?php foreach ($section as $ar_result) :
		$i++;
		?>
				<li><strong><a href="/bitrix/admin/iblock_list_admin.php?IBLOCK_ID=<?php echo $arFields["IBLOCK_ID"];?>&type=catalog&lang=ru&find_section_section=<?php echo $ar_result['IBLOCK_SECTION_ID']?>" target="_blank"><?php echo $ar_result['CHILD_SECTION_NAME'];?></a></strong> --> <a href="/bitrix/admin/iblock_element_edit.php?ID=<?php echo $ar_result['ID']?>&IBLOCK_ID=<?php echo $arFields["IBLOCK_ID"];?>&lang=ru&type=catalog" target="_blank"><?php echo $ar_result['NAME']?></a> [<?php echo $ar_result['CODE']?>] <a href="/product/--/<?php echo $ar_result['IBLOCK_SECTION_ID']?>.<?php echo $ar_result['ID']?>.html" target="_blank">карточка на сайте</a></li>
		<?php endforeach;?>
	</ul>
	<?php endforeach;?>	
	
<?php endif;?>
<h3>Выбрано: <?php echo $i?></h3>
<a href="/managers_interface/count.php">статистика состояния маркеров</a>
<br /><br /><br />