// incdec plugin
(function ($) {
    $.fn.extend({
        incdec: function (options) {
			var settings = $.extend( {
				onInc: function(){},
				onDec: function(){},
				onChange: function(){},
			}, options);
            return this.each(function () {
                var item = $(this);

                if (item.parent().hasClass('incdeced'))
                    return false;

                item.parent().addClass('incdeced');

                var mode = item.parent().find('select');

                if ( ! mode.length)
                    mode = item.parent().find('[name=mode]');

                item
					.focus(function () { // при фокусе запомним что было
	                    $(this).attr('oldval', $(this).val());
	                })
					.bind('mouseup keyup', function () {
						if( $(this).attr('oldval') != $(this).val() ){
							settings.onChange(item);
							$(this).attr('oldval', $(this).val());
						}
					})
					/* .bind('keyup', function (e) {
						settings.onChange(item);
					}) 
					.change(function(){
						settings.onChange(item);
					}) */;

                var a = $('<a class="dec">-</a>')
                    .bind('selectstart', function () {
                        return false;
                    })
                    .click(function () {
						if( settings.onDec(item) !== false ){
							var val = parseInt(item.val(), 10);
							var qty = parseInt(mode.val(), 10);

							if (isNaN(val)) val = 0;
							item.val(Math.max(0, val - qty));
							retotal(item);
							settings.onDec(item);
						}
                    });
                $(this).before(a);

                a = $('<a class="inc">+</a>')
                    .bind('selectstart', function () {
                        return false;
                    })
                    .click(function () {
						if( settings.onInc(item) !== false ){
							var val = parseInt(item.val(), 10);
							var qty = parseInt(mode.val(), 10);
							if (isNaN(val)) val = 0;
							item.val(val + qty);
							retotal(item);
						}
                    });
                $(this).after(a);
            })
        }
    })
})(jQuery);
