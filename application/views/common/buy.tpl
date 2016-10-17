    {*if $g->is_cloth()}
        <input type="hidden" id="qty_{$g->id}" name="qty[{$g->id}]" value="1" />
        <a class="butt small i i_cart c" rel="{$g->id}"><i></i>В корзину</a>
    {else}
        {include file='common/buy.tpl' good=$g}
    {/if*}

{if $good == 'blago'} {* благотворительность обрабатываем первой *}

    <div class="incdeced">
        <a class="dec">-</a>
        <input id="qty_blago" name="qty[blago]" value="{$blago|default:0}" oldval="{$blago|default:0}" price="1" max="1000000" />
        <a class="inc">+</a>
    </div>

{elseif $good->qty eq 0 && $good != 'blago'} {* товар не в наличии *}

    <a class="do" rel="ajax" href="{Route::url('warn', ['id' => $g->id])}">Уведомить о&nbsp;поставке</a>

{elseif $good->quantity|default:0} {* мы в корзине *}

    <div class="incdeced">
        <a class="dec {if $good->quantity|default:0 lte 1} disabled{/if}">-</a>
        <input id="qty_{$good->id}" name="qty[{$good->id}]" value="{$good->quantity|default:0}" oldval="{$good->quantity|default:0}" price="{$good->price|default:1}" max="{if $good->qty eq -1}500{else}{$good->qty}{/if}" />
        <a class="inc {if $good->quantity|default:0 gte $good->qty} disabled{/if}">+</a>
    </div>

{elseif $infancy|default:0} {* мы в быстром просмотре *}

    <div class="buy wide incdeced">
        <a class="dec min-zero">-</a>
        <input id="qty_{$good->id}" name="qty[{$good->id}]" value="{if $active}1{else}0{/if}" oldval="{if $active}1{else}0{/if}" price="{$good->price|default:1}" max="{if $good->qty eq -1}500{else}{$good->qty}{/if}" />
        <a class="inc">+</a>
        <a class="c" rel="{$good->id}"></a>
    </div>

{else} {* в обычной карточке *}

    {if $good->grouped|default:1 eq 1}
        <input type="hidden" id="qty_{$good->id}" name="qty[{$good->id}]" value="{$good->quantity|default:1}" oldval="{$good->quantity|default:1}" price="{$good->price|default:1}" max="{$good->qty|default:0}" />
        <a class="butt bbutt small i i_cart c" rel="{$good->id}">В корзину</a>
    {else}
        <a data-fancybox-href="{$good->get_link(FALSE)}?ajax=1" class="butt small" rev="{$good->id}" rel="ajax" data-fancybox-type="ajax">Выбрать</a>
    {/if}

{/if}
