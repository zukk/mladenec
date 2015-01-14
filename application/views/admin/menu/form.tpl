<form action="" method="post" class="forms forms-columnar" enctype="multipart/form-data">
    <div class="units-row">
        <h1 class="unit-80">#{$i->id} {$i->name}</h1>
        <div class="unit-20"><a href="/{$i->link}" class="btn" target="blank">Посмотреть на сайте</a></div>
    </div>

     {if $i->id}{/if}
    <p>
    
        <label for="parent_id">Родитель</label>
        <select name="parent_id" id="parent_id">
            <option value=""></option>
            {html_options options=Model_Menu::parents($i->id) selected=$i->parent_id}
        </select>
    </p>
    <p>
        <label for="name">Название</label>
        <input type="text" id="name" name="name" value="{$i->name}" class="width-50" />
    </p>
    <p>
        <label for="link">Ссылка</label>
        <input type="text" id="link" name="link" value="{$i->link}" class="width-50" />
    </p>
    <p>
        <label for="text">Текст</label>
        <textarea id="text" name="text" cols="80" rows="5" class="html">{$i->text}</textarea>
    </p>
    <p>
        <label>SEO description</label>
        <textarea name="description" rows="15" cols="50" style="height:100px;">{$i->description}</textarea>
    </p>
    <p>
        <label for="menu">Показывать в меню</label>
        <input type="checkbox" id="menu" name="menu" value="1" {if $i->menu}checked="checked"{/if} />
    </p>

    <p>
        <label for="show">Активность</label>
        <input type="checkbox" id="show" name="show" value="1" {if $i->show}checked="checked"{/if} />
    </p>
    <p class="do">
        <input name="edit" value="Сохранить" type="submit" class="btn ok"/>
        <input name="edit" value="Сохранить и вернуться к списку" type="submit" class="btn ok" alt="list" />
    </p>

{if $i->id}
    <p><a href="{$i->id}/del" onclick="return confirm('Удалить насовсем?')" class="red">Удалить</a></p>
{/if}
</form>