<div class="units-row width-60 unit-centered">
    <div class="unit-70">  
        {$pager->html('Наборы товаров')}
    </div>
    <div class="unit-30">
        <a href="{Route::url('admin_add',['model'=>'good_set'])}" class="btn">Добавить набор</a>
    </div>
</div>
<table id="list">
    <tr>
        <th>#</th>
        <th>Название</th>
        <th>В корзине</th>
        <th>Активность</th>
    </tr>
    {foreach from=$list item=i}
    <tr {cycle values='class="odd",'}>
        <td><small>{$i->id}</small></td>
        <td><a href="{Route::url('admin_edit',['model'=>'good_set','id'=>$i->id])}">{$i->name}</a></td>
        <td>{if $i->cart}да{/if}</a></td>
        <td>{if $i->active}активен{else}нет{/if}</a></td>
    </tr>
    {/foreach}
    </table>
{$pager->html('Наборы товаров')}