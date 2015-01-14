<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php"); ?>
<?
$time = time();
$result = mysql_query("SELECT * FROM baby_orders WHERE complit=1");
$i = 0;
$u = 0;
while ($row = mysql_fetch_array($result)):
    $i++;


    if ($row["status"] == "get"):
        $STATUS = "N";
    elseif ($row["status"] == "sent"):
        $STATUS = "S";
    elseif ($row["status"] == "approve"):
        $STATUS = "D";
    elseif ($row["status"] == "done"):
        $STATUS = "F";
    elseif ($row["status"] == "cancel"):
        $STATUS = "X";
    else:
        $STATUS = "N";
    endif;

    if ($row["manager"] == "242563135"):
        $MANAGER = "27783";
    elseif ($row["manager"] == "278152836"):
        $MANAGER = "27784";
    elseif ($row["manager"] == "424226553"):
        $MANAGER = "27785";
    elseif ($row["manager"] == "214667114"):
        $MANAGER = "27786";
    else:
        $MANAGER = "27787";
    endif;


    $USER_XML = $row["user_id"];
    $rsUsers = CUser::GetList(($by = "id"), ($order = "desc"), Array("XML_ID" => $USER_XML));
    if ($arUsers = $rsUsers->GetNext()):
        $USER_ID = $arUsers["ID"];
    endif;


    $arFields = Array(
        "LID" => "s1",
        "PERSON_TYPE_ID" => "1",
        "STATUS_ID" => "$STATUS",
        "EMP_STATUS_ID" => "$MANAGER",
        "PRICE" => $row["sum"],
        "CURRENCY" => "RUB",
        "USER_ID" => "1",
        "PAY_SYSTEM_ID" => "1",
        "COMMENTS" => $row["id"],
    );


    //$ORDER_ID = CSaleOrder::Add($arFields);
    echo $row["created"];

    //mysql_query("UPDATE b_sale_order SET DATE_INSERT='$row[created]' WHERE ID='$ORDER_ID'");
    die();
    mysql_query("UPDATE baby_orders SET complit=1 WHERE id='$row[id]'");
    //die();
    $now = time();
    if ($time < $now - 25) {
        LocalRedirect("/convertorders.php");
    }
    ?>
    <br>
<? endwhile; ?>
    ����� ���������: <?= $u ?>
<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>