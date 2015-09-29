<?php

class Pager {
    const PARAM = 'page';
    const PER_PAGE = 20;

    var $p = 1;
    var $per_page = 20;
    var $total = FALSE; // может быть не известно
    var $pages = 1;
    var $offset = 0;
    var $link = '';

    protected $html = '';
    protected $query_params = array();

    public function __construct($total = FALSE, $per_page = self::PER_PAGE)
    {
        $this->total = $total;
        $this->per_page = $per_page;
        $this->pages = ceil($total / $per_page);
        $this->p = 1;

        if ($total > 0)
        {
            if ($this->pages > 1)
            {
                $this->p = Request::current()->query(self::PARAM);
                if ($this->p > $this->pages) $this->p = $this->pages;
            }
        }
        else
        { // do not check max page if total = 0
            $this->p = Request::current()->query(self::PARAM);
        }
        
        if ($this->p < 1) $this->p = 1;

        $this->offset = ($this->p - 1) * $this->per_page;

        $this->query_params = Request::current()->query();
        
        if (isset($this->query_params[self::PARAM])) unset($this->query_params[self::PARAM]);
    }

    /**
     * 
     * @param int $total
     * @param int $per_page
     * @return \self
     */
    public static function factory($total = 0, $per_page = self::PER_PAGE)
    {
        return new self($total, $per_page);
    }

    public function html($word, $hash = FALSE, $fancy = FALSE)
    {
        if ( ! $hash) 
        {
			$this->base = '/' . Request::current()->uri();
            $query = http_build_query($this->query_params);
            $this->link = '?' . ($query ?  $query.'&' : '');
        }
        else
        {
			$this->base = '';
            $this->link = '#!'.$hash;
        }

		if( $this->p >= 2 ){
			
			if( $this->p == 2 ){
				$prev_link = $this->base . mb_substr($this->link, 0, -1 );
			}
			else
				$prev_link = $this->base . $this->link . Pager::PARAM . '=' . ( $this->p - 1 );
			
			View::bind_global('pager_prev', $prev_link );		
		}
		
		if( $this->p <= ( $this->pages - 1 ) ){
			$next_link = $this->base . $this->link . Pager::PARAM . '=' . ( $this->p + 1 );
			View::bind_global('pager_next', $next_link );		
		}
		
        $this->html = View::factory('smarty:common/pager', array(
            'p'         => $this->p,
            'total'     => $this->total,
            'per_page'  => $this->per_page,
            'pages'     => $this->pages,
            'base'      => $this->base,
            'link'      => $this->link,
            'hash'      => $hash,
            'fancy'      =>$fancy,
            'from'      => ($this->p - 1) * $this->per_page + 1,
            'to'        => min($this->total, $this->per_page * $this->p)
        ))->render();

        return str_replace('###', $word, $this->html);
    }
}
