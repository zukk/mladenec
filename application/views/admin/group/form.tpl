<form action="" method="post" class="forms forms-columnar" enctype="multipart/form-data">
    <h1>#{$i->id} {htmlspecialchars($i->name)}</h1>

    <p>
        <label for="name">Название</label>
        <input type="text" id="name" name="name" value="{htmlspecialchars($i->name)|default:''}" class="width-50" />
    </p>
    <p>
        <label for="name">ЧПУ</label>
        <input type="text" id="translit" name="translit" value="{$i->translit|default:''}" class="width-50" />
    </p>
    <p>
        <label for="img">Иконка</label>
        {if $i->img}{$i->get_image()}{/if}
    </p>
    <p>
        <label for="image">Загрузить новую</label>
        <input type="file" id="image" name="image" />
    </p>
    <p>
        <label for="active">Активность</label>
        <input type="checkbox" id="active" name="active" value="1" {if $i->active}checked="checked"{/if} />
    </p>
    <p>
        <label for="goods">Товары</label>
        <div class="area hi">
            <table>
                <tr>
                    <th>ID<br />Арт</th>
                    <th>Сортировка</th>
                    <th>Группа<br />Название</th>
                    <th>Цена</th>
                    <th>Активность</th>
                </tr>
            {foreach from=$i->goods->order_by('order','asc')->find_all()->as_array() item=good}
                <tr>
                    <td><a href="{Route::url('admin_edit',['model'=>'good','id'=>$good->id])}" target="_blank">{$good->id}</a><br />
                        {$good->code}</td>
                    <td><input type="number" min="0" max="1000" name="misc[good_order][{$good->id}]" value="{$good->order}" size="4" /></td>
                    <td>{$good->group_name}<br />
                        {$good->name}</td>
                    <td>{$good->price}</td>
                    <td>{if $good->active}<span class="green">акт</span>{else}<span class="red">выкл</span>{/if}</td>
                </tr>
            {/foreach}
            </table>
        </div>
        <span class="forms-desc">500 — значение по умолчанию, т.е. без сортировки. При меньшем значении 
            товар поднимается выше, при значении более 500 &mdash; опускается.</span>
    </p>
    <div class="units-row">
        <div class="unit-80">
            <input name="edit" value="Сохранить" type="submit" class="btn btn-green" />
            {if $i->id}<input name="edit" value="Сохранить и вернуться к списку" type="submit" class="btn  btn-green ok" alt="list" />{/if}
        </div>
    </div>
</form>