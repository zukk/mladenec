<? require($_SERVER["DOCUMENT_ROOT"]."/odinc/auth.php");?>
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

$current_users = array(27791, 27789, 22855, 27790, 4, 3, 22846, 1);

$rsUsers = CUser::GetList(($by="id"), ($order="desc")); // �������� �������������
$i = 0;
while($arUsers=$rsUsers->GetNext()):
	$ID = $arUsers["ID"];
	if(!in_array($ID, $current_users)) {
		$user = new CUser;
		$user->Delete($ID);
	}
endwhile;
?>