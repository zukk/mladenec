var zoombox_clickable = function () {
    $('.zoombox_st').children().click(function () {
        $(this).siblings().removeClass('zoombox_st_active');
        $(this).addClass('zoombox_st_active');

    });
    $('.zoombox_st img').click(function (zbLarge) {
        var index = $(this).index('.zoombox_st img');
        $('.zoombox_roll img').removeClass('zoombox_thumb_active');
        $('.zoombox_roll img:not([class="zoombox_thumb_active"])').hide();
        $('.zoombox_roll img').eq(index).addClass('zoombox_thumb_active').show();
        if (zbLarge.length) {
            $('.zoombox_large img').removeClass('zoombox_large_active').hide();
            $('.zoombox_large img').eq(index).addClass('zoombox_large_active').show();
        }
    });

    $('.zoombox_thumb img').mouseenter(function (event) {
        var target = $(event.target);
        var big = target.attr('rel');
        if (big) {
            $('.zoombox_large').html('<img src="' + big + '" />');
            $('.zoombox_large').show();
            $('.zoombox_magnifier').show();
            $('.zoombox_magnifier_icon').hide();
        }
        event.stopPropagation();
    });
    $('.zoombox_thumb').mouseleave(function () {
        $('.zoombox_large').hide();
        $('.zoombox_magnifier').hide();
        $('.zoombox_magnifier_icon').show();
    });
    $(".zoombox_thumb").mousemove(function (e) {
        var parentOffset = $(this).parent().offset();

        var relX = e.pageX - parentOffset.left;
        var relY = e.pageY - parentOffset.top;
        var zmWidth = $(".zoombox_magnifier").outerWidth();
        var zmHeight = $(".zoombox_magnifier").outerHeight();
        var zThumbWidth = $(".zoombox_thumb").width();
        var zThumbHeight = $(".zoombox_thumb").height();

        var maxPosX = zThumbWidth - zmWidth;
        var maxPosY = zThumbHeight - zmHeight;

        var maxLargeX = $(".zoombox_large img").width() - $(".zoombox_large").width();
        var maxLargeY = $(".zoombox_large img").height() - $(".zoombox_large").height();

        var zLargeProportionX = maxLargeX / maxPosX;
        var zLargeProportionY = maxLargeY / maxPosY;

        var posX = relX - zmWidth / 2;
        var posY = relY - zmHeight / 2;

        if (posX < 0) {
            posX = 0;
        }
        if (posY < 0) {
            posY = 0;
        }
        if ((posX + zmWidth) > zThumbWidth) {
            posX = zThumbWidth - zmWidth;
        }
        if ((posY + zmHeight) > zThumbHeight) {
            posY = zThumbHeight - zmHeight;
        }

        var zLargePosX = 0 - posX * zLargeProportionX;
        var zLargePosY = 0 - posY * zLargeProportionY;

        $('.zoombox_magnifier').css("left", posX);
        $('.zoombox_magnifier').css("top", posY);
        $('.zoombox_large img').css("left", zLargePosX);
        $('.zoombox_large img').css("top", zLargePosY);
    });
};

var zoombox_init = function() {
    zoombox_clickable();
    $('.zoombox_st img').first().addClass('zoombox_st_active');

    $('.zoombox_large img').first().addClass('zoombox_large_active');

    setInterval(function () {
        var index = $('.zoombox_st img.zoombox_st_active').index('.zoombox_st img');
        if (index >= ($('.zoombox_st img').size() - 1)) {
            index = 0;
        }
        else {
            index++;
        }
        if ($('.zoombox_large:hidden').size()) {
            $('.zoombox_st img').eq(index).trigger('click');
        }
    }, 5000);

    $('.zoombox_st').click(function (event) {
        event.stopPropagation();
    });
};
var zoombox_fancybox = function() {

    $('.zoombox_roll,.zoombox_magnifier').click(function () {
        // var index = $('.zoombox_st img.zoombox_st_active').index('.zoombox_st img');
        var zbLarge = [];
        $('.zoombox_roll img').each(function (i) {
            zbLarge[i] = $(this).attr('rel');
        });
        $.fancybox(zbLarge, {/* index:index, */ nextEffect: 'none', prevEffect: 'none', nextSpeed: 0, prevSpeed: 0});
    });
}

$(document).ready(function() {
    if ( ! touchable) {
        zoombox_init();
    } else {
        $('.zoombox_roll').css({cursor: 'pointer'});
    }
    zoombox_fancybox();
});