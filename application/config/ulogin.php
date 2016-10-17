<?php

return [
	// Возможные значения: small, panel, window
	'type' 			=>  'panel',
	
	// на какой адрес придёт POST-запрос от uLogin
	'redirect_uri' 	=>  'http://www.mladenec-shop.ru/user/login',
	
	// Сервисы, выводимые сразу
	'providers' => [
		'vkontakte',
		'facebook',
		'yandex',
		'google',
	],
	
	// Выводимые при наведении
	'hidden'    => [
		'odnoklassniki',
		'mailru',
		'livejournal',
		'twitter'
	],
	
	// Эти поля используются для поля username в таблице users
	'username' 		=> [
		'first_name',
		'last_name',
	],
	
	// Обязательные поля
	'fields' 		=> [
		'email',
	],
	
	// Необязательные поля
	'optional'		=> [],
];
