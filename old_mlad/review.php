<?

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-cache, must-revalidate");
header("Pragma: no-cache");
header("content-type: application/x-javascript; charset=windows-1251");

define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

if (!$_POST) {
    ?><?$APPLICATION->IncludeComponent(
        "ku:review.add",
        ".default",
        Array("ITEM" => $_GET[ID]

        ),
        false
    );?><?
} elseif (preg_match("/^[a-zA-Z]+$/", $_POST[title])) {
} else {

    CModule::IncludeModule("iblock");
#print_r($_POST);
//���������������� ������

    $arFilter = Array('IBLOCK_ID' => $GLOBALS[REVIEW_PARAM_IB], 'GLOBAL_ACTIVE' => 'Y', array("LOGIC" => "OR", array('SECTION_ID' => $_POST[review_section]), array('SECTION_ID' => $GLOBALS[REVIEW_ABOUT])));
    $db_list = CIBlockSection::GetList(Array($by => $order), $arFilter, true);

    while ($ar_result = $db_list->GetNext()) {
        #echo $ar_result['ID'].' '.$ar_result['NAME'].'<br>';
        //�������� ��� ���. ������
        $arSelect2 = Array("ID", "NAME");
        $arFilter2 = Array("IBLOCK_ID" => $GLOBALS[REVIEW_PARAM_IB], "ACTIVE" => "", "SECTION_ID" => $ar_result['ID']);
        $res = CIBlockElement::GetList(Array(), $arFilter2, false, false, $arSelect2);
        while ($ob = $res->GetNextElement()) {
            $f = $ob->GetFields();
            #print_r($arFields);
            $exist[$ar_result['ID']][] = $f;
        }

        //���� ���� �� ����� ��� � ����
        if ($_POST['ch_' . $ar_result['ID']])
            foreach ($_POST['ch_' . $ar_result['ID']] as $k => $v) {
                if ($exist[$ar_result['ID']])
                    $founded = 0;
                foreach ($exist[$ar_result['ID']] as $kk => $vv) {
                    if ($vv[NAME] == tsi($v)) {
                        $ch[$ar_result['ID']][] = $vv[ID];
                        $founded = 1;
                    }
                }
                if (!$founded) {
                    $el = new CIBlockElement;
                    $arLoadProductArray = Array(
                        "IBLOCK_SECTION_ID" => $ar_result['ID'],          // ������� ����� � ����� �������
                        "IBLOCK_ID" => $GLOBALS[REVIEW_PARAM_IB],
                        "NAME" => tsi($v),
                        "ACTIVE" => "N",            // �������
                    );
                    $newid = $el->Add($arLoadProductArray);
                    $ch[$ar_result['ID']][] = $newid;
                }
            }
    }

    $el = new CIBlockElement;
    $PROP[item] = $_POST[item];
    $PROP[section] = $_POST[section];
    $PROP[rating] = $_POST[rating];
    $PROP[user] = $USER->GetID();
    $PROP[r1] = tsi($_POST[r1]);
    $PROP[r2] = tsi($_POST[r2]);
    $PROP[r3] = tsi($_POST[r3]);
    $PROP[friendmail] = tsi($_POST[friendmail]);
    if ($ch)
        foreach ($ch as $k => $v)
            if ($v)
                foreach ($v as $kk => $vv) {
                    $PROP[param][] = $vv;
                }

    $PROP[url] = "http://mladenec.ru/product/view/" . $_POST[section] . "." . $_POST[item] . ".html";
    $arLoadProductArray = Array(
        "IBLOCK_SECTION_ID" => false,
        "IBLOCK_ID" => $GLOBALS[REVIEW_PARAM_OT],
        "NAME" => tsi($_POST[title]),
        "ACTIVE" => "N",
        "PROPERTY_VALUES" => $PROP,
        "DETAIL_TEXT" => tsi($_POST[review1]),
    );
    $el->Add($arLoadProductArray);

    #echo "EX ";
    # print_r($exist);
    # echo "CH ";
    # print_r($ch);

    if (tsi($_POST[friendmail])) {

        $res = CIBlockSection::GetByID($_POST[section]);
        if ($ar_res = $res->GetNext()) {

            $arEventFields = array(
                "NAME" => $ar_res['NAME'],
                "TO" => $_POST[friendmail],
                "LINK" => "/product/view/" . $_POST[section] . "." . $_POST[item] . ".html"

            );

            $a = CEvent::Send("FRIEND", 's1', $arEventFields);
            #echo "-".$a;
        }
    }

    echo "�������! ��� ����� ��������.
 ����� �������� �� �������� �� �����.";
}
?>
