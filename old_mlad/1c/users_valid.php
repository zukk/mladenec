<? require($_SERVER["DOCUMENT_ROOT"]."/odinc/auth.php");?>
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
ob_start();
CModule::IncludeModule("sale");
$fp = fopen('php://input', 'r');
$report = date("H:i:s")."<br>";

$counter = 0;

if(!$USER->IsAuthorized() ) $USER->Authorize(5);

while (!feof($fp)):

		$id = trim(fgets($fp));
		
		$rsUsers = CUser::GetList(($by="id"), ($order="desc"), Array("ID"=>$id)); // �������� �������������
		
		if($arUsers=$rsUsers->GetNext()):
			//$id = 1;
				$user = new CUser;
				$fields = Array(
				  "UF_VALID"  => "Y",
				  );
				if($user->Update($arUsers["ID"], $fields)) echo "������������ ID=".$arUsers["ID"]." ������� - OK \r\n";
		endif;
					
endwhile;

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>