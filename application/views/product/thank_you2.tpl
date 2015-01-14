<div id="breadcrumb">
    <a href="{Route::url('user')}">Личный кабинет</a> &rarr; <a href="{Route::url('order')}">Мои заказы</a>
</div>

<div style="padding: 20px;">
	{assign var=rand_num value=rand(1,4)}
	<div class="fl-rght"><img src="/i/averburg/cat/{$rand_num}.jpg" /></div>
	<h1 class="b black">Заказ оформлен</h1>
	<div style="font-size: 1.2em; padding: 40px;">
		<p>Номер вашего заказа <b>{$o->id}</b></p>
		<p>Итого <strong>{$o->get_total()|price}</strong>{if $o->type neq 1 and $o->delivery_type neq Model_Order::SHIP_SERVICE} (в том числе доставка {$o->price_ship|price}){else} (без учёта стоимости доставки){/if}</p>
		{if $o->delivery_type eq Model_Order::SHIP_SERVICE}{/if}

		{if $o->type eq 1}
			<p>Менеджер перезвонит
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
		{else}
			{if $o->delivery_type eq 2 and not empty( $o->data->ship_date )}
				<p>Заказ будет доставлен <b>{Txt::ru_date($o->data->ship_date)}</b></p>
			{/if}
		{/if}
		<p>Чтобы увидеть состав заказа или повторить заказ, <br />войдите в <a class="b black u" href="{Route::url('order_list')}">Личный кабинет</a></p>
		{if $o->type eq 1}
			<p style="font-size: 0.9em; color: #999;">
				В&nbsp;случае, если мы не&nbsp;сможем с&nbsp;Вами связаться в&nbsp;течение суток, просим повторно оформить заказ или перезвонить нам по&nbsp;телефону: <nobr>8&nbsp;(800)&nbsp;555-699-4</nobr>
			</p>
		{/if}
		<p class="b" style="color: #02a7c5;">Будем рады видеть Вас снова!</p>

{* тут все скрипты для всякой статистики *}

		<iframe src="{$coolstathref|default:''}" height="0" width="0" frameborder="0" class="sync"></iframe>
		{if !empty( $is_new )}
		<script>
			$(function(){
				var ya_goods = [];
				{foreach from=$order_goods item=g}
				{if $g->price gt 0}
				ya_goods.push({
					'id': '{$o->id}',
					'name': '{$g->group_name|escape:'quotes'} {$g->name|escape:'quotes'}',
					'category': '{$g->section->name}',
					'price': '{$g->price}',
					'quantity': '{$g->quantity}'
				});
				{/if}
				{/foreach}

				var yaParams = {
					order_id: '{$o->id}',
					order_price: {$o->price},
					currency: "RUR",
					exchange_rate: 1,
					goods: ya_goods
				};

				(function (d, w, c) {
					(w[c] = w[c] || []).push(function() {
						try {
							w.yaCounter11895307 = new Ya.Metrika({ id:11895307,
									webvisor:true,
									clickmap:true,
									accurateTrackBounce:true,
									ut:"noindex",params:window.yaParams||{ }});
						} catch(e) { }
					});

					var n = d.getElementsByTagName("script")[0],
						s = d.createElement("script"),
						f = function () { n.parentNode.insertBefore(s, n); };
					s.type = "text/javascript";
					s.async = true;
					s.src = (d.location.protocol == "https:" ? "https:" : "http:") + "//mc.yandex.ru/metrika/watch.js";

					if (w.opera == "[object Opera]") {
						d.addEventListener("DOMContentLoaded", f, false);
					} else { f(); }
				})(document, window, "yandex_metrika_callbacks");

				// google analitics
				(function(i,s,o,g,r,a,m){ i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
					(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
					m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
				})(window,document,'script','//www.google-analytics.com/analytics.js','ga');

				ga('require', 'ecommerce', 'ecommerce.js');
				ga('ecommerce:addTransaction', {
					'id': '{$o->id}',
					'affiliation': '{$vitrina}',
					'revenue': '{$o->price}',
					'shipping': '{$o->price_ship}'
				});
				{foreach from=$order_goods item=g}
				{if $g->price gt 0}
				ga('ecommerce:addItem', {
					'id': '{$o->id}',
					'name': '{$g->group_name|escape:'quotes'} {$g->name|escape:'quotes'}',
					'sku': '{$g->code}',
					'category': '{$g->section->name}',
					'price': '{$g->price}',
					'quantity': '{$g->quantity}'
				});
				{/if}
				{/foreach}
				ga('ecommerce:send');
			});
		</script>
		{/if}
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
	</div>
</div>
