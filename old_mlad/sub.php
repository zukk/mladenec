<?require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
// --- lib
function p($arr)
{
    echo "<pre>";
    print_r($arr);
    echo "</pre>";
}

// --- vars
$email = array(); // адреса
$arFilter = array(); // фильтры 
$arSort = array();  // сортировка
$i = 0;
// ===

if (CModule::IncludeModule("subscribe")) {
    /* $fo = fopen("sub.txt", "r") or die ("File does not exist!");
    $email = array();
    while (!feof($fo))
    {
        $email[]  = trim(fgets($fo));
    }
    $arSub = CSubscription::GetList(Array(), Array(), false);
    //p($email);
    while($arFiled = $arSub->Fetch())
    {
        if (in_array($arFiled["EMAIL"], $email)){
        echo $i;
            if ($res = CSubscription::Delete($arFiled["ID"]))
                echo "Error deleting subscription.<br>";
            else
                echo "Subscription deleted.<br>";
        $i++;
        }
    }  */

    //активируем подписку у всех.
    /* $rsSub = CSubscription::GetList(array("ID"=>"ASC"),  array("RUBRIC"=>$aPostRub, "CONFIRMED"=>"N", "ACTIVE"=>"Y"));
    while($arSub = $rsSub -> GetNext())
    {
        $arFields = Array("SEND_CONFIRM" => "N", "CONFIRMED" => "Y", "ACTIVE"=>"Y");
        $subscr = new CSubscription;
        if ($res = $subscr->Update($arSub["ID"], $arFields))
        {
            echo "modify <br />";
        }else
        {
            echo "Error<br />";
        } */
    /* $arFields = Array("SEND_CONFIRM" => "N", "CONFIRMED" => "Y", "ACTIVE"=>"Y", "EMAIL" => $arUser["EMAIL"]);
    $subscr = new CSubscription;
    if ($ID = $subscr->Add($arFields))
       echo "done <br />";
    else
       echo "no <br />";
   //p($arUser["EMAIL"]);
   //

   //die();
}*/
}
?>