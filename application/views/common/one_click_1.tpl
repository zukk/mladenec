<form id="one_click" class="ajax" method="post" action="{Route::url('one_click')}">
    <strong>Или заказ в&nbsp;1&nbsp;клик</strong>
    +7 <input class="txt small" type="tel" name="phone" placeholder="(999)123-45-67" /><input disabled="disabled" type="submit" value="Заказать" /><br />
    Менеджер перезвонит Вам, узнает все детали и&nbsp;сам оформит заказ на&nbsp;Ваше имя
</form>
<div id="one_click_message" class="hide">
    <p>Cоздан заказ <strong id="oco_id"></strong> на&nbsp;сумму <strong id="oco_sum"></strong></p>
    <p class="hide" id="oco_clean">Корзина очищена.</p>
    <p>Менеджер перезвонит
        <span class="hide" id="oco_labor">в&nbsp;течение нескольких минут</span>
        <span class="hide" id="oco_weekend">с&nbsp;Вами свяжется в&nbsp;рабочее время с&nbsp;9:00 до&nbsp;18:00</span>
        на&nbsp;номер <span id="oco_phone"></span>.</p>
    <p class="hide" id="oco_show_login">Логин: <strong id="oco_login"></strong> Пароль: <strong id="oco_password"></strong> для&nbsp;управления заказом.</p>
</div>
<script>
    $(document).ready(function() {
        var one_click_form = $('#one_click'), ophone = $('input[name=phone]', one_click_form), do_one_click = ophone.next();
        ophone.mask('(999)999-99-99');
        ophone.on('keyup mouseup touchend', function () {
            do_one_click.prop('disabled', !ophone.val().match(/^\(\d\d\d\)\d\d\d-\d\d-\d\d$/));
        });
        one_click_form.on('submit', function () {
            $.post(one_click_form.prop('action'), {
                phone: ophone.val(),
                good: {$good_id|default:0},
                qty: $('#qty_{$good_id|default:0}').val()
            }, function (data) {
                var msg = $('#one_click_message'), spans = $('span', msg), p = $('p', msg), strong = $('strong', msg), box = $('#cart-wrap');
                if (data.error) {
                    alert(data.error);
                    return;
                }
                $('#oco_id').text('M' + data.order_id);
                $('#oco_phone').text('+7' + data.phone);
                $('#oco_sum').text(data.sum + 'р.');

                // чистим корзину
                if (data.cart_clean) {
                    $('#oco_clean').show();
                    $('#cart').hide();
                }

                var now = new Date(), day = now.getDay(), hour = now.getHours(), weekend = day == 0 || day == 6;

                if ((weekend && (hour >= 21 || hour < 10)) || ( ! weekend && (hour >= 22 || hour < 9))) {
                    $('#oco_weekend').show();
                } else {
                    $('#oco_labor').show();
                }

                ophone.val('').trigger('keyup'); // телефон в форме - убрать

                if ( ! data.sms_sent && data.new_user) {
                    $('#oco_login').text(data.new_user.login);
                    $('#oco_password').text(data.new_user.password);
                    $('#oco_show_login').show();
                }
                if (box.length) {
                    $('#content').replaceWith('<h1>Корзина</h1>' + msg.show().html());
                    $(window).scrollTop(300);
                } else {
                    one_click_form.replaceWith(msg.show());
                }
                //$.fancybox(msg[0], { nextEffect: 'none', prevEffect: 'none', nextSpeed: 0, prevSpeed: 0});
            }, 'json');
            return false;
        });
    });
</script>
