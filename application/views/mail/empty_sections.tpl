<tr align="center">
	<td>
		<table bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="712">
		<tr>
			<td>
<p>Пустые категории:</p>
<table cellpadding="5" cellspacing="0" border="1">
    <tr>
        <th>ID</th>
        <th>Название</th>
        <th>Дата, когда стала пустой</th>
        <th>Отображение на сайте</th>
    </tr>
    {foreach $sections as $sid => $section}
        <tr>
            <td align="center"><a href="http://mladenec-shop.ru/{Route::url('admin_edit', ['model'=>'section','id'=>$section->id])}">{$section->id}</a></td>
            <td align="left"><a href="http://{if $section->vitrina eq 'mladenec'}mladenec-shop.ru{else}eatmart.ru{/if}/{$section->get_link(0,0)}">{$section->name}</a></td>
            <td align="center">{$section->empty_date}</td>
            <td align="center">{if $section->active}отображается{else}скрыта{/if}</td>
        </tr>
    {/foreach}
</table>
			</td>
		</tr>
		</table>
	</td>
</tr>