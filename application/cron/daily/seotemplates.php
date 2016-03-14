<?php
/**
 * Скрипт для генерации шаблонов для SEO
 * Запускать раз в сутки
 */
require(__DIR__.'/../../../www/preload.php');

$del_products_from_seo = DB::delete('z_seo')
    ->where('z_seo.item_id', 'NOT IN', DB::select('z_good.id')
        ->from('z_good')
        ->where('z_good.seo_auto', '=', 0))
    ->where('z_seo.type', '=', 4)
    ->execute();

$all_products = DB::select(
    'z_good.id',
    'z_good.price',
    DB::expr('z_good.name as good_name'),
    DB::expr('z_group.name as group_name'),
    DB::expr('z_section.name as section_name'),
    DB::expr('z_brand.name as brand_name'),
    DB::expr('z_country.name as country_name'))
    ->from('z_good')
    ->where('z_good.show', '=', 1)
    ->where('z_good.seo_auto', '=', 1)
    ->join('z_group')
    ->on('z_good.group_id', '=', 'z_group.id')
    ->join('z_section')
    ->on('z_good.section_id', '=', 'z_section.id')
    ->join('z_brand')
    ->on('z_good.brand_id', '=', 'z_brand.id')
    ->join('z_country')
    ->on('z_good.country_id', '=', 'z_country.id')
    ->execute()
    ->as_array();

$all_templates = DB::select('z_seotemplates.id', 'z_seotemplates.title', 'z_seotemplates.rule', 'z_seotemplates.type', 'z_seotemplates.active')
    ->from('z_seotemplates')
    ->where('z_seotemplates.active', '=', 1)
    ->execute()
    ->as_array();

foreach($all_products as $product) {
    $rule = array();
    foreach($all_templates as $template) {
        $rule[] = $template['rule'];
    }
    $query = DB::insert('z_seo')
        ->columns(array('title', 'description', 'keywords', 'item_id', 'type'));

    $rule = $rule[array_rand($rule)];
    $rule = str_replace('[group]', $product['group_name'], $rule);
    $rule = str_replace('[section]', $product['section_name'], $rule);
    $rule = str_replace('[brand]', $product['brand_name'], $rule);
    $rule = str_replace('[country]', $product['country_name'], $rule);
    $rule = str_replace('[price]', $product['price'], $rule);

    $query->values(array($rule, '', '', $product['id'], 4));
    $query->execute();
}