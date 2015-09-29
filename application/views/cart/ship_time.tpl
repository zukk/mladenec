{if isset($terminal)} {* региональная доставка (до терминала) *}

    {if not empty($terminal)}
        <h3>До терминала</h3>

        {foreach from=$terminal item=v}
            <label><input type="radio" name="comment" value="T:{$v->cost}" {if ($min_param.dt eq 'T')}checked{/if} />{'день'|plural:$v->days} - <b>{$v->cost|price}</b></label>
        {/foreach}

        <em>
            {$closest.address}.<br />
            {assign var=worktime value=$closest.worktime|json_decode}
            {if $worktime}
            {foreach from=$worktime item=wt}
                {if not empty($wt->weekDays)}
                {$wt->weekDays}: {$wt->workTime};
                {/if}
            {/foreach}
            {/if}
        </em>

    {/if}

{else} {* доставка нашим курьером - часики *}

    {if $zamkad}
        <p>Адрес доставки находится вне центральной зоны доставки.<br />
            За МКаД дополнительно взимаетcя плата {Model_Order::PRICE_KM}р./км<br />
            {if not empty($smarty.get.mkad)} что составит <b>+{Model_Order::PRICE_KM*$smarty.get.mkad}&nbsp;р.</b>{/if}</p>
    {/if}

    <div id="times">
        {foreach from=$times item=t key=k name=k}
            <label class="l"><input type="radio" name="ship_time" data-grad="{Txt::grad($t.name)}" value="{$k}" {if $smarty.foreach.k.first}checked{/if} rel="{$t.price}" />{$t.name} - <b>{$t.price|price}</b></label>
        {/foreach}
    </div>

    <div class="fl" id="watch"><b>12</b><b>15</b><b>18</b><b>21</b></div>

    {if ! empty($friday_mkad)}
        <em>В&nbsp;связи с&nbsp;увеличением плотности движения в&nbsp;сторону области по&nbsp;пятницам на&nbsp;летний период доставка
            за&nbsp;МКАД возможна только с&nbsp;общим интервалом: с&nbsp;14 до&nbsp;22 часов, просим с&nbsp;пониманием отнестись
            к&nbsp;данным ограничениям!</em>
    {else}
        <em>Просим Вас не&nbsp;ограничивать время доставки без&nbsp;крайней необходимости!</em>
    {/if}
{/if}


