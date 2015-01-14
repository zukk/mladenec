<?php


require('../www/preload.php');

// скрипт проверяет тексты на битые ссылки на товары и стирает такие ссылки
$dead_linx = 0;

// возвращает текст с удаленными ссылками на битые продукты
function clear_dead_linx($text)
{
    global $dead_linx;
    $changed = FALSE;

    if (preg_match_all('~href=\s*("|\')([^>]*)\\1~isuU', $text, $matches)) {
        foreach ($matches[2] as $k => $match) {
            if (preg_match('~.*/product/.*/(\d+)\.(\d+)\.html~isu', $match, $href)) {

                $g = new Model_Good($href[2]);
                if (!$g->loaded() || !$g->show) {
                    $dead_linx++;
                    $changed = TRUE;
                    $text = str_replace($matches[0][$k], '', $text);

                } elseif ($g->group_id != $href[1]) { // group changed
                    $dead_linx++;
                    $changed = TRUE;
                    $text = str_replace($matches[0][$k], 'href="' . $g->get_link(0) . '"', $text);

                }
            }
        }
        if ($changed) return $text;
    }
    return FALSE;
}

// новости
$news = ORM::factory('new')->where('text', 'LIKE', '%href=%')->find_all();
$news_fixed = 0;
foreach ($news as $n) {
    $n->text = clear_dead_linx($n->text);
    if ($n->text !== FALSE) {
        try {
            $n->save();
            $news_fixed++;
        } catch (Kohana_ORM_Validation_Exception $e) {
            print_r($n->as_array());
        }
    }
}
echo 'News fixed:' . $news_fixed . "\n";

// статьи
$news = ORM::factory('article')->where('text', 'LIKE', '%href=%')->find_all();
$arts_fixed = 0;

foreach ($news as $n) {
    $n->text = clear_dead_linx($n->text);
    if ($n->text !== FALSE) {
        try {
            $n->save();
            $arts_fixed++;
        } catch (Kohana_ORM_Validation_Exception $e) {
            print_r($n->as_array());
        }
    }
}
echo 'Arts fixed:' . $arts_fixed . "\n";

// отзывы о сайте
$news = ORM::factory('comment')->where('answer', 'LIKE', '%href=%')->find_all();
$coms_fixed = 0;

foreach ($news as $n) {
    $n->answer = clear_dead_linx($n->answer);
    if ($n->answer !== FALSE) {
        try {
            $n->save();
            $coms_fixed++;
        } catch (Kohana_ORM_Validation_Exception $e) {
            print_r($n->as_array());
        }
    }
}
echo 'Comms fixed:' . $coms_fixed . "\n";

// акции
$news = ORM::factory('action')->where('text', 'LIKE', '%href=%')->find_all();
$act_fixed = 0;

foreach ($news as $n) {
    $n->text = clear_dead_linx($n->text);
    if ($n->text !== FALSE) {
        try {
            $n->save();
            $act_fixed++;
        } catch (Kohana_ORM_Validation_Exception $e) {
            print_r($n->as_array());
        }
    }
}
echo 'Acts fixed:' . $act_fixed . "\n";

// страницы
$news = ORM::factory('menu')->where('text', 'LIKE', '%href=%')->find_all();
$menu_fixed = 0;

foreach ($news as $n) {
    $n->text = clear_dead_linx($n->text);
    if ($n->text !== FALSE) {
        try {
            $n->save();
            $menu_fixed++;
        } catch (Kohana_ORM_Validation_Exception $e) {
            print_r($n->as_array());
        }
    }
}
echo 'Pages fixed:' . $menu_fixed . "\n";

echo $dead_linx . ' dead product linx found';
