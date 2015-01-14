<tr align="center">
	<td>
		<table bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="712">
			<tr>
				<td>

<table border="1">
    <tr>
        <th>Товар</th><th>Отзыв</th><th>редактировать</th>
    </tr>
    {foreach from=$gr item=i}
    <tr>
        <td><a href="{$site}{$i->good->get_link(0)}">{$i->good->group_name} {$i->good->name}</a></td>
        <td><strong>{$i->name}</strong><br />{$i->text|truncate:200}</td>
        <td><a href="{$site}{Route::url('admin_edit', ['model' => 'good_review', 'id' => $i->id])}">в админку</a></td>
    </tr>
    {/foreach}
</table>

				</td>
			</tr>
		</table>
	</td>
</tr>