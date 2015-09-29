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
            <div class="unit-25">
                <b>Статус заказа:</b><br />
                <select name="status">
                    <option value="">все</option>
                    {html_options options=Model_Order::get_status_list() selected=$smarty.get.status|default:''}
                </select>
            </div>
            <div class="unit-25">
                <b>Тип оплаты:</b><br />
                <select name="pay_type">
                    <option value="">все</option>
                    {html_options options=Model_Order::pay_types() selected=$smarty.get.pay_type|default:''}
                </select>
                <!--br />
                <a href="{Route::url('admin_order_card')}">Проблемный безнал</a-->
            </div>
            <div class="unit-50 datepicker">
                <b>Создан:</b><br />

                <nobr><b>С:</b><br />{html_select_date time=$from|default:null field_array=from field_order=DMY all_empty='' start_year="-2" end_year="+1"}
                    <input type="text" name="from[Time_Hour]" value="{$smarty.get.from['Time_Hour']|default:'00'}" style="width: 20px; display: inline" /> :
                    <input type="text" name="from[Time_Minute]" value="{$smarty.get.from['Time_Minute']|default:'00'}" style="width: 20px; display: inline" /></nobr><br />

                <nobr><b>По:</b><br />{html_select_date time=$to|default:null field_array=to field_order=DMY all_empty='' end_year="+1"}
                    <input type="text" name="to[Time_Hour]" value="{$smarty.get.to['Time_Hour']|default:'00'}" style="width: 20px; display: inline" /> :
                    <input type="text" name="to[Time_Minute]" value="{$smarty.get.to['Time_Minute']|default:'00'}" style="width: 20px; display: inline" /></nobr>
            </div>
        </div>
        <div class="units-row">
            <div class="unit-80"><input type="submit" name="search" value="Показать" /></div>
            <div class="unit-20"><a href="{Route::url('admin_order_excel')}?{$smarty.server.QUERY_STRING}">Скачать в Excel</a></div>
        </div>
    </fieldset>
</form>
<div class="cb"></div>
<div class="pager">
	<span>
		Оформленных: {$oformlennikh}
	</span>
</div>

{include file='admin/order/_list.tpl'}

