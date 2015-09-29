$(document).ready(function () {

    $(document).on('focus', 'a.dec + input', function() { // при фокусе запомним что было
        $(this).attr('oldval', $(this).val());
    });

    $(document).on('change mouseup keyup', 'a.dec + input', function() {
        var max = parseInt($(this).attr('max'));
        $(this).next('a.inc').toggleClass('disabled', $(this).val() >= max).attr('title','Максимально доступно для заказа: ' + max); // больше нельзя
        var minimum = 1;
        if($(this).prev('a.dec').hasClass('min-zero')) {
            minimum = 0;
        }
        $(this).prev('a.dec').toggleClass('disabled', $(this).val() <= minimum); // меньше нельзя
        if( minimum==1 && ($(this).val()==='' || $(this).val()=== '0' )) {
            if($(this).val()==='0') $(this).val($(this).attr('oldval'));
        } else if ($(this).attr('oldval') != $(this).val() ) {
            retotal($(this));
            $(this).attr('oldval', $(this).val());
            if ($(this).closest('#cart_goods').length > 0) { // пересчитать корзину
                cart_recount();
            }
        }
    });
    
    $(document).on('focusout', 'a.dec + input', function() {
        if($(this).val()==='') $(this).val($(this).attr('oldval'));
    });

    $(document).on('click', 'a.dec', function () { // -1
        var item = $(this).next('input'), mode = $(this).parent().find('[name=mode]');
        var val = parseInt(item.val(), 10);
        var qty = parseInt(mode.val(), 10);        
        var min_value =  $(this).hasClass('min-zero') ? 0 : 1;
        if ( ! qty ) {
            qty = 1;
        }

        if (isNaN(val)) val = 0;
        item.val(Math.max(min_value, val - qty));

        item.change();

        return false;
    });

    $(document).on('click', 'a.inc', function () { // +1
        var item = $(this).prev('input'),
            mode = $(this).parent().find('[name=mode]'),
            val = parseInt(item.val(), 10),
            qty = parseInt(mode.val(), 10),
            max = parseInt(item.attr('max'), 10);

        if ( ! qty) qty = 1;

        if (isNaN(val)) val = 0;
        item.val(Math.min(val + qty, max));
        item.change();

        return false;
    });

    $(document).on('selectstart', 'a.inc, a.dec', function() { // выделять ничего не нужно
        return false;
    });
});