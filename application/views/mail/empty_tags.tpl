<tr align="center">
	<td>
		<table bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="712">
			<tr>
				<td>

<table border="1">
    <tr>
        <th>Теговая</th>
    </tr>
    {foreach from=$tags item=t}
    <tr>
        <td><a href="{$site}{Route::url('admin_edit', ['model' => 'tag', 'id' => $t->id])}">{$t->name}</a></td>
    </tr>
    {/foreach}
</table>

				</td>
			</tr>
		</table>
	</td>
</tr>