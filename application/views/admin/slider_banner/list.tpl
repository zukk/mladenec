<form action="" class="forms forms-inline">
    <div class="units-row">
        <h1  class="unit-75">Управление баннерами в слайдерах</h1>
        <div class="unit-25"><a href="{Route::url('admin_add',['model'=>'slider_banner'])}" class="btn btn-green">+ добавить баннер</a></div>
    </div>
    <fieldset>
            <legend>Поиск товаров</legend>
        <div class="units-row">
            <div class="unit-25">
                <b>Название</b><br /><input type="text" class="width-100" name="name" value="{$smarty.get.name|default:''}">
            </div>
            <div class="unit-25">
				<b>Сладер</b><br />
                <select name='slider_id'>
					<option value='0'>все</option>
					<option value='1'{if $smarty.get.slider_id|default eq 1} selected{/if}>Младенец</option>
					<option value='2'{if $smarty.get.slider_id|default eq 2} selected{/if}>Итмарт</option>
				</select>
            </div>
            <div class="unit-25">
				<b></b><br />
                <input type="submit" class="btn" name="search" value="Показать" />
            </div>
        </div>
        <div class="units-row">
            <div id="search_flags" class="unit-50">
                <label><i class="tr{$smarty.get.active|default:''}"></i><span>Активный</span><input type="hidden" name="active" value="{$smarty.get.active|default:''}" /></label>
            </div>
        </div>
    </fieldset>

    
    <table id="list">
    <tr>
        <td>#</td>
        <th>Слайдер</th>
        <th>с-по</th>
        <th>название</th>
        <th>сортировка</th>
        <th>активность</th>
        <th>разрешен</th>
        <th>превью</th>
    </tr>

    {foreach from=$list item=i}
    <tr {cycle values='class="odd",'}>
        <td><small>{$i->id}</small></td>
        <td><small>{$i->get_slider_name()}</small></td>
        <td>
			<small>
				{if $i->from}
					{date('Y-m-d H:i', strtotime($i->from))}
				{else}
					нет
				{/if}
					- <br />
				{if $i->to}
					{date('Y-m-d H:i', strtotime($i->to))}
				{else}
					нет
				{/if}
			</small>
		</td>
        <td><a href="{Route::url('admin_edit',['model'=>'slider_banner','id'=>$i->id])}">{$i->name|default:'<без названия>'}</a></td>
        <td><input type='text' data-id="{$i->id}" name='order' value='{$i->order}' style='width: 40px;' /></td>
        <td>
			{if $i->active}
				<span class="label label-green">да</span>
			{else}
				<span class="label label-red">нет</span>
			{/if}
		</td>
        <td>
			{if $i->allow}
				<span class="label label-green">да</span>
			{else}
				<span class="label label-red">нет</span>
			{/if}
		</td>
		<td>
			<img src="{$i->src}" style="width: 100px;" />
		</td>
    </tr>
    {/foreach}
    </table>
	<script>
		$(function(){
			$('[name=order]').keyup(function(){
				var o = $(this);
				$.post('/od-men/ajax/slider_order.php',{
					id: $(this).attr('data-id'),
					value: $(this).val()
				}, function(data){
					setTimeout(function(){
						$('#saved').fadeOut(400);
					},1000);
					if( data == 'ok' ){
						$('#saved').remove();
						o.after('<div class="label label-green" id="saved">ок</div>');
					}
				});
			});
		});
	</script>
</form>

{$pager->html('Баннеры')}
