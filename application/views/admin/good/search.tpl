<form action="" id="goodz" method="post">
    <input type="hidden" name="rand" value="{$smarty.request.rand}" />
    Витрина:
    <select name="vitrina">
        {foreach from=Kohana::$hostnames item=config key=k}
            <option value="{$k}" {if $vitrina|default:'' eq $config['host']}selected="selected"{/if}>{$config['host']}</option>
        {/foreach}
    </select>

    <div class="units-row">
        <div class="unit-50">
            <select class="" name="section_id[]" multiple>
                <option value="0">все категории</option>
            {foreach from=$sections item=s}
                <optgroup label="{$s->name}">
                    {foreach from=$s->children item=c}
                        <option value="{$c->id}" {if in_array($c->id, $section_id|default:[])}selected="selected"{/if}>{$c->name}</option>
                    {/foreach}
                </optgroup>
            {/foreach}
            </select>
        </div>

        <div class="unit-50">
            <select class="width-50" name="brand_id[]" multiple>
                <option value="0">все производители</option>
            {foreach from=$brands item=b}
                <option value="{$b->id}" {if in_array($b->id, $brand_id|default:[])}selected="selected"{/if}>{$b->name}</option>
            {/foreach}
            </select>
        </div>
    </div>

    <input type="text" name="name" class="width-100" value="{$name|default:''}" placeholder="Название" />
    <textarea name="id1c" class="width-100" placeholder="Коды 1с, можно много">{$id1c|default:[]|implode:"\n"}</textarea>
    <input type="submit" name="search" value="Отобрать товары" rel="{$smarty.request.rand}" />

    <hr />

    <strong>Отобрано товаров: <span id="qty">{$pager->total}</span></strong> |
    <input type="submit" name="all" value="Добавить все отобранные" rel="{$smarty.request.rand}" /> |
    <input type="submit" name="marked" value="Добавить только отмеченные" rel="{$smarty.request.rand}" />

    <table id="list">
        <tr>
            <th>#</th>
            <th>код1с</th>
            <th>группа</th>
            <th>название</th>
            <th>на складе</th>
            <th>цена</th>
            <th>отобр.</th>
            <th>выбрать</th>
        </tr>

    {foreach from=$list item=i}
        <tr {cycle values='class="odd",'}>
            <td><small>{$i->id}</small></td>
            <td><small>{$i->id1c}</small></td>
            <td>{$i->group_name}</td>
            <td><a href="{Route::url('admin_edit', ['model' => 'good', 'id' => $i->id])}" target="_blank">{$i->name}</a></td>
            <td>{$i->qty|admin_qty}</td>
            <td>{$i->price|price}</td>
            <td>{$i->show|admin_show}</td>
            <td><input name="choice[{$i->id}]" type="checkbox" value="1" {if $choice[$i->id]}checked="checked"{/if} /></td>
        </tr>
    {/foreach}
    </table>
</form>

{$pager->html('Товары')}
