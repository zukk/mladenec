{if $answer->email_sent}
	<span class="label label-green">письмо отправлено</span>
{else}
	<span class="label label-red">письмо не отправлено</span> <a id="send-{$answer->id}" href="javascript:void(0)" style="text-decoration: none; margin-left: 10px;">отправить</a>
	<script>
		$(function(){
			var loader = new Image();
			loader.src = '/i/load.gif';
			$('#send-{$answer->id}').click(function(){
				
				var o = $(this);
				
				if( o.hasClass('disabled') )
					return false;
				
				o.addClass('disabled');
				
				var timeout = setTimeout(function(){
					o.after(loader);
					o.next().css({
						'margin-bottom': '-6px',
						'margin-left': '10px'
					});
				},200);
				$.get('/od-men/ajax/comment_send_answer.php', {
					id: {$answer->id}
				}, function(data){
					clearTimeout(timeout);
					$(loader).remove();
					o.prev().replaceWith(data);
					o.remove();
				});
			});
		});
	</script>
{/if}
