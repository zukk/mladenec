<?php
/**
 *  addGarages, addGaragesExt, addGarage, deleteGarage, getGarage, deleteAllGarages
 */
class Astra_Garage extends Astra_Client {
    /**
     * Ключи - имена в Астра, поля - имена в БД
     */
    private $field_map = array(
            //'id'        => 'id',
            'name'      => 'name',
    );
    
    
    protected function model_to_array($model, $map) {
        $array = parent::model_to_array($model, $map);
        
        if (empty($model->point_id)) {
            return FALSE;
        }
        $point = new Model_Astra_Point($model->point_id);
        
        $array['id']        = $point->id;
        $array['address']   = $point->address;
        $array['latitude']  = $point->latitude;
        $array['longitude'] = $point->longitude;
        
        return $array;
    }
    
    public function add_garage($garage_obj,$skip_existing = FALSE) {
        $method = 'addGarage';
        $params = array('order'=>$this->model_to_array($garage_obj, $this->field_map));

        if ($skip_existing) {
            $method = 'addGarageExt';
            $params['skipExisting'] = true;
        }
        
        return $this->c($method,$params);
    }
    
    public function add_garages($garages_obj,$skip_existing = FALSE) {
        $method = 'addGarages';
        $params = array('garageList' => array());
        
        foreach($garages_obj as $grg_o) { $params['garageList'][] = $this->model_to_array($grg_o, $this->field_map); }
        
        if ( empty($params['garageList'])) return FALSE;
        
        if ($skip_existing) {
            $params['skipExisting'] = true;
            $method = 'addGaragesExt';
        }
        
        //var_dump($params);
        
        return $this->c('addGarages',$params);
    }
    
    public function delete_garage($garage_id) {
        return $this->c('deleteGarage',array('garageId' => $garage_id));
    }
    
    public function delete_all_garages() {
        return $this->c('deleteAllGarages',array());
    }
    
    public function __construct() {
        parent::__construct('garage');
    }
}
