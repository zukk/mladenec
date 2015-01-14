<?php 
require($_SERVER["DOCUMENT_ROOT"].
"/bitrix/modules/main/include/prolog_before.php");
CModule::IncludeModule("iblock");
CModule::IncludeModule("catalog");
///////// LIBERY
	/*---����� �� �������� ������---*/
	function ItemName($Name)                
	{
		$arItemID=array();
		if(CModule::IncludeModule("iblock"))
		{ 
			$dbSection = CIBlockSection::GetList( array(), array("NAME" => "%".$Name."%" , "IBLCOK_ID"=> 7), false, array());
			while ($arSection = $dbSection->GetNext())
			{
				$dbItem = CIBlockElement::GetList (array(), array("IBLOCK_ID"=> 7, "SECTION_ID" => $arSection[ID]));
				while($arItem = $dbItem->GetNext())
				{
					$arItemID[]= $arItem[ID];
				}
			}
			return $arItemID;
		}
	}
	/*----------------------------*/
	
	/*------------������ �� ����� ������������---------*/
		function GetByName($User)
		{
			$arUsers = array();
			if($User)
			{
				$filter = Array("ACTIVE"=> "Y", "NAME"=> $User."%" /*"NAME" =>""*/);
				$rsUsers = CUser::GetList(($by="name"), ($order="desc"), $filter); // �������� �������������
				while ($iUser = $rsUsers->GetNext())
				{
					$arUsers[] = $iUser["ID"];
					//echo $iUser["ID"]."<br>";
				}
			}
			return $arUsers;
		}
	/*-------------------------------------------------*/
	/*------------������ �� ������---------*/
		function GetByNick($Nick)
		{
			$arUsersbyNick = array();
			if($Nick)
			{
				$filter = Array("ACTIVE"=> "Y", "LOGIN" => $Nick."%");
				$rsUsers = CUser::GetList(($by="name"), ($order="desc"), $filter); // �������� �������������
				while ($iUserNick = $rsUsers->GetNext())
				{
					$arUsersbyNick = $iUserNick["ID"];
					//echo $iUserNick["ID"]."<br>";
				}
			}
			return $arUsersbyNick;
		}
	/*-------------------------------------------------*/
/////////////////////
?>

<style>
 * {margin: 0px; padding: 0px;}
 body {font: 12px Tahoma,Geneva; color:#04376C;}
 table td{font-size: 12px; padding: 5px;}
 table.filter td{font-size: 12px; padding: 5px; background: #61B4CF; color: white;}
 table a {color:#fff;}
 table a:hover {color: #fff; text-decoration: none;}
 .nav {margin: 20px; font-size: 120%;}
 .white {color: #fff;}
 .chUtility {display: none; position: absolute; width: 200px; height: 100px; background: #fff; border: 1px solid black; padding-top:20px;}
 .chUtilityClose {position:absolute; right:0px; top:0px; width: 10px; height: 20px; border: 1px solid red; border-radius: 10px; padding: 0px 5px; margin: 0px; cursor:pointer;} 
 .priznak_id {}
 .detailtext_rew_text { width: 100%; height: 100px;}
 .reating_change {display:none;}
 .SelectPriznakArea {display: none;}
 .button_like {border: 1px solid white; -webkit-border-radius: 20px; -moz-border-radius: 20px; border-radius: 20px; width: 120px; padding: 5px; text-align: center; cursor: pointer; background: #216278; color: #ffffff; margin-bottom: 5px;}
</style>

<script src="/script/jquery-1.6.2.min.js" type="text/javascript"></script>
<script src="changProp.js" type="text/javascript"></script>
<link type="text/css" href="/script/datapicker/css/start/jquery-ui-1.8.16.custom.css" rel="stylesheet" />	
<script type="text/javascript" src="/script/datapicker/js/jquery-ui-1.8.16.custom.min.js"></script>
		<script type="text/javascript">
			$(function(){
				$('.datepicker').datepicker({ dateFormat: 'dd.mm.yy' });				
			});
		</script>
<body>

<a href="http://<?=$_SERVER["SERVER_NAME"]?>/rew/">�������� �������</a>
<form action="" method="GET">
	<table cellspacing="0" cellpadding="5" class="filter">
	<tr><td colspan="2" style="background: #024E68; padding: 5px; font-weight: bold; color: white;">�� ����</td></tr>
	<tr>
		<td><input type="hidden" value="Y" name="filt"/>����������� �� ����</td>
		<td>�� <input type="text" name="data_for" class="datepicker" /> �� <input type="text" name="data_to" class="datepicker"/></td>
	</tr>
	<!--<tr>
		<td>����������� �� ������</td>
		<td><input type="text" name="by_name" value=""  class="secion_list"/></td>
	</tr>-->	
		<tr><td colspan="2" style="background: #024E68; padding: 5px; font-weight: bold; color: white;">�� ������</td></tr>
		<tr><td><label for="ByItemID">�� ID ������</label></td><td><input type="text" name="ByItemID" placeholder="ID ������" /></td></tr>
		<tr><td><label for="ByItemName">�� ����� ������</label></td><td><input type="text" name="ByItemName" placeholder="��� ������" /></td></tr>
		<tr><td colspan="2" style="background: #024E68; padding: 5px; font-weight: bold; color: white;">�� ������</td></tr>
		<tr><td><label for="ByRewID">�� ID ������</label></td><td><input type="text" name="ByRewID" placeholder="ID ������" /></td></tr>
		<tr><td><label for="ByRewName">�� ����� ������</label></td><td><input type="text" name="ByRewName" placeholder="��� ������" /></td></tr>
		<tr><td colspan="2" style="background: #024E68; padding: 5px; font-weight: bold; color: white;">�� ������</td></tr>
		<tr><td><label for="ByClientName">�� ����� �����</label></td><td><input type="text" name="ByClientName" placeholder="����� �������" /></td></tr>		
		<tr><td><label for="ByClientNick">�� ������</label></td><td><input type="text" name="ByClientNick" placeholder="������ �������" /></td></tr>		
		<tr><td><label for="ByClientID">�� ID ������</label></td><td><input type="text" name="ByClientID" placeholder="ID ������" /></td></tr>		
		<tr><td colspan="2"><input type="checkbox" value="N" name="unaciv" /><label  for="unaciv">���������� ������ ���������� ��������</label></td></tr>
		
	</table>
	<input type="submit" />
</form>



	<?if(isset($_GET["filt"])){
	 $arFilter = array(
		"IBLOCK_ID"=> 1377,
		//"SECTION_ID" => $_GET["S_ID"]
		);
	if ($_GET[data_for] or $_GET["data_to"]){
		$arFilter["><DATE_CREATE"] = array($_GET["data_for"], $_GET["data_to"]);
	}
	if ($_GET["ByItemID"]){
		$arFilter["PROPERTY_item"] = intval($_GET["ByItemID"]);
	}
	if ($_GET["ByRewID"]) {
		$arFilter["ID"] = intval($_GET["ByRewID"]);
	}
	if ($_GET["ByRewName"]) {
		$arFilter["NAME"] = $_GET["ByRewName"]."%";
	}
	if ($_GET["ByItemName"]) {
		
		//p(ItemName($_GET["ByItemName"]));
		$arFilter["PROPERTY_item"] = ItemName($_GET["ByItemName"]);
	}
	 if ($_GET["ByClientName"]) {
		$arFilter["PROPERTY_user"] = GetByName($_GET["ByClientName"]);
	} 
	if ($_GET["ByClientNick"]) {
		$arFilter["PROPERTY_user"] = GetByNick($_GET["ByClientNick"]);
	}
	if ($_GET["ByClientID"]) {
		$arFilter["PROPERTY_user"] = $_GET["ByClientID"];
	}
	if ($_GET["unaciv"]) {
		$arFilter["ACTIVE"] = "N";
	}



	$dbRev = CIBlockElement::GetList( Array("SORT"=>"ASC"), $arFilter, false, array("nPageSize" => 20), array());
		$arResult = array(); 
		?>
		<div class="nav">
			<?echo $dbRev->NavPrint();?>
		</div>
		<?$i=0;?>
		<?	
		while($objRev = $dbRev->GetNextElement())
		{
			$arRev = $objRev->GetFields();
			$propRev = $objRev->GetProperties();
			$arResult[$i] = $arRev;
			$arResult[$i]["prop"] = $propRev;
			$Autor = CUser::GetByID($propRev["user"]["VALUE"]);
			$arAutor=$Autor->Fetch();
			$i++;			
			?>			
			<div class="rewArea" style="border-bottom: 1px solid green; margin-bottom: 20px; padding-bottom: 10px; background: #61B4CF; color: #024E68; padding: 10px;" >
			<div class="nameIn">
				<h3 style="margin: 5px 0px; color:#fff;"><?=$arRev['NAME']?></h3>
				<button class="chang_name">...</button><br />
			</div>
			<div class="NameChang" style="display: none;">
				<input type="text" value="<?=$arRev['NAME']?>" name="change_name" />
				<button class="chang_it" rewid="<?=$arRev['ID']?>">���������</button>
			</div>
			<small><?=$arRev['ID'];?></small>
			<p>���� ��������: <?=$arRev[DATE_CREATE]?></p>	
			<p class="showAutor">��� ������ ID: <?=$propRev["user"]["VALUE"]?><br/>
				 ��� ������������: <?=$arAutor["NAME"]?><br/>
				 Login: <a href="http://www.mladenec-shop.ru/bitrix/admin/user_edit.php?ID=<?=$propRev["user"]["VALUE"]?>"><?=$arAutor["LOGIN"]?></a><br/>
			</p>
				<!--<div class="rew" style="padding: 0px 5px; color: #fff; border: 1px solid #ccc; background: #216278; margin-top: 10px; border-radius: 10px; padding: 10px; ">
					<?=$arRev['DETAIL_TEXT']?>
				</div>-->
				<div class="detail_rew_chenge">
				<textarea class="detailtext_rew_text"><?=$arRev['DETAIL_TEXT']?></textarea>
				<button class="detailtext_rew_save" rewid="<?=$arRev['ID'];?>">���������
				</button>
				</div>
				<h4> ��������� ������</h4>
				<div>
				<table style="color:#024E68;">
				<tr>
				<td>C����� �� �����:</td>
				<td>
					<?
					  $dbElement = CIBlockElement::GetByID($propRev["item"]["VALUE"]);
					  $arElement = $dbElement->GetNext();
					  $dbSection = CIBlockSection::GetByID($arElement["IBLOCK_SECTION_ID"]);
					  $arSection = $dbSection->GetNext();
					?>
					<?if  ($propRev["url"]["VALUE"]){?>
					<a href="<?=$propRev["url"]["VALUE"]?>" target="new"> <?=$arSection["NAME"]?> <?=$arElement['NAME']?></a><br>
					<?}else{?>
					<a href="http://www.mladenec-shop.ru/product/view/<?=$arSection["ID"]?>.<?=$arElement['ID']?>.html" target="new"> <?=$arSection["NAME"]?> <?=$arElement['NAME']?></a><br>
					<?}?>
				</td>
				</tr>
				<tr>
					<td>������ �� ��������������: </td>
					<td>				
					<a href="http://www.mladenec-shop.ru/bitrix/admin/iblock_element_edit.php?WF=Y&ID=<?=$arRev['ID']?>&type=comments&lang=ru&IBLOCK_ID=1377&find_section_section=0" target='new'>������������� �����</a></td>
					
				</tr>
					<tr>
						<td>�������</td><td><div><b class="reating"><?=$propRev["view"]["VALUE"]?></b><input type="button" class="chang_reating_button" value="..." /></div>
							<div class="reating_change">
								<input type="text" value="<?=$propRev["view"]["VALUE"]?>" class="reating" rewid="<?=$arRev['ID']?>" />
								<input type="button" value="���������" class="chang_reating_save"/>
							</div>
						</td>
					</tr>
					<tr>
						<td>����������</td><td><span class="sactive"><input type="button" class="active" value="<?=$arRev["ACTIVE"]?>" elid="<?=$arRev["ID"]?>"/></td>
					</tr>
					<tr>
						<td>���������� ������</td><td><div class="chUtility_result"><b class="white">yes: <?if ($propRev["vote_yes"]["VALUE"]){echo $propRev["vote_yes"]["VALUE"];}else{ echo "0";}?>; no: <?if ($propRev["vote_no"]["VALUE"]){echo $propRev["vote_no"]["VALUE"];}else{ echo "0";}?>; result: <?if ($propRev["vote_result"]["VALUE"]){echo $propRev["vote_result"]["VALUE"];}else{ echo "0";}?>;</b><input class="utility" type="button" value="�������� ����������" />
						</div>
						<div class="chUtility">
							<table>
							<tr>
								<td>��:</td><td><input type="text" value="<?=$propRev["vote_yes"]["VALUE"]?>" size="2" class="yes" /></td>
								</tr>
							<tr>
								<td>���:</td><td><input type="text" value="<?=$propRev["vote_no"]["VALUE"]?>" size="2" class="no"/></td>
							</tr>
							<!--<tr>
								<td>������:</td><td><input type="text" value="<?=$propRev["vote_result"]["VALUE"]?>" size="2" class="result"/></td>							
							</tr>-->
							</table>
							<input type="button" class="vote_submit" value="ok" elid="<?=$arRev["ID"]?>"/>
							<div class="chUtilityClose">X</div>
						</div>
						</td>
					</tr>
					<tr>
						<td></td><td> <?foreach ($propRev["param"]["VALUE"] as $key => $ParamRev)  
						{
							$dbPropRew = CIBlockElement::GetByID($ParamRev);
								if ($arPropRew = $dbPropRew->GetNext())
								{
									$dbSection = CIBlockSection::GetByID($arPropRew['IBLOCK_SECTION_ID']);
									if($arSection=$dbSection->GetNext())
									{
										echo "<b>".$arSection["NAME"].": </b>";										
									}
									echo "<div><span class='NameProp'>".$arPropRew["NAME"]."</span><select name='".$arPropRew["NAME"]."' class='chkProp' style='display: none;'></select> <input type='button' name='".$arRev['ID']."' section='".$arSection['ID']."' paramID = '".$arPropRew[ID]."' class='change' value='...'  ><input type='button' name='".$arRev[ID]."' section='".$arSection['ID']."' class='submitProp' value='���������' style='display: none;' ><button class='priznak_id' value='".$arPropRew["ID"]."'>".$arPropRew["ACTIVE"]."</button><button class=\"unlink\" value = ".$arPropRew["ID"]." name=".$arRev["ID"].">X</button><a href='http://www.mladenec-shop.ru/bitrix/admin/iblock_element_edit.php?WF=Y&ID=".$arPropRew["ID"]."&type=comments&lang=ru&IBLOCK_ID=1376&find_section_section=0' target='new'>������������� �������� � �������</a></div> "; 
								//p($arPropRew);
								}
							
						}?> </td>
					</tr>
				</table>
				<?//p($propRev)?>
			
				 <div id="<?=$arRev["ID"]?>" class="DobavitPriznak button_like">
					�������� �������				
				</div>
				<div class="addPriznak" rewID="<?=$arRev["ID"]?>" style=" display: none; width: 200px; height: 100px; border: 1px solid red; background: white; position:absolute;">
				</div>
				<div class="selectPriznak button_like" rewID="<?=$arRev["ID"]?>">
					������� �� ������������
				</div>
				<div class="SelectPriznakArea">
					
				</div>
			</div>
		</div>
			
		<?}
	
	 ?>
	<div>
		
		
		
	</div>
	
<div class="nav">
	<?echo $dbRev->NavPrint();?>
</div>
<?}?>
<?
//p($arResult);
?>
</body>