<form action="" xmlns="http://www.w3.org/1999/html" method="post" class="forms forms-columnar" enctype="multipart/form-data">
    {if $i->id}
        <input type="hidden" name="misc[id]" id="good_set_id" value="{$i->id}" />
        <h1>#{$i->id} {$i->name}</h1>
    {else}<h1>Создание набора товаров</h1>{/if}
    <p>
        <label for="name">Название</label>
        <input type="text" id="name" name="name" value="{$i->name}" class="width-50" />
    </p>
    <p>
        <label for="name">Отображать в корзине</label>
        <input type="checkbox" id="cart" name="cart" {if $i->cart}checked="checked"{/if} value="1" />
    </p>
    <p>
        <label for="name">Активность</label>
        <input type="checkbox" id="active" name="active" {if $i->active}checked="checked"{/if} value="1" />
    </p>
    {if $i->id}
        <fieldset>
            <legend>Подбор товаров</legend>
            <div id="good_set_search" class="ajax">

            </div>
            <div class="units-row">
                <div class="unit-50">
                    <input type="button" class="btn" id="gss_do_find" value="Найти" />
                </div>
            </div>
            <p>
                <label for="misc[new_id]">Добавить по ID</label>
                <input type="text" id="misc_criteria_good_ids" name="misc[criteria][good_ids]" value="" class="width-100" />
                <span class="descr">ID товаров через запятую, не более 30 за 1 раз</span>
            </p>
        </fieldset>
        <fieldset>
            <legend>Критерии отбора</legend>
            {$criteries_data = $i->get_criteries()}
            {$criteries = $criteries_data['criteries']}
            <table>
                <tr>
                    <th>id</th>
                    <th>раздел</th>
                    <th>бренд</th>
                    <th>фильтр</th>
                    <th>значение</th>
                    <th colspan="5">товар</th>
                    <td>del</td>
                </tr>
            {foreach from=$criteries item=cr key=cr_id}
                    <tr>
                        <td>#{$cr_id}</td>
                        <td>
                            {if not empty($cr['section_id']) AND not empty($criteries_data['sections'][$cr['section_id']])}
                                {$criteries_data['sections'][$cr['section_id']]->name}
                            {else}
                                &mdash;
                            {/if}
                        </td>
                        <td>
                        {if not empty($cr['brand_id'])}{$criteries_data['brands'][$cr['brand_id']]->name}{else}&mdash;{/if}
                        </td>
                        {if not empty($cr['filter_value_id'])}
                            {$filter_value = $criteries_data['filter_values'][$cr['filter_value_id']]}<br />
                            {$filter = $criteries_data['filters'][$filter_value->filter_id]}
                            <td>
                                {$filter->name}
                            </td>
                            <td>
                                {$filter_value->name}
                            </td>
                            
                        {else}
                            <td>-</td><td>-</td>
                        {/if}
                        {if not empty($cr['good_id'])}
                            {$cg = $criteries_data['goods'][$cr['good_id']]}
                            <td>
                                <a href="{Route::url('admin_edit',['model'=>'good','id'=>$cr['good_id']])}">{$cr['good_id']}</a>
                            </td>
                            <td>
                               {$cg->group_name}
                               {$cg->name}
                            </td>
                            <td>
                               {$cg->price}
                            </td>
                            <td>
                               {$cg->qty}&nbsp;шт.
                            </td>
                            <td>
                                {if $cg->active}<span class="green">акт</span>
                                {else}<span class="red">неакт</span>
                                {/if}<br />
                                {if $cg->show}<a class="green" title="Открыть на сайте" href="{Route::url('product',['translit'=>$cg->translit,'group_id'=>$cg->group_id,'id'=>$cg->id])}" target="_blank">отобр</a>
                                {else}<span class="red">скр</span>
                                {/if}
                            </td>
                        {else}
                            <td>-</td><td>-</td><td>-</td><td>-</td><td>-</td>
                        {/if}
                        <td><input type="checkbox" name="misc[criteries_del][{$cr_id}]" value="{$cr_id}" /></td>
                    </tr>
            {/foreach}
            </table>
        </fieldset>
    {else}
        <p>Для подбора товаров необходимо сохранить новый набор.</p>
    {/if}
    <p class="forms-inline">
        <input name="edit" value="Сохранить" type="submit" class="btn btn-green"/>
        <input name="edit" value="Сохранить и вернуться к списку" type="submit" class="btn btn-green" alt="list" />
    </p>
    {if $i->id}
        <fieldset>
            <legend>Прикрепленные товары</legend>
            {$attached_goods=$i->get_goods(50,0)}
            {if $attached_goods}
                <table>
                    <thead>
                        <tr>
                            <th>Id</th>
                            <th>Название</th>
                            <th>Цена</th>
                            <th>Остаток на&nbsp;складе</th>
                            <th>Активность<br />отображение</th>
                            <th>del</th>
                        </tr>
                    </thead>
                    <tbody>
                    {foreach from=$attached_goods item=g}
                        <tr>
                            <td><a href="{Route::url('admin_edit',['model'=>'good','id'=>$g->id])}">{$g->id}</a></td>
                            <td>{$g->group_name} {$g->name}</td>
                            <td>{$g->price}</td>
                            <td>{$g->qty}</td>
                            <td>
                                {if $g->active}<span class="green">акт</span>
                                {else}<span class="red">неакт</span>
                                {/if},
                                {if $g->show}<a class="green" title="Открыть на сайте" href="{Route::url('product',['translit'=>$g->translit,'group_id'=>$g->group_id,'id'=>$g->id])}" target="_blank">отобр</a>
                                {else}<span class="red">скр</span>
                                {/if}
                            </td>
                            <td><input type="checkbox" name="misc[delete_goods][{$g->id}]" value="1" /></td>
                        </tr>
                    {/foreach}
                    </tbody>
                </table>
            {else}
                <p>Нет прикрепленных товаров.</p>
            {/if}
        </fieldset>
    {/if}
</form>
<script type="text/javascript">
    function gss_fv() {
        
    }
    function gss() {
        var data = {
            'set_id':       $('#good_set_id').val(),
            'section_id':   $('#gss_section_id').val(),
            'brand_id':     $('#gss_brand_id').val(),
            'name':         $('#gss_name').val(),
            'code':         $('#gss_code').val(),
            'code1c':       $('#gss_code1c').val(),
            'goods_page':   $('#gss_goods_page').val(),
            
            'filterVals':   [],
        }
        if ($('#gss_active').prop('checked')) { data.goods_active = 1; }
        if ($('#gss_show').prop('checked')) { data.goods_show = 1; }
        $('#filters input[type=checkbox]').each(function(index,el){
            if($(el).prop('checked')) data.filterVals.push($(el).val());
        });
        $('#good_set_search').html('<img src="/i/load.gif" />');
        $.post('/od-men/good_sert_ajax_search', data,
        function(html){
            $('#good_set_search').html(html);
        }, 'html');
    }
    $(document).ready(function(){
        $('#gss_do_find').click(function(){
            gss();
        });
        $('#gss_do_find').trigger("click");
    });
</script>