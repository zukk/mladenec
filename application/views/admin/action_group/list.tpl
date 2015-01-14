<form action="">
    <fieldset>
        <legend>Поиск акций</legend>
        <div class="units-row">
                <div class="unit-33">
                Название<br />
                <input type="text" name="name" class="width-100" value="{$smarty.get.name|default:''}" />
            </div>
            <div class="unit-33">
                Тип: <br />
                <select name="type" style="width:100%">
                    <option value="0">Все</option>
                    {foreach Model_Action::types() as $k=>$t}
                        <option value="{$k}">{$t}</option>

                    {/foreach}
                </select>
            </div>
            <div class="unit-33">
                Витрина: <br />
                <select name="vitrina">
                    {foreach from=Conf::vitrinas()  key=vid item=vname}
                        <option value="{$vid}" {if $smarty.get.vitrina|default:0 eq $vid} selected="selected"{/if}>{$vname}</option>
                    {/foreach}
                </select>
            </div>
        </div>
        <div class="units-row">
            <div class="unit-100">
                <div id="search_flags">
                    <label><i class="tr{$smarty.get.active|default:''}"></i><span>Активна</span><input type="hidden" name="active" value="{$smarty.get.active|default:''}" /></label>
                    <label><i class="tr{$smarty.get.show|default:''}"></i><span>Опубликована</span><input type="hidden" name="show" value="{$smarty.get.show|default:''}" /></label>
                    <label><i class="tr{$smarty.get.incoming_link|default:''}"></i><span>Входящая ссылка</span><input type="hidden" name="incoming_link" value="{$smarty.get.incoming_link|default:''}" /></label>
                    <label><i class="tr{$smarty.get.main|default:''}"></i><span>Показывать на главной</span><input type="hidden" name="main" value="{$smarty.get.main|default:''}" /></label>
                    <label><i class="tr{$smarty.get.show_wow|default:''}"></i><span>WOW</span><input type="hidden" name="show_wow" value="{$smarty.get.show_wow|default:''}" /></label>
                    <label><i class="tr{$smarty.get.show_actions|default:''}"></i><span>Показывать в акциях</span><input type="hidden" name="show_actions" value="{$smarty.get.show_actions|default:''}" /></label>
                </div>
            </div>
        </div>
        <div class="units-row">
            <div class="unit-25">
                <input type="submit" class="btn" name="find" value="Показать" />
            </div>
        </div>
    </fieldset>
</form>
            
{$pager->html('Акции')}
<form action="">
    <table id="list">
    <tr>
        <th>#</th>
        <th>название<br />кратко</th>
        <th>баннер</th>
        <th>Отображаемых товаров</th>
        <th>Отображается:</th>
        <th>активность<br />входящая ссылка</th>
        <th>опубликовано<br />витрина</th>
    </tr>
    {foreach from=$list item=i}
    <tr {cycle values='class="odd",'}>
        <td><small><a href="{Route::url('admin_edit',['model'=>'action_group','id'=>$i->id])}">{$i->id}</a></small></td>
        <td>
            <a href="{Route::url('admin_edit',['model'=>'action_group','id'=>$i->id])}">{$i->name}</a><br />
            {$i->preview|truncate:100}
        </td>
        <td><img src="{$i->banner}" width="300" /></td>
        <td>
            {$i->visible_goods_cnt} отображаемых,
        </td>
        <td>
            {if $i->main}на главной{/if}<br />
            {if $i->show_wow}в WOW акциях{/if}<br />
            {if $i->show_actions}в списке акций{/if}
        </td>
        <td>
            <input name="active[{$i->id}]" type="checkbox" value="1" {if $i->active}checked="checked"{/if} disabled="disabled" /><br />
            {if $i->incoming_link}да{else}нет{/if}
        </td>
	<td><input name="show[{$i->id}]" type="checkbox" value="1" {if $i->show}checked="checked"{/if} disabled="disabled" /><br />
            {Conf::vitrina_name($i->show_vitrina)}
        </td>
    </tr>
    {/foreach}
    </table>

</form>

{$pager->html('Акции')}