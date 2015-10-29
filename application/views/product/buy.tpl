{* Быстрый просмотр (всплывающие карточки товара)

@param $goods

*}
<div id="good_main">
    <h1>{capture assign=good_name}{$group->name|escape:html} {$goods[{$prop->id}]->name|escape:html}{/capture}{$good_name}</h1>

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

    <form action="{Route::url('product_add')}" method="post">
        <div id="view">
            <table id="goods">
            <tbody>
            {assign var=buyable value=0}
            {foreach $goods as $g}
            {assign var=active value=$g->id==$prop->id}
            <tr {if $active}class="a"{/if}>
                <td class="vt"><a name="{$g->id}"></a></td>
                <td class="qty">{$g|qty:0}</td>
                <td class="name"><a href="{$g->get_link(0)}?ajax=1" rev="{$g->group_id}" rel="buy">{$g->name}</a></td>

                <td class="price">
					{if $active}
						{include file="google/detail.tpl" good=$g}
					{/if}

                    {if $g->old_price gt 0}<del>{$g->old_price|price}</del>{/if}
                    <span {if $g->old_price gt 0}class="no"{/if}>{$g->price|price}</span>

                    <abbr>{$price[$g->id]|price}</abbr>
                </td>
                <td>
                    {if $g->qty != 0}
                        {include file='common/buy.tpl' good=$g infancy=1 active=$active}
                        {assign var=buyable value=$g->price}
                        {if $active}{assign var=total value=$g->price}{/if}
                    {else}
                        <a class="do" rel="ajax" href="{Route::url('warn', ['id' => {$g->id}])}">Уведомить о&nbsp;поставке</a>
                    {/if}
                </td>
            </tr>
            {/foreach}
            </tbody>

            {if $buyable}
            <tfoot class="totals a">
            <tr>
                <th colspan="3">Итого: <span id="total">{if not empty($total)}1{/if}</span></th>
                <td id="pricetotal">{$total|default:0|price}</td>
                <td><a class="butt small i i_cart c" rel="{$cgood->id}"><i></i>В корзину</a></td>
            </tr>
            </tfoot>
            {/if}
            </table>
        </div>

    </form>

    <div id="good_desc">
        {include file='product/view/info.tpl'}
        <a href="{$cgood->get_link(FALSE)}">Подробнее</a>
    </div>

</div>

