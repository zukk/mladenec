<h2>#{$i->id|default:''} {$i->title|default:''}</h2>
<form action="" class="forms forms-inline" method="post">
    <div class="units-row">
        <div class="unit-50">
            <b>Наименование</b><br />
            <div>
                <input type="text" name="title" required="required" class="width-100" value="{$i->title|default:''}">
            </div>
            <br />
            <b>Правило</b><br />
            <div>
                <input type="text" name="rule" required="required" class="width-100" value="{$i->rule|default:''}">
            </div>
            <br />
            <b>Тип</b><br />
            <div>
                {assign var=seo_title value=['seo_title']}
                <select name="type" class="width-100">
                    {foreach from=$seo_title item=title}
                        <option value="{$title}"{if $i->type == $title} selected{/if}>{$title|replace:'_':' '}</option>
                    {/foreach}
                </select>
            </div>
            <br />
            <b>Активность</b><br />
            <div>
                {assign var=active value=[0 => 'Нет', 1 => 'Да']}
                <select name="active" class="width-100">
                    {foreach from=$active key=k item=act}
                        <option value="{$k}" {if $i->active == $k} selected{/if}>{$act}</option>
                    {/foreach}
                </select>
            </div>
            <br />
            <b>Описание</b><br />
            <div>
                <div>Вы можете использовать следующие атрибуты:<br /></div>

                <div>Группа: [group]</div>
                <div>Название продукта: [name]</div>
                <div>Категория: [section]</div>
                <div>Бренд: [brand]</div>
                <div>Страна: [country]</div>
                <div>Цена: [price]</div>
            </div>
        </div>
    </div>
    <a class="btn" href="/od-men/seotemplates">Назад</a>
    <input type="submit" class="btn" name="edit" value="Сохранить">
</form>