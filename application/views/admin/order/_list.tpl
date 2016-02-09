{$pager->html('Заказы')}

<form action="" >
    <table id="list">
        <tr>
            <th>#</th>
            <th title="Если заказ не изменялся стоит только время создания">Создан<br />Отправлен<br />Изменен</th>
            <th>Клиент</th>
            <th>Оплата</th>
            <th>Сумма</th>
            <th>Доставка</th>
            <th>Всего</th>
            <th>Статус</th>
            <th>Число заказов</th>
            <th>Купон</th>
            <th>Источник</th>
        </tr>
        {foreach from=$list item=i}
            <tr {cycle values='class="odd",'}>
                <td><a href="{Route::url('admin_edit',['model'=>'order','id'=>$i->id])}"><small>{$i->id}</small></a></td>
                <td>{$i->created}<br />{$i->sent}<br />{if $i->created neq $i->changed}{$i->changed}{/if}</td>
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

                        {if not $i->can_pay}
                            Оплата ещё не разрешена

                        {else}
                            {assign var=payment value=$i->payment()}
                            {if $payment->status gt Model_Payment::STATUS_New}
                                {$payment->status_info()} <small>({$payment->status_time})</small>
                            {else}
                                Клиент нe начинал оплату
                            {/if}

                        {/if}
                    {/if}
                </td>
                <td>{$i->price}</td>
                <td>{$i->price_ship}<br /><small>{$i->data->ship_date}<br />{Model_Zone_Time::name($i->data->ship_time)}</small></td>
                <td>{$i->price + $i->price_ship}</td>
                <td>{$i->status()}<br /><small>{$i->status_time}</small></td>
                <td>{$i->data->num}</td>
                <td>{if $i->coupon_id}{HTML::anchor(Route::url('admin_edit', ['model'=>'coupon', 'id'=> $i->coupon_id]), $i->coupon->name)}{/if}</td>
                <td>{if $i->data->source}{assign var=json value=$i->data->source|json_decode}<strong>{$json->source}</strong>{/if}</td>
            </tr>
        {/foreach}
    </table>
</form>

{$pager->html('Заказы')}
