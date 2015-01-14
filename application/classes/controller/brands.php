<?php

class Controller_Brands extends Controller_Frontend {

    public function after() {
        $this->layout->menu = Model_Menu::html();
        parent::after();
    }

    public function action_view()
    {
        $brand = ORM::factory('brand', $this->request->param('id'));
        if ( ! $brand->active) throw new HTTP_Exception_404;

        $this->tmpl['brand'] = $brand;
		
        $this->layout->title = !empty( $brand->seo->title ) ? $brand->seo->title : $brand->name;
        if (!empty( $brand->seo->description ) ) $this->layout->description = $brand->seo->description;
        if (!empty( $brand->seo->keywords ) ) $this->layout->description = $brand->seo->keywords;
    }

    public function action_list()
    {
        
        $q = ORM::factory('brand')->where('active', '=', 1)->reset(FALSE);

        $iPerPageQty = @Kohana::$hostnames[Kohana::$server_name]['per_page_elements'] ?: 50;
        $this->tmpl['pager'] = $pager = Pager::factory($q->count_all(), $iPerPageQty);

        $this->tmpl['brands'] = $q
            ->order_by('name', 'asc')
            ->limit($pager->per_page)
            ->offset($pager->offset)
            ->find_all();
    }

}

