{literal}
<script type="text/javascript" src="/j/admin/admin_bind.js"></script>
<script type="text/javascript">
$(document).ready(function() {
    chooseGoods('#add_goods',{},
    function(selectedIds) {
        for(var i in selectedIds) {
            $('#selected_goods').append(selectedIds[i] + ',');
        }
        bind('promo',{/literal}{$i->id}{literal},'goods',selectedIds,function(){
            loadPromoGoods({'promo_id':{/literal}{$i->id}{literal}},function(goods) {
                bindedGoods(goods,'promo',{/literal}{$i->id}{literal},'goods','#selected_goods');
            });
        });
    });
    loadPromoGoods({'promo_id':{/literal}{$i->id}{literal}},function(goods) {
        bindedGoods(goods,'promo',{/literal}{$i->id}{literal},'goods','#selected_goods');
    });
});

</script>
{/literal}
<form action="" xmlns="http://www.w3.org/1999/html" method="post" class="forms forms-columnar" enctype="multipart/form-data">
    <h1>#{$i->id}</h1>
    <p>
        <label for="name">Название</label>
        <input type="text" id="name" name="name" value="{$i->name}" class="width-50" />
    </p>
    <p>
        <label for="name">Заголовок бегунка</label>
        <input type="text" id="slider_header" name="slider_header" value="{$i->slider_header}" class="width-50" />
    </p>
    <p>
        <label for="active">Активность</label>
        <input type="checkbox" id="active" name="active" value="1" {if $i->active}checked="checked"{/if} />
    </p>
    {if $i->id}
        <p>
            <label>Участвующие товары</label>
            <div class="area" id="selected_goods"></div>
        </p>
        <p>
            <label>Добавить:</label>
            <input type="button" class="btn btn-round" id="add_goods" value="Добавить товары">
        </p>
    {/if}
    <fieldset>
        <legend>Отображается в брендах:</legend>
        <p>
            <label>Прикреплено:</label>
            <div class="area hi"><table>
            {foreach from=$i->brands->find_all()->as_array() item=br}
                <tr><td>#{$br->id}</td><td>{$br->name}</td><td><a class="btn btn-round" href="{Route::url('admin_unbind',['model'=>'promo','id'=>$i->id,'alias'=>'brands','far_key'=>$br->id])}">удалить</a></td></tr>
            {foreachelse}
                <p>Бренды не прикреплены.</p>
            {/foreach}
            </table></div>
        </p>
        <p>
            <label>Добавить:</label>
            <select name="misc[brand_id]">
                <option id="0">Не прикреплять</option>
                {foreach from=ORM::factory('brand')->where('active','=',1)->order_by('name','asc')->find_all()->as_array() item=brand}
                    <option value="{$brand->id}">{$brand->name}</option>
                {/foreach}
            </select>

        </p>
    </fieldset>
    <div class="units-row">
        <div class="unit-80">
            <input name="edit" value="Сохранить" type="submit" class="btn btn-green" />
            {if $i->id}<input name="edit" value="Сохранить и вернуться к списку" type="submit" class="btn  btn-green ok" alt="list" />{/if}
        </div>
    </div>
    <fieldset>
        <p>
            <legend>Отображается в товарах:</legend>
            <div class="area hi">
                <table>
                {foreach from=$i->showningoods->find_all()->as_array() item=good}
                    <tr>
                        <td><a href="{Route::url('admin_edit',['model' => 'good', 'id' => $good->id])}">{$good->id}</a></td>
                        <td>{$good->code}</td>
                        <td>{$good->group_name}</td>
                        <td>{$good->name}</td>
                        <td>{$good->qty}</td>
                        <td>{if $good->active}<span class="green">акт</span>{else}<span class="red">неакт</span>{/if}</td>
                    </tr>
                {/foreach}
                </table>
            </div>
        </p>
    </fieldset>
</form>

<div id="json_profiling"></div>