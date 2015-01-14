<? require($_SERVER["DOCUMENT_ROOT"]."/odinc/auth.php");?>
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

CModule::IncludeModule("iblock");

$fp = fopen('php://input', 'r');


while (!feof($fp)):
		
		$p = trim(fgets($fp));
		$arr = explode("�", $p);

		if($arr[1]) {
			$arFields = Array(
			  "IBLOCK_ID" => "81",
			  "NAME" => $arr[1],
			  "CODE" => $arr[0],
			  "ACTIVE" => $arr[2],
			 );
					$arFilter = Array('IBLOCK_ID'=>81, 'CODE'=>$arr[0]);
					$db_list = CIBlockElement::GetList(Array($by=>$order), $arFilter);
				
					$bs = new CIBlockElement;
					if($ar_result = $db_list->GetNext()):
						echo "UPDATE"; 
						$res = $bs->Update($ar_result["ID"], $arFields);
						echo $ar_result["ID"];
					else:
						echo "ADD";
						echo $ID = $bs->Add($arFields);
					endif;
			echo "\n";
		}
endwhile;

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
?>