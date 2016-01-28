<h1>Регистрация</h1>

<form id="regform" action="" method="post">

    <div>
        <label>Адрес e-mail:<sup>*</sup></label>
        <div class="inp err"><input name="email" type="email" value="{$i->email}" required="required"/></div>
    </div>

    <div>
        <label>Пароль (мин. 6 символов):<sup>*</sup></label>
        <div class="inp err"><input type="password" name="password" required="required"/></div>
        <i class="info">?</i><div class="info">Пароль должен состоять из 6-20 символов русского и/или латинского алфавитов и/или цифр. <i>Совет: не создавайте слишком простых паролей. Комбинируйте буквы и цифры.</i></div>
    </div>

    <div>
        <label>Подтверждение пароля:<sup>*</sup></label>
        <div class="inp err"><input type="password" name="password2" required="required"/></div>
    </div>
    <div>
        <label>Мобильный телефон:<sup>*</sup></label>
        <div class="inp err"><input name="phone" type="tel" value="{$i->phone}" /></div>
        <i class="info">?</i><div class="info">Этот телефон будет использован для связи с&nbsp;вами и&nbsp;подтверждения заказа</div>
    </div>
    <div>
        <label>Имя:<sup>*</sup></label>
        <div class="inp err"><input name="name" value="{$i->name}" /></div>
        <i class="info">?</i><div class="info">Данное имя будет использовано при&nbsp;обращении к&nbsp;вам и&nbsp;в&nbsp;подписях отзывов о&nbsp;сайте и&nbsp;товарах.</div>
    </div>
    <div>
        <label>Отчество:</label>
        <input name="second_name" value="{$i->second_name}" />
    </div>
    <div>
        <label>Фамилия:</label>
        <input name="last_name" value="{$i->last_name}" />
    </div>
    <div>
        <label><input type="checkbox" name="sub" value="1" checked />&nbsp;Подписаться на рассылки<br /><small>получить 200 рублей в&nbsp;подарок на&nbsp;первый заказ!</small></label>
    </div>
    <div>
        <input type="submit" value="Зарегистрироваться" name="register" />
    </div>

</form>

{View::factory('user/ulogin')->render()}