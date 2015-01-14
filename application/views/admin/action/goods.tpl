<table>
{foreach from=$goods item=g}
<tr>
    <td><a href="/od-men/good/{$g->id}" target="_blank">{$g->id}</a></td>
    <td>{$g->code}</td>
    <td>{$g->group_name} {$g->name}</td>
    <td>{$g->price|price}</td>
    <td>{$g->qty|admin_qty}</td>
    <td>{$g->show|admin_show}</td>
    <td><a href="{Route::url('admin_unbind',['model' => 'action', 'id' => $action_id, 'alias'=>'goods', 'far_key'=>$g->id])}" class="btn btn-small btn-red trdel">удалить</a></td>
</tr>
{foreachelse}
    <tr><td>Пока не выбрано ни одного товара<td></tr>
{/foreach}
</table>

