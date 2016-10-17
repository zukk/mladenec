<tr align="center">
	<td>
		<table bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="712">
		<tr>
			<td>

            <p>Пользователь {$f['name']} оставил обратную связь:<br /><br />
                {$f['text']|nl2br}
            </p>

                <p><b>Контактные данные пользователя:</b><br /><br />
                    Имя: {$f['name']}<br />
                    Email: {$f['email']}<br />
                    {if $f['user_id'] AND $f['phone']}
                        Телефон: {$f['phone']|default:'&mdash;'}<br />
                        Профиль в админке: <a href="http://mladenec-shop.ru{Route::url('admin_edit',['model'=>'user', 'id'=>$f['user_id']])}">{$f['name']}</a><br />
                    {else}
                        Пользователь не зарегистрирован.<br />
                    {/if}
                </p>
            </td>
		</tr>
		</table>
	</td>
</tr>