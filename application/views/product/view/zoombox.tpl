{if empty($infancybox)}

    {if $cgood->show}

        <div class="zoombox {if $prop->img380}_380{/if}">
            <div class="zoombox_thumb">
                <div class="zoombox_magnifier"></div>
                <div class="zoombox_roll">
                    {foreach from=$good_pics key=k item=i}
                        <img itemprop="image" src="{if ! empty($i[$imgsize])}{$i[$imgsize]->get_img(0)}{else}{$i.255->get_img(0)}{/if}" {if $k gt 0}class="hide"{/if} {if $i.1600}rel="{$i.1600->get_img(0)}"{/if} alt="{$good_name} {$k}" />
                        {foreachelse}
                        <img src="http://www.mladenec-shop.ru/images/no_pic70.png" alt="{$good_name}" />
                    {/foreach}
                </div>
                <img class="zoombox_magnifier_icon" src="/i/lupa.png" />
                {if $cgood->new}<div class="product_new_marker"><img src="/i/new.png" /></div>{/if}
            </div>

            {if $cgood->old_price > 0} {* есть старая цена - покажем скидку в процентах *}
                <div class="real-discount">
                    <span>скидка {Txt::discount($cgood)}%</span>
                    {assign var=real value=$cgood->old_price-$cgood->price}
                    <span>экономия {$real|price:0} р.</span>
                </div>
            {/if}

            {if count($good_pics) gt 1 and empty( $infancybox )}
                <div class="zoombox_st">{foreach from=$good_pics key=k item=i}{if $i.255}<img {if (k%3)}class="cl"{/if} src="{$i.255->get_img(0)}" alt="{$good_name}" />{/if}{/foreach}</div>
            {/if}
        </div>

    {else}

        {include file='product/view/notshowimage.tpl'}

    {/if}

{else}

    <div class="zoombox">
        <div class="zoombox_thumb">
            <div class="zoombox_magnifier"></div>
            <div class="zoombox_roll">
                {foreach from=$good_pics key=k item=i}
                    {if $i.255}<img src="{$i.255->get_img(0)}" {if $k > 0}class="hide"{/if} {if $i.1600}rel="{$i.1600->get_img(0)}"{/if} alt="{$good_name}" />{/if}
                {/foreach}
            </div>
        </div>
    </div>

{/if}
<div class="zoombox_large"></div>