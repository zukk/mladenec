<form action="" class="forms forms-inline">
    <fieldset>
        <legend>Выбор месяца</legend>
        
				<script src="/j/jslider/jQDateRangeSlider-min.js"></script>
				<style>
                    {literal}
					@import "/j/jslider/css/classic-min.css";
                    {/literal}
				</style>
				<div id="jslider" style="width: 100%;"></div>
				<script>
					$(function(){
						$("#jslider").dateRangeSlider({
						bounds:{
						  min: new Date(2010, 0, 1),
						  max: new Date({date('Y')}, {date('m')-1}, {date('d')})
						},defaultValues:{
							min: new Date({$from[0]}, {$from[1]-1}, {$from[2]}),
							max: new Date({$to[0]}, {$to[1]-1}, {$to[2]})
						  }});
						$("#jslider").bind("valuesChanging", function(e, data){
							$('[name=from]').val(data.values.min.getFullYear()+'-'+data.values.min.getMonth()+'-'+data.values.min.getDate());
							$('[name=to]').val(data.values.max.getFullYear()+'-'+data.values.max.getMonth()+'-'+data.values.max.getDate());
						});						
					});
				</script>
				<input type="hidden" name="from" value="{$from[0]}-{$from[1]}-{$from[2]}" />
				<input type="hidden" name="to" value="{$to[0]}-{$to[1]}-{$to[2]}" />
        <div class="units-row">
            <div class="unit-25">
	                <b>Месяц</b><br />
				<div style='float: left; margin-left: 10px; margin-top: -4px;'>
					<input class='btn' type='submit' value='Выбрать' />
					<a style="padding: 6px;" class="btn" href="/od-men/stat?update=1&url=/od-men/stat?month=">обновить данные</a>
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

	var dateStart = Date.UTC({$from[0]}, {$from[1]}-1, {$from[2]});
	
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
			pointInterval: 24 * 3600 * 1000,
			pointStart: dateStart,
			data: dataNew
		},{
			name: 'Доставлено',
			pointInterval: 24 * 3600 * 1000,
			pointStart: dateStart,
			data: dataComplete
		},{
			name: 'Отменено',
			pointInterval: 24 * 3600 * 1000,
			pointStart: dateStart,
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
			pointInterval: 24 * 3600 * 1000,
			pointStart: dateStart,
			data: dataNewSum
		},{
			name: 'Доставлено',
			pointInterval: 24 * 3600 * 1000,
			pointStart: dateStart,
			data: dataCompleteSum
		},{
			name: 'Отменено',
			pointInterval: 24 * 3600 * 1000,
			pointStart: dateStart,
			data: dataCancelSum
		}]
	});
	
});
</script>
list
<table id="list">
<tr>
	<th>Дата</th>
	<th>Сделано</th>
	<th>Доставлено</th>
	<th>Отменено</th>
</tr>
    {assign var=list value=$list->as_array()|array_reverse}
    {foreach from=$list item=i}
    <tr {cycle values='class="odd",'}>
        <td>{Txt::ru_date($i->sdate)}</td>
        <td><span title='{$i->sum}'>{$i->new}</span> / <span title='{$i->sum_card}'>{$i->new_card}</span></td>
        <td><span title='{$i->complete_sum}'>{$i->complete}</span> / <span title='{$i->complete_sum_card}'>{$i->complete_card}</span></td>
        <td><span title='{$i->cancel_sum}'>{$i->cancel}</span> / <span title='{$i->cancel_sum_card}'>{$i->cancel_card}</span></td>
		<script>
			dataNew.push({$i->new});
			dataComplete.push({$i->complete});
			dataCancel.push({$i->cancel});
			dataNewSum.push(parseInt({$i->sum})/1000000);
			dataCompleteSum.push(parseInt({$i->complete_sum})/1000000);
			dataCancelSum.push(parseInt({$i->cancel_sum})/1000000);
		</script>
	</tr>
	{/foreach}
</table>
