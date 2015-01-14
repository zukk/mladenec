<form action="" class="forms forms-inline">
    <fieldset>
        <legend>Запросы поиска</legend>
        
        <div class="units-row">
            <div id="search_flags" class="unit-40">
                с <input type="text" name="from" class="datepicker" value="{$from}" /> по
                <input type="text" name="to" class="datepicker" value="{$to}" />
				<script>
					$(function(){
						$('.datepicker').datepicker({
							dateFormat: 'yy-mm-dd'
						});
					});
				</script>
            </div>
            <div id="search_flags" class="unit-60">
				<label>статус</label>
                <label><i class="tr{$smarty.get.status0|default:''}"></i><span>не определен</span><input type="hidden" name="status0" value="{$smarty.get.status0|default:''}" /></label>
                <label><i class="tr{$smarty.get.status1|default:''}"></i><span>целевой</span><input type="hidden" name="status1" value="{$smarty.get.status1|default:''}" /></label>
                <label><i class="tr{$smarty.get.status2|default:''}"></i><span>нецелевой</span><input type="hidden" name="status2" value="{$smarty.get.status2|default:''}" /></label>
                <label><i class="tr{$smarty.get.is_error|default:''}"></i><span>ошибочный</span><input type="hidden" name="is_error" value="{$smarty.get.is_error|default:''}" /></label>
            </div>
            <div class="unit-80" style="text-align: right;">
				<input type="submit" class="btn" name="search" value="Показать" />
            </div>
        </div>        
    </fieldset>
</form>
<form action="" class="cb">
    <table id="list">
    <tr>
        <th>#</th>
        <th>запрос</th>
        <th>кол-во</th>
        <th>ошибочный</th>
        <th>бренды</th>
        <th>статус</th>
    </tr>
	{include file='./trs.tpl' list=$list brandRels=$brandRels brands=$brands count=$count}
    </table>
	<div class="more"></div>
</form>
<script>
	$(document).ready(function() {
		var $loading = $("<div class='loading'><p>Загрузка&hellip;</p></div>"),
		$footer = $('.more'),
		opts = {
			offset: '100%'
		};
		var offset = 0;
		var perPage = 30;
		var working = false;
		$(window).scroll(function(e){
			var sc = window.scrollY ? window.scrollY: document.documentElement.scrollTop;

			if( $footer.offset().top <=( sc + $(window).height() + 500 ) ){

			if( working )
				return false;

			working = true;

			offset += perPage;

			if( offset >= {$count} )
				return false;
				
			$footer.after($loading);

			$.get('/od-men/searchwords?offset=' + offset, function(data) {
				$loading.remove();
				working = false;
				var d = $(data);
				$('#list').append(d);
			});
			}
		});
	});		
</script>


