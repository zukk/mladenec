<?php

trait Seo {

	protected $seo;

    /**
     * Для seo объектов магический метод
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
		if ($name != 'seo') return parent::__get($name);

        return $this->seo();
	}

    /**
     * @return Model_Seo[]
     */
    protected function seo()
    {
		if ( ! is_object($this->seo)) {
            $this->seo = Model_Seo::findSeo(strtolower(str_replace('Model_', '', get_class())), $this->id);
        }

        return $this->seo;
	}

    /**
     * returns array of errors for messages or null if success
     * @param $values
     * @return array|null
     */
    public function seo_save($values)
    {
	    $this->seo();

		$this->seo->title       = $values['title'];
        $this->seo->description = $values['description'];
        $this->seo->keywords    = $values['keywords'];

        if ($this->seo->validation()->check()) {

            $this->seo->save();

            return NULL;

        } else {

            return ['errors' => $this->seo->validation()->errors('admin/seo')];
        }
	}
}

