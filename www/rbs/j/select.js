function initSelect() {
//    if ($('.select').size() && !navigator.userAgent.match(/iPhone|iPad|iPod/i)) {
//        var select = $('select.select').select();
//    }
};
//version 1.0.4
$.fn.select = function (o) {
    var callMethod = $.fn.select.method,
        itemClick = jQuery.Event("itemClick"),
        selectReady = jQuery.Event("selectReady"),
        enabled = jQuery.Event("enabled"),
        disabled = jQuery.Event("disabled"),
        destroyed = jQuery.Event("destroyed");
    if (typeof o == "string" && o in $.fn.select.method) {
        var select = $(this);
        callMethod[o](select);
        return select;
    }
    if (!("method" in $.fn.select)) {
        $.fn.select.method = {
            "destroy": function (select) {
                if (select.data('customized')) {
                    select.off('change.select');
                    $(document).off('click.select');
                    select.each(function () {
                        $(this).data('customSelect').off('click.select').remove();
                    });
                    select.removeData();
                    select.trigger('destroyed');
                } else {
                    throw new Error('объект не проинициализирован');
                }
            },
            "enable": function (select) {
                if (select.data('disable')) {
                    select.attr('disabled', false);
                    select.data('customSelect').first().on('click.select', select.data('openerHandler')).removeClass('disabled');
                    select.trigger('enabled');
                }
            },
            "disable": function (select) {
                if (!select.data('disable')) {
                    select.data('disable', true);
                    select.attr('disabled', true);
                    select.data('openerHandler', $._data(select.data('customSelect').first().get(0), "events").click[0].handler);
                    select.data('customSelect').first().off('click').addClass('disabled');
                    select.trigger('disabled');
                }
            }
        };
        callMethod = $.fn.select.method;
    }
    o = $.extend({
        "list": "ul",
        "item": "li",
        "itemHTML": "li",
        "openerClass": "ui-selectmenu",
        "icoClass": "ui-selectmenu-icon",
        "selectedClass": "ui-selectmenu-status",
        "activeItemClass": "active",
        "dropDownClass": "ui-selectmenu-menu",
        "style": "dropdown", //popup,dropdown
        "transferClass": true,
        "dropdownHasBorder": true,
        "hasIcons": false,
        "resizable": true,
        "triggerEvents": true,
        "autocomplete": false
    }, o);
    var select = [],
        body = $('body'),
        openerHTML = $('<a class="' + o.openerClass + '"><span class="' + o.icoClass + '"></span><span class="' + o.selectedClass + '"></span></a>'),
        dropdownHTML = $('<div class=' + o.dropDownClass + '>' +
            '<div class="select-top">' +
            '<div class="select-l"></div>' +
            '<div class="select-r"></div>' +
            '</div>' +
            '<div class="select-c">' +
            '<div class="c appendHere">' +
            '</div>' +
            '</div>' +
            '<div class="select-bottom">' +
            '<div class="select-l"></div>' +
            '<div class="select-r"></div>' +
            '</div>' +
            '</div>');
    $(this).each(function (i) {
        if (!$(this).data('customized')) {
            select.push(this);
        }
    });
    if (select.length) {
        $(select).each(function () {
            var opener = openerHTML.clone(),
                nativeSelect = $(this),
                title = nativeSelect.find("option[title]").text(),
                options = nativeSelect.find("option[title]").attr('disabled', true).end().find('option'),
                optionSize = options.size() - 1,
                dropdown = dropdownHTML.clone(),
                itemTree = o.itemHTML.split(' '),
                hasChild = itemTree.length >= 2,
                list = "<" + o.list + ">";
            nativeSelect.find('option').each(function (i, data) {
                if ($(this).attr('title')) {
                    list += "<" + o.item + " class='title' style='display:none;'>" + data.childNodes[0].nodeValue + "</" + o.item + ">";
                } else {
                    if (!hasChild) {
                        list += "<" + o.item + ">" + data.childNodes[0].nodeValue + "</" + o.item + ">";
                    } else {
                        var buffer = '';
                        for (var k = itemTree.length - 1; k != 0; k--) {
                            if (!buffer) {
                                buffer += "<" + itemTree[k] + ">" + data.childNodes[0].nodeValue + "</" + itemTree[k] + ">";
                            } else if (k != 0 && itemTree.length > 2) {
                                buffer = "<" + itemTree[k] + ">" + buffer + "</" + itemTree[k] + ">";
                            }
                        }
                        buffer = "<" + itemTree[0] + ">" + buffer + "</" + itemTree[0] + ">";
                        list += buffer;
                    }
                }
                if (i == optionSize) {
                    list += "</" + o.list + ">";
                }
            });
            list = $(list);
            dropdown = dropdown.find('.appendHere').removeClass('appendHere').append(list).end();
            opener.insertAfter(nativeSelect);
            opener.find('.' + o.selectedClass).text(nativeSelect.find('option:selected').text());
            body.append(dropdown);
            (o.dropdownHasBorder) ? dropdown.width(opener.width()) : dropdown.width(opener.outerWidth());
            if (o.transferClass) {
                opener.addClass(opener.attr('class') + " " + nativeSelect.attr('class'));
                dropdown.addClass(dropdown.attr('class') + " " + nativeSelect.attr('class'));
            }
            $(this).data('customSelect', opener.add(dropdown));
            $(this).data('customized', true);
            var listItems = list.find(">" + o.item),
                dropdownWidth = dropdown.outerWidth(),
                dropdownHeight = dropdown.outerHeight();
            selectedByHover = '',
                selected = '';
            if (!o.resizable) {
                opener.width(nativeSelect.outerWidth());
                (o.dropdownHasBorder) ? dropdownWidth = dropdown.width(opener.width()) : dropdownWidth = dropdown.width(opener.outerWidth());
            } else {
                $(window).on('resize.opener',function () {
                    (o.dropdownHasBorder) ? dropdownWidth = dropdown.width(opener.width()) : dropdownWidth = dropdown.width(opener.outerWidth());
                }).trigger('resize.opener');
            }
            if (title) {
                opener.find('.' + o.selectedClass).text(title);
                nativeSelect.trigger('change.select', [options.filter(':selected').index()]);
            }
            //autocomplete section
            if (o.autocomplete) {
                if (title) opener.find('.' + o.selectedClass).get(0).defaultValue = title;
                opener.find('.' + o.selectedClass).html('<input type="text" />');
                opener.find('.' + o.selectedClass).find('input').keyup(function (e) {
                    var searchVal = $.trim($(this).val()),
                        matched = [];
                    dropdown.show();
                    listItems.not('.title').each(function () {
                        var text = $(this).text();
                        if ((new RegExp(searchVal, 'ig')).test(text)) {
                            matched.push(this);
                        }
                    });
                    matched = $(matched);
                    matched.show().first().addClass(o.activeItemClass).siblings().removeClass(o.activeItemClass);
                    listItems.not(matched).hide();
                    $(this).off('keydown').keydown(function (e) {
                        if (e.keyCode == 13) {
                            matched.first().trigger('click');
                            $(this).blur();
                        }
                    });
                    if (!listItems.filter(':visible').size()) {
                        dropdown.hide();
                    }
                });
            }
            nativeSelect.on("change.select", function (e, selectedIndex, dontHide) {
//				alert("fire!");
				if (!selectedIndex && selectedIndex !== 0) selectedIndex = this.selectedIndex;
                listItems.removeClass(o.activeItemClass).eq(selectedIndex).addClass(o.activeItemClass);
                selected = options.removeAttr('selected').eq(selectedIndex);
				selected.get(0).selected = true;
                selectedByHover = selected;
                if (o.autocomplete) {
                    opener.find('input').val(selected.text());
                } else {
                    opener.find('.' + o.selectedClass).text(selected.text());
                }
                if (!dontHide) {
                    dropdown.hide();
                    $(document).off('keydown.select');
                }
            });
            if (o.hasIcons) {
                options.each(function (i) {
                    listItems.eq(i).prepend('<span class="' + this.className + '"></span>');
                });
                nativeSelect.on("change.select", function (e, selectedIndex, dontHide) {
                    opener.find('.' + o.selectedClass).prepend('<span class="' + selected.attr('class') + '"></span>');
                });
                opener.find('.' + o.selectedClass).prepend('<span class="' + options.filter(':selected').attr('class') + '"></span>');
            }
            nativeSelect.hide();
            listItems.click(function (e) {
                nativeSelect.trigger("change.select", [$(this).index()]);
                dropdown.hide();
            });
            listItems.hover(function () {
                selectedByHover = $(this);
            }, function () {
                selectedByHover = "";
            });
            opener.click(function (e) {
                if (bindingsEnabled) return true;
                dropdown.click(function(){
                    $('#'+nativeSelect.attr('id')).trigger('change.payment');
                });
                if (dropdown.is(':hidden')) {
                    dropdown.show();
                    alignDropDown();
                    $(document).off('keydown.select');
                    $(document).on('keydown.select', function (e) {
                        if (selected && e.keyCode == 13 && selectedByHover) {
                            nativeSelect.trigger("change.select", [selectedByHover.index()]);
                            e.preventDefault();
                        }
                        if (selected && e.keyCode == 38 && selected.prev().size() && !selected.prev().is(':disabled')) {
                            nativeSelect.trigger("change.select", [selected.prev().index(), true]);
                            if (o.style == "popup") {
                                alignDropDown();
                            }
                            e.preventDefault();
                        } else if (selected && e.keyCode == 40 && selected.next().size() && !selected.next().is(':disabled')) {
                            nativeSelect.trigger("change.select", [selected.next().index(), true]);
                            alignDropDown();
                            e.preventDefault();
                        }
                    });
                } else {
                    dropdown.hide();
                }
            });
            $(window).on('resize.select', function () {
                if (dropdown.is(':visible')) {
                    alignDropDown();
                }
            });
            $(document).on('mousedown.select', function (e) {
                if (!$(e.target).closest(dropdown).size() && !$(e.target).closest(opener).size()) {
                    dropdown.hide();
                    $(document).off('keydown.select');
                }
            });
            //event section
            if (o.triggerEvents) {
                listItems.click(function (e) {
                    nativeSelect.trigger(itemClick, [$(this).text()]);
                });
                nativeSelect.trigger(selectReady, [dropdown]);
            }
            function alignDropDown() {
                if (o.style == "dropdown") {
                    var top = opener.offset().top + opener.outerHeight(),
                        left = opener.offset().left;
                    /*
                     if(top + dropdownHeight > $(window).height() && top - dropdownHeight - opener.outerHeight() > 0){
                     dropdown.css({
                     'top': top - dropdownHeight - opener.outerHeight(),
                     'left': left
                     });
                     }else{
                     */
                    dropdown.css({
                        'top': top,
                        'left': left
                    });
                    /*
                     }
                     */
                } else {
                    var activeEl = listItems.eq(nativeSelect.get(0).selectedIndex);
                    activeEl = activeEl.hasClass('title') ? activeEl.next() : activeEl;
                    var top = opener.offset().top - activeEl.position().top,
                        left = opener.offset().left;
                    dropdown.css({
                        'top': top,
                        'left': left
                    });
                }
            }

            if (nativeSelect.is(':disabled')) nativeSelect.select('disable');
        });
    } else {
        //throw Error('селект/ы уже проинициализирован/ы');
    }
}