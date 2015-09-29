<h3 class="h">Для продолжения оформления заказа:</h3>
<table class="cart-login">
    <col width="33%" />
    <col width="33%" />
    <col width="34%" />
    <thead>
        <tr>
            <th>Авторизуйтесь</th>
            <th>Зарегистрируйтесь</th>
            <th>{if Model_User::can_one_click()}Купите в&nbsp;1&nbsp;клик{/if}</th>
        </tr>
    </thead>
    <tbody>
    <tr>
        <td class="active" id="cart-login">
            <div>
            {include file="user/login.tpl"}
            </div>
        </td>
        <td>
            <div>
            {include file="user/registration.tpl"}
            </div>
        </td>
        <td>
            <div>
            {if Model_User::can_one_click()}
            {include file="common/one_click.tpl"}
            {/if}
            </div>
        </td>
    </tr>
    </tbody>
</table>

<script>
$(document).ready(function() {
    $(".cart-login td").click(function(){
        $(".cart-login td").removeClass("active");
        $(this).addClass("active");
    });
});
</script>