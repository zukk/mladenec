<form action="" class="forms forms-columnar">
    <fieldset class="fivesixth">
        <legend>Поиск пользователей</legend>

        <div class="units-row">
            <div class="unit-20"><b>Id:</b><br /><input type="text" name="id" class="width-100" value="{$smarty.get.id|default:''}" /></div>
            <div class="unit-40"><b>Email:</b><br /><input type="text" name="email" class="width-100" value="{$smarty.get.email|default:''}" /></div>
            <div class="unit-20"><b>Телефон:</b><br /><input type="tel" name="phone" class="width-100" value="{$smarty.get.phone|default:''}" /></div>
            <div class="unit-20"><b>Имя:</b><br /><input type="text" name="name" class="width-100" value="{$smarty.get.name|default:''}" /></div>
        </div>
        <div class="units-row">
            <div class="unit-80" id="search_flags">
                <label><i class="tr{$smarty.get.sub|default:''}"></i><span>подписан на рассылку</span><input type="hidden" name="sub" value="{$smarty.get.sub|default:''}" /></label>
                <label><i class="tr{$smarty.get.lk|default:''}"></i><span>любимый</span><input type="hidden" name="lk" value="{$smarty.get.lk|default:''}" /></label>
                <label><input type="checkbox" name="admin" {if $smarty.get.admin|default:''}checked="checked"{/if} value="1" /> <span>Администратор</span></label>                
                <label><i class="tr{$smarty.get.childs|default:''}"></i><span>с детьми</span><input type="hidden" name="childs" value="{$smarty.get.childs|default:''}" /></label>
            </div>
            <div class="unit-20"><a href="{Route::url('admin_user_excel')}?{$smarty.server.QUERY_STRING}">Скачать в Excel</a></div>
        </div>
        <div class="units-row">
            <div class="unit-33 datepicker">
                <nobr><b>С:</b><br />{html_select_date time=$from|default:null field_array=from field_order=DMY all_empty='' start_year="-2" end_year="+1"} <input type="text" name="from[Time_Hour]" value="{$smarty.get.from['Time_Hour']|default:'00'}" style="width: 35px; display: inline" /> : <input type="text" name="from[Time_Minute]" value="{$smarty.get.from['Time_Minute']|default:'00'}" style="width: 35px; display: inline" /></nobr>
            </div>
            <div class="unit-33 datepicker">
                <nobr><b>По:</b><br />{html_select_date time=$to|default:null field_array=to field_order=DMY all_empty='' end_year="+1"} <input type="text" name="to[Time_Hour]" value="{$smarty.get.to['Time_Hour']|default:'00'}" style="width: 35px; display: inline" /> : <input type="text" name="to[Time_Minute]" value="{$smarty.get.to['Time_Minute']|default:'00'}" style="width: 35px; display: inline" /></nobr>
            </div>
            <div class="unit-33"><input type="submit" name="search" class="btn" value="Показать" /></div>   
        </div>
    </fieldset>
</form>

<form action="">
{$pager->html('Пользователи')}
    <table id="list">
    <tr>
        <th>#</th>
        <th>email</th>
        <th>телефоны</th>
        <th>имя</th>
        <th>сумма</th>
        <th>любимый</th>
        <th>рассылка</th>
        <th>создан</th>
        <th>дети</th>
        {if ! empty($access)}
        <th>админ доступ</th>
        {/if}
    </tr>

    {foreach from=$list item=i}
    <tr {cycle values='class="odd",'}>
        <td><small>{$i->id}</small></td>
        <td>{$i->email}</td>
        <td>{$i->phone}<br />{$i->phone2}</td>
        <td><a href="/od-men/{$m}/{$i->id}">{$i->name}</a></td>
        <td>{$i->sum|price}</td>
        <td>{if $i->status_id}ДА{/if}</td>
        <td>{if $i->sub}ДА{/if}</td>
        <td>{$i->created|date_format:'%F %T'}</td>
        <td>
            {foreach $i->kids->find_all()->as_array('id') as $child}
            <div>{if $child->sex eq 1}мальчик{else}девочка{/if} &mdash; {$child->birth|child_age}</div>
            {/foreach}
            {if $i->pregnant eq 1}
            <div>ждет малыша &mdash; {$i->get_pregnant_weeks()} неделя</div>
            {/if}
        </td>
        {if ! empty($access)}
            <td>
            {if ! empty($access[$i->id])}
                {foreach from=$access[$i->id] item=module}{Kohana::message('admin',$module)}<br />{/foreach}
            {else}
                &mdash;
            {/if}
            </td>
        {/if}
    </tr>
    {/foreach}
    </table>
</form>

{$pager->html('Пользователи')}
