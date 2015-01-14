<div id="index_slider">
	<div class="is_outer">
		<div class="is_inner">
			<div class="is_runner">
				{foreach from=$slider item=s}
				<a href="{$s->url}" style="background-image:url({$s->src})"></a>
				{/foreach}
			</div>
		</div>
	</div>
	<div id="nav"></div>
</div>