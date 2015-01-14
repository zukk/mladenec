{$pager->html('Сообщения')}
<form action="">
    <table id="list">
    <tr>
        <th>#</th>
        <th>Дата<br />Время</th>
        <th>Код</th>
        <th>Модуль</th>
        <th>Действие</th>
        <th>Item ID</th>
        <th>Отправлено</th>
    </tr>
    {foreach from=$list item=i}
        <tr {cycle values='class="odd",'}>
            <td><small>{$i->id}</small></td>
            <td>{$i->date}<br />{$i->time}</td>
            <td>{$i->code}</td>
            <td>{$i->model}</td>
            <td>{$i->action}</td>
            <td>{$i->item_id}</td>
            <td>{if $i->sent}да{else}—{/if}</td>
        </tr>
    {/foreach}
    </table>
</form>

{$pager->html('Сообщения')}
