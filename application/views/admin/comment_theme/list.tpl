<form action="" class="forms forms-inline">
    <fieldset>
        <legend>Поиск отзывов</legend>
		<table style='width: 100%;'>
			<col width='30%' />
			<col width='40%' />
			<col width='30%' />
			<tr>
				<td>
					<label>ID Пользователя<br />
					<input type="text" name="user_id" value="{$user_id|default:''}" /></label>
				</td>
				<td>
					<label>Диапазон дат</label>
					c	<input class='datepicker-jqui' type='text' name='date_from' value='{$date_from}' />
					по	<input class='datepicker-jqui' type='text' name='date_to' value='{$date_to}' />
				</td>
				<td></td>
			</tr>
			<tr>
				<td>
					<div id="search_flags">
						<label><i class="tr{$smarty.get.active|default:''}"></i><span>Активность</span><input type="hidden" name="active" value="{$smarty.get.active|default:''}" /></label>
						<label><i class="tr{$smarty.get.answered|default:''}"></i><span>Ответ</span><input type="hidden" name="answered" value="{$smarty.get.answered|default:''}" /></label>
					</div>
				</td>
				<td>
					<script>
					  $(function() {
						$( "#slider-range" ).slider({
						  range: true,
						  min: 0,
						  max: 5,
						  values: [ {$rating_range['min']}, {$rating_range['max']} ],
						  slide: function( event, ui ) {
							$( "[name=rating_range]" ).val( ui.values[ 0 ] + " - " + ui.values[ 1 ] );
						  }
						});
						$( "[name=rating_range]" ).val( $( "#slider-range" ).slider( "values", 0 ) +
						  " - " + $( "#slider-range" ).slider( "values", 1 ) );
					  });
					</script>			
					<label>Выборка по оценкам: <input type="text" readonly="true" name="rating_range" style="border: 0; box-shadow: none; display: inline;"></label>
					<div id="slider-range"></div>			  
				</td>
				<td style='vertical-align: middle; text-align: center'>
	                <input type="submit" class="btn" name="search" value="Показать" style='font-size: 1.3em; margin: 10px;' />
					<a href='/od-men/comment_theme'>сброс</a>
				</td>
			</tr>
		</table>
        <div class="units-row">
			Всего: {$count}. Статистика по рейтингу:
			{$marks = array(1,2,3,4,5)}
			{foreach from=$marks item=mark}
				&nbsp;&nbsp;&nbsp;{$mark} - {if !empty($counts[$mark])}{$counts[$mark]}{else}0{/if}
			{/foreach}.
			{if !empty($counts[0])}
			Без оценок: {$counts[0]} ({round($counts[0]/$count*100,2)} %).
			{/if}
        </div>        
    </fieldset>
</form>
<form action="">
    <table id="list">
    <tr>
		{foreach from=$fields key=field item=fieldname}
	        {if $sort==$field}
				<th>
					<nobr>
					{if $desc == 'asc'}
						<a href="?order={$field}&desc=desc&{$filterquery}">{$fieldname}</a> ↑
					{else}
						<a href="?order={$field}&desc=asc&{$filterquery}">{$fieldname}</a> ↓
					{/if}
					</nobr>
				</th>
			{else}
				<th><a href="?order={$field}&desc=desc&{$filterquery}">{$fieldname}</a></th>
			{/if}
		{/foreach}
    </tr>

    {foreach from=$list item=i}
    <tr {cycle values='class="odd",'}>
        <td><small>{$i->id}</small></td>
        <td><small>{$i->date}</small></td>
        <td>
			{if $i->active}
				<span class='label label-green'>да</span>
			{else}
				<span class='label label-red'>нет</span>
			{/if}
        <td>
			{if $i->email_sent}
				<span class='label label-green'>да</span>
			{else}
				<span class='label label-red'>нет</span>
			{/if}
        <td>
			{if $i->user_id}
				{if ! empty($smarty.get.user_id)}
				<a href='/od-men/user/{$i->user_id}'>{$i->user_name}</a>
				{else}
				<a href='?user_id={$i->user_id}'>{$i->user_name}</a>
				{/if}
			{else}
				{$i->user_name} 
			{/if}
		</td>
        <td>{$i->get_to($i->to)}</td>
        <td><a href="/od-men/{$m}/{$i->id}">{$i->name}</a></td>
        <td>{$i->internal_rating}</td>
    </tr>
    {/foreach}
    </table>

</form>

{$pager->html('Отзывы')}
