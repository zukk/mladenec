<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=Edge" />
    <title>{$title|default:'Mladenec.ru'}</title>

    <script type="text/javascript" src="{Model_File::get_host()}/j/jquery.min.js?v=1.11.1"></script>
    <script type="text/javascript" src="{Model_File::get_host()}/j/script.js?v=100814"></script>
    <script type="text/javascript" src="{Model_File::get_host()}/j/jquery.maskedinput.min.js"></script>
    <script type="text/javascript" src="{Model_File::get_host()}/j/fancybox/jquery.fancybox.pack.js?v=2.1.5"></script>

    <link href="{Model_File::get_host()}/j/fancybox/jquery.fancybox.css" rel="stylesheet" type="text/css" />
    <link href="{Model_File::get_host()}/c/style.css?v=100814" rel="stylesheet" type="text/css" />
    <link href="{Model_File::get_host()}/c/averburg.css?v=100814" rel="stylesheet" type="text/css" />

    <link rel="shortcut icon" href="/i/favicon.ico" type="image/x-icon" />
    <link rel="icon" href="/i/favicon.ico" type="image/x-icon" />

    <link href="/c/1c.css" rel="stylesheet" type="text/css" />

    <script type="text/javascript">
        is_kiosk = false; register_poll = false; product_load = false; IE7 = false;
    </script>
</head>

<body>
<div id="all">

    <div id="body">

        <div id="content">
            <br />
            {$body|default:'Младенец.РУ'}
        </div>

    </div>

</div>

</body>
</html>
{$profile|default:''}