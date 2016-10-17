<a href="/od-men/new/add">+ Добавить новость</a>

<form action="">
    <table id="list">
    <tr>
        <th>#</th>
        <th>дата</th>
        <th>название</th>
        <th>активность</th>
    </tr>

    {foreach from=$list item=i}
    <tr {cycle values='class="odd",'}>
        <td><small>{$i->id}</small></td>
        <td>{$i->date}</td>
        <td><a href="/od-men/{$m}/{$i->id}">{$i->name}</a></td>
        <td><input name="active[{$i->id}]" type="checkbox" value="1" {if $i->active}checked="checked"{/if} disabled="disabled" /></td>
    </tr>
    {/foreach}
    </table>

    <!--input type="submit" name="save" value="Сохранить изменения" /-->

</form>

{$pager->html('Новости')}
