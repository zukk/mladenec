<?php
$Key = "Mshop859";
$Data = $_POST['Data'];
$OrderId = $_POST["OrderId"];
$Amount = $_POST["Amount"];
//�������������� �����
$curl = curl_init();
 
//�c����������� ���, � �������� ���������
curl_setopt($curl, CURLOPT_URL, 'https://secure.payture.com/apim/Init');
 
//�������� ����� ����������
curl_setopt($curl, CURLOPT_HEADER, 1);
 
//�������� ������ �� ������ post
curl_setopt($curl, CURLOPT_POST, 1);
 
//������ curl ������ ��� �����, � �� �������
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
 
//����������, ������� ����� ���������� �� ������ post
curl_setopt($curl, CURLOPT_POSTFIELDS, 'Key='.
$Key.'&Data='.$Data);

//� �� ������, � ������� �����
curl_setopt($curl, CURLOPT_USERAGENT, 'Opera 10.00');
 
$res = curl_exec($curl);

//���������, ���� ������, �� �������� ����� � ���������
if(!$res)
{
	$error = curl_error($curl).'('.curl_errno($curl).')';
	echo $error;
}else{
	$XML = explode("\r\n\r\n", $res);
	echo ($XML[1]);
	
	$xml = simplexml_load_string($XML[1]);
	$json = json_encode($xml);
	$array = json_decode($json,TRUE);
	$res = $array["@attributes"];
	if ($res["Success"]=="True")
	{
	 header	("Location: https://secure.payture.com/apim/Pay?&SessionId=$res[SessionId]");
	}else{
		echo "Error: Success is not True";
	}
}
 
curl_close($curl);
?>