{*
To attach brands, model must have "brands" rel

@param $name
@param $attached_brands
@param $brands 
@param $model - name of model, for example, 'good'
@param $id

*}
<fieldset>
    <legend>{$name|default:'Прикрепленные бренды'}</legend>
    <p>
        <label>Прикреплено:</label>
        <div class="area hi"><table>
        {foreach from=$attached_brands item=br}
            <tr><td>#{$br->id}</td><td>{$br->name}</td><td><a class="btn btn-round" href="{Route::url('admin_unbind', ['model'=>$model, 'id'=>$id, 'alias'=>'brands', 'far_key'=>$br->id])}">удалить</a></td></tr>
        {foreachelse}
            <p>Бренды не прикреплены.</p>
        {/foreach}
        </table></div>
    </p>
    <p>
        <label>Добавить:</label>
        <select name="misc[bind][brands][]">
            <option id="0">Не прикреплять</option>
            {foreach from=$brands item=brand}
                <option value="{$brand->id}">{$brand->name}</option>
            {/foreach}
        </select>
    </p>
</fieldset>