<a id="cart" href="/personal/basket.php" title="{if $cart->get_qty() eq 0}Корзина пуста, добавьте товары{else}Оформить заказ{/if}">
    <span><i></i></span>
    <span>
        <small class="status">
            Cтатус: <abbr id="abbr" class="info" abbr="Условия получения скидки <strong>Любимый клиент</strong><ul>
                <li>Заказ от&nbsp;4&nbsp;500&nbsp;руб. (только на&nbsp;этот заказ)</li>
                <li>Общая сумма заказов от&nbsp;20&nbsp;000&nbsp;руб. (на&nbsp;все последующие заказы)</li></ul>">{if $cart->status_id}Любимый клиент{else}Обычный{if empty($user->id)} (не вошли){/if}{/if}</abbr>
        </small>

        <strong>{$cart->get_qty()}</strong>
        <ins>{$cart->get_total()|price}</ins>

        <span id="just"{if empty($cart->added)} class="hide"{/if}>Товар положен в&nbsp;корзину</span>
    </span>
</a>

{literal}
<script type="text/javascript">
$(document).ready(function() {
    if ( ! $('#just').hasClass('hide')) {

        if ($('.fancybox-inner').length) { // есть окошко - переносим блок в него
            $('.fancybox-inner').append($('#just').detach());
        }
        $('#just').fadeOut(3000, function() {$(this).remove()});
    }
});
</script>
{/literal}
