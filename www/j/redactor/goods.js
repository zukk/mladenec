if (!RedactorPlugins) var RedactorPlugins = {};
 
RedactorPlugins.goods = {
	init: function(){
		var o = this;
		this.buttonAddSeparator();
		this.buttonAdd('justify', 'Товары', function(redactor, button){
			
			// такой хак чтобы кнопка сразу отжалась
			button.addClass('redactor_act');
			button.parents('.redactor_toolbar').next()
			$.get('/od-men/ajax/promo', function(data){
				$.fancybox.open(data);
				setTimeout(function(){
					$(".fancybox-inner a[href^='/od-men/promo/']").click(function(e){
						e.preventDefault();
						e.stopPropagation();
						var id = $(this).attr('href').replace(/\/od\-men\/promo\//g,'');
						
						o.insertHtml('[promo='+id+']');
						$.fancybox.close();
						return false;
					});
				},600);
			});
			return false;
		});
	},
	show: function(){
		console.log('myplugin show');
	}
};
