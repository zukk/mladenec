<div>
    {foreach from=Kohana::message('user/personal') key=route item=name}
        {assign var=class value='t'}
        {if $active == $route}
            {assign var=class value='t active'}
        {/if}
    {HTML::anchor(Route::url($route), $name, ['class' => $class])}
    {/foreach}
</div>

{assign var=coupon value=Model_Coupon::for_user($user->id, Model_Coupon::TYPE_CHILD)}

{if $coupon}

    <p>Поздравляем с&nbsp;днем рождения малыша!<br />
        Скидка 10% на&nbsp;все товары по&nbsp;промокоду <strong>{$coupon->name}</strong><br />
        промокод не&nbsp;распространяется на&nbsp;товары из&nbsp;категории &laquo;подгузники и&nbsp;пеленки&raquo;, &laquo;детское питание&raquo;.<br />
        Срок действия промокода: 1 неделя до&nbsp;и&nbsp;1 неделя после дня рождения ребенка!</p>

{/if}
