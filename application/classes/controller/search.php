<?php
/**
 * Search controller.
 * 
 * @package     mladenec.ru
 * @subpackage  search
 * @category    controller
 * 
 * @author      iFabrik <input@ifabrik.ru>
 * @version     $Id$
 */
class Controller_Search extends Controller_Frontend
{
    /**
     * Обработка запроса поиска по сайту.
     * 
     * @throws HTTP_Exception_404
     */
    public function action_do()
    {
        // Определение запроса поиска.
        if (null === ($query = $this->request->post('query', null))) exit(1);
        if (3 >= strlen($query)) exit(1);

        // Корректировка пользовательского ввода.
        require_once(APPPATH . 'classes' . DIRECTORY_SEPARATOR . 'langcorrect.php');
        $arCorrect = array();
        $langCorrect = new LangCorrect();
        $langCorrect->parse($query, LangCorrect::SIMILAR_CHARS | Text_LangCorrect::KEYBOARD_LAYOUT | LangCorrect::ADD_FIX, $arCorrect);
        $searchQuery = (count($arCorrect) ? str_replace(array_keys($arCorrect), array_values($arCorrect), $query) : $query);

        // Подключение к базе sphinx
        $sphinx = Database::instance('sphinx');

        // Выборка базовых словоформ из стеммера.
        $q = Sphinx::correct_user_query($searchQuery);
        $arBaseWords = array_keys($sphinx->query(Database::SELECT, sprintf("call keywords('%s', 'goods')", $q))->as_array('normalized'));

        // Формирование запроса для sphinx.
        $sSphinxQuery = implode(' | ', array_map(function($v) { return sprintf('%s | *%s* | =%s ', $q, $q, $q); }, $arBaseWords));

        // Поиск активных акций.
        $sqlquery = DB::select()->from('actions')->where(DB::expr("MATCH('@words "), sprintf('%s @vitrina=%s', $sSphinxQuery, Kohana::$server_name), DB::expr("')"))
                ->where('from', '>=', ($t = time()))->where('to', '<=', $t);

        $result = $sphinx->query(Database::SELECT, $sqlquery . " OPTION ranker=expr('sum(lcs)+bm25')")->as_array();
        if (! empty($result)) $arResult['actions'] = $result;

        /**
         * Поиск брендов.
         * 
         * Так как добиться внятной релевантности с сортировкой достаточно трудоемко,
         * по причине разницы между значениями сортировки (где 999999, а где 1),
         * то:
         * 1. выбираем все бренды.
         * 2. если в запросе явно присутствует название бренда: ranker без сортировки.
         * 3. если в запросе не присутствует название бренда: ranker с сортировкой.
         */
        // Выборка всех брендов.
        $arBrands = DB::select(DB::expr("LOWER(name) as name"))->from('z_brand')->where('active', '=', 1)->execute()->as_array('name');
        $iBrandExists = count(array_filter($arBrands, function($v) use ($searchQuery) { 
            $arbr = array_filter(split('[-\s]', $v['name']), function($v) { return (1 < mb_strlen($v)); });
            if (! count($arbr)) return false;
            return count(array_filter(array_map("stripos", array_fill(0, count($arbr), $searchQuery), $arbr), "is_int"));
        }));

        // Формирование запроса к sphinx.
        $sqlquery = DB::select('id', 'name', 'sort')->from('brands')->where(DB::expr("MATCH('"), $sSphinxQuery, DB::expr("')"))->limit(4);
        $sSphinxRanker = ($iBrandExists ? "expr('sum(hit_count+bm25)')" : "expr('sum(hit_count+bm25)*sort')");
        $result = $sphinx->query(Database::SELECT, $sqlquery . ' OPTION ranker=' . $sSphinxRanker)->as_array('id');
        
        if (! empty($result))
        {
            // Выборка разделов сайта относительно брендов.
            $arSections = DB::query(Database::SELECT, 
                            "SELECT s.*, sb.brand_id FROM z_section s JOIN z_section_brand sb ON ( sb.section_id = s.id AND (sb.brand_id = " . implode(' OR sb.brand_id = ', array_keys($result)) . "))")
                            ->execute()->as_array('id');

            foreach($arSections as $sid => $section) { $result[$section['brand_id']]['sections'][] = $section; }
            $result = array_filter($result, function($value) { return (isset($value['sections'])); });

            // Сохранение брендов в результате.
            $arResult['brands'] = $result;
        }

        // Поиск товаров в магазине.
        $sqlquery = DB::select('id')->from('goods')
                    ->where(DB::expr("MATCH('"), sprintf('%s @vitrina=%s', $sSphinxQuery, Kohana::$server_name), DB::expr("')"))
                    ->limit(15);

        $result = $sphinx->query(Database::SELECT, $sqlquery . " OPTION ranker=expr('sum((lcs+hit_count))*bm25+query_word_count+popularity')")->as_array('id');
        if (! empty($result))
        {
            $arResult['goods'] = ORM::factory('good')->where('id', 'IN', array_keys($result))->find_all()->as_array('id');
        }

        // Сохранение поискового запроса.
        $search = ORM::factory('search_history')->where('tokenized_query', '=', join(' ', $arBaseWords))->where('vitrina', '=', Kohana::$server_name)->find();
        if ($search->loaded()) { $search->rate += 0.1; $search->update(); }
        else {
            $search = new Model_Search_History();
            $search->search_query = mb_strtolower($searchQuery);
            $search->tokenized_query = join(' ', $arBaseWords);
            $search->vitrina = Kohana::$server_name;
            $search->save();
        }

        $v = View::factory('smarty:search/' . Kohana::$server_name, $arResult);
        exit($v->render());
    }

    /**
     * Запрос примера пользовательского запроса.
     * 
     * @throws HTTP_Exception_404
     */
    public function action_example()
    {
        die(json_encode(array('result' => DB::select('search_query')->from('z_search_history')->limit(1)->execute()->as_array())));
    }
}