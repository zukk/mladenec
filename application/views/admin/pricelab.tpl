<h1>История цен Pricelab</h1>

{foreach from=$history key=k item=h}
<a href="{Route::url('admin_pricelab', ['ymd' => $k])}">{$k}</a><br />
{/foreach}