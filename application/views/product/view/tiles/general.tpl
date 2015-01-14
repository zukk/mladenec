    <div class="good_tiles">
    {foreach from=$goods item=g name=g}
        <div class="g{if $smarty.foreach.g.iteration mod 4 eq 1} ml0{/if}{if $smarty.foreach.g.iteration mod 4 eq 3} ml11{/if}">
            <span class="stars"><span style="width:{$g->rating*20}%"></span></span>
            
            {if $g->grouped}
                {assign var=link value=$g->get_link(0)}
                {capture assign=name}{$g->group_name|escape:'html'} {if $g->grouped eq 1}{$g->name|escape:'html'}{/if}{/capture}

                <a class="review" title="{'отзыв'|plural:$g->review_qty}" href="{$link}#reviews">{$g->review_qty}</a>
                <a href="{$link}" title="{$name}">{$images[$g->id].255->get_img()}</a>
                <a href="{$link}">{$name}</a>
                <a href="{$link}?ajax=1" class="butt small fastview" rev="{$g->id}" rel="ajax" data-fancybox-type="ajax">Быстрый просмотр</a>    

                <div class="price">
                    {if $g->same_price and $g->old_price gt 0}<del>{$g->old_price|price}</del>{/if}
                    <strong>{if ! $g->same_price}от {/if}{$g->price|price}</strong>
                    <abbr>{$price[$g->id]|price}</abbr>
                    {if $g->grouped eq 1}{$g->qty|qty:0}{/if}
                </div>
                <div class="ico">
                    {if not $g->is_advert_hidden() and not empty($actions[$g->id])}
                        {include file="common/action.tpl" action=$actions[$g->id]}{* акции по товару *}
                    {elseif $g->new}
                        <img src="/i/new_h.png" alt="новинка" />
                    {elseif $g->section_id eq 29051}
                        <abbr abbr="Вся продукция при&nbsp;доставке перевозится в&nbsp;специальных авто-холодильниках<br /><strong>(от +3 до +5 градусов)</strong>"><img src="/i/ice.png" alt="***" /></abbr>
                    {/if}
                </div>

                {if $g->grouped eq 1}
                    <input type="hidden" id="qty_{$g->id}" name="qty[{$g->id}]" value="1" />
                    <a class="butt small i i_cart c" rel="{$g->id}"><i></i>В корзину</a>
                {else}
                    <a href="{$link}?ajax=1" class="butt small" rev="{$g->id}" rel="ajax" data-fancybox-type="ajax">Выбрать</a>
                {/if}

            {else}
                <a class="review" title="{'отзыв'|plural:$g->review_qty}" href="{$g->get_review_link()}">{$g->review_qty}</a>
                {assign var=link value=$g->get_link(0)}

                {capture assign=name}{$g->group_name|escape:'html'} {$g->name|escape:'html'}{/capture}

                <a href="{$link}" title="{$name}"><img src="{$g->prop->get_img(255)}" alt="{$name}" /></a>
                <a href="{$link}">{$name}</a>
                <a href="{$link}?ajax=1" class="butt small fastview" rev="{$g->id}" rel="ajax" data-fancybox-type="ajax">Быстрый просмотр</a>

                <div class="price">
                    {if $g->old_price != 0}<del>{$g->old_price}</del>{/if}
                    <strong>{$g->price|price}</strong>
                    <abbr>{$price[$g->id]|default:$g->price|price}</abbr>
                    {$g|qty:0}
                </div>
                <div class="ico">
                    {if not $g->is_advert_hidden() and not empty($actions[$g->id])}
                        {include file="common/action.tpl" action=$actions[$g->id]}{* акции по товару *}
                    {elseif $g->new}
                        <img src="/i/new_h.png" alt="новинка" />
                    {elseif $g->section_id eq 29051}
                        <abbr abbr="Вся продукция при&nbsp;доставке перевозится в&nbsp;специальных авто-холодильниках<br /><strong>(от +3 до +5 градусов)</strong>"><img src="/i/ice.png" alt="***" /></abbr>
                    {/if}
                </div>
                {if $g->qty != 0}
                    {if in_array($g->section_id,[29773,29768,39717,29706,46063,29699,39716,29725,39715])}
                        <input type="hidden" id="qty_{$g->id}" name="qty[{$g->id}]" value="1" />
                        <a class="butt small i i_cart c" rel="{$g->id}"><i></i>В корзину</a>
                    {else}
                        {include file='common/buy.tpl' good=$g can_buy=$g->id}
                    {/if}
                {else}
                    <div class="buy wide">
                        <a class="do" rel="ajax" href="/product/warn/{$g->id}">Уведомить о&nbsp;поставке</a>
                    </div>
                {/if}
            {/if}
        </div>
    {/foreach}
    </div>
