<script>
window.dataLayer = window.dataLayer || [];
dataLayer.push({
    userId: {$o->user_id},
    ecommerce: {
        purchase: {
            actionField: {
                id: '{$o->id}',
                affiliation: '{$vitrina}',
                revenue: '{$o->price}',
                shipping: '{$o->price_ship}'
            },
            products: [
            {foreach from=$order_goods item=g name=n}
                {if $g->price gt 0}
                    {
                    id: '{$o->id}',
                    name: '{$g->group_name|escape:'javascript'} {$g->name|escape:'javascript'}',
                    price: '{$g->price}',
                    category: '{$g->section->name|escape:'javascript'}',
                    brand: '{$g->brand->name|escape:'javascript'}',
                    quantity: '{$g->quantity}'
                    }{if ! $smarty.foreach.n.last},{/if}
                {/if}
            {/foreach}
            ]
        }
    },
    event: 'transsuccess'
});
</script>