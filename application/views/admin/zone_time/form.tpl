
<form action="" method="post" class="forms forms-columnar" enctype="multipart/form-data">
    <div class="units-row">
        <h1 class="unit-80">#{$i->id} {$i->name}</h1>
    </div>
    <p>
        <label for="name">Название</label>
        <input type="text" id="name" name="name" value="{$i->name|default:''|escape:'html'}" class="width-50" />
        <small>это название увидят пользователи</small>
    </p>
    <p>
        <label for="name">Зона доставки</label>
        {Form::select('zone_id', ORM::factory('zone')->order_by('priority', 'DESC')->find_all()->as_array('id', 'name'), $i->zone_id)}
    </p>
    <p>
        <label>Дни недели</label>
        <div class="area">
        {$i->week_day|week_day}
        </div>
    </p>
    <p>
        <label for="name">Стоимость</label>
        <div class="area hi">
            <table class="table-simple">
                <tbody>
                    <tr>
                        <td>
                            <input type="text" id="price" name="price" size="5" value="{$i->price|default:''|escape:'html'}" />
                        </td>
                        <td>
                            <small>при сумме заказа&nbsp;от</small>
                        </td>
                        <td>
                            <small>0&nbsp;руб.</small>
                        </td>
                        <td></td>
                    </tr>
                    {foreach from=$i->prices->order_by('min_sum')->find_all() item=ip}
                        <tr>
                            <td>
                                <input type="text" size="5" name="prices[{$ip->id}][price]" value="{$ip->price}" />
                            </td>
                            <td>
                                <small>при сумме заказа&nbsp;от</small>
                            </td>
                            <td>
                                <small><input type="text" size="5" name="prices[{$ip->id}][min_sum]" value="{$ip->min_sum}" /></small>
                            </td>
                            <td>
                                <small>руб.</small>
                            </td>
                        </tr>
                    {/foreach}
                </tbody>
                <tr>
                    <td>
                        <input type="text" name="new_price[]" size="5" placeholder="цена" />
                    </td>
                    <td>
                        <small>при сумме от </small>
                    </td>
                    <td>
                        <small><input type="text" size="5" name="new_min_sum[]" placeholder="сумма" /></small>
                    </td>
                    <td><input class="btn btn-green" value="+ Добавить цену" type="button" id="add_zone_time"/></td>
                </tr>
            </table>
        </div>
    </p>
    <p>
        <label for="name">Сортировка</label>
        <input type="text" id="sort" name="sort" value="{$i->sort|default:''|escape:'html'}" class="width-25" />
        <small>чем меньше цифра, тем выше интервал в списке</small>
    </p>
    <p>
        <label for="active">Активность</label>
        <input type="checkbox" id="active" name="active" value="1" {if $i->active}checked="checked"{/if} />
   </p>
    <p>
        <input name="edit" value="Сохранить" type="submit" class="btn ok"/>
        <input name="edit" value="Сохранить и вернуться к списку" type="submit" class="btn ok" alt="list" />
    </p>
</form>
