<h1>Товары в категории озона</h1>

<p>
<strong>{$i->name}</strong>
<br >
{$i->path_name}
</p>
<form action="" method="post" enctype="multipart/form-data">

<input type="hidden" name="id" value="{$i->id}" />
<div class="area" id="goods_a">
    {include file='admin/good/chosen.tpl' goods=$i->get_goods()}
</div>

<div class="r">
    <input name="del" value="Удалить все" type="button" class="btn btn-red btn_del"  onclick="$('#goods_a .trdel').click()"/>
</div>

<div class="do">
    <input name="edit" value="Сохранить" type="submit" class="btn btn-green"/>
</div>

</form>