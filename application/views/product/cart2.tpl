{if not empty($sync)}{* удаление куки корзины на других доменах при чистке корзины *}
	{foreach from=Kohana::$hostnames item=conf key=h}
		<iframe src="//{$conf.host}/sync?clear=cart" height="0" width="0" frameborder="0" class="sync"></iframe>
	{/foreach}
{/if}
{if not empty($goods)}
<div id="cart-wrap">
<h1 class="h">Корзина</h1>
	<script src='//api-maps.yandex.ru/2.1/?lang=ru_RU'></script>
    <script type="text/javascript">
        $(document).ready(function() {
			var loader = new Image();
			loader.src="/i/load.gif";
			var cart = {
				jq: null,
				init: function(){
					
					if( !this.jq ){
						this.jq = $('#cart-wrap');
						this._initPencilator();
						this._initClearLink();
						this._initRemoveGoodLink();
						this._initRecountLink();
					}
				},
				_initRecountLink: function(){
					this.jq.find('.cart-recount-link').click(function(){
						var l = $(this);
						var timeout = setTimeout(function(){
							$(loader).insertAfter(l).css({
								'margin-left': '10px'
							});
						},400);
						$.ajax({
							url: '/personal/cart_recount.php',
							dataType: 'JSON',
							method: 'GET',
							data: {
								
							},
							success: function(data){
							
								$(loader).remove();
								clearTimeout( timeout ); 
								
								$('.cart-header').empty().append(data.header);
								$('#cart-delivery').empty().append(data.delivery);
								$.map(data.goods, function(v, id){
									$("[name='qty["+id+"]']").val(v);
									$('#cart-good-tr-'+id+' .price span').html(data.prices[id]);
									var total = data.prices[id] * v;
									$('#cart-good-tr-'+id+' .total span').html(total);
								});
								$('.cart-all-summ span').html(data.total);
								
								var o = $('.cart-recount-link');
								o.addClass('changed').html('Пересчитали');
								setTimeout(function(){
									o.removeClass('changed').html('Пересчитать');
								},2000);
							}
						});
						return false;
					});
				},
				_initPencilator: function(){
				
					this.jq.find('.pencil button').click(function(event){
						if ( 0 === $('#pencilator').size()) { // Диалог НЕ открыт
							$(event.target).pencilator();
						} else {
							$('#pencilator').show();
						}
						
						return false;
					});
				},
				_initRemoveGoodLink: function(){
					var working = false, o = this.jq, l = o.find('.cart-remove-link');
					l.each(function(){
						var q = $(this);
						q.click(function(){
						
							var id = q.attr('data-id');

							if( working )
								return false;

							working = true;

							if (confirm('Действительно удалить товар?')){

								var timeout = setTimeout(function(){
									q.replaceWith(loader);
								},400);

								$.ajax({
									url: '/personal/cart_remove_good.php',
									method: 'GET',
									data: {
										id: id
									},
									dataType: 'JSON',
									success: function(data){
										clearTimeout(timeout);

										if( data.cart ){
											o.replaceWith(data.cart);
										}
									}
								});
							}
						});
					});
				},
				_initClearLink: function(){
					var o = this.jq, l = o.find('.cart-clear-link');
					var working = false;
					l.click(function(){
						
						if( working )
							return false;
						
						working = true;
						
						if (confirm('Вы уверены, что хотите полностью очистить корзину?')){
							var timeout = setTimeout(function(){
								$(loader).insertBefore(l).css({
									'margin-right': '10px'
								});
							},400);
							$.post('/personal/cart_clear.php',function(data){
								$(loader).remove();
								clearTimeout(timeout);
								o.replaceWith(data);
							});
						}
						
						return false;
					});
					
				}
			};
			
			cart.init();
        });
    </script>
	<div class='fl-rght'>
		<a class="fr no cart-clear-link">Очистить корзину</a>
	</div>
	<div class='clear'></div>
	<div class='cart-header'>
		{include file='averburg/cart/header.tpl'}
	</div>
	{include file='averburg/cart/cart.tpl' cart=$cart goods=$goods promo=$promo presents=$presents present_goods=$present_goods  coupon_error=$coupon_error}
	<div id='cart-delivery' {if empty( $open_delivery )}style="display: none"{/if}>
		{$delivery}
	</div>
</div>
{if isset($slider_id)}
	<div class="cart-slider">
	<h2 class="h1 cb">{$slider_name}</h2>
	<span id="just" class="hide" style='right: 0; left: auto'>Товар положен в&nbsp;корзину</span>
	<div class="slider cb" rel="{Route::url('slide_set',['type'=>$cart_slide_method,'set_id'=>$slider_id])}?page={$cart_slider_page}">
		{if empty( $cart_slider_count) or $cart_slider_count > 5 }
		<i></i>
		{/if}
		{include file='common/goods.tpl' goods=$cart_slider_goods short=1}
		{if empty( $cart_slider_count) or $cart_slider_count > 5 }
		<i></i>
		{/if}
	</div>
	</div>
		<script>
			$(function(){
				$('.slider.cb .c').click(function(e){
					e.preventDefault();
					e.stopPropagation();
					
					$.ajax({
						url: '/personal/cart_recount.php',
						data: {
							'all_cart': true,
							inc: $(this).attr('rel')
						},
						dataType: 'JSON',
						method: 'POST',
						success: function(data){

							if( data.cart ){
								console.log(data);
								$('#cart-wrap').replaceWith(data.cart);
							}
							
							$('#just').fadeIn(function(){
								setTimeout(function(){
									$('#just').fadeOut();
								},2000);
							});
						}
					});
					
					return false;
				});
			});
		</script>
{/if}
{else}
<h1 class="h">Корзина</h1>
<p>Вы&nbsp;пока не&nbsp;положили в&nbsp;корзину ни&nbsp;одного товара. Оформление заказа без товаров не&nbsp;допускается.</p>
{/if}
