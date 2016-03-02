<h2>Блок ссылок</h2>
<form method="post" action="">
    <input type="text" id="" name="link" value="" class="width-80" />
    <input type="submit" value="Добавить ссылку" />
</form>

<form action="" class="cb">
    {$pager->html('Ссылки')}
    <table id="list">
        <tr>
            <th>#</th>
            <th>Ссылка</th>
            <th></th>
            <th></th>
        </tr>

        {foreach from=$list item=l}
            <tr {cycle values='class="odd",'}>
                <td><small>{$l->id}</small></td>
                <td>
                    <a href="/{$l->link}" target="_blank">
                        {$l->link}
                    </a>
                </td>
                <td>
                    <a href="/od-men/blocklinks/{$l->id}">
                        Редактировать
                    </a>
                </td><td>
                    <a href="/od-men/blocklinks/{$l->id}/del">
                        Удалить
                    </a>
                </td>
            </tr>
        {/foreach}
    </table>
</form>
