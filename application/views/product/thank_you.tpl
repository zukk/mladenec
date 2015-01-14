<div id="breadcrumb">
    <a href="{Route::url('user')}">Личный кабинет</a> &rarr; <a href="{Route::url('order')}">Мои заказы</a>
</div>

<div id="simple">

    {if 0}
        <h1 class="yell">Заказ {$o->id}</h1>

        {* Polls promo *}
        {if ! empty($can_poll)}
        <p class="mt" style="font-size:17px; line-height:23px;">
            <a href="/communication/oprosi" class="butt fr" id="endtest">Участвовать в&nbsp;опросе</a>
            Ваше мнение очень важно для нас. Просим Вас принять участие в&nbsp;опросе:<br />
            Это не&nbsp;займёт у&nbsp;Вас много времени, но&nbsp;очень нам поможет. Спасибо.
        </p>

        {/if}

    {else}

		<form action="/product/add" method="post">

		<input type="submit" value="Повторить заказ" class="butt fr" />
        <h1>Заказ {$o->id}</h1>

        <h2 class="order_status">{$o->status()}</h2>
		{if $o->status == 'C' and $o->card->status lt Model_Payment::STATUS_Authorized and $o->card->form_url}
			<a href="{$o->card->form_url}" class="butt small fl">оплатить</a>
		{/if}

    {/if}

    {include file='user/order/view.tpl' cart=$o repeat=1}

    {if $o->description}
        <h3 class="cl">Комментарий</h3>
        <dl>
            <dd>{$o->description}</dd>
        </dl>
    {/if}

</div>