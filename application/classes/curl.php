<?php
class Curl {
    const   TIMEOUT   = 30;
    public  $response = NULL;
    private $error    = NULL;
    private $errno    = NULL;

    /**
     * @param $url
     * @param bool|array $post
     * @return mixed
     */
    public function get_url($url, $post = FALSE, $header = NULL)
    {
        $ch = curl_init();

        if (is_array($header)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, self::TIMEOUT);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 16);

        if ($post) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
        }

        $this->response = curl_exec($ch);
        $this->errno = curl_errno($ch);
        $this->error = curl_error($ch);

        curl_close($ch);
        
        if ($this->errno) {
            $log_message = 'Curl error on url: ' . $url . ', ' . $this->errno . ': ' . $this->error;
            if ($post) $log_message .= ' POST: ' . json_encode($post);
            Log::instance()->add(Log::WARNING, $log_message);
            return FALSE;
        }
        
        return $this->response;
    }
    
    public function get_errno()
    {
        return $this->errno;
    }
    
    public function get_error()
    {
        return $this->error;
    }
    
    public function is_ok()
    {
        if ( is_null($this->errno)) return TRUE;
        else return FALSE;
    }
}