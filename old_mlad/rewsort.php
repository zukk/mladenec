<?
error_reporting(0);
session_start();


if ($_GET[type] == 'rat') $_SESSION[rewsort] = 'rat';
if ($_GET[type] == 'date') $_SESSION[rewsort] = 'date';
if ($_GET[type] == 'pol') $_SESSION[rewsort] = 'pol';
header("Location: " . $_SERVER['HTTP_REFERER']);
exit();
?>