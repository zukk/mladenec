<h1>Набор товаров {if $id|default:false}#{$i->id} &bdquo;{$i->name}&ldquo;{else} - создание{/if}</h1>
<p><a  rel="ajax" data-fancybox-type="ajax" href="{Route::url('admin_ajax_list',['model'=>'set'])}">Вернуться к списку</a></p>
{if $ok|default:false}<p><b>Сохранено!</b></p>{/if}
<form id='section-form' action="{if $id|default:false}{Route::url('admin_ajax_form',['model'=>'set','id'=>$i->id])}{else}{Route::url('admin_ajax_add',['model'=>'set'])}{/if}" rel="ajax" method="post" class="ajax forms forms-columnar" enctype="multipart/form-data">
    <p>
        <label for="name">Название</label>
        <input type="text" id="name" {if $i->lock}readonly="readonly"{/if} name="name" value="{$i->name}" size="50" />
    </p>
    <p>
        <label for="autoapply">Применять автоматически</label>
        <input type="checkbox" id="autoapply" {if $i->lock}readonly="readonly"{/if} name="autoapply" onclick="return confirm('Точно автоматически?')" value="1" {if $i->autoapply}checked="checked"{/if} />
    </p>
    <p>
        <label for="lock">Запретить изменения</label>
        <input type="checkbox" id="lock_flag" name="lock" onclick="return confirm('Уверен?')" value="1" {if $i->lock}checked="checked"{/if} />
    </p>
    {if $id|default:false}
        <fieldset>
            <legend>Условия:</legend>

            <p>
                <label for="misc[vitrina]">Витрины:</label>
                <select  {if $i->lock}readonly="readonly"{/if} name="misc[vitrina]">
                    <option value="{Conf::VITRINA_ALL}" {if 'all' eq $rules_vitrina}selected="selected"{/if}>Все витрины</option>
                    <option value="{Conf::VITRINA_MLADENEC}" {if Conf::VITRINA_MLADENEC eq $rules_vitrina}selected="selected"{/if}>Только Младенец</option>
                    <option value="{Conf::VITRINA_EATMART}"  {if Conf::VITRINA_EATMART eq $rules_vitrina}selected="selected"{/if}>Только Eatmart</option>
                </select>
            </p>
            <p class="forms-inline">
                <label for="misc[section_action]">Раздел:</label>
                {if not $i->lock}
                    <select name="misc[section_action]">
                        <option value="add">Добавить</option>
                        <option value="except">Исключить</option>
                        <option value="clear">Удалить из условий</option>
                    </select>
                    <select name="misc[section_id]">
                        <option> </option>
                        {foreach $top_sections as $s}
                            <optgroup>
                                <option disabled="disabled">{if $s->vitrina eq 'mladenec'}Mladenec{else}Eatmart{/if} - {$s->name}</option>
                                {foreach  ORM::factory('section')->where('parent_id', '=', $s->id)->where('active', '=', 1)->order_by('sort','ASC')->find_all()->as_array() as $sc}
                                    <option value="{$sc->id}">{if $s->vitrina eq 'mladenec'}Mladenec{else}Eatmart{/if} - {$s->name} - {$sc->name}</option>
                                {/foreach}
                            </optgroup>
                        {/foreach}
                    </select><br />
                {/if}
                <span style="descr"><b>Включенные разделы:</b><br />
                    {foreach $rules_section as $rs}
                        <a href="{Route::url('admin_edit',['model'=>'section','id'=>$rs->id])}" target="_blank" title="{if $rs->vitrina eq 'mladenec'}Mladenec{else}Eatmart{/if}, {$sections[$rs->parent_id]->name}">
                            {$rs->name}
                        </a>, 
                    {foreachelse}нет {/foreach}<br />
                </span>
                <span style="descr"><b>Исключенные разделы:</b><br />
                    {foreach $rules_section_except as $rse}<a href="{Route::url('admin_edit',['model'=>'section','id'=>$rse->id])}" target="_blank">{$rse->name}</a>, {foreachelse}нет {/foreach}<br />
                </span>
            </p>
            <p class="forms-inline">
                <label for="misc[brand_action]">Бренд:</label>
                {if not $i->lock}
                <select name="misc[brand_action]">
                    <option value="add">Добавить</option>
                    <option value="except">Исключить</option>
                    <option value="clear">Удалить из условий</option>
                </select>
                <select name="misc[brand_id]">
                    <option> </option>
                    {foreach $brands as $b}
                        <option value="{$b->id}">{$b->name}</option>
                    {/foreach}
                </select><br />
                {/if}
                <span style="descr"><b>Включенные бренды:</b><br />
                    {foreach $rules_brand as $rb}<a href="{Route::url('admin_edit',['model'=>'brand','id'=>$rb->id])}" target="_blank">{$rb->name}</a>, {foreachelse}нет {/foreach}<br />
                </span>
                <span style="descr"><b>Исключенные бренды:</b><br />
                    {foreach $rules_brand_except as $rbe}<a href="{Route::url('admin_edit',['model'=>'brand','id'=>$rbe->id])}" target="_blank">{$rbe->name}</a>, {foreachelse}нет {/foreach}<br />
                </span>
            </p>
            <p class="forms-inline">
                <label for="misc[min_price]">Цена:</label>
                От: <input type="numeric" name="misc[min_price]" value="{$rule_min_price}" />
                До: <input type="numeric" name="misc[max_price]" value="{$rule_max_price}" />
            </p>
            <p class="forms-inline">
                <label for="misc[filters]">Фильтры:</label>
                {if not empty($filters)}
                    <select name="misc[filtervals_action]">
                        <option value="add">Добавить</option>
                        <option value="except">Исключить</option>
                        <option value="clear">Удалить из условий</option>
                    </select><br />
                    {foreach $filters as $f}
                        <select name="misc[filtervals][]" multiple="multiple" size="10">
                            <option disabled="disabled">{$f->name}</option>
                            {if not empty($filtervals[$f->id])}
                                {foreach $filtervals[$f->id] as $fv}
                                    <option value="{$fv->id}" class="{if not empty($rules_filtervals[$fv->id])}green{/if} {if not empty($rules_filtervals_except[$fv->id])}red{/if}">{if not empty($rules_filtervals[$fv->id])}+{/if}{if not empty($rules_filtervals_except[$fv->id])}-{/if} {$fv->name}</option>
                                {/foreach}
                            {/if}
                        </select>
                    {foreachelse}
                        <span class="descr">Фильтры не прикреплены.</span>
                    {/foreach}
                {else}
                    <span class="descr">Фильтры можно добавлять только когда установлены условия на соответствующие разделы.</span>
                {/if}
            </p>
            <p>
                <label for="misc[by_ids_action]">По ID/артикулу:</label>
                {if not $i->lock}
                    <select name="misc[by_ids_action]">
                        <option value="">Без изменений</option>
                        <optgroup>
                            <option value="add_by_code">Добавить по артикулам</option>
                            <option value="add_by_id">Добавить по ID</option>
                        </optgroup>
                        <optgroup>
                            <option value="ex_by_code">Исключить по артикулам</option>
                            <option value="ex_by_id">Исключить по ID</option>
                        </optgroup>
                        <optgroup>
                            <option value="clear_by_code">Удалить из условий по артикулам</option>
                            <option value="clear_by_id">Удалить из условий по ID</option>
                        </optgroup>
                    </select>
                    <textarea id="misc_by_ids_vals" name="misc[by_ids_vals]" cols="80" rows="5" style="height:50px;"></textarea>
                {/if}
                <span style="descr"><b>Включенные ID:</b><br />
                    {foreach $rules_ids as $id}<a href="{Route::url('admin_edit',['model'=>'good','id'=>$id])}" target="_blank">{$id}</a>, {foreachelse}нет {/foreach}<br />
                </span>
                <span style="descr"><b>Исключенные ID:</b><br />
                    {foreach $rules_except_ids as $eid}<a href="{Route::url('admin_edit',['model'=>'good','id'=>$eid])}" target="_blank">{$eid}</a>, {foreachelse}нет {/foreach}<br />
                </span>
            </p>
        </fieldset>
    {/if}
    <p class="forms-inline">
        <input name="edit" value="Сохранить" type="submit" class="btn ok btn-green"/>
    </p>
</form>
{if $goods}
    {$goods_pager->html('Товары', FALSE, TRUE)}
    <table class="small">
        <tr>
            <th>ID</th>
            <th>артикул</th>
            <th>название</th>
            <th>остаток</th>
            <th>цена</th>
        </tr>
        {foreach from=$goods item=g}
            <tr>
                <td><a href="/od-men/good/{$g->id}" target="_blank">{$g->id}</a></td>
                <td><a href="/od-men/good/{$g->id}" target="_blank">{$g->code}</a></td>
                <td>{$g->group_name} {$g->name}</td>
                <td>{$g->qty}&nbsp;шт.</td>
                <td>{$g->price}&nbsp;р.</td>
            </tr>
        {/foreach}
    </table>
    {$goods_pager->html('Товары', FALSE, TRUE)}
{else}
    <p>Пока не выбрано ни одного товара</p>
{/if}