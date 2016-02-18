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

    <div id="list_props">

        <label for="page">Порядок:</label>

        <select id="sort" name="s" data-url="{$sphinx->href(['s' => 1])}">
			{foreach from=$params.sorts item=field}
			<option value="{$field}"{if not empty($params.s) and $params.s eq $field} selected='selected'{/if}>{Kohana::message('sorts', $field)}</option>
			{/foreach}
		</select>

        <label for="page">На странице:</label>
        <select id="page" name="pp" data-url="{$sphinx->href(['pp' => 1])}">
            {foreach from=$params.per_page item=p}
            <option{if not empty($params.pp) and $p eq $params.pp} selected="selected"{/if}>{$p}</option>
            {/foreach}
        </select>

	    <div id="choice">
            {assign var=choice value=0}
            {if not empty($params)}
                {if not empty($params.b)}
                {foreach from=$params.b item=i}
                    {if $b = $menu.brands[$i]|default}
                    <a title="{$b.name}" href="{$sphinx->href(['b' => [-$b.id]])}">{$b.name}</a>
                        {assign var=choice value=1}
                    {/if}
                {/foreach}
                {/if}

                {if empty($section) && not empty($params.c)}
                    {foreach from=$params.c item=i}
                        {if $c = $menu.sections[$i]|default:''}
                        <a title="{$c.name}" href="{$sphinx->href(['c' => [-$c.id]])}">{$c.name}</a>
                            {assign var=choice value=1}
                        {/if}
                    {/foreach}
                {/if}

                {if not empty($params.f)}
                    {foreach from=$params.f key=fid item=fvals}
                        {if not Model_Filter::big($fid)}
                            {if in_array($fid, [Model_Filter::STROLLER_WEIGHT, Model_Filter::STROLLER_SHASSI])}
                                {if is_array($fvals)}
                                    {$fvals|print_r}
                                {else}
                                    <a title="{$fvals}" href="{$sphinx->href(['f' => [$fid => [0]]])}">{$fvals} {if $fid eq Model_Filter::STROLLER_WEIGHT}кг{else}см{/if}</a>
                                {/if}
                            {else}
                                {foreach from=$fvals item=i}
                                    {if not empty($menu.vals[$fid][$i])}
                                        {assign var=v value=$menu.vals[$fid][$i]}
                                        <a title="{$v.name}" href="{$sphinx->href(['f' => [$fid => [-$i]]])}">{$v.name}</a>
                                        {assign var=choice value=1}
                                    {/if}
                                {/foreach}
                            {/if}
                        {/if}
                    {/foreach}
                {/if}

                {if not empty($params.weight)}
                    <a href="{$sphinx->href(['weight' => -1])}">
                        {$params.weight.0|default:$minw|default:0} - {$params.weight.1|default:$maxw|default:35} кг
                    </a>
                    {assign var=choice value=1}
                {/if}

                {if not empty($params.pr)}
                    <a href="{$sphinx->href(['pr' => -1])}">
                        {$params.pr.0|default:$min|default:0|number_format:0:'':' '} - {$params.pr.1|default:$max|number_format:0:'':' '} р.
                    </a>
                    {assign var=choice value=1}
                {/if}

                {if not empty($params.a) and $params.a === true}
                    <a href="{$sphinx->href(['a' => 1])}">
                        Акции
                    </a>
                    {assign var=choice value=1}
                {/if}
            {/if}
            {if $choice}
                <a href="{$sphinx->href(Sphinx::HREF_INIT)}">Сбросить фильтры</a>
            {/if}
	    </div>
    </div>
{/if}

{if not isset($grouped)}{assign var=grouped value=1}{/if}

<div id="product_list">
{if ! empty($goods)}
    
    {include file="product/view/tiles.tpl"}

    {$pager->html('Товары')}

{else}

    <p>Товаров, удовлетворяющих всем условиям поиска, не&nbsp;найдено</p>
    <!-- NOT FOUND MARKER -->{* не удалять, это маркер автоотслеживания объявлений *}

{/if}
    {if not empty($other_shop_goods_counter)}
    <div class="yell">
        <h2>По запросу&nbsp;
            {if Kohana::$server_name eq 'mladenec'}
                <a href="http://www.eatmart.ru/search?q={$search_text}">&laquo;{$search_text}&raquo; на Eatmart.ru</a>
            {else}
                <a href="http://www.mladenec-shop.ru/search?q={$search_text}">&laquo;{$search_text}&raquo; на Младенец.ру</a>
            {/if}
            &nbsp;{'найден'|plural:$other_shop_goods_counter:FALSE} {'товар'|plural:$other_shop_goods_counter}:
        </h2>
    </div>
        {include file="product/view/tiles.tpl" goods=$other_shop_goods}
    {/if}
</div>
{/strip}
<script>
	if (typeof('updateLinks') === 'function') updateLinks();
</script>
