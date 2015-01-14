<?php
require($_SERVER["DOCUMENT_ROOT"] .
    "/bitrix/modules/main/include/prolog_before.php");
if (CModule::IncludeModule("iblock")) {
    if ($_POST['field'] and $_POST['val']) {
        //echo "<hr>".$_POST['field']." ".$_POST['val']."<hr>" ;
        if ($_POST['field'] == "login") {

            $filter = Array("LOGIN_EQUAL" => iconv("UTF-8", "WINDOWS-1251", $_POST['val']));
        } elseif ($_POST['field'] == "email") {
            $filter = Array("EMAIL" => $_POST['val']);
        }
        $rsUsers = CUser::GetList(($by = "personal_country"), ($order = "desc"), $filter); // выбираем пользователей
        while ($User = $rsUsers->getNext()) {
            $bul = true;
        }
        if ($bul == true) {
            echo 0;
        } else {
            echo 1;
        }
    }
}
?>