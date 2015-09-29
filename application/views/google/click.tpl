<script>
	var googleGood_{$good->id} = {
		"id": "{$good->id1c}",
		"name": "{addslashes($good->group_name)} {addslashes($good->name)}",
		"price": "{$good->get_price()}",
		"brand": "{$good->brand->name}",
		"category": "{$good->section->name}",
	};
</script>
