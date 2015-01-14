<form action="" method="post" class="forms forms-columnar" enctype="multipart/form-data">
    <h1>#{$i->id} {$i->name}</h1>
    {assign var=sections value=Model_Section::get_catalog()}
    {assign var=brands value=$i->get_brands( TRUE )}

    <p>
        <label for="name">Название</label>
        <input type="text" id="name" name="name" value="{$i->name|default:''}" class="width-50" />
    </p>
	<p>
		<label for="image">Картинка</label>
		<input type="file" id="image" name="image" />
	</p>
    <p class="datepicker">
        <label for="name">Действителен до:</label>
        {html_select_date time=$i->expires field_array=expires field_order=DMY all_empty='' start_year='-1' end_year='+5'}
    </p>

{if $i->id}
    {assign var=binded value=$i->get_binded()}
    {if $binded}
    <p>
        <label>Привязано:</label>
        {foreach from=$binded item=bnd}
            <small>{if $bnd['brand_id']}{$bnd['brand_name']}{else}все бренды{/if}, {if $bnd['section_id']}{$bnd['section_name']}{else}все разделы{/if}&nbsp;<a class="btn btn-small" href="{Route::url('admin_sert_unbind',['id'=>$i->id])}?section_id={$bnd['section_id']}&brand_id={$bnd['brand_id']}">x</a></small>
        {/foreach}
    </p>
    {/if}
{/if}

    {if $sections|default:FALSE AND $i->id}
        <p>
            <label for="section_id">Разделы</label>
            <select name="misc[section_id]">
                <option value="0">Не привязывать</option>
                {foreach from=$sections item=s}
                    <option value="{$s->id}" disabled="disabled">{$s->name}</option>
                    {if ! empty($s->children)}
                        {foreach from=$s->children item=sub}
                            <option value="{$sub->id}">{$s->name}::{$sub->name}</option>
                        {/foreach}
                    {/if}
                {/foreach}
            </select>
        </p>
    {/if}
    {if $brands|default:FALSE AND $i->id}
        <p>
            <label for="brand_id">Бренды</label>
            <select name="misc[brand_id]">
                <option value="0">Не привязывать</option>
                {foreach from=$brands item=brand}
                    <option value="{$brand->id}">{$brand->name}</option>
                {/foreach}
            </select>
        </p>
    {/if}
    {if $sections|default:FALSE AND $brands|default:FALSE AND $i->id}
    <fieldset>
        <legend>Прикрепить к группе товаров</legend>
        <p>
            <label for="sert_group_id">Искать в разделе:</label>
            <select name="misc[group_search_section_id]" id="group_search_section_id">
                <option value="0">Все разделы</option>
                {foreach from=$sections item=s}
                    <option value="{$s->id}" disabled="disabled">{$s->name}</option>
                    {if ! empty($s->children)}
                        {foreach from=$s->children item=sub}
                            <option value="{$sub->id}">{$s->name}::{$sub->name}</option>
                        {/foreach}
                    {/if}
                {/foreach}
            </select><br /><br />
            </p>
            <p>
                <label for="brand_id">Искать по бренду:</label>
                <select name="misc[group_search_brand_id]" id="group_search_brand_id">
                    <option value="0">Все бренды</option>
                    {foreach from=$brands item=brand}
                        <option value="{$brand->id}">{$brand->name}</option>
                    {/foreach}
                </select><br /><br />
            </p>
            <p>
                <label for="sert_group_id">Группа товаров</label>
                <select name="misc[group_id]" id="group_id"><option value="0" disabled="disabled">Выберите раздел или бренд</option></select>
                <small>Сертификат будет привязан к выбранной группе товаров.<br />
                    {foreach from=$i->groups->distinct('id')->find_all() item=sert_group}
                        <span class="bindings">{if $sert_group->active}<a href="{$sert_group->get_link(0)}" target="_blank">{$sert_group->name}</a>{else}{$sert_group->name}{/if}&nbsp;<a class="btn btn-small" href="{Route::url('admin_unbind',['model'=>'sert','id'=>$i->id,'alias'=>'groups','far_key'=>$sert_group->id])}">x</a></span>
                    {/foreach}
                </small>
            </p>
    </fieldset>
    {/if}

    <div class="units-row">
        <div class="unit-80">
            <input name="edit" value="Сохранить" type="submit" class="btn btn-green" />
            {if $i->id}<input name="edit" value="Сохранить и вернуться к списку" type="submit" class="btn  btn-green ok" alt="list" />{/if}
        </div>
        {if $i->id}<div class="unit-20"><a  href="{$i->id}/del" onclick="return confirm('Удалить насовсем?')" class="btn btn-red" target="_blank">Удалить</a></div>{/if}
    </div>
</form>
{if $i->image}{$i->big->get_img()}{/if}
<script type="text/javascript">
    function load_groups_as_options( section_id, brand_id) {
        $('#group_id').html('');
        $.post("/od-men/group_ajax_get", { 'section_id': section_id, 'brand_id': brand_id })
            .done(function(data) {
                if (data.length > 0) {
                    $('#group_id').html('<option value="0">Выберите группу</option>');
                    for (var group in data) {
                        $('#group_id').append('<option value="' + data[group]['id'] + '">' + data[group]['name'] + '</option>');
                    }
                } else {
                    $('#group_id').html('<option value="0">Группы не найдены</option>');
                }
            });
    
    }

    $(document).ready(function() {
         $('#group_search_section_id').change(function(){
            var brand_id = $('#group_search_brand_id').val();
            var section_id = $('#group_search_section_id').val();
            load_groups_as_options(section_id,brand_id);
        });
        $('#group_search_brand_id').change(function(){
            var brand_id = $('#group_search_brand_id').val();
            var section_id = $('#group_search_section_id').val();
            load_groups_as_options(section_id,brand_id);
        });
    }); 
</script>