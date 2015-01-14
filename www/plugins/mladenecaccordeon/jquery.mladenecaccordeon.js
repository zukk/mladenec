(function( $ ){

  $.fn.mladenecaccordeon = function( options ) {  

    var settings = $.extend( {
		onClick: function(){}
    }, options);

    return this.each(function() {        

		var o = $(this);
		
		if( o.hasClass('mladenecaccordeon') ){
			return;
		}
		o.addClass('mladenecaccordeon');
		
		var h = o.find(' > div > span');
		var c = o.find(' > div > div');

		o.find('> div').each(function(){
			if( !$(this).hasClass('active') ){
				$(this).find('> div').slideUp(0);
			}
		});
		h.click(function(){
			
			var p = $(this).parent();
			if( p.hasClass('active') ){
				return false;
			}
			
			if( settings.onClick(p) ){
				
				var opened = o.find(' > div.active');

				if( opened.length ){
					opened.find('> div').slideUp(function(){
						opened.removeClass('active');
					});
				}

				p.addClass('active').find('> div').slideDown();
			}
			else{
				return false;
			}
		});
    });
  };
})( jQuery );
