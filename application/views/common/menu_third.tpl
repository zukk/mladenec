{* левое меню для категорий верхнего уровня - повторяет выпадающее, но полное *}
{strip}
    {if not isset($column)}{assign var=column value=8}{/if}
    <div id="menu">
        <div id="ff">
            {foreach from=$subs_filter item=f name=f}
                <strong><a href="{$section->get_link(0, $f.id)}">{$f.name}</a></strong>

                {if $f.sub}
                <ul class="ms">
                    {foreach from=$f.sub item=sub key=k name=n}
                        <li class="item {if $smarty.foreach.n.iteration gt $column}hide{/if}">
                            <a href="{$sub.href}">{$sub.name|trim}<small>{$sub.qty}</small></a>
                        </li>
                    {/foreach}
                    {if $smarty.foreach.n.total gt $column}
                        <li><a class="toggler">+ Показать все</a></li>
                    {/if}
                </ul>
                {/if}
            {/foreach}
        </div>
    </div>
{/strip}