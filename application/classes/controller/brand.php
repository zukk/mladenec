<?php

class Controller_Brand extends Controller_Frontend {

    /**
     * Страница одного бренда - покажем все товары бренда
     * @throws HTTP_Exception_404
     */
    public function action_view()
    {
        $brand = ORM::factory('brand', ['translit' => $this->request->param('translit')]);
        if ( ! $brand->loaded() || ! $brand->active) throw new HTTP_Exception_404;

        $this->tmpl['brand'] = $brand;

        $sphinx = new Sphinx('brand', $brand->id);
        $this->tmpl['search_result'] = $sphinx->search();

        $this->tmpl['found'] = $sphinx->found;

        $this->layout->menu = $sphinx->menu();

        if ($sphinx->qs) { // есть параметры из командной строки - меняем титулы и прочее
            $seo = $sphinx_seo = $sphinx->seo();
            $this->layout->set($seo);

        } else {

            $seo = $brand->seo;
            $title = $seo->title;

            if ( ! empty($title)) {
                $this->layout->title = ! empty($brand->seo->title) ? $brand->seo->title : $brand->name;
                if ( ! empty($brand->seo->description)) $this->layout->description = $brand->seo->description;
                if ( ! empty($brand->seo->keywords)) $this->layout->description = $brand->seo->keywords;
            }
        }

        if (($p = $this->request->query('page')) && $p > 1) { // если мы не на первой странице - добавим текст в title
            /*
            if (empty($sphinx_seo)) {
                $seo = $sphinx->seo();
            } else {
                $seo = $sphinx_seo;
            }
            */
            $this->layout->title .= ' (страница каталога №'.$p.') - Младенец.ру';
        }

        if ($this->request->post('goodajax') || $this->request->is_ajax()) { // возвращаем json c данными
            $json = [
                'title' => ! empty($this->layout->title) ? $this->layout->title : 'Младенец. РУ',
                'data' => View::factory('smarty:section/ajax', ['menu' => $this->layout->menu, 'body' => View::factory('smarty:brand/view', $this->tmpl)])->render(),
            ];
            $this->request->query();
            $this->return_json($json);
        }
    }

    /**
     * Список всех брендов, по 50 на странице
     */
    public function action_list()
    {
        $q = ORM::factory('brand')->where('active', '=', 1)->reset(FALSE);

        $this->tmpl['pager'] = $pager = Pager::factory($q->count_all(), 50);

        $this->tmpl['brands'] = $q
            ->order_by('name', 'asc')
            ->limit($pager->per_page)
            ->offset($pager->offset)
            ->find_all();

        $this->layout->menu = Model_Menu::html();
    }

}

