<div id="breadcrumb"><a href="/">Главная</a> | <a href="{Route::url('action_list')}">Акции</a></div>
<div id="simple">
    <h1>{$config->actions_header|default:'Акции месяца'}</h1>
    <ul id="action_banner_list">
        {include file='action/current_list_item.tpl' actions=$actions}
    </ul>
	<a class='more'></a>
	<script>
		$(document).ready(function() {
			var $loading = $("<div class='loading'><p>Загрузка&hellip;</p></div>"),
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
				
				$.get('/actions/current?offset=' + offset, function(data) {
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
    {if ( !empty($smarty.get.all))}
        <div class="fl cl">
            <a href="{Route::url('action_current_list')}?all=1">Все акции</a>
        </div>
    {/if}
    <div class="fr cr">
        <a href="{Route::url('action_arhive')}">Архив акций</a>
    </div>
    {include file='action/star.tpl'}
</div>