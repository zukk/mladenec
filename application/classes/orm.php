<?php defined('SYSPATH') or die('No direct script access.');

class ORM extends Kohana_ORM
{
    /**
     * 
	 * @chainable
	 * @param  Validation $validation Validation object
	 * @return ORM
	 */
	public function save(Validation $validation = NULL)
	{
        if (method_exists($this, 'events'))
        {
            $events = $this->events();
            
            foreach($events as $type => $message)
            {
                Model_Event::log($type, $this->pk(), $message);
            }
        }
        
        return parent::save($validation);
	}
}