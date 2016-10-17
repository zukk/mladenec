<div class="stars">
    <span class="stars"><span style="width:{$rating*20}%"></span></span>
    <a href="#reviews">{'мнение'|plural:$review_qty}</a>
</div>

{if ! empty($params.total[1])}
    <div class="good">
        {foreach from=$params.total[1] key=name item=qty}<span>+{$qty}</span> {$name}<br />{/foreach}
    </div>
{/if}

{if ! empty($params.total[-1])}
    <div class="bad">
        {foreach from=$params.total[-1] key=name item=qty}<span>-{$qty}</span> {$name}<br />{/foreach}
    </div>
{/if}

{if ! empty($params.total[0])}
    <div class="neutral">
        <strong>Использовать с</strong><br />
        {foreach from=$params.total[0] key=name item=qty}<span>({$qty})</span> {$name}<br />{/foreach}
    </div>
{/if}
