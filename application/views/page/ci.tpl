Retailer_Name|Retailer_SKU|Manufacturer_Name|Product_Name|UPC|Description|Category|Buy_URL|Image_URL|Availability|Price
{foreach from=$goods item=g}
Младенец.РУ|{$g->upc}|{$g->brand->name|for_ci}|{$g->group_name|for_ci} {$g->name|for_ci}|{$g->upc}|{$g->prop->desc|for_ci}|{$g->section->parent->name}/{$g->section->name}|http://www.mladenec-shop.ru{$g->get_link(0)}|{if $g->prop->img255}{$g->prop->image255->get_url()}{/if}|{if $g->qty}Y{else}N{/if}|{$g->price}
{/foreach}