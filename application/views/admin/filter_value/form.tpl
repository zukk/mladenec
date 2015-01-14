<h1>Значение фильтра {$i->filter->name}, категория {$i->filter->section->name}</h1>

<form action="" method="post" class="forms forms-columnar" enctype="multipart/form-data">
    <div class="units-row">
        <h1 class="unit-80">#{$i->id} {$i->name}</h1>
    </div>
	<p>
		<label for="code">Код в 1с</label>
		<input type="text" id="code" name="code" value="{$i->code|default:''|escape:'html'}" class="width-50" />
	</p>
    <p>
        <label for="name">Значение</label>
        <input type="text" id="name" name="name" value="{$i->name|default:''|escape:'html'}" class="width-50" />
    </p>
    <p>
        <label for="img">Картинка 225x120</label>
        <input type="file" id="img" name="img" />
        {if ! empty($i->img)}{$i->image->get_img()}{/if}
    </p>
    <p>
        <label for="sort">Сортировка</label>
	    <input type="text" id="sort" name="sort" value="{$i->sort|default:''|escape:'html'}" class="width-50" />
    </p>
    <p>
        <input name="edit" value="Сохранить" type="submit" class="btn ok"/>
        <input name="edit" value="Сохранить и вернуться к списку" type="submit" class="btn ok" alt="list" />
    </p>
</form>