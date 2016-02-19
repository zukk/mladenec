<?php
class Model_Seostatistics extends ORM {

    protected $_table_name = 'z_seostatistics';

    protected $_table_columns = array(
        'id'                          => '',
        'products_count'              => '',
        'prod_missing_title'          => '',
        'prod_missing_desc'           => '',
        'prod_missing_keywords'       => '',
        'categories_count'            => '',
        'categories_missing_title'    => '',
        'categories_missing_desc'     => '',
        'categories_missing_keywords' => '',
        'tags_count'                  => '',
        'tags_missing_title'          => '',
        'tags_missing_desc'           => '',
        'tags_missing_keywords'       => '',
        'date'                        => ''
    );

    public function get_seostatistics(){

        $date = Request::current()->query('date');
        $presents['error'] = '';
        if(!empty($date)){
            $date = date('Y_m_d', strtotime($date));
            $dir = APPPATH.'../www/export/seo_statistics/';
            $files = scandir($dir, 1);
            $zip = new ZipArchive();
            $zip_name = $dir."seo_statistics.zip";
            if($zip->open($zip_name, ZIPARCHIVE::CREATE) !== TRUE){
                $presents['error'] = "Извините, создание ZIP архива невозможно в настоящее время";
            }
            foreach($files as $file){
                if(strrpos($file, $date) !== FALSE ){
                    $filename = $dir.$file;
                    $zip->addFile($filename, $file); // добавляем файлы в zip архив
                    $zip->deleteName($dir.$file); // добавляем файлы в zip архив
                } else {
                    $presents['error'] = "Нет файлов для выбранной даты";
                }
            }
            $zip->close();
            if(file_exists($zip_name)) {
                header('Content-type: application/zip');
                header('Content-Disposition: attachment; filename="'.$zip_name.'"');
                readfile($zip_name);
                unlink($zip_name);
            }
        }
        $domains = Kohana::$config->load('domains')->as_array();
        $host = $domains['mladenec']['host'];

        $count_row = Request::current()->query('count_row');
        if(!empty($count_row)){
            $count = $count_row;
        } else {
            $count = 20;
        }
        $query = ORM::factory('seostatistics');

        $presents['count_row'] = $count;
        $presents['pager'] = $pager = new Pager($query->count_all(), $count);
        $presents['list'] = $query
            ->limit($pager->per_page)
            ->offset($pager->offset)
            ->order_by('date', 'DESC')
            ->find_all();
        return $presents;
    }
}