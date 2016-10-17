<?php

class Controller_Action extends Controller_Frontend
{

    /**
     * просмотр страницы акции, акция неактивна или не показывается = вернёт 404
     * @throws HTTP_Exception_404
     */
    public function action_view()
    {
        $item = new Model_Action($this->request->param('id'));
        if ( ! $item->show) throw new HTTP_Exception_404;

        $this->tmpl['action'] = $item;
		
        $title = ! empty( $item->seo->title ) ? $item->seo->title : $item->name;
        if ( ! empty( $item->seo->description ) ) $this->layout->description = $item->seo->description;
        if ( ! empty( $item->seo->keywords ) ) $this->layout->description = $item->seo->keywords;
		
        $this->layout->title = 'Акции: '.$title;

        $menu = View::factory('smarty:common/new_novelty_wow', array('new' =>
            ORM::factory('new')
                ->with('image')
                ->where('active', '=', 1)
                ->where('date', '<=', date('Y-m-d'))
                ->order_by('date', 'desc')
                ->limit(1)
                ->find()
        ))->render(); // общий заголовок для акций

        if ( $item->active AND $item->show_goods) {
            if ( ! $item->total) {
                $sphinx = new Sphinx('action', $item->id);
                $menu .= $sphinx->menu();
                $this->tmpl['search_result'] = $sphinx->search();
            }
        }
        $this->layout->menu = $menu;

        if ($this->request->post('goodajax') || $this->request->is_ajax()) { // возвращаем json c данными
            $json = [
                'title' => ! empty($this->layout->title) ? $this->layout->title : 'Младенец. РУ',
                'data' => View::factory('smarty:section/ajax', ['menu' => $this->layout->menu, 'body' => View::factory('smarty:action/view', $this->tmpl)])->render(),
            ];
            $this->request->query();
            $this->return_json($json);
        }
    }

    /**
     * показ списка акций
     */
    public function action_list()
    {
        $q = ORM::factory('action')
            ->where('show_actions', '=', 1)
            ->where('active', '=', 1)
            ->where('show', '=', 1)
            ->where_open()
                ->where('vitrina_show', '=', 'all')
                ->or_where('vitrina_show', '=', Kohana::$server_name)
            ->where_close()
            ->reset(FALSE);

        $this->tmpl['pager'] = $pager = Pager::factory($q->count_all(), 10);

        $this->tmpl['actions'] = $q
            ->order_by('main', 'desc')
            ->order_by('to', 'asc')
            ->order_by('id', 'desc')
            ->limit($pager->per_page)
            ->offset($pager->offset)
            ->find_all();

        $this->layout->title = 'Акции';
        $this->layout->menu = FALSE;
    }

    /**
     * Архив акций
     * @throws Kohana_Exception
     */
    public function action_arhive()
    {
        $q = ORM::factory('action')
            ->where('active','=','0')
            ->where('show', '=', 1)
            ->where_open()
                ->where('vitrina_show', '=', 'all')
                ->or_where('vitrina_show', '=', Kohana::$server_name)
            ->where_close()
            ->reset(FALSE);

        $this->tmpl['pager'] = $pager = Pager::factory($q->count_all(), 10);
        
        $actions = $q
            ->order_by('id', 'desc')
            ->limit($pager->per_page)
            ->offset($pager->offset)
            ->find_all();
        
        $this->tmpl['actions'] = $actions;

        $this->layout->title = 'Архив акций';
        $this->layout->menu = Model_Menu::html();
    }
    
    /**
     * показ списка акций, возможно отобранных по тегам
     */
    public function action_current_list()
    {
        $actiontags = new Model_Actiontag();

        $tags = $this->request->param('tags');

        if ($tags == 'current') { // редирект на полный список
            $this->request->redirect(Route::url('action_list'));
        }

        if ( ! empty($tags)) { // запрошены какие-то теги
            $tags = explode('/', $tags);
            $unique_tags = array_unique($tags);

            if (count($unique_tags) < count($tags)) { // дубли тегов - редирект
                $this->request->redirect(Route::url('action_list', ['tags' => implode('/', $unique_tags)]));
            }
        }

        $all_tags = $actiontags->get_actiontags(); // все возможные теги

        foreach ($all_tags as &$tag) { // оформим для списка тегов
            if (empty($tags)) {
                $tag->url = Route::url('action_list', ['tags' => $tag->url]);
            } elseif (in_array($tag->url, $tags)) { // активный тег
                $tag->active = TRUE;
                $tag->url = Route::url('action_list', ['tags' => implode('/', array_diff($tags, [$tag->url]))]);
            } else {
                $tag->active = FALSE;
                $tag->url = Route::url('action_list', ['tags' => implode('/', array_merge($tags, [$tag->url]))]);
            }
        }

        // запрос на получение списка акций
        $q = ORM::factory('action')
            ->where('active', '=', 1)
            ->where('show', '=', 1)
            ->where_open()
                ->where('vitrina_show', '=', 'all')
                ->or_where('vitrina_show', '=', Kohana::$server_name)
            ->where_close()
            ->order_by('order', 'desc')
            ->order_by('main', 'desc')
            ->order_by('id', 'desc');

        if ( ! empty($tags)) { // условие на теги (любой из списка, если есть)

            $q->join('z_actiontag_ids')
                    ->on('action.id', '=', 'z_actiontag_ids.action_id')
                ->join('z_actiontag')
                    ->on('z_actiontag.id', '=', 'z_actiontag_ids.actiontag_id')
                ->where('z_actiontag.url', 'IN', $tags);
        }

        $this->tmpl['actions'] = $actions = $q->find_all()->as_array();
        if (count($actions) == 0) throw new HTTP_Exception_404;

        $this->tmpl['tags'] = $all_tags;

        $this->layout->title = 'Акции';
        $this->layout->menu = FALSE;
    }
}