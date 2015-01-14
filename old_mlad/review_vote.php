<?

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-cache, must-revalidate");
header("Pragma: no-cache");
header("content-type: application/x-javascript; charset=windows-1251");
error_reporting(0);
define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);

require_once("script/php2js.php");

if (($_GET[type] == 'yes' or $_GET[type] == 'no') and $_GET[item] > 0) {

    require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
    CModule::IncludeModule("iblock");


    $IP = $_SERVER['REMOTE_ADDR'];

    $arSelect = Array("ID", "NAME");
    $arFilter = Array("IBLOCK_ID" => $GLOBALS[REVIEW_IP], "ACTIVE" => "", "NAME" => $IP, "CODE" => ($_GET[item] + 0));
    $res = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
    if ($ob = $res->GetNextElement()) {
        exit(php2js(array('data' => "error", 'id' => ($_GET[item] + 0))));
    } else {

        //���������� ��
        $el = new CIBlockElement;
        $arLoadProductArray = Array(
            "IBLOCK_SECTION_ID" => false,
            "IBLOCK_ID" => $GLOBALS[REVIEW_IP],
            "NAME" => $IP,
            "ACTIVE" => "Y",
            "CODE" => ($_GET[item] + 0),
            "TRAGS" => $_GET[type]
        );
        $el->Add($arLoadProductArray);

        //������ ����
        $res = CIBlockElement::GetByID(($_GET[item] + 0));
        $ob = $res->GetNextElement();
        $p = $ob->getProperties();


        $yes = $p[vote_yes][VALUE];
        $no = $p[vote_yes][VALUE];
        $result = $p[vote_result][VALUE];

        if ($_GET['type'] == 'yes') {
            $yes++;
            //$result++;
            CIBlockElement::SetPropertyValues(($_GET[item] + 0), $GLOBALS[REVIEW_PARAM_OT], $yes, 'vote_yes');
            CIBlockElement::SetPropertyValueCode(($_GET[item] + 0), "vote_yes", $yes);
        }

        if ($_GET['type'] == 'no') {
            $no++;
            //$result--;
            CIBlockElement::SetPropertyValues(($_GET[item] + 0), $GLOBALS[REVIEW_PARAM_OT], $no, 'vote_no');
            CIBlockElement::SetPropertyValueCode(($_GET[item] + 0), "vote_no", $no);
        }
        $result_v = $yes - $no;
        $rer = CIBlockElement::SetPropertyValueCode(($_GET[item] + 0), "vote_result", $result_v);
        CIBlockElement::SetPropertyValues(($_GET[item] + 0), $GLOBALS[REVIEW_PARAM_OT], $result_v, 'vote_result');
        exit(php2js(array("data" => "�� (" . ($yes + 0) . ") / ��� (" . ($no + 0) . ")", "id" => ($_GET[item] + 0))));

    }


} else {
    exit(php2js(array('data' => "error", 'id' => ($_GET[item] + 0))));
}

?>
