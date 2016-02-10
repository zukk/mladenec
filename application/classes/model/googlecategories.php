<?php
class Model_Googlecategories extends ORM {

    protected $_table_name = 'google_categories';

    protected $_table_columns = [
        'id' => '', 'category_id' => '','parent_id' => '', 'name_cat' => ''
    ];

    /*
     * Запускалось один раз для импорта гугл категорий
     *
     * */
    public function action_export(){

        $file = "d:/web/mladenec.beta_original/beta/taxonomy_with_ids.csv";

        if (($handle = fopen($file, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
                for ($i=0; $i < count($data) ; $i++) {
                    $count = count($data);
                    switch ($count) {
                        case 3:
                            $mass['category_id'] = $data[0];
                            $mass['parent_id'] = 0;
                            $mass['name_cat'] = substr($data[1], 0, 250);
                            $a = $data[0];
                            break;
                        case 4:
                            $mass['category_id'] = $data[0];
                            $mass['parent_id'] = $a;
                            $mass['name_cat'] = substr($data[2], 0, 250);
                            $b = $data[0];
                            break;
                        case 5:
                            $mass['category_id'] = $data[0];
                            $mass['parent_id'] = $b;
                            $mass['name_cat'] = substr($data[3], 0, 250);
                            $c = $data[0];
                            break;
                        case 6:
                            $mass['category_id'] = $data[0];
                            $mass['parent_id'] = $c;
                            $mass['name_cat'] = substr($data[4], 0, 250);
                            $d = $data[0];
                            break;
                        case 7:
                            $mass['category_id'] = $data[0];
                            $mass['parent_id'] = $d;
                            $mass['name_cat'] = substr($data[5], 0, 250);
                            $e = $data[0];
                            break;
                        case 8:
                            $mass['category_id'] = $data[0];
                            $mass['parent_id'] = $e;
                            $mass['name_cat'] = substr($data[6], 0, 250);
                            break;
                    }
                }
            }
            fclose($handle);
        }
    }
}