{if empty( $ga_list )}
	{assign var=ga_list value="category"}
{/if}

{if not empty($goods) and is_array($goods)}
<script>
	window.dataLayer = window.dataLayer || [];
	
	var googleImpressionsList = "{$ga_list}";
	if(typeof(dataLayerDetail) != "undefined") {
		googleImpressionsList = "item";
	}
	
	var impressions = [
		{foreach from=array_values($goods) item=good key=i}
		{
		  "id": "{$good->id1c}",
		  "name": "{$good->group_name|escape:'javascript'} {$good->name|escape:'javascript'}",
		  "price": "{$good->get_price()}",
		  "brand": "{$good->brand->name|escape:'javascript'}",
		  "category": "{$good->section->name|escape:'javascript'}",
		  "position": {$i},
		  "list": googleImpressionsList
		},
		{/foreach}
	];
	
	var ecommerce = {
		"currencyCode": "RUR",
		"impressions": impressions
	};
	
	if( typeof( dataLayerDetail ) != "undefined" ){
		ecommerce['detail'] = dataLayerDetail;
	}
	
	if( typeof( googlePromotions ) != "undefined" ){
		ecommerce['promoView'] = {
			"promotions": googlePromotions
	    };
	}
	
	var impressionsObject = {
	  'userId': uid,
	  "ecommerce": ecommerce,
	  {if ! empty( $ga_ajax )}
		"event" : "dataload"
	  {else}
		"event" : "dataload"
	  {/if}
    };

	// dataLayerDetail
	dataLayer.push(impressionsObject);
</script>
{/if}