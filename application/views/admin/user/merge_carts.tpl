<p>
	<b>{$user->name}</b>, во время прошлого визита в корзине оставалось несколько товаров. Они представлены в таблице.
</p>
<p>
	Добавить эти товары в корзину?
</p>
<div id="goods-wraper" style="overflow-y: auto; margin-bottom: 40px; padding-right: 20px;">
<table id="goods" style="width: 200px;">
	<tr>
		<th></th>
		<th>Товар</th>
		<th style="white-space: nowrap">Кол-во</th>
	</tr>
{foreach from=$old_goods key=good_id item=good}
	<tr>
		<td>
			<img src="{$good->prop->get_img(70)}" />
		</td> 
		<td style="white-space: nowrap">
			{$good->group_name} {$good->name} 
		</td>
		<td align="center">
			{$old_goods_counts[$good_id]}
		</td>
	</tr>
{/foreach}
</table>
</div>
<div id="merge-carts-buttons" style="position: absolute; bottom: 0; background: #f9f9f9; width: 100%;">
	<div style="width: 350px; position: relative; left: 50%; margin-left: -175px;">
		<a id="cart-merge-apply" href="javascript:void(0)" class="butt" style="float: left">Добавить</a>
		<a href="javascript:$.fancybox.close()" style="float: right; padding: 11px 27px; display: block;">Нет, спасибо</a>
	</div>
</div>
<script>
	$(function(){
		var loader = new Image();
		loader.src='/i/load.gif';
		
		$('#goods-wraper').height($(window).height()-200);
		$(window).resize(function(){
			$('#goods-wraper').height($(window).height()-200);
		});
		$('#cart-merge-apply').click(function(){
		
			var o = $(this);
			
			if(o.hasClass('disabled') )
				return false;
			
			o.addClass('disabled');
			
			var timeout = setTimeout(function(){
				o.after(loader);
				$(loader).css({
					padding: '8px',
					display: 'block',
					float: 'left',
					width: '20px',
					height: '20px'
				});
			},300);
			
			$.post('/cart_merge', {
				old_session: ['0'
					{foreach from=$old_sessions item=sess_id}
						,'{$sess_id}'
					{/foreach}
				]
			}, function(data){
				$(loader).remove();
				clearTimeout(timeout);
				
				if( data == 'ok' ){
					$.fancybox.close();
				}
			});
			return false;
		});
	});
</script>

