<?php defined('SYSPATH') or die('No direct script access.');

// -- Environment setup --------------------------------------------------------
// Load the core Kohana class
require SYSPATH . 'classes/kohana/core' . EXT;

if (is_file(APPPATH . 'classes/kohana' . EXT)) {
    // Application extends the core
    require APPPATH . 'classes/kohana' . EXT;
} else {
    // Load empty core extension
    require SYSPATH . 'classes/kohana' . EXT;
}

/**
 * Set the default time zone.
 *
 * @see  http://kohanaframework.org/guide/using.configuration
 * @see  http://php.net/timezones
 */
date_default_timezone_set('Europe/Moscow');

/**
 * Set the default locale.
 *
 * @see  http://kohanaframework.org/guide/using.configuration
 * @see  http://php.net/setlocale
 */
setlocale(LC_ALL, 'en_US.utf-8');

/**
 * Enable the Kohana auto-loader.
 *
 * @see  http://kohanaframework.org/guide/using.autoloading
 * @see  http://php.net/spl_autoload_register
 */
spl_autoload_register(array('Kohana', 'auto_load'));

/**
 * Enable the Kohana auto-loader for unserialization.
 *
 * @see  http://php.net/spl_autoload_call
 * @see  http://php.net/manual/var.configuration.php#unserialize-callback-func
 */
ini_set('unserialize_callback_func', 'spl_autoload_call');

// -- Configuration and initialization -----------------------------------------

/**
 * Set the default language
 */
I18n::lang('en-us');

/**
 * Set Kohana::$environment if a 'KOHANA_ENV' environment variable has been supplied.
 *
 * Note: If you supply an invalid environment name, a PHP warning will be thrown
 * saying "Couldn't find constant Kohana::<INVALID_ENV_NAME>"
 */
Kohana::$environment = Kohana::PRODUCTION; // БОЕВОЙ САЙТ!!!
//Kohana::$environment = Kohana::DEVELOPMENT; // САЙТ НА РАЗРАБОТКЕ !!!
/**
 * Initialize Kohana, setting the default options.
 *
 * The following options are available:
 *
 * - string   base_url    path, and optionally domain, of your application   NULL
 * - string   index_file  name of your index file, usually "index.php"       index.php
 * - string   charset     internal character set used for input and output   utf-8
 * - string   cache_dir   set the internal cache directory                   APPPATH/cache
 * - boolean  errors      enable or disable error handling                   TRUE
 * - boolean  profile     enable or disable internal profiling               TRUE
 * - boolean  caching     enable or disable internal caching                 FALSE
 */
Kohana::init(array(
    'base_url' => '/',
    'index_file' => '',
    'errors'	=> FALSE,
));

/**
 * Attach the file write to logging. Multiple writers are supported.
 */
Kohana::$log->attach(new Log_File(APPPATH . 'logs'));

/**
 * Attach a file reader to config. Multiple readers are supported.
 */
Kohana::$config->attach(new Config_File);

/**
 * Enable modules. Modules are referenced by a relative or absolute path.
 */
Kohana::modules(array(
    'cache' => MODPATH . 'cache',      // Caching with multiple backends
    'codebench' => MODPATH . 'codebench',  // Benchmarking tool
    'database' => MODPATH . 'database',   // Database access
    'image' => MODPATH . 'image',      // Image manipulation
    'orm' => MODPATH . 'orm',        // Object Relationship Mapping
    'smarty' => MODPATH . 'smarty',     // Smarty Template Engine

    'unittest' => MODPATH . 'unittest',   // Unit testing
    // 'userguide'  => MODPATH.'userguide',  // User guide and API documentation
));

$to_id = DB::select('to_id')
        ->from('tag_redirect')
        ->where('to_id', '>', 0)
        ->where('url', '=', Request::detect_uri())
        ->execute()->get('to_id');

if ( ! empty($to_id)) {

    $to_url = DB::select('url')
            ->from('tag_redirect')
            ->where('id', '=', $to_id)
            ->execute()->get('url');

    if ( ! empty($to_url)) {
        header("HTTP/1.1 301 Moved Permanently");
        header ('Location: http://' . $_SERVER['HTTP_HOST'] . '/' . $to_url);
        exit();
    }
}

/**
 * Set the routes. Each route must have a minimum of a name, a URI and a set of
 * defaults for the URI.
 */

if( empty($_SERVER['HTTP_HOST']) ||  ! preg_match( '#^m\.#', $_SERVER['HTTP_HOST'] ) ){

	// арбузы
	Route::set('arbuz', 'arbuz.php')
		->defaults(array('controller' => 'ajax', 'action' => 'arbuz'));

	// проброс вызовов из GetResponse
	Route::set('getresponse', 'user/getresponse_q9w8E7r6.php')
		->defaults(array('controller' => 'page', 'action' => 'getresponse'));

	// проброс отписки из subscribe
	Route::set('unsubscribe_pro', 'unsubscribe_q6jknPvOLDF8_<email>', array(
		'email' => '.+@.+\..+'
	))
		->defaults(array('controller' => 'user', 'action' => 'unsubscribe_pro'));

	// работа с 1c - Астра
	Route::set('odinc_astra', '1c/astra/<action>.php')
		->defaults(array('controller' => 'odinc_astra', 'action' => 'index'));

	// работа с 1c
	Route::set('odinc', '1c/<action>.php')
		->defaults(array('controller' => 'odinc', 'action' => 'index'));

	// поищу
	Route::set('sync', 'sync')
		->defaults(array('controller' => 'page', 'action' => 'sync'));

	// user
	Route::set('unsubscribe', 'unsubscribe')
		->defaults(array('controller' => 'user', 'action' => 'unsubscribe'));

	Route::set('user_error', 'user/error')
		->defaults(array('controller' => 'ajax', 'action' => 'error'));

    Route::set('toggle_state', 'toggle_state')
        ->defaults(array('controller' => 'ajax', 'action' => 'toggle'));

	Route::set('register', 'registration')
		->defaults(array('controller' => 'user', 'action' => 'register'));

	Route::set('user', 'account')
		->defaults(array('controller' => 'user', 'action' => 'view'));

	Route::set('user_forgot', 'account/index.php')
		->defaults(array('controller' => 'user', 'action' => 'forgot'));

	Route::set('user_password', 'account/password')
		->defaults(array('controller' => 'user', 'action' => 'password'));

    Route::set('pay', '<id>', array('id' => '\d{6,}'))
        ->defaults(array('controller' => 'user', 'action' => 'pay'));

	Route::set('login', 'user/login')
		->defaults(array('controller' => 'user', 'action' => 'login'));

	Route::set('logout', 'user/logout')
		->defaults(array('controller' => 'user', 'action' => 'logout'));

	Route::set('external_clean', 'user/external/clean')
		->defaults(array('controller' => 'user', 'action' => 'external_clean'));

	Route::set('order2', 'personal/order_data2.php')
		->defaults(array('controller' => 'user', 'action' => 'order2'));

	Route::set('google_goods', 'get-goods')
		->defaults(array('controller' => 'product', 'action' => 'google_goods'));

    Route::set('add_deferred', 'add_deferred')
        ->defaults(array('controller' => 'product', 'action' => 'add_deferred'));

	Route::set('cart_clear', 'personal/cart_clear.php')
		->defaults(array('controller' => 'ajax', 'action' => 'cart_clear'));

	Route::set('cart_remove_good', 'personal/cart_remove_good.php')
		->defaults(array('controller' => 'product', 'action' => 'cart_remove_good'));

	Route::set('cart_recount', 'personal/cart_recount.php')
		->defaults(array('controller' => 'product', 'action' => 'cart_recount'));

	Route::set('cart_presents', 'ajax/cart_presents.php')
		->defaults(array('controller' => 'product', 'action' => 'cart_presents'));

	Route::set('order_back', 'order/back')
		->defaults(array('controller' => 'user', 'action' => 'back'));

	Route::set('edost', 'personal/edost.php')
		->defaults(array('controller' => 'ajax', 'action' => 'edost'));

	Route::set('order_valid', 'personal/order.php')
		->defaults(array('controller' => 'user', 'action' => 'order_valid'));

	Route::set('payment', 'payment/pay_success.php')
		->defaults(array('controller' => 'user', 'action' => 'pay_success'));

	Route::set('payment_back', 'payment/back')
		->defaults(array('controller' => 'user', 'action' => 'payment_back'));

	Route::set('user_order', 'account/order')
		->defaults(array('controller' => 'user', 'action' => 'orders'));

    Route::set('user_deferred', 'account/deferred')
        ->defaults(array('controller' => 'user', 'action' => 'deferred'));

	Route::set('user_address', 'account/address')
		->defaults(array('controller' => 'user', 'action' => 'address'));

	Route::set('user_child', 'account/children')
		->defaults(array('controller' => 'user', 'action' => 'children'));

    Route::set('user_city', 'user/city')
        ->defaults(array('controller' => 'ajax', 'action' => 'city'));

	Route::set('user_action', 'account/action')
		->defaults(array('controller' => 'user', 'action' => 'action'));

	Route::set('user_reviews', 'account/reviews')
		->defaults(array('controller' => 'user', 'action' => 'reviews'));

	Route::set('user_goods', 'account/goods')
		->defaults(array('controller' => 'user', 'action' => 'goods'));

	Route::set('user_child_delete', 'account/child/delete/<id>', array('id' => '\d+'))
		->defaults(array('controller' => 'user', 'action' => 'child_delete'));

	Route::set('order_detail', 'account/order/<id>(/<thanx>)', array('id' => '\d+', 'thanx' => 'thanx'))
		->defaults(array('controller' => 'user', 'action' => 'order'));

	Route::set('one_click', 'one_click')
		->defaults(array('controller' => 'product', 'action' => 'one_click'));

	Route::set('ajax_user_phone', 'user/phone')
		->defaults(array('controller' => 'user', 'action' => 'phone'));

	Route::set('ajax_zone', 'user/zone')
		->defaults(array('controller' => 'user', 'action' => 'zone'));

	Route::set('ajax_user_goods', 'user/goods_ajax')
		->defaults(array('controller' => 'user', 'action' => 'goods_ajax'));

	Route::set('ajax_frequent', 'frequent/<id>', array('id' => '\d+'))
		->defaults(array('controller' => 'ajax', 'action' => 'frequent'));

    Route::set('ajax_cart_merge', 'cart_merge', array())
		->defaults(array('controller' => 'ajax', 'action' => 'cart_merge'));

	Route::set('callback', 'callback')
		->defaults(array('controller' => 'ajax', 'action' => 'callback'));

	Route::set('pampers_anketa', 'pampers_anketa')
		->defaults(array('controller' => 'page', 'action' => 'pampers'));

	// section
	Route::set('section_map', 'catalog')
		->defaults(array('controller' => 'section', 'action' => 'map'));

	Route::set('pampers', 'pampers')
		->defaults(array('controller' => 'section', 'action' => 'pampers'));

    Route::set('section', 'catalog/<translit>(/<id>(_<fv_id>).html(&<query>))', array(
        'translit' => '[a-z0-9_-]+',
        'id' => '\d+',
        'fv_id' => '\d+',
        'query' => '.*',
    ))->defaults(array('controller' => 'section', 'action' => 'view',));

    // после секции проверим таг
    Route::set('tag', '<code>', array('code' => '(tag/[/0-9a-z_-]+\.html|catalog/[0-9a-z_-]+/[0-9a-z_-]+)(\.html)?')) // сначала проверим на теговую, потом на каталог
        ->defaults(array('controller' => 'product', 'action' => 'tag'));

	// product
	Route::set('product_1c', 'product/1c/<code>', array(
		'code' => '.+',
	))->defaults(array('controller' => 'product', 'action' => 'view1c'));

	Route::set('product', 'product/<translit>/<group_id>.<id>.html', array(
		'translit' => '[a-z0-9-+]+',
		'group_id' => '\d+',
		'id' => '\d+',
	))->defaults(array('controller' => 'product', 'action' => 'view'));

	Route::set('productajax', 'product/<id>.html', array(
		'id' => '\d+',
	))->defaults(array('controller' => 'product', 'action' => 'view_ajax'));

    Route::set('ajax_hitz_section', 'ajax/hitz/<section_id>', array(
		'section_id' => '\d+'
    ))->defaults(array('controller' => 'ajax', 'action' => 'hitz'));

	// контекстная подсказка.
	Route::set('search_suggest', 'suggest/search')
		->defaults(array('controller' => 'ajax', 'action' => 'search_suggestion'));

	Route::set('search_suggest_help', 'suggest/example')
		->defaults(array('controller' => 'search', 'action' => 'example'));

	Route::set('search', 'search')
		->defaults(array('controller' => 'product', 'action' => 'search'));

	Route::set('cart', 'personal/basket.php')
		->defaults(array('controller' => 'product', 'action' => 'cart'));

	Route::set('cart_delivery', 'cart/delivery.php')
		->defaults(array('controller' => 'product', 'action' => 'cart_delivery'));

	Route::set('cart_coupon', 'cart/coupon.php')
		->defaults(array('controller' => 'product', 'action' => 'cart_coupon'));

    Route::set('delivery_open', 'cart/delivery_open.php')
        ->defaults(array('controller' => 'product', 'action' => 'delivery_open'));

	Route::set('cart_comments', 'cart/comments.php')
		->defaults(array('controller' => 'product', 'action' => 'cart_comments'));

	Route::set('cart_ajax', 'personal/basket_ajax')
		->defaults(array('controller' => 'ajax', 'action' => 'cart'));

	Route::set('product_add', 'product/add')
		->defaults(array('controller' => 'product', 'action' => 'add'));

	Route::set('review', 'review/add/<id>', array('id' => '[0-9]+'))
		->defaults(array('controller' => 'product', 'action' => 'review'));

	Route::set('review_vote', 'review/<vote>/<id>', array('id' => '[0-9]+', 'vote' => '(ok|no)'))
		->defaults(array('controller' => 'ajax', 'action' => 'review'));

	Route::set('warn', 'product/warn/<id>', array('id' => '[0-9]+'))
		->defaults(array('controller' => 'ajax', 'action' => 'warn'));

	Route::set('novelty', 'novelty')
		->defaults(array('controller' => 'product', 'action' => 'new'));

	Route::set('superprice', 'superprice')
		->defaults(array('controller' => 'product', 'action' => 'superprice'));

	Route::set('hitz', 'hitz')
		->defaults(array('controller' => 'product', 'action' => 'hitz'));

	Route::set('discount', 'about/sale.php')
		->defaults(array('controller' => 'product', 'action' => 'discount'));

	Route::set('slide', 'slide/<type>(/<param>)')
		->defaults(array('controller' => 'ajax', 'action' => 'slide'));

	Route::set('stats', 'rate/<type>/<id>', array('type' => '(product|group)', 'id' => '[0-9]+'))
		->defaults(array('controller' => 'ajax', 'action' => 'stats'));

	Route::set('reviews', 'review/<type>/<id>(/<page>)', array('type' => '(product|group)', 'id' => '[0-9]+', 'page' => '[0-9]+'))
		->defaults(array('controller' => 'ajax', 'action' => 'reviews'));

	// карта сайта
	Route::set('map', 'site_map/list.php')
		->defaults(array('controller' => 'page', 'action' => 'map'));

	Route::set('map_section', 'site_map/<translit>/<id>.html', array(
		'translit' => '[a-z0-9_-]+',
		'id' => '\d+'
	))->defaults(array('controller' => 'page', 'action' => 'map'));

	// дерево теговых (@deprecated?)
	Route::set('tag_tree', 'tag')
		->defaults(array('controller' => 'page', 'action' => 'tag'));

	// новости
	Route::set('new', 'about/news/<id>', array('id' => '\d+'))
		->defaults(array('controller' => 'news', 'action' => 'view'));

	Route::set('news', 'about/news')
		->defaults(array('controller' => 'news', 'action' => 'list'));

	/* Бренды */
	Route::set('brands', 'about/brands')
		->defaults(array('controller' => 'brand', 'action' => 'list'));

    Route::set('brand', 'brand/<translit>', array('translit' => '[a-z0-9_-]+'))
        ->defaults(array('controller' => 'brand', 'action' => 'view'));

	// отзывы
	Route::set('comment', 'about/review/<id>', array('id' => '\d+'))
		->defaults(array('controller' => 'comments', 'action' => 'view'));

	Route::set('comment_add', 'about/review/add')
		->defaults(array('controller' => 'comments', 'action' => 'add'));

	Route::set('comments', 'about/review')
		->defaults(array('controller' => 'comments', 'action' => 'index'));

	Route::set('comments_list', 'about/review/list')
		->defaults(array('controller' => 'comments', 'action' => 'list'));

	// статьи
	Route::set('article', '(<old_link>)article(/<id>)', array('id' => '\d+', 'old_link' => 'about/'))
		->defaults(array('controller' => 'page', 'action' => 'article'));

	// акции
	//Route::set('action_list', 'actions')
	//	->defaults(array('controller' => 'action', 'action' => 'list'));

	Route::set('action_list', 'actions(/current)')
		->defaults(array('controller' => 'action', 'action' => 'current_list'));

	Route::set('action_arhive', 'actions/arhive')
		->defaults(array('controller' => 'action', 'action' => 'arhive'));

	Route::set('action_goods_ajax', 'actions/<id>/goods', array('id' => '\d+'))
		->defaults(array('controller' => 'ajax', 'action' => 'action_goods'));

	Route::set('action', 'actions/(<id>)', array('id' => '\d+'))
		->defaults(array('controller' => 'action', 'action' => 'view'));

	// капча
	Route::set('captcha', 'captcha')
		->defaults(array('controller' => 'page', 'action' => 'captcha'));


	// aдмин
	Route::set('admin', 'od-men')
		->defaults(array('controller' => 'admin', 'action' => 'index'));

    Route::set('admin_ajax_call', 'od-men/ajax/card_call')
        ->defaults(array('controller' => 'admin_ajax', 'action' => 'card_call'));

    Route::set('admin_ajax_cash', 'od-men/ajax/card_cash')
        ->defaults(array('controller' => 'admin_ajax', 'action' => 'card_cash'));

	Route::set('admin_ajax_can_pay', 'od-men/ajax/card_can_pay')
		->defaults(array('controller' => 'admin_ajax', 'action' => 'card_can_pay'));

	Route::set('admin_ajax_add', 'od-men/ajax/<model>/form', array('model' => '[a-z_]+'))
		->defaults(array('controller' => 'admin_ajax', 'action' => 'form'));

    Route::set('admin_ajax_form', 'od-men/ajax/<model>/<id>', array('model' => '[a-z_]+', 'id' => '\d+'))
		->defaults(array('controller' => 'admin_ajax', 'action' => 'form'));

	Route::set('admin_ajax_list', 'od-men/ajax/<model>', array('model' => '[a-z_]+'))
		->defaults(array('controller' => 'admin_ajax', 'action' => 'list'));

	Route::set('admin_ajax', 'od-men/ajax/<action>.php')
		->defaults(array('controller' => 'admin_ajax', 'action' => 'index'));

	Route::set('admin_pricelab', 'od-men/pricelab(/<ymd>)', array('ymd' => '20\d\d/\d\d/\d\d'))
		->defaults(array('controller' => 'admin', 'action' => 'pricelab'));

	Route::set('admin_tag_excel', 'od-men/tag/excel')
		->defaults(array('controller' => 'admin', 'action' => 'tag_excel'));

    Route::set('admin_tag_recount', 'od-men/tag/recount')
        ->defaults(array('controller' => 'admin_ajax', 'action' => 'tag_recount'));

	Route::set('admin_order_excel', 'od-men/order_excel')
		->defaults(array('controller' => 'admin', 'action' => 'order_excel'));

    Route::set('admin_user_excel', 'od-men/user_excel')
		->defaults(array('controller' => 'admin', 'action' => 'user_excel'));


    Route::set('admin_order_card', 'od-men/order_card')
        ->defaults(array('controller' => 'admin', 'action' => 'order_card'));

	Route::set('admin_filemanager_dir', 'od-men/filemanager/<mdir_id>', array('mdir_id' => '\d+'))
		->defaults(array('controller' => 'admin', 'action' => 'filemanager'));

	Route::set('admin_filemanager', 'od-men/filemanager')
		->defaults(array('controller' => 'admin', 'action' => 'filemanager'));

	Route::set('admin_direct', 'od-men/direct')
		->defaults(array('controller' => 'admin', 'action' => 'direct'));

	Route::set('admin_tagbylink', 'od-men/tagbylink')
		->defaults(array('controller' => 'admin', 'action' => 'tagbylink'));

	Route::set('admin_tag_excel', 'od-men/tag_excel')
		->defaults(array('controller' => 'admin', 'action' => 'tag_excel'));

	Route::set('admin_json_mdir_list', 'od-men/json/mdir/list')
		->defaults(array('controller' => 'admin_json', 'action' => 'mdir_list'));

	Route::set('admin_json_mfile_upload', 'od-men/json/mfile/upload')
		->defaults(array('controller' => 'admin_json', 'action' => 'mfile_upload'));

	Route::set('json_object', 'od-men/json/<model>/<id>', array('model' => '[a-z_]+', 'id' => '\d+'))
		->defaults(array('controller' => 'admin_json', 'action' => 'object'));
	/*
	Route::set('admin_mediafiles_list', 'od-men/json/mediafiles')
		->defaults(array('controller' => 'admin_json', 'action' => 'mediafiles'));

	Route::set('json_brands', 'od-men/json/brands')
		->defaults(array('controller' => 'admin_json', 'action' => 'brands'));

	Route::set('json_sections', 'od-men/json/sections')
		->defaults(array('controller' => 'admin_json', 'action' => 'sections'));

	Route::set('json_goods', 'od-men/json/goods')
		->defaults(array('controller' => 'admin_json', 'action' => 'goods'));

	Route::set('json_groups', 'od-men/json/groups')
		->defaults(array('controller' => 'admin_json', 'action' => 'groups'));
	Route::set('json_promo_goods', 'od-men/json/promo_goods')
		->defaults(array('controller' => 'admin_json', 'action' => 'promo_goods'));
	*/

	Route::set('admin_json_bind', 'od-men/json/bind/<model>/<id>/<alias>', array('model' => '[a-z_]+', 'id' => '\d+', 'alias' => '[a-z_]+'))
		->defaults(array('controller' => 'admin_json', 'action' => 'bind'));

	Route::set('admin_json_unbind', 'od-men,json/unbind/<model>/<id>/<alias>', array('model' => '[a-z_]+', 'id' => '\d+', 'alias' => '[a-z_]+'))
		->defaults(array('controller' => 'admin_json', 'action' => 'unbind'));

	Route::set('admin_json', 'od-men/json/<action>')
		->defaults(array('controller' => 'admin_json', 'action' => 'index'));

	Route::set('admin_medialib', 'od-men/medialib')
		->defaults(array('controller' => 'admin', 'action' => 'medialib'));

	Route::set('admin_group_ajax_get', 'od-men/group_ajax_get')
		->defaults(array('controller' => 'admin', 'action' => 'group_ajax_get'));

	Route::set('admin_good_sert_ajax_search', 'od-men/good_sert_ajax_search')
		->defaults(array('controller' => 'admin', 'action' => 'good_sert_ajax_search'));

	Route::set('admin_upload', 'od-men/image')
		->defaults(array('controller' => 'admin', 'action' => 'image'));

	Route::set('admin_goods', 'od-men/goods')
		->defaults(array('controller' => 'admin', 'action' => 'goods'));

	Route::set('admin_chosen', 'od-men/chosen')
		->defaults(array('controller' => 'admin', 'action' => 'chosen'));

	Route::set('admin_mail', 'od-men/mail')
		->defaults(array('controller' => 'admin', 'action' => 'mail'));

	Route::set('admin_spam_stat', 'od-men/spam_stat')
		->defaults(array('controller' => 'admin', 'action' => 'spam_stat'));

	Route::set('admin_list', 'od-men/<model>', array('model' => '[a-z_]+'))
		->defaults(array('controller' => 'admin', 'action' => 'list'));

	Route::set('admin_add', 'od-men/<model>/add', array('model' => '[a-z_]+'))
		->defaults(array('controller' => 'admin', 'action' => 'add'));

	Route::set('admin_sert_unbind', 'od-men/sert_unbind/<id>', array('id' => '\d+'))
		->defaults(array('controller' => 'admin', 'action' => 'sert_unbind'));

	Route::set('admin_bind', 'od-men/bind/<model>/<id>/<alias>/<far_key>', array('model' => '[a-z_]+', 'id' => '\d+', 'alias' => '[a-z_]+', 'far_key' => '\d+'))
		->defaults(array('controller' => 'admin', 'action' => 'bind'));

	Route::set('admin_unbind', 'od-men/unbind/<model>/<id>/<alias>/<far_key>', array('model' => '[a-z_]+', 'id' => '\d+', 'alias' => '[a-z_]+', 'far_key' => '\d+'))
		->defaults(array('controller' => 'admin', 'action' => 'unbind'));
	/*
	Route::set('admin_sert_edit', 'od-men/sert/<id>', array('id' => '\d+'))
		->defaults(array('controller' => 'admin', 'action' => 'sert_edit'));
	*/
	Route::set('admin_edit', 'od-men/<model>/<id>', array('model' => '[a-z_]+', 'id' => '\d+'))
		->defaults(array('controller' => 'admin', 'action' => 'edit'));

	Route::set('admin_del', 'od-men/<model>/<id>/del', array('model' => '[a-z_]+', 'id' => '\d+'))
		->defaults(array('controller' => 'admin', 'action' => 'del'));

	Route::set('admin_good', 'od-men/good/<id>', array('id' => '\d+'))
		->defaults(array('controller' => 'admin', 'action' => 'good'));

	// ажакс загрузка опций доставки
	Route::set('delivery_zone', 'delivery/zone')
		->defaults(array('controller' => 'ajax', 'action' => 'zone')); // загрузка данных зоны доставки (интервалы, адреса, настройки)

	Route::set('delivery_time', 'delivery/time')
		->defaults(array('controller' => 'ajax', 'action' => 'time')); // загрузка данных времени доставки по дате

        Route::set('delivery_ozon', 'delivery/ozon')
		->defaults(array('controller' => 'ajax', 'action' => 'ozon_price')); // загрузка данных времени доставки по дате

	// ажакс загрузки вариантов голосования
	Route::set('poll_variants', 'poll/variants/<id>', array('id' => '\d+',))
		->defaults(array('controller' => 'ajax', 'action' => 'poll_variants'));

	// статика
	Route::set('yml', 'export/yml.xml')
		->defaults(array('controller' => 'page', 'action' => 'yml'));

    Route::set('wikimart', 'export/wikimart.xml')
		->defaults(array('controller' => 'page', 'action' => 'wikimart_yml'));

	Route::set('findologic_yml', 'export/findologic_yml.xml')
		->defaults(array('controller' => 'page', 'action' => 'findologic_yml'));

	Route::set('google', 'export/google.xml')
		->defaults(array('controller' => 'page', 'action' => 'google'));

	Route::set('mailru', 'export/mailru.xml')
		->defaults(array('controller' => 'page', 'action' => 'mailru_xml'));

	Route::set('ozon_yml', 'export/ozon_yml.xml')
		->defaults(array('controller' => 'page', 'action' => 'ozon_yml'));

	Route::set('security_error', 'security-error')
		->defaults(array('controller' => 'page', 'action' => 'security_error'));

	Route::set('ci', 'ci')
		->defaults(array('controller' => 'page', 'action' => 'ci'));

	Route::set('mango', 'mango')
		->defaults(array('controller' => 'page', 'action' => 'mango'));

	Route::set('clear', 'clear')
		->defaults(array('controller' => 'page', 'action' => 'clear'));

	Route::set('test', 'test')
		->defaults(array('controller' => 'page', 'action' => 'test'));

	Route::set('index', '(<index>)', array('index' => '(index\.php|)'))
		->defaults(array('controller' => 'page', 'action' => 'index'));

	// для Астры
	Route::set('astra_request', 'astra/request.php')
		->defaults(array('controller' => 'page', 'action' => 'astra_request'));
	// для Астры - маршруты готовы
	Route::set('astra_routes_ready', 'astra/routes_ready.php')
		->defaults(array('controller' => 'page', 'action' => 'astra_routes_ready'));

    // тест 1с
	Route::set('astra_1ctest', 'od-test/test1c.php')
		->defaults(array('controller' => 'page', 'action' => 'test1c'));

	// CLI
	Route::set('daemon', 'daemon/imp')
		->defaults(array('controller' => 'daemon', 'action' => 'index'));

	Route::set('page', '<static>', array('static' => '.*'))
		->defaults(array('controller' => 'page', 'action' => 'view'));

} else {

	Route::set('index', '<index>', array('index' => '(index\.php|)'))
		->defaults(array('controller' => 'mobile', 'action' => 'index'));

	Route::set('section', 'catalog/<translit>', array(
		'translit' => '[a-z0-9_-]+',
	))->defaults(array('controller' => 'mobile', 'action' => 'section'));

}

/* основные настройки */
Cookie::$salt = 'Каждый охотник желает знать где сидит фазан';
Cookie::$expiration = 3600 * 24 * 365; // куку кладём на год

Cache::$default = 'memcache'; // кэшируем в мемкеше

set_exception_handler(array('Error', 'handler'));

/* To prevent CLI errors */
if ( ! empty($_SERVER['REQUEST_URI'])) {

    Kohana_Session::$default = 'db';
    $conf = Kohana::$config->load('session')->get(Kohana_Session::$default);

    // Cookie across domains.
    header('P3P: CP="NOI ADM DEV PSAi COM NAV OUR OTRo STP IND DEM"');

    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Credentials: true");

    $domains = Kohana::$config->load('domains')->as_array(); // = Kohana::$config->load('domains')->as_array();

    header("Access-Control-Allow-Origin: http://" . current($domains)['host']);
    header("Access-Control-Allow-Credentials: true");

    if (parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) == '/sync') { // синзронизация

        if ( ! empty($_GET['clear'])) { // удаление куки

            Cookie::delete($_GET['clear']);
            exit($_GET['clear']);

        } elseif ($_GET['hash'] == md5($_GET['mladenec'] . Cookie::$salt)) { // синхронизация сессии

            Cookie::set($conf['name'], $_GET['mladenec'], $conf['lifetime']);
            exit($_GET['mladenec']);
        }
    }

    $contentPolicy = Cache::instance()->get('content_policy');

    if (empty($contentPolicy)) {

        $hosts = Kohana::$config->load('domains')->as_array();

        $hh = [];
        foreach ($hosts as $h) $hh[] = (strpos($h['host'], '.') !== FALSE) ? '*.' . $h['host'] : 'http://'.$h['host'];

		$hh[] = "*.mladenec-shop.ru"; // mladenec main
		$hh[] = "*.mladenec.ru"; // mladenec static

		$hh[] = "*.jivosite.com"; // jivosite
		$hh[] = "*.amazonaws.com";
        $hh[] = "wss://*.jivosite.com";

		$hh[] = "*.retailrocket.ru"; // retailrocket

        $hh[] = "*.wapstart.ru"; // counters & trackers
        $hh[] = "https://*.doubleclick.net";
        $hh[] = "*.doubleclick.net";
		$hh[] = "*.adfox.ru";
		$hh[] = "*.yadro.ru";
		$hh[] = "top-fwz1.mail.ru";
		$hh[] = "https://cts-secure.channelintelligence.com"; // pampers + nutricia

		$hh[] = "*.vk.com"; // socials and youtube
		$hh[] = "https://*.vk.com";
		$hh[] = "*.facebook.net";
		$hh[] = "*.facebook.com";
		$hh[] = "https://*.facebook.com";
		$hh[] = "*.youtube.com";

		$hh[] = "*.google.ru";  // google & services
		$hh[] = "https://*.google.ru";
		$hh[] = "*.google.com";
		$hh[] = "https://*.google.com";
		$hh[] = "*.gstatic.com";
		$hh[] = "*.googleadservices.com";
		$hh[] = "*.google-analytics.com";
		$hh[] = "*.googletagmanager.com";
		$hh[] = "*.googleapis.com";
        $hh[] = "*.trmit.com"; // retag cdn

        $hh[] = "*.yandex.ru"; // yandex
		$hh[] = "*.yandex.net";
		$hh[] = "https://*.yandex.ru";
		$hh[] = "https://*.yandex.net";

		$hh[] = "*.asbmit.com"; // admitad
		$hh[] = "ad.admitad.com";
        $hh[] = "*.veinteractive.com";
		$hh[] = "a.marketgid.com";
		$hh[] = "ad.adriver.ru";
		$hh[] = "cdn.dumedia.ru";
		$hh[] = "ads.heias.com";
		$hh[] = "*.audiencemanager.de";
		$hh[] = "https://adsclever.ru";
		$hh[] = "cdn.admail.am";
		$hh[] = "track.dumedia.ru";
		$hh[] = "display.intencysrv.com";
		$hh[] = "content.adriver.ru";
		$hh[] = "https://p.tpm.pw";
		$hh[] = "p.tpm.pw";
        $hh[] = "www.mainadv.com";
        $hh[] = "https://www.mainadv.com";
        $hh[] = "uaadcodedsp.rontar.com";

		$hh[] = "https://ulogin.ru"; // ulogin
        $hh[] = "ulogin.ru";
        $hh[] = "x.ulogix.ru";

        $hh[] = "static-trackers.adtarget.me"; // google remarketing
        $hh[] = "https://trackers.adtarget.me";
        $hh[] = "https://engine.adclick.lt:8081";
        $hh[] = "https://engine.adclick.lv:8081";
        $hh[] = "https://ib.adnxs.com";
        $hh[] = "https://adsearch.adkontekst.pl";
        $hh[] = "https://r3.c8.net.ua";

        $hh[] = "https://cdn.findologic.com"; // поиск от findologic
        $hh[] = "http://service.findologic.com";
        $hh[] = "https://secure.findologic.com";
        $hh[] = "http://tracking.findologic.com";

		$hh[] = "http://code.directadvert.ru"; // пиксель

		$contentPolicy =
			"Content-Security-Policy: default-src 'self' 'unsafe-inline' 'unsafe-eval' blob: data: ".implode(' ', $hh)
			.";report-uri /security-error";

        $contentPolicy = preg_replace('#(//.*?)?\n#ius', '', $contentPolicy);

        Cache::instance()->set('content_policy', $contentPolicy);
    }

    header($contentPolicy);
}
