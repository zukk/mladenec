
    <p><a href="{Route::url('admin_add', ['model' => 'zone_time'])}" class="btn">Добавить интервал доставки</a></p>

    <table id="list">
        <tr>
            <th>#</th>
            <th><form action="">Зона
                    {Form::select('zone_id', ORM::factory('zone')->order_by('priority', 'DESC')->find_all()->as_array('id', 'name'), Request::current()->query('zone_id'))}
                    <input type="submit" name="search" value="Показать" />
                </form>
            </th>
            <th>Название</th>
            <th>Дни недели</th>
            <th>Стоимость<br /><small>(при заказе от 0 руб)</small></th>
            <th>Утро</th>
            <th>Сортировка</th>
            <th>Активность</th>
        </tr>
    <tbody>
    <form action="">

        {foreach from=$list item=i}
            <tr {cycle values='class="odd",'}>
                <td><small>{$i->id}</small></td>
                <td><a href="{Route::url('admin_edit', ['model' => 'zone', 'id' => {$i->zone_id}])}">{$i->zone->name}</a></td>
                <td><a href="{Route::url('admin_edit', ['model' => 'zone_time', 'id' => {$i->id}])}">{$i->name}</a></td>
                <td>{$i->week_day|week_day:1}</td>
                <td>{$i->price}</td>
                <td><input name="morning[{$i->id}]" type="checkbox" value="1" {if $i->morning}checked="checked"{/if} disabled="disabled" /></td>
                <td>{$i->sort}</td>
                <td><input name="active[{$i->id}]" type="checkbox" value="1" {if $i->active}checked="checked"{/if} disabled="disabled" /></td>
            </tr>
        {/foreach}
    </form>
    </tbody>

    </table>

{$pager->html('Интервалы доставки')}


