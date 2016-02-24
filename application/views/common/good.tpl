{* общий блок, использовать для всех миникарточек товара
@param $g - Model_Good - текущий товар
@param $price - [] Массив цен ЛК
@param $images - Model_File[] Массив картинок товара
@param $actions - Model_Action[] Массив активных акций
*}

{assign var=link value=$g->get_link(0)}
{capture assign=name}{$g->group_name|escape:'html'} {if $g->grouped eq 1}{$g->name|escape:'html'}{/if}{/capture}

<a class="google-good" data-id="{$g->id}" href="{$link}" title="{$name}">{$images[$g->id].255->get_img(['alt' => $name])}</a>
<a class="google-good" data-id="{$g->id}" href="{$link}"><b>{$g->group_name|escape:'html'}</b> {if $g->grouped eq 1}{$g->name|escape:'html'}{/if}</a>

<a href="{$link}?ajax=1" data-id="{$g->id}" class="butt small fastview" rev="{$g->id}" rel="ajax" data-fancybox-type="ajax">Быстрый просмотр</a>

<div class="price">
    {if $g->grouped eq 1}{$g|qty:0}{/if}

    {if $g->old_price > 0}<del>{$g->old_price|price}</del>{/if}

    {assign var=lovely value=Cart::instance()->status_id()}
    {assign var=lovely_price value=$price[$g->id]}
    {assign var=default_price value=$g->price}
    {if ! empty($lovely)}
        {assign var=current_price value=$lovely_price}
    {else}
        {assign var=current_price value=$default_price}
    {/if}

    <b>{$current_price|price}</b>
</div>
{if not empty($per_pack) and $g->per_pack}
    {assign var=one value=$current_price/$g->per_pack}
    <small class="per_pack">[{$one|string_format:'%01.2f'} р/шт]</small>
{/if}

{if $g->review_qty}
    <span class="stars"><span style="width:{$g->rating*20}%"></span></span>
    <a class="review google-good" data-id="{$g->id}" title="{'отзыв'|plural:$g->review_qty}" href="{$g->get_review_link()}">
    ({if not empty($per_pack) and $g->per_pack}{$g->review_qty}{else}{'отзыв'|plural:$g->review_qty}{/if})</a>
{/if}

{include file='common/buy.tpl' good=$g}

<div class="ico">
    {if $g->sborkable()}
        <a href="/delivery#sborka_tovara"><img src="/i/sborka_icon.png" alt="Бесплатная сборка" title="Бесплатная сборка" /></a>
    {/if}

    {if $g->old_price > 0} {* есть старая цена - покажем скидку в процентах *}
        <span class="real-discount">скидка {Txt::discount($g)}%</span>
    {/if}

    {if not $g->is_advert_hidden() and not empty($good_action[$g->id])}
        {include file="common/action.tpl" action=$good_action[$g->id]}{* акции по товару *}
    {elseif $g->new}
        <img src="/i/new_h.png" alt="новинка" />
    {/if}

    {if $g->section_id eq Model_Section::MILK_ID}
        <abbr abbr="Вся продукция при&nbsp;доставке перевозится в&nbsp;специальных авто-холодильниках<br /><strong>(от +3 до +5 градусов)</strong>"><img src="/i/ice.png" alt="***" /></abbr>
    {/if}
</div>

{include file="google/click.tpl" good=$g}
