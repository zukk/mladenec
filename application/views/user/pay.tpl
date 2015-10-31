<div id="breadcrumb">
    <a href="{Route::url('user')}">Личный кабинет</a> &rarr; <a href="{Route::url('user_order')}">Мои заказы</a>
</div>

<h1>Оплата заказа {$order->id}</h1>

{assign var=payment value=$order->payment()}

{if $order->can_pay and $payment->status lt Model_Payment::STATUS_Authorized}
    <form action="" method="post" class="ajax fr">
        <input type="submit" name="do_pay" class="butt" value="Перейти к оплате"/>
    </form>
{/if}

<p>Сумма к оплате <strong>{$order->get_total()|price}</strong></p>

<a href="{Route::url('order_detail', ['id' => $order->id])}">подробнее о заказе</a>

{if not $order->can_pay}

    <p>Извините, сумма к&nbsp;оплате ещё не&nbsp;подтверждена менеджером. Оплата этого заказа пока невозможна. Мы&nbsp;сообщим Вам по&nbsp;email и&nbsp;SMS, когда заказ будет готов к оплате</p>

{elseif $payment->status gte Model_Payment::STATUS_Authorized}

    <h2 class="mt">Статус оплаты</h2>
    <p>{$payment->status_info()} ({$payment->status_time})</p>
    {if $payment->status == Model_Payment::STATUS_Rejected}
        <p><span style="color:red;">При последней попытке оплаты произошла ошибка!</span></p>

        <form action="" method="post" class="cb ajax fl">
            <input type="submit" name="do_pay" class="butt" value="Попробовать оплатить ещё раз"/>
        </form>

    {/if}

{else}

    <p>Далее, при нажатии кнопки &laquo;Перейти к&nbsp;оплате&raquo;, Вы&nbsp;будете перенаправлены на&nbsp;платежный шлюз RBS для ввода
    следующих данных о&nbsp;Вашей карте: номер карты, имя и&nbsp;фамилия держателя карты, срок действия карты,
    защитный код (CVV2/CVC2).</p>

    <p>
        <img src="/i/attention.png" alt="!" align="left" />
        Пожалуйста, приготовьте Вашу пластиковую карту заранее. <b>На&nbsp;сеанс ввода данных карты дается 20&nbsp;минут.</b>
    </p>

    <p>Соединение с&nbsp;платежным шлюзом осуществляется по&nbsp;защищенному каналу связи с&nbsp;использованием протокола шифрования SSL. Далее информация передается
    по&nbsp;высокозащищенным банковским сетям, что исключает возможность ее&nbsp;компрометации.</p>

    <p>В&nbsp;случае если Ваш банк поддерживает технологию безопасного проведения <nobr>интернет-платежей</nobr> Verified By&nbsp;Visa или Secure Code,
    для проведения операции по&nbsp;карте также может потребоваться ввод одноразового пароля. Способы и&nbsp;возможность получения
    одноразового пароля для совершения <nobr>интернет-платежа</nobr> Вы&nbsp;можете уточнить в&nbsp;банке, выпустившем карту.</p>

    <p><strong>Если сумма по&nbsp;доставленному заказу меньше блокированной, то&nbsp;разница возвращается покупателю на&nbsp;карту в&nbsp;течение
    30&nbsp;(тридцати) банковских дней с&nbsp;даты получения товара покупателем, если больше&nbsp;&mdash; разницу необходимо доплатить курьеру наличными средствами.</strong></p>

    <p><strong>ВНИМАНИЕ! Если заблокированная при&nbsp;оформлении заказа разница или&nbsp;полная сумма при&nbsp;отмене заказа
    по&nbsp;каким-то причинам не&nbsp;вернулась к&nbsp;вам в&nbsp;оговоренные сроки&nbsp;&mdash; обязательно свяжитесь
    с&nbsp;банком-эмитентом вашей карты и&nbsp;сообщите о&nbsp;проблеме нам на&nbsp;электронную почту
    <a href="mailto:request@mladenec.ru">request@mladenec.ru</a>. Мы&nbsp;приложим все&nbsp;силы, чтобы&nbsp;разрешить
    вопрос в&nbsp;кратчайшие сроки.</strong></p>

    <p><b><span style="color:red;">ВНИМАНИЕ!!!</span> После ввода данных карты на&nbsp;странице оплаты нажмите кнопку "Оплатить", затем обязательно
    ждите перехода на&nbsp;страницу c&nbsp;результатом оплаты. Это необходимо для корректного оформления заказа.</b></p>
    <p><nobr>ООО &laquo;ТД Младенец. РУ&raquo;</nobr> не&nbsp;передает, не&nbsp;получает, не&nbsp;хранит и&nbsp;не&nbsp;обрабатывает данные о&nbsp;Вашей пластиковой карте.</p>
    <p><a href="/about/cards.php" target="_blank">подробнее об оплате банковскими картами</a></p>

{/if}


