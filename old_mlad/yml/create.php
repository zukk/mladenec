<?
require($_SERVER["DOCUMENT_ROOT"].
"/bitrix/modules/main/include/prolog_before.php");
CModule::IncludeModule("iblock");
CModule::IncludeModule("catalog");

$fileName = "price/YML.xml";
// echo $fileName;
// die();
$file = fopen($fileName, 'w+');
$data = date("Y-m-d H:i");
$XML_header = <<<HEAD
<?xml version="1.0" encoding="windows-1251"?>
<!DOCTYPE yml_catalog SYSTEM "shops.dtd">
HEAD;
$Xml_Body = "<yml_catalog date=\"".$data."\">
    <shop>
		<name>mladenec.ru</name>
			<company>ООО \"младенец.ру\"</company>
			<url>http://www.mladenec-shop.ru/</url>
			<platform>\"1С-Битрикс: Управление сайтом\"</platform>
			<version>10.0.7</version>
			<agency></agency>
			<email>parfenov.a@mladenec.ru</email>

			<currencies>
				    <currency id=\"RUR\" rate=\"1\"/>
					<currency id=\"USD\" rate=\"CBRF\" plus=\"1\"/>
					<currency id=\"EUR\" rate=\"CBRF\" plus=\"1\"/>
			</currencies>
<categories>";
foreach ($_SESSION["YML"]["category"] as $section){
$Xml_Body .= $section;
}
$Xml_Body .= "</categories>\n<local_delivery_cost>350</local_delivery_cost>\n<offers>";

foreach ($_SESSION["YML"]["units"] as $unit){
$Xml_Body .= $unit;
}
$Xml_Body .= "</offers>\n</shop>
</yml_catalog>";

$XML = $XML_header. $Xml_Body;
$writr = fwrite($file, $XML);
unset ($_SESSION["YML"]);
header('Location: /yml/xml.php');
?>