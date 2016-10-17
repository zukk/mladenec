<?php


require('../www/preload.php');

ob_get_clean();

ob_start();

// скрипт проверяет тексты на битые ссылки на товары и стирает такие ссылки
$dead_linx = 0;

// возвращает текст с удаленными ссылками на битые продукты
function clear_dead_linx($text)
{
    global $dead_linx;
    $changed = FALSE;

    if (preg_match_all('~href=\s*("|\')([^>\\1]*)\\1~isuU', $text, $matches)) {
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
			
			$match = urldecode( htmlspecialchars_decode($match) );
            if (preg_match('~catalog/([^\./]+)/([0-9]+)\.html(.*)$~isu', $match, $href)) {
				
				$changed = true;
				$s = new Model_Section($href[2]);
				
				if( !$s->loaded() ){
					
                    $text = str_replace($matches[0][$k], '', $text);
					$match = '';
				}
				else{
					
                    $text = str_replace($href[0], 'catalog/' . $s->translit . $href[3], $text);
                    $match = str_replace($href[0], 'catalog/' . $s->translit . $href[3], $match);
				}
				echo 'link to catalog from ' . $href[0] . ' to  catalog/' . $s->translit . $href[3] . "\n";
			}
			
            if (preg_match('~catalog/([^\.\#"]+)#\!(.+)$~isu', $match, $href)) {
				$changed = true;
				
				$_ = explode( ';', $href[2]);
				
				foreach( $_ as $k => $v){
					if( empty( $v ) )
						unset( $_[$k] );
				}
				
				$replaced = implode('&', $_);
				
				echo 'changed ' . $href[0] . ' to ' . 'catalog/' . $href[1] . '?' . $replaced . "\n";
				$text = str_replace($href[0], 'catalog/' . $href[1] . '?' . $replaced, $text);
				$match = str_replace($href[0], 'catalog/' . $href[1] . '?' . $replaced, $match);
			}
			
            if (preg_match('~catalog/([^\.\?"]+)(?:\?(.*))?$~isu', $match, $href)) {

                $g = ORM::factory('section')->where('translit', '=', $href[1])->find();
				
				$is_empty = false;
				
                if (!$g->loaded() || !$g->active) {
					
	                $g = ORM::factory('tag')->where('code', '=', $href[0])->find();
					
	                if (!$g->loaded()) {
						$dead_linx++;
						$changed = TRUE;
						$text = str_replace($matches[0][$k], '', $text);
						$is_empty = true;
						
						echo 'not found section and tag ' . $href[1] . "\n";
					}
					else{
						echo 'not found section but found tag ' .$href[1] . "\n";
					}
                }
				
				if( !$is_empty && !empty( $href[2]) ){
					
					parse_str($href[2], $params);
					
					if( isset( $params['s'] ) )
						unset( $params['s'] );
					
					if( isset( $params['a'] ) ){
						unset( $params['a'] );
					}
					
					if( isset( $params['pp'] ) ){
						unset( $params['pp'] );
					}
					
					if( isset( $params['x'] ) ){
						unset( $params['m'] );
					}
					
					if( !empty( $params['f'] ) && is_array( $params['f'] ) && count( $params['f'] ) == 1 ){
						list( $key, $value ) = each( $params['f'] );
						$params['f' . $key] = $value;
						unset( $params['f'] );
					}
					
					if( !empty( $params['p']['PROPERTY_BRAND'] ) ){
						$params['b'] = $params['p']['PROPERTY_BRAND'];
						unset( $params['p']['PROPERTY_BRAND'] );
					}
					
					if( empty( $params['p'] ) ){
						unset( $params['p'] );
					}
					else{
						if( preg_match( '#^[0-9]+_[0-9]+$#ius', $params['p'] ) ){
							
							list( $f, $fv ) = explode( '_', $params['p'] );
							unset( $params['p'] );
							$params['p' . $f] = $fv;
						}
					}
					
					if( !empty( $params ) ){
						$qstring = '?' . http_build_query($params);
					}
					else {
						$qstring = '';
					}
					
					if( $href[0] != 'catalog/' . $href[1] . $qstring ){
						
						$text = str_replace($href[0], 'catalog/' . $href[1] . $qstring, $text);
						$changed = true;
						echo 'updated query string ' . $href[0] . ' to ' . 'catalog/' . $href[1] . $qstring . "\n";
					}
				}
            }
            if (preg_match('~tag/([^\.]+)\.html$~isu', $match, $href)) {
				
                $g = ORM::factory('tag')->where('code', '=', $href[0])->find();
                if (!$g->loaded() /* || !$g->checked */) {
                    $dead_linx++;
                    $changed = TRUE;
                    $text = str_replace($matches[0][$k], '', $text);
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

// каталог
$news = ORM::factory('section')->where('text', 'LIKE', '%href=%')->find_all();
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
echo 'Sections fixed:' . $menu_fixed . "\n";

echo $dead_linx . ' dead product linx found';
$content = ob_get_clean();
echo $content;
$file = fopen('dead_linx.log', "w");
fwrite($file, $content);
fclose( $file );