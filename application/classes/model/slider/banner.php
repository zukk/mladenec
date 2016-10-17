<?php
class Model_Slider_Banner extends ORM {

    const SLIDER_MLADENEC_INDEX = 1;
    const SLIDER_EATMART_INDEX  = 2;
    
    protected $_table_name = 'z_slider_banner';

    protected $_table_columns = array(
        'id'        => '',
        'slider_id' => '',
        'action_id' => 0,  // Акция, к которой привязан баннер
        'name'      => '',
        'from'      => '',
        'to'        => '',
        'url'       => '', // Ссылка
        'newtab'    => '', // Открыть в новом окне
        'src'       => '', // Картинка
        'active'    => '',
        'allow'    => '',
        'order'     => ''
    );

    /**
     * Список чекбоксов
     * @return array
     */
    public function flag()
    {
        return array('active', 'newtab', 'allow');
    }

    /**
     * Список картинок
     * @return array
     */
    public function img()
    {
        return array('file' => array());
    }

    /**
     * @static Типы баннеров
     * @return array
     */
    public function sliders()
    {
        return array(
            self::SLIDER_MLADENEC_INDEX => 'Младенец - главная',
            self::SLIDER_EATMART_INDEX  => 'Итмарт - главная',
        );
    }

    public function get_slider_name() {
        $sliders = $this->sliders();
        $name = '-';
        if ( ! empty($sliders[$this->slider_id])) {
            $name = $sliders[$this->slider_id];
        }
        return $name;
    }
    
    /**
     * @return array
     */
    public function rules()
    {
        return array(
            'name' => array(
                array('not_empty'),
            ),
        );
    }

    /**
     * Включает баннерокрутилку
     * @return Model_Ad
     */
    public function init() {
        if ($this->_ads !== FALSE) return $this;

        $all = self::factory('ad')
            ->where('active', '=', 1)

            ->and_where_open()
                ->where('from', 'IS', DB::expr('NULL'))
                ->or_where('from', '<=', DB::expr('NOW()'))
            ->and_where_close()

            ->and_where_open()
                ->where('to', 'IS', DB::expr('NULL'))
                ->or_where('to', '>', DB::expr('NOW()'))
            ->and_where_close()
            ->order_by('code')
            ->order_by('weight')
            ->with('img')
            ->find_all();

        $this->_ads = array();
        foreach ($all as $ad) {
            if (empty($this->_ads[$ad->code])) $this->_ads[$ad->code] = array();
            $this->_ads[$ad->code][] = $ad;
        }
        return $this;
    }

    /**
     * Получить html код баннера по его типу, до вызова нужно сделать init
     * @param $code
     * @return string
     */
    public function html($code) {

        return View::factory('smarty:common/banner', array('code' => $code))->render();
    }

    /**
     * Показывает число активных баннеров на баннерном месте
     * @param $code
     * @return string
     */
    public function stat($code)
    {
        if (empty($this->_ads[$code])) return 'нет активных баннеров';

        $to = 0;
        foreach($this->_ads[$code] as $a) {
            if ( ! empty($a->to)) $to = max($to, $a->to);
        }

        return 'активных баннеров '.count($this->_ads[$code])
            .($to ? ' до '.date('d.m.y H:M', $to) : '');
    }

    /**
     * Может ли показываться сейчас баннер
     * @return mixed
     */
    public function is_ok()
    {
        $t = time();
        return $this->active AND ($this->to == 0 OR $this->to > $t) AND ($this->from == 0 OR $this->from <= $t);
    }
}