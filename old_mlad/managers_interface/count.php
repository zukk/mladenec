<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$count = 0;
$array = array('PROPERTY__GRAF_VALUE'=>0, 'PROPERTY__FULL_GRAF_VALUE'=>0, 'PROPERTY__MODIFY_ITEM_VALUE'=>0, 'PROPERTY__NEW_ITEM_VALUE'=>0, 'PROPERTY__DESC_VALUE'=>0, 'PROPERTY__OPTIM_VALUE'=>0, 'PROPERTY__SUPERVISOR_VALUE'=>0, 'CATALOG_QUANTITY'=>0);
$arFilter = array('IBLOCK_ID'=>7, 'INCLUDE_SUBSECTIONS'=>'Y');
$db_list = CIBlockElement::GetList(Array($by=>$order), $arFilter, false, Array("nPageSize"=>15000), array('PROPERTY__graf', 'PROPERTY__modify_item', 'PROPERTY__full_graf', 'PROPERTY__new_item', 'PROPERTY__desc', 'PROPERTY__optim', 'PROPERTY__supervisor', 'CATALOG_QUANTITY'));
while($ar_result = $db_list->GetNext()) {
	foreach (array_keys($array)as $prp) {
		if(!empty($ar_result[$prp]) || ($ar_result[$prp])) {
			$array[$prp] ++;
		}
	}
	$count ++;
}
?>
<h3><?php echo $array['PROPERTY__NEW_ITEM_VALUE']?> ����� ��������</h3>	
<h3><?php echo $array['PROPERTY__MODIFY_ITEM_VALUE']?> ���������</h3>		
<h3><?php echo $array['PROPERTY__DESC_VALUE']?> � ���������</h3>		
<h3><?php echo $array['PROPERTY__OPTIM_VALUE']?> ���������������</h3>	
<h3><?php echo $array['PROPERTY__GRAF_VALUE']?> � ��������</h3>		
<h3><?php echo $array['PROPERTY__FULL_GRAF_VALUE']?> � ������������ ��������</h3>
<h3><?php echo $array['PROPERTY__SUPERVISOR_VALUE']?> ��������� ������������</h3>
<h3><?php echo $array['CATALOG_QUANTITY']?> ���� � �������</h3>
<hr />
<h3>�����: <?php echo $count?> �������</h3>
<a href="/managers_interface/">� ������� �� ��������</a>
<br /><br /><br />