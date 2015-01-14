<form action="" method="post" class="forms forms-columnar" enctype="multipart/form-data">
    <div class="units-row">
        <h1 class="unit-80">#{$i->id} {$i->name}</h1>
        <div class="unit-20"><a href="{$i->get_link(0)}" class="btn" target="_blank">Посмотреть на сайте</a></div>
    </div>
    <p>
        <label for="name">Название</label>
        <input type="text" id="name" name="name" value="{$i->name}" class="width-50" />
    </p>
   {include file='admin/seo/widget.tpl'}
    <p>
        <label for="img">Иконка</label>
        <input type="file" id="preview_img" name="preview_img" />
        {if $i->preview_img}{$i->minimg->get_img()}{/if}
    </p>
    <p>
        <label for="preview">Текст кратко</label>
        <textarea id="preview" name="preview" cols="40" rows="2">{$i->preview|default:''}</textarea>
    </p>
    <p>
        <label for="text">Текст</label>
        <textarea id="text" name="text" cols="80" rows="5" class="html">{$i->text}</textarea>
    </p>
    <p>
        <label for="active">Активность</label>
        <input type="checkbox" id="active" name="active" value="1" {if $i->active}checked="checked"{/if} />
    </p>
    <p>
        <input name="edit" value="Сохранить" type="submit" class="btn ok"/>
        <input name="edit" value="Сохранить и вернуться к списку" type="submit" class="btn ok" alt="list" />
    </p>

{if $i->id}
    <p><a href="{$i->id}/del" onclick="return confirm('Удалить насовсем?')" class="red">Удалить</a></p>
{/if}
</form>