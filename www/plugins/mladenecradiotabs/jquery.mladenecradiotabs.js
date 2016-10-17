(function( $ ){

  $.fn.mladenecradiotabs = function( options ) {  

    var settings = $.extend( {
		onClick: function(){}
    }, options);

    return this.each(function() {        
		var o = $(this);
		
		if( o.hasClass('mladenecradiotabs') ){
			return;
		}
		
		o.addClass('mladenecradiotabs');
		
		var l = o.find('label'), w = Math.floor( 100 / l.length );
		l.find('input[type=radio]').hide();
		
		l.each(function(){
			var q = $(this), i = q.find('input[type=radio]');
			var d = $('<div></div>').appendTo(o).width(w+'%').append(q);
			
			if( i.attr('checked') ){
				d.addClass('checked');
				settings.onClick(i.val());
			}
			
			d.click(function(){
				
				if( d.hasClass('checked') )
					return false;
				
				o.find('div.checked').removeClass('checked').find('input[type=radio]').removeAttr('checked');
				
				d.addClass('checked');
				i.attr('checked', 'checked');
				settings.onClick(i.val());
				
				return false;
			});
		});
		
		o.after('<div style="clear: both; height: 0;"></div>');
    });
  };
})( jQuery );
