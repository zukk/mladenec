<div class="width-wrap">
    
    <form action="" class="forms forms-columnar">
        <fieldset>
            <legend>Поиск заказов</legend>
            <div class="units-row">
                <div class="unit-25">
                    <b>Tracking ID заказа:</b><br />
                    <input type="text" class="width-100" name="id" value="{$smarty.get.id|default:''}" />
                </div>
                <div class="unit-25">
                    <b>Код 1С заказа:</b><br />
                    <input type="text" class="width-100" name="order_code" value="{$smarty.get.order_code|default:''}" />
                </div>
                <div class="unit-25">
                    <b>Номер чека:</b><br />
                    <input type="text" class="width-100" name="check_number" value="{$smarty.get.check_number|default:''}" />
                </div>
                <div class="unit-25">
                    <b>ID&nbsp;сайта:</b><br />
                    <input type="text" class="width-100" name="order_id" value="{$smarty.get.order_id|default:''}" />
                </div>
            </div>
            <div class="units-row">
                <div class="unit-33 forms-inline">
                    <b>Дата:</b><br />
                    {html_select_date time=$date|default:null field_array=date field_order=DMY all_empty='' start_year="-4" end_year="+1"}
                    <nobr>{*html_select_date time=$smarty.get.date|default:'' field_array=date field_order=YMD all_empty='' start_year="-2" end_year="+1"*}</nobr>
                </div>
                <div class="unit-33">
                    <b>Статус:</b><br />
                    <select name="status">
                        {html_options options=['N'=>'Новый','C'=>'Отменен до выезда курьера','F'=>'Отменен после выезда курьера'] selected=$smarty.get.status|default:''}
                    </select>
                </div>
            </div>            
            <input type="submit" name="search" value="Показать" />
        </fieldset>
    </form>
    <div class="cb"></div>
    {$pager->html('Заказы')}

    <form action="" >
        <table id="list">
        <tr>
            <th>#</th>
            <th>код 1С</th>
            <th>номер чека</th>
            <th>номер на сайте</th>
            <th>дата<br>Доставка с-по</th>
            <th>статус</th>
            <th>сумма</th>
            <th>вес</th>
            <th>объем</th>
        </tr>
        {foreach from=$list item=i}
        <tr {cycle values='class="odd",'}>
            <td>{$i->id}</td>
            <td>{$i->order_code}</td>
            <td>{$i->check_number}</td>
            <td><a href="{Route::url('admin_edit',['model'=>'order','id'=>$i->order_id])}">{$i->order_id}</a></td>
            <td>{$i->date}<br /><small>{$i->delivery_from}-{$i->delivery_to}</small></td>
            <td>{$i->status}</td>
            <td>{$i->sum|price}</td>
            <td>{$i->weight}</td>
            <td>{$i->volume}</td>
        </tr>
        {/foreach}
        </table>

    </form>
    {$pager->html('Заказы')}
</div>