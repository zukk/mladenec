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
		                    <li><a href="{$sublink}"><strong>{$ch->name}</strong></a></li>
                        {assign var=subs value=$ch->sub|count}
                        {foreach from=$ch->sub item=n key=k name=n}
                            {if $smarty.foreach.n.iteration lt 7}
                            {if $ch->settings.sub eq Model_Section::SUB_BRAND}
                                <li><a href="{$sublink}?b={$k}">{$n}</a></li>
                            {elseif $ch->settings.sub eq Model_Section::SUB_FILTER}
                                {if $item->id eq Model_Section::CLOTHS_ROOT or $ch->settings.list neq Model_Section::LIST_GOODS}
                                    <li><a href="{$ch->get_link(0, $k)}">{$n}</a></li>
                                {else}
                                    <li><a href="{$sublink}?f{$ch->settings.sub_filter}={$k}">{$n}</a></li>
                                {/if}
                            {/if}
                            {/if}
                        {/foreach}
		                    <li>{if $subs gt 7}<a href="{$sublink}">+ Показать все</a>{/if}
							{if $ch->img_menu}<a href="{$sublink}">{$ch->menu_img->get_img()}</a>{/if}</li>
                        </ul>
                    {/if}
                </li>
                {/foreach}
            </ul>
        </div>
        {/if}
    </div></td>
{/if}
{/foreach}
</tr>
</table>