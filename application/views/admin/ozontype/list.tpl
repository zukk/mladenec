<form action="">
    <table id="list">
        <tr>
            <th>#</th>
            <th>тип</th>
            <th>template_id</th>
        </tr>

        {foreach from=$list item=i}
            <tr {cycle values='class="odd",'}>
                <td><small>{$i->id}</small></td>
                <td><a href="{Route::url('admin_edit', ['model' => 'ozontype', 'id' => $i->id])}">{$i->name}</a><br /><small>{$i->path_name}</small></td>
                <td><small>{$i->template_id}</small></td>
            </tr>
        {/foreach}
    </table>

</form>

{$pager->html('Категории OZON')}
