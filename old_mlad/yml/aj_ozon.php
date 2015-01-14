<?php
require($_SERVER["DOCUMENT_ROOT"].
"/bitrix/modules/main/include/prolog_before.php");

$DLVL = intval($_POST["dlvl"])+1;
$SectID = intval($_POST["SectID"]);
if(CModule::IncludeModule("iblock")){
 $dbSectionName = CIBlockSection::GetByID($SectID);
 $arSectionName =  $dbSectionName->GetNext();
}
?>
<p class="navi"><?=iconv("UTF-8", "WINDOWS-1251", $_POST["navi"])?><?=$arSectionName["NAME"]?> / </p>
<input type="hidden" name="lvl[<?=$DLVL?>]" value="<?=$DLVL?>" />
<select name="section" class="kat" > 
<option value=<?=$SectID?>>Select</option>
<?
if(CModule::IncludeModule("iblock")){
$arFilter = array(
			"IBLOCK_ID" 	=> "7",
			"DEPTH_LEVEL"	=> $DLVL,
			"SECTION_ID" 	=> $SectID
			);
$arSelect = array ("ID","NAME", "DEPTH_LEVEL");		

$dbSection = CIBlockSection::GetList(array(), $arFilter, false);
while ($arSection = $dbSection->GetNext()){?>
	<option value="<?=$arSection["ID"]?>"><?=$arSection["NAME"]?></option>
	
<?
}
}
?>
</select>