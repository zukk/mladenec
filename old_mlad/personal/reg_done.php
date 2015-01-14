<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("");
$burl = $_SESSION["NEW_USER"]["BACK_URL"];
unset ($_SESSION["NEW_USER"]["BACK_URL"]);
?>
Loading...
<script type="text/javascript" defer>
window.setTimeout("document.location.href='<?=$burl?>'", 1000);
</script>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>