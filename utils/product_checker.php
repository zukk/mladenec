<?php

require_once('../www/preload.php');

$goods = ORM::factory('good')->where('show', '=', 1)->find_all()->as_array('id');


$total = count($goods);
$not_in_stock = 0;

foreach($goods as $g)
{

   $link = 'http://mladenec-shop.dev.mladenec.lan/product/' . $g->translit . '/' . $g->group_id . '.' . $g->id. '.html';
   
   try 
   {
      $page = file_get_contents($link);
      
      if (strpos($page, 'Этого товара нет в наличии')) $not_in_stock ++;
   } 
   catch (ErrorException $e)
   {
      echo($e->getMessage() . PHP_EOL);
      Log::instance()->add(Log::INFO,$e->getMessage());
   }
   $local_name =  './product_checker/' . $g->id . '.html';
   
   if ( file_exists($local_name)) unlink($local_name);
   
   file_put_contents($local_name,$page);
   
}

echo(PHP_EOL . "Всего" . $total . " товаров." . PHP_EOL);
echo("Не в наличии " . $not_in_stock . " товаров." . PHP_EOL);