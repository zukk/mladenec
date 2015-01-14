<?php
$Key = "Mshop859";
$Data = $_POST['Data'];
$OrderId = $_POST["OrderId"];
$Amount = $_POST["Amount"];
//инициализируем сеанс
$curl = curl_init();
 
//уcтанавливаем урл, к которому обратимся
curl_setopt($curl, CURLOPT_URL, 'https://secure.payture.com/apim/Init');
 
//включаем вывод заголовков
curl_setopt($curl, CURLOPT_HEADER, 1);
 
//передаем данные по методу post
curl_setopt($curl, CURLOPT_POST, 1);
 
//теперь curl вернет нам ответ, а не выведет
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
 
//переменные, которые будут переданные по методу post
curl_setopt($curl, CURLOPT_POSTFIELDS, 'Key='.
$Key.'&Data='.$Data);

//я не скрипт, я браузер опера
curl_setopt($curl, CURLOPT_USERAGENT, 'Opera 10.00');
 
$res = curl_exec($curl);

//проверяем, если ошибка, то получаем номер и сообщение
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