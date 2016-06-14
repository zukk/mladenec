{assign var=td value=0}
{assign var=half value=count($top_menu)/2}
<table id="catalog">
<tr>
{foreach from=$top_menu item=item name=i}
{if not empty($item->id)}
    <td {if $smarty.foreach.i.last}class="last"{/if}><div class="{if $td gt $half}r{else}l{/if}{if $item->setting('new')} new{/if}">
        {assign var=td value=$td+1}
        <a href="{$item->get_link(0)}">{$item->name|replace:' и ':'<br />и&nbsp;'|replace:'Для ':'Для&nbsp;'}</a>
        {if not empty($item->children)}
        <div>
            <ul>
                {foreach from=$item->children item=ch key=kk}
                    {assign var=sublink value=$ch->get_link(0)}
                    <li><a href="{$sublink}">{$ch->name}</a>
                    {if not empty($ch->sub)}
	                    <ul>
		                    <li><a href="{$sublink}">{$ch->name}</a></li>
                            {assign var=subs value=$ch->sub|count}
                            {foreach from=$ch->sub item=n key=k name=n}
                            {if $smarty.foreach.n.iteration lte 7}
                                <li><a href="{$n.href}">{$n.name}</a></li>
                            {/if}
                            {/foreach}
		                    <li>{if $subs gt 7}<a href="{$sublink}">+ Показать все</a>{/if}
							{if $ch->img_menu}<a href="{$sublink}">{$ch->menu_img->get_img()}</a>{/if}</li>
                        </ul>
                    {/if}
                    </li>
                {/foreach}
                {if $item->id eq 29890}
                    <li id="bike_button"><a href="/det-transport">Детский транспорт</a></li>
                {/if}
                {if $item->id eq 29429}
                    <li id="holika-holika"><a href="/holika">Холика</a></li>
                {/if}

            </ul>
        </div>
        {/if}
    </div></td>
{/if}
{/foreach}
</tr>
</table>
