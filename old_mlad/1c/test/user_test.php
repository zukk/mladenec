<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
CModule::IncludeModule("iblock");

	global $USER;
	$arGroups = CUser::GetUserGroup(45587);
	print_r($arGroups);
	die();
	
	$user_ = new CUser;
	$fields = Array(
	  "ACTIVE"            => "Y",
	  "GROUP_ID"          => array(8, 3),
	  );
	  	
	print_r($user);
	print_r($fields);
	if($user_->Update(45587, $fields)) echo 45587;
?>