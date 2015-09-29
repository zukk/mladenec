<form action="{Route::url('register')}" method="post" class="user-registration ajax" id="user-registration" {if not empty($reg)}style="display:block"{/if}>
	<input name="email" value="" placeholder="E-mail (эл.почта)" class="txt" />
	<input id="reg_pass" name="password" value="" placeholder="Пароль" class="txt" type="password" error="Пароль должен содержать не&nbsp;меньше 6&nbsp;символов.<br />
Совет: не&nbsp;создавайте слишком простых паролей. Комбинируйте буквы и&nbsp;цифры."/>
	<input id="reg_pass2" name="password2" value="" placeholder="Подтверждение пароля" class="txt" type="password" />
	<input name="name" placeholder="Имя" class="txt" error="Данное имя будет использовано при&nbsp;обращении к&nbsp;вам и&nbsp;в&nbsp;подписях отзывов о&nbsp;сайте и&nbsp;товарах." />
	<input type="tel" name="phone" placeholder="+7(___)___-__-__" class="txt" error="Этот телефон будет использован для&nbsp;связи с&nbsp;вами и&nbsp;подтверждения заказа" />
	<input type="submit" class="butt small fl registration-submit" value="Зарегистрироваться" />
	<br clear="all" />
	{if not empty($register_poll)}{* откуда вы о нас узнали *}
		<p>{$register_poll->name}</p>
		<div class="regpoll"><i class="load"></i></div>
	{/if}
</form>
