<h1>Личный кабинет</h1>

<div class="tabs mt">

    {include file='user/personal.tpl' active='user_order'}

    <div class="tab-content active">

    {if $orders}
        <table id="orders" class="tt">
            <thead>
            <tr>
                <th>Номер</th>
                <th>Создан</th>
                <th>Оплата</th>
                <th class="r">Сумма</th>
                <th class="r">Доставка</th>
                <th class="r">К&nbsp;оплате</th>
                <th>Состояние</th>
            </tr>
            </thead>
            <tbody>
            {foreach from=$orders item=o}
            <tr {cycle values='class="odd",'}>
                <td>{$o->get_link()}</td>
                <td>{$o->created}</td>
                <td class="c">{if $o->pay_type eq Model_Order::PAY_CARD}
                        <img src="/i/cards.png" alt="Картой Visa, Mastercard" /><br />
                        {include file='user/order/payment.tpl'}
                    {else}
                        наличными
                    {/if}
                </td>
                <td class="r nw">{$o->price|price}</td>
                <td class="r nw">{if $o->delivery_type eq Model_Order::SHIP_COURIER or (($o->delivery_type eq Model_Order::SHIP_SERVICE or $o->delivery_type eq Model_Order::SHIP_OZON) and $o->price_ship gt 0)}{$o->price_ship|price}{else}?{/if}</td>
                <td class="r nw">{$o->get_total()|price}</td>
                <td class="c">{$o->status()}</td>
            </tr>
            {/foreach}
            </tbody>
        </table>
        {$pager->html('Заказы')}

    {else}

        <p>Вы ещё не&nbsp;сделали ни&nbsp;одного заказа</p>

        {if not empty($config->rr_enabled)}
            <div class="cl rr_slider" title="Рекомендуем Вам:" data-func="PersonalRecommendation" data-param="{$smarty.cookies.rrpusid}"></div>
        {/if}

    {/if}
    </div>
</div>
