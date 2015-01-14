<form method="post" action="" enctype="multipart/form-data" class="forms forms-columnar">
    <div class="units-row">
        <h1 class="unit-80">#{$i->id} {$i->name}</h1>
    </div>
    
	<p>
	   <label for="name">Название</label>
	   <input name="name" id="name" value="{$i->name|escape:'html'}" class="width-100" />
   </p>
   {include file='admin/seo/widget.tpl'}
	<p>
	   <label for="name">Фразы для поиска</label>
	   <textarea name='search_words' class='width-100' style='height: 100px'>{$i->search_words}</textarea>
   </p>
	<p>
	   <label for="img225">Изображение</label>
        {if $i->img225}
            <img src="{$i->get_img()}" alt="" />
        {else}
            <strong>отсутствует</strong>
        {/if}
		<br />
        <input type="file" name="img225" />
   </p>

    <div class="units-row">
        <div class="unit-80">
            <input name="edit" value="Сохранить" type="submit" class="btn btn-green" />
            {if $i->id}<input name="edit" value="Сохранить и вернуться к списку" type="submit" class="btn  btn-green ok" alt="list" />{/if}
        </div>
    </div>
</form>