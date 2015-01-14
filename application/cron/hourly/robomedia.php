<?php
/**
 * Отсылка товаров для продвижения в яндексе (через апи с robo-media)
 */

require('../../../www/preload.php');

$lock_file = APPPATH.'cache/robomedia_on';

if (file_exists($lock_file)) exit('Already running, lock file found at '.$lock_file);

touch($lock_file);

$moves = ORM::factory('move')
    ->with('good')
    ->with('good.section')
    ->with('good.brand')
    ->where('done', 'IS', DB::expr('NULL'))
    ->find_all()
    ->as_array('id');

foreach ($moves as $m) {
    switch($m->do) {
        case 'create': // создание товара в рекламке
            if ($m->good->section->parent_id) {
                Robomedia::request('categories', 'POST', array( // категория
                    'name' => $m->good->section->parent->name,
                    'slug' => $m->good->section->parent->id,
                ));
            }

            Robomedia::request('categories', 'POST', array( // категория
                'name' => $m->good->section->name,
                'slug' => $m->good->section_id,
                'parent' => $m->good->section->parent_id,
            ));

            Robomedia::request('classes', 'POST', array( // брэнд
                'name' => $m->good->brand->name,
                'slug' => $m->good->brand_id,
            ));

            Robomedia::request('products', 'POST', array( // товар
                'name' => $m->good->group_name.' '.$m->good->name,
                'url' => Mail::site().$m->good->get_link(0),
                'category_slug' => $m->good->section_id,
                'class_slug' => $m->good->brand_id,
                'article' => $m->good->code,
                'price' => $m->good->price,
            ));

            Robomedia::request('products', 'PUT', array( // товар
                'article' => $m->good->code,
                'active' => ($m->good->show && $m->good->qty != 0) ? 1 : 0,
            ));
            break;

        case 'update':
            Robomedia::request('categories', 'POST', array( // категория
                'name' => $m->good->section->name,
                'slug' => $m->good->section_id,
                'parent' => $m->good->section->parent_id,
            ));

            Robomedia::request('classes', 'POST', array( // брэнд
                'name' => $m->good->brand->name,
                'slug' => $m->good->brand_id,
            ));

            Robomedia::request('products', 'PUT', array( // товар
                'article' => $m->good->code,
                'name' => $m->good->group_name.' '.$m->good->name,
                'url' => Mail::site().$m->good->get_link(0),
                'category_slug' => $m->good->section_id,
                'class_slug' => $m->good->brand_id,
                'active' => ($m->good->show && $m->good->qty != 0) ? 1 : 0,
                'price' => $m->good->price,
            ));

            break;

        case 'delete':
            Robomedia::request('products', 'DELETE', array( // товар
                'article' => $m->good->code,
            ));
            break;

        /*      Работа с признаком наличия товара, пока не используется
                case 'avail':
                    Robomedia::request('marks', 'PUT', array('product' => $m->good->code, 'mark' => 'avail', 'value' => $m->good->qty));
                    break;
        */
    }
    $m->done = date("Y-m-d H:i:s");
    $m->save();
}

unlink($lock_file);
