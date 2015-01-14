<?php
class Model_Ad extends ORM {

    protected $_ads = FALSE;

    protected $_table_name = 'z_ad';

    protected $_belongs_to = array(
        'image' => array('model' => 'file', 'foreign_key' => 'file')
    );

    protected $_table_columns = array(
        'id'=>'','code'=>'','name'=>'','active'=>'','weight'=>'','from'=>'','to'=>'',
        'file'=>'','url'=>'','newtab'=>'','type'=>'','showz'=>'','clickz'=>''
    );

    /**
     * Список чекбоксов
     * @return array
     */
    public function flag()
    {
        return array('active', 'newtab');
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
    public function types()
    {
        $types = array(
            'banner_950X60_1',
            'banner_360X256_2',
            'banner_360X256_3',

            'banner_300X210_4',
            'banner_300X210_5',
            'banner_300X210_6',
        );

        $return = array();
        if (strpos($this->code, 'banner_300X210') === 0) {
            foreach($types as $type) {
                if (strpos($type, 'banner_300X210') === 0) {
                    $return[$type] = $type;
                }
            }
        } elseif (strpos($this->code, 'banner_360X256') === 0) {
            foreach($types as $type) {
                if (strpos($type, 'banner_360X256') === 0) {
                    $return[$type] = $type;
                }
            }
        } elseif ($this->code) {
            $return[$this->code] = $this->code;
        } else {
            $return = array_combine($types, $types);
        }

        return $return;

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

        return Kohana::$environment == Kohana::PRODUCTION ? View::factory('smarty:common/banner', array('code' => $code))->render() : '<!-- Ad '.$code.' to be shown here on prod -->';
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