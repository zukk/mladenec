<h1 class="h">Корзина</h1>

{if not empty($sync)}{* удаление куки корзины на других доменах при чистке корзины *}
	{foreach from=Kohana::$hostnames item=conf key=h}
		<iframe src="//{$conf.host}/sync?clear=cart" height="0" width="0" frameborder="0" class="sync"></iframe>
	{/foreach}
{/if}

{if not empty($goods)}
    <script type="text/javascript">
        $(document).ready(function() {
            $(document).on('click', 'table#goods td.pencil button', function(event) {
                if ( 0 === $('#pencilator').size()) { // Диалог НЕ открыт
                    $(event.target).pencilator();
                } else {
                    $('#pencilator').show();
                }
                event.preventDefault();
                event.stopPropagation();
            });
        });
    </script>
    <div id="cart_box">
		<a class="fr no" onclick="if (confirm('Вы уверены, что хотите полностью очистить корзину?')) $('#clean').submit()" style="font-size: 1.2em;">Очистить корзину</a>
        {include file='product/view/cart.tpl' cart=$cart goods=$goods promo=$promo presents=$presents present_goods=$present_goods}
		<div id='cart-delivery'>
			{$delivery}
		</div>
		<script>
			var updateCartDelivery;
			$(function(){
				updateCartDelivery = function(){
					$.post('/cart/delivery.php', function(data) {
						if( data.redirect || data.ok ){
							updateCartDelivery();
						}
					});
				};
			});
		</script>
		
    </div>
    {*if isset($slider_id)}
        <h2 class="h1 cb">{$slider_name}</h2>
        <div class="slider cb" rel="{Route::url('slide_set',['type'=>$cart_slide_method,'set_id'=>$slider_id])}?page={$cart_slider_page}">
			{if empty( $cart_slider_count) or $cart_slider_count > 5 }
            <i></i>
			{/if}
            {include file='common/goods.tpl' goods=$cart_slider_goods short=1}
			{if empty( $cart_slider_count) or $cart_slider_count > 5 }
            <i></i>
			{/if}
        </div>
    {/if*}
{else}
<p>Вы&nbsp;пока не&nbsp;положили в&nbsp;корзину ни&nbsp;одного товара. Оформление заказа без товаров не&nbsp;допускается.</p>
{/if}
