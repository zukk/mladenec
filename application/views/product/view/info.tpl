	{if !empty( $notInSale )}
		<div class="alert alert-warning" style="margin-top: 15px; clear:left;">
		Этого товара нет в наличии {if $notInSale > 0}c {Txt::ru_date($notInSale)}{/if}<br />
			{if ! empty($tags)}
				<strong>С этим товаром искали:</strong> {Model_Tag::links($tags, $prop->tags)}
			{/if}
		</div>
	{/if}
{if $cgood->show}
<div class="tabs mt">
	<div>
		<a class="active t">Описание</a>
		{if ! empty($prop->spoiler_title)}<a class="t">{$prop->spoiler_title}</a>{/if}
		{if ! empty($prop->spoiler2_title)}<a class="t">{$prop->spoiler2_title}</a>{/if}
		{if ! empty($prop->spoiler3_title)}<a class="t">{$prop->spoiler3_title}</a>{/if}
		{if ! empty($serts)}<a class="t">Сертификаты соответствия</a>{/if}
		<small class="r">
			артикул: {$goods[{$prop->id}]->code}
			{if $goods[{$prop->id}]->code1c neq $goods[{$prop->id}]->code}
				<br />артикул Младенец: {$goods[{$prop->id}]->code1c}
			{/if}
		</small>
	</div>
	<div class="tab-content active">
		<div class="txt">
			{$prop->desc}
			{if not empty($filters)}
				{foreach from=$filters key=fname item=vals}
				<p>
					<strong>{$fname}:</strong> {', '|implode:$vals}
				</p>
				{/foreach}
			{/if}

			{if ! empty($tags)}
				<p><strong>С этим товаром искали:</strong> {Model_Tag::links($tags, $prop->tags)}</p>
			{/if}
		</div>
	</div>
	{if ! empty($prop->spoiler_title)}
	<div class="tab-content">
		<div class="txt spoiler oh">
		{$prop->spoiler}
		</div>
	</div>
	{/if}
	{if ! empty($prop->spoiler2_title)}
	<div class="tab-content">
		<div class="txt spoiler oh">
		{$prop->spoiler2}
		</div>
	</div>
	{/if}
	{if ! empty($prop->spoiler3_title)}
	<div class="tab-content">
		<div class="txt spoiler oh">
		{$prop->spoiler3}
		</div>
	</div>
	{/if}
	{if ! empty($serts)}
	<div class="tab-content">
		<div class="txt sert oh">
		{foreach from=$serts item=s}
			<a href="{$s->big->get_img(0)}" title="{$s->name}" rel="sert">{$s->small->get_img()}</a>
		{/foreach}
		</div>
	</div>
	{/if}
</div>
{/if}