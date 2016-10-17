<form action="" class="forms forms-columnar">
    <fieldset>
        <legend>Поиск заказов</legend>
        <div class="units-row">
            <div class="unit-20">
                <b>ID заказа:</b><br />
                <input type="text" class="width-100" name="order_id" value="{$smarty.get.order_id|default:''}" />
            </div>
            <div class="unit-20">
                <b>ID&nbsp;Пользователя:</b><br />
                <input type="text" class="width-100" name="user_id" value="{$smarty.get.user_id|default:''}" />
            </div>
            <div class="unit-30">
                <b>Имя:</b><br />
                <input type="text" class="width-100" name="name" value="{$smarty.get.name|default:''}" />
            </div>
            <div class="unit-30">
                <b>Email:</b><br />
                <input type="text" class="width-100" name="email" value="{$smarty.get.email|default:''}" />
            </div>
        </div>
        <div class="units-row">
            <div class="unit-25">
                <b>Статус заказа:</b><br />
                <select name="status">
                    <option value="">все</option>
                    {html_options options=Model_Order::get_status_list() selected=$smarty.get.status|default:''}
                </select>
            </div>
            <div class="unit-25">
                <b>Тип оплаты:</b><br />
                <select name="pay_type">
                    <option value="">все</option>
                    {html_options options=Model_Order::pay_types() selected=$smarty.get.pay_type|default:''}
                </select>
                <br />
                <label><input type="checkbox" name="coupon" value="1" {if $smarty.get.coupon}checked{/if}> Купон <input id="coupon_name" name="coupon_name" value="{$smarty.get.coupon_name}" /></label>

                <script>
                    $(document).ready(function() {

                        $("#coupon_name")
                                .on('blur keyup mouseup', function () {
                                    var v = $.trim($(this).val());
                                    if (v == '') $(this).next().val('').addClass("empty")
                                })
                                .autocomplete({
                                    source: function (request, response) {
                                        var term = $.trim(request.term);
                                        $.getJSON("/od-men/ajax/autocomplete.php?term=" + term, {
                                            model: "coupon",
                                            fields: [ "name"]
                                        }, function (data) {
                                            response(data);
                                        });
                                    },
                                    minLength:1,
                                    maxHeight:300,
                                    select: function(value, data) {
                                        $(this).removeClass('input-error');
                                        $(this).next().val(data.item.id).removeClass("empty");
                                    }
                                })

                                .data("ui-autocomplete")

                                ._renderItem = function(ul, item) {

                            return $( "<li></li>" )
                                    .data( "item.ui-autocomplete", item )
                                    .append( "<a><span style=\"color: green\">" + item.id + "</span> " + item.label + "</a>" )
                                    .appendTo( ul );
                        };
                    });
                </script>


                <!--a href="{Route::url('admin_order_card')}">Проблемный безнал</a-->
            </div>
            <div class="unit-50 datepicker">
                <b>Отправлен:</b><br />

                <div class="nowrap" style="overflow: hidden;">
                    <b class="fl">С: </b>
                    <input class="fl" type="date" name="from[date]" value="{$from.date|default:''}" />
                    <input class="fl" type="time" name="from[time]" value="{$from.time|default:''}" />
                </div><br />

                <div class="nowrap cl" style="overflow: hidden;">
                    <b class="fl">По: </b>
                    <input class="fl" type="date" name="to[date]" value="{$to.date|default:''}" />
                    <input class="fl" type="time" name="to[time]" value="{$to.time|default:''}" />
                </div>
            </div>
        </div>
        <div class="units-row">
            <div class="unit-80"><input type="submit" name="search" value="Показать" /></div>
            <div class="unit-20"><a href="{Route::url('admin_order_excel')}?{$smarty.server.QUERY_STRING}">Скачать в Excel</a></div>
        </div>
    </fieldset>
</form>
<div class="cb"></div>
<div class="pager">
	<span>
		Оформленных: {$oformlennikh}
	</span>
</div>

{include file='admin/order/_list.tpl'}

