{$pager->html('Фильтры в категориях')}

<form action="" class="forms forms-inline">
	<fieldset>
		<legend>Поиск фильтров</legend>
		<table style='width: 100%;'>
		<tr>
			<td><label for="section_id">Категория</label></td>
			<td><select class="width-100" name="section_id" id="section_id">
					<option value="">все</option>
					{foreach from=Model_Section::get_catalog(FALSE) item=s}
						<option value="{$s->id}" class="bold"  {if $smarty.get.section_id|default:'' eq $s->id}selected="selected"{/if}>{$s->name|default:''}</option>
						{foreach from=$s->children item=c}
							<option value="{$c->id}" {if $smarty.get.section_id|default:'' eq $c->id}selected="selected"{/if}>...{$c->name}</option>
						{/foreach}
					{/foreach}
				</select>
			</td>
			<td><input type="submit" value="Найти" /></td>
		</tr>
		</table>
	</fieldset>
</form>

<table id="list">
<tr>
    <th>#</th>
    <th>Фильтр</th>
    <th>Категория</th>
    <th>Код в 1с</th>
    <th>Сортировка</th>
</tr>
{foreach from=$list item=i}
<tr {cycle values='class="odd",'}>
    <td>{$i->id}</td>
    <td><a href="{Route::url('admin_list', ['model' => 'filter_value'])}?filter_id={$i->id}">{$i->name}</a></td>
    <td>{$i->section->name}</td>
    <td>{$i->code}</td>
    <td><input data-id="{$i->id}" type="text" class="sort-order" value="{$i->sort}" style="width: 70px; display: block; float: left;" />
		<span style="width: 100px; padding: 3px 0 0 10px; display: block; float: left;"></span>
	</td>
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
					value: o.val()
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
