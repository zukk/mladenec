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
	$p = trim(fgets($fp));
	if(CSaleOrder::StatusOrder($p, "S")) echo " - OK\r\n";
	else echo " - error\r\n";
endwhile;

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>