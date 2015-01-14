<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php"); ?>
<?
die();
$rsUsers = CUser::GetList(($by = "id"), ($order = "asc")); // �������� �������������
while ($rsUsers->NavNext(true, "f_")) :
    if ($f_XML_ID > 0) echo "" . $f_XML_ID . ";" . $f_ID . ";" . $f_NAME . " " . $f_LAST_NAME . "<br>";
endwhile;
?>
<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>