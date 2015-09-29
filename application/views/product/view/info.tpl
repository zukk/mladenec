{if not empty($notInSale)}
    <div class="alert alert-warning fl" style="margin-top: 15px; clear:left;">
    Этого товара нет в&nbsp;наличии {if $notInSale > 0}c {Txt::ru_date($notInSale)}{/if}<br />
        
    </div>
{/if}

{if $cgood->show}
	{include file='product/view/tabs.tpl'}
{/if}