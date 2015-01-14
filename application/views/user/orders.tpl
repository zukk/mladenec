<h1>Личный кабинет</h1>

<div class="tabs mt">
    <div>
        <a class="t" href="{Route::url('user')}">Мои данные</a>
        <a class="t active" href="{Route::url('order_list')}">Мои заказы</a>
        <a class="t" href="{Route::url('user_address')}">Мои адреса</a>
        <a class="t" href="{Route::url('user_child')}">Мои дети</a>
        <a class="t" href="{Route::url('user_action')}">Мои баллы по акции</a>
        <a class="t" href="{Route::url('user_reviews')}">Мои отзывы</a>
    </div>

    <div class="tab-content active">

        {if $orders}
        <table id="orders" class="tt">
            <thead>
            <tr>
                <th>Номер</th>
                <th>Создан</th>
                <th>Сумма</th>
                <th>Доставка</th>
                <th>Итого</th>
                <th>Состояние</th>
            </tr>
            </thead>
            <tbody>
            {foreach from=$orders item=o}
            <tr {cycle values='class="odd",'}>
                <td>{$o->get_link()}</td>
                <td>{$o->created}</td>
                <td class="r">{$o->price|price}</td>
                <td class="r">{$o->price_ship|price}</td>
                <td class="r">{$o->get_total()|price}</td>
                <td class="c">{$o->status()}
                {if $o->status == 'C' and $o->card->status lt Model_Payment::STATUS_Authorized and $o->card->form_url}
	                <a href="{$o->card->form_url}" class="butt small" style="width:80px; margin:0 auto;">оплатить</a>
	            {/if}
                </td>
            </tr>
            {/foreach}
            </tbody>
        </table>
        {$pager->html('Заказы')}

        {else}

            <p>Вы ещё не сделали ни одного заказа</p>

        {/if}

    </div>
</div>
