<h1>У&nbsp;вас есть незавершённый заказ</h1>

<div class="mt">
    <p>У&nbsp;вас есть заказ номер <strong>{$order->id}</strong>, на&nbsp;сумму <strong>{$order->get_total()|price}</strong>, для которого не&nbsp;завершена оплата банковской картой</p>
    <p><a href="/payment/back?{$order->id}">Отменить оплату и&nbsp;вернуться к&nbsp;оформлению заказа</a></p>
    <p><a href="/payment">Вернуться на&nbsp;страницу ввода данных банковской карты</a></p>

</div>

