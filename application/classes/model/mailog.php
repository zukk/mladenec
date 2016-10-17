<?php

// моделька для истории изменений в админке и не только
class Model_Mailog extends ORM {

    const PRICE_FRANSH_RATIO = 1.05; // коэффициент цены, показываемой франшизникам (цена умножается на этот коэф)

    protected $_table_name = 'z_mailog';

    protected $_table_columns = array(
        'id' => '', 'code' => '', 'date'=>'', 'time'=>'', 'data' => '', 'model'=>'', 'action'=>'', 'item_id'=>'',  'sent' => ''
    );

    /**
     * Добавить запись в историю
     *
     * @param string $code
     * @param mixed $data
     * @param string $model
     * @param int $item_id
     *
     * @param null $action
     * @param bool $attach
     * @return int
     */
    public static function log($code, $data, $model = NULL, $item_id = NULL, $action = NULL, $attach = FALSE)
    {
        $ml = new self();
        
        $ml->values(array(
            'code' => $code,
            'date' => date('Y-m-d'),
            'time' => date('G:i:00'),
            'data' => serialize($data),
            'model' => $model,
            'item_id' => $item_id,
            'action' => $action,
            'sent' => 0,
            'attach' => $attach,
        ));
        $ml->save();

        return $ml->id;
    }

    /**
     *
     * @param Model_Mailog $logs
     *
     * @param bool $add_header
     * @param string $separator
     * @return string Logs in csv format
     */
    public static function to_csv($logs, $add_header = TRUE, $separator = ';') {
        //$separator = ';';
        $csv = '';
        
        if (TRUE == $add_header) {
            $csv = 'id;code;date;time;model;name;group_name;translit;section_id;brand_id;group_id;promo_id;active;order;image;xml_id;code;price;pack;rating;review_qty;qty;popularity;upc;old_price;barcode';
        }
        
        foreach($logs as $l) {
            $csv .= $l->id    . $separator;
            $csv .= $l->code  . $separator;
            $csv .= $l->date  . $separator;
            $csv .= $l->time  . $separator;
            $csv .= $l->model . $separator;
            $csv .= $l->item_id . $separator;
            $csv .= $l->action  . $separator;
            $csv .= "\r\n";
        }
    }
    
    /**
     * 
     * @return mixed
     * @throws Exception
     */
    public function get_data()
    {
        if ( ! $this->loaded()) throw new Exception('Unable to get data from not loaded mailog instance');
        
        return unserialize($this->data);
    }
}
