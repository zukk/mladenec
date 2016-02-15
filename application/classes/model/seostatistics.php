<?php
class Model_Seostatistics extends ORM {

    protected $_table_name = 'z_good';

    public function get_seostatistics(){
        $domains = Kohana::$config->load('domains')->as_array();
        $host = $domains['mladenec']['host'];

        $result_title = DB::select('z_good.id', 'z_good.group_name', 'z_good.name')
            ->distinct('z_good.id')
            ->from('z_good')
            ->where('z_good.show', '=', 1)
            ->where('z_good.id', 'NOT IN', DB::select('z_seo.item_id')
                ->from('z_seo')
                ->where('z_seo.title', '!=', ''));
        $presents['title'] = $result_title->execute()->as_array();

        $result_desc = DB::select('z_good.id', 'z_good.group_name', 'z_good.name')
            ->distinct('z_good.id')
            ->from('z_good')
            ->where('z_good.show', '=', 1)
            ->where('z_good.id', 'NOT IN', DB::select('z_seo.item_id')
                ->from('z_seo')
                ->where('z_seo.description', '!=', ''));
        $presents['desc'] = $result_desc->execute()->as_array();

        $result_keywords = DB::select('z_good.id', 'z_good.group_name', 'z_good.name')
            ->distinct('z_good.id')
            ->from('z_good')
            ->where('z_good.show', '=', 1)
            ->where('z_good.id', 'NOT IN', DB::select('z_seo.item_id')
                ->from('z_seo')
                ->where('z_seo.keywords', '!=', ''));
        $presents['keywords'] = $result_keywords->execute()->as_array();

        $dir = APPPATH.'../www/export/sitemap/';
        $file = fopen( $dir . 'seostatistics.xml', 'w' );
        fwrite($file, '<?xml version="1.0" encoding="UTF-8"?>'. "\n". '<?xml-stylesheet type="text/xsl" href="/xml-seostatistics.xsl"?>' . "\n". '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">'."\n");

        foreach($presents['title'] as $keyword_title){
            $line = '<url><loc>http://' . $host . '/od-men/goods/' . $keyword_title['id'] . '</loc></url>'. "\n";
            fwrite($file, $line);

        }
        foreach($presents['desc'] as $keyword_desc){
            $line = '<url><loc>http://' . $host . '/od-men/goods/' . $keyword_desc['id'] . '</loc></url>'. "\n";
            fwrite($file, $line);
        }
        foreach($presents['keywords'] as $keyword_keywords){
            $line = '<url><loc>http://' . $host . '/od-men/goods/' . $keyword_keywords['id'] . '</loc></url>'. "\n";
            fwrite($file, $line);
        }

        fwrite($file, '</urlset>');
        fclose($file);
        return $presents;
    }
}