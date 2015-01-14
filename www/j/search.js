(function ($) {
    /* Регистрация функции поиска с возможностью отмены */
    $.fn.searchDelayedSuggest = function (event, reqType, delay, url, dataType, dataCallback, successCallback, eventCallback) {
        var xhr, timer, ct = 0;
        return this.bind(event, function (e) {
            clearTimeout(timer);
            if ('function' === typeof(eventCallback)) {
                eventCallback(e);
            }
            if (e.which == 27) {
                if (xhr) xhr.abort();
                return false;
            }
            if (xhr) xhr.abort();
            timer = setTimeout(function () {
                var id = ++ct;
                xhr = jQuery.ajax({
                    type: reqType,
                    dataType: dataType,
                    url: url,
                    data: (dataCallback && dataCallback(xhr)),
                    success: function (data, status) {
                        xhr = null;
                        if (id == ct) successCallback(data, status);
                    }
                });
            }, delay);
        });
    };
})(jQuery);

jQuery(document).ready(function () {
    /** Загрузка подсказки **/
    jQuery.post('suggest/example', null, function (data) {
        if ('undefined' !== typeof(data.result)) {
            jQuery('form#search #search_suggest_sample_query').text(data.result[0].search_query);
            jQuery('form#search #search_suggest_sample').css('display', 'block');
        }
    }, 'json');

    var context = jQuery('form#search'), suggests = jQuery('div#search_suggestions'), input = jQuery('form#search #search_query_string');
    input.val('');
    input.bind('focus', function () {
        jQuery(this).val('');
    });

    /** Обработка клика на поле ввода текста запроса **/
    jQuery('form#search #search_suggest_sample_query').bind('click', function () {
        input.trigger('focus');
        input.val(jQuery(this).text());
        input.trigger('keyup');
    });

    /** Обработка результатов поиска **/
    input.searchDelayedSuggest('keyup', 'post', 400, "/suggest/search/", 'html',
        function (xhr) {
            input.addClass('request');
            suggests.stop().fadeOut();
            if (!input.val().length) {
                input.removeClass('request');
                xhr.abort();
                return {};
            }
            return {query: input.val()};
        },
        function (response) {
            input.removeClass('request');
            suggests.fadeIn();
            suggests.html(response);
            suggests.bind('mouseleave', function () {
                if (!input.is(":focus")) {
                    jQuery(this).fadeOut();
                }
            });
        },
        function (event) {
            if (!jQuery(event.target).val().length) {
                input.removeClass('request');
                suggests.fadeOut();
            }
            if (event.which == 27) {
                input.removeClass('request');
                suggests.stop().fadeOut();
            }
        }
    );
});