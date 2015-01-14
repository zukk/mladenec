<tr align="center">
	<td>
		<table bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="712">
		<tr>
			<td>

                <p>Пользователь {$name} прислал на работу на конкурс:<br /><br />
                    {$text|nl2br}
                </p>

                <p><b>Контактные данные пользователя:</b><br /><br />
                    Имя: {$name}<br />
                    Email: {$email}<br />
                    {if $user_id}
                        Профиль в админке: <a href="http://mladenec-shop.ru{Route::url('admin_edit',['model'=>'user', 'id'=>$user_id])}">{$name}</a><br />
                    {else}
                        Пользователь не зарегистрирован.<br />
                    {/if}
                </p>
            </td>
		</tr>
		</table>
	</td>
</tr>