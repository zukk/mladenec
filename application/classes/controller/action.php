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
     * показ списка акций, к которым есть прикреплённые товары
     */
    public function action_current_list()
    {

        $actiontags = new Model_Actiontag();

        $param = $this->request->param();
        $pop = array_pop($param);
        $repeat_el = array_search($pop, $param);

        if(isset($repeat_el) && !empty($repeat_el)){
            unset($param[$repeat_el]);
            $this->request->redirect(Route::url('action_list', $param));
        } else {
            $param = $this->request->param();
        }

        for($i=1; $i<=count($param); $i++){
            $tag[] = $param['tag'.$i];
        }
        $uri = $this->request->uri();

        if(isset($param) && !empty($param)){
            $urltags = $actiontags->get_urltags($param);
        }

        $res = $actiontags->get_actiontags();

        foreach($res as $res_url){
            if(!empty($param)) {
                for ($i = 1; $i <= count($param); $i++) {
                    if ($param['tag'.$i] == $res_url->url) {
                        $res->url[$res_url->url] = $uri;
                    } else {
                        if(empty($res->url[$res_url->url])) {
                            $res->url[$res_url->url] = $uri . '/' . $res_url->url;
                        }
                    }
                }
            } else {
                $res->url[$res_url->url] = $uri . '/' . $res_url->url;
            }
        }

        $q = ORM::factory('action');
        if(!empty($param)) {
            if (isset($urltags) && !empty($urltags)) {
                $q->where('action.id', 'IN', $urltags)
                    ->where('show_wow', '=', 1)
                    ->where('active', '=', 1)
                    ->where('show', '=', 1)
                    ->where_open()
                    ->where('vitrina_show', '=', 'all')
                    ->or_where('vitrina_show', '=', Kohana::$server_name)
                    ->where_close()
                    ->reset(FALSE)
                    ->order_by('order', 'desc')
                    ->order_by('main', 'desc')
                    ->order_by('id', 'desc');
                $actions = $q->find_all();
            } else{
                $actions = array();
                $this->tmpl['actions'] = $actions;
            }
        } else {
            $q->where('show_wow', '=', 1)
                ->where('active', '=', 1)
                ->where('show', '=', 1)
                ->where_open()
                ->where('vitrina_show', '=', 'all')
                ->or_where('vitrina_show', '=', Kohana::$server_name)
                ->where_close()
                ->reset(FALSE)
                ->order_by('order', 'desc')
                ->order_by('main', 'desc')
                ->order_by('id', 'desc');
            $actions = $q->find_all();
        }
        $all = $this->request->query('all');
        $_offset = $offset = $this->request->query('offset');

        $iPerPageQty = @Kohana::$hostnames[Kohana::$server_name]['per_page_elements'] ?: 10;
        $this->tmpl['perPage'] = $iPerPageQty;

        //$actions = $q->find_all();
        $this->tmpl['actions'] = $actions;
        $this->tmpl['actiontags'] = $res;
        $this->tmpl['tag'] = $tag;

        if (empty($all)) {

            if( empty( $offset ) ){

                $this->tmpl['pager'] = $pager = Pager::factory($q->count_all(), $iPerPageQty);
                $offset = $pager->offset;
                $per_page = $pager->per_page;
            }
            else{
                $per_page = $iPerPageQty;
            }

            $q->limit($per_page)
                ->offset($offset);
        }

        $this->tmpl['count'] = $q->reset(false)->count_all();

        if( !empty( $_offset ) ){
            $v = View::factory('smarty:action/current_list_item',$this->tmpl);
            echo ($v->render());
            exit;
        }

        $this->layout->title = 'Акции';
        $this->layout->menu = FALSE; // Model_Menu::html();
    }
}