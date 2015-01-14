{foreach from=$orders item=o}
{capture assign=address}{$o->data->city}|{$o->data->street}|{$o->data->house}©{if $o->data->correct_addr}Y{else}N{/if}©{$o->data->latlong}©{$o->data->enter}|{$o->data->lift}|{$o->data->floor}|{$o->data->domofon}|{$o->data->kv}|{$o->data->mkad}|{$o->data->comment|replace:"\n":' '}{/capture}
ЗАКАЗ
ТИПЗАКАЗА {if empty($o->delivery_type)}1{else}0{/if}

ЗАРЕГИСТРИРОВАН: [{$o->data->ship_date|date_ru:1}, {Model_Zone_Time::name($o->data->ship_time)}, {$o->price_ship}][{if $o->data->call}{Model_Order::delivery_call($o->data->call)}{/if}, {$o->data->last_name} {$o->data->name} {$o->data->second_name}, {$o->data->phone} {$o->data->phone2}]
ОПИСАНИЕ: {$o->created|date_format:'%d.%m.%y|%H:%M:%S'}©{$o->id}©{$o->user_id}©{$o->status}©{if $o->user_status == 1}gold{else}stan{/if}

ТипДоставки: {$o->delivery_type|default:Model_Order::SHIP_COURIER}©{if $o->delivery_type == Model_Order::SHIP_SELF}{$o->data->address_id}{/if}{if $o->delivery_type == Model_Order::SHIP_SERVICE}{$o->data->address}{/if}
{*assign var=blag value=0}
{foreach from=$o->get_goods() item=g}{if $g->code eq '1blag'}{assign var=blag value=$g->total}{/if}{/foreach}
{if $o->coupon_id}{assign var=coupon value=$o->coupon->sum}{else}{assign var=coupon value=0}{/if}
{assign var=big value=$o->discount+$o->price-$blag+$coupon}{assign var=small value=$o->price-$blag+$coupon}{assign var=disc value=$small/$big*100-100*}
Скидка: 0 {*$disc|abs|round*}{* скидка тут - в процентах, без учёта благотворительности и купонов *}
Купон: {if $o->coupon_id}{$o->coupon->name}©{$o->coupon->sum}{/if}

{foreach from=ORM::factory('user_address')->where('user_id', '=', $o->user_id)->find_all() item=a}
АДРЕС{$a->id}: {$a->city}|{$a->street}|{$a->house}©{if $a->correct_addr}Y{else}N{/if}©{$a->latlong}©{$a->enter}|{$a->lift}|{$a->floor}|{$a->domofon}|{$a->kv}|{$a->mkad}|{$a->comment|replace:"\n":' '}
{/foreach}
КОД АДРЕСА: {if $o->delivery_type != Model_Order::SHIP_SELF}{$o->data->address_id}{/if}

ДОСТАВКА: {$o->data->ship_date|date_ru:1}©{Model_Zone_Time::name($o->data->ship_time)}©{$address}
ОСНОВНОЙ ТЕЛЕФОН: {$o->data->phone}
ДОПОЛНИТЕЛЬНЫЙ ТЕЛЕФОН: {$o->data->phone2}
ТЕЛЕФОН ДЛЯ СМС: {$o->data->mobile_phone}
ОПЛАТА: {$o->pay_type}©{$o->get_total($o->delivery_type == Model_Order::SHIP_SERVICE)}©Без сдачи
КОММЕНТАРИЙ: {$o->description|replace:"\n":' '}
{foreach from=$o->get_goods() item=g}
{$g->code}©{$g->quantity}©{$g->price}©{$g->total}©{$g->order_comment}
{/foreach}
SystDost{if $o->delivery_type == Model_Order::SHIP_SERVICE}TR{/if}©{$o->price_ship-$o->data->mkad*Model_Order::PRICE_KM}{* тут цена без учета мкад!!! *}
{if $o->data->mkad}SystMKAD©{$o->data->mkad}
{/if}
КОНЕЦ ЗАКАЗА
{/foreach}
КОНЕЦ ФАЙЛА