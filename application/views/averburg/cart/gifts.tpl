{$total_pqty = 0}
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
		<tr class="cart-gift-row">
			<td colspan="7" class="name" style="border-width: 0 1px 1px 1px;">
				Выберите подарок по акции &laquo;{if $a->show}<a href="{$a->get_link(0)}" target="_blank">{$a->name}</a>{else}{$a->name}{/if}&raquo;:
			</td>
		</tr>
		{else}
			{assign var=current_present value=$present_goods[$cart->present_variants[$action_id]|current]}
		{/if}
	{/if}

	<tr class="cart-gift-row">    
		{if !$a->is_funded()}
			{if $present_count gt 1}
				<td><abbr abbr="Выберите подарок">?</abbr></td>
			{else}
				<td class='cart-qty'>{$current_present|qty:0}</td>
			{/if}
        {else}
            <td></td>
		{/if}
		<td><img src="/i/averburg/gift.png" alt="{$a->name}" title="{$a->name}" class="img70" /></td>
		<td class="txt-lft" colspan="2">
		{if $present_count gt 1}
			{foreach $select_present as $sp}
				<label><input class="cart-gift-radio" type="radio" 
					{if ( empty( $session_params['select_present'][$a->id] ) and $cart->get_present_id($a->id) eq $sp OR ($cart->get_present_id($a->id) eq 0 AND $sp@index eq 0) ) or ($session_params['select_present'][$a->id] eq $sp)}checked="checked"{/if}
					name="select_present[{$a->id}]" value="{$sp}" /> {$present_goods[$sp]->name}
				</label>
			{/foreach}
		{else}
			{if $a->show}<a href="{$a->get_link(0)}" target="_blank">
				{$current_present->name}</a>
			{else}
				{$current_present->name}
			{/if}
		{/if}
		<script>
			$(function(){
				$('.cart-gift-radio').mladenecradio({
					onClick: function(){
						delivery.sync();
					}
				});
			});
		</script>
		</td>
		<td><abbr style='color: #a4cd00; border-color: #a4cd00;' abbr="{$a->preview}">Подарок<br />к&nbsp;покупке</abbr></td>
		<td>{$a->pq}</td>
		<td>
		{if $a->is_funded()}
			<a class="ico ico-del" abbr="Пометить товар на удаление"></a>
		{/if}
		</td>
	</tr>
{/foreach}
