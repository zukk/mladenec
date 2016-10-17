<form action="" xmlns="http://www.w3.org/1999/html" method="post" class="forms forms-columnar" enctype="multipart/form-data">
    {if $i->id}
        <input type="hidden" name="misc[id]" id="action_id" value="{$i->id}" />
        <input type="hidden" name="action_id" id="action_id" value="{$i->id}" />
        <div class="units-row">
            <h1 class="unit-80">#{$i->id} {$i->name}</h1>
            <div class="unit-20"><a href="{$i->get_link(0)}" class="btn" target="_blank">Посмотреть на сайте</a></div>
        </div>
    {else}<h1>Создание акции</h1>{/if}

    <div class="units-row">
        <div class="unit-70">
            <div class="units-row">
                <div class="unit-50">
                    <b>Статус: {if $i->active}<span class="green">Работает</span>{else}<span class="red">Остановлена</span>{/if},</b>
                    {if $i->allowed}<span class="green">разрешена к запуску</span>{else}<span class="red">запрещена к запуску</span>{/if}.
                </div>
                <div class="unit-50">
                    {if $i->id}
                        <b>Тип: </b> {$i->type_name()}<br />
                        {if $i->is_gift_type()}
                            <b>Подарки: </b>{if $i->presents_instock}<span class="green">Есть</span>{else}<span class="red">Кончились</span>{/if}
                        {/if}
                    {/if}
                </div>
            </div>
            
            <p>
                <label for="name">Название</label>
                <input type="text" id="name" name="name" value="{$i->name|escape:html}" class="width-50" />
            </p>
			{include file='admin/seo/widget.tpl'}
            <p>
                <label>Теги для акций</label>
            <div id="magicsuggest"></div>
            </p>
            <p>
                <label>Тип акции</label>
                {html_options name=type id=type selected=$i->type options=$i->types()}
            </p>
            <p>
                <label for="name">Основная акция</label>
                <select name="parent_id">
                    <option value="0">нет</option>
                    {foreach from=ORM::factory('action')->where('active','=','1')->where('parent_id','=','0')->find_all()->as_array('id','name') key=pai item=pan}
                        <option {if $i->parent_id eq $pai}selected="selected"{/if} value="{$pai}">#{$pai} {$pan}</option>
                    {/foreach}
                </select>
                <span class="forms-desc">Если данная акция является частью другой акции, выберите основную акцию в данном поле.</span>
            </p>
            <p>
                <label for="quantity">Сортировка</label>
                <input class="text" type="number" id="order" name="order" value="{$i->order}" />
                <span class="forms-desc">Чем больше число, тем выше акция в списке</span>
            </p>
        </div>
        <div class="unit-30">
            
            <p class="forms-inline">
                <label for="allowed">Активность</label>
                {if $i->incoming_link AND $i->allowed}
                    <input type="hidden" name="allowed" value="1" />
                    <input type="checkbox" disabled="disabled" checked="checked" value="1" />
                {else}
                    {* Внимание! Снятый флаг ОТКЛючает акцию и всю автоматику!!! *}
                    <input type="checkbox" id="allowed" title="При отключении активности акции - прекращается действие её условий, акция отображается в архиве." name="allowed" onclick="return confirm('Меняем активность? Вы точно уверены?')" {if $i->allowed}checked="checked"{/if} value="1" />
                {/if}
                {if $i->active}<input type="hidden" name="active" value="1" />{/if}
                {if $i->incoming_link}
                    <span class="forms-desc red">На акцию ведет входящая ссылка, отключить можно только после 
                        удаления входящей ссылки, например, отключении баннера. Отключите баннер, и 
                        снимите флаг «входящая ссылка», чтобы отключить акцию.</span>
                {/if}
            </p>
            <p>
                <label for="show">Опубликовать</label>
                {if $i->parent_id gt 0}
                    <input type="hidden" name="show" value="0" />
                    <input type="checkbox" disabled="disabled" value="0" />
                    <span class="forms-desc red">Подчиненную акцию публиковать нельзя!</span>
                {else}
                    {if $i->incoming_link AND $i->show}
                        <input type="hidden" name="show" value="1" />
                        <input type="checkbox" disabled="disabled" checked="checked" value="1" />
                    {else}
                        <input type="checkbox" id="show" name="show" onclick="return confirm('Вы точно уверены?')" {if $i->show}checked="checked"{/if} value="1" />
                    {/if}

                    {if $i->incoming_link}
                        <span class="forms-desc red">На акцию ведет входящая ссылка, снять с публикации можно только после 
                            удаления входящей ссылки, например, отключении баннера. Отключите баннер, и 
                            снимите флаг «входящая ссылка», чтобы отключить публикацию.</span>
                    {/if}
                {/if}
            </p>
            <hr />
            <p>
                <label for="main">На главной</label>
                <input type="checkbox" id="main" name="main" value="1" {if $i->main}checked="checked"{/if} />
            </p>
            <p>
                <label for="show_wow">Отображать в Wow акциях</label>
                <input type="checkbox" id="show_wow" name="show_wow" value="1" {if $i->show_wow}checked="checked"{/if} />
            </p>
            <p>
                <label for="show_actions">отображать в списке акций</label>
                <input type="checkbox" id="show_actions" name="show_actions" value="1" {if $i->show_actions}checked="checked"{/if} />
            </p>
            <p>
                <label for="show_actions">отображать товары в акции</label>
                <input type="checkbox" id="show_goods" name="show_goods" value="1" {if $i->show_goods}checked="checked"{/if} />
            </p>
            <hr />
            <p>
                <label for="require_all_presents">Включать только когда все подарки в наличии</label>
                <input type="checkbox" id="require_all_presents" name="require_all_presents" value="1" {if $i->require_all_presents}checked="checked"{/if} />
            </p>
        </div>
    </div>
            
    <p class="forms-inline">
        <label class="unit-40">Начало</label>
        {html_select_date time=$i->from field_array=from field_order=DMY all_empty='' start_year="-4" end_year="+1"}
        {html_select_time minute_interval=1 display_seconds=0 time=$i->from field_array=from all_empty=''}
    </p>
    <p class="forms-inline">
        <label class="unit-40">Окончание</label>
        {html_select_date time=$i->to field_array=to field_order=DMY all_empty='' end_year="+1"}
            {html_select_time minute_interval=1 display_seconds=0 time=$i->to field_array=to all_empty=''}
    </p>
    <p>
        <label for="preview">Краткий текст</label>
        <textarea id="preview" name="preview" cols="80" rows="5" style="height:50px;">{$i->preview}</textarea>
    </p>
    <p>
        <label for="text">Описание</label>
        <textarea id="text" name="text" cols="40" rows="10" class="html">{$i->text}</textarea>
    </p>
    <p class="input-groups">
        <label for="banner">Плашка</label>

        <span class="input-groups">
            <input type="text" size="40" name="banner" class="input-search" id="banner" value="{$i->banner}" />
            <span class="btn-append"><button class="btn" data-filemanager="#banner">Выбрать</button></span>
	    </span>

        {if ! empty($i->banner)}
            <span class="forms-desc"><img src="{$i->banner}" /></span>
        {/if}
        <span class="forms-desc">
            URL файла (желательно без имени домена, если находится у нас на сервере), например /upload/a/b/c/5/123.jpg<br />
            Ширина плашки должна быть строго 712 точек.
        </span>
    </p>
	<p>
        <label for="total">Все товары</label>
        <input type="checkbox" id="total" name="total" onclick="return confirm('Точно учавствуют все товары?')" value="1" {if $i->total}checked="checked"{/if} />
	</p>
    {if $i->id}
        <p class="forms-inline">
            <label>
                Товары, участвующие в&nbsp;акции<br />
                <a onclick="$('#goods_a .trdel').click()" class="no">удалить все</a>
            </label>

            <div class="area" id="goods_a">
                {include file='admin/good/chosen.tpl' goods=$i->goods->with('action_good')->find_all()}
            </div>
        </p>
        {if $i->is_ab_type()}
            <p class="forms-inline">
                <label>
                    Товары Б<br />
                    <a onclick="$('#goods_b .trdel').click()" class="no">удалить все</a>
                </label>
                <div class="area" id="goods_b">
                    {include file='admin/good/chosen.tpl' goods=$i->get_b_goods() mode=b}
                </div>
            </p>
        {/if}
    {/if}
    <div class="units-row">
        <div class="unit-30">
            {if $i->is_gift_type()}
                <p>
                    <label for="each">Давать за каждые</label>
                    <input type="checkbox" id="each" name="each" onclick="return confirm('давать за каждые?')" value="1" {if $i->each}checked="checked"{/if} />
                </p>
            {/if}
            {if $i->type neq Model_Action::TYPE_PRICE}
                <p>
                    <label for="new_user">Только новым пользователям</label>
                    <input type="checkbox" id="new_user" name="new_user" onclick="return confirm('Только новым пользователям?')" value="1" {if $i->new_user}checked="checked"{/if} />
                </p>
            {/if}
        </div>
        <div class="unit-70">
            <p class="forms-inline">
                <label>Накопительная, считать в заказах с:</label>
                {html_select_date time=$i->count_from field_array=count_from field_order=DMY all_empty='' start_year="-1" end_year="+1"}
                <span class="forms-desc">
                    <br>Дата, с которой считаются накопления баллов по акции (с 0 часов начинают считаться)
                </span>
            </p>
            <p class="forms-inline">
                <label>Накопительная, считать в заказах до:</label>
                {html_select_date time=$i->count_to field_array=count_to field_order=DMY all_empty='' start_year="-1" end_year="+2"}
                <span class="forms-desc">
                    <br>Дата, по которую считаются накопления баллов по акции (с 0 часов перестают считаться)
                </span>
            </p>
        </div>
    </div>
    {if $i->count_from OR $i->count_to}
        <p>
            <label for="cart_icon">Иконка в корзине</label>
            <input  type="text" id="cart_icon" name="cart_icon" value="{$i->cart_icon}" class="width-50" />
            {if ! empty($i->cart_icon)}
                <span class="forms-desc"><img src="{$i->cart_icon}" /></span>
            {/if}
            <span class="forms-desc">
                URL файла (желательно без имени домена, если находится у нас на сервере), например /upload/a/f/s/5/123.jpg<br />
                Размер иконки максимум 92 по ширине и 111 точек по высоте.
            </span>
        </p>
        <p>
            <label for="cart_icon_text">Начало текста к иконке в корзине</label>
            <input type="text" id="cart_icon_text" name="cart_icon_text" value="{$i->cart_icon_text}" class="width-50" />
            <span class="forms-desc">Например &laquo;Осталось купить бытовой химии на&raquo;.</span>
        </p>
    {/if}

    {if $i->is_gift_type()}
        <fieldset  class="forms-inline">
            <table>
                <tr>
                    <th>Подарок</th>
                    <th>На складе</th>
                    <th>
                        {if $i->type eq Model_Action::TYPE_GIFT_SUM}Сумма{/if}
                        {if $i->type eq Model_Action::TYPE_GIFT_QTY}Кол-во{/if}
                    </th>
                    <th>Уведомлять меньше</th>
                    <th></th>
                </tr>

                {$present_objs = $i->presents->find_all()->as_array('id')}

                {$presents = DB::select()
                    ->from('z_action_present')
                    ->where('action_id', '=', $i->id)
                    ->order_by('val')
                    ->order_by('good_id')
                    ->execute()->as_array('id')}

                {foreach from=$presents key=id item=p}
                    <tr>
                        <td>
                            <input type="hidden" name="misc[presents][{$id}]" value="1" />
                            <a href="{Route::url('admin_edit',['model' => 'good', 'id' => $p.good_id])}" target="_blank">#{$p.good_id}</a>,
                            {$present_objs[$p.good_id]->group_name} {$present_objs[$p.good_id]->name}</td>
                        <td>{$present_objs[$p.good_id]->qty}&nbsp;шт.</td>
                        <td><b>от&nbsp;{$p.val}
                            {if $i->type eq Model_Action::TYPE_GIFT_SUM}руб.{/if}
                            {if $i->type eq Model_Action::TYPE_GIFT_QTY}шт.{/if}
                            </b>
                        </td>
                        <td>
                            <input class="text" id="gift_id" name="misc[warn_on_qtys][{$p.good_id}]" value="{$p.warn_on_qty|default:10}" size="3" />
                        </td>
                        <td><input type="button" class="btn btn-small btn-red trdel" value="удалить" /></td>
                    </tr>
                {/foreach}

                <tr>
                    <td><input class="text" id="gift_id" name="misc[present_new][good_id]" value="" class="width-100" /><br /><small>Введите ID подарка</small></td>
                    <td></td>
                    <td><input class="text" id="gift_id" name="misc[present_new][val]" value="" class="width-100" /><br /><small>Введите сумму.</small></td>
                    <td><input class="text" id="gift_id" name="misc[present_new][warn_on_qty]" value="" size="4" /><br /><small>Введите количество, при котором уведомлять.</small></td>
                    <td></td>
                </tr>
            </table>
        </fieldset>
    {else}
        {if in_array($i->type, [Model_Action::TYPE_PRICE_QTY,Model_Action::TYPE_PRICE_QTY_AB])}
            <p>
                <label for="quantity">Заказанное количество:</label>
                <input class="text" id="quantity" name="quantity" value="{$i->quantity}" />
            </p>
            <p>
                <label for="sum">Скидка, процентов:</label>
                <input class="text" id="sum" name="sum" value="{$i->sum}" />
            </p> 
        {/if}
        {if in_array($i->type, [Model_Action::TYPE_PRICE_SUM, Model_Action::TYPE_PRICE_SUM_AB])}
            <p>
                <label for="quantity">Скидка, процентов:</label>
                <input class="text" id="quantity" name="quantity" value="{$i->quantity}" />
            </p>
            <p>
                <label for="sum">При заказе на сумму выше:</label>
                <input class="text" id="sum" name="sum" value="{$i->sum}" />
            </p> 
        {/if}
    {/if}

    <fieldset>
     <p>
        <label for="name">Активна на витрине:</label>
        <select name="vitrina_active">
            <option value="all" {if $i->vitrina_active eq 'all'} selected="selected"{/if}>На всех витринах</option>
            {foreach from=$vitrinas key=dcode item=ddata}
                <option value="{$dcode}" {if $i->vitrina_active eq $dcode} selected="selected"{/if}>{$ddata['host']}</option>
            {/foreach}
        </select>
        <span class="forms-desc">Акция будет применяться при расчете корзины на выбранной витрине.</span>
    </p>

    <p>
        <label for="name">Опубликована на витрине:</label>
        <select name="vitrina_show">
            <option value="all" {if $i->vitrina_active eq 'all'} selected="selected"{/if}>На всех витринах</option>
            {foreach from=$vitrinas key=dcode item=ddata}
                <option value="{$dcode}" {if $i->vitrina_show eq $dcode} selected="selected"{/if}>{$ddata['host']}</option>
            {/foreach}
        </select>
        <span class="forms-desc">Акция будет отображаться на выбранной витрине.</span>
    </p>
    </fieldset>
    <p>
        <label for="incoming_link">Входящая ссылка</label>
        <input type="checkbox" id="incoming_link" name="incoming_link" value="1" {if $i->incoming_link}checked="checked"{/if} />
        <span class="forms-desc">Если установлен этот флажок - значит, что на акцию ведет входящая ссылка! 
            При снятии с публикации, отключении и изменении акции убедитесь, что ведущие на акцию ссылки
            также удалены.</span>
    </p>
    <p>
        <label for="link_comment">Комментарий к входящей ссылке</label>
        <textarea id="link_comment" class="wide" cols="80" rows="5" style="height:5em;" name="link_comment" >{$i->link_comment}</textarea>
    </p>
    <p>
        <label for="sales_notes">Добавка в YML sales_notes</label>
        <input id="sales_notes" class="width-100" name="sales_notes" value="{$i->sales_notes}" />
    </p>

    <p class="forms-inline">
        <input name="edit" value="Сохранить" type="submit" class="btn btn-green"/>
        <input name="edit" value="Сохранить и вернуться к списку" type="submit" class="btn btn-green" alt="list" />
    </p>
</form>
    {$subactions = ORM::factory('action')->where('parent_id','=',$i->id)->find_all()->as_array()}
    {if ! empty($subactions)}
        <p>
            <label for="subactions">Подчиненные акции</label>
            <div class="area hi">
                <table>
                    {foreach from=$subactions item=sa}
                        <tr>
                            <td><a href="{Route::url('admin_edit',['model'=>'action','id'=>$sa->id])}">{$sa->id}</a></td>
                            <td>{$sa->name}</td>
                            <td>
                                {if $i->is_gift_type() AND $i->presents_instock eq 1}<span class="green">подарки на складе</span>
                                {elseif $i->is_gift_type() AND $i->presents_instock eq 0}<span class="red">подарки кончились</span>
                                {elseif $i->is_price_type()}скидка
                                {/if}
                            </td>
                            <td>{if $sa->allowed}<span class="green">разрешена</span>{else}<span class="red">запрещена</span>{/if}</td>
                            <td>{if $sa->active}<span class="green">работает</span>{else}<span class="red">отключена</span>{/if}</td>
                        </tr>
                    {/foreach}
                </table>
            </div>
            <span class="forms-desc">Чем больше число, тем выше акция в списке</span>
        </p>
    {/if}

<script>
    $(function() {
        var action_id = $("#action_id").val();
        $.ajax({
            url: '/admin/actiontags.php',
            method: 'POST',
            data: {
                id: action_id
            },
            dataType: 'json',
            success: function(data){

                var obj = data.ids;
                var arr = [];

                $.each(obj, function(value, index){
                    arr.push(index.id);
                });

                $('#magicsuggest').magicSuggest({
                    style: 'border: 1px solid #A9A9A9 !important',
                    value: arr,
                    name: 'actiontag_id',
                    data: data.items
                });
            }
        });

    });
</script>