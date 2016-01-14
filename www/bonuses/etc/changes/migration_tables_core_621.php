<?php

require_once(MAX_PATH.'/lib/OA/Upgrade/Migration.php');

class Migration_621 extends Migration
{

    function __construct()
    {

		$this->aTaskList_constructive[] = 'beforeAddField__banners__activate_time';
		$this->aTaskList_constructive[] = 'afterAddField__banners__activate_time';
		$this->aTaskList_constructive[] = 'beforeAddField__banners__expire_time';
		$this->aTaskList_constructive[] = 'afterAddField__banners__expire_time';


		$this->aObjectMap['banners']['activate_time'] = array('fromTable'=>'banners', 'fromField'=>'activate_time');
		$this->aObjectMap['banners']['expire_time'] = array('fromTable'=>'banners', 'fromField'=>'expire_time');
    }



	function beforeAddField__banners__activate_time()
	{
		return $this->beforeAddField('banners', 'activate_time');
	}

	function afterAddField__banners__activate_time()
	{
		return $this->afterAddField('banners', 'activate_time');
	}

	function beforeAddField__banners__expire_time()
	{
		return $this->beforeAddField('banners', 'expire_time');
	}

	function afterAddField__banners__expire_time()
	{
		return $this->afterAddField('banners', 'expire_time');
	}

}
