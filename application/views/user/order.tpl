<div id="breadcrumb">
    <a href="{Route::url('user')}">Личный кабинет</a> &rarr; <a href="{Route::url('user_order')}">Мои заказы</a>
</div>

{if empty($thanx)} {* старый заказ *}

    <form action="{Route::url('product_add')}" method="post">

    <input type="submit" value="Повторить заказ" class="butt fr" />
    <h1>Заказ {$o->id}</h1>

    <h2 class="order_status">{$o->status()}</h2>

    {include file='user/order/payment.tpl'}

    {include file='user/order/view.tpl' cart=$o repeat=1}

    {if $o->description}
        <h3 class="cl">Комментарий</h3>
        <dl>
            <dd>{$o->description}</dd>
        </dl>
    {/if}
    </form>

{else} {* спасибо - страница*}

    <div style="font-size:1.2em; padding: 20px;">
        <div class="fr"><img src="/i/averburg/cat/{rand(1,4)}.jpg" /></div>

        <h1 class="b black">Заказ принят</h1>

        {if $o->can_pay AND $o->pay_type == Model_Order::PAY_CARD}
            <p>Для проведения платежа нажмите &laquo;ОПЛАТИТЬ ЗАКАЗ&raquo;</p>
        {/if}

        <div style="padding:40px;">
            <p>Номер вашего заказа <b>{$o->id}</b></p>
            <p>Итого <strong>{$o->get_total()|price}</strong>
                {if $o->price_ship != '0.00' || $o->delivery_type eq Model_Order::SHIP_COURIER}
                    (в том числе доставка {$o->price_ship|price})
                {else}
                    (без учёта стоимости доставки)
                {/if}
            </p>

            {if $o->delivery_type eq Model_Order::SHIP_SERVICE and $o->price_ship eq '0.00'}
                <small>Окончательную стоимость доставки уточнит менеджер по&nbsp;телефону</small>
            {/if}

            {if $o->can_pay AND $o->pay_type == Model_Order::PAY_CARD}

                <p class="fl cl"><a href="{Route::url('pay', ['id' => $o->id])}" class="butt">Оплатить заказ</a></p>

            {/if}

            {if $o->type eq Model_Order::TYPE_ONECLICK}
                <p class="cl">Менеджер перезвонит
                    <span class="hide" id="oco_labor">в&nbsp;течение нескольких минут</span>
                    <span class="hide" id="oco_weekend">с&nbsp;Вами свяжется в&nbsp;рабочее время с&nbsp;9:00 до&nbsp;18:00</span>
                    <script>
                        $(function(){
                            var now = new Date(), day = now.getDay(), hour = now.getHours(), weekend = day == 0 || day == 6;

                            if ((weekend && (hour >= 21 || hour < 10)) || ( ! weekend && (hour >= 22 || hour < 9))) {
                                $('#oco_weekend').show();
                            } else {
                                $('#oco_labor').show();
                            }
                        });
                    </script>
                    на&nbsp;номер <span id="oco_phone">{$phone}</span>.
                </p>
                <p>
                    Спасибо!
                </p>
            {/if}

            {if $o->delivery_type eq Model_Order::SHIP_COURIER and not empty($o->data->ship_date)}
                <p class="cl">Заказ будет доставлен <b>{Txt::ru_date($o->data->ship_date)}</b></p>
            {/if}

            <p class="cl">Чтобы увидеть состав заказа или повторить заказ, <br />войдите в&nbsp;<a class="b black u" href="{Route::url('order_detail', ['id' => $o->id])}">Личный кабинет</a></p>

            {if $o->type eq Model_Order::TYPE_ONECLICK}
			<p style="font-size: 0.9em; color: #999;">
				В&nbsp;случае, если мы не&nbsp;сможем с&nbsp;Вами связаться в&nbsp;течение суток,
				просим повторно оформить заказ или перезвонить нам по&nbsp;телефону: <nobr>8 (800) 555-699-4</nobr>
			</p>
		    {/if}

            <p style="font-weight:bold; color: #02a7c5;">Будем рады видеть Вас снова!</p>

        </div>
    </div>

    {*  тут все скрипты для всякой статистики *}
    {if not empty($is_new) AND (Kohana::$environment eq Kohana::PRODUCTION)}
        <script>

            var ya_goods = [];
            {foreach from=$order_goods item=g}
            {if $g->price gt 0}
            ya_goods.push({
                id: {$g->id},
                name: '{$g->group_name|escape:'javascript'} {$g->name|escape:'javascript'}',
                category: '{$g->section->name|escape:'javascript'}',
                price: {$g->price},
                quantity: {$g->quantity}
            });
            {/if}
            {/foreach}

            var yaParams = {
                order_id: {$o->id},
                order_price: {$o->price},
                currency: "RUR",
                exchange_rate: 1,
                goods: ya_goods
            };
        </script>

        {* P&G promo confirmation code *}
        {if not empty($pg_goods)}
            <script>
                var CI_OrderID = '{$o->id}', CI_ItemIDs = [] , CI_ItemQtys = [], CI_ItemPrices = [];
                {foreach $pg_goods as $i}
                CI_ItemIDs.push('{$i->upc}');
                CI_ItemQtys.push('{$i->quantity}');
                CI_ItemPrices.push('{$i->price}');
                {/foreach}
            </script>
            <script src="https://cts-secure.channelintelligence.com/321604975_confirmation.js"></script>
        {/if}

        <img src="http://cnv.plus1.wapstart.ru/921d46dcb6eece3408c14d7a1de5fc80eae3d950/" width="1" height="0" alt="" />

        {* admitad retargeting *}
        <script>
            window.ad_order = "{$o->id}";    // required
            window.ad_amount = "{$o->price}";
            window.ad_products = [
                {foreach from=$order_goods item=g name=n}
                {
                    "id": "{$g->id}",
                    "number": "{$g->quantity}"
                }{if ! $smarty.foreach.n.last},{/if}
                {/foreach}
            ];
        </script>

        {include file='common/retag.tpl' level=4}

        {* admitad counter *}
        {capture assign=action_code}{if $o->data->num eq 1}2{* новый клиент*}{else}3{/if}{/capture}
        {if not empty($admitad_uid)}
            <script>
                (function (d, w) {
                    w._admitadPixel = {
                        response_type: 'img',
                        action_code: '{$action_code}',
                        campaign_code: '773f9c05f3'
                    };
                    w._admitadPositions = w._admitadPositions || [];

                    {foreach from=$order_goods item=g name=n}
                    {if $g->price > 1}
                        w._admitadPositions.push({
                            uid: '{$admitad_uid}',
                            order_id: '{$o->id}',
                            position_id: '{$smarty.foreach.n.iteration}',
                            client_id: '{$o->user_id}',
                            tariff_code: '1',
                            currency_code: 'RUB',
                            position_count: '{$smarty.foreach.n.total}',
                            price: '{$g->price}',
                            quantity: '{$g->quantity}',
                            product_id: '{$g->id}',
                            screen: '',
                            tracking: '',
                            old_customer: '{if $o->data->num gt 1}1{else}0{/if}',
                            coupon: '{if $o->coupon_id}1{else}0{/if}',
                            payment_type: 'sale'
                        });
                    {/if}
                    {/foreach}
                    var id = '_admitad-pixel';
                    if (d.getElementById(id)) { return; }
                    var s = d.createElement('script');
                    s.id = id;
                    var r = (new Date).getTime();
                    var protocol = (d.location.protocol === 'https:' ? 'https:' : 'http:');
                    s.src = protocol + '//cdn.asbmit.com/static/js/pixel.min.js?r=' + r;
                    d.head.appendChild(s);
                })(document, window)
            </script>
            <noscript>
                {foreach from=$order_goods item=g name=n}
                    {if $g->price > 1}
                        <img src="//ad.admitad.com/r?campaign_code=773f9c05f3&action_code={$action_code}&response_type=img&uid={$admitad_uid}&order_id={$o->id}&client_id={$o->user_id}&position_id={$smarty.foreach.n.iteration}&tariff_code=1&currency_code=RUB&position_count={$smarty.foreach.n.total}&price={$g->price}&quantity={$g->quantity}&product_id={$g->id}&coupon={if $o->coupon_id}1{else}0{/if}&payment_type=sale&old_customer={if $user->sum gt 0}1{else}0{/if}" width="1" height="1" alt="" />
                    {/if}
                {/foreach}
            </noscript>
        {/if}

        {* google adwords remarketing params *}
        <script>
            var google_tag_params = {
                ecomm_pagetype: 'purchase',
                ecomm_totalvalue: '{$o->price}'
            };
        </script>

        {* RR *}
        {if $config->rr_enabled}
        <script>
        rrApiOnReady.push(function() {
            try {
                {if $user->sub}
                    rrApi.setEmail('{$user->email}');
                {/if}

                var items = [];
                {foreach from=$order_goods item=g name=n}
                {if $g->price gt 0}
                items.push({
                    id: '{$g->id}',
                    price: '{$g->price}',
                    qnt: {$g->quantity}
                });
                {/if}
                {/foreach}
                rrApi.order({
                    transaction: {$o->id},
                    items: items
                });

            } catch(e) { }
        });
        </script>
        {/if}

        {* findologic *}
        {if $config->instant_search == 'findologic'}
        <script>
            {foreach from=$order_goods item=g name=n}
            _paq.push(['addEcommerceItem',
                "{$g->id}",
                "{$g->group_name|escape:'javascript'} {$g->name|escape:'javascript'}",
                ["{$g->section->name|escape:'javascript'}"],
                {$g->price},
                {$g->quantity}
            ]);
            {/foreach}

            _paq.push(['trackEcommerceOrder',
                "{$o->id}",
                {$o->price}
            ]);

           _paq.push(['trackPageView']);
        </script>
        {/if}
    {/if}

    {*if ! empty($can_poll)}
    <p class="mt" style="font-size:17px; line-height:23px;">
        <a href="/communication/oprosi" class="butt fr" id="endtest">Участвовать в&nbsp;опросе</a>
        Ваше мнение очень важно для нас. Просим Вас принять участие в&nbsp;опросе:<br />
        Это не&nbsp;займёт у&nbsp;Вас много времени, но&nbsp;очень нам поможет. Спасибо.
    </p>
    {/if*}

{/if}

<script type="text/javascript" src="//static.criteo.net/js/ld/ld.js" async="true"></script>
<script type="text/javascript">
    window.criteo_q = window.criteo_q || [];
    window.criteo_q.push(
            { event: "setAccount", account: 28691 },
            { event: "setEmail", email: "{$o->data->email}" },
            { event: "setSiteType", type: "d" },
            { event: "trackTransaction", id: "{$o->id}", item: [
                    {foreach from=$order_goods item=order_good}
                        { id: "{$order_good->id}", price: {$order_good->price}, quantity: {$order_good->quantity} },
                    {/foreach}
            ]}
    );

    window.flocktory = window.flocktory || [];
    window.flocktory.push(['postcheckout', {
        user: {
            name: "{$o->data->name} {$o->data->last_name}",
            email: "{$o->data->email}"
        },
        order: {
            id: "{$o->id}",
            price: {$o->price},
            items: [
                {foreach from=$order_goods item=order_good}
                    { id: "{$order_good->id}", title: "{$order_good->group_name} {$order_good->name}", price: {$order_good->price}, count: {$order_good->quantity}},
                {/foreach}
            ]
        }
    }]);
</script>
