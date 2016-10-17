<form action="" class="forms forms-inline">
    <fieldset class="fivesixth">
        <legend>Поиск отзывов о товарах</legend>
        <div class="units-row">
            <div class="unit-50">
                <label>От: 
                    {html_select_date time=$from|default:NULL field_array=from field_order=DMY all_empty='' start_year='-1'}
                </label>
            </div>
            <div class="unit-50">
                <label>до: 
                    {html_select_date time=$to|default:NULL field_array=to field_order=DMY all_empty='' start_year='-1'}
                </label>
            </div>
        </div>
        <div class="units-row">
            <div class="unit-40" id="search_flags">
                <label><i class="tr{$smarty.get.active|default:''}"></i><span>Активность</span><input type="hidden" name="active" value="{$smarty.get.active|default:''}" /></label>
                <label><i class="tr{$smarty.get.hide|default:''}"></i><span>Плохой (не публиковать)</span><input type="hidden" name="hide" value="{$smarty.get.hide|default:''}" /></label>
            </div>
            <div class="unit-40">
				<label>
					ID пользователя: <input type="text" name="user_id" value="{$smarty.get.user_id|default:''}" />
				</label>
			</div>
            <div class="unit-20"><input type="submit" name="search" class="btn" value="Показать" />
            </div>
        </div>
    </fieldset>
</form>


<form action="">
    <table id="list">
    <tr>
        <th>#</th>
        <th>время</th>
        <th>автор</th>
        <th>товар</th>
        <th>отзыв</th>
        <th>активность</th>
        <th>плохой</th>
    </tr>

    {foreach from=$list item=i}
    <tr {cycle values='class="odd",'}>
        <td><small>{$i->id}</small></td>
        <td><small>{$i->time|date_format:'\<\n\o\b\r\>d-m-y\<\/\n\o\b\r\> h:s'}</small></td>
        <td><a href="?user_id={$i->author->id}">{$i->author->name}</a></td>
        <td>{$i->good->group_name} {$i->good->name}&nbsp;<a target="_blank" href="{$i->good->get_link(0)}">&gt;&gt;&gt;</a></td>
        <td><a href="/od-men/{$m}/{$i->id}">{$i->name}</a><br />
            {$i->text|truncate:100}</td>
        <td><input name="active[{$i->id}]" type="checkbox" value="1" {if $i->active}checked="checked"{/if} disabled="disabled" /></td>
        <td><input name="hide[{$i->id}]" type="checkbox" value="1" {if $i->hide}checked="checked"{/if} disabled="disabled" /></td>
    </tr>
    {/foreach}
    </table>

</form>

{$pager->html('Отзывы')}
