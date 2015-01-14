<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Результаты Оплаты");
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
//Для проверки Статуса Оплаты
//инициализируем сеанс
$curl = curl_init();
 $OrderId = $_SESSION["PAYET_ORDER"];
curl_setopt($curl, CURLOPT_URL,  $Pay_host.'/apim/PayStatus'); //уcтанавливаем урл, к которому обратимся
curl_setopt($curl, CURLOPT_HEADER, 1); //включаем вывод заголовков
curl_setopt($curl, CURLOPT_POST, 1); //передаем данные по методу post
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); //теперь curl вернет нам ответ, а не выведет 
curl_setopt($curl, CURLOPT_POSTFIELDS, "Key=".$Pay_key."&OrderId=$OrderId");//переменные, которые будут переданные по методу post
curl_setopt($curl, CURLOPT_USERAGENT, 'Opera 10.00'); //я не скрипт, я браузер опера

 $res = curl_exec($curl);
//проверяем, если ошибка, то получаем номер и сообщение
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
	switch ($res["State"]){// Статус "Средства заморожены"
	
		case "Authorized"://Средства замарожены на крточке и ждут списания.
			echo "<h1>Спасибо за покупку!</h1><p class=\"ok\">Оплата завершена</p>";
			//меняем статус оплаты. на оплачено
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
			echo "<h1>Ошибка!</h1><p>Не удалось выполнить платёж</p><p>Попробуйте повторить заказ</p>";
		break;
		
		case "Charged":
			echo "<h1>Ошибка!</h1><p>Платеж уже завершен</p>";
		break;
		
		default: 
			echo "<h1>Ошибка!</h1><p>Неизвестная ошибка ".$res["State"].". Попробуйте повторить заказ.</p>";
		break;
	}
}
curl_close($curl2);
?>
<p>Следить за состоянием заказа можно в <a href="/account/">личном кабинете</a></p>
</div>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>