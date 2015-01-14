<div class='answer' id='answer-{$answer->id}'>
	<style>
		.opacity50{
			opacity: 0.6;
		}
	</style>
	<div style='display: table; margin-top: 10px;'>
		Ответил <b>{$by[$answer->answer_by]}</b> {Txt::ru_date($answer->date)} в {date('H:i', strtotime($answer->date))}
		{include file='admin/comment_theme/answer/sent.tpl' answer=$answer}
		<div style='float: right'>
			Статус
			<select name='active-{$answer->id}'>
				<option value='1'{if $answer->active} selected{/if}>Показан</option>
				<option value='0'{if ! $answer->active} selected{/if}>Скрыт</option>
			</select>
			<a id='delete-answer-{$answer->id}' href='javascript:void(0)' style='color: red; text-decoration: none; margin: 0 5px;'>удалить</a>
		</div>
		{if not $answer->email_sent or $user->allow('admin')}
			<textarea name="answer-text-{$answer->id}" class="text{if not $answer->active} _opacity50{/if}">{$answer->answer}</textarea>
		{else}
            <div style="border:1px solid #000; padding:5px; background:#fff;">
            {$answer->answer}
            </div>
		{/if}
		<div id='answer-button-block-{$answer->id}'>
			{if $answer->email_sent and not $user->allow('admin')}
                <span>Изменить текст сообщения при отправленном письме может только Администратор.</span>
            {else}
                <input id='answer-button-{$answer->id}' type='button' value='Сохранить ответ' class='btn btn-green' style='float: right; margin: 10px;' />
            {/if}
		</div>
		<script>
            {literal}
            var answer_id = {/literal}{$answer->id}{literal};
			$(function(){
                var bBlock = $('#answer-button-block-' + answer_id), textarea = $('[name=answer-text-' + answer_id + ']'), statusSelect = $('[name=active-' + answer_id + ']'),
                    startVal = textarea.val(), startStatus = statusSelect.val();
                setInterval(function(){
					if( textarea.val() != startVal || statusSelect.val() != startStatus ){
						bBlock.slideDown();
						startVal = textarea.val();
						startStatus = statusSelect.val();
					}
				},500);
                $('#answer-button-' + answer_id).click(function(){
					
					var loader = new Image();
					loader.src = '/i/load.gif';

					var o = $(this);
					
					o.attr('disabled', 'disabled');
					
					var timeout = setTimeout(function(){
						o.after(loader);
						$(loader).css({
							float: 'right',
							'margin-top': '15px',
							'margin-right': '6px'
						});
					},200);
					$.post('/od-men/ajax/comment_answer_save.php',{
						id: answer_id ,
						answer: $('[name=answer-text-' + answer_id + ']').val(),
						active: $('[name=active-' + answer_id + ']').val()
					}, function(data){
						
						clearTimeout(timeout);
						$(loader).remove();
						o.removeAttr('disabled');
						
						if( data == 'ok' ){
							bBlock.slideUp();
						}
					});
					return false;
				});
				$('.redactor_box:has(.redactor__opacity50)').addClass('opacity50');
				
				$('[name=active-' + answer_id + ']').change(function(){
					$('#answer-' + answer_id).find('.redactor_box').toggleClass('opacity50');
				});
				$('#delete-answer-' + answer_id).click(function(){
					if( confirm('Действительно удалить ответ?') ){
						$.get('/od-men/ajax/comment_delete_answer.php',{
							id: answer_id
						}, function(ok){
							if( ok == 'ok' ){
								$('#answer-' + answer_id).slideUp(function(){
									$(this).remove();
								});
							}
						});
					}
					return false;
				});
			});
        {/literal}
		</script>
	</div>
</div>
