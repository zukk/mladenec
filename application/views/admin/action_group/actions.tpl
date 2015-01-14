<table>
    <tr>
        <th>#</th>
        <th>сроки</th>
        <th>название<br />тип</th>
        <th>товаров</th>
        <th>активность<br />витрина</th>
    </tr>
    {foreach from=$actions item=i}
    <tr {cycle values='class="odd",'}>
        <td><small>{$i->id}</small></td>
        <td>
            {if $i->from}c {$i->from}<br />{/if}
            {if $i->to > 0}<span{if $i->to < date('Y-m-d H:i:00')} class="red"{/if}>по {$i->to}</span>{/if}
        </td>
        <td>
            <a href="/od-men/action/{$i->id}">{$i->name}</a><br />
            {$i->type_name()}
        </td>
        <td>
            Товаров: {$i->goods_cnt|admin_qty}<br />
            Отображаемых: {$i->visible_goods_cnt|admin_qty}<br />
            {if $i->is_ab_type()}Б-товаров: {$i->goods_b_cnt|admin_qty}{/if}
            {if $i->is_gift_type() AND $i->presents_instock eq 1}<span class="green">подарки на складе</span>
            {elseif $i->is_gift_type() AND $i->presents_instock eq 0}<span class="red">подарки кончились</span>
            {/if}
        </td>
        <td>
            {if $i->vitrina_active eq 'all'}все витрины{else}{$i->vitrina_active}{/if}
        </td>
        <td>
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
            {/if}
        </td>
    </tr>
    {/foreach}
    </table>