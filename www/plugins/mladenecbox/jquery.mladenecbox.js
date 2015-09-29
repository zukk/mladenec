(function($) {
    $.fn.mladenecbox = function(options) {

        var settings = $.extend({
            size: 30,
            border: 2,
            background: '#fff',
            onClick: function() { }
        }, options);

        return this.each(function() {

            var o = $(this), f, p, w = settings.size;

            var _ = {
                init: function(){

                    p = o.parent().filter('label');

                    if( ! p.length ){
                        p = $('<label></label>').insertAfter(o);
                        p.append(o);
                    }

                    p.addClass('mladenecbox-label').css({
                        cursor: 'pointer'
                    });

                    f = $('<div class="mladenecbox-face"><div class="mladenecbox-face-img"></div></div>').insertBefore(o);
                    o.hide();

                    if( ! o.prop('checked') ){
                        o.val('0');
                    } else{
                        p.addClass('checked');
                        o.val('1');
                    }

                    o.prop('checked', true);
                },
                setCss: function(){
                    var fw = w - ( settings.border * 2 );

                    p.css({
                        lineHeight: w + 'px',
                        paddingLeft: Math.floor(w * 1.5) + 'px'
                    });
                    f.css({
                        width: fw,
                        height: fw,
                        marginTop: -Math.floor(w * .5) + 'px',
                        borderWidth: settings.border + 'px',
                        background: settings.background
                    });
                },
                setClicks: function(){
                    p.click(function(){
                        if (p.hasClass('checked')) {
                            p.removeClass('checked');
                            o.val('0');
                            settings.onClick(false);
                        } else {
                            p.addClass('checked');
                            o.val('1');
                            settings.onClick(true);
                        }
                        return false;
                    });
                }
            };

            if (o.hasClass('mladenecbox')) {
                return;
            }
            o.addClass('mladenecbox');

            _.init();
            _.setCss();
            _.setClicks();
        });
    };
})(jQuery);
