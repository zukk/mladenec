<script>
    $(document).ready( function() {

        $('input[name^=call]').click(function() {
            var item = $(this), id = item.attr('id').replace('call_', '');
            if (confirm('Заказ ' + id + ' отзвонили?')) {
                $.post('{Route::url('admin_ajax_call')}', { 'id': id }, function(data) {
                    if (data == 'ok') {
                        item.replaceWith('<span class="green">Отзвонили</span>');
                    }
                })
            }
        });

        $('input[name^=cash]').click(function() {
            var item = $(this), id = item.attr('id').replace('cash_', '');
            if (confirm('Заказ ' + id + ' перевести на НАЛ?')) {
                $.post('{Route::url('admin_ajax_cash')}', { 'id': id }, function(data) {
                    if (data == 'ok') {
                        item.replaceWith('<span class="green">перевели на НАЛ</span>');
                    }
                })
            }
        });

        $('input[name^=can_pay]').click(function() {
            var item = $(this), id = item.attr('id').replace('can_pay_', '');

            if (confirm("Заказ " + id + " разрешить оплату?\n\nРазрешая оплату, убедитесь в возможности поставки всех заказанных клиентом товаров и правильности суммы доставки")) {
                $.post('{Route::url('admin_ajax_can_pay')}', { 'id': id }, function(data) {
                    if (data == 'ok') {
                        item.replaceWith('<span class="green">Разрешили оплату</span>');
                    }
                })
            }
        });
    });
</script>


{$pager->html('Заказы')}

<form action="" >
    <table id="list">
        <tr>
            <th>#</th>
            <th title="Если заказ не изменялся стоит только время создания">Создан<br />Изменен</th>
            <th>Клиент</th>
            <th>Оплата</th>
            <th>Сумма</th>
            <th>Доставка</th>
            <th>Всего</th>
            <th>Статус</th>
        </tr>
        {foreach from=$list item=i}
            <tr {cycle values='class="odd",'}>
                <td><a href="{Route::url('admin_edit',['model'=>'order','id'=>$i->id])}"><small>{$i->id}</small></a></td>
                <td>{$i->created}<br />{if $i->created neq $i->changed}{$i->changed}{/if}</td>
                <td>{if $i->user_id}
                    <b><a href="{Route::url('admin_edit',['model'=>'user','id'=>$i->user_id])}">{$i->data->name}</a></b> {$i->user->email}{/if}<br />
                    {$i->data->city},{$i->data->street}, {$i->data->house} {if $i->data->corp}, корп. {$i->data->corp}{/if} {if $i->data->kv}, кв./оф.{$i->data->kv}
                    {/if}.
                </td>
                <td>
                    {if $i->pay_type eq Model_Order::PAY_DEFAULT}
                        Наличные

                    {elseif $i->pay_type eq Model_Order::PAY_CARD}

                        <img src="/i/cards.png" alt="Виза, Мастеркард" /><br />

                        {*if $i->call_card}
                            <span class="green">Отзвонили</span>
                        {else}
                            <input id="call_{$i->id}" name="call[{$i->id}]" value="Отзвонили" type="button" />
                        {/if*}

                        {if not $i->can_pay}
                            Оплата ещё не разрешена
                            <!--input id="can_pay_{$i->id}" name="can_pay[{$i->id}]" value="Разрешить" type="button" /-->

                        {else}
                            {assign var=payment value=$i->payment()}
                            {if $payment and $payment->status gt Model_Payment::STATUS_New}
                                {$payment->status_info()} <small>({$payment->status_time})</small>
                            {else}
                                Клиент нe начинал оплату
                            {/if}
                            <!--input id="cash_{$i->id}" name="cash[{$i->id}]" value="НАЛ" type="button" /-->

                        {/if}
                    {/if}
                </td>
                <td>{$i->price}</td>
                <td>{$i->price_ship}<br /><small>{$i->data->ship_date}<br />{Model_Zone_Time::name($i->data->ship_time)}</small></td>
                <td>{$i->price + $i->price_ship}</td>
                <td>{$i->status()}<br /><small>{$i->status_time}</small></td>
            </tr>
        {/foreach}
    </table>
</form>

{$pager->html('Заказы')}
