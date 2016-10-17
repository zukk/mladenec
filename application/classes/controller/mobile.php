<?php

class Controller_Mobile extends Controller_Frontend {

    /* public $menu = TRUE;

    public function after()
    {
        // some pages still have no menu
        if ($this->menu) $this->layout->menu = Model_Menu::html();
        parent::after();
    } */

	public function action_index()
    {
		$this->tmpl['vitrina'] = Model_Section::get_catalog(FALSE, 'mladenec');
		$this->layout->title = 'Младенец.ру| Детский интернет магазин, продажа товаров для ребенка, доставка на дом, онлайн заказ на сайте.';
	}
	
	public function action_section()
    {
		$catalog = Model_Section::get_catalog(FALSE, 'mladenec');
		
		$key = 0;
		foreach ($catalog as $id => &$item) {
			if ($item->translit == $this->request->param('translit')) {
				$key = $id;
				break;
			}
		}
		unset( $item );
		
		$this->tmpl['section'] = $catalog[$key];
		$this->layout->title = $catalog[$key]->name;
		
		$this->tmpl['hits'] = Model_Good::get_hitz($key);
	}
}
