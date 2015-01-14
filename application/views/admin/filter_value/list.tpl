{$pager->html('Значения фильтров')}

<form action="" class="forms forms-inline">
	<fieldset>
		<legend>Поиск значений</legend>
		<table style='width: 100%;'>
		<tr>
			<td><label for="filter_id">Фильтр</label></td>
			<td>
				<select class="width-100" name="filter_id" id="filter_id">
					<option value="">-- все --</option>
					{assign var=cur_section_id value=0}
					{foreach from=ORM::factory('filter')->find_all() item=f}
						{if $f->section->active}
						{assign var=section_id value=$f->section->id}
						{if $section_id neq $cur_section_id}
							{if $cur_section_id neq 0}</optgroup>{/if}
							<optgroup label="{$f->section->name|escape:'html'}">
							{assign var=cur_section_id value=$section_id}
						{/if}
							<option value="{$f->id}" {if $smarty.get.filter_id|default:'' eq $f->id}selected="selected"{/if}>{$f->name|default:''}</option>
						{/if}
					{/foreach}
					</optgroup>
				</select>
			</td>
			<td><input type="submit" value="Показать" /></td>
		</tr>
		</table>
	</fieldset>
</form>

<table id="list">
<tr>
    <th>#</th>
    <th>Фильтр</th>
    <th>Значение</th>
    <th>Код в 1с</th>
    <th>Сортировка</th>
</tr>
{foreach from=$list item=i}
<tr {cycle values='class="odd",'}>
    <td>{$i->id}</td>
    <td>{$i->filter->name}</td>
    <td><a href="{Route::url('admin_edit', ['model' => 'filter_value', 'id' => $i->id])}">{$i->name}</a></td>
    <td>{$i->code}</td>
    <td><input data-id="{$i->id}" type="text" class="sort-order" value="{$i->sort}" style="width: 70px; display: block; float: left;" />
		<span style="width: 100px; padding: 3px 0 0 10px; display: block; float: left;"></span></td>
</tr>
{/foreach}
</table>
<script>
	$(function(){
		var working = false;
		$('.sort-order').keyup(function(e){
			
			var isw = working ? true: false;
			working = true;
			
			var o = $(this);
			if( true ){
				var loader = new Image();
				loader.src='/i/load.gif';
				
				if( !isw )
					var timeout = setTimeout(function(){
						o.next().empty().append(loader);
					}, 400);
				$.post('/od-men/ajax/filter_order.php',{
					id: o.attr('data-id'),
					value: o.val(),
					model: 'filter_value'
				}, function(data){
					
					clearTimeout(timeout);
					$(loader).remove();
					
					working = false;
					if( isw ){
						return false;
					}
					
					if( data== 'ok' ){
						var i = $('<span class="label label-green" style="position: relative; top: -3px;">сохранено</span>');
						$(o).next().empty().append(i);
						setTimeout(function(){
							i.fadeOut(1000, function(){
								$(this).remove();
							});
						},1000);
					}
				});
			}
		});
	});
</script>



{$pager->html('Фильтры в категориях')}
