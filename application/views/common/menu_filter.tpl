<div id="menu">
	<div id="ff">
		<ul>
		{foreach from=$vals item=v key=k}
			<li><label data-url="{$section->get_link(0, $k)}" title="{$v.name}" class="label">{$v.name} <small>{$v.qty}</small></label></li>
		{/foreach}
		</ul>
		<br />
	</div>
</div>