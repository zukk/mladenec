<?php

class Controller_News extends Controller_Frontend {

    public function after() {
        $this->layout->menu = Model_Menu::html();
        parent::after();
    }

    public function action_view()
    {
        $new = ORM::factory('new', $this->request->param('id'));
        if ( ! $new->active) throw new HTTP_Exception_404;

        $this->tmpl['new'] = $new;
        $this->tmpl['site'] = Mail::site();

        $this->layout->title = !empty( $new->seo->title ) ? $new->seo->title : $new->name;
        if (!empty( $new->seo->description ) ) $this->layout->description = $new->seo->description;
        if (!empty( $new->seo->keywords ) ) $this->layout->description = $new->seo->keywords;
    }

    public function action_list() {
        $q = ORM::factory('new')
            ->where('date','<=',date('Y-m-d'))
            ->where('active', '=', 1)
            ->reset(FALSE);

        $iPerPageQty = @Kohana::$hostnames[Kohana::$server_name]['per_page_elements'] ?: 10;
        $this->tmpl['pager'] = $pager = Pager::factory($q->count_all(), $iPerPageQty);

		$title = 'О новинках для детей и будущих мам в ' . ( empty($_SERVER['HTTP_HOST']) ? 'default' : $_SERVER['HTTP_HOST'] );
		$description = 'Младенец.ру представляет новинки товаров для детей. Обзор новых игрушек, продуктов питания и других детских товаров.';
		
		if( $pager->p > 1 ){

			$title = 'Страница ' . $pager->p . '. ' . $title;
			$description = 'Страница ' . $pager->p . '. ' . $description;
		}
		
        $this->tmpl['news'] = $q
            ->order_by('date', 'desc')
            ->limit($pager->per_page)
            ->offset($pager->offset)
            ->with('image')
            ->find_all();
		
		$this->layout->title = $title;
		$this->layout->description = $description;
    }

} // End Welcome

