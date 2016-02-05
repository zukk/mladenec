<?php

class Model_File extends ORM {

    protected $_reload_on_wakeup = FALSE;

    protected $_table_name = 'b_file';

    protected $_primary_key = 'ID';

    protected $_table_columns = [
        'ID' => '',
        'TIMESTAMP_X' => '', // дата загрузки
        'MODULE_ID' => '', // модель для которой загружается картинка
        'SUBDIR' => '', // путь к файлу
        'FILE_NAME' => '', // имя файла
        'original' => '', // ссылка на оригинал если картинка - обработанный оригинал
        'item_id' => '', // ссылка на объект для которого картинка
    ];

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

        return [$dir, Text::random().'.'.$ext];
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
        $f->values([
            'CONTENT_TYPE' => $size['mime'],
            'SUBDIR'    => $dirname,
            'FILE_NAME' => $path['basename'],
        ]);
        $f->save();

        return $f;
    }

    /**
     * создать модельку из Imagick
     * @param $imagick Imagick
     * @param $original - id оригинала картинки
     * @return \Model_File
     */
    public static function from_imagick($imagick, $original = 0)
    {
        $format = $imagick->getimageformat();

        list($dir, $name) = self::name($format);
        $imagick->writeimage($filename = $dir.'/'.$name);

        $subdir = str_replace('\\','/',$dir);
        $subdir = str_replace(Upload::$default_directory.'/', '', $subdir);
        
        $f = new Model_File();
        $f->values([
            'SUBDIR'    => $subdir ,
            'FILE_NAME'	=> $name,
            'original'  => $original,
        ]);
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
        
        return self::from_imagick($image, $this->ID);
    }

    /**
     * Сгенерить тумбик для файла и сохранить в новый файл
     * @param $width
     * @param $height
     * @param $original - id оригинальной картинки
     * @return \Model_File
     */
    function resize($width, $height = 0, $original = FALSE)
    {
		$maxsize = max([$width, $height]);
		
        $im = new Imagick($tmp_name = $this->get_path());
        $im->thumbnailimage($maxsize, $maxsize, TRUE);
		
		if ($width < $maxsize) {
			$im->cropimage($width, $height, intval(($maxsize - $width) / 2), 0);
		} elseif ($height < $maxsize) {
			$im->cropimage($width, $height, 0, intval(($maxsize - $height) / 2));
		}

        if ($original == FALSE) $original = $this->ID;
        return self::from_imagick($im, $original);
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
        return self::from_imagick($im, $this->ID);
    }

    /**
     * Хосты для статики
     * @return string
     */
    function get_host()
    {
        return 'http://st'.($this->pk() % 10).'.mladenec.ru';
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
