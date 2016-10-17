<?php
class Model_Filter_Value extends ORM {

    protected $_table_name = 'z_filter_value';

    protected  $_belongs_to = [
        'filter' => ['model' => 'filter', 'foreign_key' => 'filter_id'],
        'image' => ['model' => 'file', 'foreign_key' => 'img']
    ];

    protected $_table_columns = [
        'id' => '', 'code' => '', 'name' => '', 'sort' => '',
        'filter_id' => '', 'img' => '', 'roditi' => ''
    ];

    /**
     * @static Почистить все фильтры для товара
     * @param int $good_id
     * @return Database_Query
     */
    public static function clear_good($good_id)
    {
        return DB::delete('z_good_filter')->where('good_id', '=', $good_id)->execute();
    }

    /**
     * @static Добавить фильтр для товара
     * @param int $good_id
     * @param int $filter_id
     * @param \Model_Filter_Value[] $vals
     * @return Database_Query
     */
    public static function bind($good_id, $filter_id, $vals)
    {
        if (empty($vals)) return FALSE;

        $ins = DB::insert('z_good_filter')
            ->columns(array('good_id', 'filter_id', 'value_id'));
        foreach($vals as $v) {
            $ins->values(array($good_id, $filter_id, $v->id));
        }

        return DB::query(Database::INSERT, str_replace('INSERT ', 'INSERT IGNORE ', $ins))->execute();
    }

    /**
     * Получает массив id => name фильтров
     * И массив значений для них в формате [filter_id] => [id => name]
     * Одним запросом
     */
    public static function filter_val($values_ids)
    {
        $fvalues = DB::select('fv.id', 'fv.name', 'fv.roditi', DB::expr('f.id as fid'), DB::expr('f.name as filter_name'))
            ->from(array('z_filter_value', 'fv'))
            ->join(array('z_filter', 'f'))
                ->on('f.id', '=', 'fv.filter_id')
            ->where('fv.id', 'IN', $values_ids)
            ->order_by('f.sort')
            ->order_by('fv.sort')
            ->order_by('fv.name')
            ->execute()
            ->as_array('id');

		$filters = $values = $roditi = array();
        foreach($fvalues as $fvid => $data) {
            $filters[$data['fid']] = $data['filter_name'];
            $values[$data['fid']][$fvid] = $data['name'];
            $roditi[$data['fid']][$fvid] = $data['roditi'];
        }
		
        return array($filters, $values, $roditi);
    }

    /**
     * После сохранения значения в админке - сохранить его пропы
     */
    public function admin_save()
    {
        $messages = array();

        // сохранение новой картинки
        if ( ! empty($_FILES['img']) AND Upload::not_empty($_FILES['img']) AND Upload::valid($_FILES['img'])) {

            if ( ! Upload::image($_FILES['img'])) {
                $messages['errors'] = array(Kohana::message('admin/section', 'img.default'));
            } else { // пришла новая картинка

                $file = Model_File::image('img');
                $file->MODULE_ID = __CLASS__;
                $file->item_id = $this->id;
                $file->save(); // save original file

                if ($this->img) ORM::factory('file', $this->img)->delete(); // delete old

                $this->img = $file->ID; // save img
                $this->save();

                Model_History::log('filter_value', $this->id, 'image', $file->ID);
            }
        }

		$d = json_decode(file_get_contents('http://export.yandex.ru/inflect.xml?name=' . urlencode($this->name) . '&format=json'), TRUE);
		
		if ( ! empty($d[2])) {
			$this->roditi = $d[2];
			$this->save();
		}
		
        return $messages;
    }

    /**
     * получает по строке с весом список значений искусственного фильтра вес
     * @param array $params
     */
    static function weight(array $params)
    {
        $weights = [];
        for($i = $params[0]; $i <= $params[1]; $i++) $weights[] = $i;

        $q = DB::select('id')
            ->from('z_filter_value')
            ->where('filter_id', '=', Model_Filter::WEIGHT)
            ->where('name', 'IN', $weights)
            ->execute();

        return $q->as_array('id', 'id'); //;
    }

    /**
     * Получить html для картинки значения фильтра
     * @return string
     */
    public function get_img()
    {
        if ($this->img == 0) return Model_File::empty_image();

        return $this->image->get_img([
            'alt'   => $this->name,
            'title' => $this->name,
        ]);
    }

}
