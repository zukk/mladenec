(function ($) {
    $.fn.extend({
        pencilator: function () {
            var self = $(this);
			
			var save = function(){
				var params = {};
				
                $('#pencilator').remove();
				
				$('.pencilator-input').each(function(){
					var v = $(this).val();
                    params[$(this).attr('name')] = v;
                    $(this).next().prop('title', v ? v : 'Укажите — для мальчика или девочки, цвет и другие пожелания по данному товару');
				});
                $('.pencilator-input-email').each(function(){
                    var v_gift = $(this).val();
                    params[$(this).attr('name')] = v_gift;
                });
				$.post('/cart/comments.php', params );
			};

            var html = '';
            var html_syst = '';

            if($(this).hasClass('syst_gift')){
                html_syst += '<h3>Отправить сертификат другу</h3>' +
                             '<div style="text-align: left">' +
                                '<p>' +
                                    '<label>Email: </label> ' +
                                    '<input class="pencilator_gift" name="comment_email" style="width: 346px" type="text" value="">' +
                                '</p>' +
                                'Сообщение: ' +
                             '</div>';
            }
            html += '<div id="pencilator"><b></b>'+html_syst+'<textarea></textarea><p class="buttons"><input type="button" class="cancel" value="Очистить" />';
            html += '<input type="button" class="save" value="Сохранить" /></p><p class="counter"><span>0</span> из 250</p></div>';
            self.parent().append(html);
            var storageId = self.attr('rel');
            if($('#gift_' + storageId)){
                var text_gift = $('#gift_' + storageId).val();
                $('#pencilator .pencilator_gift').val(text_gift);
            }
            var text = $('#' + storageId).val();
            $('#pencilator textarea').val(text);
            $('#pencilator p.counter span').text(text.length);
            $('#pencilator .pencilator_gift').keyup(function (event) {
                var tgt_gift = $(event.target);
                var val_gift = tgt_gift.val();
                $('#gift_' + storageId).val(val_gift);
            });
            $('#pencilator textarea').keyup(function (event) {
                var tgt = $(event.target);
                var val = tgt.val();
                var len = val.length;
                $('#pencilator p.counter span').text(len);
                if (len > 250) {
                    tgt.css('border', '2px solid red');
                    val = val.substr(0, 250);
                    tgt.val(val);
                } else {
                    tgt.css('border', '1px solid #ccc');
                }
                if (len > 0) self.addClass('filled');
                else self.removeClass('filled');
                $('#' + storageId).val(val);
            });

            $('#pencilator input.save').click(function () {
				save();
            });
            $('#pencilator input.cancel').click(function () {
                $('#' + storageId).val('');
                self.removeClass('filled');
				save();
            });
            $('#pencilator').click(function (event) {
                event.preventDefault();
                event.stopPropagation();
            });
            $(document).click(function () {
                $('#pencilator input.save').click();
            });
        }
    })
})(jQuery);
