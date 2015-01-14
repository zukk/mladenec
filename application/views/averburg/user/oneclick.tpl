<form action="/registration" method="post" id="reg_form" class="ajax" autocomplete="off">
	<input name="phone" value="" placeholder="Мобильный телефон" class="txt" error="Этот телефон будет использован для&nbsp;связи с&nbsp;вами и&nbsp;подтверждения заказа" />
	<input type="submit" value="Купить в один клик" class="butt" />

	{if not empty($register_poll)}
		<p>{$register_poll->name}</p>
		<div class="regpoll"><img src="/i/load.gif" alt="" /></div>
	{/if}
</form>
