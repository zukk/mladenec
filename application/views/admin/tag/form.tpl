<form method="post" action="" enctype="multipart/form-data" class="forms forms-columnar">
    {if $i->id}
    <div class="units-row">
        <div class="unit-70">
            <h1>Тег #{$i->id} {$i->name}</h1>
        </div>
        <div class="unit-30">
            <a target="_blank" class="btn" href="{$i->get_link(0)}">открыть на сайте</a>.
        </div>
    </div>
    {/if}
    <p>
        <label for="tree_id">Группа</label>
        <select class="width-50" name="tree_id" id="tree_id">
            <option value="">все</option>
        {foreach from=ORM::factory('tag_tree')->order_by('lft')->find_all()->as_array() item=s}
            <option value="{$s->id}" {if $i->tree_id eq $s->id}selected="selected"{/if}>{'&nbsp;&nbsp;&nbsp;'|str_repeat:$s->depth}{$s->name}</option>
        {/foreach}
        </select>
    </p>
    <p>
        <label for="name">Название</label>
        <input name="name" id="name" value="{$i->name|escape:'html'}" size="50" />
    </p>
    <p>
        <label for="anchor">Анкор </label>
        <input name="anchor" id="anchor" value="{$i->anchor|escape:'html'}" size="50" />
    </p>
    <p>
        <label for="code">ЧПУ</label>
        <input name="misc[old_code]" value="{$i->code}" type="hidden" />
        <input name="code" id="code" value="{$i->code}"  size="50" />
    </p>
	<p>
        <label>Текст</label>
        <textarea class="html" name="text" rows="15" cols="50">{$i->text}</textarea>
    </p>
    
    {if $i->id}
        {include file="admin/common/attach_brands.tpl"
            name='Прикрепленные бренды'
            attached_brands=$i->brands->find_all()->as_array()
            brands=ORM::factory('brand')->where('active','=',1)->order_by('name','asc')->find_all()->as_array()
            model='tag'
            id=$i->id}
        {include file="admin/common/attach_sections.tpl"
            name='Прикрепленные разделы'
            sections=Model_Section::get_catalog()
            attached_sections=$i->sections->find_all()->as_array()
            model='tag'
            id=$i->id}
        {include file="admin/common/attach_filter_values.tpl"
            name='Товары по фильтрам'
            filters=$i->get_filters()
            values=$i->filter_values->find_all()->as_array('id','id')
            model='tag'
            id=$i->id}
    {/if}

    {if $i->id}
    <p>
        <label for="params">Параметры отбора:<small></small></label>
        <input name="params" id="params" readonly="readonly" value="{$i->params}"  size="50" />
    </p>
    {/if}
    {include file='admin/seo/widget.tpl'}
    <p class="forms-inline">
        <input name="edit" value="Сохранить" type="submit" class="btn ok"/>
        <input name="edit" value="Сохранить и вернуться к списку" type="submit" class="btn ok" alt="list" />
    </p>
</form>
{if $i->id}
    <p><a href="{$i->id}/del" onclick="return confirm('Удалить насовсем?')" class="red">Удалить</a></p>
{/if}