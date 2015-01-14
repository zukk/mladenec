<form id='section-form' action="" method="post" class="forms forms-columnar" enctype="multipart/form-data">
    <div class="units-row">
        <h1 class="unit-80">#{$i->id} {$i->name}</h1>
        <div class="unit-20"><a href="{$i->get_link(0)}" class="btn" target="_blank">Посмотреть на сайте</a></div>
    </div>
    <p>
        <label for="parent_id">Родитель</label>
        <select name="parent_id" id="parent_id">
            <option value=""></option>
            {html_options options=Model_Section::parents($i->id) selected=$i->parent_id}
        </select>
    </p>
    <p>
        <label for="name">Название (h1)</label>
        <input type="text" id="name" name="name" value="{$i->name}" size="50" readonly="readonly"/>
    </p>
    <p>
        <label for="translit">ЧПУ</label>
        <input type="text" id="translit" name="translit" value="{$i->translit}" size="50" readonly="readonly"/>
    </p>
	{include file='admin/seo/widget.tpl'}
    <p>
        <label for="text">Текст страницы</label>
        <textarea name="text" class="html" rows="10" cols="40">{$i->text}</textarea>
    </p>

    <p>
        <label for="active">Активность</label>
        <input type="checkbox" id="active" name="active" value="1" {if $i->active}checked="checked"{/if}  readonly="readonly"/>
    </p>
	{if ! $i->parent_id}
	<!-- пока не появятся другие типы выгрузки -->
    <p style="display: none;">
        <label for="active">Тип выгрузки</label>
		<select name="export_type">
			<option value="0">выберите</option>
			{foreach from=$exporttypes item=opt key=val}
				<option value="{$val}"{if $i->export_type eq $val} selected{/if}>{$opt}</option>
			{/foreach}
		</select>
    </p>
	{else}
        <input type="hidden" name="export_type" value="0" />
	{/if}
    <fieldset>
        <legend><h3>Настройки отображения</h3></legend>

{$hits = $i->hits->find_all()->as_array()}

<p>
	<label>Картинка 93x96 (для мобильной версии):</label>
	{if $i->image93}{$i->img93->get_img()}{/if}
	<input type="file" name="img93" />
</p>
<br />
<br />
<br />
{if $i->parent_id}
        {assign var=filters value=ORM::factory('filter')->where('section_id', 'IN', [$i->parent_id, $i->id])->find_all()->as_array()}
        <p class="cb">
            <label>Картинка 225x120 (для страницы {HTML::anchor($i->parent->get_link(0), $i->parent->name, ['target' => '_blank'])})</label>
            {if $i->image}{$i->img->get_img()}{/if}
            <input type="file" name="img" />
        </p>
        {literal}
        <script>function sub_change() {
                $('#sub_menu').toggle($('#sub').val() != {/literal}{Model_Section::SUB_NO}{literal});
                $('#sub_filter').toggle($('#sub').val() == {/literal}{Model_Section::SUB_FILTER}{literal});
        }</script>
        {/literal}
        <fieldset style="margin-top:40px;">
            <legend>Меню 3го уровня
                <select name="settings[sub]" id="sub" onchange="sub_change()">
                    {html_options options=Model_Section::settings('sub') selected=$i->settings.sub}
                </select>
            </legend>

			<p id="sub_menu" {if $i->settings.sub eq Model_Section::SUB_NO}style="display:none;"{/if}>
				<label for="img_menu">Картинка 200x110 (для меню 3го уровня):</label>
				{if $i->img_menu}{$i->menu_img->get_img()}{/if}
				<input type="file" name="img_menu" />
			</p>
            <p id="sub_filter" {if $i->settings.sub neq Model_Section::SUB_FILTER}style="display:none;"{/if}>
                <label>Фильтр для показа:</label>
                <select name="settings[sub_filter]" >
                    {foreach from=$filters item=f}
                        <option value="{$f->id}" {if $f->id eq $i->settings.sub_filter|default:0}selected="selected"{/if}>{$f->name}</option>
                    {/foreach}
                </select>
            </p>
        </fieldset>

        <fieldset style="margin-top:40px;">
            {literal}
                <script>function list_change() {
                        $('#list_filter').toggle($('#list').val() != {/literal}{Model_Section::LIST_GOODS}{literal});
                    }
                </script>

            {/literal}

            <legend>Вид страницы категории
                <select name="settings[list]" id="list" onchange="list_change()">
                    {html_options options=Model_Section::settings('list') selected=$i->settings.list}
                </select>
            </legend>

            <p class="llist" id="list_filter" {if $i->settings.list eq Model_Section::LIST_GOODS}style="display:none;"{/if}>
                <label>Фильтр для показа:</label>
                <select name="settings[list_filter]">
                    {foreach from=$filters item=f}
                        <option value="{$f->id}" {if $f->id eq $i->settings.list_filter|default:0}selected="selected"{/if}>{$f->name}</option>
                    {/foreach}
                </select>
            </p>
        </fieldset>

        <p>
            <label for="per_page">На странице</label>
            <input type="text" id="per_page" name="settings[per_page]" value="{','|implode:$i->settings.per_page}" />
        </p>
        <p>
            <label for="m">Вид по умолчанию</label>
            <select name="settings[m]" id="m">
                {html_options options=Model_Section::settings('m') selected=$i->settings.m}
            </select>
        </p>
        <p>
            <label for="x">Наличие</label>
            <select name="settings[x]" id="x">
                {html_options options=Model_Section::settings('x') selected=$i->settings.x}
            </select>
        </p>
        <p>
            <label for="buy">Тип кнопки `Купить`</label>
            <select name="settings[buy]" id="buy">
                {html_options options=Model_Section::settings('buy') selected=$i->settings.buy}
            </select>
        </p>
        <p>
            <label for="buy">Селект сортировки товаров</label>
			<script>
				$(function(){
					$('.sortableItems').sortable({
						axis: 'y'
					});
				});
			</script>
            Перетаскивайте мышкой элементы. В каком порядке они здесь, в таком же будут в селекте в рубрике. Значение первого сверху элемента определяет сортировку по-умолчанию.
            <div class="sortableItems" id='sortableOrders' style="padding-left: 170px;">
                {foreach from=$sortableOrderItems key=value item=item}
                <div>
                    {$item}
                    <input type="hidden" name="settings[orderByItems][]" value="{$value}" />
                </div>
                {/foreach}
            </div>
        </p>
		<p>
            <label>Сортировка брендов</label>
            Первые 7 брендов будут в топе и всегда видны пользователю. Поэтому они выделены жирным.  Они будут отсортированы как в списке. Остальные - всегда по алфавиту
            <div class="sortableItems" id='sortableBrands' style='padding-left: 170px;'>
                {foreach from=$sBrands item=brand}
                <div> {$brand->name} <input type="hidden" name="settings[brands][]" value="{$brand->id}" /></div>
                {/foreach}
            </div>
        </p>
			<p>
		<legend>Вид тайлов
			<select name="settings[view_type]">
                {html_options options=Model_Section::settings('view_type') selected=$i->settings.view_type}
            </select>
		</legend>
	
		</p>
{else}
        <fieldset style="margin-top:40px;">
            {literal}
                <script>function list_change() {
                        $('#list_filter').toggle($('#list').val() != {/literal}{Model_Section::LIST_GOODS}{literal});
                }</script>
            {/literal}
            <p>
                 <label>Значок NEW в верхнем меню</label>
                 <input type="checkbox" name="misc[new]" value="1" {if $i->setting('new')}checked="checked"{/if} />
            </p>

            <legend>Вид страницы категории
                <select name="settings[list]" id="list" onchange="list_change()">
                    {html_options options=Model_Section::settings('listroot') selected=$i->settings.list}
                </select>
            </legend>

        </fieldset>
        <p>
            <label style='text-align: left'>Хиты</label>
        </p>
        <div style='padding: 10px'>
            {for $k=0 to 4}
                <div class='hit' style='{if $k %2==0}background: #eee;{/if} padding: 10px; float: left; width: 100%; max-width: 700px;'>
                    <input type='text' value='{if !empty($hits[$k])}{$hits[$k]->code} {$hits[$k]->group_name} {$hits[$k]->name}{/if}' id='good-input-text-{$k}' style='width: 100%;' />
                    <input name='misc[hits][]' type='hidden' class='empty{if empty($hits[$k])} hits{/if}' value='{if !empty($hits[$k])}{$hits[$k]->id}{/if}' />
                </div>
                <script>
                    {literal}
                    $(function() {
                        $("#good-input-text-{/literal}{$k}{literal}").autocomplete({
                            source: function (request, response) {
                                var term = request.term;
                                $.getJSON('/od-men/ajax/autocomplete.php?term=' + term, {
                                    model: 'good',
                                    fields: [ 'code', 'name', 'group_name'],
                                    section_id: {/literal}{$i->id}{literal}
                                }, function (data, status, xhr) {
                                    response(data);
                                });
                            },
                            minLength:1,
                            maxHeight:300,
                            select: function(value, data) {
                                $(this).next().val(data.item.id).removeClass('empty');
                            }
                        }).data( "ui-autocomplete" )._renderItem = function( ul, item ) {

                            // if( item.hit != '0' ) return $("");

                            return $( "<li></li>" )
                                .data( "item.ui-autocomplete", item )
                                .append( "<a><span style='color: green'>" + item.code + '</span> ' + item.group_name + ' ' + item.name + "</a>" )
                                .appendTo( ul );
                        };
                    });
                    {/literal}
                </script>
            {/for}
        </div>
{/if}

    </fieldset>

    <p class="do">
        <input name="edit" value="Сохранить" type="submit" class="btn ok"/>
        <input name="edit" value="Сохранить и вернуться к списку" type="submit" class="btn ok" alt="list" />
    </p>
    <script>
        $(function(){
            $('#section-form').submit(function(){
                if( $('.hits.empty').length < 5 && $('.hits.empty').length != 0 ){
                    alert('Хиты должны быть заполнены');
                    return false;
                }
            });
        });
    </script>
</form>