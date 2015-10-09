<?php

class Model_File extends ORM {

    protected $_reload_on_wakeup = FALSE;

    protected $_table_name = 'b_file';

    protected $_primary_key = 'ID';

    protected $_table_columns = array(
        'ID' => '', 'TIMESTAMP_X' => '', 'MODULE_ID' => '', 'HEIGHT' => '', 'WIDTH' => '', 'FILE_SIZE' => '',
        'CONTENT_TYPE' => '', 'SUBDIR' => '', 'FILE_NAME' => '', 'ORIGINAL_NAME' => '', 'DESCRIPTION' => '',
    );

    const WATERMARK = 'config/watermark.png';


    /**
     * Сгенерить каталог и имя для файла
     * @param $ext
     * @return array
     */
    public static function name($ext)
    {
        $sub_dir = preg_replace('~([0-9a-f])~', '/$1', substr(md5(time()), 0, 4)); // рандомная директория
        $dir = Upload::$default_directory.$sub_dir;

        if ( ! file_exists($dir)) mkdir($dir, 0777, TRUE); // создадим директорию для картинки

        return array($dir, Text::random().'.'.$ext);
    }

    /**
     * Выдаёт html-код пустой картинки
     */
    public static function empty_image($props = [], $size = NULL)
    {
        if( ! is_null($size) && file_exists(DOCROOT.'/i/no_'.intval($size).'.png')) {
            /* Custom cap size */
            $url = '/i/no_'.intval($size).'.png';
        } else {
            $url = '/i/no.png';
        }
        
        if ($props === 0) return $url;
        return HTML::image(trim($url, '/'), $props);
    }

    /**
     * Получить путь к файлу в ФС
     * @return string
     */
    public function get_path()
    {
        return sprintf('upload/%s/%s', $this->SUBDIR, $this->FILE_NAME);
    }

    /**
     * Получить урл файла
     * @return string
     */
    public function get_url()
    {
        return sprintf($this->get_host().'/upload/%s/%s', $this->SUBDIR, rawurlencode($this->FILE_NAME));
    }

    /**
     * Получить html картинки
     * @param array $props
     * @return string
     */
    public function get_img($props = [])
    {
        if ( ! $this->FILE_NAME) return self::empty_image($props);

        $url = $this->get_url();
        if ($props === 0) return $url;
        return HTML::image(trim($url, '/'), $props);
    }

    /**
     * создать модельку из файла
     * @param $saved_as
     * @return \Model_File
     */
    public static function from_file($saved_as)
    {
        $size = getimagesize($saved_as);
        $path = pathinfo($saved_as);

        $dirname = str_replace('\\','/',$path['dirname']);
        $dirname = preg_replace('~.*/'.Upload::$default_directory.'/~', '', $dirname);

        $f = new Model_File();
        $f->values(array(
            'HEIGHT' => $size[1],
            'WIDTH'	=> $size[0],
            'FILE_SIZE'	=> filesize($saved_as),
            'CONTENT_TYPE' => $size['mime'],
            'SUBDIR' => $dirname,
            'FILE_NAME'	=> $path['basename'],
            'ORIGINAL_NAME' => $saved_as,
        ));
        $f->save();

        return $f;
    }

    /**
     * создать модельку из Imagick
     * @param $imagick Imagick
     * @return \Model_File
     */
    public static function from_imagick($imagick)
    {
        $format = $imagick->getimageformat();

        list($dir, $name) = self::name($format);
        $imagick->writeimage($filename = $dir.'/'.$name);

        $subdir = str_replace('\\','/',$dir);
        $subdir = str_replace(Upload::$default_directory.'/', '', $subdir);
        
        $f = new Model_File();
        $f->values(array(
            'HEIGHT' => $imagick->getimageheight(),
            'WIDTH'	=> $imagick->getimagewidth(),
            'FILE_SIZE'	=> $imagick->getimagelength(),
            'CONTENT_TYPE' => strtolower('image/'.$format),
            'SUBDIR' => $subdir ,
            'FILE_NAME'	=> $name,
            'ORIGINAL_NAME' => $filename,
        ));
        $f->save();

        return $f;
    }

    /**
     * Загрузка картинки и создание модельки для неё
     * @param $i
     * @param array $size
     * @return Model_File|bool
     */
    public static function image($i, $size = [])
    {
        if ( ! Upload::image($_FILES[$i])) return FALSE; // не картинка

        list($dir, $file_name) = self::name(pathinfo($_FILES[$i]['name'], PATHINFO_EXTENSION));

        if ( ! ($saved_as = Upload::save($_FILES[$i], $file_name, $dir)))   return FALSE;

        if ( ! empty($size)) { // resize image
            Image::factory($saved_as)->resize($size[0], $size[1])->save($saved_as);
            clearstatcache($saved_as);
        }

        $f = self::from_file($saved_as);

        return $f;
    }

    /**
     * При удалении модели надо удалить файл из файловой системы
     */
    function delete()
    {
        if ($this->ID AND file_exists($this->get_path())) unlink($this->get_path());
        parent::delete();
    }

    /**
     * Наложение ватермарки на картинку - создание новой картинки
     * @return \Model_File
     */
    function watermark()
    {
        $image = new Imagick($this->get_path());
        
        $image->setImageFormat("jpg");
        
        $watermark_img = new Imagick(APPPATH . self::WATERMARK);
        
        $wm_width  = $watermark_img->getImageWidth();
        $wm_height = $watermark_img->getImageHeight();

        $img_width  = $image->getImageWidth();
        $img_height = $image->getImageHeight();

        $wm_pos_x = intval(($img_width / 2) - ($wm_width / 2));
        $wm_pos_y = intval(($img_height / 2) - ($wm_height / 2));
        
        $image->compositeimage($watermark_img, imagick::COMPOSITE_DEFAULT, $wm_pos_x, $wm_pos_y);
        
        return self::from_imagick($image);
    }

    /**
     * Сгенерить тумбик для файла и сохранить в новый файл
     * @param $width
     * @param $height
     * @return \Model_File
     */
    function resize($width, $height = 0)
    {
		if( $height == 0 )
			$height = $width;
		
		$maxsize = max([$width, $height]);
		
        $im = new Imagick($tmp_name = $this->get_path());
        $im->thumbnailimage($maxsize, $maxsize, TRUE);
		
		if ($width < $maxsize) {
			$im->cropimage($width, $height, intval(($maxsize - $width) / 2), 0);
		} else if($height < $maxsize) {
			$im->cropimage($width, $height, 0, intval(($maxsize - $height) / 2));
		}
		
        return self::from_imagick($im);
    }

    /**
     * Сгенерить тумбик заданной высоты и ширины для файла
     * и сохранить в новый файл
     *
     * @param $width
     * @param $height
     * @return \Model_File
     */
    function resizeWH($width, $height)
    {
        $im = new Imagick($tmp_name = $this->get_path());
        $im->thumbnailimage($width, $height, TRUE);
        return self::from_imagick($im);
    }

    /**
     * Хосты для статики
     * @return string
     */
    function get_host()
    {
        return (Kohana::$environment == Kohana::PRODUCTION) ? 'http://st'.($this->pk() % 10).'.mladenec.ru' : '';
    }

    /**
     * После сохранения файла даём задание демону на оптимизацию
     */
    function save(Validation $validation = NULL)
    {
        parent::save();

        $quest = new Model_Daemon_Quest();
        $quest->values([
            'action'    => 'image',
            'params'    => $this->get_path(),
        ]);
        $quest->save();
        Daemon::new_task();
    }
}
