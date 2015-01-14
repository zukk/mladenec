{if $good == 'blago'}

    <div class="buy">
        <input id="qty_blago" name="qty[blago]" value="{$cart->blago}" oldval="{$cart->blago}" price="1" />
        <select class="small">
                <option value="1">Шт</option>
                <option value="100">по 100 руб.</option>
        </select>
    </div>

{else}

    {if not empty($good->quantity)}{assign var=q value=$good->quantity}{/if}
    {if not empty($can_buy)}{assign var=id value=$can_buy}{else}{assign var=id value=$good->id}{/if}

    <div class="buy{if ! empty($can_buy)} wide{/if}">
        <input id="qty_{$id}" name="qty[{$id}]" value="{if not empty($active)}1{else}{$q|default:0}{/if}" oldval="{$q|default:0}" price="{$good->price}"/>
        <select class="small{if $good->pack eq 1} readonly{/if}">
    {if $good->pack gt 1}
            <option value="1">Штук</option>
            <option value="{$good->pack}">Упаковок ({$good->pack} шт.)</option>
    {else}
            <option value="1">Шт</option>
    {/if}
        </select>
    {if not empty($can_buy) and empty($no_cart)}
        <a class="c" rel="{$id}"></a>
    {/if}
    </div>

{/if}
