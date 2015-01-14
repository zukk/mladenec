<form action="{Route::url('admin_ajax_ags',['id'=>$action_id])}{if $b|default:0}?b=1{/if}" method="post" rel="ajax" class="ajax">
    <div class="units-row">
        <div class="unit-33">
            Название<br />
            <input type="text" name="name" class="width-100" value="{$name|default:''}" />
        </div>
        <div class="unit-33">
            Категория<br />
            <select class="width-100" name="section_id" id="ags_section_id">
                <option value="">все</option>
                {foreach from=Model_Section::get_catalog(FALSE) item=s}
                    <optgroup label="{$s->name}">
                        {foreach from=$s->children item=c}
                            <option value="{$c->id}" {if $section_id|default:'' eq $c->id}selected="selected"{/if}>{$c->name}</option>
                        {/foreach}
                    </optgroup>
                {/foreach}
            </select>
        </div>
        <div class="unit-33">
            Производитель<br />
            <select class="width-100" id="ags_brand_id" name="brand_id">
                <option value="0">все</option>
                {foreach from=$brands item=b}
                    <option value="{$b->id}" {if $brand_id|default:'' eq $b->id}selected="selected"{/if}>{$b->name}</option>
                {/foreach}
            </select>
        </div>
    </div>
    <div class="units-row">
        <div class="unit-80">
            Артикулы:<br />
            <textarea name="code" class="width-100">{$code|default:''}</textarea>
        </div>
        <div class="unit-20">
            <input type="submit" id="ags_search" name="search" value="Показать" />
        </div>
    </div>

    <strong>Отобрано товаров: <span id="qty">{$pager->total}</span></strong> |
    <input type="submit" name="all" value="Добавить все отобранные" /> |
    <input type="submit" name="marked" value="Добавить только отмеченные" />

    <table id="list">
        <tr>
            <th>#</th>
            <th>Артикул</th>
            <th>группа</th>
            <th>название</th>
            <th>наличие</th>
            <th>выбрать</th>
        </tr>

        {foreach from=$list item=i}
            <tr {cycle values='class="odd",'}>
                <td><small>{$i->id}</small></td>
                <td><small>{$i->code}</small></td>
                <td>{$i->group_name}</td>
                <td><a href="/od-men/good/{$i->id}" target="_blank">{$i->name}</a></td>
                <td><small>{$i->qty|admin_qty}</small></td>
                <td><small>{$i->show|admin_show}</small></td>
                <td><input name="choice[{$i->id}]" type="checkbox" value="1" {if $choice[$i->id]}checked="checked"{/if} /></td>
            </tr>
        {/foreach}
    </table>
</form>
{$pager->html('Товары', FALSE, TRUE)}
<script type="text/javascript">
    $('#ags_section_id').change(function(){
        $('#ags_brand_id option:first').prop('selected', 'selected');
        $('#ags_search').click();
    })
</script>
    