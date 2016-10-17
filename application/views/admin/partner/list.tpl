<form action="">
    <table id="list">
    <tr>
        <th>#</th>
        <th>дата</th>
        <th>компания</th>
        <th>заявка</th>
        <th>ответ</th>
        <th>ответ отправлен</th>
    </tr>

    {foreach from=$list item=i}
    <tr {cycle values='class="odd",'}>
        <td><small>{$i->id}</small></td>
        <td>{$i->created}</td>
        <td><a href="/od-men/{$m}/{$i->id}">{$i->name}</a></td>
        <td>{$i->text|truncate:100}</td>
        <td>{$i->answer|truncate:100}</td>
        <td>{if $i->answer_sent}{date('d m Y, H:m', $i->answer_sent)}{/if}</td>
    </tr>
    {/foreach}
    </table>

</form>

{$pager->html('Заявки')}
