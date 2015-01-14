<div class="cart-oplata" class='sys'>
	<label>
		<input type="radio" name="pay_type" value="{Model_Order::PAY_DEFAULT}"{if $session_params.pay_type eq Model_Order::PAY_DEFAULT} checked="checked"{/if} /> Наличный расчет
	</label>
	<label>
		<input type="radio" name="pay_type" value="{Model_Order::PAY_CARD}"{if $session_params.pay_type eq Model_Order::PAY_CARD} checked="checked"{/if} /> Оплата картой VISA, Mastercard
	</label>
</div>
<div class='cart-visa' style='margin-top: 10px; padding: 10px;'>
	{include file='averburg/cart/card/info.tpl'}
</div>
