<table class="tt">
    <thead>
    <tr>
        <th></th>
        <th>Название</th>
        <th>Цена</th>
        <th>Шт</th>
        <th>Сумма</th>
    </tr>
    </thead>
    <tbody>
    {assign var=total_pqty value=0}
    {assign var=pqty value=0}
    {assign var=qty value=0}
    {assign var=big value=0}
    {assign var=x value=1}
    {foreach from=$order_goods item=g name=g}
        {if $g->id neq Cart::BLAG_ID and $g->price neq 0}{assign var=qty value=$qty+$g->quantity}{/if}
            <tr {if $x eq 1}class="odd"{/if}>
                {assign var=x value=-$x}
                <td>
                    {if $g->id eq Cart::BLAG_ID}
                        <img src="/i/otkazniki.jpg" alt="" class="img70" />
                    {elseif $g->price eq 0}
                        <img src="/i/gift70.png" alt="" class="img70" />
                    {else}
                        <img src="{$g->prop->get_img(70)}" alt="" class="img70" />
                    {/if}
                </td>
                <td class="name">
                    {if $g->id eq Cart::BLAG_ID}
                        <a href="/charity/cooperation.php">Благотворительность</a>
                    {elseif $g->price eq 0}
                        {$g->group_name} {$g->name}

                        {assign var=total_pqty value=$total_pqty+$g->quantity}
                    {else}
                        <a href="{$g->get_link(0)}">{$g->group_name} {$g->name}</a><input type="hidden" name="qty[{$g->id}]" value="{$g->quantity}" />{if ! empty($g->order_comment)}<br />{$g->order_comment}{/if}
                    {/if}
                </td>
                <td class="r nw">{$g->price|price}</td>
                <td class="r nw">x {$g->quantity} =</td>
                <td class="r nw">{$g->total|price}</td>
            </tr>
        {if $g->big}{assign var=big value=$big+1}{/if}
    {/foreach}

    {if not empty($presents)}
        {foreach from=$presents item=pid key=aid name=pid}
            {assign var=present value=$present_goods[$pid]}
	    {assign var=a value=$cart->actions[$aid]}
            {assign var=pqty value=$a->pq|default:1}
            {assign var=total_pqty value=$total_pqty + $pqty}
        <tr {if $x eq 1}class="odd"{/if}>
            {assign var=x value=-$x}
            <td><img src="/i/gift70.png" alt="{$present->name}" title="{$present->name}" class="img70" /></td>
            <td class="name">
	            <a href="{$a->get_link(0)}" target="_blank">{$present->name}</a></td>
            <td class="r nw"><abbr abbr="{$a->preview}">Подарок</abbr></td>
            <td class="r nw">x {$pqty} =</td>
            <td class="r nw">0</td>
        </tr>
        {/foreach}
    {/if}

    {if ! empty($blago)}
        <tr {if $x eq 1}class="odd"{/if}>
            {assign var=x value=-$x}
            <td><img src="/i/otkazniki.jpg" alt="В&nbsp;помощь объединению волонтеров Otkazniki.ru, оказывающему помощь детям-сиротам" title="В&nbsp;помощь объединению волонтеров Otkazniki.ru, оказывающему помощь детям-сиротам" class="img70" /></td>
            <td class="name"><a href="/charity/cooperation.php" target="_blank">Благотворительность</a></td>
            <td class="r nw">{$blago|price}</td>
            <td class="r nw"><abbr abbr="В&nbsp;помощь объединению волонтеров Otkazniki.ru, оказывающему помощь <nobr>детям-сиротам</nobr>.">?</abbr></td>
            <td class="r nw">{$blago|price}</td>
        </tr>
    {/if}

    {if ! empty($coupon)}
        <td><img src="/i/sale.png" alt="Скидка по купону" width="70"/></td>
        <td class="name" >Промо-акция {$coupon->name}</td>

        {if $coupon->type eq Model_Coupon::TYPE_SUM}

            <td class="r nw">-{$coupon->sum|price}</td>
            <td class="r nw">x 1 =</td>
            <td class="r nw">-{$coupon->sum|price}</td>

        {elseif $coupon->type eq Model_Coupon::TYPE_PERCENT}

            <td class="c nw" colspan="3">Вы получили скидку {$o->discount|price}</td>

        {/if}

    {/if}

    </tbody>
    <tfoot>
    <tr>
        <td colspan="2" class="nw">Общее количество товара:
	        <strong>{$qty}</strong>{if $total_pqty eq 1} + подарок{/if}{if $total_pqty gt 1} + подарки{/if}
	        {if $o->discount}. Ваша скидка - <strong class="discount">{$o->discount|price}</strong>{/if}
        </td>
        <td colspan="2" class="r nw">Сумма заказа:</td>
        <th class="r nw">{$o->price|price}</th>
    </tr>
    <tr>
        <td colspan="4" class="r">Стоимость доставки:</td>
        <th class="r nw">{if $o->delivery_type eq Model_Order::SHIP_COURIER or (($o->delivery_type eq Model_Order::SHIP_SERVICE or $o->delivery_type eq Model_Order::SHIP_OZON) and $o->price_ship gt 0)}{$o->price_ship|price}{else}?{/if}</th>
    </tr>
    <tr>
        <th colspan="2" class="l">
            {if $o->check}
                Электронный чек: {HTML::anchor($o->get_check(), basename($o->get_check()))}
            {/if}
        </th>
        <th colspan="2" class="r">Итого:</th>
        <th class="r nw">{$o->get_total()|price}</th>
    </tr>
    </tfoot>
</table>

{if $coupons|count > 0}
    <h2>Подарочные сертификаты</h2>
    <table id="list">
        <thead>
        <tr>
            <th>#</th>
            <th>Код</th>
            <th>Тип</th>
            <th>Активность</th>
            <th>Использований</th>
            <th>Работает</th>
        </tr>
        </thead>

        <tbody>
            {foreach from=$coupons item=coupon}
                <tr {cycle values='class="odd",'}>
                    <td>{$coupon.id}</td>
                    <td><a href="{Route::url('admin_edit', ['model' => 'coupon', 'id' => $coupon.id])}">{$coupon.name}</a></td>
                    <td>{Model_Coupon::type($coupon.type)}</td>
                    <td>{if $coupon.active}
                            <span class="green">вкл</span>
                        {else}
                            <span class="red">откл</span>
                        {/if}
                    </td>
                    <td>
                        {if $coupon.from}<span class="nowrap">c {$coupon.from}</span><br />{/if}
                        {if $coupon.to}<span{if !empty($coupon.to) && ($coupon.to < date('Y-m-d G:i:00'))} class="red nowrap"{/if}>по {$coupon.to}</span><br />{/if}
                        {if $coupon.min_sum}при сумме заказа от {$coupon.min_sum}р.<br />{/if}
                        <br />
                        {if $coupon.type eq Model_Coupon::TYPE_SUM}
                            Дает скидку {$coupon.sum} руб
                        {/if}
                    </td>
                    <td>{$coupon.uses} (использовано {$coupon.used})
                        <br />{$coupon.per_user} раз на аккаунт
                    </td>
                </tr>
            {/foreach}
        </tbody>
    </table>
    <br />
    {foreach from=$orderdata item=order_data}
        {assign var=ship_date value=$order_data.ship_date}
        {assign var=city value=$order_data.city}
        {assign var=street value=$order_data.street}
        {assign var=house value=$order_data.house}
        {if $order_data.correct_addr == 1}
            {assign var=correct_addr value=Y}
        {else}
            {assign var=correct_addr value=N}
        {/if}
        {assign var=latlong value=$order_data.latlong}
        {assign var=enter value=$order_data.enter}
        {assign var=lift value=$order_data.lift}
        {assign var=floor value=$order_data.floor}
        {assign var=domofon value=$order_data.domofon}
        {assign var=kv value=$order_data.kv}
        {assign var=mkad value=$order_data.mkad}
        {assign var=comment value=$order_data.comment}
    {/foreach}

    <form action="" method="post">
        <input type="submit" value="Отправить купоны" class="btn" name="activate_coupon">
    </form>

    <div>ЗАКАЗ</div>
    <div>{$ship_date|date_format:"%e.%m.%y"}©{$i->id}©{$i->user_id}©{$i->status}©0©{$i->price}©©©©©0</div>
    <div>АДРЕС: {$city}|{$street}|{$house}©{$correct_addr}©{$latlong}©{$enter}|{$lift}|{$floor}|{$domofon}|{$kv}|{$mkad}|{$comment}</div>
    <div>СКИДКА: {$o->discount}</div>
    <div>ОПЛАТА: {$i->pay_type}©{$i->price}©N</div>
    {foreach from=$order_goods item=g name=g}
        <div>{$g->code}©{$g->quantity}©{$g->price}</div>
    {/foreach}
    <div>КОНЕЦЗАКАЗА</div>
{/if}

<br />

<h2>Информация о доставке</h2>
<h3>Контактная информация</h3>
<dl>
    <dt>Имя:</dt><dd>{$od->name}</dd>
    <dt>Телефон:</dt><dd>{Txt::phone_format($od->phone)}</dd>
    {*<dt>Телефон для СМС:<dd>{Txt::phone_format($od->mobile_phone)}</dd>*}
    <dt>E-mail:</dt><dd>{$od->email}</dd>
</dl>

{if $o->delivery_type == Model_Order::SHIP_COURIER}
<h3 class="cl">Адрес доставки</h3>
<dl>
    <dt>Город:</dt><dd>{$od->city}</dd>
    <dt>Улица:</dt><dd>{$od->street}</dd>
    <dt>Дом:</dt><dd>{$od->house}</dd>
    <dt>Этаж:</dt><dd>{$od->floor}</dd>
    <dt>Номер квартиры/офиса:</dt><dd>{$od->kv}</dd>
    <dt>Лифт:</dt><dd>{if $od->lift}есть{else}нет{/if}</dd>
    <dt class="cl">Координаты:</dt><dd>{$od->latlong}</dd>
    <dt>Дом найден на карте?</dt><dd>{if $od->correct_addr}да{else}нет{/if}</dd>
    <dt class="cl">Зона доставки:</dt><dd>{Model_Zone::name($od->ship_zone)}</dd>
	{if $od->ship_zone eq Model_Zone::ZAMKAD}<dt>Расстояние от&nbsp;МКАД (км):</dt> <dd>{$od->mkad}</dd>{/if}
    <dt>Комментарий:</dt><dd>{$o->description|default:'нет'}</dd>
</dl>

<h3 class="cl">Время доставки</h3>
<dl>
    <dt>День доставки:</dt><dd>{$od->ship_date|date_ru:1}{if $big} *{/if}</dd>
    <dt>Часы доставки:</dt><dd>{$od->ship_time_text|default:Model_Zone_Time::name($od->ship_time)}</dd>
    <dt>Предварительный звонок:</dt><dd>{Model_Order::delivery_call($od->call)}</dd>
</dl>

{elseif $o->delivery_type == Model_Order::SHIP_SERVICE}

<h3 class="cl">Доставка транспортной компанией</h3>
<dl>
    <dt>Город:</dt> <dd>{$od->city}</dd>
    <dt>Улица:</dt><dd>{$od->street}</dd>
    <dt>Дом:</dt><dd>{$od->house}</dd>
    <dt>Номер квартиры/офиса:</dt><dd>{$od->kv|default:'не указан'}</dd>

    <dt class="cl">Cтоимость доставки:</dt> <dd>{if $o->price_ship != '0.00'}{$o->price_ship|price}{else}?{/if}</dd>
    <dt>Код доставки:</dt> <dd>{$od->comment|default:'?'}</dd>
</dl>

{elseif $o->delivery_type == Model_Order::SHIP_OZON}
<h3 class="cl">Пункт выдачи</h3>
<dl>
    <dt>Адрес:</dt> <dd>{$od->address}</dd>
</dl>
{/if}

{if empty($hide_pay_type)}
<h3 class="cl">Способ оплаты</h3>
<dl>
    <dd>
        {if $o->pay_type == Model_Order::PAY_CARD}
            <img src="/i/cards.png" alt="Виза, Мастеркард" />
        {else}
            Оплата наличными курьеру
        {/if}
    </dd>
    <dt>Сумма к&nbsp;оплате:</dt><dd>{if $o->pay8 == '0.00'}{$o->get_total()|price}{else}{$o->pay8|price}{/if}</dd>

    {if $o->pay_type == Model_Order::PAY_CARD}

        {if $o->pay1 != '0.00'}
            <dt>Доплата наличными:</dt><dd>{$o->pay1|price}</dd>
        {/if}

        <dd>{include file='user/order/payment.tpl'}</dd>

    {/if}
</dl>
{/if}