$(document).ready(function () {
    $('#catalog td > div > a').each(function () {
        var text = $(this).html();
        $(this).html('<span class="catalog_ie_shadow">' + text + '</span>' + text);
        $(this).css('color', '#7eb20a');
        $(this).css('filter', 'progid:DXImageTransform.Microsoft.Blur(pixelradius=1,makeShadow=1,ShadowOpacity=0.3);');
    });
});