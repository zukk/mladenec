<div class="units-row">
    <div class="unit-50">
        <b>Раздел</b>
        <select id="gss_section_id" name="misc[criteria][section_id]">
            <option value="0">Все разделы</option>
            {foreach from=$sections item=s}
                <option {if $s->id eq $section_id}selected="selected"{/if} value="{$s->id}">{$s->name}</option>
                {if ! empty($s->children)}
                    {foreach from=$s->children item=sub}
                        <option {if $sub->id eq $section_id}selected="selected"{/if} value="{$sub->id}">---{$s->name}::{$sub->name}</option>
                    {/foreach}
                {/if}
            {/foreach}
        </select>
    </div>
    <div class="unit-50">
        <b>Бренд</b>
        <select id="gss_brand_id" name="misc[criteria][brand_id]">
            <option value="0">Все бренды</option>
            {foreach from=$brands item=brand}
                <option {if $brand->id eq $brand_id}selected="selected"{/if} value="{$brand->id}">{$brand->name}</option>
            {/foreach}
        </select>
    </div>
</div>
{if not empty($section_id)}
    <div class="units-row">

        <div id="filters" style="height:10em; overflow-y: scroll;"  class="unit-100">
            {foreach from=$filters|default:[] item=f}
                <ul>
                    <li><strong>{$f->name}</strong></li>
                    {foreach from=$f->values->order_by('sort', 'ASC')->order_by('name', 'DESC')->find_all() item=v}
                        <li>
                            <label><input type="checkbox" {if not empty($filter_values[$v->id])}checked="checked"{/if} name="misc[criteria][filter_values][{$v->id}]" value="{$v->id}" />{$v->name}</label>
                        </li>
                    {/foreach}
                </ul>
            {foreachelse}
                <p>Фильтры отсутствуют.</p>
            {/foreach}
        </div>
    </div>
{/if}
{if not empty($goods_total)}
    <div class="units-row">
        <div class="unit-25">Всего товаров:&nbsp;{$goods_total}</div>
        <div class="unit-25 forms-inline">страница:&nbsp;<select name="misc[gss][page]" id="gss_goods_page">
            {for $i=1 to ceil($goods_total/30)}<option {if $i eq $page}selected="selected"{/if} value="{$i}">{$i}</option>{/for}
        </select></div>
        <div class="unit-25"><input type="checkbox" name="misc[gss][active]" {if $active}checked="checked"{/if} id="gss_active" />&nbsp;Активные</div>
        <div class="unit-25"><input type="checkbox" name="misc[gss][show]" {if $show}checked="checked"{/if} id="gss_show" />&nbsp;Отображаемые</div>
    </div>
    <div class="forms-inline">, 

    </div>
    <div style="height:25em; overflow-y: scroll;" >
        <table>
            <thead>
                <tr>
                    <th></th>
                    <th>Id</th>
                    <th>Название</th>
                    <th>Цена</th>
                    <th>Остаток на&nbsp;складе</th>
                    <th>Активность<br />отображение</th>
                </tr>
            </thead>
            <tbody>
            {foreach from=$goods item=g}
                <tr>
                    <td><input type="checkbox" name="misc[goods][{$g->id}]" {if $active}checked="checked"{/if} value="1" id="gss_active" /></td>
                    <td><a href="{Route::url('admin_edit',['model'=>'good','id'=>$g->id])}">{$g->id}</a></td>
                    <td>{$g->group_name} {$g->name}</td>
                    <td>{$g->price}</td>
                    <td>{$g->qty}</td>
                    <td>
                        {if $g->active}<span class="green">акт</span>
                        {else}<span class="red">неакт</span>
                        {/if},
                        {if $g->show}<a class="green" title="Открыть на сайте" href="{Route::url('product',['translit'=>$g->translit,'group_id'=>$g->group_id,'id'=>$g->id])}" target="_blank">отобр</a>
                        {else}<span class="red">скр</span>
                        {/if}
                    </td>
                </tr>
            {/foreach}
            </tbody>
        </table>
    </div>
    <div>
        
    </div>
{/if}
