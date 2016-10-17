<form action="{Route::url('login')}" method="post" class="user-login ajax" id="user-login" target="dummy">
	<input type="text" name="login" value="" placeholder="E-mail / Логин" class="txt" />
	<input name="password" value="" placeholder="Пароль" class="txt" type="password" />
	<label class="fl cb">
		<input type="checkbox" name="remember" /> Оставаться в системе
	</label>

	<a class="fr" href="{Route::url('user_forgot')}">Забыли<br />пароль?</a>
    <input type="submit" class="butt cb fl login-submit" value="Войти" />

    <div class="clear"></div>

    {Ulogin::factory()->render()}
</form>
<iframe name="dummy" style="display:none;"></iframe>
