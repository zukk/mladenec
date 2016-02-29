{if $cgood->show}

    <div id="good-price" itemprop="offers" itemscope itemtype="http://schema.org/Offer">

        <div class="share42init fr" data-path="/i/"></div>
        <script src="/j/share42.js"></script>

        <strong {if $cgood->old_price gt 0}class="no"{/if}>
            {$current_price|price}
            <meta itemprop="priceCurrency" content="RUR" />
            <meta itemprop="price" content="{$current_price}" />
            {if $cgood->qty != 0}
                <link itemprop="availability" href="http://schema.org/InStock"/>
            {else}
                <link itemprop="availability" href="http://schema.org/OutOfStock"/>
            {/if}
        </strong>
        {if $cgood->old_price gt 0}<del>{$cgood->old_price|price}</del>{/if}

        {if $cgood->sborkable()}
            <a href="/delivery#sborka_tovara"><img src="/i/sborka_icon.png" alt="Бесплатная сборка" title="Бесплатная сборка" /></a>
        {/if}

        {if not $cgood->is_advert_hidden() and not empty($good_action[$cgood->id])}
            {include file="common/action.tpl" action=$good_action[$cgood->id] g=$cgood} {* акции по товару *}
        {/if}

        <br />
        {if ($default_price neq $lovely_price)}
            {if $lovely}
                <abbr abbr="Ваш статус - Любимый клиент">{$default_price|price} обычная цена</abbr>
            {else}
                <abbr>{$lovely_price} для любимого клиента</abbr>
            {/if}
        {/if}
    </div>

{/if}
