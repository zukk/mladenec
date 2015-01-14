    <div class="good_tiles">
	{assign var=all_colors value=Controller_Product::$COLORS}
	<script>
		var goodsdata = { };
		$(function(){
			var setCard = function(o){
				var nid = o.attr('data-id');
				var id = arguments[1];
				if( ! id ) id = nid;
					
				var info = goodsdata[nid][id], c = $(o.parents('.g2')[0]);
				o.find('img').removeClass('active');
				o.find('img[rel='+id+']').addClass('active');
				c.find('.g2-a-img img').replaceWith(info.image);
				c.find('.price strong').html(info.price + "<small> р.</small>");
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
	</script>
    {foreach from=$goods item=g name=g}
        <div class="g2">
			<script>
				goodsdata[{$g->id}] = { };
			{if ! empty( $cloth )}
            {foreach from=$cloth.groups[$g->group_id] item=gId}
				var i = new Image();
				// сразу грузим все изображения
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
            {if $g->grouped}
			    {assign var=link value=$g->get_link(0)}

				{* мини-картинки всех товаров в группе*}
                {if not empty($cloth)}
			    {assign var=groupValues value=$cloth['groupValues'][$g->group_id]}
                <div data-id="{$g->id}" class="smallpic">
                {foreach from=$cloth['groups'][$g->group_id] item=gId}
                    {if not empty( $images[$gId] )}
                        <img title="{$goods[$gId]->group_name|escape:html} {$goods[$gId]->name|escape:html}" rel="{$gId}"
                             class="{if $g->id eq $gId}active{/if}" src="{$images[$gId].70->get_img(0)}" />
                    {/if}
                {/foreach}
                </div>
                {/if}

                {* цвета, встречающиеся в группе}
                {assign var=colors value=$groupValues[Controller_Product::FILTER_COLOR]|default:0}
			    {if not empty($colors) and count($colors) gt 1}
				<div class="colors">
				{foreach from=$colors key=fvalue item=_1}
					{if not empty( $all_colors[$fvalue] )}
						<div style="background-color:{$all_colors[$fvalue]};
                            {if $all_colors[$fvalue] eq '#ffffff'}border: 1px solid #ccc;{/if}
                            {if $cloth.goodValues[$g->id][Controller_Product::FILTER_COLOR] eq $fvalue}border: 1px solid #fff; outline: 1px solid
                                {if $colors[$fvalue] eq '#ffffff'}#ccc{else}{$colors[$fvalue]}{/if};
                            {/if}" class="colors-item" title="{$fvalue}"></div>
					{/if}
				{/foreach}
				</div>
			    {/if*}

                {* карточка*}
				<div style="height: 255px"></div>
                <a class="g2-a-img" href="{$link}" title="{$g->group_name}">{$images[$g->id].173x255->get_img()}</a>

				<a href="{$link}" style="font-weight: bold">{$menu.brands[$g->brand_id].name|default:''}</a>
				<div>{$menu.vals[Controller_Product::FILTER_TYPE][$cloth.goodValues[$g->id][Controller_Product::FILTER_TYPE]]|default:''}</div>

				<script>$(document).ready(function() { if (touchable) $('.fastview').hide()})</script>
                <a href="{$link}?ajax=1" class="fastview" rev="{$g->id}" rel="ajax" data-fancybox-type="ajax">Быстрый просмотр</a>    

                <div class="price">
                    <strong>{$g->price|price}</strong>
                    <abbr style="float: left; margin-left: 10px; font-style: italic; color: #76ab77;">{$price[$g->id]|price}</abbr>
					<br clear="all" />
                    {if $g->same_price and $g->old_price gt 0}<del>{$g->old_price|price}</del>{/if}
                    {* {if $g->grouped eq 1}{$g->qty|qty:0}{/if} *}
                </div>
                <div class="ico">
                    {if not $g->is_advert_hidden() and not empty($actions[$g->id])}
                        {include file="common/action.tpl" action=$actions[$g->id]}{* акции по товару *}
                    {elseif $g->new}
                        <img src="/i/new_h.png" alt="новинка" />
                    {/if}
                </div>
					<span class="stars"><span style="width:{$g->rating*20}%"></span></span> <span style="font-size: 0.9em; display: inline-block; position: relative; top: -2px;">({$g->review_qty})</span>

                {*if $g->grouped eq 1}
                    <input type="hidden" id="qty_{$g->id}" name="qty[{$g->id}]" value="1" />
                    <a class="butt small i i_cart c" rel="{$g->id}"><i></i>В корзину</a>
                {else}
                    <a href="{$link}?ajax=1" class="butt small" rev="{$g->id}" rel="ajax" data-fancybox-type="ajax">Выбрать</a>
                {/if*}

                {* доступные ростовки или размеры *}
                <div class="g2-growth">
                {if ! empty( $groupValues[Controller_Product::FILTER_GROWTH] )}
                    <span style="color: #999">Рост:</span>
                    <div>
                    {foreach from=$groupValues[Controller_Product::FILTER_GROWTH] key=fvalue item=_1}
                        {$fvalue}
                    {/foreach}
                    </div>
                {elseif ! empty( $groupValues[Controller_Product::FILTER_SIZE] )}
                    <span style="color: #999">Размер:</span>
                    <div>
                    {foreach from=$groupValues[Controller_Product::FILTER_SIZE] key=fvalue item=_1}
                        {$fvalue}
                    {/foreach}
                    </div>
                {/if}
                </div>

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
                    <br clear="all" />
                    {if $g->same_price and $g->old_price gt 0}<del>{$g->old_price|price}</del>{/if}
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
                        <a class="do appendhash" rel="ajax" data-url='/product/warn/' href="#{$g->id}">Уведомить о&nbsp;поставке</a>
                    </div>
                {/if}
            {/if}
        </div>
    {/foreach}
    </div>
	<script>
		$(function(){
			var moved = false;
			$('.g2 a').on('mousedown', function(event){
                event.preventDefault();
                event.stopPropagation();
			})
			.on('touchstart', function(event){
				moved = false;
			}).on('touchmove', function(event){
				moved = true;
			}).on('touchend', function(event){
                event.preventDefault();
                event.stopPropagation();
				
				var href = $(this).attr('href');
				
				if( ! moved) document.location.href = href;
				moved = false;
			});
		});
	</script>
