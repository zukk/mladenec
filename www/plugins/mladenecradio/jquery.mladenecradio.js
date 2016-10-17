(function($) {
    $.fn.mladenecradio = function(options) {

        var settings = $.extend({
		    size: 24,
		    onClick: function() { }
        }, options);

        return this.each(function() {
		
            var o = $(this), n = o.attr('name');

            if (o.parent().hasClass('mladenecradio') || o.hasClass('hide')) { return; }

            var l = o.parent().filter('label');
            if ( ! l.length) {
                l = $('<label></label>').insertAfter(o);
                o.appendTo(l);
            }
            l.addClass('mladenecradio').attr('rel', n);

            if ($(this).prop('checked')) l.addClass('checked');
            var i = $('<i></i>').insertBefore(o);
            o.hide();

            l.click(function() {
                var radios = $("input[name=\"" + n + "\"]");
                radios.parent('label').removeClass('checked');
                radios.prop('checked', false);

                l.addClass('checked');
                o.prop('checked', true);
                settings.onClick(radios.filter(':checked').val(), o); // value + input

                return false;
            });
        });
    }
})(jQuery);
