<form action="" id="goodz" method="post">
    <table class="simple tableforms width-100">
    <tbody><tr class="labels">
        <td class="width-50">Название</td>
        <td class="width-50">Производитель</td>
    </tr>
    <tr>
        <td><input type="text" name="name" class="width-100" value="{$name|default:''}"></td>
        <td>
            <select class="width-100" name="brand_id">
                <option value="">все</option>
            {foreach from=$brands item=b}
                <option value="{$b->id}" {if $brand_id|default:'' eq $b->id}selected="selected"{/if}>{$b->name}</option>
            {/foreach}
            </select>
        </td>
    </tr>
    <tr class="labels">
        <td>Категория</td>
        <td>Артикул</td>
    </tr>
    <tr>
        <td><select class="width-100" name="section_id">
            <option value="">все</option>
        {foreach from=Model_Section::get_catalog(FALSE) item=s}
            <optgroup label="{$s.name}">
                {foreach from=$s.children item=c}
                    <option value="{$c->id}" {if $section_id|default:'' eq $c->id}selected="selected"{/if}>{$c->name}</option>
                {/foreach}
            </optgroup>
        {/foreach}
        </select>
        </td>
        <td><input type="text" name="code" class="width-100" value="{$code|default:''}"></td>
    </tr>
    </tbody></table>
    <input type="submit" name="search" value="Показать" />
    <hr />

    <strong>Отобрано товаров: <span id="qty">{$pager->total}</span></strong> |
    <input type="submit" name="all" value="Добавить все отобранные" /> |
    <input type="submit" name="marked" value="Добавить только отмеченные" />

    <table id="list">
        <tr>
            <th>#</th>
            <th>группа</th>
            <th>название</th>
            <th>выбрать</th>
        </tr>

    {foreach from=$list item=i}
        <tr {cycle values='class="odd",'}>
            <td><small>{$i->id}</small></td>
            <td>{$i->group_name}</td>
            <td><a href="/od-men/good/{$i->id}" target="_blank">{$i->name}</a></td>
            <td><input name="choice[{$i->id}]" type="checkbox" value="1" {if $choice[$i->id]}checked="checked"{/if} /></td>
        </tr>
    {/foreach}
    </table>
</form>

{$pager->html('Товары')}
