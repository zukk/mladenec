
	{if !empty($list)}
	{assign var=rand value= 1|rand:10000 } 
	{foreach from=$list item=i}
    <tr {cycle values='class="odd",'}>
        <td><small>{$i['id']}</small></td>
        <td>{$i['name']}</td>
		<td>
			{$i['count']}
		</td>
		<td>
			{if $i['is_error']}
				<span class="label label-red">да</span>
			{else}
				<span class="label label-green">нет</span>
			{/if}
		</td>
		<td>
			{if !empty($brandRels[$i['id']])}
				{foreach $brandRels[$i['id']] item=bId}
					{if !empty( $brands ) and !empty($brands[$bId])}
						<a href="/od-men/brand/{$brands[$bId]['id']}">{$brands[$bId]['name']}</a>;
					{/if}
				{/foreach}
			{/if}
		</td>
        <td>
			<select name='status-{$rand}' data-id='{$i['id']}' style='float: left'>
				<option value='0'{if $i['status'] eq 0} selected='selected'{/if}>не определен</option>
				<option value='1'{if $i['status'] eq 1} selected='selected'{/if}>целевой</option>
				<option value='2'{if $i['status'] eq 2} selected='selected'{/if}>нецелевой</option>
			</select>
			<div style="width: 20px; float: left; margin-left: 10px;"></div>
		</td>
    </tr>
    {/foreach}
	{else}
		<tr>
			<td colspan="5" style="text-align: center">ничего не найдено</td>
		</tr>
	{/if}
	<script>
		$(function(){
			$('[name=status-{$rand}]').change(function(){
				
				var o = $(this);
				var loader = new Image();
				loader.src='/i/load.gif';
				
				var timeout = setTimeout(function(){
					o.next().append(loader);
				}, 400);
				$.post('/od-men/ajax/search_status.php',{
					id: o.attr('data-id'),
					value: o.val()
				}, function(data){
					clearTimeout(timeout);
					$(loader).remove();
					if( data== 'ok' ){
						var i = $('<span class="label label-green" style="position: relative; top: -3px;">сохранено</span>');
						$(o).next().append(i);
						setTimeout(function(){
							i.fadeOut(1000, function(){
								$(this).remove();
							});
						},1000);
					}
				});
			});
		});
	</script>
