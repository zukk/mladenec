<tr align="center">
	<td>
		<table bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="712">
		<tr>
			<td width="30"></td>
			<td align="left">

				<br><h3>Здравствуйте, {$user->name}!<br></h3>

				<p>
					Администрацией сайта для Вас был создан новый пароль пользователя <a href="{$site}">Младенец.РУ</a></a>
				</p>

				<p>Ваши регистрационные данные:<br><br>
					Логин: <strong>{$user->email}</strong><br>
					Пароль: <strong>{$passwd}</strong><br><br>
				</p>
				<p>
					Рекомендуем Вам <a href="{$site}{Route::url('user_password')}">сменить пароль</a> пользователя на&nbsp;удобный Вам&nbsp;сразу после захода на&nbsp;сайт.
				</p>
				<br>
			</td>
			<td width="30"></td>
		</tr>
		</table>
	</td>
</tr>