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
        </div>
        <div class="units-row">
            <div class="unit-50">
                <div class="nowrap" style="overflow: hidden;">
                    <b class="fl">С: </b>
                    <input class="fl" type="date" name="from[date]" value="{$smarty.get.from.date|default:''}" />
                    <input class="fl" type="time" name="from[time]" value="{$smarty.get.from.time|default:''}" />
                </div>
            </div>

            <div class="unit-50">
                <div class="nowrap cl" style="overflow: hidden;">
                    <b class="fl">По: </b>
                    <input class="fl" type="date" name="to[date]" value="{$smarty.get.to.date|default:''}" />
                    <input class="fl" type="time" name="to[time]" value="{$smarty.get.to.time|default:''}" />
                </div>
            </div>
        </div>
        <div class="units-row">
            <div class="unit-80"><input type="submit" name="search" value="Показать" /></div>
            <div class="unit-20"><a href="{Route::url('admin_user_excel')}?{$smarty.server.QUERY_STRING}">Скачать CSV для GR (max 10000)</a></div>
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
        <th>заказы</th>
        <th>любимый</th>
        <th>рассылка</th>
        <th>создан</th>
        <th>дети</th>
        <th>источник</th>
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
        <td>{$i->sum|price} / {$i->qty}<br />={$i->avg_check()|price}<br />
            {if $i->last_order}
            <a href="{Route::url('admin_edit', ['model'=>'order', 'id' => $i->last_order])}">{$i->last_order}</a><br /><small>{$orders[$i->last_order]->created|default:''}</small>
            {/if}
        </td>
        <td>{if $i->status_id}ДА{/if}</td>
        <td>{if $i->sub}ДА{/if}</td>
        <td>{$i->created|date_format:'%F %T'}</td>
        <td>
            {if ! empty($kids[$i->id])}
                {foreach $kids[$i->id] as $child}
                <div>{if $child->sex eq 1}мальчик{else}девочка{/if} &mdash; {$child->birth|child_age}</div>
                {/foreach}
                {if $i->pregnant eq 1}
                <div>ждет малыша &mdash; {$i->get_pregnant_weeks()} неделя</div>
                {/if}
            {/if}
        </td>
        <td>
            {if $i->source}
                {assign var=source value=Txt::parse_source($i->source)}
                <strong>{$source.type}</strong>
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
