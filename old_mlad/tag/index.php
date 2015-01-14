<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Карта тэговых страниц");
$arResult = array();
$i="0";
 if(CModule::IncludeModule("iblock"))
{ 
$arSection = array();
	$dbSection = CIBlockSection::GetList( Array("left_margin"=>"ASC", "depth_level"=>"ASC", ), Array("IBLOCK_ID"=>1373, ));
		while($res = $dbSection->GetNext())
		{
			$arSection[$i] = $res;
			$dbElement = CIBlockElement::GetList(Array(), Array("SECTION_ID"=>$res["ID"]), false, Array());
			$dbElement->SetUrlTemplates('/tag/#CODE#.html');
			while($arElement = $dbElement->GetNext())
			{
				 $arSection[$i]["Elements"][] = $arElement;
			}
			$i++;
		}
	
} 
//p($arSection);
 foreach ($arSection as $Section){
?>
<style>
	.cat_name { font-weight: bold;}
</style>

			<div <?if ($Section["DEPTH_LEVEL"] == 2 ) {echo "style='margin-left: 20px; padding-left: 10px; border-left: 1px dotted green;'";}elseif($Section["DEPTH_LEVEL"] > 2 ){echo "style='margin-left: 40px; padding-left: 10px; border-left: 2px dotted green;'";}else{echo "style='margin-left: 0px; padding-left: 10px;'";}?>>
					<p class="cat_name" <?if ($Section["DEPTH_LEVEL"] == 1 ){echo "style=\" color: #00AAEE; font-size: 120%;\"";}?>>
						<?if ($Section["DEPTH_LEVEL"] > 1 )
							{
								for ( $iter = 0; $Section["DEPTH_LEVEL"] - 1 > $iter; $iter++)
								{
									echo "-";
								}
							echo ">";
							}
	
					?> <?=$Section["NAME"]?></p>
						<ul style="display:block;">
							<?foreach($Section["Elements"] as $Element){?>
							<li><a href="<?=str_replace('%2F', '/', $Element["DETAIL_PAGE_URL"])?>"><?=$Element["NAME"]?></a>
							</li>
							 
							<?
							//p($Element);
							}?>
						</ul>
			</div>	

<?
}
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>