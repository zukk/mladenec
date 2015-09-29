<h1>Оплата по карте, заказ {$o->id}</h1>

<div class="mt">
    <strong>Сумма</strong>: {$payment->sum/100|price}<br />

    <strong>Статус</strong>:

{if $payment->status == Model_Payment::STATUS_New}
    Начало оплаты

{elseif $payment->status == Model_Payment::STATUS_PreAuthorized3DS}
    Платеж ожидает завершения 3DS авторизации

{elseif $payment->status == Model_Payment::STATUS_PreAuthorizedAF}
    Платеж ожидает завершения подтверждения от&nbsp;владельца карты

{elseif $payment->status == Model_Payment::STATUS_Authorized}
    Средства заблокированы
    Средства будут списаны с&nbsp;карты после доставки заказа.

{elseif $payment->status == Model_Payment::STATUS_Charged}
    Средства списаны с&nbsp;карты

{elseif $payment->status == Model_Payment::STATUS_Voided}
    Средства разблокированы
    Сроки будут возвращены на&nbsp;ваш счёт в&nbsp;сроки, зависящие от&nbsp;правил банка, выпустившего вашу карту.

{elseif $payment->status == Model_Payment::STATUS_Refunded}
    Возврат средств выполнен
    Сроки будут возвращены на&nbsp;ваш счёт в&nbsp;сроки, зависящие от&nbsp;правил банка, выпустившего вашу карту.

{elseif $payment->status == Model_Payment::STATUS_Rejected}
    Не удалось выполнить платёж

{elseif $payment->status == Model_Payment::STATUS_Error}
    Последняя операция прошла с&nbsp;ошибкой

{/if}

	{if $payment->status eq Model_Payment::STATUS_Charged or $payment->status eq Model_Payment::STATUS_Authorized}
	<script>
			window.dataLayer = window.dataLayer || [];
			dataLayer.push({
			  'userId': uid,
			  'ecommerce': {
				'purchase': {
				  'actionField': {
				'id': '{$o->id}',
				'affiliation': '{$vitrina}',
				'revenue': '{$o->price}',
				'shipping': '{$o->price_ship}'
				  },
				  'products': [
					{foreach from=$o->get_goods() item=g name=n}
					{if $g->price gt 0}
					  {                            
						'id': '{$o->id}',
						'name': '{$g->group_name|escape:'html'} {$g->name|escape:'quotes'}',
						'price': '{$g->price}',
						'category': '{$g->section->name|escape:'html'}',
						'brand': '{$g->brand->name|escape:'html'}',
						'quantity': '{$g->quantity}'
					   }{if !$smarty.foreach.n.last},{/if}
					{/if}
					{/foreach}
				   ]
				}
			  },
			  'event': 'transsuccess'
			});
	</script>
	{/if}
    <p><a href="/account/order/{$o->id}{if ! empty($thanx)}/thanx{/if}">Перейти к&nbsp;данным заказа</a></p>

</div>

