<?php

class Session_Db extends Session {

    protected $_session;
    protected $_id;
    protected $_data = array();

    /**
     * @return  string
     */
    public function id()
    {
        return $this->_id;
    }

    /**
     * @param   string $id  session id
     * @throws Kohana_Session_Exception
     * @return  null
     */
    protected function _read($id = NULL)
    {
        if ( ! $id) {

            $from_cookie = Cookie::get($this->_name);
            if (empty($from_cookie)) { // открываем новую сессию
                $id = $this->_regenerate();
                $this->_write();
            } else {
                $id = $from_cookie;
            }
        }

        if ($id) { // есть ид - берём по нему
            $this->_session = new Model_Session($id);

            if ( ! $this->_session->loaded()) { // нет такой сессии - начинаем новую
                Log::instance()->add(Log::INFO, 'Lost session for '.$id. ' vitrina '.Kohana::$server_name);
            }
            $this->_id = $id;
        }

        Cookie::set($this->_name, $this->id(), $this->_lifetime); // проставляем куку - тут у нас должна уже быть сессия

        if ($data = unserialize($this->_session->data)) {
            $this->_data = $data;
        }

        #заполним код региона, если нет в сессии
        if ( ! isset($this->_data['region']) || (time() - $this->_session->last_active) > 3600*24) { // если нет региона или прошёл день - определим регион
            $SxGeo = new SxGeo(APPPATH.'config/SxGeoCity.dat');

            if ($city = $SxGeo->getCityFull($_SERVER['REMOTE_ADDR'])) {
                $this->_data['region'] = $city['region']['iso'];
            } else {
                $this->_data['region'] = FALSE;
            }
        }

        return NULL;
    }

    /**
     * @return  string
     */
    protected function _regenerate()
    {
        do {
            $id = uniqid().uniqid();
            $sess = new Model_Session($id);
        } while ($sess->loaded()); // подбираем свободный ид

        // Regenerate the session with new id, keeping data
        $sess->id = $this->_id = $id;
        $sess->data = array();
        if ( ! empty($this->_session->data)) {
            $sess->data = $this->_session->data;
        }
        $this->_session = $sess;

        return $sess->id;
    }

    /**
     * @return  bool
     */
    protected function _write()
    {
        $to_save = $this->_data;
		
        unset($to_save['last_active']); // это поле нам не нужно

		$this->_session->id = $this->_id;
		
		$this->_session->data = serialize($to_save);
		
		if( ! empty($to_save['user']) && ! empty(unserialize($to_save['user'])->id)) {
			$this->_session->user_id = unserialize($to_save['user'])->id;
		} else {
            $this->_session->user_id = '';
        }
		
		$this->_session->save();

        return TRUE;
    }

    /**
     * @return  bool
     */
    protected function _restart()
    {
        do {
            $id = uniqid().uniqid();
            $sess = new Model_Session($id);
        } while ($sess->loaded()); // подбираем свободный ид

        // Regenerate the session with new id, keeping data
        $sess->id = $this->_id = $id;
        $sess->data = array();
        $this->_session = $sess;
		
        return TRUE;
    }

    /**
     * @return  bool
     */
    protected function _destroy()
    {
        if ($this->_session->loaded()) {
            $this->_session->delete();
        }

        Cookie::delete($this->_name);

        return TRUE;
    }
}
