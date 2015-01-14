{strip}
    {if not empty($other_shop_goods_counter) AND not empty($goods)}
        <script type="text/javascript">
            var another_vitrina_str = '<div class="another_vitrina_found">По запросу ';
            {if Kohana::$server_name eq 'mladenec'}
                another_vitrina_str += '<a href="http://eatmart.ru/search?q={$search_text}">&laquo;{$search_text}&raquo; на Eatmart.ru</a>';
                another_vitrina_str += ' {"найден"|plural:$other_shop_goods_counter:FALSE}';
                another_vitrina_str += '<a href="http://eatmart.ru/search?q={$search_text}"> {"товар"|plural:$other_shop_goods_counter}.</div>';
            {else}
                another_vitrina_str += '<a href="http://mladenec-shop.ru/search?q={$search_text}">&laquo;{$search_text}&raquo; на Младенец.ру</a>';
                another_vitrina_str += ' {"найден"|plural:$other_shop_goods_counter:FALSE}';
                another_vitrina_str += '<a href="http://mladenec-shop.ru/search?q={$search_text}"> {"товар"|plural:$other_shop_goods_counter}.</div>';
            {/if}
            
            $(document).ready(function() {
                $('div.another_vitrina_found').detach();
                $('h1').before(another_vitrina_str);
            });
        </script>
    {/if}

{if empty($hide_props)}

    <form action="" id="list_props" method="get">
        <input type="hidden" id="mode" name="m" value="{$params.m}" />

        {if empty( $section ) or not $section->is_cloth()}
        <div id="view_mode">
            <label for="sort"><strong>Вид:</strong></label>
            <a class="m0 {if $params.m eq 0}a{/if}" title="списком" rel="0"><i></i></a>
            <a class="m1 {if $params.m eq 1}a{/if}" title="группами" rel="1"><i></i></a>
        </div>
		{/if}
        <label for="page">Порядок:</label>

        <select id="sort" name="s">
			{foreach from=$params.sorts item=field}
			<option value="{$field}"{if not empty($params.s) and $params.s eq $field} selected='selected'{/if}>{Kohana::message('sorts', $field)}</option>
			{/foreach}
		</select>

        <label for="page">На странице:</label>
        <select id="page" name="pp">
            {foreach from=$params.per_page item=p}
            <option{if not empty($params.pp) and $p eq $params.pp} selected="selected"{/if}>{$p}</option>
            {/foreach}
        </select>

        {if $params.x eq 2}
            <input type="hidden" id="has" name="x" value="1" />
        {else}
            <label class="label"><i class="check"></i><input type="checkbox" id="has" name="x" value="1" class="big" {if $params.x eq 1}checked="checked"{/if} /> В наличии</label>
        {/if}

	    <div id="choice">
            {if not empty($params)}
                {if not empty($params.b)}
                {foreach from=$params.b item=i}
                    {if $b = $menu.brands[$i]|default}
                    <a rel="b{$b.id}" title="{$b.name}">{$b.name}</a>
                    {/if}
                {/foreach}
                {/if}

                {if empty($is_section) && not empty($params.c)}
                    {foreach from=$params.c item=i}
                        {if $c = $menu.sections[$i]|default:''}
                        <a rel="c{$c.id}" title="{$c.name}">{$c.name}</a>
                        {/if}
                    {/foreach}
                {/if}
                {if not empty($params.f)}
                    {foreach from=$params.f key=fid item=fvals}
                        {if $fid neq Model_Filter::CLOTH_BIG_TYPE and $fid neq Model_Filter::TOYS_BIG_TYPE}
                            {foreach from=$fvals item=i}
                                {if not empty($menu.vals[$fid])}
                                    {assign var=v value=$menu.vals[$fid][$i]}
                                    <a rel="f{$fid}_{$i}" title="{$v}">{$v}</a>
                                {/if}
                            {/foreach}
                        {/if}
                    {/foreach}
                {/if}
            {/if}
	    </div>

    </form>

{/if}

{if not isset($grouped)}{assign var=grouped value=1}{/if}

<div id="product_list">
{if ! empty($goods)}
    
    {if $params.m eq 1}
		
        {include file="product/view/tiles.tpl" goods=$goods row=4}

    {else}

        <form action="/product/add" method="post">
        {foreach from=$goods item=g name=g}
        {if $g->grouped}

            {assign var=link value=$g->get_link(0)}
            {capture assign=name}{$g->group_name|escape:'html'} {if $g->grouped eq 1}{$g->name|escape:'html'}{/if}{/capture}

            <table class="tt group">
                <tbody>
                <tr>
                    <td rowspan="2" class="img">
                        {if $images[$g->id].255 instanceof Model_File}
                            <a href="{$link}" title="{$name}">{$images[$g->id].255->get_img()}</a>
                        {else}
                            {$images[$g->id].255}
                        {/if}
                    </td>
                    <td colspan="4" class="name">
                        <a href="{$link}" title="{$name}"><strong>{$name}</strong></a>
                    </td>
	                <td>{if $g->section_id eq 29051}
			                <abbr abbr="Вся продукция при&nbsp;доставке перевозится в&nbsp;специальных авто-холодильниках<br /><strong>(от +3 до +5 градусов)</strong>"><img src="/i/ice.png" alt="***" /></abbr>
		                {/if}
	                </td>
                    <td class="r">
                        <span class="stars"><span style="width:{$g->rating*20}%"></span></span>
                        <a title="{'отзыв'|plural:$g->review_qty}" href="{$link}#reviews">{'мнение'|plural:$g->review_qty}</a>
                    </td>
                </tr>
                <tr>
                    <td class="qty">{if $g->grouped == 1}<div>{$g|qty:0}</div>{/if}</td>
                    <td class="name">{if $g->grouped == 1}{$g->name}{else}в ассортименте{/if} {if $g->qty == -1}<small class="wait">Доставка в течение 2-х дней</small>{/if}</td>
                    <td>{if $g->new}<img src="/i/new_h.png" alt="новинка" />{/if}
                        {if not $g->is_advert_hidden() and not empty($actions[$g->id])}
                            {include file="common/action.tpl" action=$actions[$g->id]}
                        {/if}
                    </td>
                    <td class="price">{if $g->old_price neq '0.00'}<del>{$g->old_price|price}</del><br />{/if}{if ! $g->same_price}от {/if}{$g->price|price}</td>
                    <td class="price"><abbr>{$price[$g->id]|price}</abbr></td>
                    <td class="c">
                        {if $g->grouped eq 1}
                            <input type="hidden" id="qty_{$g->id}" name="qty[{$g->id}]" value="1" />
                            <a class="butt small i i_cart c" rel="{$g->id}"><i></i>В корзину</a>
                        {else}
                            <a href="{$link}?ajax=1" class="butt small fr w82" rev="{$g->id}" rel="ajax" data-fancybox-type="ajax">Выбрать</a>
							<script>
                                $(document).ready(function() {
                                    $('a[rev={$g->id}]').click(function() {
                                        if ( touchable ) {
                                            document.location.href = "{$link}";
                                            return false;
                                        }
                                    });
                                });
							</script>
                        {/if}
                    </td>
                </tr>
                </tbody>
            </table>

        {else}

            {assign var=link value=$g->get_link(0)}
            {capture assign=name}{$g->group_name|escape:'html'} {$g->name|escape:'html'}{/capture}

            <table class="tt group">
            <tbody>
            <tr>
                <td rowspan="2" class="img">
                    {if $images[$g->id].255 instanceof Model_File}
                        <a href="{$link}" title="{$name}">{$images[$g->id].255->get_img()}</a>
                    {else}
                        {$images[$g->id].255}
                    {/if}
                </td>
                <td colspan="5" class="name">
                    <a href="{$link}" title="{$name}"><strong>{$g->group_name}</strong></a>
                </td>
	            {if $g->section_id eq 29051}
                <td>
                    <abbr abbr="Вся продукция при&nbsp;доставке перевозится в&nbsp;специальных авто-холодильниках<br /><strong>(от +3 до +5 градусов)</strong>"><img src="/i/ice.png" alt="***" /></abbr>
				</td>
	            {/if}
                <td class="r" colspan="{if $g->section_id eq 29051}1{else}2{/if}">
                    <span class="stars"><span style="width:{$g->rating*20}%"></span></span>
					{if $g->rating gt 0}
                    <a title="{'отзыв'|plural:$g->review_qty}" href="{$link}#reviews">{'мнение'|plural:$g->review_qty}</a>
					{/if}
                </td>
            </tr>
            <tr>
                <td class="qty"><div>{$g|qty:0}</div></td>
                <td class="name">
                    {$g->get_link()}
                    {if $g->qty == -1}<small class="wait">Доставка в течение 2-х дней</small>{/if}
                </td>
                <td>{if $g->new}<img src="/i/new_h.png" alt="новинка" />{/if}</td>
                <td>{if not $g->is_advert_hidden() and not empty($actions[$g->id])} {include file="common/action.tpl" action=$actions[$g->id]}{/if}</td>
                <td class="price">{if $g->old_price neq '0.00'}<del>{$g->old_price|price}</del><br />{/if}{$g->price|price}</td>
                <td class="price"><abbr>{$price[$g->id]|price}</abbr></td>
                <td class="c">
                    {if $g->qty != 0}
                        <input type="hidden" id="qty_{$g->id}" name="qty[{$g->id}]" value="1" />
                        <a class="butt small i i_cart c" rel="{$g->id}"><i></i>В корзину</a>
                    {else}
                        <a class="do" rel="ajax" href="/product/warn/{$g->id}">Уведомить о&nbsp;поставке</a>
                    {/if}
                </td>
            </tr>
            </tbody>
            </table>
        {/if}
        {/foreach}

        </form>

    {/if}

    {$pager->html('Товары')}

{else}

    <p>Товаров, удовлетворяющих всем условиям поиска, не&nbsp;найдено</p>
    <!-- NOT FOUND MARKER -->{* не удалять, это маркер автоотслеживания объявлений *}

{/if}
    {if not empty($other_shop_goods_counter)}
    <h2 class="yell">По запросу&nbsp;
        {if Kohana::$server_name eq 'mladenec'}
            <a href="http://eatmart.ru/search?q={$search_text}">&laquo;{$search_text}&raquo; на Eatmart.ru</a>
        {else}
            <a href="http://mladenec-shop.ru/search?q={$search_text}">&laquo;{$search_text}&raquo; на Младенец.ру</a>
        {/if}
        &nbsp;{'найден'|plural:$other_shop_goods_counter:FALSE} {'товар'|plural:$other_shop_goods_counter}:</h2>

        {include file="product/view/tiles.tpl" goods=$other_shop_goods row=4}
    {/if}
</div>
{/strip}
<script>
	if (typeof('updateLinks') === 'function') updateLinks();
</script>
