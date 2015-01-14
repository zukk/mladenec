<? require($_SERVER["DOCUMENT_ROOT"]."/odinc/auth.php");?>
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
ob_start();
CModule::IncludeModule("sale");
$fp = fopen('php://input', 'r');
$report = date("H:i:s")."<br>";

$counter = 0;

if(!$USER->IsAuthorized() ) $USER->Authorize(5);
$user_groups = array(14=>'stan', 12=>'silv', 8=>'gold', 7=>'plat');

while (!feof($fp)):
	$user_groups_unset = $user_groups;
	$p = trim(fgets($fp));
	//$p = "1©Ô È Î©©Òåëåôîí©alex@marabo.ru©plat";
	writeReportLog($p, 'users');
	
	$arr = explode("©", $p);
	ob_start();
	print_r($arr);
	
	$phone = explode('|', $arr[3]);
	$arrName = explode(" ", $arr[1]);
	$user["ID"]             = $arr[0];
	$user["LAST_NAME"]      = $arrName[0];
	$user["NAME"]           = $arrName[1];
	$user["SECOND_NAME"]    = $arrName[2];
	$user["PERSONAL_MOBILE"] = array_shift($phone);
	$user["PERSONAL_PHONE"] = array_shift($phone);
	$user["EMAIL"] = $arr[4];

	$user["GROUP"] = array_search($arr[5], $user_groups);
	unset($user_groups_unset[$user["GROUP"]]);
	$systemGroup = array_search(2, $user_groups);
	unset($user_groups_unset[$systemGroup]);
	
	global $USER;
	$arGroups = CUser::GetUserGroup($user["ID"]);
	$arGroups[] = $user["GROUP"];
	$arGroups = array_unique($arGroups);
	$arGroups = array_diff($arGroups, array_keys($user_groups_unset));
	
	$user_ = new CUser;
	$fields = Array(
	  "NAME"              => $user["NAME"],
	  "LAST_NAME"         => $user["LAST_NAME"],
	  "SECOND_NAME"       => $user["SECOND_NAME"],
	  "EMAIL"             => $user["EMAIL"],
	  "PERSONAL_PHONE"    => $user["PERSONAL_PHONE"],
	  "PERSONAL_MOBILE"   => $user["PERSONAL_MOBILE"],
	  "ACTIVE"            => "Y",
	  "GROUP_ID"          => $arGroups,
	  );
	  	
	print_r($user);
	print_r($fields);
	if($user_->Update($user["ID"], $fields)) echo $user["ID"]." - OK\r\n";
	else echo $user["ID"]." ".$user_->LAST_ERROR."\r\n";;
	writeReportLog(ob_get_contents(), 'users');
	
endwhile;

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>