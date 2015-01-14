{strip}
{$delivery_time = Model_Zone_Time::name($od->ship_time)}
Принят заказ {$o->id},сумма {$o->price + $o->price_ship}р.
Доставка {$od->ship_date|date_format:'d.m.y'} {$delivery_time|replace:':00':''},
на {$od->street|replace:['улица ','улица']:['ул.','ул']}.
Звонок в день доставки.Ваш www.mladenec.ru
{/strip}