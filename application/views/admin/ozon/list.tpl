<fieldset>
	<legend>Добавление условия</legend>
	<form id='add-ozon' action='/od-men/ozon/add' method='post'>
		<input type='hidden' name='edit' value='1' />
		<select name='type'>
			<option value='0'>тип</option>
			{foreach from=$types item=type key=type_id}
				<option value='{$type_id}'>{$type}</option>
			{/foreach}
		</select>
		<input type='text' id='id_item_text' value='' placeholder='элемент' />
		<input type='hidden' name='id_item' value='' />
		<script>
			{literal}
			$(function(){
				var models = [
					{/literal}
					''
					{foreach from=$types_models item=model key=type_id}
						,'{$model}'
					{/foreach}
					{literal}
				];
				$("#id_item_text").autocomplete({
					source: function (request, response) {
						var term = request.term;
						$.getJSON('/od-men/ajax/autocomplete.php?term=' + term, {
							model: models[$('[name=type]').val()],
							fields: [ 'id', 'name'],
						}, function (data, status, xhr) {
							response(data);
						});
					},
					minLength:1,
					maxHeight:300,
					select: function(value, data) {
						$(this).next().val(data.item.id);
					}
				});
			});
		{/literal}
		</script>
		<input type='text' name='scount' value='' placeholder="кол-во" />
		<input id='add-form' type='submit' value='Добавить' />
	</form>
	<script>
		$(function(){
			$('#add-form').click(function(e){
				if( $('[name=id_item]').val() == '' || $('[name=scount]').val() == '' || $('[name=type]').val() == '0' ){
					return false;
				}
			});
		});
	</script>
</fieldset>
{$pager->html('Условия выгрузки на озон')}

        <form action="" >
            <table id="list">
            <tr>
                <th>#</th>
                <th>тип</th>
                <th>элемент</th>
                <th>мин. кол-во</th>
                <th>удл.</th>
            </tr>
            {foreach from=$list item=i}
            <tr>
				<td>{$i->id}</td>
				<td>{$types[$i->type]}</td>
				<td>
					{if !empty( $elements[$i->type][$i->id_item] )}
						{if $i->type eq 3}
							<a target='_blank' href='/od-men/{$types_models[$i->type]}/{$i->id_item}'>{$elements[$i->type][$i->id_item]->group_name} {$elements[$i->type][$i->id_item]->name}</a>
						{else}
							<a target='_blank' href='/od-men/{$types_models[$i->type]}/{$i->id_item}'>{$elements[$i->type][$i->id_item]->name}</a>
						{/if}
					{/if}
				</td>
				<td>{$i->scount}</td>
				<td><a class='del' href='/od-men/ozon/{$i->id}/del?return_url=/od-men/ozon'>удалить</a></td>
            </tr>
            {/foreach}
            </table>
			<script>
				$(function(){
					$('.del').click(function(){
						if( !confirm('действительно удалить?') )
							return false;
					});
				});
			</script>
        </form>
    </div>
</div>
{$pager->html('Условия выгрузки на озон')}

