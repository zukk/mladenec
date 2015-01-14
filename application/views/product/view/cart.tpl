<p class="big sys">
    {if $cart->status_id == 0}
        Ваш статус&nbsp;&mdash; &laquo;<strong>Обычный</strong>&raquo;,
        для получения статуса &laquo;<strong>Любимый клиент</strong>&raquo;
        добавьте в&nbsp;корзину товаров на&nbsp;сумму <strong>{$cart->get_delta()|price|replace:'р.':'руб.'}</strong>
    {else}
        Ваш статус&nbsp;&mdash; &laquo;<strong>Любимый клиент</strong>&raquo;.
    {/if}
</p>

{if ! empty($coupon_error)}
<p><span class="discount">При активации промо-кода произошла ошибка:</span>
	{$coupon_error}
</p>

{/if}

{if ! empty($cart->no_possible)}
<p><span class="discount">Извините, доступное для заказа количество товаров ограничено:</span>
	<ul>
    {foreach from=$cart->no_possible item=g name=np}
    <li>&laquo;<strong>{$g.group_name} {$g.name}</strong>&raquo; &mdash;&nbsp;до&nbsp;<strong>{$g.qty}</strong>&nbsp;штук{if not $smarty.foreach.np.last}, {/if}</li>
    {/foreach}
	</ul>
    Надеемся на&nbsp;Ваше понимание.</p>
{/if}
<form action="{if empty($user->id)}?reg_done=-9341{/if}" method="post">
    <table class="tbl" id="">
		<col width="6%" />
		<col width="10%" />
		<col width="50%" />
		<col width="14%" />
		<col width="3%" />
		<col width="10%" />
		<col width="10%" />
        <thead>
            <tr>
                <th>№</th>
                <th></th>
                <th>Название</th>
                <th>Цена</th>
                <th>Количество</th>
                <th>Сумма</th>
                <th>Удалить</th>
            </tr>
        </thead>
        <tbody>
			{assign var=i value=0}
            {foreach array_values( $goods ) item=g}
                {capture assign=gname}{$g->group_name} {$g->name}{/capture}
                <tr>
					<td>{assign var=i value=$i+1}{$i}</td>
                    <td><img src="{$g->prop->get_img(70)}" alt="{$gname}" title="{$gname}" class="img70" /></td>
                    <td class="txt-lft">
						<table class='tbl-noborder'>
							<tr>
								<td>
									<div class="pencil fl-lft" style=''>
										<input type="hidden" name="comment[{$g->id}]" id="good_comment_{$g->id}" value="{$comments[$g->id]|default:''}" />
										<button title="Укажите &mdash; для мальчика или девочки, цвет и другие пожелания по данному товару" {if not empty($comments[$g->id])}class="filled"{/if} rel="good_comment_{$g->id}"></button>
									</div>
								</td>
								<td>
									<a href="{$g->get_link(0)}" target="_blank">{$gname}</a>
									<span class="cart_comment">{$cart->get_comment($g->id)}</span>
								</td>
							</tr>
						</table>
					</td>
                    <td class="price"><span>{$g->price|price}</span></td>
                    <td>
                        {include file='averburg/common/buy.tpl' good=$g}
                    </td>
                    <td><span>{$g->total|price}</span></td>
                    <td>
						<a class="ico ico-del" abbr="Пометить товар на удаление"></a>
					</td>
                </tr>
            {/foreach}
			{include file='averburg/cart/blago.tpl'}
        {assign var=total_pqty value=0}
		{include file='averburg/cart/gifts.tpl'}
        </tbody>
    </table>
	<div>
		{if $cart->coupon}
			<table>
				<tr>
	            <td class="delable"><abbr abbr="Не использовать купон"><label class="label"><i class="check"></i><input type="checkbox" name="remove_coupon[]"
					value="{$cart->coupon}" /></label></abbr></td>
				<td></td>
				<th class="name" colspan="2">Промо-акция {$cart->coupon.name}</th>
				<td class="price">-{$cart->coupon.sum|price}</td>
				<td>1</td>
				<td class="price">-{$cart->coupon.sum|price}</td>
				</tr>
			</table>
		{else}
		<div class='cart-prm fl-lft'>
			<abbr abbr="Если Вы хотите получить скидку, введите код купона"><b>ПРОМОКОД:</b></abbr>
			<input type='text' name="coupon" value="{$cart->coupon.name}" />
			<a href='#' class='undln'>Применить</a>
			{if $cart->discount gt 0}Ваша экономия <strong class="discount">{$cart->discount|price}</strong>{/if}
		</div>
		{/if}
		
		<div class='cart-info fl-rght'>
			{*
			{$cart->get_qty()} шт
			{if not empty($presents)}
				{if $total_pqty == 1}+ подарок{/if}
				{if $total_pqty > 1}+ подарки{/if}
			{/if}
			*}
			{assign var=allPrice value=$cart->get_total()|price}
			<div>Сумма заказа: {$allPrice} <abbr>Стоимость доставки</abbr></div>
			<br />
			<div class='cart-all-price fl-rght'>Итого <span class='b'>{$allPrice}</span></div>
			<div class='clear'></div>
			<br />
		</div>
	</div>
	<div class='clear'></div>
    {if not empty($promo)}
	<div id="kopilki">
        {foreach from=$promo item=p}
            <div class="promo" style="background-image:url({$p.cart_icon|default:'/i/attention.png'})">
                <p><i></i>
                    {$p.cart_icon_text|default:'Осталось купить&nbsp;на'}<br />
                    <strong>{$p.delta|price}</strong><br />
                    {if not empty($p.stage)}
                        <nobr>и&nbsp;ПОДАРОК {$p.stage} этапа ВАШ</nobr><br />
                    {else}
                        <nobr>и&nbsp;СКИДКА {$p.discount}% ВАША</nobr><br />
                    {/if}
                    <a href="{Route::url('action', ['id' => $p.action_id])}">Подробнее</a>
                </p>
            </div>
        {/foreach}
	</div>
    {/if}
	<div class='fl-lft'>
		<a title='Нажмите для пересчета' href='#' style='padding-left: 12px; padding-bottom: 12px; font-size: 1.2em' class="fl-lft undln">Пересчитать</a>
	</div>
	{*if empty( $user )}
	<div class='fl-rght'>
		<input {if empty( $user )} disabled{/if} type="submit" value="Оформить заказ" name="order" class="butt fl-lft" />
	</div>
	{/if*}
	<div class='clear'></div>
</form>

    {include file='common/one_click.tpl'}
