<div class="row">
<div class="twofifth">
    <form action="">
        Раздел:&nbsp;<select name="section_id">
            <option value="0">Все</option>
            {foreach from=$sections item=s}
                <option value="{$s->id}" {if $smarty.get.section_id|default:'' eq $s->id}selected="selected"{/if} {if empty($s->parent_id)} disabled="disabled"{/if}>{$s->name}</option>
            {/foreach}
        </select><br /><br />
        Бренд:&nbsp;<select name="brand_id">
            <option value="0">Все</option>
            {foreach from=$brands item=b}
                <option value="{$b->id}" {if $smarty.get.brand_id|default:'' eq $b->id}selected="selected"{/if}>{$b->name}</option>
            {/foreach}
        </select><br /><br />
        Активность:&nbsp;<select name="active">
            <option value="all">Все</option>
            <option {if $smarty.get.active|default:'' eq 'active'}selected="selected"{/if} value="active">Активные</option>
            <option {if $smarty.get.active|default:'' eq 'expired'}selected="selected"{/if} value="expired">Просроченные</option>
        </select><br /><br />
        <input type="submit" class="btn btn-round" name="submit" value="найти" />
    </form>
</div>
<div class="threefifth">
<a href="/od-men/sert/add" class="btn btn-round">+ Добавить сертификат</a>
</div>
</div>

{$pager->html('Сертификаты')}

<form action="">
    <table id="list">
    <tr>
        <th>#</th>
        <th>название</th>
        <th>картинка</th>
        <th><a href="{Route::url('admin_list', ['model' => 'sert'])}?order_by=expires">истекает</a></th>
        <th>разделы и бренды</th>
        <th>группы товаров</th>
    </tr>

    {foreach from=$list item=i}
    <tr {cycle values='class="odd",'}>
        <td><small>{$i->id}</small></td>
        <td><a href="/od-men/{$m}/{$i->id}">{$i->name}</a></td>
        <td>{if $i->preview}{$i->small->get_img()}{/if}</td>
        <td>{if $i->expires gt 0}<span {if strtotime($i->expires) < time()}class="red"{/if}>{$i->expires}</span>{/if}</td>
        <td>
            {foreach from=$i->get_binded() item=bnd}
                {if $bnd['brand_id']}{$bnd['brand_name']}{else}все бренды{/if}, {if $bnd['section_id']}{$bnd['section_name']}{else}все разделы{/if}<br />
            {/foreach}
        </td>
        <td>
            {foreach from=$i->groups->distinct('id')->find_all() item=group}
                {$group->name}<br />
            {/foreach}
        </td>
    </tr>
    {/foreach}
    </table>

    <!--input type="submit" name="save" value="Сохранить изменения" /-->

</form>

{$pager->html('Сертификаты')}
