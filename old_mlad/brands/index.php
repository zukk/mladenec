<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Бренды");
?> 
<style>
.brand_header {
	color:#B2D235;
	text-transform:uppercase;
	font-size:15px;
	font-weight:bold;
	padding:10px 0px 10px 0px;
}
#brand_list a {
	color:#808080;
	text-decoration:none;
}
#brand_list a:hover {
	text-decoration:underline;
}
#brand_links {
	text-align:right;
	color:#00C0F3;
	font-weight:bold;
}
#brand_links a {
	text-decoration:underline;
	font-weight:100;
}
#brand_links a:hover {
	text-decoration:none;
}
</style>
 
<div id="brand_list"> <? if($_GET["act"]=="alph"): ?> 
  <div id="brand_links" style="text-align: left; "> <a href="/brands/" >По категориям</a> &nbsp;&nbsp;&nbsp;&nbsp;По алфавиту </div>
 		<?
        $arSelect = Array("ID", "NAME");
        $arFilter = Array("IBLOCK_ID"=>"11", "ACTIVE_DATE"=>"Y", "ACTIVE"=>"Y");
        $arrBrand = array();
        $res = CIBlockElement::GetList(Array("NAME"=>"ASC"), $arFilter, false, false, $arSelect);
        while($ob = $res->GetNextElement())
        {
            $arFields = $ob->GetFields();
          
            $arFilter = Array("IBLOCK_ID"=>"7", "ACTIVE"=>"Y", "PROPERTY_BRAND"=>$arFields["ID"], "!PROPERTY_ITEM"=>"1038");
            $resCount = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
            $resCount->NavStart(1);
            $count = $resCount->SelectedRowsCount();
            $arFields["COUNT"] = $count;
            
            if($count>0) $arrBrand[] = $arFields;
            
        }
        ?> 
  <table width="100%"> 
    <tbody> 
      <tr valign="top"><td style="border-image: initial; "> 			<?
            $i=0; $old = ""; $td = true;
            foreach($arrBrand as $brand):
            $i++;
            $new = substr($brand["NAME"], 0, 1);
            if($i >= intval(count($arrBrand)/2) && $new!=$old && $td) { echo "</td><td>"; $td = false; }
            if($new!=$old) echo "<div class='brand_header'>$new</div>";
            ?> <a href="<?=$section['SECTION_PAGE_URL']?>?s=1&p[PROPERTY_BRAND]=<?=$key?>" ><?=$brand["NAME"]?> (<?=$brand["COUNT"]?>)</a> 
          <br />
         <?
            
            $old = $new;
            endforeach;
            ?> </td></tr>
     </tbody>
   </table>
 <? else: ?> 
  <div id="brand_links" style="text-align: left; "> По категориям &nbsp;&nbsp;&nbsp;&nbsp;<a href="/brands/?act=alph" >По алфавиту</a> </div>
 
  <div style="text-align: left; "><?
		  $arFilter = Array('IBLOCK_ID'=>"7", 'GLOBAL_ACTIVE'=>'Y', "DEPTH_LEVEL"=>1);
		  $db_list = CIBlockSection::GetList(Array("sort"=>"asc"), $arFilter, true);
		  $db_list->SetUrlTemplates("", "/catalog/_SECTION_/#SECTION_ID#.html");

		  echo $db_list->NavPrint($arIBTYPE["SECTION_NAME"]);
		  $arrSection = array();
		  
		  while($ar_result = $db_list->GetNext())
		  {
				$arrSection[$ar_result['ID']]['NAME'] = $ar_result['NAME'];
				$arrSection[$ar_result['ID']]['SECTION_PAGE_URL'] = $ar_result['SECTION_PAGE_URL'];
					
				$arSelect = Array("ID", "PROPERTY_BRAND");
				$arFilter = Array("IBLOCK_ID"=>"7", "ACTIVE_DATE"=>"Y", "ACTIVE"=>"Y", "SECTION_ID"=>$ar_result['ID'], "INCLUDE_SUBSECTIONS"=>"Y");
				$res = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
				$arrGroups = array();
				while($ob = $res->GetNextElement())
				{
				  $arFields = $ob->GetFields();
				  if(!isset($arrSection[$ar_result['ID']]["BRANDS"][$arFields["PROPERTY_BRAND_VALUE"]]["COUNT"])) {
				  	$arrSection[$ar_result['ID']]["BRANDS"][$arFields["PROPERTY_BRAND_VALUE"]]["COUNT"] = 0;
				  }
				  
  				$arrSection[$ar_result['ID']]["BRANDS"][$arFields["PROPERTY_BRAND_VALUE"]]["COUNT"] ++;

				}

		  }
			$brandsNames = array();
			$arSelect = Array("ID", "NAME");
			$arFilter = Array("IBLOCK_ID"=>"11", "ACTIVE_DATE"=>"Y", "ACTIVE"=>"Y");

			$res = CIBlockElement::GetList(Array("NAME"=>"ASC"), $arFilter, false, false, $arSelect);
			while($ob = $res->GetNextElement())
			{
			  $arFields = $ob->GetFields();
			  $brandsNames[$arFields['ID']] = $arFields['NAME'];
			}

		  //$arrBrands[666] = $arrBrands[444];
		 // pre($arrBrands);
		?> <? endif; ?> </div>
 
  <br />
 
  <table width="100%"> 
    <tbody> 
      <tr valign="top"><td style="border-image: initial; "> <?
    $i=0;
    foreach($arrSection as $SECTION_ID=>$section):
		$i++;

    echo "<div class='brand_header'>$section[NAME]</div>";
		foreach($section["BRANDS"] as $key=>$val):
			$name = $brandsNames[$key];
		?> 			 
          <li><a href="<?=$section['SECTION_PAGE_URL']?>?s=1&p[PROPERTY_BRAND]=<?=$key?>" ><?=$name?> (<?=$val["COUNT"]?>)</a></li>
         		<?
		endforeach;
		if(count($arrBrands)>$i) echo "<div style='border-bottom:1px dashed #808080; padding:10px 0px 10px 0px;'></div><br>";
    endforeach;
    ?> </td></tr>
     </tbody>
   </table>
 </div>
 <?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>