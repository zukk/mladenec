{assign var=rand value=rand(1,1000000)}
<form action="/registration" method="post" class="user-registration ajax" id='user-registration-{$rand}' {if !empty($reg)}style="display:block"{/if} autocomplete="off">
	<input name="email" value="" placeholder="E-mail (эл.почта)" class="txt" />
	<input id="reg_pass" name="password" value="" placeholder="Пароль" class="txt" type="password" error="Пароль должен содержать не&nbsp;меньше 6&nbsp;символов.<br />
Совет: не&nbsp;создавайте слишком простых паролей. Комбинируйте буквы и&nbsp;цифры."/>
	<input id="reg_pass2" name="password2" value="" placeholder="Подтверждение пароля" class="txt" type="password" />
	<input name="name" value="" placeholder="Имя" class="txt" error="Данное имя будет использовано при&nbsp;обращении к&nbsp;вам и&nbsp;в&nbsp;подписях отзывов о&nbsp;сайте и&nbsp;товарах." />
	<input name="phone" value="" placeholder="Мобильный телефон" class="txt" error="Этот телефон будет использован для&nbsp;связи с&nbsp;вами и&nbsp;подтверждения заказа" />
	<script>
		$(document).ready(function() {
			$('#user-registration-{$rand} input').keyup(function(e){
				if( e.which == 13 ){
					$('#user-registration-{$rand} .login-submit').click();
					return false;
				}
			});
			$('#user-registration-{$rand} input[name=phone]').mask('+7(999)999-99-99');
		});
	</script>
	<a class="butt small fl-lft registration-submit" href="#" onclick="$(this).parents('form').submit();return false;">Зарегистрироваться</a>
	<br clear='all' />
	{if not empty($register_poll)}
		<p>{$register_poll->name}</p>
		<div class="regpoll"><img src="/i/load.gif" alt="" /></div>
	{/if}
</form>
