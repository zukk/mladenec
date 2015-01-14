<?php

class Controller_Mobile extends Controller_Frontend {

    /* public $menu = TRUE;

    public function after()
    {
        // some pages still have no menu
        if ($this->menu) $this->layout->menu = Model_Menu::html();
        parent::after();
    } */

	public function action_index(){
		$this->tmpl['vitrina'] = Model_Section::get_catalog(false, 'mladenec');
		$this->layout->title = 'Младенец.ру| Детский интернет магазин, продажа товаров для ребенка, доставка на дом, онлайн заказ на сайте.';
	}
	
	public function action_section(){
		
		$catalog = Model_Section::get_catalog(false, 'mladenec');
		
		$key = 0;
		foreach( $catalog as $id => &$item ){
			
			if( $item->translit == $this->request->param('translit') ){
				$key = $id;
				break;
			}
		}
		unset( $item );
		
		$this->tmpl['section'] = $catalog[$key];
		$this->layout->title = $catalog[$key]->name;
		
		$this->tmpl['hits'] = $this->get_hits($key);
	}
	
	protected function get_hits($random_section = 0){
		
		$hitz = Model_Good::get_hitz();
		if( empty( $random_section ) ){
			$hitz_sections = Model_Section::id_name(array_keys($hitz));
			$random_section = array_rand($hitz_sections);
			$sections_count = count($hitz_sections); // сколько секций
			$section_line = array_merge($hitz_sections, $hitz_sections, $hitz_sections); // 3 раза
			// ищем в середине нашу секцию
			$i = $sections_count;
			while ($section_line[$i]['id'] != $random_section) $i++;
		}
		
		return $hitz[$random_section];
	}
}
