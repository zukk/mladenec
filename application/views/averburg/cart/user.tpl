<h3 class='h'>Для продолжения оформления заказа:</h3>
<table class='cart-login'>
	<col width='33%' />
	<col width='33%' />
	<col width='34%' />
	<thead>
		<tr>
			<th>Авторизуйтесь</th>
			<th>Зарегистрируйтесь</th>
			<th>
				{if $can_one_click}
				Купите в 1 клик
				{/if}
			</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td class="active" id="cart-login">
				<div>
				{include file='averburg/user/login.tpl'}
				</div>
			</td>
			<td>
				<div>
				{include file='averburg/user/registration.tpl'}
				</div>
			</td>
			<td>
				<div>
				{if $can_one_click}
				{include file='common/one_click.tpl'}
				{/if}
				</div>
			</td>
		</tr>
	</tbody>
</table>
<script>
	$(function(){

		var working = false;
		var loader = new Image();
		loader.src = '/i/load.gif';

		$('.cart-login form').submit(function(e){

			e.preventDefault();
			e.stopPropagation();
		});

		// вешаем обработчик сразу на две формы
		$('.login-submit,.registration-submit').click(function(e){

			e.preventDefault();
			e.stopPropagation();

			$('#tooltip').hide();

			var o = $(this), f = o.parents('form');

			if( working )
				return false;

			working = true;

			var oldLabel = o.html();

			var timeout = setTimeout(function(){
				o.after(loader);
				$(loader).css({
					margin: '10px'
				});
			},400);

			var data = f.serialize();
			data +='&mode=cart&ajax=true';

			$.ajax({
				url: f.attr('action'),
				data: data,
				success: function(data) {

					working = false;	

					clearTimeout(timeout);
					$(loader).remove();

					if( data.delivery ){
						$('#cart-delivery').replaceWith(data.delivery);
					}

					if( data.userpad ){
						$('#userpad').replaceWith(data.userpad);
					}

					if (data.error) {

						$('input.txt, input.wtxt, textarea.txt, textarea.wtxt', f).each(function () { // сообщения об ошибках на инпутах

							if (!$(this).hasClass('misc')) {
								var n = $(this).attr('name');
								if (data.error[n]) {
									$(this)
										.removeClass('ok')
										.addClass('error')
										.attr('error', data.error[n]);
								}
							}
						});
					}
				},
				error: function(){

					working = false;	

					clearTimeout(timeout);
					o.empty().append(oldLabel);
				},
				dataType: 'JSON',
				method: 'POST'
			});

			return false;
		});
	});
</script>
<script>
	$(function(){
		$('.cart-login td').click(function(){
			$('.cart-login td').removeClass('active');
			$(this).addClass('active');
		});
	});
</script>
			