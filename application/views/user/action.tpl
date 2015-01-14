<h1>Личный кабинет</h1>

<div class="tabs mt">
    <div>
        <a class="t" href="{Route::url('user')}">Мои данные</a>
        <a class="t" href="{Route::url('order_list')}">Мои заказы</a>
        <a class="t" href="{Route::url('user_address')}">Мои адреса</a>
        <a class="t" href="{Route::url('user_child')}">Мои дети</a>
        <a class="t active" href="{Route::url('user_action')}">Мои баллы по акции</a>
        <a class="t" href="{Route::url('user_reviews')}">Мои отзывы</a>
    </div>
    <div class="tab-content active">
        <p>Накопленные по акциям баллы</p>
        <table class="tt">
            <tr>
                <th class="l">Название акции</th>
                <th class="l">Накопление баллов до</th>
                <th class="l">Баллы</th>
            </tr>
            {foreach $actions as $a}
                <tr>
                    <td>{$a->get_link()}</td>
                    <td>{$a->count_to|date_ru|default:'&mdash;'}</td>
                    <td>
                        {$credits[$a->pk()]['sum']|price|default:'&mdash;'}
                    </td>
                </tr>
            {/foreach}
        </table>
    </div>
</div>
