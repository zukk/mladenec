{if not empty($cart->coupon)}
    {if is_array($cart->coupon)}{assign var=coupon value=$cart->coupon.name}{else}{assign var=coupon value=$cart->coupon}{/if}
{/if}

{if not empty($coupon) && empty($cart->coupon_error)}

    <div class="cart-prm active">
        Использован ПРОМОКОД <b>{$coupon}</b>
        <input type="hidden" name="coupon" id="coupon" value="{$coupon}" />
        <a id="cart-coupon-remove" class="do no">не использовать</a>
    </div>

{else}

    {if not empty($cart->coupon_error)}<p class="coupon_error">Промокод не сработал: {$cart->coupon_error}</p>{/if}

    <div class="cart-prm">
    {if empty($coupon)}
        <abbr abbr="Если Вы хотите получить скидку, введите код купона"><b>ПРОМОКОД:</b></abbr>
        <input id="coupon" type="text" name="coupon" value="{$coupon|default:''}" />
        <a href="#" class="undln" id="cart-coupon">Применить</a>
    {/if}
    </div>

{/if}
