<div class='user-phone'>
	<select name='phone_active'>
		{foreach from=$user_phones item=model_phone key=id}
			<option value='{$id}'{if $user->phone_active eq $id} selected{/if}>{$model_phone->phone}</option>
		{/foreach}
		<option value='0'>Добавить новый</option>
	</select>
		<br clear="left" />
	<script>
		$(function(){
			$('[name=phone_active]').change(function(){
				var b = $('.user-phone-add');
				if( $(this).val() == '0' ){
					b.slideDown();
				}
				else{
					b.slideUp();
				}
			});
		});
	</script>
	<div class='user-phone-add fl{if $user->phone_active > 0} hide{/if}' style='margin-bottom: 20px;'>
		<input class='txt' name='phone_add' value='' />
		<input type='button' class='user-phone-add-button butt small' value='добавить' />
		<script>
			$(function(){
				$('.user-phone-add-button').click(function(){
					var ph = $('[name=phone_add]').val();
					var loader = new Image();
					loader.src = "/i/load.gif";

					$('.user-phone-add-button').attr('disabled','disabled');
					var timeout = setTimeout(function(){
						$('.user-phone-add-button').before(loader);
						$(loader).css({
							float: 'right',
							display: 'block',
							clear: 'left',
							left: '-64px',
							position: 'relative',
							top: '5px'
						});
					},400);

					$.ajax({
						url: '/user/phone',
						data: {
							phone: ph
						},
						success: function(data){

							clearTimeout(timeout);
							$(loader).remove();

							if( data.html ){
								$('.user-phone').replaceWith(data.html);
							}
						},
						dataType: 'JSON',
						method: 'POST'
					});
				});
			});
		</script>
	</div>
</div>
