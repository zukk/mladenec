<strong>{$goods|count}</strong>
{assign var=rand value=rand(1,100000)}
<a href="{Route::url('admin_goods')}?rand={$rand}{if isset($discount)}&discount={$discount}{/if}" data-fancybox-type="ajax" class="green btn btn-round" rel="chose{$rand}"
   {if isset($discount)} data-discount="{$discount}"{/if}>+ Добавить</a>
<table id="chose{$rand}" {if not empty($mode)}class="goods_b"{/if} {if isset($discount)} data-discount="{$discount}"{/if}>
{foreach from=$goods item=g}
<tr>
    <td><a href="/od-men/good/{$g->id}" target="_blank">{$g->id}</a></td>
    <td>{if $g->show}<span class="green">отобр.</span>{else}<span class="red">скр.</span>{/if}</td>
    <td>{$g->code} | {$g->id1c}</td>
    <td>{$g->group_name} {$g->name}</td>
    <td>{$g->qty}&nbsp;шт.</td>
    <td>{$g->price}&nbsp;р.</td>
    <td><input type="hidden" name="goods{if not empty($mode)}_b{/if}{if isset($discount) and $discount != ''}[{$discount}]{/if}[]" value="{$g->id}" /><input type="button" class="btn btn-small btn-red trdel" value="удалить" /></td>
</tr>
{foreachelse}
<tr><td>Пока не выбрано ни одного товара<td></tr>
{/foreach}
</table>

