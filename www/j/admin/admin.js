$(document).ready(function() {
    /* Скрывать элементы при загрузке */
    $('.hidden').hide();
    /* универсальная кнопка свернуть/развернуть */
    $('.expand-toggle-control').click(function(){
        $(this).nextAll('.expand-toggle').first().toggle();
    });

    $('[data-filemanager]').filemanager();

    $('textarea.html').redactor({
        lang:'ru',
        imageUpload: '/od-men/json/mfile/upload',
        imageManagerJson: '/od-men/json/mediafiles',
        convertDivs: false,
        iframe: true,
        fixed: true,
        fixedBox: true,
        css: '/c/body.css',
        minHeight: 500,
        autoresize: true,
        plugins: ['fontcolor', 'goods', 'table', 'video', 'imagemanager', 'filemanager']
    });
    $('textarea.text').redactor({lang:'ru',  air: true, airButtons: ['bold', 'italic', 'link'], convertDivs: false });
    $("a[data-fancybox-type='ajax']").fancybox({
        arrows: false,
        type: 'ajax'
    });

    $(document).on('click', 'a.changee', function() {
        var id = $(this).closest('label').prop('for');

        $('#' + id).prop('readonly', false).css({ borderColor: 'red' }).after('<a class="no">!!! меняя email, убедитесь в согласии клиента !!!</a>');
        $('#' + id + ':after').css({ content:"" });
    });

    $('#search_flags label').click(function(e) {
        var c = this.childNodes[0].className, c1 = 'tr';
        switch(c) {
            case 'tr0':
                c1 = 'tr';
                break;
            case 'tr1':
                c1 = 'tr0';
                break;
            case 'tr':
            default:
                c1 = 'tr1';
                break;
        }
        this.childNodes[0].className = c1;
        this.childNodes[2].value = c1.replace('tr', '');
    });

    $(document).on('submit', '#goodz', function() {
        return false;
    });
    $(window).scroll(function() {
        $('nav#menu').css('position','fixed');
        if (0 === $(window).scrollTop()) {
            $('nav#menu').css('position','relative');
        }
        return false;
    });

    $('#menu-toggle').click(function(event){
        $('nav#menu > blockquote').toggle();
        event.preventDefault();
        event.stopPropagation();
    });
    $(document).click(function(){$('nav#menu > blockquote').hide();});

    $(document)
        .on('click', '#goodz input[name=search]', function(ev) { // подбор товаров
            $.post('/od-men/goods', $('input, textarea, select', '#goodz').serialize(), function(data) {
                $('#goodz').parent().html(data);
            });
            return false;
        })
        .on('click', 'input[alt="list"]', function() {
            var sq = $('#search_query').detach();
            $(this).closest('form').append(sq);
        })
        .on('click', '#goodz input[name=all]', function() {
            var t = parseInt($('#qty').text(), 10), rel = $(this).attr('rel');
            if (isNaN(t)) {
                alert('Нет отобранных товаров');
                return false;
            }
            if (t > 200 && ! confirm('Будет добавлено более 200 товаров, вы уверены?')) {
                return false;
            }
            var send = $('input, select, textarea', '#goodz').serialize();
            send += '&action_id=' + $('#action_id').val();
            send += '&mode=' + ($('#chose' + rel).hasClass('goods_b') ? 'b' : '');

            var discount = $('#chose' + rel).attr('data-discount');
            if (typeof(discount) != 'undefined' && discount != "") send+= '&discount=' + discount;

            var min_qty = $('#chose' + rel).attr('data-min_qty');
            if (typeof(min_qty) != 'undefined' && min_qty != "") send+= '&min_qty=' + min_qty;

            $.post(
                '/od-men/chosen',
                send,
                function(data) {
                    $('#chose' + rel).closest('.area').append(data);
                    $.fancybox.close();
                }
            );
        })
        .on('click', '#goodz input[name=marked]', function() {
            var inputs = $('#goodz input:checked'), choice = [], rel = $(this).attr('rel');
            if (inputs.length < 1) {
                alert('Товаров не выбрано');
                return;
            }
            for(var i = 0; i < inputs.length; i++) {
                choice.push($(inputs[i]).attr('name').replace('choice[', '').replace(']', ''));
            }

            var post = {
                choice:       choice,
                action_id: $('#action_id').val(),
                mode: $('#chose' + rel).hasClass('goods_b') ? 'b' : ''
            };

            var discount = $('#chose' + rel).attr('data-discount');
            if (typeof(discount) != 'undefined' && discount != "") post.discount = discount;

            var min_qty = $('#chose' + rel).attr('data-min_qty');
            if (typeof(min_qty) != 'undefined' && min_qty != "") send+= '&min_qty=' + min_qty;

            $.post('/od-men/chosen', post, function(data) {
                $('#chose' + rel).closest('.area').append(data);
                $.fancybox.close();
            });

        })
        .on('click', '#goodz + .pager a', function() { // товары по страницам
            var page = $(this).text();
            $.post('/od-men/goods?page=' + page, $('input, textarea, select', '#goodz').serialize(), function(data) {
                $('#goodz').parent().html(data);
                $.fancybox.update();
            });
            return false;
        })
        .on('click', '#add_zone_time', function() {
            var table = $(this).prev('table'), clone = table.find('tr').last().clone();
            $('input:text', clone).val('');
            table.find('tbody').last().append(clone);
            return false;
        })
        .on('click', 'input[type=button].trdel', function() {
            var cnt = $(this).closest('.area').children('strong'),
                t = parseInt(cnt.text(), 10);
            $(this).closest('tr').remove();
            t--;
            $(cnt).text(t);
        })
        .on('submit', 'form.ajax', function() { // формы обрабатываются ажаксом

            var f = $(this), fancy = false;
            if (f.hasClass('proceed')) return false; // do not work with form already proceeded
            f.addClass('proceed');

            if (f.parent().hasClass('fancybox-inner')) fancy = true;
            $('input[type=submit]', this).after('<i class="load"></i>');

            $.post($(this).attr('action'), $('input', this).serialize(), function(data) {
                var redir = function(){};

                if (data.redirect){
                    redir = function(){
                        location.href = data.redirect;
                    }

                    if( Object.keys(data).length == 1 )
                        redir();
                }

                if (data.reload) location.reload();

                if( data.fancybox ){
                    $.fancybox.open([{
                        content:data.fancybox,
                        type: 'html',
                        beforeClose: redir
                    }]);
                }
                if (data.error) {
                    $('input.txt, input.wtxt, textarea.txt, textarea.wtxt', f).each(function() { // сообщения об ошибках на инпутах
                        if ( ! $(this).hasClass('misc')) {
                            var n = $(this).attr('name');
                            if (data.error[n]) {
                                $(this)
                                    .removeClass('ok')
                                    .addClass('error')
                                    .attr('error', data.error[n]);
                            } else {
                                $(this)
                                    .addClass('ok')
                                    .removeClass('error')
                                    .removeAttr('error');
                            }
                        }
                    });
                    $('select', f).each(function() { // ошибки на селектах

                    })
                }
                if (data.ok) {
                    $('input.txt, textarea.txt, input.wtxt, textarea.wtxt', f).each(function(i, item) {
                        $(item)
                            .addClass('ok')
                            .removeClass('error')
                            .removeAttr('error');
                    });
                }
                if (data.html) {
                    if (fancy) {
                        f.parent().html(data.html);
                        $.fancybox.update();
                    } else {
                        $('input.txt, textarea.txt, input.wtxt, textarea.wtxt', f).each(function(i, item) {
                            $(item)
                                .prop('readonly', 'readonly')
                                .addClass('ok')
                                .removeClass('error')
                                .removeAttr('error');
                        });
                        $('input[type=submit]', f).replaceWith(data.html);
                    }
                }
                $('i.load', f).remove();
                f.removeClass('proceed');
            }, 'json')
                .error(function (xhr, status, errorThrown) {
                    alert('Произошла ошибка:' + errorThrown +'\n' + status + '\n' + xhr.statusText);
                    f.removeClass('proceed');
                });

            return false;
        });

    /* SEO url validation */
    $('#translit').keyup(function(){
        var sef = $(this).val()
        if (sef.search(/[^a-z0-9-]/) != -1) {
            $(this).addClass('input-error');
            if ( $(this).next().is('.descr')) {
                $(this).next().addClass('error').html('Недопустимые символы в ЧПУ!');
            } else {
                $(this).after('<div class="descr error">Недопустимые символы в ЧПУ!</div>');
            }
        } else {
            $(this).removeClass('input-error').next().removeClass('error').html('Данные верны');
        }
    });

    /* Добавление товаров в акции, кнопка отметить все */
    $('#check_all').click(function(){
        if($(this).hasClass('do_uncheck')) {
            $('input.action_visible_good').removeAttr('checked');
            $(this).removeClass('do_uncheck');
        } else {
            $('input.action_visible_good').prop('checked','checked');
            $(this).addClass('do_uncheck');
        }
    });

    $('.datepicker-jqui').datepicker({
        dateFormat: 'yy-mm-dd'
    });
});
