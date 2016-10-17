<a href="{Route::url('admin_add', ['model' => 'coupon'])}">+ добавить купон</a>

{$pager->html('Купоны')}

<form action="" >
    <fieldset class="fivesixth">
        <legend>Поиск купонов</legend>

        <div class="units-row">
            <div class="unit-20"><b>Код:</b><br /><input type="text" name="name" class="width-100" value="{$smarty.get.name|default:''}" /></div>
        </div>
        <div class="units-row">
            <div class="unit-80"><input type="submit" name="search" value="Показать" /></div>
        </div>
    </fieldset>
</form>

<form action="">
    <table id="list">
    <tr>
        <th>#</th>
        <th>Код</th>
        <th>Тип</th>
        <th>Активность</th>
        <th>Условия работы</th>
        <th>Использований</th>
	    <th>Работает</th>
	    <th>Номер заказа</th>
    </tr>
    {foreach from=$list item=i}
    <tr {cycle values='class="odd",'}>
        <td>{$i->id}</td>
        <td><a href="{Route::url('admin_edit', ['model' => 'coupon', 'id' => $i->id])}">{$i->name}</a></td>
        <td>{Model_Coupon::type($i->type)}</td>
        <td>{if $i->active}
                <span class="green">вкл</span>
            {else}
                <span class="red">откл</span>
            {/if}
        </td>
        <td>
            {if $i->from}<span class="nowrap">c {$i->from}</span><br />{/if}
            {if $i->to}<span{if $i->is_expired()} class="red nowrap"{/if}>по {$i->to}</span><br />{/if}
            {if $i->min_sum}при сумме заказа от {$i->min_sum}р.<br />{/if}
            <br />
            {if $i->type eq Model_Coupon::TYPE_SUM}
                Дает скидку {$i->sum}руб
            {/if}
        </td>
        <td>{$i->uses} (использовано {$i->used})
            <br />{$i->per_user} раз на аккаунт
        </td>
	    <td>{if $i->is_usable()}
                <span class="green">да</span>
            {else}
                <span class="red">нет</span>
            {/if}
        </td>
        <td>
            {$i->order_id}
        </td>
    </tr>
    {/foreach}
    </table>

</form>

{$pager->html('Купоны')}
