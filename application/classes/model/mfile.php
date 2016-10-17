<?php

/**
 * Description of mfile
 *
 * @author mit08
 */
class Model_Mfile extends ORM {

    /**
     * Ширина превьюшки
     */
    const THUMB_WIDTH = '100';
    
    /**
     * Высота превьюшки
     */
    const THUMB_HEIGHT = '100';
    
    /**
     * Глубина вложенности подкаталогов хранилища
     */
    const STORAGE_DEPTH = '4';
    
    /**
     * Адрес хранилища от DOCROOT, т.е. www корня сайта
     */
    const STORAGE_ALIAS = 'upload/mediafiles';
    
    /**
     * Символы, допустимые в именах каталогов и файлов хранилища
     */
    protected static $storage_chunk_names = array('0','1','2','3','4','5','6','7','8','9','a','b','c','d','e','f');
    
    protected $_table_name = 'z_mfile';

    protected $_table_columns = array(
        'id' => '',
        /* id виртуальной директории */
        'mdir_id'=>'',
        /* директория хранилища где физически лежит файл*/
        'subdir'=>'',
        'name' => '',
        /* расширение файла */
        'ext' => '',
        'comment' => '',
        'size'=>'',
        'created_ts'=>''
    );
    
    public function get_thumb($html = true) {
        
        $thumb_url = '';
        $ext = strtolower($this->ext);
        switch($ext) {
            case 'jpg':
            case 'jpeg':
            case 'png':
            case 'gif':
                $thumb_dir = $this->get_dir() . '/.thumb';
                $thumb_filename = $this->id . '.' . $this->ext;
                if ( ! file_exists($thumb_dir . '/' .$thumb_filename)) {
                    /* This gets an _absolute_ path and returns _relative_ from site root */
                    $thumb_url = $this->make_thumb_img($thumb_dir, $thumb_filename);
                } else {
                    $thumb_url = self::STORAGE_ALIAS . '/' . $this->get_dir( FALSE ) . '/.thumb/'.$thumb_filename;
                }
                break;
            case 'doc':
            case 'docx':
            case 'rtf':
                $thumb_url = 'i/filemanager/icon_word.png';
                break;
            case 'xls':
            case 'xlsx':
                $thumb_url = 'i/filemanager/icon_excel.png';
                break;
            case 'ppt':
            case 'pptx':
                $thumb_url = 'i/filemanager/icon_powerpoint.png';
                break;
            case 'pdf':
                $thumb_url = 'i/filemanager/icon_pdf.png';
                break;
            case 'zip':
            case 'rar':
            case '7z':
            case 'gz':
            case 'tgz':
                $thumb_url = 'i/filemanager/icon_arhive.png';
                break;
            default:
                $thumb_url = 'i/filemanager/icon_file.png';
        }
        
        return $html ? html::image($thumb_url) : $thumb_url;
    }
    
    protected function make_thumb_img ($thumb_dir, $thumb_filename) {
        
        $filename           = $this->get_path();
        $thumb_url          = self::STORAGE_ALIAS . '/' . $this->get_dir( FALSE ) . '/.thumb/' . $thumb_filename;
        $thumb_abs_filename = $thumb_dir . '/' .$thumb_filename;
        
        if ( ! file_exists($filename)) { // А есть ли файл?
            Log::instance()->add(Log::INFO,'Trying to create thumbnail for a missing file ' . $this->id);
            return 'i/filemanager/icon_error.png';
        }
        
        list($w, $h) = getimagesize($filename);

        if( ! file_exists($thumb_dir)) { // Есть ли уже директория превьюшек?
            mkdir($thumb_dir, 0755, TRUE);
        }
        

        if (is_writable($thumb_dir)) {
            if(($w > self::THUMB_WIDTH) OR ($h > self::THUMB_HEIGHT)) { // Надо ли ресайзить?
                $thumb = Image::factory($filename,'Imagick');

                $thumb->resize(self::THUMB_WIDTH, self::THUMB_HEIGHT, Image::AUTO);
                $thumb->save($thumb_abs_filename);
            } else { // Нет смысла менять размер, копируем
                copy( $filename, $thumb_abs_filename);
            }
        } else {
            Log::instance()->add(Log::INFO, 'Unable to generate a thumb - unable to write at '.$thumb_dir);
            $thumb_url = '/i/filemanager/icon_file.png';
        }
        return $thumb_url;
    }


    /**
     * 
     * @param string $name original filename
     * @param string $type mime type of file
     * @param string $tmp_name
     * @param int $size filesize in bytes
     * @param int $mdir_id
     * @return \self
     */
    public static function from_upload($file, $mdir_id = 0) {
        $name = $file['name'];
        $ext  = pathinfo($name,PATHINFO_EXTENSION);
        $size = $file['size'];
        
        $f = new self();
        $f->values(array(
            'mdir_id'    => $mdir_id,
            'name'       => $name,
            'ext'        => $ext,
            'size'       => $size,
            'created_ts' => time()
        ));
        ob_start();
        $id = $f->save();
        /* var_dump($id); */
        $store_dir = $f->get_dir(TRUE);
        
        if ( ! file_exists($store_dir)) {
            /* Creating nessesary directories */
            if ( ! mkdir($store_dir, 0777, TRUE)) throw new Exception('Unable to create storage directory '. $store_dir);
        }

        $saved_as = Upload::save($file, $id . '.' . $ext, $store_dir);
        
        Log::instance()->add(Log::INFO,ob_get_clean());
        $f->subdir =  $f->get_dir( FALSE );
        $f->save();
        
        return $f;
    }

    /**
     *
     * @param boolean $html
     * @return string
     */
    public function get_link($html = TRUE) {
        
        $href = '/'.self::STORAGE_ALIAS.'/'.$this->get_path( FALSE );
        
        return $html ? HTML::anchor($href, $this->name) : $href;
    }
    
    /**
     * 
     * @param boolean $html
     */
    public function get_link_admin($html = TRUE) {
        
    }

    /**
     * Получить путь к файлу
     *
     * @param bool $absolute
     * @internal param bool $name Вернуть абсолютный путь, иначе путь от корня хранилища, вместе с именем файла
     * @return string
     */
    public function get_path($absolute = TRUE) {
        
        $dir = $this->get_dir($absolute);
        
        return $dir . '/' . $this->id . ($this->ext ? ('.' . $this->ext) : '');
    }
    /**
     * Получить путь к директории в которой хранится файл
     * 
     * @param Boolean $name Вернуть абсолютный путь, иначе путь от корня хранилища
     * @return string
     */
    public function get_dir($absolute = TRUE) {
        $root = $absolute ? (self::get_storage_root().'/') : '';
        return $root . $this->id_to_subdir($this->id);
    }
    
    public function save(Validation $validation = NULL) {
        
        $subdir = $this->subdir;
        if ( empty($subdir) AND $this->loaded()) {
            $this->subdir = self::id_to_subdir($this->id);
        }
        
        $id = parent::save($validation = NULL);
        
        $mdir = NULL;
        if ( $this->mdir_id > 0) {
            $mdir = ORM::factory('mdir', $this->mdir_id);
            if($mdir->loaded()) {
                $mdir->files_count = Model_Mdir::recount_files($mdir->id);
                $mdir->save();
            } else {
                Log::instance()->add(Log::INFO, 'Unable to update files count in a non-existent directory');
            }
        }
        return $id;
    }
    
    public function delete() {
        $mdir = NULL;
        if ( $this->mdir_id > 0) {
            $mdir = ORM::factory('mdir', $this->mdir_id);
        }
        
        /* Deleting files */
        if ( file_exists($this->get_path())) {
            unlink($this->get_path());
        }
        
        $thumb_path = $this->get_dir() . '/.thumb/' . $this->id . '.' . $this->ext;
        if ( file_exists($thumb_path)) {
            unlink($thumb_path);
        }
        
        parent::delete();
        
        if ( ! is_null($mdir) AND $mdir->loaded()) {
            $mdir->files_count = Model_Mdir::recount_files($mdir->id);
            $mdir->save();
        } else {
            Log::instance()->add(Log::INFO, 'Deleting an Mfile into non-existent Mdirectory');
        }
    }
    
    /**
     * Получить имя поддиректории хранилища по id
     * 
     * @param type $id
     */
    protected static function id_to_subdir($id) {
        /* float так как есть ошибка в вычислениях при числах > PHP_MAX_INT */
        $hex_id = dechex( (float) $id);
                
        $id_str = self::zeropad($hex_id,self::STORAGE_DEPTH);
        
        
        $subdir_arr = str_split(substr($id_str, -self::STORAGE_DEPTH));
        
        $subdir = implode('/',$subdir_arr);
        
        return $subdir;
    }
    
    /**
     * Добавляет необходимое количество нулей в начало строки
     * 
     * @param int $num
     * @param int $lim
     * 
     * @return string
     */
    protected static function zeropad($num, $lim)
    {
        return str_pad ($num, $lim, '0', STR_PAD_LEFT);
    }
    
    /**
     * Возвращает директорию, где непосредственно находится хранилище
     * 
     * @return string Путь к директории от корня сервера
     */
    protected static function get_storage_root() {
        return( DOCROOT . self::STORAGE_ALIAS );
    }
    
    /**
     * 
     */
    protected function is_image() {
        
    }
}
