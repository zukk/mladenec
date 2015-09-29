{if Kohana::$environment eq Kohana::PRODUCTION}
    <script>
        var googlePromotions = [];

        {foreach from=$slider item=s}
        googlePromotions.push({
            id: "{$s->id}",
            name: "{$s->name|escape:html}",
            creative: "Ротатор",
            position: "Главная"
        });

        $(function () {
            $('#promo-{$s->id}').click(function () {
                var href = $(this).attr('href');
                window.dataLayer = window.dataLayer || [];
                dataLayer.push({
                    event: 'promotionClick',
                    ecommerce: {
                        promoClick: {
                            promotions: [
                                {
                                    id: "{$s->id}",
                                    name: "{$s->name|escape:html}",
                                    creative: "Ротатор",
                                    position: "Главная"
                                }
                            ]
                        }
                    },
                    eventCallback: function () {
                        document.location = href;
                    }
                });
                return false;
            });
        });
        {/foreach}
    </script>
{/if}

<div id="index_slider">
	<div class="is_outer">
		<div class="is_inner">
			<div class="is_runner">
				{foreach from=$slider item=s}
                    <a id="promo-{$s->id}" href="{$s->url}" style="background-image:url({$s->src})"></a>
				{/foreach}
			</div>
		</div>
	</div>
    {if count($slider) gt 1}
        <div id="nav"></div>
    {/if}
</div>