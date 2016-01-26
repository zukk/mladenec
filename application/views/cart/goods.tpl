<form action="" method="post" id="cart-form">

    <a class="cart-clear-link">Очистить корзину</a>

    <div class="cart-header cb">
        <p class="big sys">
            {if $cart->status_id == 0}
                Ваш статус&nbsp;&mdash; &laquo;<strong>Обычный</strong>&raquo;,
                                                                              для получения статуса &laquo;<strong>Любимый клиент</strong>&raquo;
                добавьте в&nbsp;корзину товаров на&nbsp;сумму <strong>{$cart->get_delta()|price|replace:'р.':'руб.'}</strong>
            {else}
                Ваш статус&nbsp;&mdash; &laquo;<strong>Любимый клиент</strong>&raquo;.
            {/if}
        </p>

        {if ! empty($cart->no_possible)}
            <div>
                <span class="discount">Извините, доступное для заказа количество товаров ограничено:</span>
                <ul>
                    {foreach from=$cart->no_possible item=g name=np}
                        <li>&laquo;<strong>{$g.group_name} {$g.name}</strong>&raquo; &mdash;&nbsp;до&nbsp;<strong>{$g.qty}</strong>&nbsp;штук{if not $smarty.foreach.np.last}, {/if}</li>
                    {/foreach}
                </ul>
                Надеемся на&nbsp;Ваше понимание.
            </div>
        {/if}
    </div>

    <table class="tbl" id="cart_goods">
		<col width="3%" />
		<col width="9%" />
        <col width="5%" />
		<col width="54%" />
		<col width="9%" />
		<col width="8%" />
		<col width="9%" />
		<col width="3%" />
        <thead>
            <tr>
                <th>№</th>
                <th></th>
                <th></th>
                <th>Название</th>
                <th>Цена</th>
                <th>Количество</th>
                <th>Сумма</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            {foreach from=$goods item=g name=i}
            {if $g->code neq Model_Good::SBORKA_ID1C}
                {capture assign=gname}{$g->group_name|escape:html} {$g->name|escape:html}{/capture}
                <tr id="cart-good-tr-{$g->id}">
					<td>{$smarty.foreach.i.iteration}</td>
                    <td>{if $images[$g->id][70]}<a href="{$g->get_link(0)}" target="_blank"><img src="{$images[$g->id][70]->get_url()}" alt="{$gname}" title="{$gname}" class="img70" /></a>{else}{$g->id}{/if}</td>
                    <td>
						<div class="pencil fl">
                            <input class="pencilator-input" type="hidden" name="comment[{$g->id}]" id="good_comment_{$g->id}" value="{$comments[$g->id]|default:''}" />
                            <button title="{$comments[$g->id]|default:'Укажите &mdash; для мальчика или девочки, цвет и другие пожелания по данному товару'}"
                            {if not empty($comments[$g->id])}class="filled"{/if} rel="good_comment_{$g->id}"></button>
                        </div>
                    </td>
                    <td class="txt-lft">
                        <a class="google-good" href="{$g->get_link(0)}" target="_blank">{$gname}</a>
					</td>
                    <td class="price">{$g->price|price}</td>
                    <td>
                        {include file="common/buy.tpl" good=$g}
                        {$g|qty}
                    </td>
                    <td class="total">{$g->total|price}</td>
                    <td>
						<a title="Удалить товар из корзины" data-id="{$g->id}" class="ico ico-del cart-remove-link"></a>
					</td>
                </tr>
            {/if}
            {/foreach}

            {if not empty($cart->sborkable)}
                {assign var=g value=$sborka}
                {capture assign=gname}{$g->group_name|escape:html} {$g->name|escape:html}{/capture}

                <tr id="cart-good-tr-{$g->id}">
                    <td><abbr abbr="Мы предоставляем услугу бесплатной сборки мебели">?</abbr></td>
                    <td colspan="2"><img src="/i/sborka.jpg" alt="Бесплатная сборка" title="Бесплатная сборка" class="img70" /></td>
                    <td class="txt-lft">
                        <a href="/delivery#sborka_tovara" target="_blank">Бесплатная сборка мебели</a><br />
                        (Точную дату сборки вы сможете согласовать с нашим оператором)
                    </td>
                    <td class="price">{$g->price|price}</td>
                    <td class="c">
                        <div class="incdeced">
                            {*{Form::select('sborka_comment', Model_Good::sborka(), $comments[$g->id]|default:'', ['id' => 'sborka', 'rel' => $g->id])}*}
                            {Form::select('sborka_comment', Model_Good::sborka(), '', ['id' => 'sborka', 'rel' => $g->id])}
                        </div>
                     </td>
                    <td class="total">{$g->total|price}</td>
                    <td></td>
                </tr>
            {/if}

            <tr id="cart-good-tr-blago">
                <td><abbr abbr="В&nbsp;помощь объединению волонтеров Otkazniki.ru, оказывающему помощь <nobr>детям-сиротам</nobr>.">?</abbr></td>
                <td colspan="2"><img src="/i/otkazniki.jpg" alt="В&nbsp;помощь объединению волонтеров Otkazniki.ru, оказывающему помощь детям-сиротам" title="В&nbsp;помощь объединению волонтеров Otkazniki.ru, оказывающему помощь детям-сиротам" class="img70" /></td>
                <td class="txt-lft"><a href="/charity/cooperation.php" target="_blank">Благотворительность</a>
                <td class="price">1&nbsp;р.</td>
                <td>{include file="common/buy.tpl" good=blago}</td>
                <td class="total"><span>{$blago}</span> р.</td>
                <td></td>
            </tr>

		    {include file="cart/gifts.tpl"}

        </tbody>
    </table>

	<div class="oh">

        {if $cart->discount gt 0}<div id="economy">Ваша экономия <strong class="discount">{$cart->discount|price}</strong></div>{/if}

        <div id="totals">
            Вес: <b id="weight">{'%01.2f'|sprintf:$cart->weight()}&nbsp;кг</b>;
            Объём: <b id="volume">{'%01.3f'|sprintf:$cart->volume()}&nbsp;м<sup>3</sup></b>
            <div class="cart-all-summ">
                Товары: <span id="goods_price">{$cart->get_total()|price}</span><br />
                Доставка: <span id="ship_price">
                    {if $user}
                        {$cart->ship_price()|price}
                    {else}
                        {if $cart->delivery_open}
                            сообщит менеджер
                        {else}
                            не определена
                        {/if}
                    {/if}
                </span>
            </div>
        </div>

        {include file="cart/coupon.tpl"}


        <div class="cl fl">
            {if ! empty($include)}
                <a title="Нажмите для пересчета" class="cart-recount-link">Пересчитать</a>
            {else}
                <a title="Нажмите для пересчета" class="cart-recount-link changed">Пересчитали</a>
            {/if}
        </div>
	</div>

    {if not empty($cart->promo)}
	<div id="kopilki">
        {foreach from=$cart->promo item=p}
            <div class="promo" style="background-image:url({$p.cart_icon|default:"/i/attention.png"})">
                <p><i></i>
                    {$p.cart_icon_text|default:"Осталось купить&nbsp;на"}<br />
                    <strong>{$p.delta|price}</strong><br />
                    {if not empty($p.stage)}
                        {if $p.stage != -1}
                        <nobr>и&nbsp;ПОДАРОК {$p.stage} этапа ВАШ</nobr><br />
                        {/if}
                    {else}
                        <nobr>и&nbsp;СКИДКА {$p.discount}% ВАША</nobr><br />
                    {/if}
                    <a href="{Route::url("action", ["id" => $p.action_id])}">Подробнее</a>
                </p>
            </div>
        {/foreach}
	</div>
    {/if}

    {* google adwords remarketing params - put inside form to replace on ajax *}
    <script>
        var google_tag_params = {
            ecomm_pagetype: 'cart',
            ecomm_totalvalue: '{$cart->get_total()}',
            ecomm_prodid: [ {foreach from=$goods item=g name=g}{$g->id}{if not $smarty.foreach.g.last},{/if}{/foreach} ]
        };
    </script>

</form>

