<tr align="center">
	<td>
		<table bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="712">
		<tr>
			<td width="30"></td>
			<td align="left">

<br><h3>Здравствуйте, {$user->name}!<br></h3>

{assign var=$coupon value=Model_Coupon::for_user($user->id)}

{if ! empty($changed)}
    <p>Ваш email был изменен на {$user->email}.</p>

{/if}

{if $coupon}
<p>Ваш купон на&nbsp;скидку при покупке: <strong>{$coupon->name}</strong><br />
    Подтвердите email чтобы иметь возможность использовать этот купон.
</p>
{/if}

<p>Подтвердить {if ! empty($changed)}изменение{/if} email: {HTML::anchor(Route::url('email_approve', ['email' => $user->email, 'md5' => md5(Cookie::$salt . $user->email)]))}</p>

			</td>
			<td width="30"></td>
		</tr>
		</table>
	</td>
</tr>