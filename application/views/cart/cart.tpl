<div id="cart-wrap">
<h1 class="h">Корзина</h1>

{if not empty($sync)}{* удаление куки корзины на других доменах при чистке корзины *}
	{foreach from=Kohana::$hostnames item=conf key=h}
		<iframe src="//{$conf.host}/sync?clear=cart" height="0" width="0" frameborder="0" class="sync"></iframe>
	{/foreach}
{/if}

{if not empty($goods)}
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
						$.get('/personal/cart_recount.php', function(data){
							var data = $(data);
							cart.jq.replaceWith(data);
							$(loader).remove();
							clearTimeout( timeout ); 
							var o = data.find('.cart-recount-link');
							o.addClass('changed').html('Пересчитали');
							setTimeout(function(){
								o.removeClass('changed').html('Пересчитать');
							},2000);
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

										if( data.success ){
											$('#cart-good-tr-' + id).remove();
										}
										else if( data.cart ){
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
	{include file='averburg/cart/cart.tpl' cart=$cart goods=$goods promo=$promo presents=$presents present_goods=$present_goods}
	<div id='cart-delivery'>
		{$delivery}
	</div>
{else}
<p>Вы&nbsp;пока не&nbsp;положили в&nbsp;корзину ни&nbsp;одного товара. Оформление заказа без товаров не&nbsp;допускается.</p>
{/if}
</div>