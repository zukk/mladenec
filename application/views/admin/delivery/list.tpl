{literal}
<script type="text/javascript">
$(document).ready(function() {
    $('form').on('submit', function() {
        return confirm($(this).find('div.hide').text() + '?');
    });
})
</script>
{/literal}

<h2>Возможные даты доставки</h2>

<ul>
	<li>Доставка на&nbsp;утренние интервалы закрывается автоматически в&nbsp;18:00 Мск предыдущего дня</li>
	<li>Доставка на&nbsp;текущую дату закрывается автоматически в&nbsp;12:00 Мск</li>
</ul>

{foreach from=$zones item=z}
<div class="fl" style="margin-right:1em;">
	<table id="list" >
	<thead><tr><td></td><td>{$z->name}</td></tr></thead>
	{foreach from=Cart::instance()->allowed_date($zone) key=date item=i}
	{assign var=var value=$date|date_ru}
	<tr {cycle values='class="odd",'}>
	    <td>{$var}</td>
	    <td><form action="" method="post">
			    <div class="hide">Закрыть доставку в зоне "{$z->name}" на {$var}</div>

                {Form::hidden('date', $date)}
			    {Form::hidden('zone_id', $z->id)}
			    <label>{Form::checkbox('morning', 1, $i == 1, ['id' => 'morning'])} утро</label>
			    {Form::submit(null, 'закрыть')}
		    </form>
	    </td>
	</tr>
	{/foreach}
	</table>
</div>
{/foreach}
