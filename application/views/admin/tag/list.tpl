<form action="" class="forms forms-inline">
    <fieldset>
        <legend>Поиск</legend>
        <div class="units-row">
            <div class="unit-40">
                <select class="" name="tree_id">
                    <option value="">все</option>
                {foreach from=ORM::factory('tag_tree')->order_by('lft')->find_all()->as_array() item=s}
                    <option value="{$s->id}" {if $smarty.get.tree_id|default:'' eq $s->id}selected="selected"{/if}>{'&nbsp;&nbsp;&nbsp;'|str_repeat:$s->depth}{$s->name}</option>
                {/foreach}
                </select>
            </div>
            <div class="unit-60">
                <input type="text" name="name" placeholder="Слово для поиска" class="width-100" value="{$smarty.get.name|default:''}">
            </div>
        </div>
        <div class="units-row">
            <div class="unit-40">Раздел:<br />
                <select name="section_id">
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
                <div id="search_flags">
                    <label><i class="tr{$smarty.get.empty|default:''}"></i><span>Пустые</span><input type="hidden" name="empty" value="{$smarty.get.empty|default:''}" /></label>
                </div>
            </div>
            <div class="unit-20">
                <input type="submit" class="btn" name="search" value="Показать" />
            </div>

        </div>
        
    </fieldset>
</form>

<form action="" method="post" class="forms forms-inline" enctype="multipart/form-data">
    <div class="units-row">
        <div class="unit-70">
            <fieldset>
                <legend>Пакетная загрузка из Excel</legend>
                <input type="file" name="excel" />
                <input type="submit" value="Загрузить из Excel" />
            </fieldset>
        </div>
        <div class="unit-30">
            <a href="{Route::url('admin_tagbylink')}" class="btn btn-big">Создать из ссылок</a>
        </div>
    </div>
</form>

<form action="" class="cb">
    <a href="{Route::url('admin_add', ['model' => 'tag'])}">+ Добавить теговую страницу</a>
    <table id="list">
    <tr>
        <th>#</th>
        <th>группа</th>
        <th>url</th>
        <th>название</th>
        <th>групп товаров</th>
        <th>товары подсчитаны</th>
    </tr>

    {foreach from=$list item=i}
    <tr {cycle values='class="odd",'}>
        <td><small>{$i->id}</small></td>
        <td>{$i->tree->name}</td>
        <td>{$i->code}</td>
        <td><a href="/od-men/{$m}/{$i->id}">{$i->name}</a></td>
        <td><a href="{$i->get_link(0)}" target="_blank" title="Открыть в новом окне на сайте, товары пересчитаются.">{$i->goods_count}</a></td>
        <td>{if $i->goods_count_ts > 0}{date('Y-d-m G:i',$i->goods_count_ts)}{else}&mdash;{/if}</td>
    </tr>
    {/foreach}
    </table>
</form>

{$pager->html('Теговые страницы')}

<p>Товары подсчитываются при заходе на теговую страницу не чаще чем 1 раз в {Model_Tag::GOODS_COUNT_PERIOD} секунд.</p>
