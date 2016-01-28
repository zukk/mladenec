<script>
$(document).on('ready', function () {
    $('#email_approve').click(function () {
        var me = $(this);
        $.post('{Route::url('email_approve')}', function (data) {
            me.html(data);
        });
    })
});
</script>

<h1>Личный кабинет</h1>

<div class="tabs mt">

    {include file='user/personal.tpl' active='user'}

    <div class="tab-content active">
        <form action="" method="post" class="ajax cols">
            <div class="half">

                <label class="l" for="email">E-mail<sup>*</sup></label><input id="email" name="email" class="txt" value="{$user->email}" />
                {if Valid::email($user->email)}
                <p class="cb">
                    {if $user->email_approved}
                        <span class="ok">email подтвержден</span>
                    {else}
                        <span class="no">email не подтвержден</span><br />
                        <a id="email_approve" class="abbr ajax">выслать письмо с&nbsp;подтверждением</a>
                    {/if}
                </p>
                {/if}

                <label class="l" for="name">Имя<sup>*</sup></label><input id="name" name="name" class="txt" value="{$user->name}" />
                <label class="l" for="sname">Отчество</label><input id="sname" name="second_name" class="txt" value="{$user->second_name}" />
                <label class="l" for="lname">Фамилия</label><input id="lname" name="last_name" class="txt" value="{$user->last_name}" />
                
				<label class="l" for="phone">Телефон<sup>*</sup></label>
                    <input type="tel" id="phone" name="phone" class="txt" value="{$user->phone}" />

                {if empty($user->phone2) && $user->phone && Txt::phone_is_mobile($user->phone)}
                    {assign var=mobile_phone value=$user->phone}
                {elseif ($user->phone2 && Txt::phone_is_mobile($user->phone2))}
                    {assign var=mobile_phone value=$user->phone2}
                {/if}

                <label class="l" for="phone2">Телефон для SMS</label>
                    <input type="tel" id="phone2" name="phone2" class="txt" value="{$mobile_phone|default:''}" />

                {*include file="user/phone.tpl"*}

            </div>

            <div class="half">

                <label class="label"><i class="check"></i><input name="sub" type="checkbox" {if $user->sub}checked="checked"{/if} /> Получать новости и&nbsp;выгодные предложения по&nbsp;email и&nbsp;СМС</label>

                {assign var=coupon value=Model_Coupon::for_user($user->id)}
                {if ! empty($coupon)}
                    <p>Мы дарим 200 рублей на&nbsp;Ваш первый заказ! <br />
                        Для получения скидки введите в&nbsp;корзине промокод:
                        <strong>{$coupon->name}</strong><br />
                        Промокод дает скидку только после завершения регистрации и&nbsp;подтверждения согласия на&nbsp;получение рассылок.
                    </p>
                {/if}

                {if Txt::phone_is_mobile($user->phone) OR  Txt::phone_is_mobile($user->phone2)}
                    <h3 style="margin-top:12px;">Информировать о статусе заказов:</h3>
                    <label class="label"><i class="radio"></i><input type="radio" name="order_notify" value="0" {if $user->order_notify eq 0}checked="checked"{/if} /> По электронной почте и СМС.</label>
                    {*<label class="label"><i class="radio"></i><input type="radio" name="order_notify" value="1" {if $user->order_notify eq 1}checked="checked"{/if} /> Только СМС.</label>*}
                    <label class="label"><i class="radio"></i><input type="radio" name="order_notify" value="2" {if $user->order_notify eq 2}checked="checked"{/if} /> Только по электронной почте.</label>
                {/if}

                <p><a href="{Route::url('user_password')}" class="no">Сменить пароль</a></p>

            </div>
            <div class="cb mt">
                <input type="submit" value="Сохранить" class="butt"/>
            </div>
        </form>
    </div>
</div>