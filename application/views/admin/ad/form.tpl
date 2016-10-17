<h2>Баннер {$i->name}</h2>

<form method="post" action="" enctype="multipart/form-data" class="forms columnar">
<fieldset>
    <ul>
        <li>
            <label for="code">Группа</label>
            {html_options options=$i->types() name="code" id="code" selected=$i->code}
        </li>
        <li>
            <label for="name">Название</label>
            <input type="text" name="name" id="name" value="{$i->name}" class="width-50" />
        </li>
        <li>
            <label for="url">URL</label>
            <input type="text" name="url" id="url" value="{$i->url}" class="width-50"/>
        </li>
        <li>
            <label for="newtab">В&nbsp;новом окне</label>
            <input type="checkbox" id="newtab" name="newtab" value="1" {if $i->newtab}checked="checked"{/if} />
        </li>
        <li>
            <label>Картинка</label>
            <div class="area hi">
                {$i->show(FALSE)}
                <input name="file" type="file" />
            </div>
        </li>
        <li>
            <label>Включить с</label>
            {html_select_date time=$i->from field_array=from field_order=DMY all_empty=''}
        </li>
        <li>
            <label>Выключить с</label>
            {html_select_date time=$i->to name=to field_array=to field_order=DMY all_empty=''}
        </li>
        <li>
            <label for="active">Активен</label>
            <input type="checkbox" id="active" name="active" value="1" {if $i->active}checked="checked"{/if} />
        </li>
        <li class="do">
            <input name="edit" value="Сохранить" type="submit" class="btn ok"/>
            <input name="edit" value="Сохранить и вернуться к списку" type="submit" class="btn ok" alt="list" />
        </li>
    </ul>
</fieldset>
</form>

{if $i->id}
    <p><a href="{$i->id}/del" onclick="return confirm('Удалить насовсем?')" class="red">Удалить</a></p>
{/if}
