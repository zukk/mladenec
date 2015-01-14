<html>
<head>
    <title>1c test</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <script type="text/javascript" src="/j/jquery.min.js"></script>
    <style type="text/css">
        #result { padding:10px; border:1px solid #ccc;}
    </style>
    <script type="text/javascript">
        $(document).ready(function () {
            $('#do').submit(function (e) {
                e.preventDefault();
                var ddo = $('#query').val();
                var date = $('#date').val();
                $.post('/1c/' + $('#action').val() + '.php?encoding=utf8' + (ddo ? '&action=' + ddo : '') + (date ? '&date=' + date : ''), $('#body').val(), function(data) {
                    $('#result').append(data);
                });
                return false;
            });
        });
    </script>
</head>
<body>
    <h1>Тестирование 1C</h1>
    <form action="" id="do">
        <textarea name="body" id="body" style="height:300px; width:400px;"></textarea><br />
        
        Действие:
        <select id="action">
            <option>orders_export</option>
            <option>orders_import</option>
            <option>users</option>
            <option>orders_valid</option>
            <option>users_valid</option>
            <option>users_upload</option>
            <option>catalog_upload</option>
            <option>couriers</option>
            <option>call</option>
            <option>price</option>
            <option>sms</option>
            <option>testreply</option>
            <option>astra/routes_export</option>
            <option>astra/orders_import</option>
            <option>astra/orders_check</option>
            <option>astra/points_import</option>
            <option>astra/garages_import</option>
            <option>astra/resources_import</option>
        </select><br />
        Дата:<br />
        <select id="date">
            <option value="">Нет</option>
            <option>{date('Y-m-d',strtotime('tomorrow'))}</option>
            <option>{date('Y-m-d',time())}</option>
            <option>{date('Y-m-d',strtotime('yesterday'))}</option>
            <option>{date('Y-m-d',strtotime('2 days ago'))}</option>
            <option>{date('Y-m-d',strtotime('3 days ago'))}</option>
            <option>{date('Y-m-d',strtotime('4 days ago'))}</option>
            <option>{date('Y-m-d',strtotime('5 days ago'))}</option>
            <option>{date('Y-m-d',strtotime('6 days ago'))}</option>
        </select>
        <select id="query">
            <option value="">Для catalog_upload:</option>
            <option>catalog</option>
            <option>manufacturers</option>
            <option>product</option>
            <option>product_light</option>
            <option>filter_cat_val</option>
            <option>filter_goods</option>
        </select>
        <input type="submit" /><br />
    </form>
    <pre id="result"></pre>
    <h2></h2>
    <p>Примеры выгрузок:</p>
    <p><b>Product light</b></p>
    <p>артикул©на складе©цена|цена любимый©активный©старая цена</p>
    <pre>
50909©174©48.8|48.8©Y©0
ICON 4 RT original cream gepard©-1©7099.11©7099.11©Y©0
    </pre>
    <p><b>SMS</b></p>
    <pre>
+79099130152|Тестовое сообщение
+79099130152|Тестовое сообщение 2
    </pre>
    <p><b>Orders import</b></p>
    <pre>
ЗАКАЗ
$ship_date @ $id @ $user_id @ $status @ $tmp @ $total @ $manager @ $courier @ $from @ $to @ $tmp 
СКИДКА: $discount
АДРЕС: $city | $street | $house @ $correct_addr @ $latlong @ $enter | $lift | $floor | $domofon | $kv | $mkad | $comment 
ОПЛАТА: $pay_type @ $pay_amount
$code @ $qty @ $price
КОНЕЦЗАКАЗА
    </pre>
    <pre>
ЗАКАЗ
12.04.14©397174©58468©X©0©1135.76©©©14©22©0
АДРЕС115498: Москва|Щелковское шоссе|3©Y©37.753038,55.803565©2||4|4|4|0|ТЕСТ ВКонтакте
СКИДКА: 0
7877©1©45.76
0012_mufta©1©840
systDOST©1©250
КОНЕЦЗАКАЗА
    </pre>
</body>
</html>