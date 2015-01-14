<div id="menu">
	<div id="ff">
		<ul>
		{foreach from=$vals item=v key=k}
			<li><label data-url="{$section->get_link(0, $k)}" title="{$v}" class="label">{$v}</label></li>
		{/foreach}
		</ul>
		<br />
	</div>
</div>