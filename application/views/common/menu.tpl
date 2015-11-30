{* левое меню с сортировками товаров для поиска *}
{strip}
{assign var=column value=8}
{assign var=sid value=$section->id|default:0}
<div id="menu" class="ajax">
	{if $vitrina eq 'kotdog'}
		<a href="/" id="logo"><img src="/i/kotdog/head.png" alt="Kot-dog.ru" /></a>
	{/if}

	<div id="ff">
    {if ! empty($actions) and (empty($params.a) || $params.a === true)}
        <a class="checkbox aaa {if $params.a|default:0}checked{/if}" href="{$sphinx->href(['a' => 1])}">Акции <b>!!!</b><i></i></a>
    {/if}

    {if ! empty($sections) and empty($sphinx->_section)}
        <strong>Категории <i class="toggler"></i></strong>
        <ul>
        {foreach from=$sections item=c name=c}
            <li {if empty($toggler[$sid]['c']) and empty($toggler[$mode][$query]['c']) and ($smarty.foreach.c.iteration gt $column
            or ($smarty.foreach.c.iteration eq $column and $smarty.foreach.c.total gt $column))}class="hide"{/if}>

                {if ! empty($params.c) AND in_array($c.id, $params.c)}
                    {assign var=checked value=1}
                {else}
                    {assign var=checked value=0}
                {/if}

                {if $checked}
                    {assign var=change value=-$c.id}
                {else}
                    {assign var=change value=$c.id}
                {/if}
                <a href="{$sphinx->href(['c' => [$change]])}" class="checkbox {if $checked}checked{/if} {if empty($c.qty)}empty{/if}" title="{$c.name} ({$c.qty})"><i></i> {$c.name|trim}{*nospace!*}<small>{$c.qty}</small></a>
            </li>

        {/foreach}
            {if $smarty.foreach.c.total gt $column}
                {assign var=delta value=$smarty.foreach.c.total-$column+1}
                <li>
                    <a rel="c" rev="{$delta}" class="toggler
                        {if empty($toggler[$sid]['c'])}">+ Показать ещё {$delta}{else} up">- Скрыть {$delta}{/if}
                    </a>
                </li>
            {/if}
        </ul>
		{/if}

        {capture assign=branda}
        {if ! empty($brands)}
        <strong>Бренд <i class="toggler"></i></strong>
        <ul>
        {foreach from=$brands item=b name=b}
            <li {if empty($toggler[$sid]['b']) and empty($toggler[$mode][$query]['b']) and ($smarty.foreach.b.iteration gt $column
                or ($smarty.foreach.b.iteration eq $column and $smarty.foreach.b.total gt $column))}class="hide"{/if}>

                {if ! empty($params.b) AND in_array($b.id, $params.b)}
                    {assign var=checked value=1}
                {else}
                    {assign var=checked value=0}
                {/if}

                {if $checked}
                    {assign var=change value=-$b.id}
                {else}
                    {assign var=change value=$b.id}
                {/if}
                <a href="{$sphinx->href(['b' => [$change]])}" class="checkbox {if empty($b.qty)}empty{/if} {if $checked}checked{/if} {if ! empty($section->settings.b_hit[$b.id])}hit{/if}" title="{$b.name} ({$b.qty})"><i></i> {$b.name|trim}{*nospace!*}<small>{$b.qty}</small></a>

                {if ! empty($binded['b'][$b.id])} {* привязанный фильтр *}
                    {assign var=fid value=$binded['b'][$b.id]}
                    {include file='common/menu/filter.tpl' vals=$vals[$fid]}
                {/if}
            </li>
        {/foreach}
            {if $smarty.foreach.b.total gt $column}
                {assign var=delta value=$smarty.foreach.b.total-$column+1}
                <li>
                    <a rel="b" rev="{$delta}" class="toggler
                        {if empty($toggler[$sid]['b'])}">+ Показать ещё {$delta}{else} up">- Скрыть {$delta}{/if}
                    </a>
                </li>
            {/if}
        </ul>
        {/if}
        {/capture}

        {if $sid != 29293}{$branda|default:''}{/if}

        {if not empty($filters)}
            {assign var=shownf value=0}
			{foreach from=$filters item=fname key=fid name=filterz}

                {if $fname and not Model_Filter::big($fid) and empty($hide[$fid])}

                    {if $sid == 29293 && $shownf eq 1} {* в этой категории бренды после первого фильтра *}
                        {$branda}
                    {/if}

                    {assign var=shownf value=$shownf+1}

                {assign var=under value=$fname|strpos:'_'}
                {if $under}{assign var=fname value=$fname|mb_substr:$under}{/if}
	            <strong>{$fname} <i class="toggler"></i></strong>

                {if $fid eq Model_Filter::STROLLER_WEIGHT or $fid eq Model_Filter::STROLLER_SHASSI}
                    {include file="common/menu/begunok.tpl"}
                {else}
                <ul>
                    {if $fid eq Model_Filter::PURE_SOSTAV and Model_Filter::taste_on($params)}

                        {if ! empty($params.f[Model_Filter::TASTE])}
                            {assign var=checked value=1}
                        {else}
                            {assign var=checked value=0}
                        {/if}

                        {if $checked}
                            {assign var=change value=-1}
                        {else}
                            {assign var=change value=1}
                        {/if}

                        <li><a href="{$sphinx->href(['f' => [Model_Filter::TASTE => [$change]]])}"
                            class="checkbox {if $checked}checked{/if}"><i></i> <b>Только выбранный состав</b></a></li>
                    {/if}

                    {foreach from=$vals[$fid] item=val name=b key=vid}
	                    <li {if empty($toggler[$sid][$fid]) and empty($toggler[$mode][$query][$fid]) and ($smarty.foreach.b.iteration gt $column
	                        or ($smarty.foreach.b.iteration eq $column and $smarty.foreach.b.total gt $column))}class="hide"{/if}>

	                        {if ! empty($params.f[$fid]) AND in_array($vid, $params.f[$fid])}
	                            {assign var=checked value=1}
	                        {else}
	                            {assign var=checked value=0}
	                        {/if}

                            {if $checked}
                                {assign var=change value=-$vid}
                            {else}
                                {assign var=change value=$vid}
                            {/if}

                            <a href="{$sphinx->href(['f' => [$fid => [$change]]])}" class="checkbox  {if empty($val.qty)}empty{/if} {if $checked}checked{/if}"  title="{$val.name} ({$val.qty})"><i></i> {$val.name|trim}{*nospace!*}<small>{$val.qty}</small></a>

                            {if not empty($binded['v'][$vid])} {* привязанный фильтр *}
                                {assign var=myfid value=$binded['v'][$vid]}
                                {include file='common/menu/filter.tpl' vals=$vals[$myfid] fid=$myfid}
                            {/if}

	                    </li>
                    {/foreach}
                    {if $smarty.foreach.b.total gt $column}
                        {assign var=delta value=$smarty.foreach.b.total-$column+1}
                        <li>
                            <a rel="{$fid}" rev="{$delta}" class="toggler
                            {if empty($toggler[$sid][$fid]) and empty($toggler[$mode][$query][$fid])}">+ Показать ещё {$delta}{else} up">- Скрыть {$delta}{/if}
                            </a>
                        </li>
                    {/if}

                    {if $fid eq 2198}{* бегунок по весу для подгузников *}
                        {if ! empty($vals[Model_Filter::WEIGHT])}
                            {assign var=first value=array_shift($vals[Model_Filter::WEIGHT])}
                            {assign var=last value=array_pop($vals[Model_Filter::WEIGHT])}
                        {/if}

                        {assign var=maxw value=$last->name|default:35}
                        {assign var=minw value=$first->name|default:0}

                        <li style="padding-top:10px;">
                            <div class="range" rev="weight">
                                <span class="range-ui">
                                    <span class="line" rel="weight" rev="weight"><i class="min"></i><i class="max"></i></span>
                                </span>
                                от<input class="min" rel="{$minw}" value="{$params.weight.0|default:''}" placeholder="{$minw}" />
                                до<input class="max" rel="{$maxw}" value="{$params.weight.1|default:''}" placeholder="{$maxw}" />кг
                            </div>

                            <input id="weight" name="weight" type="hidden" value="{$weight|default:'0-35'}" data-url="{$sphinx->href(['weight' => 1])}"/>{* weight *}
                        </li>
                    {/if}

                </ul>
                {/if}

                {/if}
	        {/foreach}
		{/if}

        {if ! empty($countries)}
            <strong>Страна <i class="toggler"></i></strong>
            <ul>
                {foreach from=$countries item=co name=co}
                    <li {if empty($toggler[$sid]['co']) and empty($toggler[$mode][$query]['co']) and ($smarty.foreach.co.iteration gt $column
                    or ($smarty.foreach.co.iteration eq $column and $smarty.foreach.co.total gt $column))}class="hide"{/if}>

                        {if ! empty($params.co) AND in_array($co.id, $params.co)}
                            {assign var=checked value=1}
                        {else}
                            {assign var=checked value=0}
                        {/if}

                        {if $checked}
                            {assign var=change value=-$co.id}
                        {else}
                            {assign var=change value=$co.id}
                        {/if}
                        <a href="{$sphinx->href(['co' => [$change]])}" class="checkbox {if empty($co.qty)}empty{/if} {if $checked}checked{/if}" title="{$co.name} ({$co.qty})"><i></i> {$co.name|trim}{*nospace!*}<small>{$co.qty}</small></a>
                    </li>

                {/foreach}
                {if $smarty.foreach.co.total gt $column}
                    {assign var=delta value=$smarty.foreach.co.total-$column+1}
                    <li>
                        <a rel="co" rev="{$delta}" class="toggler
                            {if empty($toggler[$sid]['co']) and empty($toggler[$mode][$query]['co'])}">+ Показать ещё {$delta}{else} up">- Скрыть {$delta}{/if}
                        </a>
                    </li>
                {/if}
            </ul>
        {/if}

        {if not empty($max)}
        <strong>Стоимость <i class="toggler"></i></strong>

        <div class="range" rev="prpr">
            <span class="range-ui">
                <span class="line" rel="prpr" rev="pr"><i class="min"></i><i class="max"></i></span>
            </span>
            от<input class="min" rel="{$min|default:0}" value="{$params.pr.0|default:''}" placeholder="{$min}" />
            до<input class="max" rel="{$max}" value="{$params.pr.1|default:''}"  placeholder="{$max}"/>р.
        </div>
        {else}
            <p>Товаров не найдено<br /><br /><br /></p>
            <!-- NOT FOUND MARKER -->{* не удалять, это маркер автоотслеживания объявлений *}
        {/if}

    </div>

	{if ! empty($params.pr)}{capture assign=pr}{$params.pr.0|default:0}-{$params.pr.1}{/capture}{/if}

    <input id="prpr" name="pr" type="hidden" value="{$pr|default:''}" data-url="{$sphinx->href(['pr' => 1])}"/>{* price *}

    <input name="section" value="{$sid}" type="hidden" id="search_section" />
    <input name="mode" value="{$mode}" type="hidden" id="search_mode" />
    <input name="query" value="{$query}" type="hidden" id="search_query" />

</div>
{/strip}