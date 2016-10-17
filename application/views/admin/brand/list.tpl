<form action="" class="forms forms-inline">
    <fieldset>
        <legend>Поиск брендов</legend>
        
        <div class="units-row">
            <div class="unit-25">
	                <b>Название</b><br />
				<div style='float: left'>
					<input type="text" name="name" class="width-100" value="{$smarty.get.name|default:''}">
				</div>
				<div style='float: right'>
					<input class='btn' type='submit' value='Поиск' />
				</div>
            </div>
		</div>
	</fieldset>
</form>
<table id="list">
<tr>
	<th>#</th>
	<th>Название</th>
</tr>
    {foreach from=$list item=i}
    <tr {cycle values='class="odd",'}>
        <td><small>{$i->id}</small></td>
        <td><a href="/od-men/{$m}/{$i->id}">{$i->name}</a></td>
	{/foreach}
</table>
{$pager->html('Бренды')}
