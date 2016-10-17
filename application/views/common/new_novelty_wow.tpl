<div id="ann">
	<a href="{Route::url('action_list')}" class="ih2">Акции</a>
	<div>
		<a href="{Route::url('action_list')}"><img src="/i/action_star25.png" alt="WOW-акции" width="25" /></a>
		<p><a href="{Route::url('action_list')}">WOW-акции</a><br />Скидки и подарки</p>
	</div>

	<a href="{Route::url('news')}" class="ih2">Новости</a>
	<div>
		<a href="{$new->get_link(0)}" style="background-size:cover; background-image:url({$new->image->get_img(0)})"></a>
		<p>{$new->get_link()} {$new->preview}</p>
	</div>

	<a href="{Route::url('novelty')}" class="ih2">Новинки</a>
	<div>
		<a href="{Route::url('novelty')}"><img src="/i/excl.png" alt="Новинки" width="15" height="29" /></a>
		<p><a href="{Route::url('novelty')}">Свежие поступления</a><br />от Младенец. РУ</p>
	</div>
</div>
