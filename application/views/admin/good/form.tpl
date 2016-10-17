<script>
$(document).ready(function() {
    $('.area').click(function(e) {
        if (e.target.className == 'no') {
            $(e.target).parent().remove();
            $('#tag_changed').val(1);
        }
        if (e.target.className == 'ok') {
            var tag_id = $('#add_tag_id').val(), tag = $('#add_tag_id :selected').text();
            if (tag_id > 0) {
                $('#add_tag_id').before('<p><a href="/od-men/tag/' + tag_id + '">' + tag + '</a> <input type="hidden" name="tag[' + tag_id + ']" value="' + $('.area p').length + '" /> <a class="no">x</a></p>');
                $('#tag_changed').val(1);
            }
        }
    });

    $('.good_imgs').click(function(e) {
        if (e.target.className == 'no') {
            $(e.target).closest('tr').remove();
        }
    });

});
</script>

<form method="post" action="" enctype="multipart/form-data" class="forms forms-columnar">
    <div class="units-row">
        <h1 class="unit-80">#{$i->id} {$i->group_name} {$i->name}</h1>
        <div class="unit-20"><a href="{$i->get_link(0)}" class="btn" target="_blank">Посмотреть на сайте</a></div>
    </div>
    
    <div class="units-row">
        <div class="unit-25">
            
            <table>
                <tr>
                    <td>{if $i->show}<b class="green">отображается</b>{else}<b class="red">скрыт</b>{/if}</td>
                    <td>{if $i->active}<b class="green">активный</b>{else}<b class="red">не активный</b>{/if}</td>
                </tr>
                <tr><td><b>Артикул:</b></td><td><a href="{Route::url('product_1c',['code'=>urlencode($i->code)])}" target="_blank">{$i->code}</a></td></tr>
                <tr><td><b>Артикул 1c:</b></td><td>{$i->code1c|default:'&mdash;'}</td></tr>
                <tr><td><b>Код 1С:</b></td><td>{$i->id1c|default:'&mdash;'}</td></tr>
                <tr><td><b>Штрихкод:</b></td><td>{$i->barcode|default:'&mdash;'}</td></tr>
                <tr><td><b>CPA-модель</b></td><td><input type="checkbox" name="cpa_model" value="1" {if $i->cpa_model}checked="checked"{/if} /></td></tr>
            </table>
        </div>
        <div class="unit-50">
            <table>
                <tr><td><b>Группа:</b></td><td><a href="/od-men/group/{$i->group_id}">{$i->group_name|escape:'html'}</a></td><td>{if $i->group->active}<span class="green">акт</span>{else}<span class="red">скрыта</span>{/if}</td></tr>
                <tr>
                    <td><b>Категория:</b></td><td><a href="{Route::url('admin_edit',['model'=>'section','id'=>$i->section_id])}">{$i->section->name}</a></td>
                    <td>
                        {if $i->section->active}<a href="{$i->section->get_link(0)}" class="green">акт</a>
                        {else}<span class="red">скрыта</span>
                        {/if}
                    </td>
                </tr>
                <tr><td><b>Бренд:</b></td><td><a href='/od-men/brand/{$i->brand->id}'>{$i->brand->name}</a></td><td>{if $i->brand->active}<span class="green">акт</span>{else}<span class="red">скрыт</span>{/if}</td></tr>
                <tr><td><b>Страна:</b></td><td>{$i->country->name}</td><td></td></tr>
                <tr><td><b>Вес / Габариты:</b></td><td>{$i->prop->weight} кг / {$i->prop->size} мм </td><td></td></tr>
            </table>
        </div>
        <div class="unit-25">
            <table>
                <tr>
                    <td><b>На складе:</b></td>
                    <td>
                        {if $i->qty gt 0}
                            <span class="green">{$i->qty}&nbsp;шт.</span>
                        {else}
                            {if $i->qty eq -1}
                                <span class="blue">у поставщика</span>
                            {else}
                                <span class="red">отсутствует</span>
                            {/if}

                        {/if}
                    </td>
                </tr>
                <tr><td><b>Цена:</b></td><td>{$i->price}</td></tr>
                <tr><td><b>Цена для любимых:</b></td><td>{assign var=lovely_price value=Model_Good::get_status_price(1, $i->id)} {$lovely_price[$i->id]}</td></tr>
                <tr><td><b>Старая цена:</b></td><td><s>{$i->old_price|default:'&mdash;'}</s></td></tr>
            </table>
        </div>
    </div>

    <div class="units-row">
        <div class="unit-70">
             <p>
                <label for="name">Название</label>
                <input name="name" id="name" value="{$i->name|escape:'html'}" class="width-100" />
            </p>
            <p>
                <label for="translit">ЧПУ</label>
                <input name="translit" id="translit" value="{$i->translit}" class="width-100" />
            </p>
			{include file='admin/seo/widget.tpl'}
        </div>
    {if $i->id}
        <div class="unit-30">
            <p>
                <label for="prop[_new_item]">Новая карточка</label>
                <input type="checkbox" name="prop[_new_item]" value="1" {if $i->prop->_new_item}checked="checked"{/if} />    
            </p>
            <p>
                <label>Изменённая карточка</label>
                <input type="checkbox" name="prop[_modify_item]" value="1" {if $i->prop->_modify_item}checked="checked"{/if} />
            </p>
            <p>
                <label>Описание</label>
                <input type="checkbox" name="prop[_desc]" value="1" {if $i->prop->_desc}checked="checked"{/if}  />
            </p>
            <p>
                <label>Оптимизирован</label>
                <input type="checkbox" name="prop[_optim]" value="1" {if $i->prop->_optim}checked="checked"{/if}  />
            </p>
            <p>
                <label>Графика</label>
                <input type="checkbox" name="prop[_graf]" value="1" {if $i->prop->_graf}checked="checked"{/if}  />
            </p>
            {*
            <p>
                <label>Полная графика</label>
                <input type="checkbox" name="prop[_full_graf]" value="1" {if $i->prop->_full_graf}checked="checked"{/if}  />
            </p>
            *}
            <p>
                <input type="checkbox" name="prop[_supervisor]" value="1" {if $i->prop->_supervisor}checked="checked"{/if}  />
                <label>Проверено супервизором</label>
            </p>
            <p>
                <input type="checkbox" name="zombie" value="1" {if $i->zombie}checked="checked"{/if}  />
                <label>Зомби</label>
            </p>
            <p>
                <input type="checkbox" name="seo_auto" id="seo_auto" value="1" {if $i->seo_auto == 1}checked="checked"{/if}  />
                <label>Автозаполнение для SEO</label>
            </p>
        </div>
        </div>
        <div class="units-row">
            <div class="unit-30">
                <p>
                    <label for="popularity">Популярность</label>
                    <input name="popularity" id="popularity" value="{$i->popularity}" type="number" class="width-20" />
                </p>
                <p>
                    <label for="order">Искусственная Популярность</label>
                    <input name="order" id="order" value="{$i->order}" type="number" class="width-20" />
                </p>
            </div>
            <div class="unit-70">
                <label>Дубликаты товара</label>

                <div id="good_dups" data-id="{$i->id}">
                    {include file='admin/good/dups.tpl'}
                </div>
            </div>

    {/if}
    </div>
    <p><label>Теговые страницы</label>
        <div class="area">
            <input name="tag_changed" id="tag_changed" value="0" type="hidden"/>
            {foreach from=$i->get_tags() key=k item=t}
                <p><a href="/od-men/tag/{$t->id}">{$t->name}</a>
                    <input type="hidden" name="tag[{$t->id}]" value="{$k}" />
                    <a class="no">x</a>
                </p>
            {/foreach}

            <select id="add_tag_id">
                <option value="">   выберите    </option>
                {foreach from=Model_Tag::get_tree() item=tr}
                {if $tr.depth eq 1}
                    {assign var=d1 value=$tr.name}
                    {assign var=d2 value=''}
                    {assign var=d3 value=''}
                    {capture assign=group}{$d1}{/capture}
                {elseif $tr.depth eq 2}
                    {assign var=d2 value=$tr.name}
                    {assign var=d3 value=''}
                    {capture assign=group}{$d1} &rarr; {$d2}{/capture}
                {else}
                    {capture assign=group}{$d1} &rarr; {$d2} &rarr; {$tr.name}{/capture}
                {/if}
                <optgroup label="{$group}">
                    {foreach from=ORM::factory('tag')->where('tree_id', '=', $tr['id'])->find_all() item=tt}
                        <option value="{$tt->id}">{$tt->name}</option>
                    {/foreach}
                </optgroup>
                {/foreach}
            </select>

            <a class="ok">+ add</a>
        </div>

    </p>
    <p>
        <label>Промоакция:</label>
            <select name="promo_id" class="width-50">
            <option value="0">не участвует</option>
            {foreach from=ORM::factory('promo')->find_all()->as_array() item=promo}
                <option {if $i->promo_id eq $promo->id}selected="selected"{/if} value={$promo->id}>#{$promo->id} {$promo->name}</option>
            {/foreach}
        </select>
        <span class="forms-desc">При выборе, в слайдере в карточке товара будут отображаться товары, прикрепленные к промоакции товары.</span>
    </p>
    <p>
        <label>Строка текста для теговых страниц</label>
        <textarea name="prop[tags]" rows="15" cols="50" style="height:100px;">{$i->prop->tags}</textarea>
    </p>
    <p>
        <label for="upc">UPC</label>
        <input name="upc" id="upc" value="{$i->upc|escape:'html'}" size="50" />
    </p>
    {if $i->big}
    <p>
        <label>Преимущества (для КГТ)</label>
        <textarea class="html" name="prop[advantage]" rows="15" cols="50">{$i->prop->advantage}</textarea>
    </p>
    {/if}
    {foreach from=$sectionTabs item=tabName key=k}
		{if $tabName != "Отзывы"}
			<p id="p-tab-{$k}">
				<label>{$tabName}</label>
				<textarea id="tab-{$k}" class="html tabText" name="good_text[{$tabName}]" rows="15" cols="50">{$goodTabs[$tabName]|default:''}</textarea>
			</p>
		{/if}
	{/foreach}

    {if in_array($user->login, ['zukk', 'olga.tarass@gmail.com'])}
        {if $i->prop->_desc}

            {assign var=empty value=ORM::factory('good')
            ->with('prop')
            ->where('_desc', '=', 0)
            ->where('_optim', '=', 0)
            ->where('group_id', '=', $i->group_id)
            ->where('qty', '!=', 0)->count_all()}

            <p><a onclick="return confirm('Размножить?')" href="{Route::url('admin_clone_group_txt', ['id' => $i->id])}">Кнопка {$empty}</a></p>
        {/if}
    {/if}
    <p>
        <label for="search">Поисковые слова</label>
        <textarea name="prop[search]" rows="15" cols="50" style="height:100px;">{$i->prop->search}</textarea>
    </p>

    <p>
        <label>Картинка 500 (no&nbsp;watermark)</label>
        {if $i->prop->img500}
            <img src="{$i->prop->get_img(500)}" alt="" />
        {else}
            <strong>отсутствует</strong>
        {/if}
        <input type="file" name="img500" />
    </p>
    <h2 style="clear:left;">Картинки</h2>
    {assign var=src_images value=$i->get_src_images()}

    <div class="good_imgs">
        <table class="tt">
            <thead>
            <tr>
                <th style="width: 70px;">70x70</th>
                <th style="width: 255px;">255x255 / 173x255 </th>
                <th style="width: 380px;">380x380</th>
                <th style="width: 380px;">380x560 </th>
                <th>1600x1600</th>
                <th>удаление</th>
            </tr>
            </thead>

            <tbody style="cursor: move" class="tt" id="sort-images">
            {foreach from=$i->get_images() item=im}
            <tr>
                <td style="width:70px">
                {if not empty($im.70)}
                    {$im.70->get_img()}
                    <input type="hidden" name="img[70][]" value="{$im.70->ID}" />
                {else}
                    <input type="hidden" name="img[70][]" value="" />
                    <p class="red">
                        нет изображения 70x70<br />
                        перезалейте картинку
                    </p>
                {/if}
            </td>
            <td style="width: 255px;">
                {if not empty($im.255)}
                    {$im.255->get_img()}
                    <input type="hidden" name="img[255][]" value="{$im.255->ID}" />
                {else}
                    <input type="hidden" name="img[255][]" value="" />
                    <p class="red">
                        нет изображения 255x255<br />
                        перезалейте картинку
                    </p>
                {/if}
                {if not empty($im.173x255)}
                    {$im.173x255->get_img()}
                    <input type="hidden" name="img[173x255][]" value="{$im.173x255->ID}" />
                {else}
                    <input type="hidden" name="img[173x255][]" value="" />
                    <p class="red">
                        нет изображения 173x255<br />
                        перезалейте картинку
                    </p>
                {/if}
            </td>
            <td style=" width: 380px; ">
                {if not empty($im.380)}
                    {$im.380->get_img()}
                    <input type="hidden" name="img[380][]" value="{$im.380->ID}" />
                {else}
                    <input type="hidden" name="img[380][]" value="" />
                    <p class="red">
                    нет изображения 380x380<br />
                    перезалейте картинку
                </p>
                {/if}
            </td>
            <td style=" width: 380px; ">
                {if not empty($im.380x560)}
                    {$im.380x560->get_img()}
                    <input type="hidden" name="img[380x560][]" value="{$im.380x560->ID}" />
                {else}
                    <input type="hidden" name="img[380x560][]" value="" />
                    <p class="red">
                        нет изображения 380x560<br />
                        перезалейте картинку
                    </p>
                {/if}
            </td>
            <td>
                {if not empty( $im.1600 )}
                    <a href="{$im.1600->get_url()}" target="_blank">открыть картинку 1600x1600 в&nbsp;новой вкладке</a>
                    <input type="hidden" name="img[1600][]" value="{$im.1600->ID}" />
                {else}
                    <input type="hidden" name="img[1600][]" value="" />
                    <p class="red">
                        нет изображения 1600x1600<br />
                        перезалейте картинку
                    </p>
                {/if}
                <br />
                Оригинал:
                {if isset($src_images[$im.1600->original])}
                    {HTML::anchor($src_images[$im.1600->original]->get_url())}
                    {$src_images[$im.1600->original]=0}
                {else}
                    <span class="red">не найден</span>
                {/if}
            </td>
            <td><a class="no">удалить</a></td>
            </tr>
            {/foreach}
            </tbody>
            </table>
        <script>
            $(function(){
                $('#sort-images').sortable({
                    start: function(e, ui){
                            ui.placeholder.height(ui.item.height());
                        }
                });
            });
        </script>
        <div class="cb mt">
            <div style='border: 1px solid red; padding: 10px;'>
                Внимание! Для одежды нужно учитывать, что будут показываться вертикальные фотографии. Например, 173x255. Т.е. у&nbsp;квадратной
                фотографии, которую вы загружаете, будут обрезаны края слева и&nbsp;справа на&nbsp;16%. Оставляйте боковые поля белыми.
            </div>
            добавить картинку:
            <input type="file" name="img" />
        </div>
    </div>
    <p>
        <label for="new">Новинка {if $i->prop->new_till}(до {$i->prop->new_till}){/if}</label>
        <input type="checkbox" id="new" name="prop[new]" value="1" {if $i->new}checked="checked"{/if} readonly="readonly" />
    </p>
    <p>
        <label for="superprice">Суперцена</label>
        <input type="checkbox" id="new" name="prop[superprice]" value="1" {if $i->prop->superprice}checked="checked"{/if} />
    </p>
    <p>
        <label for="to_yandex">Выгружать в&nbsp;Yandex</label>
        <input type="checkbox" id="to_yandex" name="prop[to_yandex]" value="1" {if $i->prop->to_yandex}checked="checked"{/if} />
        <pre>&lt;sales_notes&gt;{Model_Action::sales_notes($i->id)}&lt;/sales_notes&gt;
        </pre>
    </p>
    <p>
        <label>Категория в&nbsp;Ozon</label>
        <input type="text" value="{$i->ozontype->name}" class="width-100"/>
        <small>{$i->ozontype->path_name}</small>
    </p>

    <p>
        <label>Категория в&nbsp;Wikimart</label>
        <input type="text" value="{$i->wikicategories->name}" class="width-100"/>
    </p>

    <div class="units-row">
        <div class="unit-80">
            <input name="edit" value="Сохранить" type="submit" class="btn btn-green" />
            {if $i->id}<input name="edit" value="Сохранить и вернуться к списку" type="submit" class="btn  btn-green ok" alt="list" />{/if}
			<script>
				$('[name=edit]').click(function(){
					var empty = false;
					$('.tabText').each(function(){
						if( $(this).redactor('code.get').match(/^\s*$/i) ){
							var t = $('#p-'+$(this).attr('id'));
                            var lab = t.find('label')[0].innerText;
                            if(lab == 'Видео'){
                                empty = false;
                            } else {
                                t.find('label').css({
                                    color: 'red'
                                });
                                $('html, body').animate({ scrollTop: t.offset().top}, 'fast');
                                empty = true;
                            }
						}
					});
					
					if( empty )
						return false;
				});
			</script>
        </div>
    </div>
</form>
<div class="units-row">
    <div class="unit-50">
        <h3>Не привязанные оригиналы изображений</h3>
        <ul>
            {foreach from=$src_images item=si}
                {if $si}
                <li><a href="/{$si->get_path()}" target="_blank">{$si->ID} &mdash; {$si->TIMESTAMP_X};</a></li>
                {/if}
            {foreachelse}не найдены
            {/foreach}
        </ul>   
    </div>
    <div class="unit-50">
        {assign var=filters value=$i->group->section->filters->find_all()}
        {assign var=values value=$i->filters->find_all()->as_array('id')}
        {if NOT empty($filters) AND NOT empty($values)}
            <p id="good_filters_header" class="btn">Фильтры</p>
            <table id="good_filters_table">
                <tr>
                    <th>id сайт</th>
                    <th>код 1С</th>
                    <th>название</th>
                    <th>значения</th>
                </tr>
                
                {foreach from=$filters item=f}
                    <tr>
                        <td>{$f->id}</td>
                        <td>{$f->code}</td>
                        <td>{$f->name}</td>
                        <td>
                            <table>
                                <tr>
                                    <th>id сайт</th>
                                    <th>код 1С</th>
                                    <th>название</th>
                                    <th>да</th>
                                </tr>
                                {foreach from=$f->values->order_by('sort', 'ASC')->order_by('name', 'DESC')->find_all() item=v}
                                    {$checked = ! empty($values[$v->id])}
                                    <tr>
                                        <td>{$v->id}</td>
                                        <td>{$v->code}</td>
                                        <td>{$v->name}</td>
                                        <td>{if !empty($values[$v->id])}<strong class="green"><a href="{$i->section->get_link(0)}#!f{$f->id}={$v->id};" target="_blank">да</a></strong>{else}<small class="red">&mdash;</small>{/if}</td>
                                    </tr>
                                {/foreach}
                                
                            </table>
                        </td>
                    </tr>
                {/foreach}
                
            </table>
                <script type="text/javascript">
                    $(document).ready(function() {
                        $('#good_filters_header').click(function(){
                            $('#good_filters_table').toggle();
                        });
                        $('#good_filters_table').hide();
                    });
                </script>
        {else}
            <p>Фильтры не найдены.</p>
        {/if}
    </div>
</div>