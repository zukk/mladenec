<a href="{Route::url('admin_add', ['model' => 'zone'])}">+ Добавить зону</a>

<form action="">
    <table id="list">
    <tr>
        <th>#</th>
        <th>название</th>
        <th>на карте</th>
	    <th>приоритет</th>
        <th>активность</th>
    </tr>

    {foreach from=$list item=i}
    <tr {cycle values='class="odd",'}>
        <td><small>{$i->id}</small></td>
        <td><a href="/od-men/{$m}/{$i->id}">{$i->name}</a></td>
        <td>
            <img src="//static-maps.yandex.ru/1.x/?l=map&pl=f:00FF00A0,{$i->poly|for_map:1}" style="width:400px;" />
        </td>
	    <td>{$i->priority}</td>
        <td><input name="active[{$i->id}]" type="checkbox" value="1" {if $i->active}checked="checked"{/if} disabled="disabled" /></td>
    </tr>
    {/foreach}
    </table>

    <!--input type="submit" name="save" value="Сохранить изменения" /-->

</form>

{$pager->html('Зоны доставки')}
