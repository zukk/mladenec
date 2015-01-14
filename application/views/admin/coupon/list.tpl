<a href="{Route::url('admin_add', ['model' => 'coupon'])}">+ добавить купон</a>

{$pager->html('Купоны')}

<form action="">
    <table id="list">
    <tr>
        <th>#</th>
        <th>Код</th>
        <th>Сроки</th>
        <th>Активность</th>
        <th>Сумма</th>
	    <th>Сумма заказа, от</th>
        <th>Использований</th>
	    <th>На человека</th>
        <th>Работает</th>
    </tr>
    {foreach from=$list item=i}
    <tr {cycle values='class="odd",'}>
        <td>{$i->id}</td>
        <td><a href="{Route::url('admin_edit', ['model' => 'coupon', 'id' => $i->id])}">{$i->name}</a></td>
        <td>c {$i->from}<br /><span{if $i->is_expired()} class="red"{/if}>по {$i->to}</span></td>
        <td>{if $i->active}
                <span class="green">вкл</span>
            {else}
                <span class="red">откл</span>
            {/if}
        </td>
        <td>{$i->sum}</td>
	    <td>{$i->min_sum}</td>
        <td>{$i->uses} (уже использовано {$i->used})</td>
	    <td>{$i->per_user}</td>
        <td>{if $i->is_usable()}
                <span class="green">да</span>
            {else}
                <span class="red">нет</span>
            {/if}
        </td>
    </tr>
    {/foreach}
    </table>

</form>

{$pager->html('Купоны')}
