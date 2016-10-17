{literal}
<script type="text/javascript">
$(document).ready(function() {
    if($('#active').attr('checked')) {
        $('#warn').show();
    } else {
        $('#warn').hide();
    }
    $('#active').on('change', function() {
        $('#warn').toggle();
    });
});
</script>
{/literal}

<form action="" method="post" class="forms forms-columnar" enctype="multipart/form-data">
    <h1>#{$i->id}, {$i->user_name}</h1>
    
    <p>
        <label>Дата</label><span>{$i->comment->date}</span>
    </p>
    <p>
        <label>Пользователь</label>
        {if $i->user_id}
            <a href="/od-men/user/{$i->user_id}">{$i->user_name}</a>
        {else}
            {$i->user_name}
        {/if}
        {$i->email} {$i->phone}
    </p>
    <p>
        <label>Кому</label>
        {if $i->to}{$i->get_to($i->to)}{else}<span>не указано</span>{/if}
    </p>
    <p>
        <label for="name">Название</label>
        <input type="text" id="name" name="theme-name-{$i->id}" value="{$i->name}" size="50"  />
    </p>
    <p>
		{$comment_rating = [1,2,3,4,5]}
        <label for="active">Внутренний рейтинг</label>
		<select name="theme-rating-{$i->id}">
			<option value="0">Выберите</option>
			{foreach from=$comment_rating item=mark}
				<option value="{$mark}"{if $i->internal_rating == $mark} selected{/if}>{$mark}</option>
			{/foreach}
		</select>
    </p>
    <p>
        <label for="active">Активность</label>
        <input type="checkbox" id="active" name="theme-active-{$i->id}" value="1" {if $i->active}checked="checked"{/if} />
    </p>
    <div class="units-row">
        <div class="unit-80">
            <input id="theme-button-{$i->id}" name="edit" value="Сохранить" type="submit" class="btn btn-green" style="font-size: 1.1em;" />
			<script>
				$(function(){
					var loader = new Image();
					loader.src = '/i/load.gif';
					
					$('#theme-button-{$i->id}').click(function(){
						
						var o = $(this);
						
						var timeout = setTimeout(function(){
							o.after(loader)
							$(loader).css({
								position: 'relative',
								top: '6px',
								left: '10px'
							});
						},200);
						
						$.post('/od-men/ajax/comment_theme_save.php',{
							id: {$i->id},
							active: $('[name=theme-active-{$i->id}]:checked').length,
							name: $('[name=theme-name-{$i->id}]').val(),
							internal_rating: $('[name=theme-rating-{$i->id}]').val()
						}, function(data){
							clearTimeout(timeout);
							$(loader).remove();
							
							if( data== 'ok' ){
								o.val('Изменения сохранены');
								setTimeout(function(){
									o.val('Сохранить');
								},3000);
							}
						});
						return false;
					});
				});
			</script>
			<div>
				<br />
				<a href="{$list_url}" class="btn  btn-black">Вернуться к списку</a>
			</div>
        </div>
    </div>
</form>    
	<fieldset style="margin: 0 auto; max-width: 1200px;">
		<legend>Переписка</legend>
		{foreach from=$data key=theme_id item=item}
			<div class="comment" id="comment-{$item['comment']->id}" style='padding: 10px; border-radius: 5px; background: #f3f3f3; border: 1px dashed #ccc; margin-bottom: 10px;'>
				<form>
					Вопрос {Txt::ru_date($item['comment']->date)} в {date('H:i', strtotime($item['comment']->date))}
					<div style='float: right'>
						Статус
						<select name='comment-active-{$item['comment']->id}'>
							<option value='1'{if $item['comment']->active} selected{/if}>Показан</option>
							<option value='0'{if !$item['comment']->active} selected{/if}>Скрыт</option>
						</select>
						<a href='javascript:void(0)' style='color: red; text-decoration: none; margin: 0 5px;' id='comment-delete-{$item['comment']->id}'>удалить</a>
						<script>
                            var comment_id = {$item['comment']->id};
                            {literal}
							$(function(){
								
								var loader = new Image();
								loader.src = '/i/load.gif';
						
								$('#comment-delete-' + comment_id).click(function(){
									
									var o = $(this);
									
									if( $('.comment').length < 2 ){
										alert('В переписке должен остаться хотя бы один вопрос');
										return false;
									}
									
									if( !confirm('Действительно удалить вопрос вместе с ответами?') ){
										return false;
									}
									
									if( o.hasClass('disabled') )
										return false;
									
									o.addClass('disabled');
									
									$.post('/od-men/ajax/comment_comment_delete.php',{
										id: comment_id
									}, function(data){
										if(data == 'ok' ){
											$('#comment-' + comment_id).slideUp(function(){
												$(this).remove();
											});
										}
									});
									return false;
								});
								
								$('[name=comment-active-' + comment_id + ']').change(function(){
									$('[name=comment-text-' + comment_id + ']').toggleClass('opacity50');
								});
								
								$('[name=comment-button-' + comment_id + ']').click(function(){
									
									var o = $(this);
									
									o.attr('disabled', 'disabled');
									
									var timeout = setTimeout(function(){
										o.after(loader);
										$(loader).css({
											float: 'right',
											position: 'relative',
											top: '5px',
											right: '6px'
										});
									}, 200);
									
									$.post('/od-men/ajax/comment_comment_save.php', {
										id: comment_id,
										text: $('[name=comment-text-' + comment_id + ']').val(),
										active: $('[name=comment-active-' + comment_id + ']').val()
									}, function(data){
										clearTimeout(timeout);
										o.removeAttr('disabled');
										$(loader).remove();
										if( data == 'ok' ){
											o.val('Изменения сохранены');
											setTimeout(function(){
												o.val('Сохранить вопрос');
											},3000);
										}
									});
									return false;
								});
							});
                            {/literal}
						</script>
					</div>
					<textarea name="comment-text-{$item['comment']->id}" class="{if !$item['comment']->active} opacity50{/if}" style='width: 100%; height: 100px;'>{$item['comment']->text}</textarea>
					<div style="padding: 10px;">
						<input name="comment-button-{$item['comment']->id}" style="float: right" type="button" class="btn btn-green" value="Сохранить вопрос" />
					</div>
				</form>
				<div id='answers-{$item['comment']->id}' style="padding: 20px;">
					{foreach from=$item['answers'] item=answer}
						{include file='admin/comment_theme/answer.tpl' answer=$answer by=$by}
					{/foreach}
				</div>
				<a class="add-answer-link" id='q{$item['comment']->id}' href="javascript:void(0)" style="font-size: 1.3em">Добавить ответ <span></span></a>					
				<div id='form-q{$item['comment']->id}' style="padding: 15px; display:none;">
					<form class="add-answer-form" style="padding: 10px; display: table">
						<h4>Ваш ответ</h4>
						От кого
						<select name='by-{$item['comment']->id}'>
							{html_options options=Model_Comment_Answer::$answer_by}
						</select>
						<div style='float: right'>
							Статус
							<select name='answer-active-{$item['comment']->id}'>
								<option value='1'>Показан</option>
								<option value='0'>Скрыт</option>
							</select>
						</div>
						<br />
						Текст ответа
						<textarea name="answer-{$item['comment']->id}" class="text"></textarea>
						<label style='margin: 5px; display: block;'>
							<div style='float: left; padding: 2px;'>
								<input type='checkbox' name='answer-send-{$item['comment']->id}' value='' /> 
							</div>
							Отправить письмо
						</label>
						<input class='add-answer-submit btn btn-green' data-question='{$item['comment']->id}' type="button" value="Добавить" style="margin-top: 5px;" />
					</form>
				</div>
			</div>
		{/foreach}
	</fieldset>
	<script>
		$(function(){
			var loader = new Image();
			loader.src = '/i/load.gif';
			
			$('.add-answer-submit').click(function(){
				
				var o = $(this), qId = $(this).attr('data-question');

				if( $('[name=by-'+qId+']').val() == '0' ){
					alert('Выберите, от кого будет ответ');
					return false;
				}
				if( $('[name=answer-'+qId+']').val() == '' ){
					alert('Текст ответа не может быть пустым');
					return false;
				}
				
				o.attr('disabled', 'disabled');
				var timeout = setTimeout(function(){
					o.after(loader);
					o.next().css({
						position: 'relative',
						top: '6px',
						left: '6px'
					});
				},200);
				$.post('/od-men/ajax/comment_answer.php',{
					question: qId,
					text: $('[name=answer-'+qId+']').val(),
					by: $('[name=by-'+qId+']').val(),
					active: $('[name=answer-active-'+qId+']').val(),
					send: $('[name=answer-send-'+qId+']:checked').length
				}, function(data){
					clearTimeout(timeout);
					$(loader).remove();
					
					var id = $(data).attr('id');
					$('#answers-'+qId).append(data);
					$('#'+id).find('textarea').redactor({
						lang:'ru',  air: true, airButtons: ['bold', 'italic', 'link'], convertDivs: false 
					});
					var h = $('#'+id).height();
					$('#'+id).css({
						height: '0px',
						overflow: 'hidden'
					}).animate({
						height: (h+10)+'px'
					},500, function(){
						$(this).css({
							height: 'auto'
						});
						$('.redactor_box:has(.redactor__opacity50)').addClass('opacity50');
					});
					$('#form-q'+qId).slideUp(500,function(){
						$('[name=answer-'+qId+']').val('').redactor('set', '');
						$('[name=by-'+qId+']').val('0');
						$('[name=active-'+qId+']').val('1');
						o.removeAttr('disabled');
					});
				});
				return false;
			});
			$('.add-answer-link').click(function(){
				$('#form-'+$(this).attr('id')).slideToggle();
				return false;
			});
		});
	</script>
