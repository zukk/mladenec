<?php

class Controller_Page extends Controller_Frontend {

    public $menu = TRUE;

    public function after()
    {
        // some pages still have no menu
        if ($this->menu) $this->layout->menu = Model_Menu::html();
        parent::after();
    }

    public function action_test()
    {
        //$wiki_categories = Model_Wikicategories::getwikicategories();
        //print_r($wiki_categories);
        //die();

        /*$wiki_categories = ORM::factory('wikicategories')->find_all();

        $cat = array();
        foreach($wiki_categories as $key => $categories){
            $cat[$key]['name'] = $categories->name;
            $cat[$key]['category_id'] = $categories->category_id;
            $cat[$key]['parent_id'] = $categories->parent_id;
        }

        View::factory('smarty:admin/section/form', ['wiki_categories'=>$cat])->render();*/


        /*$xmlURL = APPPATH . "classes".DS."controller".DS."xml_feed.xml";
        $sxml = simplexml_load_file($xmlURL);

        foreach($sxml->categories as $category) {
            foreach($category as $cat) {
                $category_id = stripslashes($cat->attributes()['id']);
                $parent_id = stripslashes($cat->attributes()['parent_id']);
                $name = stripslashes((string)$cat->name);

                $wiki_categories = ORM::factory('wiki_categories');
                $wiki_categories->category_id = $category_id;
                $wiki_categories->parent_id = $parent_id;
                $wiki_categories->name = $name;
                $wiki_categories->save();
            }
        }*/




        /*$start_memory = memory_get_usage();
        $lock_file = APPPATH.'cache/wikimart_yml_on';

        if (file_exists($lock_file)) exit('Already running, lock file found at '.$lock_file);
        touch($lock_file);

        $filename = APPPATH . 'cache/wikimart_yml.xml';
        //$heap_size = 1000; // Сколько товаров писать в файл за 1 раз
        $heap_size = 10; // Сколько товаров писать в файл за 1 раз

        $fp = fopen($filename, 'w');

        fwrite($fp, '<?xml version="1.0" encoding="utf-8"?>
        <!DOCTYPE yml_catalog SYSTEM "shops.dtd">
        <yml_catalog date="' . date('Y-m-d H:i') . '">
            <shop>
                <name>ООО &quot;TД Младенец.РУ&quot;</name>
                <company>Младенец.РУ</company>
                <url>http://www.mladenec-shop.ru/</url>
                <currencies><currency id="RUR" rate="1" plus="0"/></currencies>
                <categories>');

                $catalog = Model_Section::get_catalog();
                $id2Catalog = [];

                foreach($catalog as $item) {
                    $id2Catalog[$item->id] = $item;
                    if ( ! empty($item->children)) {
                        foreach($item->children as $child) {
                            $id2Catalog[$child->id] = $child;
                        }
                    }
                }

                fwrite($fp, View::factory('smarty:page/export/yml/categories', ['catalog' => $catalog]));
                fwrite($fp, View::factory('smarty:page/export/wikimart/categories', ['catalog' => $catalog]));

                fwrite($fp, '</categories><local_delivery_cost>350</local_delivery_cost><offers>');
                $goods_written = 0;

                define('EXPORTXML_SEX', 1951);
                define('EXPORTXML_COLOR', 1952);
                define('EXPORTXML_SIZE', 1949);
                define('EXPORTXML_GROWTH', 1950);

                $goodFilters = [
                    Model_Section::EXPORTYML_CLOTHERS => [
                        EXPORTXML_GROWTH	=> 'Рост',
                        EXPORTXML_SIZE		=> 'Размер',
                        EXPORTXML_COLOR		=> 'Цвет',
                        EXPORTXML_SEX		=> 'Пол'
                    ]
                ];

                $filterClosures = [

                    EXPORTXML_SEX => function($name) {
                        return ['name' => preg_match('#^девочка$#iu', $name) ? 'Женский' : 'Мужской'];
                    },

                    EXPORTXML_GROWTH => function($name) {
                        if ( ! preg_match('#^([0-9\- ]+(см|м))$#iu', $name, $matches)) return FALSE;
                        return [
                            'name' => (int)$matches[1],
                            'unit' => $matches[2]
                        ];
                    },

                    EXPORTXML_SIZE => function($name) {

                        if( ! preg_match('#^([0-9\- ]+)$#iu', $name, $matches)) return FALSE;
                        return [
                            'name' => (int)$matches[1],
                            'unit' => 'RU'
                        ];
                    },

                    EXPORTXML_COLOR => function($name) {
                        return ['name' => mb_convert_case($name, MB_CASE_TITLE)];
                    },
                ];

                $goodFiltersLabels = [];
                foreach($goodFilters as $type => $filters) {
                    foreach($filters as $id => $label) {
                        $goodFiltersLabels[$id] = $label;
                    }
                }

                $goodFiltersIds = [];
                $image_types = '500';
                for ($heap_number = 0, $goods = Model_Good::for_yml($heap_size, $heap_number); $heap_number < $heap_size; $heap_number++) {
                    $c = 0;
                    $good_ids = [];
                    foreach ($goods as &$g) {
                        if ($id2Catalog[$g['section_id']]->parent_id > 0) {
                            $section = $id2Catalog[$id2Catalog[$g['section_id']]->parent_id];
                        } else {
                            $section = $id2Catalog[$g['section_id']];
                        }

                        $g['real_section'] = $section->id;

                        //if ($section->is_cloth()) {
                            $goodFiltersIds[1][] = $g['id'];
                        //}
                        $good_ids[] = $g['id'];
                    }

                    $goodFiltersV = [];
                    if ( ! empty($goodFiltersIds)) {
                        foreach ($goodFiltersIds as $filterType => $ids) {

                            $filtersIds = array_keys($goodFilters[$filterType]);

                            $result = DB::select('value_id', 'good_id', 'filter_id')
                                ->from('z_good_filter')
                                ->where('filter_id', 'IN', $filtersIds)
                                ->where('good_id', 'IN', $ids)
                                ->execute();

                            $filterValuesIds = [];
                            while ($row = $result->current()) {
                                $filterValuesIds[$row['value_id']] = 1;
                                $result->next();
                                $goodFiltersV[$row['good_id']][$row['filter_id']][] = $row['value_id'];
                            }
                        }
                    }

                    $filterValues = [];
                    if ( ! empty($filterValuesIds)) {

                        $filterValuesIds = array_keys( $filterValuesIds );

                        $result = DB::select('name', 'id')
                            ->from('z_filter_value')
                            ->where('id', 'IN', $filterValuesIds)
                            ->execute();

                        while ($row = $result->current()) {
                            $filterValues[$row['id']] = $row['name'];
                            $result->next();
                        }
                    }

                    $images = Model_Good::many_images([$image_types], $good_ids);
                    foreach($goods as &$g) { // тут передаем по ссылке, иначе послдний элемент дублируется
                        // Если одновременно мальчик-девочка, то пол не передаем
                        if ( ! empty($goodFiltersV[$g['id']][EXPORTXML_SEX]) && count($goodFiltersV[$g['id']][EXPORTXML_SEX]) > 1) {
                            unset($goodFiltersV[$g['id']][EXPORTXML_SEX]);
                        }


                        if ( ! empty($goodFiltersV[$g['id']])) {
                            foreach($goodFiltersV[$g['id']] as $filter_id => $valuesIds) {
                                foreach($valuesIds as $key => $valueId) {
                                    $rr = $filterClosures[$filter_id]($filterValues[$valueId]);
                                    if ($rr !== FALSE) $valuesIds[$key] = $rr;
                                    break; // Яндекс примет только первое значение
                                }
                            }
                        }
                        //подготовка изображений
                        $good_images = [];
                        if (isset($images[$g['id']][$image_types]) && count($images[$g['id']][$image_types]) > 0) {
                            //загрузка только 1 фото на товар
                            $good_images[] = array_pop($images[$g['id']][$image_types]);

                        } elseif ( ! empty($g['img1600'])) {
                            $good_images[] = ORM::factory('file', $g['img1600']); // если нет картинок никаких, добавим 1600 - но она с вотермаркой
                        }

                        fwrite($fp, View::factory('smarty:page/export/wikimart/good', [
                            'g'             => $g,
                            'images'        => $good_images,
                            'section'       => $id2Catalog[$g['real_section']],
                            'filter_labels' => $goodFiltersLabels,
                            'good_filter'   => ! empty($goodFiltersV[$g['id']]) ? $goodFiltersV[$g['id']] : [],
                            'label'         => 'wikimart.ru'
                        ]));
                        $goods_written++;
                    }

                    gc_collect_cycles();
                }

                fwrite($fp,'</offers>
        </shop>
        </yml_catalog>
        ');

        fclose($fp);

        exec('gzip -c '.$filename.' > '.$filename.'.gz'); // делаем gzip
        unlink($lock_file);
        $memory = memory_get_usage() - $start_memory;
        Log::instance()->add(Log::INFO, 'Wikimart XML file generated ok. Memory used: ' . $memory . '. Heap size: ' .
            $heap_size . '. Exported ' . $goods_written . ' offers.');*/
    }


    /**
     * Зачистка откомпиленных смарти-шаблонов и кеша
     */
    public function action_clear()
    {
        $this->menu = FALSE;

        Model_Good::refresh();
        
        Cache::instance()->delete_all();
        
        $d = dir(APPPATH.'cache/smarty_compiled');

        while (FALSE !== ($entry = $d->read())) {
            if (strpos($entry, '.tpl.php') != FALSE) {
                echo $d->path.'/'.$entry.'<br />';
                var_dump(unlink($d->path.'/'.$entry));
            }
        }
        exit();
    }
    /**
     * Тестирование 1С
     */
    public function action_test1c()
    {
        $this->menu = FALSE;
        $body = View::factory('smarty:page/test1c')->render();
        //header('Content-Type: text/html; charset=windows-1251');
        exit($body);       
    }
    
    /**
     * Запросы от GetResponse
     */
    public function action_getresponse()
    {
        switch ($this->request->query('action')) {
            
            case 'unsubscribe':
                $email = $this->request->query('contact_email');
                Model_User::unsubscribe($email);
                break;
            
            case 'open':
                // открыл письмо
                break;
        }
        
        exit('ok');       
    }
	
    public function action_security_error()
    {
		$input = json_decode( file_get_contents("php://input"), true );
		$data = &$input['csp-report'];
		
		DB::insert('z_security_errors')
            ->columns(array('document_uri', 'referrer', 'violated_directive', 'original_policy', 'blocked_uri '))
            ->values(array($data['document-uri'], $data['referrer'], $data['violated-directive'], $data['original-policy'], $data['blocked-uri']));
		exit;
    }
	
	public function action_script()
    {
		$content = '';
		foreach($this->scripts as $name) {
			$filename = DOCROOT . '/j/' . $name;
			if(is_file($filename)) $content .= file_get_contents($filename);
		}
        file_put_contents(DOCROOT . '/j/' . 'script.' . $this->request->param('revision') . '.jsgz', gzencode($content));
		
		header('Last-Modified: '. gmdate("D, d M Y H:i:s \G\M\T", time()));
		header('Content-encoding: gzip');
		header('Content-Type:application/x-javascript; charset=utf-8');
		
		exit($content);
	}

	public function action_css()
    {
		$content = '';
		foreach($this->css as $name) {
			$filename = DOCROOT . $name;
			if(is_file($filename)) $content .= file_get_contents($filename);
		}
		file_put_contents(DOCROOT . '/c/' . 'style.' . $this->request->param('revision') . '.cssgz', gzencode($content));

		header('Last-Modified: '. gmdate("D, d M Y H:i:s \G\M\T", time()));
		header('Content-encoding: gzip');
		header('Content-Type:text/css; charset=utf-8');

		exit($content);
	}
	
    /**
     * Главная страница
     * Разная логика для разных витрин
     */
    public function action_index()
    {
        if ($this->request->param('index')) $this->request->redirect('/', 301);

		$this->tmpl['comments'] = ORM::factory('comment_theme')->getLast(2);
			
        $this->menu = FALSE;

        Kohana::$server_name = 'mladenec';

        $vitrina = Kohana::$server_name;

        if (Kohana::$server_name == 'ogurchik') {

            $this->layout->title = 'Продукты на дом. Заказ и доставка продуктов по Москве и Московской области.';

            // 3 новости
            $this->tmpl['news'] = ORM::factory('new')
                ->with('image')
                ->where('active', '=', 1)
                ->where('date', '<=', date('Y-m-d'))
                ->order_by('date', 'desc')
                ->limit(3)
                ->find_all();

            // 4 aкции
            $this->tmpl['actions'] = ORM::factory('action')
                ->where('active', '=', 1)
                ->where('show', '=', 1)
                ->where_open()
                ->where('vitrina_show', '=', 'all')
                ->or_where('vitrina_show', '=', $vitrina)
                ->where_close()
                ->where('main', '=', 1)
                ->order_by('id', 'desc')
                ->limit(4)
                ->find_all();

        } elseif (Kohana::$server_name == 'kotdog') {

            $this->menu = TRUE;

        } else { // главная страница младенца

            // 1 новость
            $this->tmpl['new'] = ORM::factory('new')
                ->with('image')
                ->where('active', '=', 1)
                ->where('date', '<=', date('Y-m-d'))
                ->order_by('date', 'desc')
                ->limit(1)
                ->find();

            // 3 статьи
            $this->tmpl['articles'] = ORM::factory('article')
                ->with('minimg')
                ->where('active', '=', 1)
                ->order_by('id', 'desc')
                ->limit(3)
                ->find_all();
            
            // hits {{{
            $hitz_sections    = Model_Good::get_hitz_sections();
            $random_section   = array_rand($hitz_sections);
            $section_line     = array_merge($hitz_sections, $hitz_sections, $hitz_sections); // 3 раза
            
            // ищем в середине нашу секцию
            $i = $sections_count = count($hitz_sections);;
            
            while ($section_line[$i]['id'] != $random_section) $i++;

            $this->tmpl['hitz'] = View::factory('smarty:hitz/view', array(
                'goods'    => Request::factory(Route::url('ajax_hitz_section', ['section_id' => $random_section]))->execute(),
                'sections' => array_slice($section_line, $i - 2, $sections_count) // берём кусок со всеми категориями
            ));
            // hits }}}

            $this->layout->title = 'Младенец.ру — интернет магазин детских товаров, питания, игрушек и других';
            $this->layout->description = 'Товары для детей по выгодным ценам – интернет магазин Младенец.ру. Покупайте игрушки, детское питание, подгузники, детский транспорт и многое другое с доставкой по Москве, Санкт-Петербургу и другим городам России.';
            $this->layout->keywords = 'детские товары, товары для детей, товары для ребенка, детский интернет магазин, младенец ру, магазин младенец, москва, купить товары для детей, продажа детских товаров';
        }

        // цены для любимых клиентов
        if ( ! empty($good_ids)) $this->tmpl['price']= Model_Good::get_status_price(1, $good_ids);
        // картинки для товаров - одним запросом
        if ( ! empty($img_ids)) {
            $this->tmpl['images'] = $imgs = ORM::factory('file')->where('id', 'IN', $img_ids)->find_all()->as_array('ID');
        }

        // слайдер на главной
        switch($vitrina) {
            case 'mladenec':
                $slider_id = Model_Slider_Banner::SLIDER_MLADENEC_INDEX;
                break;
            case 'ogurchik':
                $slider_id = Model_Slider_Banner::SLIDER_EATMART_INDEX;
                break;
            default:
                $slider_id = Model_Slider_Banner::SLIDER_MLADENEC_INDEX;
        }
        
        $active_action_ids = DB::select('id')->from('z_action')->where('active','=',1)->execute()->as_array('id','id');
        $active_action_ids[] = 0; // not binded to actions too

        $this->tmpl['slider'] = ORM::factory('slider_banner')
            ->where('active', '=',  1)
            ->where('slider_id', '=',  $slider_id)
            ->where('action_id', 'IN', $active_action_ids)
            ->where_open()
                ->where('from', 'IS', NULL)
                ->or_where('from', '<=', date('Y-m-d G:i:00'))
            ->where_close()
            ->where_open()
                ->where('to', 'IS', NULL)
                ->or_where('to', '>=', date('Y-m-d G:i:00'))
            ->where_close()
            ->order_by('order','ASC')
            ->find_all()
            ->as_array();

        $this->layout->body = View::factory('smarty:index/'.Kohana::$server_name, $this->tmpl);
    }

    /**
     * Просмотр статической страницы
     * @throws HTTP_Exception_404
     */
    public function action_view()
    {
        $link = $this->request->param('static');
        if ($link == 'news') $this->request->redirect(Route::url('news'), 301);

        $page = ORM::factory('menu')->where('link', '=', $link)->find();
        if ( ! $page->id) throw new HTTP_Exception_404;
        if ( ! $page->show) throw new HTTP_Exception_404;

        $text = $page->text;

        // если на странице есть форма
        if (preg_match('~(<p class="form([^"]*)" id="([a-z_]+)(\d*)">(.*?)</p>)~isu', $page->text,  $matches)) {

            $a = $matches[3];
            $param = $matches[4];
            $method = 'action_'.$a;

            if (method_exists($this, $method)) {

                if ($this->request->is_ajax()) { // запрос к форме - вернём ажакс

                    return $this->{$method}();

                } else {
                    if ($a == 'delivery') {
                        $this->{$method}($param);
                    } else {
                        $this->{$method}();
                    }

                    $text = str_replace($matches[1], View::factory('smarty:page/'.$a, $this->tmpl + array('class' => $matches[2]))->render(), $text);
                }
            }
        }

        $this->layout->body = $text;
        $this->layout->title = $page->name;
        if ($page->description)  $this->layout->description = $page->description;
    }

    /**
     * Отправка заявки на партнёрство
     */
    public function action_partner_form()
    {
        if ($this->request->post('partner')) {
            
            $p = new Model_Partner();
            $p->values($this->request->post());

            if ( ! empty($this->user)) {
                
                $p->user_id = $this->user->id;
                $p->email = $this->user->email;
                $captcha = TRUE;
                
            } else {
                
                $captcha = Captcha::check($this->request->post('captcha'));
            }

            $file_ok = NULL;
            
            
            if ( ! empty($_FILES['price'])) {
                
                if (Upload::valid($_FILES['price']) AND ($_FILES['price']['size'] < Mail::MAX_ATTACHMENT_SIZE)) {
                    $file_ok = TRUE;
                } else {
                    $file_ok = FALSE;
                }
            }
            
            if (FALSE !== $file_ok AND $p->validation()->check() AND $captcha) {

                $p->save();
                $this->tmpl['p'] = $p;
                
                if ($to = Conf::instance()->mail_partner) {
                    
                    $letter = new Mail();
                    $letter->setHTML($html_text = View::factory('smarty:mail/partner', array('p' => $p,'site'=>Mail::site()))->render());

                    if (TRUE === $file_ok) {
                        Log::instance()->add(Log::INFO, 'Attaching file');
                        $letter->attachUploaded('price');
                    }
                    
                    $letter->send($to, 'Поступила заявка на сотрудничество');
                }
                
            } else {
                $this->tmpl['errors'] = $p->validation()->errors('partner');
                if (FALSE === $file_ok) {
                    $this->tmpl['errors']['price'] = Kohana::message('partner', 'price.big');
                }
                if ( ! $captcha) $errors['captcha'] = Kohana::message('captcha', 'captcha.default');
            }
        }
    }
    
    /**
     * Отправка заявки на конкурс
     */
    public function action_contest()
    {
        if ($this->request->post('contest'))
        {
            $user_id = FALSE;
            $file_ok = NULL;
            $errors = array();
            
            if ( ! empty($this->user))
            {
                $user_id  = $this->user->id;
                $name     = $this->user->name;
                $email    = $this->user->email;
                $captcha  = TRUE;
                
            }
            else
            {
                $email   = trim($this->request->post('email'));
                $name    = htmlentities(trim($this->request->post('name')),ENT_IGNORE,'UTF-8');
                $captcha = Captcha::check($this->request->post('captcha'));
            }
            
            $this->tmpl['email'] = $email;
            $text = htmlentities(trim($this->request->post('text')),ENT_IGNORE,'UTF-8');

            if ( ! empty($_FILES['price']))
            {
                if (Upload::valid($_FILES['price']) AND ($_FILES['price']['size'] < Mail::MAX_ATTACHMENT_SIZE)) {
                    $file_ok = TRUE;
                } else {
                    $file_ok = FALSE;
                }
            }
            
            if (empty($name))    $errors['name'] = Kohana::message ('contest', 'name.not_empty');
            if ( ! empty($email))
            {
                if ( ! Valid::email($email)) $errors['email'] = Kohana::message ('contest', 'email.email');
            }
            else
            {
                $errors['email'] = Kohana::message ('contest', 'email.not_empty');
            }
            if (empty($text))    $errors['text']    = Kohana::message ('contest', 'text.not_empty');
            if (empty($captcha)) $errors['captcha'] = Kohana::message ('captcha', 'captcha.default');
            if ( ! $file_ok)     $errors['file']    = Kohana::message ('contest', 'file.upload_error');
            
            if ( empty($errors) AND ($to = Conf::instance()->mail_contest))
            {
                $letter = new Mail();
                $letter->setHTML(View::factory('smarty:mail/contest', array(
                    'user_id'   => $user_id,
                    'name'      => $name,
                    'email'     => $email,
                    'text'      => $text,
                    'site'      => Mail::site()
                        ))->render());

                if (TRUE === $file_ok) 
                {
                    Log::instance()->add(Log::INFO, 'Attaching file');
                    $letter->attachUploaded('price');
                }

                $letter->send($to, 'Работа на конкурс');
                $this->tmpl['sent'] = TRUE;
            } 
            else
            {
                $this->tmpl['errors'] = $errors;
            }
        }
    }

    /**
     * Отправка претензии
     */
    public function action_return()
    {
        if ($this->request->post('return')) {
        
			$p = new Model_Return();
			$p->values($this->request->post());
			if ($this->user) $p->user_id = $this->user->id;

			$captcha = $this->user ? TRUE : Captcha::check($this->request->post('captcha'));

			if ($p->validation()->check() AND $captcha) {
				if ( ! empty($_FILES['img']) && Upload::valid($_FILES['img']) && Upload::image($_FILES['img'])) {
					$image = Model_File::image('img');
					$p->img = $image->ID;
				}
				$p->save();
                Mail::htmlsend('return', array('r' => $p), Conf::instance()->mail_return, 'Претензия '.$p->id.': '.$p->name);
				$this->tmpl['p'] = $p;

			} else {

				$errors = $p->validation()->errors('return');
				if ( ! $captcha) $errors['captcha'] = Kohana::message('captcha', 'captcha.default');
				$this->tmpl['errors'] = $errors;
			}
		}
    }
    
    /**
     * Отправка отзыва
     */
    public function action_feedback()
    {
        $post = $this->request->post();
        
        if (empty($post['feedback'])) return;
        
        $captcha = $this->user ? TRUE : Captcha::check($this->request->post('captcha'));

        $name       = htmlentities(trim($this->request->post('name')),ENT_IGNORE,'UTF-8');
        $email      = trim($this->request->post('email'));
        $text       = htmlentities(trim($this->request->post('text')),ENT_IGNORE,'UTF-8');
        $phone      = FALSE;
        $user_id    = FALSE;

        if ($captcha) {
            $feedback_valid = Validation::factory($post);
            $feedback_valid->rules(
                'email', array(
                        array('not_empty'),
                        array('max_length', array(':value', 254)),
                        array('min_length', array(':value', 3)),
                        array('email')
                ),
                'name', array(
                        array('not_empty'),
                        array('max_length', array(':value', 254)),
                        array('min_length', array(':value', 3))
                ),
                'text', array(
                        array('not_empty'),
                        array('max_length', array(':value', 2048)),
                        array('min_length', array(':value', 3))
                )
            );

            if ($this->user)
            {
                $phone      = $this->user->phone;
                $user_id    = $this->user->id;
            }

        } 

        if ($feedback_valid->check() AND $captcha) {

            Mail::htmlsend(
                'feedback',
                array(
                    'f' => array(
                        'name'      => $name,
                        'email'     => $email,
                        'phone'     => $phone,
                        'user_id'   => $user_id,
                        'text'      => $text
                    )
                ),
                Conf::instance()->mail_feedback,
                'Обратная связь от ' . $name);
            $this->tmpl['p'] = TRUE;

        } else {

            $errors = $feedback_valid->errors('return');
            if ( ! $captcha) $errors['captcha'] = Kohana::message('captcha', 'captcha.default');
            $this->tmpl['errors'] = $errors;
        }
        
    }


    /**
     * Выдача капчи
     */
    public function action_captcha()
    {
        $this->menu = FALSE;
        new Captcha();
    }

    /**
     * Статьи
     * @throws HTTP_Exception_404
     * @return void
     */
    public function action_article()
    {
        $old_link = $this->request->param('old_link');
        $id = $this->request->param('id');

        if ($old_link) { // редирект со старых ссыло к на новые
            if ($id) {
                $this->request->redirect(Route::url('article', ['id' => $id]), 301);
            } else {
                $page = $this->request->query('page');
                $this->request->redirect(Route::url('article').($page ? '?page='.$page : ''), 301);
            }
        }

        $q = ORM::factory('article')->where('active', '=', 1)->reset(FALSE);

        $description = 'Читайте наши статьи по уходу за ребенком и о поддержании его здоровья.';
        $title = 'Полезные статьи по уходу за детьми от Младенец.РУ';

        if (empty($id)) { // show list

            $this->tmpl['pager'] = $pager = new Pager($q->count_all(), 10);

			$this->tmpl['articles'] = $q
                ->order_by('id', 'desc')
                ->limit($pager->per_page)
                ->offset($pager->offset)
                ->with('minimg')
                ->find_all();

            if ($pager->p > 1) {
                $title = 'Страница ' . $pager->p . '. ' . $title;
                $description = 'Страница ' . $pager->p . '. ' . $description;
            }

        } else {
			
			$article = $q->with('image')->where('article.id', '=', $id)->find();
			if ( ! $article->loaded()) throw new HTTP_Exception_404;

			$this->tmpl['article'] = $article;

            if ( ! empty($article->seo->title)) {
                $title = $article->seo->title;
                $description = $article->seo->description;
            } else {
                $title = $article->name;
            }
		}
		
		$this->layout->title = $title;
		$this->layout->description = $description;
    }

    /**
     * Опросник для посетителей
     */
    public function action_poll()
    {
        $polls_q = ORM::factory('poll')
                ->with('variants')
                ->where('active', '=', 1)
                ->where('type', '!=', Model_Poll::TYPE_REGISTER);

        if ($this->user instanceof Model_User AND $this->user->loaded()) {
            $votes_exists = Model_Poll::votes($this->user->id);
            $votes_ids = array_keys($votes_exists);
            if ( ! empty($votes_ids)) {
                $polls_q->where('id', 'NOT IN', $votes_ids);
            }
        }
        $this->tmpl['polls'] = $polls = $polls_q->find_all();

        if ( ! empty($this->user->id)) {
            $this->tmpl['votes'] = Model_Poll::votes($this->user->id);
        }

        if ($this->request->post('poll_id')) { // Пришел ответ пользователя
            $poll = new Model_Poll($this->request->post('poll_id'));
            if ( ! $poll->loaded()) throw new HTTP_Exception_404;

            $ok = $poll->vote_handler($this->user->id);

            if ($ok) {
                $view = View::factory('smarty:page/poll/ok', array('p' => $poll));
                if ($poll->type == Model_Poll::TYPE_COUPON) { // этот опрос даёт купон
                    $view->coupon = Model_Coupon::generate($poll->coupon);
                }
                $this->return_html($view->render());
            } else {
                throw new HTTP_Exception_403;
            }
        }
    }

    /**
     * Карта теговых страниц
     */
    public function action_tag()
    {
        $tags = ORM::factory('tag')->find_all();
        $tag_by_section = array();

        foreach($tags as $t) {
            if (empty($tag_by_section[$t->tree_id])) $tag_by_section[$t->tree_id] = array();
            $tag_by_section[$t->tree_id][] = $t;
        }

        $this->tmpl['tree'] = Model_Tag::get_tree();
        $this->tmpl['tag_by_section'] = $tag_by_section;
        $this->menu = FALSE;
    }

    /**
     * 
     */
    public function action_astra_request() {
        
        $request_file = APPPATH.'cache/astra_request_on';
        if (file_exists($request_file)) {
            unlink($request_file);
        }
        
        $key = $this->request->query('key');
        if (empty($key)) {
            $key = 'ApiKey';
        }
        $name = $this->request->query('name');
        if (empty($name)) {
            if (Kohana::$environment === Kohana::DEVELOPMENT) $name = 'astra-test';
            else $name = 'astrapro';
        }
        $port = $this->request->query('port');
        if (empty($port)) $port = '9090';
        
        $date = $this->request->query('date');
        if (empty($date)) {
            $date = date('Y-m-d');
        }
        
        file_put_contents($request_file, $name . '|' . $port . '|' . $key . '|' . $date);
        
        Astra_Client::params($name, $port, $key);
        
        $generic_client = new Astra_Generic();
        $generic_client->block_client();
        
        exit(View::factory('smarty:page/astra_request',  
                array(
                    'key'=>$key,
                    'name'=>$name,
                    'port'=>$port,
                    'date'=>$date
                ))->render());
    }
    
    /**
     * 
     */
    public function action_astra_routes_ready() {
        
        $request_file = APPPATH.'cache/astra_routes_ready';
        if (file_exists($request_file)) {
            unlink($request_file);
        }
        
        $file = '';
        
        $key = $this->request->query('key');
        if (empty($key)) {
            $key = 'ApiKey';
        }
         $name = $this->request->query('name');
        if (empty($name)) {
            if (Kohana::$environment === Kohana::DEVELOPMENT) $name = 'astra-test';
            else $name = 'astrapro';
        }
        $port = $this->request->query('port');
        if (empty($port)) $port = '9090';
        
        $date = $this->request->query('date');
        if (empty($date)) {
            $date = date('Y-m-d');
        }
        
        file_put_contents($request_file, $name . '|' . $port . '|' . $key . '|' . $date);
        
        Astra_Client::params($name, $port, $key);
        
        $generic_client = new Astra_Generic();
        $generic_client->block_client();
        
        exit(View::factory('smarty:page/astra_routes_ready', 
                array(
                    'key'=>$key,
                    'name'=>$name,
                    'port'=>$port,
                    'date'=>$date
                ))->render());  
    }
    
    /**
     * Показ карты сайта
     */
    public function action_map()
    {
        $this->menu = FALSE;
        if ($this->request->param('id')) {
            $this->tmpl['section'] = $section = new Model_Section($this->request->param('id'));
            if ( ! $section || $section->active == 0) throw new HTTP_Exception_404;

            $this->layout->title = 'Карта раздела &laquo;'.$section->name.'&raquo;';
            $this->layout->allbg = $section->id;

            $this->tmpl['goods'] = $goods = ORM::factory('good')
                ->where('section_id', '=', $section->id)
                ->where('show', '=', 1)
                ->group_by('group_id')
                ->order_by('group_name')
                ->find_all();

        } else {
            $this->tmpl['catalog'] = Model_Section::get_catalog();
            $this->tmpl['menu'] = Model_Menu::html('map_menu');
        }
    }

    /**
     * YML для Яндекса
     */
    public function action_yml()
    {
        $this->menu = FALSE;
        $filename = APPPATH . 'cache/yml.xml';
        $this->return_xml(file_get_contents($filename));
    }

    /**
     * YML для Wikimart
     */
    public function action_wikimart_yml()
    {
        $this->menu = FALSE;
        $filename = APPPATH . 'cache/wikimart_yml.xml';
        $this->return_xml(file_get_contents($filename));
    }

     /**
     * YML для Findologic
     */
    public function action_findologic_yml()
    {
        $this->menu = FALSE;
        $filename = APPPATH . 'cache/findologic_yml.xml';
        $this->return_xml(file_get_contents($filename));
    }

    /**
     * YML для Google
     */
    public function action_google()
    {
        $this->menu = FALSE;
        $filename = APPPATH . 'cache/google.xml';
        $this->return_xml(file_get_contents($filename));
    }

    /**
     * XML для Товары@mail.ru
     */
    public function action_mailru_xml()
    {
        $this->menu = FALSE;
        $filename = APPPATH . 'cache/mailru.xml';
        $this->return_xml(file_get_contents($filename));
    }

    /**
     * YML для Озона
     */
    public function action_ozon_yml()
    {
        $this->menu = FALSE;
        $filename = APPPATH . 'cache/ozon_yml.xml';
        $this->return_xml(file_get_contents($filename));
    }

    /**
     * Получить feed для channel intelligence
     */
    public function action_ci()
    {

        $this->tmpl['goods'] = ORM::factory('good')
            ->where('good.show', '=', '1')
            ->where('good.qty', '>', '0')
            ->where('upc', '>', '')
            ->with('brand')
            ->with('section')
            ->with('prop')
            ->with('prop.image255')
            ->find_all();

        $this->response->headers('Content-Type', 'text/plain; charset=utf-8')->send_headers();
        exit(str_replace('|', "\t", View::factory('smarty:page/ci', $this->tmpl)->render()));
    }

    /**
     * Звонок по манго-телефону, с компьютера
     */
    public function action_mango()
    {
	}


    /**
     * Показ зон и условий доставки
     */
    public function action_delivery($zone_id = NULL)
    {
        if ($zone_id && $zone_id != Model_Zone::ZAMKAD) { // показ конкретной зоны доставки
            $zone = ORM::factory('zone', $zone_id);
            if ( ! $zone->loaded()) throw new HTTP_Exception_404;
            if ( ! $zone->active) throw new HTTP_Exception_404;

            $times = $zone->times->where('active', '=', 1)->find_all()->as_array('id');

            $prices = ORM::factory('zone_time_price')
                ->where('time_id', 'IN', array_keys($times))
                ->order_by('time_id')
                ->order_by('min_sum')
                ->find_all()
                ->as_array('id');

            $tp = $ztp = array();
            foreach($prices as $p) $tp[$p->time_id][$p->id] = $p;
            foreach($times as $t) $ztp[$t->zone_id][$t->id] = $tp[$t->id];

            $this->tmpl['zones'] = array($zone_id => $zone);
            $this->tmpl['times'] = $times;
            $this->tmpl['prices'] = $prices;
            $this->tmpl['ztp'] = $ztp;
            $this->tmpl['active_zone'] = $zone_id;

        } else { // показ всех зон доставки

            $zones = ORM::factory('zone')
                ->select('id', 'name', 'poly', 'color')
                ->where('active', '=', 1)
                ->order_by('priority', 'ASC')
                ->find_all()
                ->as_array('id');

            $times = ORM::factory('zone_time')
                ->where('zone_id', 'IN', array_keys($zones))
                ->where('active', '=', 1)
                ->order_by('sort')
                ->find_all()
                ->as_array('id');

            $prices = ORM::factory('zone_time_price')
                ->where('time_id', 'IN', array_keys($times))
                ->order_by('time_id')
                ->order_by('min_sum')
                ->find_all()
                ->as_array('id');

            $tp = $ztp = array();
            foreach($prices as $p) $tp[$p->time_id][$p->id] = $p;
            foreach($times as $t) {
                if ( ! empty( $tp[$t->id])) {
                    $ztp[$t->zone_id][$t->id] = $tp[$t->id];
                }
            }

            $this->tmpl['zones'] = $zones;
            $this->tmpl['times'] = $times;
            $this->tmpl['prices'] = $prices;
            $this->tmpl['ztp'] = $ztp;
            $this->tmpl['active_zone'] = $zone_id == Model_Zone::ZAMKAD ? Model_Zone::ZAMKAD : Model_Zone::DEFAULT_ZONE;
       }
    }

    /**
     * Определение зоны доставки по адресу (город улица дом)
     */
    public function action_ship()
    {
        // показ таблицы стоимости для всех зон доставки
        $zones = ORM::factory('zone')
            ->select('id', 'name', 'poly', 'color')
            ->where('active', '=', 1)
            ->order_by('priority', 'ASC')
            ->find_all()
            ->as_array('id');

        $times = ORM::factory('zone_time')
            ->where('zone_id', 'IN', array_keys($zones))
            ->where('active', '=', 1)
            ->order_by('sort')
            ->find_all()
            ->as_array('id');

        $prices = ORM::factory('zone_time_price')
            ->where('time_id', 'IN', array_keys($times))
            ->order_by('time_id')
            ->order_by('min_sum')
            ->find_all()
            ->as_array('id');

        $tp = $ztp = array();
        foreach($prices as $p) $tp[$p->time_id][$p->id] = $p;
        foreach($times as $t) $ztp[$t->zone_id][$t->id] = $tp[$t->id];

        $this->tmpl['zones'] = $zones;
        $this->tmpl['times'] = $times;
        $this->tmpl['prices'] = $prices;
        $this->tmpl['ztp'] = $ztp;
    }

    // показ зоны доставки
    protected function show_zone($zone_id)
    {
        $zone = new Model_Zone($zone_id);
        if ( ! $zone->loaded()) throw new HTTP_Exception_404;
        $this->tmpl['poly'] = $zone->poly;
    }

    // страница с анкетой для памперса
    public function action_pampers()
    {
        if ($this->request->post('anketa')) {

            $p = new Model_Pampers();
            $p->values($this->request->post());

            if ($p->validation()->check()) {

		        Mail::htmlsend('admin_pampers', array('o' => $p, 'time' => date('d.m.y H:59:59')), 'contest@new-point.ru,a.melnikov@mladenec.ru', 'анкета памперс');
				
                $p->save();
                $this->tmpl['p'] = TRUE;

            } else {
                $this->tmpl['errors'] = $p->validation()->errors('pampers');
            }
        }
    }

}
