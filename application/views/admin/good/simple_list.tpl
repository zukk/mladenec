<table>
{foreach from=$goods item=g}
<tr>
    <td><a href="/od-men/good/{Route::url('admin_edit',['model'=>'good','id'=>$g->id])}" target="_blank">{$g->id}</a></td>
    <td>{$g->code}</td>
    <td>{$g->code1c}</td>
    <td>{if $g->show}<span class="green">отобр.</span>{else}<span class="red">скр.</span>{/if}</td>
    <td>{$g->group_name} {$g->name}</td>
    <td>{$g->qty}&nbsp;шт.</a></td>
    <td>{$g->price}&nbsp;р.</td>
    <td><s>{$g->old_price}&nbsp;р.</s></td>
</tr>
{foreachelse}
<tr><td>Не найдено ни одного товара<td></tr>
{/foreach}
</table>

