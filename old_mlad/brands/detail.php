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
<div id="brand_list">
<?
$BRAND_ID = (isset($_GET["ID"]) && $_GET["ID"]) ? $_GET["ID"] : 0;


$arSelect = Array("ID", "NAME", "PREVIEW_TEXT", "DETAIL_TEXT");
$arFilter = Array("IBLOCK_ID"=>"11", "ACTIVE_DATE"=>"Y", "ACTIVE"=>"Y", "ID"=>$BRAND_ID);
$arrBrand = array();
$brandResult = array();
$res = CIBlockElement::GetList(Array("NAME"=>"ASC"), $arFilter, false, false, $arSelect);
if($ob = $res->GetNextElement())
{
    $brandResult = $ob->GetFields();
    
}

if($_GET["act"]=="alph"): ?>
        <div id="brand_links">
                <a href="/brands/">По категориям</a> &nbsp;&nbsp;&nbsp;&nbsp;По алфавиту
        </div>
        
		<?
        $arSelect = Array("ID", "NAME");
        $arFilter = Array("IBLOCK_ID"=>"11", "ACTIVE_DATE"=>"Y", "ACTIVE"=>"Y", "ID"=>$BRAND_ID);
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
        
        <table width="100%"><tr valign="top"><td>
			<?
            $i=0; $old = ""; $td = true;
            foreach($arrBrand as $brand):
            $i++;
            $new = substr($brand["NAME"], 0, 1);
            if($i >= intval(count($arrBrand)/2) && $new!=$old && $td) { echo "</td><td>"; $td = false; }
            if($new!=$old) echo "<div class='brand_header'>$new</div>";
            ?>
            <a href="/catalog/?BRAND=<?=$brand["ID"]?>"><?=$brand["NAME"]?> (<?=$brand["COUNT"]?>)</a><br />
            <?
            
            $old = $new;
            endforeach;
            ?>
        </td></tr></table>
        
        
<? else: ?>
        <div id="brand_links">
                По категориям &nbsp;&nbsp;&nbsp;&nbsp;<a href="/brands/?act=alph">По алфавиту</a>
        </div>
        
        <?
		  $arFilter = Array('IBLOCK_ID'=>"6", 'GLOBAL_ACTIVE'=>'Y', "DEPTH_LEVEL"=>1);
		  $db_list = CIBlockSection::GetList(Array("sort"=>"asc"), $arFilter, true);
		  echo $db_list->NavPrint($arIBTYPE["SECTION_NAME"]);
		  $arrBrands = array();
		  
		  while($ar_result = $db_list->GetNext())
		  {
				//echo $ar_result['ID'].' '.$ar_result['NAME'].'<br>';
				
				$arSelect = Array("ID");
				$arFilter = Array("IBLOCK_ID"=>"6", "ACTIVE_DATE"=>"Y", "ACTIVE"=>"Y", "SECTION_ID"=>$ar_result['ID'], "INCLUDE_SUBSECTIONS"=>"Y", /*, "PROPERTY_BRAND"=>$BRAND_ID*/);
				$res = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
				$arrGroups = array();
				while($ob = $res->GetNextElement())
				{
				  $arFields = $ob->GetFields();
				  $arrGroups[] = $arFields["ID"];
				}
			
				if(count($arrGroups)>0):
						$arrBrands[$ar_result['ID']]["NAME"] = $ar_result['NAME'];
						$arrBrands[$ar_result['ID']]["COUNT"] = 0;
						$arSelect1 = Array("PROPERTY_BRAND");
						$arFilter1 = Array("IBLOCK_ID"=>"7", "ACTIVE_DATE"=>"Y", "ACTIVE"=>"Y", "PROPERTY_ITEM"=>$arrGroups, "PROPERTY_BRAND"=>$BRAND_ID);
						$res1 = CIBlockElement::GetList(Array(), $arFilter1, false, false, $arSelect1);
						while($ob1 = $res1->GetNextElement())
						{
							$arrBrands[$ar_result['ID']]["COUNT"]++;
						}
			
				endif;
		  }
		  //$arrBrands[666] = $arrBrands[444];
		 // pre($arrBrands);
		?>
        
<? endif; ?>
<br />
<br />
<table width="100%"><tr valign="top"><td>
    <?

    echo "<div class='brand_header'>{$brandResult["NAME"]}</div>";
    echo $brandResult["DETAIL_TEXT"];
    $i=0;
    echo "<div style='border-bottom:1px dashed #808080; padding:10px 0px 10px 0px;'></div>";
		echo "<h2>Категории:</h2>";
    foreach($arrBrands as $key=>$brand):
    if($brand["COUNT"]):
    ?>
		<a href="/catalog/?SECTION_ID=<?=$key?>&BRAND=<?=$BRAND_ID?>"><?=$brand[NAME]?> (<?=$brand["COUNT"]?>)</a><br />
    <?endif;?>
    <?endforeach;?>
</td></tr></table>

</div>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>