<div class="units-row">
    <div class="unit-50 centered">
        <a class="btn" href="/od-men/spam/add">+ Добавить рассылку</a>
        <a class="btn" href="/od-men/spam_stat">Статистика пользователей по доменам</a>
    </div>
</div>

<form action="">
    <table id="list">
    <tr>
        <th>#</th>
        <th>название</th>
        <th>статус</th>
    </tr>

    {foreach from=$list item=i}
    <tr {cycle values='class="odd",'}>
        <td><small>{$i->id}</small></td>
        <td><a href="/od-men/{$m}/{$i->id}">{$i->name}</a></td>
        <td>{$i->status()}</td>
    </tr>
    {/foreach}
    </table>

</form>

{$pager->html('Рассылки')}
