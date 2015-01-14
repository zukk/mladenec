{if empty( $rand )}
	{assign var=rand value=rand(1,1000000)}
{/if}
{if ! empty($coupon_error)}
<p><span class="discount">При активации промо-кода произошла ошибка:</span>
	{$coupon_error}
</p>

{/if}
{if !empty($cart->coupon)}
	<table class='cart-coupon-table fl-lft'>
		<tr>
			<td>Промо-акция <b>{$cart->coupon.name}</b></td>
		<td class="price">-{$cart->coupon.sum|price}</td>
		<td>1</td>
		<td class="price">-{$cart->coupon.sum|price}</td>
		<td>
			<a id="cart-coupon-remove-{$rand}" style="border-bottom: 1px dotted red; color: red;" href='#'>не использовать</a>
			<script>
				$(function(){
					var working = false;
					$('#cart-coupon-remove-{$rand}').click(function(){

						if( working )
							return false;

						working = true;

						$.post('/cart/coupon.php', {
								remove: true
							}, function(data){

							working = false;

							$('#cart-wrap').replaceWith(data);
						});

						return false;
					});
				});
			</script>
		</td>
		</tr>
	</table>
{else}
<div class='cart-prm fl-lft'>
	<abbr abbr="Если Вы хотите получить скидку, введите код купона"><b>ПРОМОКОД:</b></abbr>
	<input id='cart-coupon-v-{$rand}' type='text' name="coupon" value="{$cart->coupon.name|default:''}" />
	<a href='#' class='undln' id='cart-coupon-{$rand}'>Применить</a>
	<script>
		$(function(){
			var working = false;
			$('#cart-coupon-v-{$rand}').keyup(function(e){
				if( e.which == 13 ){
					$('#cart-coupon-{$rand}').click();
					return false;
				}
			}).blur(function(){
				$('#cart-coupon-{$rand}').click();
			});
			$('#cart-coupon-{$rand}').click(function(){
				
				if( working )
					return false;
				
				working = true;
				$.post('/cart/coupon.php', {
						'coupon': $('#cart-coupon-v-{$rand}').val(),
						'open_delivery': $('#cart-delivery').css('display') == 'block'
					}, function(data){
						
					working = false;
					
					$('#cart-wrap').replaceWith(data);
				});
				
				return false;
			});
		});
	</script>
	{if $cart->discount gt 0}Ваша экономия <strong class="discount">{$cart->discount|price}</strong>{/if}
</div>
{/if}
