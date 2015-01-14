<strong>{$goods|count}</strong>
<a href="/od-men/goods" data-fancybox-type="ajax" class="green btn btn-round">+ Добавить</a>
<table>
{foreach from=$goods item=g}
<tr>
    <td><a href="/od-men/good/{$g->id}" target="_blank">{$g->id}</a></td>
    <td>{if $g->show}<span class="green">отобр.</span>{else}<span class="red">скр.</span>{/if}</td>
    <td>{$g->code}</td>
    <td>{$g->group_name} {$g->name}</td>
    <td>{$g->qty}&nbsp;шт.</td>
    <td>{$g->price}&nbsp;р.</td>
    <td><input type="hidden" name="goods[]" value="{$g->id}" /><input type="button" class="btn btn-small btn-red trdel" value="удалить" /></td>
</tr>
{foreachelse}
<tr><td>Пока не выбрано ни одного товара<td></tr>
{/foreach}
</table>

