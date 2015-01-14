<?require($_SERVER["DOCUMENT_ROOT"].
"/bitrix/modules/main/include/prolog_before.php");
p($_SESSION["YML"]);
unset($_SESSION["YML"]["Elements"]);
 
//header("Location:".$_SERVER["HTTP_REFERER"]);
?>