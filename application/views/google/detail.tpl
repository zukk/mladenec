<script>
	window.dataLayerDetail = window.dataLayerDetail || {};
	dataLayerDetail = {
		  'products': [{
			"id": "{$good->id1c}",
			"name": "{$good->group_name|escape:'javascript'} {$good->name|escape:'javascript'}",
			"price": "{$good->get_price()}",
			"brand": "{$good->brand->name|escape:'javascript'}",
			"category": "{$good->section->name|escape:'javascript'}"
		   }]
		 };
</script>
