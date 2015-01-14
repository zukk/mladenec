<br />
<div class="cb">
    {if $opts}
    <table class="tt">
    <thead>
    <tr>
        <th class="l"><h3>Варианты доставки</h3></th>
        <th width="15%" class="c">Срок доставки</th>
        <th class="r">Стоимость, примерно</th>
    </tr>
    </thead>
    <tbody>
    {foreach from=$opts item=r name=r}
    <tr{cycle values=' class="odd",'}>
        <td><label class="label" style="height:30px;"><i class="radio"></i><input type="radio" name="tarif_id" value="{$smarty.foreach.r.iteration}" {if $smarty.foreach.r.first}checked="checked"{/if}/> <strong>{$r->company}</strong> {$r->name}</label></td>
        <td class="c">{assign var=d value=$r->day|trim}{$d|default:'нет данных'}</td>
        <td class="r">{$r->price}&nbsp;р. {if $r->pricecash} ({$r->pricecash}&nbsp;р.) - ({$r->transfer}&nbsp;р.){/if}</td>
    </tr>
    {/foreach}
    </tbody>
    </table>
    {else}
        К&nbsp;сожалению, для&nbsp;вашего заказа не&nbsp;удалось автоматически рассчитать стоимость доставки
    {/if}
</div>
<br /><br />
