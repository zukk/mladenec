<a href="/od-men/article/add">+ Добавить статью</a>

<form action="">
    <table id="list">
        <tr>
            <th>#</th>
            <th>название</th>
        </tr>

    {foreach from=$list item=i}
        <tr {cycle values='class="odd",'}>
            <td><small>{$i->id}</small></td>
            <td><a href="/od-men/{$m}/{$i->id}">{$i->name}</a></td>
        </tr>
    {/foreach}
    </table>

    <!--input type="submit" name="save" value="Сохранить изменения" /-->

</form>

{$pager->html('Статьи')}
