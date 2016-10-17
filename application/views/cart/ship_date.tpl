{if isset($door)} {* региональная доставка*}

    {if empty($door) and empty($terminal)}
        <em>Стоимость и&nbsp;сроки доставки сообщит менеджер после приёма заказа</em>
    {else}
        <h3>До двери</h3>
        {foreach from=$door item=v}
            <label><input type="radio" name="comment" value="D:{$v->cost}" {if ($min_param.dt eq 'D')}checked{/if} />{'день'|plural:$v->days} - <b>{$v->cost|price}</b></label>
            {foreachelse}
        {/foreach}
    {/if}
{else if isset($ozon_delivery_price)}  {* самовывоз озон *}
    <h3>Самовывоз</h3>
    <label><input type="radio" name="comment" value="T:{$ozon_delivery_price}" checked="checked"/><b>{$ozon_delivery_price|price}</b></label>
{else} {* доставка нашим курьером *}

    {assign var=ad value=$dates}
    {assign var=dates value=$ad|array_keys}
    {assign var=start value=$dates|current|strtotime}
    {assign var=finish value=$dates|end|strtotime}

    {assign var=swd value=date('N', $start)}
    {assign var=fwd value=date('N', $finish)}
    {assign var=from value=$start-($swd+6)*3600*24}
    {assign var=to value=$finish+($fwd+6)*3600*24}

    <table class="tt calendar">
        <col width="10%" />
        <col width="30%" />
        <col width="30%" />
        <col width="30%" />
        <thead>
        <tr>
            <th colspan="5">{$start|date_ru}</th>
        </tr>
        </thead>
        <tbody>
        {for $i=0 to 6}
            <tr {if $i gt 4}class="weekend"{/if}>
                {assign var=wd value=pow(2,$i)}
                <th><span>{$wd|week_day:1}</span></th>
                {for $w=0 to 3}
                    <td>
                        {assign var=date value=$from+3600*24*($w*7+$i)}
                        {assign var=cur value='Y-m-d'|date:$date}
                        {if isset($ad[$cur])}
                            <label {if $cur == $dates.0}class="a"{/if} title="{$date|date_ru}">
                                <input type="radio" name="ship_date" value="{$cur}" />
                                {'j'|date:$date}
                            </label>
                        {else}
                            <span>{'j'|date:$date}</span>
                        {/if}
                    </td>
                {/for}
            </tr>
        {/for}
        </tbody>
    </table>

{/if}