<h1>Личный кабинет</h1>

<div class="tabs mt">

    {include file='user/personal.tpl' active='user_action'}

    <div class="tab-content active">
        <p>Накопленные по&nbsp;акциям баллы</p>
        <table class="tt">
            <tr>
                <th class="l">Название акции</th>
                <th class="l">Сроки проведения</th>
                <th class="l">Баллы</th>
            </tr>
            {foreach $actions as $a}
                <tr>
                    <td>{$a->get_link()}</td>
                    <td>{if $a->count_from}
                            начало:&nbsp;{$a->count_from|date_ru}
                        {/if}
                        {if $a->count_to}<br />
                            окончание:&nbsp;{$a->count_to|date_ru} в&nbsp;0&nbsp;часов
                        {/if}
                    </td>
                    <td>{assign var=credits value=$user->get_funded($a)}
                        {$credits.sum|price}
                        {if $credits.from_order}
                        <p>Подарок получен в&nbsp;заказе
                            <a href="{Route::url('order_detail', ['id' => $credits.from_order])}">{$credits.from_order}</a>
                        </p>
                        {/if}
                    </td>
                </tr>
            {/foreach}
        </table>
    </div>
</div>
