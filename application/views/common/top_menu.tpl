<style>
    #zima_menu {
        background:none;
    }
    #zima_menu a {
        letter-spacing: 1px;
        height: 37px;
        margin: 10px 8px 0 !important;
        background: #fff url(/images/menu_zima.png) no-repeat 0 -13px;
        border-radius: 5px;
        line-height: 37px !important;
        font-size: 15px  !important;
        padding: 0 0 0 47px !important;
    }
    #japan_menu {
        background:none;
    }
    #japan_menu a {
        letter-spacing: 1px;
        height: 37px;
        margin: 10px 8px 0 !important;
        background: #fff url(/images/menu-japan.png) no-repeat 0 0;
        border-radius: 5px;
        line-height: 37px !important;
        padding: 0 0 0 37px !important;
    }
    #japan_menu {
        height:37px;
    }
</style>
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
                    <li id="zima_menu"><a href="/catalog/progulka-i-puteshestvie/zima">Зимние забавы</a></li>
                {/if}
                {if $item->id eq 29429}
                    <li id="japan_menu"><a href="/jp_kr_cosmetics">Японская&nbsp;косметика</a></li>
                {/if}
            </ul>
        </div>
        {/if}
    </div></td>
{/if}
{/foreach}
</tr>
</table>
