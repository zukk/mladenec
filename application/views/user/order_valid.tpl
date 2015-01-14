<div class="order_header">
    <div class="order_step1">
        <a href="{Route::url('order')}">Оформление заказа</a>
        Шаг 1 из 2
    </div>
    <div class="order_step2 active">
        <span>Подтверждение заказа</span>
        Шаг 2 из 2
    </div>
</div>
{include file='user/order/view.tpl' hide_pay_type=1}

{literal}
<script type="text/javascript">
$(document).ready(function() {
    $('#send_order').on('submit', function() {
        if (! document.getElementById('agree').checked) {
            alert('Для отправки заказа Вы должны согласиться с пользовательским соглашением.');
            return false;
        }
    });
    $('input[name=pay_type]').change(function() {
        $('#cardinfo').toggle($(this).val() == {/literal}{Model_Order::PAY_CARD}{literal});
    });
});
</script>
{/literal}

<form action="/personal/order.php" class="ajax" id="send_order">
<h3 class="cl">Дополнительная информация</h3>
<small>укажите здесь пол ребёнка или другие пожелания к заказу</small>
<textarea name="description" class="txt" style="width:500px; height:50px;"></textarea>

{if ! empty($config->accept_cards)}

<h2 class="mt">Выберите способ оплаты</h2>
<div>

    {* оплата наликом *}
    {if $o->delivery_type neq Model_Order::SHIP_SERVICE}
		<label class="label"><i class="radio"></i><input type="radio" name="pay_type" value="{Model_Order::PAY_DEFAULT}" checked="checked">{if $o->delivery_type eq Model_Order::SHIP_SELF}Оплата при получении заказа{else}Оплата наличными курьеру{/if}</label>
	{/if}

    {if $o->delivery_type eq Model_Order::SHIP_SELF}
        <p>Вы&nbsp;не&nbsp;можете сейчас оплатить Ваш заказ картой, так как Вы&nbsp;выбрали самовывоз</p>
    {else}

        {if $o->delivery_type eq Model_Order::SHIP_COURIER or empty($big)}
            <label class="label"><i class="radio"></i><input type="radio" name="pay_type" value="{Model_Order::PAY_CARD_LATER}" {if $o->delivery_type eq Model_Order::SHIP_SERVICE}checked="checked"{/if}>Оплата картой VISA, MasterCard после сборки заказа</label>
            <label class="label"><i class="radio"></i><input type="radio" name="pay_type" value="{Model_Order::PAY_CARD}" {if $o->delivery_type eq Model_Order::SHIP_SERVICE}checked="checked"{/if}>Оплата картой VISA, MasterCard прямо сейчас</label>
        {else}

            <label class="label"><i class="radio"></i><input type="radio" name="pay_type" value="{Model_Order::PAY_CARD_LATER}" {if $o->delivery_type eq Model_Order::SHIP_SERVICE}checked="checked"{/if}>Оплата картой VISA, MasterCard после сборки заказа</label>
            {*<p>Вы&nbsp;не&nbsp;можете оплатить Ваш заказ картой, так как Вы&nbsp;заказали крупногабаритный товар:
                <ul>
                    {foreach from=$big item=i}
                    <li><a href="{$i->get_link(0)}">{$i->group_name} {$i->name}</a></li>
                    {/foreach}
                </ul>
            </p>*}
        {/if}
    {/if}

</div>
<br />
<div id="cardinfo" class="hide">
    <p align="justify">Далее, при нажатии кнопки &laquo;Отправить заказ&raquo;, Вы&nbsp;будете перенаправлены на&nbsp;платежный шлюз Payture для ввода следующих данных о&nbsp;Вашей карте: номер карты, имя и&nbsp;фамилия держателя карты, срок действия карты, защитный код (CVV2/CVC2). Пожалуйста, приготовьте Вашу пластиковую карту заранее. Соединение с&nbsp;платежным шлюзом осуществляется по&nbsp;защищенному каналу связи с&nbsp;использованием протокола шифрования SSL. Далее информация передается по&nbsp;высокозащищенным банковским сетям, что исключает возможность ее&nbsp;компрометации.</p>
    <p align="justify">В&nbsp;случае если Ваш банк поддерживает технологию безопасного проведения <nobr>интернет-платежей</nobr> Verified By&nbsp;Visa или Secure Code, для проведения операции по&nbsp;карте также может потребоваться ввод одноразового пароля. Способы и&nbsp;возможность получения одноразового пароля для совершения <nobr>интернет-платежа</nobr> Вы&nbsp;можете уточнить в&nbsp;банке, выпустившем карту.</p>
    <p align="justify"><strong>Если сумма по&nbsp;доставленному заказу меньше блокированной, то&nbsp;разница возвращается покупателю на&nbsp;карту в&nbsp;течение 30&nbsp;(тридцати) банковских дней с&nbsp;даты получения товара покупателем, если больше&nbsp;&mdash; разницу необходимо доплатить курьеру наличными средствами.</strong></p>
    <p align="justify"><img src="/i/attention.png" align="left" /><strong>ВНИМАНИЕ! Если заблокированная при&nbsp;оформлении заказа разница или&nbsp;полная сумма при&nbsp;отмене заказа 
        по&nbsp;каким-то причинам не&nbsp;вернулась к&nbsp;вам в&nbsp;оговоренные сроки&nbsp;&mdash; обязательно свяжитесь
        с&nbsp;банком-эмитентом вашей карты и&nbsp;сообщите о&nbsp;проблеме нам на&nbsp;электронную почту
        <a href="mailto:request@mladenec.ru">request@mladenec.ru</a>. Мы&nbsp;приложим все&nbsp;силы, чтобы&nbsp;разрешить
        вопрос в&nbsp;кратчайшие сроки.</strong></p>
    <p align="justify"><b><span style="color:red;">ВНИМАНИЕ!!!</span> После ввода данных карты на&nbsp;странице оплаты нажмите кнопку "Оплатить", затем обязательно
            ждите возврата на&nbsp;страницу "Спасибо, Ваш заказ принят!" нашего сайта. Это необходимо для корректного оформления заказа.</b></p>
    <p align="justify"><nobr>ООО &laquo;ТД Младенец. РУ&raquo;</nobr> не&nbsp;передает, не&nbsp;получает, не&nbsp;хранит и&nbsp;не&nbsp;обрабатывает данные о&nbsp;Вашей пластиковой карте.</p>
    <p align="justify"><a href="/about/cards.php" target="_blank">подробнее об оплате банковскими картами</a></p>
</div>

{else}

	<input type="hidden" name="pay_type" value="{Model_Order::PAY_DEFAULT}" />

{/if}

<div class="mt">
    <label class="label"><i class="check"></i><input type="checkbox" name="agree" id="agree" value="1" checked="checked" />С&nbsp;пользовательским соглашением
        на&nbsp;предоставление услуг <nobr>интернет-магазином</nobr> Младенец.ру согласен.</label>
    <br /><a href="/about/agreement.php" target="_blank">читать пользовательское соглашение</a>

</div>

<div class="mt">
    <input type="submit" value="Отправить заказ" class="butt" />
</div>
{if ! empty($big)}
    <p>* Срок поступления товара на&nbsp;склад может быть увеличен в&nbsp;связи с&nbsp;непредвиденными обстоятельствами. Мы&nbsp;ценим своих клиентов и&nbsp;делаем все возможное, чтобы товар был доставлен Вам в&nbsp;срок!</p>
{/if}
</form>
