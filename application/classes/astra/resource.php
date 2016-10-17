<?php
/**
 *  addResources, addResourcesExt, deleteResources, addResource, addResourceExt, deleteResource
 */
class Astra_Resource extends Astra_Client {
    
    /**
     * garage_id => point_id
     *
     * @var array
     */
    private $garage_point_id_cache = array();
    
    /**
     * Ключи - имена в Астра, поля - имена в БД
     */
    private $field_map = array(
        'id'              => 'id',
        'name'            => 'name',
        'weightCapacity'  => 'weight_capacity',
        'volumeCapacity'  => 'volume_capacity',
        'volumeACapacity' => 'volume_a_capacity',
        'mileageRate'     => 'mileage_rate',      
        'timeRate'        => 'time_rate',         
        'garageId'        => 'garage_id',
        'demandMarker'    => 'demand_marker',     // (string) DemandMarker метки - разделитель ","
    );
    
    protected function model_to_array($model, $map) {
        $array = parent::model_to_array($model, $map);
        
        if ( ! empty($this->garage_point_id_cache[$model->garage_id])) {
            $garage_point_id = $this->garage_point_id_cache[$model->garage_id];
        } else {
            $garage = new Model_Astra_Garage($model->garage_id);
            $garage_point_id = $garage->point_id;
        }
        
        
        $array['garageId'] = $garage_point_id;
        $array['start']  = Txt::time_to_seconds($model->start)  * 1000; // milliseconds...
        $array['finish'] = Txt::time_to_seconds($model->finish) * 1000;

        return $array;
    }
    
    public function add_resource($resource_obj, $skip_existing = FALSE) {
        
        $method_name = 'addResource';
        $params = array('resource'=>$this->model_to_array($resource_obj, $this->field_map));
        
        if ($skip_existing) {
            $method_name = 'addResourceExt';
            $params['skipExisting'] = true;
        }
        
        return $this->c($method_name,$params);
    }
    
    public function add_resources($resources_obj, $skip_existing = FALSE) {
        
        $method_name = 'addResources';
        $params = array('resource' => array());
        
        foreach($resources_obj as $res_o) { $params['resourceList'][] = $this->model_to_array($res_o, $this->field_map); }
        
        if ( empty($params['resourceList'])) return FALSE;
        
        if ($skip_existing) {
            $method_name = 'addResourcesExt';
            $params['skipExisting'] = true;
        }
        return $this->c($method_name, $params);
    }
    
    public function delete_resource($resource_id) {
        return $this->c('deleteResource',array('resourceId'=>$resource_id));
    }
    
    public function delete_all_resources() {
        return $this->c('deleteAllResources',array());
    }
    
    public function __construct() {
        parent::__construct('resource');
    }
}
