<strong>{$goods|count}</strong>
<span id="check_all" class="btn btn-round" style="cursor:pointer;">Отметить все</span> <a href="/od-men/goods" data-fancybox-type="ajax" class="green btn btn-round">+ Добавить</a>
<table>
{foreach from=$goods item=g}
<tr>
    <td><input class="action_visible_good" type="checkbox" {if ! empty($shown_goods[$g->id])} checked="checked"{/if} name="goods_show[]" value="{$g->id}" title="Отображать на странице" /></td>
    <td><a href="/od-men/good/{$g->id}" target="_blank">{$g->group_name} {$g->name} [{$g->code}]</a></td>
    <td><a href="/od-men/good/{$g->id}" target="_blank">{$g->qty}&nbsp;шт.</a></td>
    <td><a href="/od-men/good/{$g->id}" target="_blank">{$g->price}&nbsp;р.</a></td>
    <td><input type="hidden" name="goods[]" value="{$g->id}" /><input type="button" class="btn btn-small btn-red trdel" value="удалить" /></td>
</tr>
{foreachelse}
<tr><td>Пока не выбрано ни одного товара<td></tr>
{/foreach}
</table>

