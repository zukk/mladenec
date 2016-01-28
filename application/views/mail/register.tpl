<tr align="center">
	<td>
		<table bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="712">
		<tr>
			<td width="30"></td>
			<td align="left">

<br><h3>Здравствуйте, {$user->name}!<br></h3>

<p>
    Благодарим вас за&nbsp;регистрацию в&nbsp;интернет-магазине <a href="{$site}">Младенец.РУ</a>
</p>

<p>
    Для завершения регистрации, пожалуйста, перейдите по&nbsp;ссылке: {HTML::anchor($user->approve_url())}<br />
    Если Вы не&nbsp;можете перейти по&nbsp;ссылке, скопируйте ее в&nbsp;адресную строку.
</p>

<p>Ваши регистрационные данные:<br><br>
    Логин: <strong>{$user->email}</strong><br>
    Пароль: <strong>{$passwd}</strong><br><br>
</p>

{if ! empty($coupon)}
<p>Мы дарим 200 рублей на&nbsp;Ваш первый заказ! Для получения скидки введите в&nbsp;корзине промокод: <br />
    <strong>{$coupon->name}</strong><br />
    Промокод дает скидку только после завершения регистрации и&nbsp;подтверждения согласия на&nbsp;получение рассылок.
</p>
{/if}
			</td>
			<td width="30"></td>
		</tr>
		</table>
	</td>
</tr>