<form id="one_click" class="ajax" method="post" action="{Route::url('one_click')}">
    {if not empty($in_good)}
        <strong>Или заказ в&nbsp;1&nbsp;клик</strong>

        <style>
            #one_click {
                width:283px;
                float:right;
            }
            #one_click .butt {
                float: left;
                display: block;
                margin-left: 10px;
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

    <input class="txt small" type="tel" name="phone" placeholder="Номер телефона" />
	<input class="butt{if !empty($in_good)} small silver{/if}" type="submit" value="Заказать" style="float: left" /><br />
    <p>Менеджер перезвонит Вам, узнает все детали и&nbsp;сам оформит заказ на&nbsp;Ваше имя</p>
</form>

<script>
    $(document).ready(function() {
        var one_click_form = $('#one_click'), ophone = $('input[name=phone]', one_click_form), do_one_click = ophone.next();
		var loader = new Image();
		loader.src = "/i/load.gif";
		
        one_click_form.on('submit', function () {
			
			var val = ophone.val();
			if ( ! val.match(PHONE_PATTERN)) {
				ophone.attr('error', 'Некорректный номер телефона').addClass('error');
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

                if ( data.redirect) { // на спасибу
					
					// из корзины
					if( typeof(googleCheckoutStep) != "undefined" ) {
						googleCheckoutStep("4");
					}

					// из карточки
					else if (typeof(dataLayerDetail) != "undefined" ) {

						var detailToSend = dataLayerDetail;
						detailToSend['actionField'] = { 'step': "4" };

						dataLayer.push({
                            userId: uid,
                            ecommerce: {
                                checkout: detailToSend
                            },
                            event: 'checkout'
						});									
					}

                    location.replace(data.redirect);
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
