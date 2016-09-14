<form action="" class="forms forms-inline">
    <fieldset>
        <legend>Поиск товаров</legend>
        
        <div id="search_flags" class="unit-100">
            <label><i class="tr{$smarty.get._new_item|default:''}"></i><span>Новая</span><input type="hidden" name="_new_item" value="{$smarty.get._new_item|default:''}" /></label>
            <label><i class="tr{$smarty.get._modify_item|default:''}"></i><span>Изменённая</span><input type="hidden" name="_modify_item" value="{$smarty.get._modify_item|default:''}" /></label>
            <label><i class="tr{$smarty.get._desc|default:''}"></i><span>Описание</span><input type="hidden" name="_desc" value="{$smarty.get._desc|default:''}" /></label>
            <label><i class="tr{$smarty.get._optim|default:''}"></i><span>Оптимизирован</span><input type="hidden" name="_optim" value="{$smarty.get._optim|default:''}" /></label>
            <label><i class="tr{$smarty.get._graf|default:''}"></i><span>Графика</span><input type="hidden" name="_graf" value="{$smarty.get._graf|default:''}" /></label>
            {*<label><i class="tr{$smarty.get._full_graf|default:''}"></i><span>Полная графика</span><input type="hidden" name="_full_graf" value="{$smarty.get._full_graf|default:''}" /></label>*}
            <label><i class="tr{$smarty.get._supervisor|default:''}"></i><span>Проверено супервизором</span><input type="hidden" name="_supervisor" value="{$smarty.get._supervisor|default:''}" /></label>
            <br />
            <br />
            <label><i class="tr{$smarty.get.show|default:''}"></i><span>Отображение</span><input type="hidden" name="show" value="{$smarty.get.show|default:''}" /></label>
            <label><i class="tr{$smarty.get.active|default:''}"></i><span>Активность</span><input type="hidden" name="active" value="{$smarty.get.active|default:''}" /></label>
            <label><i class="tr{$smarty.get.ozon|default:''}"></i><span>Озон</span><input type="hidden" name="ozon" value="{$smarty.get.ozon|default:''}" /></label>
            <label><i class="tr{$smarty.get.old_price|default:''}"></i><span>Старая цена</span><input type="hidden" name="old_price" value="{$smarty.get.old_price|default:''}" /></label>
            <label><i class="tr{$smarty.get.superprice|default:''}"></i><span>Суперцена</span><input type="hidden" name="superprice" value="{$smarty.get.superprice|default:''}" /></label>
            <label><i class="tr{$smarty.get._present|default:''}"></i><span>Подарок</span><input type="hidden" name="_present" value="{$smarty.get._present|default:''}" /></label>
            <label><i class="tr{$smarty.get.move|default:''}"></i><span>Продвигать</span><input type="hidden" name="move" value="{$smarty.get.move|default:''}" /></label>
            <label><i class="tr{$smarty.get.big|default:''}"></i><span>Крупногабаритка</span><input type="hidden" name="big" value="{$smarty.get.big|default:''}" /></label>
            <label><i class="tr{$smarty.get.wiki_cat|default:''}"></i><span>Не в викикатегории</span><input type="hidden" name="wiki_cat" value="{$smarty.get.wiki_cat|default:''}" /></label>
            <label><i class="tr{$smarty.get.google_cat|default:''}"></i><span>Не в гугл категории</span><input type="hidden" name="google_cat" value="{$smarty.get.google_cat|default:''}" /></label>
        </div>
        <div class="cb"></div>
        <div class="units-row">
            <div class="unit-25">
                <b>Название</b><br /><input type="text" class="width-100" name="name" value="{$smarty.get.name|default:''}">
            </div>
            <div class="unit-25">
                <b>Код 1С</b><br /><input type="text" class="width-100" name="id1c" value="{$smarty.get.id1c|default:''}">
            </div>
            <div class="unit-25">
                <b>Производитель</b><br />
                <select class="width-100" name="brand_id">
                    <option value="">все</option>
                    {foreach from=$brands item=b}
                    <option value="{$b->id}" {if $smarty.get.brand_id|default:'' eq $b->id}selected="selected"{/if}>{$b->name}</option>
                    {/foreach}
                </select>
            </div>
            <div class="unit-25"><b>Категория</b><br />
                <select class="width-100" name="section_id">
                    <option value="">все</option>
                {foreach from=Model_Section::get_catalog(FALSE) item=s}
                    <option value="{$s->id}" class="bold"  {if $smarty.get.section_id|default:'' eq $s->id}selected="selected"{/if}>{$s->name|default:''}</option>
                        {foreach from=$s->children item=c}
                        <option value="{$c->id}" {if $smarty.get.section_id|default:'' eq $c->id}selected="selected"{/if}>...{$c->name}</option>
                        {/foreach}
                {/foreach}
                </select>
            </div>
        </div>

        <div class="units-row">
            <div class="unit-25">
                <b>Артикул</b><br /><input type="text" name="code" class="width-100" value="{$smarty.get.code|default:''}">
            </div>
            <div class="unit-25">
                <b>Артикул 1C</b><br /><input type="text" name="code1c" class="width-100" value="{$smarty.get.code1c|default:''}">
            </div>
            <div class="unit-25">
                <b>Зомби:</b><br />
                <select class="width-100" name="zombie">
                    <option {if $smarty.get.zombie|default:'' eq 'hide'}selected="selected"{/if} value="hide">Скрыть</option>
                    <option {if $smarty.get.zombie|default:'' eq 'show'}selected="selected"{/if} value="show">Показать</option>
                    <option {if $smarty.get.zombie|default:'' eq 'all'}selected="selected"{/if} value="all">Все товары</option>
                </select>
            </div>
            <div class="unit-25">
                <b>Сортировать по:</b><br />
                {$orderBy = [ 'id' => 'Новизне', 'order' => 'Популярности']}
                <select name="orderBy">
                    {html_options options=$orderBy selected=$smarty.get.orderBy|default:'id'}
                </select>
            </div>
        </div>
		<div style='float: right'>
			<a href='/od-men/brand'>Бренды</a>
		</div>
                <div class="unit-20 unit-push">
                    
        <input type="submit" class="btn" name="search" value="Показать" />
                </div>


    </fieldset>
</form>
<form action="" class="cb">
{$pager->html('Товары')}
    <table id="list">
    <tr>
        <th>#</th>
        <th>популярность</th>
        <th>группа</th>
        <th>название</th>
        <th>артикул<hr />aртикул 1С<hr />код 1С</th>
        <th>цена</th>
        <th>старая цена</th>
        <th>количество</th>
        <th>картинка</th>
        <th>отображение<br />активность</th>
        <th>Продвигать</th>
	    <th>Крупногабаритка</th>
        {if $smarty.get.ozon|default:''}<th>Озон</th>{/if}
    </tr>

    {foreach from=$list item=i}
    <tr {cycle values='class="odd",'}>
        <td><small>{$i->id}</small></td>
        <td>{$i->order}</td>
        <td>{$i->group_name}</td>
        <td><a href="/od-men/{$m}/{$i->id}">{$i->name}</a></td>
        <td>
            {if $i->zombie}<b class="red">ЗОМБИ!!!</b>{/if}<br />
            <a href="{Route::url('product_1c',['code'=>urlencode($i->code)])}" target="_blank">{$i->code}</a>
            <hr />
            {$i->code1c}<br />
            <hr />
            {$i->id1c}
        </td>
        <td>{$i->price}</td>
        <td>{$i->old_price}</td>
        <td>{$i->qty}</td>
        <td>{if $i->image}{$i->img->get_img()}{/if}</td>
        <td>
            <input name="show[{$i->id}]" type="checkbox" value="1" {if $i->show}checked="checked"{/if} disabled="disabled" />&nbsp;<span class="{if $i->show}green{else}red{/if}">отобр</span><br />
            <input name="active[{$i->id}]" type="checkbox" value="1" {if $i->active}checked="checked"{/if} disabled="disabled" />&nbsp;<span class="{if $i->active}green{else}red{/if}">акт</span>
        </td>
        <td>{if $i->move}Да{/if}
            <label class="cpa" data-id="{$i->id}">cpa <input name="cpa[{$i->id}]" type="checkbox" value="1" {if $i->cpa_model}checked="checked"{/if} id="cpa_{$i->id}"/></label>
        </td>
	    <td>{if $i->big}Да{/if}</td>
        {if $smarty.get.ozon|default:''}
            <td>
                 {if $i->is_ozon_ready()}
                     <p class="green">продавать</p>
                 {else}
                     <p class="list_expandable_switch red">снять с продажи</p>
                     <ul class="list_expandable_body">
                         <li>Товар активен: {if $i->active > 0}<span class="green">да</span>{else}<span class="red">нет</span>{/if}</li>
                         <li>Цена: {if $i->price > 0}<span class="green">указана</span>{else}<span class="red">не указана</span>{/if}</li>
                         <li>Наличие: {if $i->qty > Model_Good::OZON_MIN_QTY}<span class="green">да, больше {Model_Good::OZON_MIN_QTY}</span>{else}<span class="red">менее {Model_Good::OZON_MIN_QTY}</span>{/if}</li>
                         <li>Описание: {if Txt::is_html_text_filled($i->prop->desc)}<span class="green">заполнено</span>{else}<span class="red">не заполнено</span>{/if}</li>
                         <li>ID бренда: 
                             {if $i->brand_id > 0}<span class="green">указан</span><br />
                                 Бренд активен: {if $i->brand->active > 0}<span class="green">да</span>{else}<span class="red">нет</span>{/if}
                             {else}<span class="red">не указан</span>{/if}</li>
                         <li>ID раздела: 
                             {if $i->section_id > 0}<span class="green">указан</span><br />
                                 Раздел активен: {if $i->section->active > 0}<span class="green">да</span>{else}<span class="red">нет</span>{/if}<br />
                             {else}<span class="red">не указан</span>{/if}</li>
                         <li>ID группы: 
                             {if $i->group_id > 0}<span class="green">указан</span><br />
                                 Группа активна: {if $i->group->active > 0}<span class="green">да</span>{else}<span class="red">нет</span>{/if}
                             {else}<span class="red">не указан</span>{/if}</li>
                         <li>Картинка 500: {if $i->prop->img500}<span class="green">да</span>{else}<span class="red">нет</span>{/if}</li>
                     </ul>
                 {/if}
            </td>
        {/if}
    </tr>
    {/foreach}
    </table>
</form>

{$pager->html('Товары')}
<script type="text/javascript">
    $(document).ready(function() {
        $('.list_expandable_body').hide();
        $('.list_expandable_switch').click(function() {
           $(this).next().toggle(); 
        });

        $('.cpa').click(function () {
            $.post({Route::url('admin_cpa')}, { id: $(this).data('id')}, function(data) {
                $('#cpa_' + $(this).data('id')).prop('checked', data);
            })
        })
    });
</script>
