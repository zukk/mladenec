{strip}<p class="big sys">
	{if $cart->status_id == 0}
		Ваш статус&nbsp;&mdash; &laquo;<strong>Обычный</strong>&raquo;,
		для получения статуса &laquo;<strong>Любимый клиент</strong>&raquo;
		добавьте в&nbsp;корзину товаров на&nbsp;сумму <strong>{$cart->get_delta()|price|replace:'р.':'руб.'}</strong>
	{else}
		Ваш статус&nbsp;&mdash; &laquo;<strong>Любимый клиент</strong>&raquo;.
	{/if}
</p>

{if ! empty($cart->no_possible)}
<p><span class="discount">Извините, доступное для заказа количество товаров ограничено:</span>
	<ul>
	{foreach from=$cart->no_possible item=g name=np}
	<li>&laquo;<strong>{$g.group_name} {$g.name}</strong>&raquo; &mdash;&nbsp;до&nbsp;<strong>{$g.qty}</strong>&nbsp;штук{if not $smarty.foreach.np.last}, {/if}</li>
	{/foreach}
	</ul>
	Надеемся на&nbsp;Ваше понимание.</p>
{/if}
{/strip}