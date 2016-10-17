<p><a href="/od-men/promo/add" class="btn btn-round">+ добавить промоакцию</a></p>

{$pager->html('Акции')}
    <table id="list">
    <tr>
        <th>#</th>
        <th>название</th>
        <th>активность</th>
    </tr>
    {foreach from=$list item=i}
        <tr {cycle values='class="odd",'}>
            <td><small>{$i->id}</small></td>
            <td><a href="{Route::url('admin_edit',['model'=>'promo','id'=>$i->id])}">{$i->name}</a></td>
            <td><input name="active[{$i->id}]" type="checkbox" value="1" {if $i->active}checked="checked"{/if} disabled="disabled" /></td>
        </tr>
    {/foreach}
    </table>

{$pager->html('Акции')}
