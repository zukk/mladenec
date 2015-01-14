<form id="one_click" class="ajax" method="post" action="{Route::url('one_click')}">
    <strong>Или заказ в&nbsp;1&nbsp;клик</strong>
    <input class="txt small" type="tel" name="phone" placeholder="Номер телефона" />
	<input class="butt{if !empty($in_good)} small silver{/if}" type="submit" value="Заказать" style="float: left" /><br />
    Менеджер перезвонит Вам, узнает все детали и&nbsp;сам оформит заказ на&nbsp;Ваше имя
	{if !empty($in_good)}
		<style>
			#one_click .butt {
				float: left;
				display: block;
				margin-left: 13px;
			}			
			#one_click input.txt {
				float: left;
				display: block;
				width: 120px;				
			}
			.butt.silver {
				background: #fdfdfd;
				background: -moz-linear-gradient(top,  #fdfdfd 0%, #ececec 100%);
				background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#fdfdfd), color-stop(100%,#ececec));
				background: -webkit-linear-gradient(top,  #fdfdfd 0%,#ececec 100%);
				background: -o-linear-gradient(top,  #fdfdfd 0%,#ececec 100%);
				background: -ms-linear-gradient(top,  #fdfdfd 0%,#ececec 100%);
				background: linear-gradient(to bottom,  #fdfdfd 0%,#ececec 100%);
				filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#fdfdfd', endColorstr='#ececec',GradientType=0 );
				color: #808080 !important;
				text-shadow: 0 1px 0 #fff;
				border-color: #d8d8d8;
			}
		</style>
	{/if}
</form>
<script>
    $(document).ready(function() {
        var one_click_form = $('#one_click'), ophone = $('input[name=phone]', one_click_form), do_one_click = ophone.next();
		var loader = new Image();
		loader.src = "/i/load.gif";
		
        ophone.mask('+7(999)999-99-99');
        /* ophone.on('keyup mouseup touchend', function () {
			do_one_click.prop('disabled', !ophone.val().match(/^\(\d\d\d\)\d\d\d-\d\d-\d\d$/));
        }); */
        one_click_form.on('submit', function () {
			var val = ophone.val().substring(2);
			if( !val.match(/^\(\d\d\d\)\d\d\d-\d\d-\d\d$/) ){
				ophone.attr('error', 'Введите номер').addClass('error');
				return false;
			}
			
			$('#one_click .butt').attr('disabled', 'disabled');
			var timeout = setTimeout(function(){
				$(loader).css({
					float: 'left',
					padding: '9px'
				}).insertAfter($('#one_click .butt'));
			},400);
            $.post(one_click_form.prop('action'), {
                phone: val,
                good: {$good_id|default:0},
                qty: $('#qty_{$good_id|default:0}').val()
            }, function (data) {
				$('#one_click .butt').removeAttr('disabled');
				clearTimeout(timeout);
				$(loader).remove();
				if( data.thank_you ){
					if( data.redirect ){
						history.pushState(null, null, data.redirect);
					}
					$('html, body').animate({
						scrollTop: '0px'
					}, 400);
					$('#content').empty().append(data.thank_you);
				}
				if( data.userpad ){
					$('#userpad').replaceWith(data.userpad);
				}
                if (data.error) {
					ophone.attr('error', data.error).addClass('error');
                    return;
                }
            }, 'json');
            return false;
        });
    });
</script>
