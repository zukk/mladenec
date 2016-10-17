<a href="{Route::url('hitz')}"><i></i>Хиты продаж на Младенец.РУ<i></i></a>
<a class="arr"></a>
{$goods}
<a class="arr"></a>
<div><i></i>
    <table><tr>
    {foreach from=$sections item=i name=s key=k}
        {assign var=subs value=$top_menu[$i.id]->children|array_keys}
        <td class="
        {if $smarty.foreach.s.iteration eq 1 or $smarty.foreach.s.iteration eq 5}o2{/if}
        {if $smarty.foreach.s.iteration eq 2 or $smarty.foreach.s.iteration eq 4}o1{/if}
        {if $smarty.foreach.s.iteration eq 3}o{/if}
        "><a href="{Route::url('hitz')}?c={'_'|implode:$subs}" rel="{$i.id}">{$i.name|replace:' и ':'<br />и&nbsp;'}</a></td>
    {/foreach}
    </tr></table>
</div>
<i class="rama"></i>