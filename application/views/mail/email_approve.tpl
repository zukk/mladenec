<tr align="center">
	<td>
		<table bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="712">
		<tr>
			<td width="30"></td>
			<td align="left">

<br><h3>Здравствуйте, {$user->name}!<br></h3>

{if ! empty($changed)}

    <p>
        Для завершения регистрации, пожалуйста, перейдите по&nbsp;ссылке: {HTML::anchor($user->approve_url())}<br />
        Если Вы не&nbsp;можете перейти по&nbsp;ссылке, скопируйте ее в&nbsp;адресную строку.
    </p>

{else}

    <p>Вы&nbsp;изменили адрес электронной почты.<br />
    Пожалуйста, подтвердите новый <nobr>e-mail</nobr> адрес почты. Для этого перейдите по&nbsp;ссылке:<br />
    Если Вы&nbsp;не&nbsp;можете перейти по&nbsp;ссылке, скопируйте ее&nbsp;в&nbsp;адресную строку.</p>

{/if}

{assign var=coupon value=Model_Coupon::for_user($user->id)}

{if ! empty($coupon)}

    <p>Мы дарим 200 рублей на&nbsp;Ваш первый заказ! Для получения скидки введите в&nbsp;корзине промокод:
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