{assign var=total_pqty value=0}

{foreach from=$presents key=action_id item=present}

	{assign var=a value=$cart->actions[$action_id]}
	{assign var=pqty value=$a->pq}
	{assign var=total_pqty value=$total_pqty + $pqty}
	{assign var=select_present value=FALSE}
	{assign var=present_count value=0}

	{if not empty($cart->present_variants[$action_id])}
		{assign var=select_present value=$cart->present_variants[$action_id]}
		{assign var=present_count value=count($cart->present_variants[$action_id])}
		{if $present_count gt 1}
		<tr>
			<td colspan="8" class="select-present">
				<b>Выберите подарок по акции</b> &laquo;{if $a->show}<a href="{$a->get_link(0)}" target="_blank">{$a->name}</a>{else}{$a->name}{/if}&raquo;:
			</td>
		</tr>
		{else}
			{assign var=current_present value=$present_goods[$cart->present_variants[$action_id]|current]}
		{/if}
	{/if}

	<tr class="cart-gift-row">    
		{if not $a->is_funded()}
			{if $present_count gt 1}
				<td><abbr abbr="Выберите подарок">?</abbr></td>
			{else}
				<td class="cart-qty">{$current_present|qty:0}</td>
			{/if}
        {else}
            <td></td>
		{/if}
		<td colspan="2"><img src="/i/averburg/gift.png" alt="{$a->name}" title="{$a->name}" class="img70" /></td>
		<td class="txt-lft" colspan="2">
		{if $present_count gt 1}
			{foreach from=$select_present item=sp name=sp}
                {assign var=selected value=$cart->get_present_id($a->id)}
				<label><input class="cart-gift-radio" type="radio" 
					{if ($selected eq $sp) or (not $selected and $smarty.foreach.sp.first)} checked="checked"{/if}
					name="select_present[{$a->id}]" value="{$sp}" /> {$present_goods[$sp]->name}
				</label>
            {/foreach}
            {if $a->is_funded()}
                {* todo - не предлагать этот вариант если мы в последнем этапе акции! *}
                <label><input class="cart-gift-radio" type="radio" name="select_present[{$a->id}]" value="-1" {if not empty($cart->no_presents[$a->id])}checked{/if}/>
                    Отказаться от&nbsp;подарка, продолжить копить баллы</label>
            {/if}
		{else}
			{if $a->show}
                <a href="{$a->get_link(0)}" target="_blank">{$current_present->name}</a>
			{else}
				{$current_present->name}
			{/if}
		{/if}
		</td>
		<td><abbr style="color: #a4cd00; border-color: #a4cd00;" abbr="{$a->preview}">Подарок<br />к&nbsp;покупке</abbr></td>
        <td class="total"><span>0</span> р.</td>
        <td></td>
	</tr>
{/foreach}
