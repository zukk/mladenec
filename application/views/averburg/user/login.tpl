{assign var=rand value=rand(1,1000000)}
<form action="/user/login" method="post" class="user-login ajax" id="user-login-{$rand}" target="dummy">
	<input name="login" value="" placeholder="E-mail / Логин" class="txt" />
	<input name="password" value="" placeholder="Пароль" class="txt" type="password" />
	<label style="float: left;">
		<input type="checkbox" name="remember" /> Оставаться в системе
	</label>
	<a style="float: right; line-height: 23px;" href="/account/index.php?forgot_password=yes">Забыли пароль?</a>
	<script>
		$(function(){
			$('#user-login-{$rand} input').keyup(function(e){
				if( e.which == 13 ){
					$('#user-login-{$rand} .login-submit').click();
					return false;
				}
			});
			$('.user-login input[type=checkbox]').mladenecbox({
				size: 23
			});
		});
	</script>
	<div class="clear"></div>
	<a class="butt fl-lft login-submit" href="#" onclick="$(this).parents('form').submit();return false;">Войти</a>
</form>