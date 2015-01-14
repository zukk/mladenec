<form action="" class="forms forms-columnar">
    <fieldset class="fivesixth">
        <legend>Поиск пользователей</legend>

        <div class="units-row">
            <div class="unit-33"><b>Id:</b><br /><input type="text" name="id" class="width-100" value="{$smarty.get.id|default:''}" /></div>
            <div class="unit-33"><b>Email:</b><br /><input type="text" name="email" class="width-100" value="{$smarty.get.email|default:''}" /></div>
            <div class="unit-33"><b>Имя:</b><br /><input type="text" name="name" class="width-100" value="{$smarty.get.name|default:''}" /></div>
        </div>
        <div class="units-row">
            <div class="unit-50" id="search_flags">
                <label><i class="tr{$smarty.get.sub|default:''}"></i><span>подписан на рассылку</span><input type="hidden" name="sub" value="{$smarty.get.sub|default:''}" /></label>
                <label><input type="checkbox" name="admin" {if $smarty.get.admin|default:''}checked="checked"{/if} value="1" /> <span>Администратор</span></label>
            </div>
            <div class="unit-50"><input type="submit" name="search" class="btn" value="Показать" /></div>
        </div>
    </fieldset>
</form>

<form action="">
{$pager->html('Пользователи')}
    <table id="list">
    <tr>
        <th>#</th>
        <th>email</th>
        <th>имя</th>
        <th>сумма</th>
        <th>любимый</th>
        <th>рассылка</th>
        <th>создан</th>
        {if ! empty($access)}
        <th>админ доступ</th>
        {/if}
    </tr>

    {foreach from=$list item=i}
    <tr {cycle values='class="odd",'}>
        <td><small>{$i->id}</small></td>
        <td>{$i->email}</td>
        <td><a href="/od-men/{$m}/{$i->id}">{$i->name}</a></td>
        <td>{$i->sum|price}</td>
        <td>{if $i->status_id}ДА{/if}</td>
        <td>{if $i->sub}ДА{/if}</td>
        <td>{$i->created|date_format:'%F %T'}</td>
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
