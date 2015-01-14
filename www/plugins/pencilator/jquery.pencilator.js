(function ($) {
    $.fn.extend({
        pencilator: function () {
            var self = $(this);
			
			var save = function(){
				var params = {};
				
                $('#pencilator').remove();
				
				$('.pencilator-input').each(function(){
					params[$(this).attr('name')] = $(this).val();
				});
				
				$.post('/cart/comments.php', params );
			};
			
            var html = '<div id="pencilator"><b></b><textarea></textarea><p class="buttons"><input type="button" class="cancel" value="Очистить" />';
            html += '<input type="button" class="save" value="Сохранить" /></p><p class="counter"><span>0</span> из 250</p></div>';
            self.parent().append(html);
            var storageId = self.attr('rel');
            var text = $('#' + storageId).val();
            $('#pencilator textarea').val(text);
            $('#pencilator p.counter span').text(text.length);
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
