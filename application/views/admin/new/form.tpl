<form action="" method="post" class="forms forms-columnar" enctype="multipart/form-data">
    <div class="units-row">
        <h1 class="unit-80">#{$i->id} {$i->name}</h1>
        <div class="unit-20"><a href="{$i->get_link(0)}" class="btn" target="_blank">Посмотреть на сайте</a></div>
    </div>
    <p>
        <label for="name">Название</label>
        <input type="text" id="name" name="name" value="{$i->name|default:''|escape:'html'}" class="width-50" />
    </p>
	{include file='admin/seo/widget.tpl'}
    <p>
        <label for="img">Иконка</label>
        <input type="file" id="img" name="img" />
        {if $i->img}{$i->image->get_img()}{/if}
    </p>
    <p>
        <label for="date">Дата<br />[yyyy-mm-dd]</label>
        <input type="text" id="date" name="date" value="{$i->date|default:$smarty.now|date_format:'Y-m-d'}" size="10" maxlength="10" />
    </p>
    <p>
        <label for="preview">Текст кратко</label>
        <textarea id="preview" name="preview" cols="40" rows="2">{$i->preview|default:''}</textarea>
    </p>
    <p>
        <label for="text">Текст</label>
        <textarea id="text" name="text" cols="60" rows="10" class="html">{$i->text|default:''}</textarea>
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