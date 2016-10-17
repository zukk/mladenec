<div id="simple" style="text-align: center">
	<a href="/" title="На главную"><img src='/i/averburg/404/face1.jpg' width="773" height="537" /></a>
	<div>
	    <h1>Что-то случилось, и этой страницы здесь нет :(...</h1>
		<p style="font-size: 1.4em;">
			Вы можете <a href="/" style="text-decoration: underline">вернуться на главную</a>, позвонить нам по номеру 8 (800) 555 699 4
		</p>
		<p>
			или <a href="#" id="send_letter"><img src="/i/averburg/404/button.png" style="margin-bottom: -23px;" /></a>
			<script>
				$(function(){
					$('#send_letter').click(function(){
						$('#return_form').slideToggle();
						return false;
					});
				});
			</script>
		</p>
		<p>&nbsp;</p>
		<form action="/contacts" method="post" class="cols hide" id="return_form" enctype="multipart/form-data" style="width: 600px;margin-left: -300px;position: relative;left: 50%;">
			<label class="l" for="name">Имя<sup>*</sup></label>
			<input id="name" name="name" class="txt " value="">

			<label class="l" for="email">Email<sup>*</sup></label>
			<input id="email" name="email" class="txt " value="">

			<label class="l" for="text">Сообщение<sup>*</sup></label>
			<textarea id="text" name="text" class="wtxt " rows="10"></textarea>
			<p>Не более 2000 символов.</p>

						<label for="captcha" class="l"><img src="/captcha" alt=""></label>
				<div class="fl">
					<label>Введите цифры с&nbsp;картинки <sup>*</sup></label><br>
					<input id="captcha" type="text" name="captcha" value="" maxlength="6" class="txt ">
				</div>
					<input type="submit" value="Отправить сообщение" class="butt" name="feedback">
		</form>

	</div>
</div>
{if not empty($config->rr_enabled)}
    <div class="cl rr_slider" title="Рекомендуем Вам:" data-func="PersonalRecommendation" data-param="{$smarty.cookies.rrpusid}"></div>
{/if}
<script>
	window.dataLayer = window.dataLayer || [];
	dataLayer.push({
		'category': '404 Response',
		'action': document.location.href,
		'label': document.referrer, 
		'event': '404error'
	});
</script>