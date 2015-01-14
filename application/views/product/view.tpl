<div id="breadcrumb">
    <a href="/">Главная</a> &rarr;
    {$parent->get_link()} &rarr;
    {$section->get_link()}
    {if $cgood->is_cloth()}
        &rarr; <a href="{$section->get_link(0, $big_filter.id)}">{$big_filter.value}</a>
    {/if}
    {if $section->id eq 29051}<abbr abbr="Вся продукция при&nbsp;доставке перевозится в&nbsp;специальных авто-холодильниках<br /><strong>(от +3 до +5 градусов)</strong>"><img src="/i/ice.png" alt="***" /></abbr>{/if}
</div>

{assign var=images value=$cgood->get_images()}
{capture assign=good_name}{$group->name} {if not $group->good}{$goods[{$prop->id}]->name}{/if}{/capture}

<div id="ajax-product-card">
    {include file='product/view/inner.tpl'}
</div>

{if ! empty($frequent)}
<div class="cl">
    <h2 class="h1">{$slider_header|default:'С этим товаром также заказывают:'}</h2>
	{assign var=rel value="/frequent/"|cat:$prop->id}
	{include file='common/goods_slider.tpl' total=$cgood->totalFrequently goods=$frequent rel=$rel}
	{if ! empty($slider_time)}<div style="margin:5px 33px 0;">{$slider_time}</div>{/if}
</div>
{/if}

<script>
var good_id = {$prop->id};
{literal}
$(document).ready(function() {
    $('#stats_for').trigger('change');
    $('#reviews_for').trigger('change');
});
</script>
<script src="/j/zoombox.js"></script>
{/literal}

{if not empty($goods[{$prop->id}]->upc)}
    <script src="http://cts.channelintelligence.com/321604975_landing.js"></script>
{/if}
