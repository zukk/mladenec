<?php
/**
 *
 */
class Astra_Point extends Astra_Client {
    
    /**
     * Ключи - имена в Астра, поля - имена в БД
     */
    private $field_map = array(
            'id'        => 'id',
            'latitude'  => 'latitude',
            'longitude' => 'longitude',
            'address'   => 'address'
    );
    
    /**
     * 
     * @param Model_Astra_Point $point
     * @param bool $skip_existing
     */
    public function add_point($point, $skip_existing = FALSE) {
        
        $point = $this->model_to_array($point, $this->field_map);
        
        if ($skip_existing) {
            $this->result = $this->c('addPointExt', array('point' => $point, 'skipExisting' => true));   
        } else {
            $this->result = $this->c('addPoint',    array('point' => $point));
        }
        
        return $this->result;
    }
    /**
     * 
     * @param Model_Astra_Point $points
     * @param bool $skip_existing
     */    
    public function add_points($points_obj, $skip_existing = FALSE) {
        
        $method_name = 'addPoints';
        $params['pointList'] = array();
        
        foreach($points_obj as $pnt_o) {
            $params['pointList'][] = $this->model_to_array($pnt_o, $this->field_map);
        }
        
        if ($skip_existing) {
            $method_name = 'addPointsExt';
            $params['skipExisting'] = true;
        }
        
        return $this->c($method_name, $params);
    }
    
    public function delete_point($id) {
        return $this->c('deletePoint',array('pointId' => $id));
    }
    
    public function delete_all_points() {
        return $this->c('deleteAllPoints',array());
    }
    
    public function __construct() {
        parent::__construct('point');
    }
}
