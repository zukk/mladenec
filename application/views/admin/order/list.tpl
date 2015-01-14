<form action="" class="forms forms-columnar">
    <fieldset>
        <legend>Поиск заказов</legend>
        <div class="units-row">
            <div class="unit-20">
                <b>ID заказа:</b><br />
                <input type="text" class="width-100" name="order_id" value="{$smarty.get.order_id|default:''}" />
            </div>
            <div class="unit-20">
                <b>ID&nbsp;Пользователя:</b><br />
                <input type="text" class="width-100" name="user_id" value="{$smarty.get.user_id|default:''}" />
            </div>
            <div class="unit-30">
                <b>Имя:</b><br />
                <input type="text" class="width-100" name="name" value="{$smarty.get.name|default:''}" />
            </div>
            <div class="unit-30">
                <b>Email:</b><br />
                <input type="text" class="width-100" name="email" value="{$smarty.get.email|default:''}" />
            </div>
        </div>
        <div class="units-row">
            <div class="unit-33">
                <b>Статус заказа:</b><br />
                <select name="status">
                    <option value="">все</option>
                    {html_options options=Model_Order::get_status_list() selected=$smarty.get.status|default:''}
                </select>
            </div>
            <div class="unit-33 datepicker">
                <nobr><b>С:</b><br />{html_select_date time=$from|default:null field_array=from field_order=DMY all_empty='' start_year="-2" end_year="+1"} <input type="text" name="from[Time_Hour]" value="{$smarty.get.from['Time_Hour']|default:'00'}" style="width: 20px; display: inline" /> : <input type="text" name="from[Time_Minute]" value="{$smarty.get.from['Time_Minute']|default:'00'}" style="width: 20px; display: inline" /></nobr>
            </div>
            <div class="unit-33 datepicker">
                <nobr><b>По:</b><br />{html_select_date time=$to|default:null field_array=to field_order=DMY all_empty='' end_year="+1"} <input type="text" name="to[Time_Hour]" value="{$smarty.get.to['Time_Hour']|default:'00'}" style="width: 20px; display: inline" /> : <input type="text" name="to[Time_Minute]" value="{$smarty.get.to['Time_Minute']|default:'00'}" style="width: 20px; display: inline" /></nobr>
            </div>
        </div>
        <input type="submit" name="search" value="Показать" />
    </fieldset>
</form>
<div class="cb"></div>
<div class="pager">
	<span>
		Оформленных: {$oformlennikh}
	</span>
</div>
{$pager->html('Заказы')}

        <form action="" >
            <table id="list">
            <tr>
                <th>#</th>
                <th title="Если заказ не изменялся стоит только время создания">Создан<br />Изменен</th>
                <th>Клиент</th>
                <th>Сумма</th>
                <th>Доставка</th>
                <th>Всего</th>
                <th>Статус</th>
            </tr>
            {foreach from=$list item=i}
            <tr {cycle values='class="odd",'}>
                <td><a href="{Route::url('admin_edit',['model'=>'order','id'=>$i->id])}"><small>{$i->id}</small></a></td>
                <td>{$i->created}<br />{if $i->created neq $i->changed}{$i->changed}{/if}</td>
                <td>{if $i->user_id}<b><a href="{Route::url('admin_edit',['model'=>'user','id'=>$i->user_id])}">{$i->data->name}</a></b> {$i->user->email}{/if}<br />
                {$i->data->city},{$i->data->street}, {$i->data->house} {if $i->data->corp}, корп. {$i->data->corp}{/if} {if $i->data->kv}, кв./оф.{$i->data->kv}{/if}.

                </td>
                <td>{$i->price}</td>
                <td>{$i->price_ship}<br /><small>{$i->data->ship_date}<br />{Model_Zone_Time::name($i->data->ship_time)}</small></td>
                <td>{$i->price + $i->price_ship}</td>
                <td>{$i->status()}<br /><small>{$i->status_time}</small></td>
            </tr>
            {/foreach}
            </table>

        </form>
    </div>
</div>
{$pager->html('Заказы')}

