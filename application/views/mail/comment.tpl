<tr align="center">
	<td>
		<table bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="712">
		<tr>
			<td>

<ul>
    <li>
        <strong>Пользователь:</strong>
        {if $i->user_id}
            <a href="/od-men/user/{$i->user_id}">{$i->user_name}</a>
        {else}
            {$i->user_name}
        {/if}
        {$theme->email} {$theme->phone} {if ($theme->check)}[{$theme->check}]{/if}
    </li>
    <li>
        <strong>Кому:</strong>
        {$i->get_to($i->to)}
    </li>
    <li>
        <strong>Название:</strong>
        {$i->name}
    </li>
    <li>
        <strong>Текст:</strong><br />
        {$i->text|nl2br}
    </li>
    <li>
        <a href="{$site}{Route::url('admin_edit', ['model' => 'comment_theme', 'id' => $theme->id])}">в админку</a>
    </li>
</ul>
			</td>
		</tr>
		</table>
	</td>
</tr>