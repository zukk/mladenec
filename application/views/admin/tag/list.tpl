<form action="" class="forms forms-inline">
    <fieldset>
        <legend>Поиск</legend>
        <div class="units-row">
            <div class="unit-40">Тип теговой
                {assign var=type value=$smarty.get.type|default:0}
                <select id="type" name="type">
                    <option value="0" {if $type eq 0}selected="selected"{/if}>не важно</option>
                    <option value="1" {if $type eq 1}selected="selected"{/if}>Старая теговая</option>
                    <option value="2" {if $type eq 2}selected="selected"{/if}>Категорийная теговая</option>
                </select>
                <script>
                    $(document).ready(function() {
                        $('#type').change(function() {
                            var val = $(this).val();
                            $('#tree_id, #section_id').show();
                            if (val == 1) $('#section_id').hide();
                            if (val == 2) $('#tree_id').hide();
                        });
                    });
                </script>
            </div>
            <div class="unit-60">
                <input type="text" name="name" placeholder="поиск по url или названию" class="width-100" value="{$smarty.get.name|default:''}">
            </div>


        </div>
        <div class="units-row">
            <div class="unit-40">
                Раздел:
                <select {if $type eq 2}style="display:none;"{/if} name="tree_id" id="tree_id">
                    <option value="0">все теговые</option>
                    {foreach from=ORM::factory('tag_tree')->order_by('lft')->find_all()->as_array() item=s}
                        <option value="{$s->id}" {if $smarty.get.tree_id|default:'' eq $s->id}selected="selected"{/if}>{'&nbsp;&nbsp;&nbsp;'|str_repeat:$s->depth}{$s->name}</option>
                    {/foreach}
                </select>

                <select {if $type eq 1}style="display:none;"{/if} name="section_id" id="section_id">
                    <option value="0">Все разделы</option>
                    {foreach from=Model_Section::get_catalog(TRUE) item=s}
                        <option value="{$s->id}" disabled="disabled">{$s->name}</option>
                        {if ! empty($s->children)}
                            {foreach from=$s->children item=sub}
                                <option value="{$sub->id}" {if $smarty.get.section_id|default:'' eq $sub->id}selected="selected"{/if}>{$s->name}::{$sub->name}</option>
                            {/foreach}
                        {/if}
                    {/foreach}
                </select>
            </div>
            <div class="unit-40">Бренд:<br />
                <select name="brand_id">
                    <option value="0">Все бренды</option>
                    {foreach from=$brands item=brand}
                        <option value="{$brand->id}" {if $smarty.get.brand_id|default:'' eq $brand->id}selected="selected"{/if}>{$brand->name}</option>
                    {/foreach}
                </select>
            </div>
        </div>
        <div class="units-row">
            <div id="search_flags">
                <label><i class="tr{$smarty.get.empty|default:''}"></i><span>Пустые</span><input type="hidden" name="empty" value="{$smarty.get.empty|default:''}" /></label>
                <label><i class="tr{$smarty.get.filter_not_exists|default:''}"></i><span>Несуществующие фильтры</span><input type="hidden" name="filter_not_exists" value="{$smarty.get.filter_not_exists|default:''}" /></label>
                <label><i class="tr{$smarty.get.checked|default:''}"></i><span>ЧПУ проверено руками</span><input type="hidden" name="checked" value="{$smarty.get.checked|default:''}" /></label>
                <label><i class="tr{$smarty.get.not_redirected|default:''}"></i><span>Без 301 редиректа</span><input type="hidden" name="not_redirected" value="{$smarty.get.not_redirected|default:''}" /></label>
            </div>
            <input type="submit" class="btn" name="search" value="Показать" />

        </div>
        {if $log_exists}<a href="?log">Лог теговых, которые стали пустыми</a>{/if}
    </fieldset>
</form>

    <div class="units-row">
        <div class="unit-70">
            <fieldset>
                <legend>Excel</legend>
                <form action="" method="post" class="forms forms-inline" enctype="multipart/form-data">
                    <input type="file" name="excel" />
                    <input type="submit" value="Загрузить из Excel" />
                </form>
                {*<a href="{Route::url('admin_tag_excel')}">Получить в формате Excel</a>*}
            </fieldset>
        </div>
        <div class="unit-30">
            <a href="{Route::url('admin_tag_recount')}" class="do">Пересчитать товары</a>
        </div>
    </div>

<form action="" class="cb">
    <a href="{Route::url('admin_add', ['model' => 'tag'])}">+ Добавить теговую страницу</a>
    <table id="list">
    <tr>
        <th>#</th>
        <th>группа / категория</th>
        <th>url</th>
        <th>название</th>
        <th>число товаров</th>
        <th>товары подсчитаны</th>
        <th>заполненность метатегов</th>
        <th>ЧПУ проверена руками</th>
    </tr>

    {foreach from=$list item=i}
    <tr {cycle values='class="odd",'}>
        <td><small>{$i->id}</small></td>
        <td>{$i->tree->name} / {$i->section->name}</td>
        <td>{$i->code}</td>
        <td><a href="{Route::url('admin_edit', ['model' => $m, 'id' => $i->id])}">{$i->name}</a></td>
        <td><a href="{$i->get_link(0)}" target="_blank" title="Открыть в новом окне на сайте.">{$i->goods_count}</a>
            {if $i->goods_count == 0}<br /><small>{date('Y-d-m', $i->goods_empty_ts)}</small>{/if}
        </td>
        <td>{if $i->goods_count_ts > 0}{date('Y-d-m G:i', $i->goods_count_ts)}{else}&mdash;{/if}</td>
		<td>
			{if ! empty($i->seo->title) and ! empty( $i->seo->keywords) and ! empty( $i->seo->description)}
				<span class="label label-green">да</span>
			{else}
				<span class="label label-red">нет</span>
			{/if}
		</td>
		<td>
			{if !empty( $i->checked)}
				<span class="label label-green">да</span>
			{else}
				<span class="label label-red">нет</span>
			{/if}
		</td>
    </tr>
    {/foreach}
    </table>
</form>

{$pager->html('Теговые страницы')}

{*<p>Товары подсчитываются при заходе на теговую страницу не чаще чем 1 раз в {Model_Tag::GOODS_COUNT_PERIOD} секунд.</p>*}
