<?php
require($_SERVER["DOCUMENT_ROOT"].
"/bitrix/modules/main/include/prolog_before.php");
CModule::IncludeModule("iblock");
if (isset($_POST["SectionID"]))	
{
	$dbQQ = CIBlockElement::GetList(array(), array("IBLOCK_ID"=> "1376", "SECTION_ID"=>$_POST["SectionID"]), false, false, array("ID", "NAME", "ACTIVE"));
	while( $arQQ = $dbQQ->GetNext())
	{?>
			 <option value="<?=$arQQ['ID']?>"><?=$arQQ['NAME']?><?if($arQQ["ACTIVE"]=="N"){echo "(N)";}else{echo "(Y)";}?>
	<?$arResult[] = $arQQ;
	}
}

if (isset($_GET["Ch"]))
{	
	$dbElement = CIBlockElement::GetProperty(1377, $_GET["El_id"], array("sort" => "asc"), Array());
	while ($prElement = $dbElement->GetNext())
	{

		if (isset($prList[$prElement['ID']]))
		{
			if(is_array($prList[$prElement['ID']])){
				$prList[$prElement['ID']][] = $prElement["VALUE"];
			}else{
			$prList[$prElement['ID']] = array($prList[$prElement['ID']]);
			$prList[$prElement['ID']][] = $prElement["VALUE"];
			}
		}else{
			$prList[$prElement[ID]] = $prElement["VALUE"];
		 }
	}	
	foreach ($prList[2045] as $key=>$val)
	{
		if($val == $_GET["old"])
		{
			$prList[2045][$key] = $_GET["Ch"];
		}
	}
	
	$el = new CIBlockElement;	
	$PROP = array();
	$PROP = $prList;
	$arLoadProductArray = Array(
		"MODIFIED_BY"    => $USER->GetID(), // ������� ������� ������� �������������
		"IBLOCK_SECTION" => false,          // ������� ����� � ����� �������.
		"PROPERTY_VALUES"=> $PROP,
	);

	$res = $el->Update ($_GET["El_id"], $arLoadProductArray);
$dbback = CIBlockElement::GetByID($_GET["Ch"]);
	if($arback = $dbback->GetNext())
	{
		if($arback["ACTIVE"]=="Y"){
			echo $arback["NAME"]."<span class=\"priznak_id\" name=\"".$arback["ID"]."\">";
		}else{
			echo $arback["NAME"]."(N)"."<span class=\"priznak_id\" name=\"".$arback["ID"]."\">";
		}
	}
}

if(isset($_GET["Yes"])){
	$el = new CIBlockElement;
	$dbElement = CIBlockElement::GetProperty(1377, $_GET["El_id"], array("sort" => "asc"), Array());
	while ($prElement = $dbElement->GetNext())
	{

		if (isset($prList[$prElement['ID']]))
		{
			if(is_array($prList[$prElement['ID']])){
				$prList[$prElement['ID']][] = $prElement["VALUE"];
			}else{
			$prList[$prElement['ID']] = array($prList[$prElement['ID']]);
			$prList[$prElement['ID']][] = $prElement["VALUE"];
			}
		}else{
			$prList[$prElement["ID"]] = $prElement["VALUE"];
		 }
	}	
	$prList[2051] = $_GET["Yes"];
	$prList[2052] = $_GET["No"];
	$prList[2053] = $prList[2051] - $prList[2052];
	
	
	$el = new CIBlockElement;	
	$PROP = array();
	$PROP = $prList;
	$arLoadProductArray = Array(
		"MODIFIED_BY"    => $USER->GetID(), // ������� ������� ������� �������������
		"IBLOCK_SECTION" => false,          // ������� ����� � ����� �������.
		"PROPERTY_VALUES"=> $PROP,
	);

	if ($el->Update($_GET["El_id"], $arLoadProductArray)){?>
	<b class="white">yes: <?=$prList[2051]?>;  no: <?=$prList[2052]?>; result: <?=$prList[2053]?>;</b><input class="utility" type="button" value="�������� ����������" />	
	<?}
	}

if(isset($_GET["Active"]))
	{
		if ($_GET["Active"] == "Y")
		{
			$Active = "Y";
		}else
		{
			$Active = "N";
		}
		$arLoadProductArray = Array(
				"MODIFIED_BY"    => $USER->GetID(), // ������� ������� ������� �������������
				"IBLOCK_SECTION" => false,          // ������� ����� � ����� �������.
				"ACTIVE"         => $Active 
		);
		$el = new CIBlockElement;			
		if ($el->Update($_GET["El_id"], $arLoadProductArray)){?>
			<input type="button" class="active" value="<?=$Active?>" elid="<?=$_GET["El_id"]?>"/>
		<?}
	}
if (isset($_GET["PropActive"])){
	if ($_GET["PropActive"] == "Y")
		{
			$Active = "N";
		}else{
			$Active = "Y";
		}
		$arLoadProductArray = Array(
				"MODIFIED_BY"    => $USER->GetID(), // ������� ������� ������� �������������
				"ACTIVE"         => $Active 
		);
		$el = new CIBlockElement;			
	if ($el->Update($_GET["PropId"], $arLoadProductArray))
	{?>
				<?=$Active?>
	<?
	}
}
	if (isset($_GET["addPriznak"]))
	{
		$id = $_GET["addPriznak"];
		{
			$dbElement = CIBlockElement::GetByID($id);
			if ($objElement = $dbElement->GetNextElement())
			{
				$arElement = $objElement->GetFields();
				$propElement = $objElement->GetProperties();
				$dbItem = CIBlockElement::GetByID($propElement["item"]["VALUE"]);
				if ($arItem = $dbItem->GetNext())
				{
					//p($arItem);
					$arFilter = array(
						'IBLOCK_ID' => $arItem["IBLOCK_ID"],
						'RIGHT_MARGIN' => 4
						);
					$res = CIBlockSection::GetNavChain($arItem["IBLOCK_ID"], $arItem["IBLOCK_SECTION_ID"]);
					$i = 0 ;
					$parsName = "";
					while ($arRes=$res->GetNext())
					{
						$parsName = $parsName."".$arRes["NAME"];
						if ($i>=1)
						{
							break;
						}
						$parsName = $parsName." - ";
						 $i++;
					}
					
					$arFilter = array(
						"NAME" => $parsName,
						"IBLOCK_ID" => 1376
					);
					
					$dbSection = CIBlockSection::GetList(array(), $arFilter, false);
					if ($arSection = $dbSection->GetNext()){
						$arFilter = array(
							"SECTION_ID" =>$arSection["ID"],
							"IBLOCK_ID" => 1376
						);
						
						$dbSection =  CIBlockSection::GetList(array(), $arFilter, false);?>
							<select name="propertyAdd" class="SelectPropSection">
							<option value="0">�� �������</option>
						<?while ($arSection = $dbSection->GetNext())
						{?>
								<option value="<?=$arSection["ID"]?>"><?=$arSection["NAME"]?></option>
						<?}?>
							</select>
							<div class="addPropName">
								<input type="text" value="" Name="PropName" placeholder="���� �������" style="display: block;  width: 170px;" />
								<span class="conformProp" style="border: 1px solid #808080; cursor: pointer;">������� �������</span><br />
								<span class="AddPropClose" style="border: 1px solid #808080; cursor: pointer;">�������</span>
							</div>
						<?
					}
				}
					
			}			 
		}
	}
	if (isset($_GET["NamePriznak"]))
	{
		$Name = iconv("UTF-8", "WINDOWS-1251", trim($_GET["NamePriznak"]));
		$SectionID = intval($_GET["SectionID"]);
		$RewID = $_GET["RevID"];
		if(CModule::IncludeModule("iblock"))
		{
			
			$el = new CIBlockElement;
			$arLoadProductArray = Array(
			  "MODIFIED_BY"    => $USER->GetID(),
			  "IBLOCK_SECTION_ID" => $SectionID,   
			  "IBLOCK_ID"      => 1376,
			  "NAME"           => $Name,
			  "ACTIVE"         => "Y",            // �������
			  );

				if($PRODUCT_ID = $el->Add($arLoadProductArray)){
				  echo $PRODUCT_ID;
				$dbElement = CIBlockElement::GetByID($RewID);
				if ($objElement = $dbElement->GetNextElement())
				{
					$arElement = $dbElement->GetNext();
					$propElement = $objElement->GetProperties();
					$propElement["param"]["VALUE"];
					$PROP = $propElement["param"]["VALUE"];
					if (is_array($PROP)){
						$PROP[count($PROP)+1] =  $PRODUCT_ID;
					}else{
						$PROP[0] = $PRODUCT_ID;
					}
					if ($res = CIBlockElement::SetPropertyValues($RewID, 1377, $PROP, "param"))
					{
						echo "pleas reload page!";
					}else{
						echo $res ;
					}
				}			
			}
			else {
			  echo "0";
			}
		}
	}
// --- ��������� ��������� ������
	if ($_GET["action"] == "RedactorName"){
		$PropID = intval($_GET["RedID"]);
		$dbElement = CIBlockElement::GetByID($_GET["RewID"]);
		if( $drElement = $dbElement ->GetNext()){
			
			if(CIBlockElement::SetPropertyValueCode($drElement["ID"], "redactor", $PropID)){
				$prop = CIBlockPropertyEnum::GetByID($PropID);			
				echo ($prop["VALUE"]);			
			}
		}
	}
// --- ��������� ������ ������	
	if ($_POST["action"] == "ChangTextRew")
	{
		$Text = iconv("UTF-8", "WINDOWS-1251", $_POST["text"]);
		
		$dbElement = CIBlockElement::GetByID($_POST["RewID"]);
		if( $drElement = $dbElement ->GetNext()){
			$el = new CIBlockElement;
			$arLoadElementArray = Array(
					"DETAIL_TEXT" => $Text
				);
			$res = $el->Update($_POST["RewID"], $arLoadElementArray);
			echo $Text;
		}else{
			echo 0;
		}		
	}
// --- ��������� ������ ������		
	if ($_POST["action"] == "ChangAnswerRew")
	{
		$Text = iconv("UTF-8", "WINDOWS-1251", $_POST["text"]);
		//$Text = $_GET["text"];
		$dbElement = CIBlockElement::GetByID($_POST["RewID"]);
		if( $drElement = $dbElement ->GetNext()){
			$el = new CIBlockElement;
			$arLoadElementArray = Array(
					"PREVIEW_TEXT" => $Text,
					"PREVIEW_TEXT_TYPE" => $_POST["html"]
				);
				
			$res = $el->Update($_POST["RewID"], $arLoadElementArray);
			echo $Text;
		}else{
			echo 0;
		}		
	}
	
	
	if ($_GET["action"] == "ChangNameRew")
	{
		$name = iconv("UTF-8", "WINDOWS-1251", $_GET["name"]);
		//$Text = $_GET["text"];
		$dbElement = CIBlockElement::GetByID($_GET["RewID"]);
		if( $drElement = $dbElement ->GetNext()){
			$el = new CIBlockElement;
			$arLoadElementArray = Array(
					"NAME" => $name
				);
			$res = $el->Update($_GET["RewID"], $arLoadElementArray);
			echo $name;
		}else{
			echo "0";
		}		
	}
	
	elseif ($_GET["action"] == "ChangReatRew")
	{
		$val = intval($_GET["val"]);
		$dbElement = CIBlockElement::GetByID($_GET["RewID"]);
		if( $drElement = $dbElement ->GetNext()){
			CIBlockElement::SetPropertyValues($_GET["RewID"], 1377, $val, "view");
			echo $val;
		}else{
			echo "0";
		}		
	}
	// ������ ������ ������� ������� �������� ��� ���������.
	elseif($_GET["action"] == "AddPropertyList")
	{		
		$id = $_GET["RewID"];
		{
			$dbElement = CIBlockElement::GetByID($id);
			if ($objElement = $dbElement->GetNextElement())
			{
				$arElement = $objElement->GetFields();
				$propElement = $objElement->GetProperties();
				$dbItem = CIBlockElement::GetByID($propElement["item"]["VALUE"]);
				if ($arItem = $dbItem->GetNext())
				{
					//p($arItem);
					$arFilter = array(
						'IBLOCK_ID' => $arItem["IBLOCK_ID"],
						'RIGHT_MARGIN' => 4
						);
					$res = CIBlockSection::GetNavChain($arItem["IBLOCK_ID"], $arItem["IBLOCK_SECTION_ID"]);
					$i = 0 ;
					$parsName = "";
					while ($arRes=$res->GetNext())
					{
						$parsName = $parsName."".$arRes["NAME"];
						if ($i>=1)
						{
							break;
						}
						$parsName = $parsName." - ";
						 $i++;
					}
					
					$arFilter = array(
						"NAME" => $parsName,
						"IBLOCK_ID" => 1376
					);
					
					$dbSection = CIBlockSection::GetList(array(), $arFilter, false);
					if ($arSection = $dbSection->GetNext()){
						$arFilter = array(
							"SECTION_ID" =>$arSection["ID"],
							"IBLOCK_ID" => 1376
						);
						
						$dbSection =  CIBlockSection::GetList(array(), $arFilter, false);?>
							<select name="propertyAdd" class="SelectPropSectionforadd">
							<option value="0">�� �������</option>
							<option value="486">� ����</option>
						<?while ($arSection = $dbSection->GetNext())
						{?>
							<option value="<?=$arSection["ID"]?>"><?=$arSection["NAME"]?></option>
						<?}?>
							</select>
						<?
					}
				}
					
			}			 
		}		
	}elseif($_GET["action"] == "PriznakElementList"){
		
		$arFilter = array(
			"IBLOCK_ID" => 1376,
			"INCLUDE_SUBSECTIONS" => "Y",
			"SECTION_ID" => $_GET["SecID"]
			);
		$res = CIBlockElement::GetList(Array( "NAME" => "ASC"), $arFilter, false, false, array());
		?>
		
		<select name="PropSelect" class="SelectPropElementforAdd">
			<option value="">������� ��������</option>
		<?while( $arItem = $res->GetNext()){?>
			<option value="<?=$arItem["ID"]?>"><?=$arItem["NAME"]?></option>
		<?}?>
		</select>
		<?
	}elseif($_GET["action"] == "PriznakAddInRew"){
		
		$res = CIBlockElement::GetByID($_GET["RewID"]);
		if ($objItem = $res->GetNextElement()){
			$arProps = $objItem->GetProperties();
			$PROP = $arProps["param"]["VALUE"];
		}
		$PROP[] =  $_GET["PriznakID"];
		
		CIBlockElement::SetPropertyValues($_GET["RewID"], 1377, $PROP, "param");
	}elseif($_GET["action"] == "UnLinkPriznak"){
		
		$res = CIBlockElement::GetByID($_GET["RewID"]);
		if ($objItem = $res->GetNextElement()){
		$PROP = array();
			$arProps = $objItem->GetProperties();
			foreach ($arProps["param"]["VALUE"] as $PropList)
			{
				if ($PropList != intval($_GET["PriznakID"])){
					$PROP[] = $PropList;
				}
			}
		}		
		CIBlockElement::SetPropertyValues($_GET["RewID"], 1377, $PROP, "param");	
	}
		
?>