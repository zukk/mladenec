{if not $o->can_pay}

    <p>Мы уже собираем Ваш заказ, проверяем наличие всех товаров и&nbsp;уточняем стоимость доставки. <br />Мы сообщим Вам, когда заказ будет готов к&nbsp;оплате</p>

{else}
    {assign var=payment value=$o->payment()}

    {if not $payment->status_final() and $o->status != 'X'}

        <a href="{Route::url('pay', ['id' => $o->id])}" class="butt small">оплатить</a>

    {else}

        {$payment->status_info()}
        <br />{$payment->sum/100|price}&nbsp;р. <small>[{$payment->status_time}]</small>
    {/if}

{/if}
