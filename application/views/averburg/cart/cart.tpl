{if empty( $rand )}
	{assign var=rand value=rand(1,1000000)}
{/if}
<form action="{if empty($user->id)}?reg_done=-9341{/if}" method="post" id="form-{$rand}">
    <input type="hidden" name="hash" value="{$rand}" />
    <table class="tbl" id="cart_goods">
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
                <tr id='cart-good-tr-{$g->id}'>
					<td>{assign var=i value=$i+1}{$i}</td>
                    <td><img src="{$g->prop->get_img(70)}" alt="{$gname}" title="{$gname}" class="img70" /></td>
                    <td class="txt-lft">
						<table class='tbl-noborder'>
							<tr>
								<td>
									<div class="pencil fl-lft" style=''>
										<input class="pencilator-input" type="hidden" name="comment[{$g->id}]" id="good_comment_{$g->id}" value="{$comments[$g->id]|default:''}" />
										<button title="Укажите &mdash; для мальчика или девочки, цвет и другие пожелания по данному товару" {if not empty($comments[$g->id])}class="filled"{/if} rel="good_comment_{$g->id}"></button>
									</div>
								</td>
								<td>
									<a style="font-size: 1.2em;" href="{$g->get_link(0)}" target="_blank">{$gname}</a>
									{*<span class="cart_comment">{$cart->get_comment($g->id)}</span>*}
								</td>
							</tr>
						</table>
					</td>
                    <td class="price" style='white-space: nowrap'>{$g->price|price}</td>
                    <td>
                        {include file='averburg/common/buy.tpl' good=$g}
                    </td>
                    <td class='total'>{$g->total|price}</td>
                    <td>
						<a title='Удалить товар из корзины' data-id='{$g->id}' class="ico ico-del cart-remove-link" abbr="Пометить товар на удаление"></a>
					</td>
                </tr>
            {/foreach}
			{include file='averburg/cart/blago.tpl'}
        {assign var=total_pqty value=0}
		{include file='averburg/cart/gifts.tpl'}
        </tbody>
    </table>
	<div>
		{include file='averburg/cart/coupon.tpl' coupon=$cart->coupon error=$coupon_error}
		<div class='cart-all-summ fl-rght'>Итого <span class='b'>{$cart->get_total()|price}</span></div>
		<div class='clear'></div>
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
		<a title='Нажмите для пересчета' href='#' class="cart-recount-link fl-lft undln">Пересчитать</a>
	</div>
	{if empty( $open_delivery )}
	<div class='fl-rght'>
		<input {if 0 and empty( $user )} disabled{/if} type="submit" value="Оформить заказ" name="order-delivery" class="butt fl-lft" id="button-{$rand}" />
		<script>
			$(function(){
				$('#button-{$rand}').click(function(){
					$('#cart-delivery').slideDown(function(){
                        $('#cart-delivery').addClass('opened');
						$('.mladenecradio.checked').click();
						$('html, body').animate({ scrollTop: $('#cart-delivery').offset().top }, 'fast');
					});
					$(this).parent().slideUp();
					$('.cart-slider').slideUp();
					return false;
				});
			});
		</script>
	</div>
	{/if}
	<div class='clear'></div>
</form>