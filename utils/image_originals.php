<?php

require('../www/preload.php');

ob_flush();
flush();
$n = 0;
// получим все картинки товаров в нужном порядке

do {
    echo $n;

    $imgs = DB::select()
        ->from('z_good_img')
        //->where('good_id', '=', 197919)
        ->order_by('good_id')
        ->order_by('id')
        ->limit(1000)
        ->offset($n)
        ->execute()
        ->as_array();

    $img2size = [];
    foreach ($imgs as $i) { // группируем по товарам
        $img2size[$i['good_id']][$i['file_id']] = $i['size'];
    }

    foreach ($img2size as $good_id => $imgsize) {

        $set = 0;
        echo $good_id . ' = ' . count($imgsize) . "\n";

        $ids = array_keys($imgsize);

        // картинки товара
        $_imgs = ORM::factory('file')
            ->where('ID', 'IN', $ids)
            ->find_all()
            ->as_array('ID');

        // оригиналы картинок
        $_origs = ORM::factory('file')
            ->where('item_id', '=', $good_id)
            ->where('original', '=', 1)
            ->find_all()
            ->as_array('ID');

        echo count($_origs) . " origs\n";

        // для всех оригиналов ищем картинку 1600 (должен быть id = id оригинала + 1)
        $orig_links = [];
        foreach ($_origs as $file) {
            if (!empty($_imgs[$file->ID + 1])) {
                $orig_links[$file->ID + 1] = $file->ID; // есть ссылка на оригинал
                echo 'found original for ' . $file->ID . "\n";
            }
        }

        // размазываем ссылку на оригинал по другим размерам этой же картинки
        // 1. разложить по размерам array( $i => array(70 => ID,255 => ID,1600 => ID), $i+1 => array(70 => ID,255 => ID,1600 => ID))
        $return = $update = [];
        $sizes = ['70' => 0, '255' => 0, '380' => 0, '1600' => 0, '380x560' => 0, '173x255' => 0];
        foreach ($imgsize as $id => $size) {
            //echo $i .'==' . $size ." - ".$id;
            $i = $sizes[$size];
            $return[$i][$size] = $_imgs[$id];
            $sizes[$size]++;
        }
        // 2. если есть оригинал у 1600 - то он же и у других
        foreach ($return as $i => $sizes) {
            if (!empty($sizes[1600]) && !empty($orig_links[$sizes[1600]->ID])) {
                foreach ($sizes as $s) {
                    $update[$orig_links[$sizes[1600]->ID]][] = $s->ID; // запоминаем какую картинку как апдейтить
                }
                $set += count($sizes);
            }
        }
        // 3. запросы на update
        foreach ($update as $orig => $ids) {
            DB::update('b_file')
                ->set(['original' => $orig])
                ->where('ID', 'IN', $ids)
                ->execute();
        }

        echo 'total ' . $set . " originals set\n\n";

        flush();
    }
    $n += 1000;
} while(count($imgs) == 1000);