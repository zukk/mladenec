{assign var=column value=11}

<div id="breadcrumb">
    <a href="/">Главная</a>
    {if ! empty($parent)} &rarr; {$parent->get_link()}
	    {if ! empty($third_level)}
		    &rarr; <a href="{$section->get_link(0)}">{$section->name}</a>
		{/if}
    {else} |
    {/if}
    <i></i>
</div>
<h1 {if empty($subs)}class="yell"{/if}>{if $third_level}{$third_level}{else}{$section->name}{/if}
{if $section->id eq 29051}<abbr abbr="Вся продукция при&nbsp;доставке перевозится в&nbsp;специальных авто-холодильниках<br /><strong>(от +3 до +5 градусов)</strong>"><img src="/i/ice.png" alt="***" /></abbr>{/if}
</h1>

{if $section->settings.list eq Model_Section::LIST_GOODS && ! empty($subs)}{* есть подкатегориии - показываем их *}

    <table id="subs">
        <tr>
        {foreach from=$subs item=s name=s}
            <td>
                {assign var=link value=$s->get_link(0)}
                <a href="{$link}" class="big">{$s->name}</a><a href="{$link}">{$s->img->get_img()}</a>
                {if ! empty($by_section[$s->id])}
                <ul>
                    {foreach from=$by_section[$s->id] key=brand_id item=qty name=m}
                    {assign var=b value=$brands[$brand_id]}
                    {if $b}
                        {if ! strpos($link, '#!')}
                            {capture assign=link2}{$link}#!b={$b->id};{/capture}
                        {else}
                            {capture assign=link2}{$link}b={$b->id};{/capture}
                        {/if}
                        <li {if $smarty.foreach.m.iteration gt $column
                            or ($smarty.foreach.m.iteration eq $column and $smarty.foreach.m.total gt $column)}class="hide"{/if}>
                            <a href="{$link2}" title="{$b->name}">{$b->name}</a>
                            <abbr abbr="Ассортимент товаров">{$qty}</abbr>
                        </li>
                    {/if}
                    {/foreach}
                    {if $smarty.foreach.m.total gt $column}
                        <li><a class="toggler">+ Показать все</a></li>
                    {/if}
                </ul>
                {/if}

            </td>
            {if $smarty.foreach.s.iteration % 3 eq 0}
        </tr>
        <tr>
            {/if}
        {/foreach}
        </tr>
    </table>

{/if}

{if $section->settings.list eq Model_Section::LIST_FILTER and empty($third_level)}{* показываем меню из значений фильтра*}
    <div id="product_list">
        <table id="subs">
            <tr>
                {foreach from=$filter_values item=s name=s}
                <td>
                    {assign var=link value=$section->get_link(0, $s->id)}

                    <a href="{$link}" class="big">{$s->name}</a><a href="{$link}">{$s->image->get_img()}</a>
                    {if ! empty($by_section[$s->id])}
                        <ul>
                            {foreach from=$by_section[$s->id] key=brand_id item=qty name=m}
                                {assign var=b value=$brands[$brand_id]}
                                {if $b}
                                    {if ! strpos($link, '#!')}
                                        {capture assign=link2}{$link}#!b={$b->id};{/capture}
                                    {else}
                                        {capture assign=link2}{$link}b={$b->id};{/capture}
                                    {/if}
                                    <li {if $smarty.foreach.m.iteration gt $column
                                    or ($smarty.foreach.m.iteration eq $column and $smarty.foreach.m.total gt $column)}class="hide"{/if}>
                                        <a href="{$link2}" title="{$b->name}">{$b->name}</a>
                                        <abbr abbr="Ассортимент товаров">{$qty}</abbr>
                                    </li>
                                {/if}
                            {/foreach}
                            {if $smarty.foreach.m.total gt $column}
                                <li><a class="toggler">+ Показать все</a></li>
                            {/if}
                        </ul>
                    {/if}

                </td>
                {if $smarty.foreach.s.iteration % 3 eq 0}
            </tr>
            <tr>
                {/if}
                {/foreach}
            </tr>
        </table>
    </div>

{/if}

{$search_result|default:''} {* товары *}

{if empty($hide_text) and ($section->settings.list eq Model_Section::LIST_TEXT or empty($search_result))}
<div class="txt">
{$section->text}
</div>
{/if}

{if ! empty($tags)}
    <div id="tags">
        {foreach from=$tags item=name key=code name=t}
        <a href="/{$code}">✔ {$name}</a>

        {if $smarty.foreach.t.iteration eq 33}
        <div class="hide" id="hidden_tags">
            {/if}

            {/foreach}
            {if $smarty.foreach.t.total gt 33}
        </div>
        <a class="toggler abbr" rel="hidden_tags" style="display:block; clear:both; float:left; padding:20px 0 0;">показать все</a>
        {/if}
    </div>
{/if}
