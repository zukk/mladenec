<h2>SEO шаблоны</h2>

<a href="/od-men/seotemplates/add" class="btn">Добавить</a>

<form action="" class="cb">
    {$pager->html('Шаблоны')}
    <table id="list">
        <tr>
            <th>#</th>
            <th>Наименование</th>
            <th>Правило</th>
            <th>Тип</th>
            <th>Активность</th>
            <th></th>
            <th></th>
        </tr>

        {foreach from=$list item=l}
            <tr {cycle values='class="odd",'}>
                <td><small>{$l->id}</small></td>
                <td>
                    {$l->title}
                </td>
                <td>
                    {$l->rule}
                </td>
                <td>
                    {$l->type}
                </td>
                <td>
                    {if $l->active == 1}
                        <span class="label label-green">Да</span>
                    {else}
                        <span class="label label-red">Нет</span>
                    {/if}
                </td>
                <td>
                    <a href="/od-men/seotemplates/{$l->id}">
                        Редактировать
                    </a>
                </td>
                <td>
                    <a href="/od-men/seotemplates/{$l->id}/del">
                        Удалить
                    </a>
                </td>
            </tr>
        {/foreach}
    </table>
</form>