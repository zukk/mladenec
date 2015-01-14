<tr align="center">
	<td>
		<table bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="712">
		<tr>
			<td width="30"></td>
			<td>

<br><h3>Здравствуйте, {$u->name}!<br></h3>
<p>На&nbsp;Ваш адрес <strong>{$u->email}</strong> было запрошено восстановление пароля.</p>
<p>Для восстановления пароля проследуйте по&nbsp;ссылке:<br>
    {capture assign=href}{$site}{Route::url('user_password')}?code={$u->checkword}{/capture}
    <a href="{$href}">{$href}</a><br /><br />

    Внимание, данная ссылка действительна один раз до&nbsp;{$time} по&nbsp;московскому времени<br /><br />

    Если Вы&nbsp;не&nbsp;запрашивали восстановления пароля, просто проигнорируйте это&nbsp;письмо
</p>
<br>

			</td>
			<td width="30"></td>
		</tr>
		</table>
	</td>
</tr>