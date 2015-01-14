<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("���������� ������");
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/php_interface/include/sale_payment/pay_init.php");
?>
<style>
	.pay_success p{ padding: 5px 0px;}
	.pay_success p a {text-decoration: underline;}
	.pay_success p a:hover {text-decoration: underline; color:#00c0f3;}
	.pay_success h1 {color:#00c0f3; margin: 20px 0px 20px 0px;}
	.pay_success p.ok {font-weight: bold; font-size: 16px;}
</style>
<div class="pay_success" style="text-align: center;">

<?
//��� �������� ������� ������
//�������������� �����
$curl = curl_init();
 $OrderId = $_SESSION["PAYET_ORDER"];
curl_setopt($curl, CURLOPT_URL,  $Pay_host.'/apim/PayStatus'); //�c����������� ���, � �������� ���������
curl_setopt($curl, CURLOPT_HEADER, 1); //�������� ����� ����������
curl_setopt($curl, CURLOPT_POST, 1); //�������� ������ �� ������ post
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); //������ curl ������ ��� �����, � �� ������� 
curl_setopt($curl, CURLOPT_POSTFIELDS, "Key=".$Pay_key."&OrderId=$OrderId");//����������, ������� ����� ���������� �� ������ post
curl_setopt($curl, CURLOPT_USERAGENT, 'Opera 10.00'); //� �� ������, � ������� �����

 $res = curl_exec($curl);
//���������, ���� ������, �� �������� ����� � ���������
curl_close($curl);
if(!$res)
{
	$error = curl_error($curl).'('.curl_errno($curl).')';
	echo $error;
}else{
	$XML = explode("\r\n\r\n", $res);	
	$xml = simplexml_load_string($XML[1]);
	$json = json_encode($xml);
	$array = json_decode($json,TRUE);
	$res = $array["@attributes"];	
	switch ($res["State"]){// ������ "�������� ����������"
	
		case "Authorized"://�������� ���������� �� ������� � ���� ��������.
			echo "<h1>������� �� �������!</h1><p class=\"ok\">������ ���������</p>";
			//������ ������ ������. �� ��������
			$arOrder = CSaleOrder::GetByID($OrderId);
				
				if ($arOrder)
				{
				$sum = SaleFormatCurrency($res["Amount"]/100, "RUR");
				$arFields = array(				 
					  "PAYED" => "Y",
					  "EMP_PAYED_ID" => $USER->GetID(),
					  "USER_ID" => $arOrder["USER_ID"],
					  "PS_SUM" => $sum,
				   );
				   
				   if (CSaleOrder::Update($arOrder["ID"], $arFields)) 
				   {
						$arOrder = CSaleOrder::GetByID($OrderId);
				   }
				}
		break;
		
		case "Rejected": 
			echo "<h1>������!</h1><p>�� ������� ��������� �����</p><p>���������� ��������� �����</p>";
		break;
		
		case "Charged":
			echo "<h1>������!</h1><p>������ ��� ��������</p>";
		break;
		
		default: 
			echo "<h1>������!</h1><p>����������� ������ ".$res["State"].". ���������� ��������� �����.</p>";
		break;
	}
}
curl_close($curl2);
?>
<p>������� �� ���������� ������ ����� � <a href="/account/">������ ��������</a></p>
</div>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>