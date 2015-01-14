<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php"); ?>
<?
$time = time();
$result = mysql_query("SELECT * FROM baby_users WHERE complit=0");
$i = 0;
$u = 0;
while ($row = mysql_fetch_array($result)):
    $i++;

    $name = trim($row["name"]);
    $PERSONAL_PHONE = trim($row["phone"]);
    $id = $row["id"];
    $email = trim($row["email"]);
    $pass = md5($row["passwd"]);
    echo "$i. $name $email<br>";
    $groups = array();
    $groups[] = 2;
    $groups[] = 3;
    if ($row["discount"] == "6.00") $groups[] = 7;
    if ($row["discount"] == "3.00") $groups[] = 8;

    print_r($groups);

    $user = new CUser;
    $arFields = Array(
        "NAME" => "$name",
        "XML_ID" => $row["id"],
        "EMAIL" => "$email",
        "LOGIN" => "$email",
        "LID" => "ru",
        "ACTIVE" => "Y",
        "GROUP_ID" => $groups,
        "PASSWORD" => $pass,
        "CONFIRM_PASSWORD" => $pass,
        "PERSONAL_PHONE" => $PERSONAL_PHONE,
    );

    $rsUsers = CUser::GetList(($by = "id"), ($order = "desc"), Array("EMAIL" => "$email")); // �������� �������������
    if ($arUsers = $rsUsers->GetNext()):
        echo "<span style='color:#000000'>Email ��� ����������.</span>";

    else:

        $USER_ID = $user->Add($arFields);
        if (intval($USER_ID) > 0) {
            $u++;
            echo "<span style='color:#FF0000'>������������ ������� ��������.</span>";
            //mysql_query("UPDATE baby_users SET add=$USER_ID WHERE id=$id");
        } else {
            echo "<b>" . $user->LAST_ERROR . "</b>";
        }

    endif;
    mysql_query("UPDATE baby_users SET complit=1 WHERE id=$id");
    $now = time();
    if ($time < $now - 25) {
        LocalRedirect("/convertusers.php");
    }
    ?>
    <br>
<? endwhile; ?>
    ����� ���������: <?= $u ?>
<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>