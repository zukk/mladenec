<div id="good_main">
    <h1>{capture assign=good_name}{$group->name} {$goods[{$prop->id}]->name}{/capture}{$good_name}</h1>

    <div id="etalage">
    {foreach from=$goods[{$prop->id}]->get_images() key=k item=i}
        {if $k eq 0}
            <a href="{$goods[{$prop->id}]->get_link(0)}"><img class="buy_thumb" src="{$i.255->get_img(0)}" alt="{$good_name}" /></a>
        {else}
            <img class="buy_small_thumb" src="{$i.70->get_img(0)}" alt="{$good_name}" />
        {/if}
    {foreachelse}
        <a href="{$goods[{$prop->id}]->get_link(0)}"><img src="http://mladenec-shop.ru/images/no_pic70.png" alt="{$good_name}" /></a>
    {/foreach}
    </div>

    <form action="/product/add" method="post">
        <div id="view">
            <table id="goods">
            <tbody>
            {assign var=buyable value=0}
            {foreach $goods as $g}
            {assign var=active value=$g->id==$prop->id}
            <tr {if $active}class="a"{/if}>
                <td class="vt"><a name="{$g->id}"></a></td>
                <td class="qty">{$g|qty:0}</td>
                <td class="name"><a href="{$g->get_link(0)}?ajax=1" rev="{$g->group_id}" rel="buy">{$g->name}</a>{if $g->qty == -1}<small class="wait">Доставка в течение 2-х дней</small>{/if}</td>

                <td class="price">
                    {if $g->old_price gt 0}<del>{$g->old_price|price}</del>{/if}
                    <span {if $g->old_price gt 0}class="no"{/if}>{$g->price|price}</span>
                    <abbr>{$price[$g->id]|price}</abbr>
                </td>
                <td>
                    {if $g->qty != 0}
                        {include file='common/buy.tpl' good=$g can_buy=$g->id}
                        {assign var=buyable value=$g->price}
                        {if $active}{assign var=total value=$g->price}{/if}
                    {else}
                        <a class="do appendhash" rel="ajax" data-url='/product/warn/' href="#{$g->id}">Уведомить о&nbsp;поставке</a>
                    {/if}
                </td>
            </tr>
            {/foreach}
            </tbody>
            </table>
        </div>

        {if $buyable}
            <table class="totals">
                <tfoot class="a"><tr>
                    <th>Итого: <span id="total">{if not empty($total)}1{/if}</span></th>
                    <td id="pricetotal">{$total|default:0} <small>р.</small></td>
                    <td><a class="butt small i i_cart c"><i></i>В корзину</a></td>
                </tr></tfoot>
            </table>
        {/if}

    </form>

    <div style="background:#fff; clear:left;">
        <div class="txt cb tabs">
            <div>
                <a class="active t">Описание</a>
                {if ! empty($prop->spoiler_title)}<a class="t">{$prop->spoiler_title}</a>{/if}
                {if ! empty($prop->spoiler2_title)}<a class="t">{$prop->spoiler2_title}</a>{/if}
                {if ! empty($prop->spoiler3_title)}<a class="t">{$prop->spoiler3_title}</a>{/if}
                <small class="r">
                    артикул: {$goods[{$prop->id}]->code}
                    {if $goods[{$prop->id}]->code1c neq $goods[{$prop->id}]->code}
                        <br />артикул Младенец: {$goods[{$prop->id}]->code1c}
                    {/if}
                </small>
            </div>
            <div class="tab-content active">
                <div class="txt">
                    {$prop->desc}
                    <br /><br /><a href="{$g->get_link(0)}">Подробнее</a>

                </div>
            </div>
            {if ! empty($prop->spoiler_title)}
                <div class="tab-content">
                    <div class="txt spoiler oh">
                        {$prop->spoiler}
                    </div>
                </div>
            {/if}
            {if ! empty($prop->spoiler2_title)}
                <div class="tab-content">
                    <div class="txt spoiler oh">
                        {$prop->spoiler2}
                    </div>
                </div>
            {/if}
            {if ! empty($prop->spoiler3_title)}
                <div class="tab-content">
                    <div class="txt spoiler oh">
                        {$prop->spoiler3}
                    </div>
                </div>
            {/if}
        </div>
    </div>
</div>

