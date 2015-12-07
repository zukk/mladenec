<div class="units-row">
    <h1 class="unit-80">#{$i->id} {$i->name}</h1>
    <div class="unit-20"><a href="{$i->get_link(0)}" class="btn" target="_blank">Посмотреть на сайте</a></div>
</div>

<form id="section-form" action="" method="post" class="forms forms-columnar" enctype="multipart/form-data">

<div class="units-row">
    <div class="unit-50">
        <table>
            <tr><td><b>Название:</b></td><td>{$i->name}</td></tr>
            <tr>
                <td>{if $i->active}<b class="green">активный</b>{else}<b class="red">не активный</b>{/if}</td>
                <td>{$i->translit}</td>
            </tr>
            <tr><td colspan="2">
                <p>
                    <label for="name">market_category</label>
                    <input class="width-100" type="text" id="market_category" name="market_category" value="{$i->market_category}" />
                </p>
                <p>
                    <label>Картинка 93x96 (для мобильной версии):</label>
                    {if $i->image93}{$i->img93->get_img()}{/if}
                    <input type="file" name="img93" />
                </p>
            </td></tr>
            <tr>
                <td colspan="2">
                    <p>
                        <label for="name">Wikimart_category</label>
                    </p>
                    {$wiki_categories = ORM::factory('wikicategories')->find_all()->as_array()}

                    {assign var='res' value = array()}
                    {foreach from=$wiki_categories key=k item=categories}
                        {$res[$categories->parent_id][$categories->category_id] = $categories}
                    {/foreach}

                    {function build_tree res=0 parent_id=0 only_parent=false}
                        {if $parent_id ==0}
                            <div id="jstree" style="display:none">
                        {/if}
                        {if (is_array($res) and isset($res.$parent_id))}
                            <ul>
                                {if ($only_parent == false)}
                                    {foreach $res.$parent_id as $cat}
                                        <li class="level_{$cat->id}" id="{$cat->id}">
                                            {$cat->name}
                                            {build_tree res=$res parent_id=$cat->category_id}
                                        </li>
                                    {/foreach}
                                {*elseif (is_numeric($only_parent))}
                                    {$cat = $res.parent_id.only_parent}
                                    <li>
                                        {$cat->name}
                                        {build_tree res=$res parent_id=$cat->category_id}
                                    </li>
                                    *}
                                {/if}
                            </ul>
                        {/if}
                        {if $parent_id ==0}
                            </div>
                        {/if}
                    {/function}

                    {build_tree res=$res parent_id=0}
                </td>
            </tr>

            {if $i->parent_id}
                <tr><td><b>Родитель:</b></td><td><a href="{Route::url('admin_edit',['model' => 'section', 'id' => $i->parent_id])}" target="_blank">{$i->parent->name}</a></td></tr>
                <tr><td colspan="2">
                    <p>
                        <label>Картинка 225x120 (для страницы {HTML::anchor($i->parent->get_link(0), $i->parent->name, ["target" => "_blank"])})</label>
                        {if $i->image}{$i->img->get_img()}{/if}
                        <input type="file" name="img" />
                    </p>
                    <p id="sub_menu" {if $i->settings.sub eq Model_Section::SUB_NO}style="display:none;"{/if}>
                        <label for="img_menu">Картинка 200x110 (для меню 3го уровня):</label>
                        {if $i->img_menu}{$i->menu_img->get_img()}{/if}
                        <input type="file" name="img_menu" />
                    </p>
                </td></tr>
            {else}
                <tr><td><b>Значок NEW в верхнем меню</b></td><td><input type="checkbox" name="misc[new]" value="1" {if $i->setting("new")}checked="checked"{/if} /></td></tr>
            {/if}
        </table>
    </div>
    <div class="unit-50">
        <h4>SEO</h4>
        <p>
            <label for="h1">h1</label>
            <input type="text" id="h1" name="h1" value="{$i->h1}" size="50" />
        </p>
        {include file="admin/seo/widget.tpl"}
    </div>
</div>

<script>
$(function() {
    var resort = function(event, ui) {
        var ul = $(ui.item).parent();
        $('> * > input[name^=sort]', ul).each(function(index, item) { $(this).val(index)});
    };


    $(".sortableItems").sortable({
        axis: "y",
        stop: resort
    });
    $(".sortableItems").disableSelection();

    $('input[name="settings[sub]"]').change(function() {
        $("#sub_menu").toggle($(this).val() != {Model_Section::SUB_NO});
        if ($(this).attr('rel')) {
            $("#sub_filter").val($(this).attr('rel'));
        }
    });
});
$(function(){
    // create an instance when the DOM is ready
    $.jstree.defaults.core.themes.variant = "small";
    $('#jstree').on('loaded.jstree', function() {
        $('#jstree').jstree(true).select_node('#3');
    });
    $('#jstree').jstree({
        "core" : {
            "multiple" : false,
            "load_open" : true
        },
        "checkbox" : {
            "keep_selected_style" : false
        },
        "plugins" : [ "wholerow", "checkbox" ]
    });

    //$('#jstree').load_all();
    // bind to events triggered on the tree
    $('#jstree').on("changed.jstree", function (e, data) {
        console.log(data.selected);
        var id_ckecked = data.selected;
        //alert(id_ckecked.length);

        var wikimart_cat_id = $("#wikimart_cat_id"); // input field

        if(id_ckecked.length == 0 || id_ckecked.length == 1) {
            if(wikimart_cat_id.length) {
                $("#wikimart_cat_id").val(id_ckecked);
            } else {
                $('<input>').attr({
                    type: 'hidden',
                    id: 'wikimart_cat_id',
                    name: 'wikimart_cat_id',
                    value: id_ckecked
                }).appendTo('#jstree');
            }
        } else {
            $("#wikimart_cat_id").val('');
        }
    });
    $('#jstree').show();

    var wikimart_cat_id_db = $("#3").find("li");
    var test = wikimart_cat_id_db.attr('class');
    //alert(test);

    /*$('li').each(function(i, elem){
        if($(this).hasClass("level_1")){
            var te = $('li').children();
            //console.log(te);
            var test = elem.attr('id');
            alert(test);
            return false;
        }
    });*/


});
</script>

    {if $i->parent_id}

    {*настройка категории 2го уровня - бренды, фильтры, меню3го уровня*}

    <div class="units-row">
        <input type="hidden" name="settings[list]" value="{$i->settings.list|default:0}" />
        <div class="unit-50">
            <h4>Меню 3го уровня</h4>
            <label> отключить<input type="radio" name="settings[sub]" value="{Model_Section::SUB_NO}" {if $i->settings.sub}checked{/if}/></label>

            <input type="hidden" id="sub_filter" name="settings[sub_filter]" value="{$i->settings.sub_filter|default:0}" />

            <p>Первые 7 брендов будут в&nbsp;топе и&nbsp;всегда видны пользователю. Поэтому они выделены жирным.<br />
            Они будут отсортированы как в&nbsp;списке. Остальные&nbsp;&mdash; всегда по&nbsp;алфавиту.</p>

            <div class="sortableItems">
                <div class="sortableItems" id="sortableBrands">
                    <label> включить как меню 3го уровня <input type="radio" name="settings[sub]" value="{Model_Section::SUB_BRAND}" {if $i->settings.sub eq Model_Section::SUB_BRAND}checked{/if}/></label>
                {foreach from=$sBrands item=brand}
                    <div>
                        {$brand->name}
                        <input type="hidden" name="settings[brands][]" value="{$brand->id}" />
                        <label class="right badge badge-green"><input type="checkbox" name="settings[b_hit][{$brand->id}]"
                        {if not empty($brand->hit)}checked="checked"{/if} />хит!</label>
                    </div>
                {/foreach}
                </div>

                {if not empty($subs.filters)}
                    {foreach from=$subs.filters item=f key=fid name=f}
                        <div class="sortableF">
                            <input type="hidden" name="sort[filter][{$fid}]" value="{$smarty.foreach.f.index}" />
                            <label> включить как меню 3го уровня <input type="radio" name="settings[sub]" rel="{$fid}" value="{Model_Section::SUB_FILTER}" {if $i->settings.sub eq Model_Section::SUB_FILTER and $i->settings.sub_filter eq $fid}checked{/if}/></label>
                            <h5>{$f}</h5>
                            <ul class="sortableItems">
                            {foreach from=$subs.vals[$fid] item=v key=vid name=v}
                                <li>
                                    {$v.name} ({$v.qty}) <input type="hidden" name="sort[value][{$vid}]" value="{$smarty.foreach.v.index}" />
                                </li>
                            {/foreach}
                            </ul>
                        </div>
                    {/foreach}
                {/if}
            </div>
        </div>

        <div class="unit-50">
            <h5>Сортировка товаров</h5>
            <p>Перетаскивайте мышкой элементы. В&nbsp;каком порядке они здесь, в&nbsp;таком&nbsp;же будут
                в&nbsp;селекте в&nbsp;рубрике. Значение первого сверху элемента определяет сортировку <nobr>по-умолчанию</nobr>.</p>

            <div class="sortableItems" id="sortableOrders">
                {foreach from=$sortableOrderItems key=value item=item}
                <div>
                    {$item}
                    <input type="hidden" name="settings[orderByItems][]" value="{$value}" />
                </div>
                {/foreach}
            </div>
            <br />

            <h5>Товаров на странице</h5>
                {if not empty($i->settings.per_page) and is_array($i->settings.per_page)}
                    {assign var=per_page value=implode(',',$i->settings.per_page)}
                {/if}
                <input name="settings[per_page]" value="{$per_page|default:'20,40,80'}" />
                <p>Укажите возможное число товаров на странице через запятую, например "20,40,80"</p>
            <br />
            <h5>Товаров в ряду</h5>
            {Form::select('settings[row]', [3 => 3, 4 => 4], $i->settings.row|default:4)}
            <br />
            <h5>Вкладки в карточках</h5>
            <div class="sortableItems" id="sortableGoodTabs">
                {foreach from=$goodTabs item=tab}
                {assign var=readonly value=in_array($tab, $defaultGoodTabs)}
                <div style"{if $readonly} border-style: solid;{/if}">
                    {if not $readonly}
                        {$tab}
                    {else}
                        <b>{$tab}</b>
                    {/if}
                    {if not $readonly}
                        <span class="right badge badge-red">удалить</span>
                    {/if}
                    <input type="hidden" name="settings[goodTabs][]" value="{$tab}" />
                </div>
                {/foreach}
            </div>
            <p>
                <input type="text" class="addGoodTab" value="" style="float: left; margin: 2px 5px;" /><button class="btn addGoodTab-btn btn-append">Добавить вкладку</button>
                <script>
                    $(function() {
                        var removeItem = function() {
                            if (confirm("действительно удалить вкладку?"))
                                $(this).parent().remove();
                                return false;
                            };
                            $("#sortableGoodTabs div span").click(removeItem);
                            $(".addGoodTab-btn").click(function(){
                                var o = $(".addGoodTab");
                                if( o.val() != "" ){
                                    var $del = $("<span style=\"color: red; float: right; cursor: pointer;\">удалить</span>");
                                    var $item = $("<div>"+o.val()+"<input type=\"hidden\" name=\"settings[goodTabs][]\" value=\""+o.val()+"\" /></div>");
                                    $("#sortableGoodTabs").append($item);
                                    $del.appendTo($item).click(removeItem);
                                    o.val("");
                                }
                                return false;
                            });
                    });
                </script>
            </p>
        </div>
    </div>

    {else}

    {* настройки категорий 1го уровня - хиты для главной *}
    {$hits = Model_Good::get_hitz($i->id, TRUE)}
    <div>
        <h4>Хиты</h4>
        {for $k=1 to 5}
        <fieldset class="hit width-80"><legend><span class="badge badge-black">{$k}</span></legend>
            <input type="text" class="hit_code width-100{if not empty($hits[$k]) and ($hits[$k]->show == 0 OR $hits[$k]->qty == 0)} input-error{/if}" value="{if ! empty($hits[$k])}{$hits[$k]->code} {$hits[$k]->group_name|escape:html} {$hits[$k]->name|escape:html}{/if}" />
            <input name="misc[hits][{$k}]" type="hidden" value="{if ! empty($hits[$k])}{$hits[$k]->id}{/if}" />
            или {assign var=n value=$k+5}
            <input type="text" class="hit_code width-100{if not empty($hits[$n]) and ($hits[$n]->show == 0 OR $hits[$n]->qty == 0)} input-error{/if}" value="{if ! empty($hits[$n])}{$hits[$n]->code} {$hits[$n]->group_name|escape:html} {$hits[$n]->name|escape:html}{/if}" />
            <input name="misc[hits][{$n}]" type="hidden" value="{if ! empty($hits[$n])}{$hits[$n]->id}{/if}" />
        </fieldset>
    {/for}
    </div>

    <script>
    $(document).ready(function() {

        $(".hit_code")
        .on('blur keyup mouseup', function () {
            var v = $.trim($(this).val());
            if (v == '') $(this).next().val('').addClass("empty")
        })
        .autocomplete({
            source: function (request, response) {
                var term = $.trim(request.term);
                $.getJSON("/od-men/ajax/autocomplete.php?term=" + term, {
                    model: "good",
                    fields: [ "id1c", "name", "group_name"],
                    section_id: {$i->id}
                }, function (data, status, xhr) {
                    response(data);
                });
            },
            minLength:1,
            maxHeight:300,
            select: function(value, data) {
                $(this).removeClass('input-error');
                $(this).next().val(data.item.id).removeClass("empty");
            }
        })

        .data("ui-autocomplete")

        ._renderItem = function(ul, item) {

            if( item.hit != "0" ) return $("");

            return $( "<li></li>" )
                .data( "item.ui-autocomplete", item )
                .append( "<a><span style=\"color: green\">" + item.code + "</span> " + item.group_name + " " + item.name + "</a>" )
                .appendTo( ul );
        };
    });
    </script>
{/if}

    <p>
        <label for="text">Текст страницы</label>
        <textarea name="text" class="html" rows="10" cols="40">{$i->text}</textarea>
    </p>

    <p class="do">
        <input name="edit" value="Сохранить" type="submit" class="btn ok"/>
        <input name="edit" value="Сохранить и вернуться к списку" type="submit" class="btn ok" alt="list" />
    </p>

</form>