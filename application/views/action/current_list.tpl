<div id="breadcrumb">    
    <span itemscope itemtype="http://data-vocabulary.org/Breadcrumb" itemref="breadcrumb-1">
        <a href="/" itemprop="url"><span itemprop="title">Главная</span></a>
    </span>
    &rarr; 
    <span itemprop="child" itemscope itemtype="http://data-vocabulary.org/Breadcrumb" id="breadcrumb-1">
        <a href="{Route::url('action_list')}"><span itemprop="title">Акции</span></a>
    </span>
    &rarr; 
    <span>{$config->actions_header|default:'Акции месяца'}</span>
</div>
<div id="simple">
    <h1>{$config->actions_header|default:'Акции месяца'}</h1>

    <div id="bg_actiontags">
        {foreach from=$actiontags item=actiontag}
            {if in_array($actiontag->url, $tag)}
                <span class="active">
                    {$actiontag->title}
                    <a href="/{$actiontags->url[$actiontag->url]}/{$actiontag->url}" class="delete_tag_link">&#10006;
                    </a>
                </span>
            {else}
                <a href="/{$actiontags->url[$actiontag->url]}" class="tag_link">{$actiontag->title}</a>
            {/if}
        {/foreach}
    </div>
    <br />

    <ul id="action_banner_list">
        {include file='action/current_list_item.tpl' actions=$actions}
    </ul>
	<a class='more'></a>
	<script>
		$(document).ready(function() {
			var $loading = $("<i class='load'></i>"),
			$footer = $('.more'),
			opts = {
				offset: '100%'
			};
			var offset = 0;
			var perPage = {$perPage};
			var working = false;
			$(window).scroll(function(e){
				var sc = window.scrollY ? window.scrollY: document.documentElement.scrollTop;
				
				if( $footer.offset().top <=( sc + $(window).height() + 500 ) ){
				
				if( working )
					return false;
				
				working = true;
				
				offset += perPage;
				
				if( offset >= {$count} )
					return false;
				$footer.after($loading);
				
				$.get('{Route::url('action_list')}?offset=' + offset, function(data) {
					$loading.remove();
					working = false;
					var d = $(data);
					$('#action_banner_list').append(d);
			        $('div.description', d).hide();
					$('div.goods', d).hide();
					$('div.goods_all', d).hide();
					$('p.banner input[type=button]', d).click(function() {
						$(this).parent('p.banner').siblings('div.description').toggle();
						$(this).parent('p.banner').parent('div.action_header').siblings('div.goods, div.goods_all').toggle();
					});
				});
				}
			});
		});		
	</script>
    {if ( not empty($smarty.get.all))}
        <div class="fl cl">
            <a href="{Route::url('action_list')}?all=1">Все акции</a>
        </div>
    {/if}
    <div class="fr cr">
        <a href="{Route::url('action_arhive')}">Архив акций</a>
    </div>
    {include file='action/star.tpl'}
</div>