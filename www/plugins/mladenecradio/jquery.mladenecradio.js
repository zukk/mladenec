(function( $ ){

  $.fn.mladenecradio = function( options ) {  

    var settings = $.extend( {
		size: 24,
		onClick: function(){}
    }, options);

    return this.each(function() {  
		
		var o = $(this), w = settings.size, n = o.attr('name');
		
		if( o.parent().hasClass('mladenecradio') ){
			return;
		}

		var l = o.parent().filter('label');
		
		if( l.length < 1 ){
			l = $('<label></label>').insertAfter(o);
			o.appendTo(l);
		}
		
		l.addClass('mladenecradio').attr('rel', n);
		
		if( $(this).attr('checked') )
			l.addClass('checked');
		
		var i = $('<i></i>').insertBefore(o);
		
		o.hide();
		
		l.click(function(){
			$(".mladenecradio.checked[rel='"+n+"']").removeClass('checked').find('input[type=radio]').removeAttr('checked');
			if( !l.hasClass('checked') ){
				l.addClass('checked');
				o.attr('checked', 'checked');
				settings.onClick(true, o.val());
			}
			else{
				settings.onClick(false, o.val());
				o.removeAttr('checked');
			}
			return false;
		});
    });
  };
})( jQuery );
