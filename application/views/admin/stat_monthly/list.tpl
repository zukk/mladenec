<form action="" class="forms forms-inline">
    <fieldset>
        <legend>Выбор периода</legend>
        
        <div class="units-row">
            <div class="unit-50">
	                <b>Период</b><br />
				<div style='float: left'>
					от
					<select name="from">
					{for $year=2010 to date('Y')}
						{for $month=1 to 12}
							{if $month gt 9}
								{assign var=v value="$year-$month-01"}
								<option value="{$year}-{$month}-01"{if $v eq $from} selected{/if}>{$year}-{$month}</option>
							{else}
								{assign var=v value="$year-0$month-01"}
								<option value="{$year}-0{$month}-01"{if $v eq $from} selected{/if}>{$year}-0{$month}</option>
							{/if}
						{/for}
					{/for}
					</select>
					до
					<select name="to">
					{for $year=2010 to date('Y')}
						{for $month=1 to 12}
							{if $month gt 9}
								{assign var=v value="$year-$month-01"}
								<option value="{$year}-{$month}-01"{if $v eq $to} selected{/if}>{$year}-{$month}</option>
							{else}
								{assign var=v value="$year-0$month-01"}
								<option value="{$year}-0{$month}-01"{if $v eq $to} selected{/if}>{$year}-0{$month}</option>
							{/if}
						{/for}
					{/for}
					</select>
					<!--select name='limit'>
						{foreach from=$months item=label key=month}
							<option value='{$month}'{if $limit eq $month} selected{/if}>{$label}</option>
						{/foreach}
					</select-->
				</div>
				<div style='float: left; margin-left: 20px; margin-top: -5px;'>
					<input class='btn' type='submit' value='Выбрать' />
					<a style="padding: 6px;" class="btn" href="/od-men/stat?update=1&url=/od-men/stat_monthly">обновить данные</a>
				</div>
            </div>
		</div>
	</fieldset>
</form>
<script src="/j/admin/highcharts.js"></script>
<table>
	<tr>
		<td>
			<div id='gr-counts'></div>
		</td>
		<td>
			<div id='gr-sum'></div>
		</td>
	</tr>
</table>
<script>
var dataNew = [],dataComplete = [],dataCancel = [];
var dataNewSum = [],dataCompleteSum = [],dataCancelSum = [];
$(function(){

	var dateStart = Date.UTC({$monthp.0}-1, {$monthp.1}+2, 01);
	
	$('#gr-counts').highcharts({
		title: {
			text: 'количество'
		},
		subtitle: {
			text: '',
		},
		xAxis: {
			type: 'datetime',
			maxZoom: 3 * 3600000,
			title: {
				text: null
			}
		},
		yAxis: {
			title: {
				text: 'кол-во'
			},
			plotLines: [{
				value: 0,
				width: 1,
				color: '#808080'
			}]
		},
		tooltip: {
			valueSuffix: ' штук'
		},
		legend: {
			layout: 'vertical',
			align: 'right',
			verticalAlign: 'middle',
			borderWidth: 0
		},
		series: [{
			name: 'Сделано',
			pointStart: dateStart,
			pointInterval: 24 * 3600 * 1000 * 30,
			data: dataNew
		},{
			name: 'Доставлено',
			pointStart: dateStart,
			pointInterval: 24 * 3600 * 1000 * 30,
			data: dataComplete
		},{
			name: 'Отменено',
			pointStart: dateStart,
			pointInterval: 24 * 3600 * 1000 * 30,
			data: dataCancel
		}]
	});
	$('#gr-sum').highcharts({
		title: {
			text: 'суммы'
		},
		subtitle: {
			text: '',
		},
		plotOptions: {
            series: {
                stacking: 'normal'
            }
        },
		xAxis: {
			type: 'datetime',
			maxZoom: 3 * 3600000,
			title: {
				text: null
			}
		},
		yAxis: {
			title: {
				text: 'сумма, млн'
			},
			plotLines: [{
				value: 0,
				width: 1,
				color: '#808080'
			}]
		},
		tooltip: {
			valueSuffix: ' миллионов рублей'
		},
		legend: {
			layout: 'vertical',
			align: 'right',
			verticalAlign: 'middle',
			borderWidth: 0
		},
		series: [{
			name: 'Сделано',
			pointStart: dateStart,
			pointInterval: 24 * 3600 * 1000 * 30,
			data: dataNewSum
		},{
			name: 'Доставлено',
			pointStart: dateStart,
			pointInterval: 24 * 3600 * 1000 * 30,
			data: dataCompleteSum
		},{
			name: 'Отменено',
			pointStart: dateStart,
			pointInterval: 24 * 3600 * 1000 * 30,
			data: dataCancelSum
		}]
	});
	
});
</script>
<table id="list">
<tr>
	<th>Дата</th>
	<th>Сделано</th>
	<th>Доставлено</th>
	<th>Отменено</th>
	<th>Детализация</th>
</tr>
	{if !empty( $list )}
    {assign var=list value=$list|array_reverse}
    {foreach from=$list item=i key=k}
    <tr {cycle values='class="odd",'}>
        <td>{date('Y-m',strtotime($i->sdate))}</td>
        <td><span title='{$i->sum}'>{$i->new}</span> / <span title='{$i->sum_card}'>{$i->new_card}</span></td>
        <td><span title='{$i->complete_sum}'>{$i->complete}</span> / <span title='{$i->complete_sum_card}'>{$i->complete_card}</span></td>
        <td><span title='{$i->cancel_sum}'>{$i->cancel}</span> / <span title='{$i->cancel_sum_card}'>{$i->cancel_card}</span></td>
        <td><a href='/od-men/stat?from={date('Y-m-01',strtotime($i->sdate))}&to={date('Y-m-01',strtotime($i->sdate)+60*60*24*31)}'>детализация</a></td>
		<script>
			dataNew.push([{$i->new}]);
			dataComplete.push([{$i->complete}]);
			dataCancel.push([{$i->cancel}]);
			dataNewSum.push([parseInt({$i->sum})/1000000]);
			dataCompleteSum.push([parseInt({$i->complete_sum})/1000000]);
			dataCancelSum.push([parseInt({$i->cancel_sum})/1000000]);
		</script>
	</tr>
	{/foreach}
	{/if}
</table>
