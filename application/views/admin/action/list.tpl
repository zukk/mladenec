<a href="/od-men/action/add">+ добавить акцию</a>
<form action="" class="forms forms-inline">
    <fieldset class="fivesixth">
    <legend>Поиск акций</legend>
    <div id="search_flags">
        <label><i class="tr{$smarty.get.allowed|default:''}"></i><span>Разрешена</span><input type="hidden" name="allowed" value="{$smarty.get.allowed|default:''}" /></label>
        <label><i class="tr{$smarty.get.active|default:''}"></i><span>Активна</span><input type="hidden" name="active" value="{$smarty.get.active|default:''}" /></label>
        <label><i class="tr{$smarty.get.show|default:''}"></i><span>Опубликована</span><input type="hidden" name="show" value="{$smarty.get.show|default:''}" /></label>
        <label><i class="tr{$smarty.get.presents_instock|default:''}"></i><span>Есть подарки на складе</span><input type="hidden" name="presents_instock" value="{$smarty.get.presents_instock|default:''}" /></label>
        <br />
        <br />
        <label><i class="tr{$smarty.get.main|default:''}"></i><span>Показывать на главной</span><input type="hidden" name="main" value="{$smarty.get.main|default:''}" /></label>
        <label><i class="tr{$smarty.get.show_wow|default:''}"></i><span>WOW</span><input type="hidden" name="show_wow" value="{$smarty.get.show_wow|default:''}" /></label>
        <label><i class="tr{$smarty.get.show_actions|default:''}"></i><span>Показывать в акциях</span><input type="hidden" name="show_actions" value="{$smarty.get.show_actions|default:''}" /></label>
    </div>
    <table class="simple tableforms width-100">
        <tbody><tr class="labels">
            <td class="width-50">Название</td>
            <td>&nbsp;</td>
            <td>Витрина</td>
        </tr>
        <tr>
            <td><input type="text" name="name" class="width-100" value="{$smarty.get.name|default:''}"></td>
            <td style="text-align:right;">
                Опубликована на:</td>
            <td>
                <select name="vitrina_show">
                    <option value="">Не важно</option>
                    <option value="all" {if $smarty.get.vitrina_show|default:'' eq 'all'} selected="selected"{/if}>На всех витринах</option>
                    {foreach from=$vitrinas key=dcode item=ddata}
                        {if empty($ddata['is_hidden'])}
                            <option value="{$dcode}" {if $smarty.get.vitrina_show|default:'' eq $dcode} selected="selected"{/if}>{$ddata['host']}</option>
                        {/if}
                    {/foreach}
                </select> 
            </td>
        </tr>
        <tr>
            <td><input type="submit" name="search" class="btn" value="Показать" /></td>
            <td style="text-align:right;">Активна на: </td>
            <td><select name="vitrina_active">
                    <option value="">Не важно</option>
                    <option value="all" {if $smarty.get.vitrina_active|default:'' eq 'all'} selected="selected"{/if}>На всех витринах</option>
                    {foreach from=$vitrinas  key=dcode item=ddata}
                        <option value="{$dcode}" {if $smarty.get.vitrina_active|default:'' eq $dcode} selected="selected"{/if}>{$ddata['host']}</option>
                    {/foreach}
                </select></td>
        </tr>
        </tbody></table>
        
    </fieldset>
</form>
            <div class="cb"></div>
{$pager->html('Акции')}
<form action="">
    <table id="list">
    <tr>
        <th>#</th>
        <th>Сроки</th>
        <th>Название</th>
        <th>Кратко</th>
        <th>Входящая ссылка</th>
        <th>Отображаемых товаров</th>
        <th>Отображается:</th>
        <th>активность<br />витрина</th>
	<th>опубликовано<br />витрина</th>
    </tr>
    {foreach from=$list item=i}
    <tr {cycle values='class="odd",'}>
        <td><small>{$i->id}</small></td>
        <td>c {$i->from}<br /><span{if $i->to > 0 AND $i->to < date('Y-m-d H:i:00')} class="red"{/if}>по {$i->to}</span></td>
        <td>
            <a href="/od-men/action/{$i->id}">{$i->name}</a><br />
            Тип:
            {$i->type_name()}<br />
            {if $i->parent_id gt 0}
            <a href="{Route::url('admin_edit',['model'=>'action','id'=>$i->parent_id])}">основная акция &rArr;</a><br />
            {/if}
        </td>
        <td>{$i->preview|truncate:100}</td>
        <td>{if $i->incoming_link}да{else}нет{/if}</td>
        <td>
            {$i->visible_goods} отображаемых,<br />
            {if $i->is_gift_type() AND $i->presents_instock eq 1}<span class="green">подарки на складе</span>
            {elseif $i->is_gift_type() AND $i->presents_instock eq 0}<span class="red">подарки кончились</span>
            {/if}
        </td>
        <td>
            {if $i->main}на главной{/if}<br />
            {if $i->show_wow}в WOW акциях{/if}<br />
            {if $i->show_actions}в списке акций{/if}
        </td>
        <td>
            <input name="active[{$i->id}]" type="checkbox" value="1" {if $i->active}checked="checked"{/if} disabled="disabled" /><br />
            {if $i->is_activatable()}
                {if $i->active}<span class="green">Работает</span>
                {else}<span class="blue">Запускается</span>
                {/if}
            {else}
                {if $i->active}<span class="blue">Останавливается</span>
                {else}<span class="red">В архиве</span>
                {/if}<br />
                {if $i->allowed}<span class="green">Разрешена админом</span>{else}<span class="red">Запрещена админом</span>{/if}<br />
                {if ! $i->is_begun()}<span class="red">Еще не началась</span><br />{/if}
                {if $i->is_expired()}<span class="red">Срок истёк</span><br />{/if}
                {if not ( $i->visible_goods OR $i->total) }<span class="red" title="К акции прикреплены товары, 
                      которые должны отображаться. Но ни один из таких товаров не может быть 
                      отображен">Закончились товары</span><br />{/if}
            {/if}<br />
            {if $i->vitrina_active eq 'all'}все витрины{else}{$i->vitrina_active}{/if}
        </td>
	<td><input name="show[{$i->id}]" type="checkbox" value="1" {if $i->show}checked="checked"{/if} disabled="disabled" /><br />
            {if $i->vitrina_show eq 'all'}все витрины{else}{$i->vitrina_show}{/if}
        </td>
    </tr>
    {/foreach}
    </table>

</form>

{$pager->html('Акции')}
