{assign var=all_colors value=Controller_Product::$COLORS}
<script>
    var goodsdata = { };
    $(function() {
        var setCard = function(o){
            var nid = o.attr('data-id');
            var id = arguments[1];
            if( ! id ) id = nid;

            var info = goodsdata[nid][id], c = $(o.parents('.g2')[0]);
            o.find('img').removeClass('active');
            o.find('img[rel='+id+']').addClass('active');
            c.find('.g2-a-img img').replaceWith(info.image);
            c.find('.price strong').html(format_price(info.price, 0));
            c.find('.g2-growth .sizes').addClass('hide');
            c.find('.g2-growth .sizes[rel='+id+']').removeClass('hide');
        };

        $('.smallpic').each(function(){
            var o = $(this), id = $(this).attr('data-id');
            $(this).on('mouseleave', function(){
                setCard(o);
            });
            $(this).find('img').css({
                cursor: 'pointer'
            }).on('mouseenter', function(){
                setCard(o, $(this).attr('rel'));
            }).on('click', function(){
                location.href = goodsdata[id][$(this).attr('rel')]['link'];
            });
        });
    });

    $(document).ready(function() { if (touchable) $('.fastview').hide()});

    $(function() {
        var moved = false;
        $('.g2 a')
        .on('mousedown', function(event){
            event.preventDefault();
            event.stopPropagation();
        })
        .on('touchstart', function(event){
            moved = false;
        })
        .on('touchmove', function(event){
            moved = true;
        })
        .on('touchend', function(event){
            event.preventDefault();
            event.stopPropagation();

            var href = $(this).attr('href');

            if ( ! moved) document.location.href = href;
            moved = false;
        });
    });

</script>

<div class="good_tiles">

{foreach from=$goods item=g name=g}
    {assign var=cs value=$colorsize[$g->group_id]}
    <div class="g2">
        <script>
            goodsdata[{$g->id}] = { };
        {if not empty($cs['colors'])}
            {foreach from=$cs['colors'] key=color item=gIds} {*сразу грузим все изображения 173x255*}
                {assign var=gId value=$gIds.0}
                var i = new Image();
                i.src = "{$images[$gId].173x255->get_img(0)}";

                goodsdata[{$g->id}][{$gId}] = {
                    image: i,
                    price: {$goods[$gId]->price},
                    lovelyprice: "{$price[$gId]|price}",
                    link: "{$goods[$gId]->get_link(0)}"
                };
            {/foreach}
        {/if}
        </script>

        {assign var=link value=$g->get_link(0)}

        {* мини-картинки всех товаров в группе, с привязкой к товарам и большим картинкам *}
        {if not empty($cs['colorimage'])}
        <div data-id="{$g->id}" class="smallpic">
        {foreach from=$cs['colorimage'] key=color item=image}
            {assign var=gId value=$cs['colors'][$color][0]}
            {if not empty($images[$gId])}
                <img title="{$goods[$gId]->group_name|escape:html} {$goods[$gId]->name|escape:html}" rel="{$gId}"
                    class="{if $g->id eq $gId}active{/if}" src="{$image}" />
            {/if}
        {/foreach}
        </div>
        {/if}

        {* карточка*}
        <div style="height: 255px"></div>
        <a class="g2-a-img google-good" data-id="{$g->id}" href="{$link}" title="{$g->group_name}">{$images[$g->id].173x255->get_img()}</a>
        <a class="google-good" data-id="{$g->id}" href="{$link}">{$menu.brands[$g->brand_id].name|default:''}</a>
        {assign var=name value=' '|explode:$g->group_name}
        <div>{$name.0} {if in_array(mb_substr($name.0, -1), ['й', 'е'])}{$name.1}{/if}</div>{* для одежды - начало названия товара, не больше 2х слов *}

        <a data-fancybox-href="{$link}?ajax=1" class="butt small fastview" rev="{$g->id}" rel="ajax" data-fancybox-type="ajax">Быстрый просмотр</a>

        <div class="price">
            {if $g->grouped eq 1}{$g->qty|qty:0}{/if}

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

        <div class="ico">
            {if not $g->is_advert_hidden() and not empty($actions[$g->id])}
                {include file="common/action.tpl" action=$actions[$g->id]}{* акции по товару *}
            {elseif $g->new}
                <img src="/i/new_h.png" alt="новинка" />
            {/if}
        </div>

        <span class="stars"><span style="width:{$g->rating*20}%"></span></span>
        <span>({$g->review_qty})</span>

        {* доступные ростовки или размеры или возраста, зависят от цвета *}
        <div class="g2-growth">
            <span>
            {if $cs.size_filter eq Controller_Product::FILTER_GROWTH}
                Рост:
            {elseif $cs.size_filter eq Controller_Product::FILTER_SIZE}
                Размер:
            {elseif $cs.size_filter eq Controller_Product::FILTER_AGE}
                Возраст:
            {/if}
            </span>
            {foreach from=$cs.sizes key=gId item=sizes}
                <div class="sizes {if $gId neq $g->id}hide{/if}" rel="{$gId}" >{', '|implode:$sizes}</div>
            {/foreach}
        </div>
		{include file="google/click.tpl" good=$g}
    </div>
{/foreach}
</div>

{include file="google/impressions.tpl"}