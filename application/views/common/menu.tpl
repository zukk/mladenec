{* левое меню с сортировками товаров для поиска *}
{strip}
{assign var=column value=8}
{assign var=chosen value=''}
<div id="menu">
	{if $vitrina eq 'kotdog'}
		<a href="/" id="logo"><img src="/i/kotdog/head.png" alt="Kot-dog.ru" /></a>
	{/if}

	<div id="ff">
        {if ! empty($sections) and count($sections) gt 1}

        <strong>Категории <i class="toggler"></i></strong>
        <ul>
        {foreach from=$sections item=c name=c}
            <li {if $smarty.foreach.c.iteration gt $column
            or ($smarty.foreach.c.iteration eq $column and $smarty.foreach.c.total gt $column)}class="hide"{/if}>

                {if ! empty($params.c) AND in_array($c.id, $params.c)}
                    {assign var=checked value=1}
                    {capture assign=chosen}{$chosen}<a rel="c{$c.id}" title="{$c.name|escape:html}">{$c.name}</a>{/capture}
                {else}
                    {assign var=checked value=0}
                {/if}

                <label title="{$c.name}" class="label{if $checked} checked{/if}"><i class="check"></i><input type="checkbox" name="c" value="{$c.id}"
					id="c{$c.id}" {if $checked}checked="checked"{/if}/> {$c.name}</label>
            </li>
        {/foreach}
            {if $smarty.foreach.c.total gt $column}
                <li>
                    <a class="toggler">+ Показать все</a>
                </li>
            {/if}
        </ul>
		{/if}

        {if ! empty($brands) and count($brands) gt 1}
        <strong>Бренд <i class="toggler"></i></strong>
        <ul>
        {foreach from=$brands item=b name=b}
            <li {if $smarty.foreach.b.iteration gt $column
                or ($smarty.foreach.b.iteration eq $column and $smarty.foreach.b.total gt $column)}class="hide"{/if}>

                {if ! empty($params.b) AND in_array($b.id, $params.b)}
                    {assign var=checked value=1}
                    {capture assign=chosen}{$chosen}<a rel="b{$b.id}" title="{$b.name|escape:html}">{$b.name}</a>{/capture}
                {else}
                    {assign var=checked value=0}
                {/if}

                <label title="{$b.name}" class="label{if $checked} checked{/if}"><i class="check"></i><input type="checkbox" name="b" value="{$b.id}"
                id="b{$b.id}" {if $checked}checked="checked"{/if}/> {$b.name}</label>

            </li>
        {/foreach}
            {if $smarty.foreach.b.total gt $column}
            <li>
                <a class="toggler">+ Показать все</a>
            </li>
            {/if}
        </ul>
        {/if}

        {if not empty($filters)}
			{foreach from=$filters item=fname key=fid}
	            {if $fname and $fid neq Model_Filter::CLOTH_BIG_TYPE and $fid neq Model_Filter::TOYS_BIG_TYPE}
	            <strong>{$fname} <i class="toggler"></i></strong>
	            <ul>
	                {foreach from=$vals[$fid] item=vname name=b key=vid}
	                    <li {if $smarty.foreach.b.iteration gt $column
	                        or ($smarty.foreach.b.iteration eq $column and $smarty.foreach.b.total gt $column)}class="hide"{/if}>

	                        {if ! empty($params.f[$fid]) AND in_array($vid, $params.f[$fid])}
	                            {assign var=checked value=1}
	                            {capture assign=chosen}{$chosen}<a rel="f{$fid}_{$vid}" title="{$vname|escape:html}">{$vname}</a>{/capture}
	                        {else}
	                            {assign var=checked value=0}
	                        {/if}

	                        <label title="{$vname}" for="f{$fid}_{$vid}" class="label{if $checked} checked{/if}"><i class="check"></i><input type="checkbox" name="f[{$fid}]" value="{$vid}"
	                        id="f{$fid}_{$vid}" {if $checked}checked="checked"{/if}/> {$vname}</label>
	                    </li>
	                {/foreach}
	                {if $smarty.foreach.b.total gt $column}
	                    <li>
	                        <a class="toggler">+ Показать все</a>
	                    </li>
	                {/if}
	            </ul>
	            {/if}
	        {/foreach}
		{/if}

        {if not empty($max)}
        <strong>Стоимость <i class="toggler"></i></strong>

        <div class="range" rel="{$ratio|default:''}">
            <span class="range-ui">
                <span class="line"><i class="min"></i><i class="max"></i></span>
            </span>
            <span class="min" rel="{$min|default:0}">{$params.pr.0|default:$min|default:0|number_format:0:'':' '}</span> - <span class="max" rel="{$max}">{$params.pr.1|default:$max|number_format:0:'':' '}</span> р.
        </div>
        {else}
            <p>Товаров не найдено<br /><br /><br /></p>
            <!-- NOT FOUND MARKER -->{* не удалять, это маркер автоотслеживания объявлений *}
        {/if}

    </div>

	{if ! empty($params.pr)}{capture assign=pr}{$params.pr.0|default:0}-{$params.pr.1}{/capture}{/if}
	<input id="prpr" name="pr" type="hidden" value="{$pr|default:''}" />{* price *}

    <form action="" method="get" id="param_form">
        <input name="mode" value="{$mode}" type="hidden" id="search_mode" />
        <input name="query" value="{$query}" type="hidden" id="search_query" />
    </form>

</div>
{/strip}