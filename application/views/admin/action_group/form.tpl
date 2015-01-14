<form action="" xmlns="http://www.w3.org/1999/html" method="post" class="forms forms-columnar width-wrap" enctype="multipart/form-data">
    {if $i->id}
        <input type="hidden" name="misc[id]" id="action_id" value="{$i->id}" />
        <div class="units-row">
            <h1 class="unit-70">#{$i->id} {$i->name}</h1>
            <div class="unit-30">
                {if $i->active}
                    <a href="{$i->get_link(0)}" class="btn" target="_blank">Работает, посмотреть на сайте</a>
                {else}
                    <span class="red">Остановлена</span>
                {/if}
            </div>
        </div>
    {else}<h1>Создание группы акций</h1>{/if}

    
    <div class="units-row">
        <div class="unit-70">
            <p>
                <label for="name">Название</label>
                <input type="text" id="name" name="name" value="{$i->name}" class="width-50" />
            </p>
            <p>
                <label for="quantity">Сортировка</label>
                <input class="text" type="number" id="order" name="order" value="{$i->order}" />
                <span class="forms-desc">Чем больше число, тем выше акция в списке</span>
            </p>
            <p>
                <label for="preview">Краткий текст</label>
                <textarea id="preview" name="preview" cols="80" rows="5" style="height:50px;">{$i->preview}</textarea>
            </p>
            <p>
                <label for="show_vitrina">Опубликована на витрине:</label>
                <select name="show_vitrina">
                    {foreach from=Conf::vitrinas()  key=vid item=vname}
                        <option value="{$vid}" {if $i->show_vitrina eq $vid} selected="selected"{/if}>{$vname}</option>
                    {/foreach}
                </select>
                <span class="forms-desc">Акция будет отображаться на выбранной витрине.</span>
            </p>
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
        </div>
        <div class="unit-30">
            
            <p class="forms-inline">
                <label for="allowed">Активность</label>
                {if $i->incoming_link AND $i->allowed}
                    <input type="hidden" name="allowed" value="1" />
                    <input type="checkbox" disabled="disabled" checked="checked" value="1" />
                {else}
                    {* Внимание! Снятый флаг ОТКЛючает акцию и всю автоматику!!! *}
                    <input type="checkbox" id="active" title="При отключении активности группы акций - прекращается действие её условий, акция отображается в архиве." name="active" onclick="return confirm('Меняем активность? Вы точно уверены?')" {if $i->active}checked="checked"{/if} value="1" />
                {/if}
                {if $i->active}<input type="hidden" name="active" value="1" />{/if}
            </p>
            <p>
                <label for="show">Опубликовать</label>
                {if $i->incoming_link AND $i->show}
                    <input type="hidden" name="show" value="1" />
                    <input type="checkbox" disabled="disabled" checked="checked" value="1" />
                {else}
                    <input type="checkbox" id="show" name="show" onclick="return confirm('Вы точно уверены?')" {if $i->show}checked="checked"{/if} value="1" />
                {/if}
                {if $i->incoming_link}
                    <span class="forms-desc red">На акцию ведет входящая ссылка, отключить или снять с публикации можно только после 
                        удаления входящей ссылки, например, отключении баннера. Отключите баннер, и 
                        снимите флаг «входящая ссылка», чтобы отключить публикацию.</span>
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
                <label for="require_all">Включать только когда все вложенные акции включены</label>
                <input type="checkbox" id="require_all" name="require_all" value="1" {if $i->require_all}checked="checked"{/if} />
            </p>
        </div>
    </div>
    
    <p>
        <label for="text">Описание</label>
        <textarea id="text" name="text" cols="40" rows="10" class="html">{$i->text}</textarea>
    </p>
    <p class="input-groups">
        <label for="banner">Плашка</label>
        <span class="btn-group">
            <input type="text" size="40" name="banner" class="input-search" id="banner" value="{$i->banner}" />
            <span class="btn btn-round" data-filemanager="#banner">Выбрать</span>
        </span>
        {if ! empty($i->banner)}
            <span class="forms-desc"><img src="{$i->banner}" /></span>
        {/if}
        <br />
        <span class="forms-desc">URL файла (желательно без имени домена, если находится у нас на сервере), например /upload/a/f/s/5/123.jpg<br />
        Ширина плашки должна быть строго 712 точек. В правом нижнем углу следует оставить место шириной 165 точек и 
        высотой 35 точек для автоматически прикрепляемой кнопки. </span>
    </p>
    <p>
        <label for="cart_icon">Иконка в корзине</label>
        <input  type="text" id="cart_icon" name="cart_icon" value="{$i->cart_icon}" class="width-50" />
        {if ! empty($i->cart_icon)}
            <span class="forms-desc"><img src="{$i->cart_icon}" /></span>
        {/if}
        <span class="forms-desc">URL файла (желательно без имени домена, если находится у нас на сервере), например /upload/a/f/s/5/123.jpg<br />
        Размер иконки максимум 92 по ширине и 111 точек по высоте.</span>
    </p>
    <p>
        <label for="cart_icon_text">Начало текста к иконке в корзине</label>
        <input type="text" id="cart_icon_text" name="cart_icon_text" value="{$i->cart_icon_text}" class="width-50" />
        <span class="forms-desc">Например &laquo;Осталось купить бытовой химии на&raquo;.</span>
    </p>

    <p class="forms-inline">
        <input name="edit" value="Сохранить" type="submit" class="btn btn-green" />
        <input name="edit" value="Сохранить и вернуться к списку" type="submit" class="btn btn-green" alt="list" />
    </p>
</form>
{if $i->id}
    <div class="width-wrap">
        <h2>Акции</h2>
        {include file='admin/action_group/actions.tpl' actions=$i->actions()}
    </div>
{/if}
<div class="width-wrap">
    {*if $i->id}
        <h2>Участвующие в акции товары:</h2>
        <p>Товаров: {$goods|count} 
<a href="{Route::url('admin_ajax_ags',['id'=>$i->id])}" data-fancybox-type="ajax" class="green btn btn-round">+ Добавить</a></p>
        
        <div class="area">
            {include file='admin/action/goods.tpl' action_id=$i->id goods=$goods}
        </div>
        
        
        
        {if $i->is_ab_type()}
            <h2>Товары Б:</h2>
            <p>Товаров Б: {$b_goods|count} 
<a href="{Route::url('admin_ajax_ags',['id'=>$i->id])}?b=1" data-fancybox-type="ajax" class="green btn btn-round">+ Добавить</a></p>
            <div class="area">
                {include file='admin/action/goods.tpl' action_id=$i->id goods=$b_goods b=1}
            </div>
        {/if}
    {/if*}

</div>        
