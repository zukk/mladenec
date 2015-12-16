<?php

require('../../../www/preload.php');

$all = FALSE;
if ( ! empty($argv[1]) && $argv[1] == '--all') { // обрабатываем всех юзеров
    $all = TRUE;
}

// Unsubscribe
$unsubscribe = DB::select('z_user.id', 'z_user.email')
    ->from('z_user');

if ( ! $all) { // всех или только тех что есть в сегментах
    $unsubscribe->join('user_segment')->on('z_user.id','=','user_segment.user_id');
}

$unsubscribe = $unsubscribe
    ->where('z_user.sub','=',0)
    ->limit($all ? NULL : 1000)
    ->execute()
    ->as_array('id', 'email');

if ( ! empty($unsubscribe)) {

    $gr = new GetResponse();
    foreach ($unsubscribe as $id => $email) {
        $resp = $gr->unsubscribe($email);
        if ($resp === TRUE) {
            $unsubscribed_ids[] = $id;
        }
    }
    
    if ( ! empty($unsubscribed_ids)) {
        DB::update('z_user')
            ->set(['segments_recount_ts' => 0])
            ->where('id', 'IN', $unsubscribed_ids)
            ->execute();

        DB::delete('user_segment')
            ->where('user_id', 'IN', $unsubscribed_ids)
            ->execute();

        echo count($unsubscribed_ids)." unsubscribed\n";
    }
}
// \ Unsubscribe

// добавим новых или тех кто заказы делал с последнего пересчёта
$user_ids = DB::select( 'z_user.id' )
    ->distinct(TRUE)
    ->from('z_user')
    ->join('z_order')
        ->on('z_order.user_id', '=', 'z_user.id')
    ->where('z_user.sub', '=', 1);

if ( ! $all) {

    $user_ids
        ->where_open()
            ->where('z_user.segments_recount_ts', '=', 0)
            ->or_where(DB::expr('UNIX_TIMESTAMP(z_order.created)'), '>', 'z_user.segments_recount_ts')
        ->where_close()
    ->limit(1000);
}

$user_ids->order_by('z_user.id', 'DESC')
    ->execute()->as_array('id', 'id');

if ( ! empty($user_ids)) {

DB::query(Database::INSERT,
        'INSERT INTO `user_segment` (`user_id`, `pregnant`, `pregnant_terms`, `last_visit`) ' .
        DB::select( ['id','user_id'], 'pregnant', 'pregnant_terms', DB::expr("FROM_UNIXTIME(`last_visit`, '%Y-%m-%d') as `last_visit`"))
            ->from('z_user')
            ->where('id', 'IN', $user_ids)
        . ' ON DUPLICATE KEY UPDATE `last_visit` = VALUES(`last_visit`), `upload_ts` = 0, 
            `user_segment`.`pregnant` = `z_user`.`pregnant`, `user_segment`.`pregnant_terms` = `z_user`.`pregnant_terms`'
        )->execute();

// Counting orders
$orders_cnt = DB::select( 
    'user_id',
    DB::expr('count(`z_order`.`id`) as `orders_count`'),
    DB::expr('SUM(`z_order`.`price`) as `orders_sum`')
)->from('z_order')
    ->join('z_user')->on('z_order.user_id','=','z_user.id')
    ->where('z_user.id', 'IN', $user_ids)
    ->where('z_order.status', '=', 'F')
    ->group_by('z_order.user_id')
    ->order_by('z_user.id','DESC');

DB::query(Database::INSERT,
    'INSERT INTO `user_segment` (`user_id`, `orders_count`, `orders_sum`) '
    . $orders_cnt
    . ' ON DUPLICATE KEY UPDATE `orders_count` = VALUES(`orders_count`), `orders_sum` = VALUES(`orders_sum`)'
)->execute();

// Last orders data
$last_orders = DB::select( 
    'user_id',
    DB::expr("DATE_FORMAT(MAX(`z_order`.`created`), '%Y-%m-%d') as `last_order`"),
    ['price','last_order_sum']
)
    ->from('z_order')
    ->where('z_order.status', '=', 'F')
    ->where('z_order.user_id', 'IN', $user_ids)
    ->group_by('z_order.user_id');

DB::query(Database::INSERT,
    'INSERT INTO `user_segment` (`user_id`, `last_order`, `last_order_sum`) '
    . $last_orders
    . ' ON DUPLICATE KEY UPDATE `last_order` = VALUES(`last_order`), `last_order_sum` = VALUES(`last_order_sum`) '
)->execute();

// big
$big_orders = DB::select( 'user_id', DB::expr("SUM(`z_order_good`.`price` * `z_order_good`.`quantity`) as `sum_big`" ))
    ->from('z_order')
    ->join('z_order_good')->on('z_order.id','=','z_order_good.order_id')
    ->join('z_good')->on('z_good.id','=','z_order_good.good_id')
    ->where('z_order.status', '=', 'F')
    ->where('z_good.big', '=', '1')
    ->where('z_order.user_id', 'IN', $user_ids)
    ->group_by('z_order.user_id');

DB::query(Database::INSERT,
    'INSERT INTO `user_segment` (`user_id`, `sum_big`) '
    . $big_orders
    . ' ON DUPLICATE KEY UPDATE `sum_big` = VALUES(`sum_big`) '
)->execute();

// diaper
$diaper_orders = DB::select( 'user_id', DB::expr("SUM(`z_order_good`.`price` * `z_order_good`.`quantity`) as `sum_big`" ))
    ->from('z_order')
    ->join('z_order_good')->on('z_order.id','=','z_order_good.order_id')
    ->join('z_good')->on('z_good.id','=','z_order_good.good_id')
    ->where('z_order.status', '=', 'F')
    ->where('z_good.section_id', '=', '29798')
    ->where('z_order.user_id', 'IN', $user_ids)
    ->group_by('z_order.user_id');

DB::query(Database::INSERT,
    'INSERT INTO `user_segment` (`user_id`, `sum_big`) '
    . $diaper_orders
    . ' ON DUPLICATE KEY UPDATE `sum_diaper` = VALUES(`sum_diaper`) '
)->execute();

// diaper
$diaper_orders = DB::select( 'user_id', DB::expr("SUM(`z_order_good`.`price` * `z_order_good`.`quantity`) as `sum_big`" ))
    ->from('z_order')
    ->join('z_order_good')->on('z_order.id','=','z_order_good.order_id')
    ->join('z_good')->on('z_good.id','=','z_order_good.good_id')
    ->where('z_order.status', '=', 'F')
    ->where('z_good.section_id', '=', '29798')
    ->where('z_order.user_id', 'IN', $user_ids)
    ->group_by('z_order.user_id');

DB::query(Database::INSERT,
        'INSERT INTO `user_segment` (`user_id`, `sum_diaper`) '
        . $diaper_orders
        . ' ON DUPLICATE KEY UPDATE `sum_diaper` = VALUES(`sum_diaper`) '
        )->execute();

// eat
$eat_orders = DB::select( 'user_id', DB::expr("SUM(`z_order_good`.`price` * `z_order_good`.`quantity`) as `sum_big`" ))
    ->from('z_order')
    ->join('z_order_good')->on('z_order.id','=','z_order_good.order_id')
    ->join('z_good')->on('z_good.id','=','z_order_good.good_id')
    ->where('z_order.status', '=', 'F')
    ->where('z_good.section_id', 'IN', [29065,98670,29150,28985,29293,28935,29253,29051,29138,29413,28968,28962])
    ->where('z_order.user_id', 'IN', $user_ids)
    ->group_by('z_order.user_id');

DB::query(Database::INSERT,
    'INSERT INTO `user_segment` (`user_id`, `sum_eat`) '
    . $eat_orders
    . ' ON DUPLICATE KEY UPDATE `sum_eat` = VALUES(`sum_eat`) '
)->execute();

// toy
$toy_orders = DB::select( 'user_id', DB::expr("SUM(`z_order_good`.`price` * `z_order_good`.`quantity`) as `sum_big`" ))
    ->from('z_order')
    ->join('z_order_good')->on('z_order.id','=','z_order_good.order_id')
    ->join('z_good')->on('z_good.id','=','z_order_good.good_id')
    ->where('z_order.status', '=', 'F')
    ->where('z_good.section_id', 'IN', [29585,31542,31541,116972,29562,31341,116970,57185,43630,116971,116969,88586,116957,119577])
    ->where('z_order.user_id', 'IN', $user_ids)
    ->group_by('z_order.user_id');

DB::query(Database::INSERT,
        'INSERT INTO `user_segment` (`user_id`, `sum_toy`) '
        . $toy_orders
        . ' ON DUPLICATE KEY UPDATE `sum_toy` = VALUES(`sum_toy`) '
        )->execute();

// care
$care_orders = DB::select( 'user_id', DB::expr("SUM(`z_order_good`.`price` * `z_order_good`.`quantity`) as `sum_big`" ))
    ->from('z_order')
    ->join('z_order_good')->on('z_order.id','=','z_order_good.order_id')
    ->join('z_good')->on('z_good.id','=','z_order_good.good_id')
    ->where('z_order.status', '=', 'F')
    ->where('z_good.section_id', 'IN', [28856,28628,28783,28719,28836,53850,28682,28704])
    ->where('z_order.user_id', 'IN', $user_ids)
    ->group_by('z_order.user_id');

DB::query(Database::INSERT,
        'INSERT INTO `user_segment` (`user_id`, `sum_care`) '
        . $care_orders
        . ' ON DUPLICATE KEY UPDATE `sum_care` = VALUES(`sum_care`) '
        )->execute();

// dress
$dress_orders = DB::select( 'user_id', DB::expr("SUM(`z_order_good`.`price` * `z_order_good`.`quantity`) as `sum_big`" ))
    ->from('z_order')
    ->join('z_order_good')->on('z_order.id','=','z_order_good.order_id')
    ->join('z_good')->on('z_good.id','=','z_order_good.good_id')
    ->where('z_order.status', '=', 'F')
    ->where('z_good.section_id', 'IN', [105926,105927,105928,105929,115704,98250])
    ->where('z_order.user_id', 'IN', $user_ids)
    ->group_by('z_order.user_id');

DB::query(Database::INSERT,
        'INSERT INTO `user_segment` (`user_id`, `sum_dress`) '
        . $dress_orders
        . ' ON DUPLICATE KEY UPDATE `sum_dress` = VALUES(`sum_dress`) '
        )->execute();

DB::query(Database::UPDATE,
        'UPDATE `user_segment`'
        . 'SET `user_segment`.`arpu` = IF (`user_segment`.`orders_count` > 0, `user_segment`.`orders_sum` / `user_segment`.`orders_count`, 0) '
        . 'WHERE `user_segment`.`user_id` IN (' . implode(',', $user_ids) . ') '
        )->execute();

DB::update('z_user')->set( [ 'segments_recount_ts' => time() ] )
        ->where('id', 'IN', $user_ids)
        ->execute();

//данные по детям
$childs =  DB::select( 'user_id', 'birth', 'sex')
        ->from('z_user_child')
        ->where('z_user_child.user_id', 'IN', $user_ids)
        ->execute()
        ->as_array();

    if (count($childs) > 0) {
        $childs_in = [];
        foreach ($childs as $child) {            
            $child = (object) $child;
            $childs_in[$child->user_id]['user_id'] = $child->user_id;            
            $childs_in[$child->user_id]['childs'][]  = ['birth'=>$child->birth, 'sex'=>$child->sex];
            if(!isset($childs_in[$child->user_id]['has_boy']) &&
               !isset($childs_in[$child->user_id]['has_girl'])) {
                $childs_in[$child->user_id]['has_boy'] = 0;
                $childs_in[$child->user_id]['has_girl'] = 0;
            }
            if($child->sex == 1) {
                $childs_in[$child->user_id]['has_boy'] = 1;
            } else {
                $childs_in[$child->user_id]['has_girl'] = 1;
            }
            if(!isset($childs_in[$child->user_id]['child_birth_max']) 
                || strtotime($child->birth) > strtotime($childs_in[$child->user_id]['child_birth_max']) ) {
                $childs_in[$child->user_id]['child_birth_max'] = $child->birth;                
            }
            if(!isset($childs_in[$child->user_id]['child_birth_min']) 
                || strtotime($child->birth) < strtotime($childs_in[$child->user_id]['child_birth_min']) ) {
                $childs_in[$child->user_id]['child_birth_min'] = $child->birth;                
            }
        }
        $insert_stmt = '';
        $count_items = 0;
        foreach ($childs_in as $k=>$child) {
            $child['childs'] = "'".json_encode($child['childs'])."'";
            $insert_stmt .= '(';
            $insert_stmt .= implode(',', $child);
            $insert_stmt .= ')';
            if(++$count_items != count($childs_in)) {
                $insert_stmt .= ",\n";
            }
        }
        DB::query(Database::INSERT, 'INSERT INTO `user_segment` (`user_id`, `childs`, `has_girl`, `has_boy`, `child_birth_min`, `child_birth_max`) VALUES '
                . $insert_stmt
                . ' ON DUPLICATE KEY UPDATE `childs` = VALUES(`childs`), `has_girl` = VALUES(`has_girl`), `has_boy` = VALUES(`has_boy`), `child_birth_min` = VALUES(`child_birth_min`), `child_birth_max` = VALUES(`child_birth_max`)'
        )->execute();
    }
}
if ($all) {
    exit('ALL DONE, CHECK SEGMENTS');
}
$data = DB::select('z_user.id', 'z_user.name', 'z_user.email','user_segment.*' )
    ->from(  'z_user' )
    ->join(  'user_segment')->on('z_user.id','=','user_segment.user_id' )
    ->where( 'z_user.sub','=',1 )
    ->order_by('user_segment.upload_ts','ASC')
    ->limit( $all ? NULL : 1000)
    ->execute()->as_array();

$uploaded_ids = [];

foreach ($data as $row) {

    $name  = $row['name'];
    $email = $row['email'];
    
    $customs = [];
    $customs[] = ['name' => 'md5', 'content' => md5(Cookie::$salt.$email)];
    
    if ( ! empty($row['last_visit']))     $customs[] = ['name' => 'last_visit',     'content' => $row['last_visit']];
    if ( ! empty($row['last_order']))     $customs[] = ['name' => 'last_order',     'content' => $row['last_order']];
    if ( ! empty($row['arpu']))           $customs[] = ['name' => 'arpu',           'content' => $row['arpu']];
    if ( ! empty($row['last_order_sum'])) $customs[] = ['name' => 'last_order_sum', 'content' => $row['last_order_sum']];
    if ( ! empty($row['orders_count']))   $customs[] = ['name' => 'orders_count',   'content' => $row['orders_count']];
    if ( ! empty($row['orders_sum']))     $customs[] = ['name' => 'orders_sum',     'content' => $row['orders_sum']];
    if ( ! empty($row['sum_big']))        $customs[] = ['name' => 'sum_big',        'content' => $row['sum_big']];
    if ( ! empty($row['sum_diaper']))     $customs[] = ['name' => 'sum_diaper',     'content' => $row['sum_diaper']];
    if ( ! empty($row['sum_eat']))        $customs[] = ['name' => 'sum_eat',        'content' => $row['sum_eat']];
    if ( ! empty($row['sum_toy']))        $customs[] = ['name' => 'sum_toy',        'content' => $row['sum_toy']];
    if ( ! empty($row['sum_care']))       $customs[] = ['name' => 'sum_care',       'content' => $row['sum_care']];
    if ( ! empty($row['sum_dress']))      $customs[] = ['name' => 'sum_dress',      'content' => $row['sum_dress']];
    if( $row['pregnant_terms'] > 0 ) {
        $row['pregnant_week'] = floor((time()-$row['pregnant_terms']) / (7*24*60*60));
        $row['pregnant_week'] = ($row['pregnant_week'] <=41) ? $row['pregnant_week'] : 0;
    }
    if ( ! empty($row['pregnant_week']))      $customs[] = ['name' => 'pregnant_week',      'content' => $row['pregnant_week']];
    if ( ! empty($row['childs']))             {
        $row['childs'] = json_decode($row['childs']);
        if(is_array($row['childs'])) {
            $customs[] = ['name' => 'childs',     'content' => count($row['childs'])];
            for($i=1;$i<=6;$i++) {
                if ( ! isset($row['childs'][$i-1])) break;
                $customs[] = ['name' => 'child_birth_'.$i,     'content' => $row['childs'][$i-1]->birth];
            }
        } else $customs[] = ['name' => 'childs',     'content' => 0];
        //print_r($customs);
    }
    if ( ! empty($row['child_birth_min']))     $customs[] = ['name' => 'child_birth_min',     'content' => $row['child_birth_min']];
    if ( ! empty($row['child_birth_min']))     $customs[] = ['name' => 'child_birth_min',     'content' => $row['child_birth_max']];
    
    $customs[] = [
        'name' => 'buy_big', 
        'content' => empty($row['sum_big'])    ? 'нет' : 'да'
    ];
    $customs[] = [
        'name' => 'buy_diaper', 
        'content' => empty($row['sum_diaper']) ? 'нет' : 'да'
    ];
    $customs[] = [
        'name' => 'buy_eat', 
        'content' => empty($row['sum_eat'])    ? 'нет' : 'да'
    ];
    $customs[] = [
        'name' => 'buy_toy', 
        'content' => empty($row['sum_toy'])    ? 'нет' : 'да'
    ];
    $customs[] = [
        'name' => 'buy_care', 
        'content' => empty($row['sum_care'])   ? 'нет' : 'да'
    ];
    $customs[] = [
        'name' => 'buy_dress', 
        'content' => empty($row['sum_dress'])  ? 'нет' : 'да'
    ];
    $customs[] = [
        'name' => 'is_pregnant', 
        'content' => empty($row['is_pregnant'])  ? 'нет' : 'да'
    ];
    $customs[] = [
        'name' => 'has_boy', 
        'content' => empty($row['has_boy'])  ? 'нет' : 'да'
    ];
    $customs[] = [
        'name' => 'has_girl', 
        'content' => empty($row['has_girl'])  ? 'нет' : 'да'
    ];
    
    $resp = $gr->upload($name, $email, $customs);
    if ($resp === TRUE) {
        $uploaded_ids[] = $row['id'];
    }
}

if ( ! empty ($uploaded_ids)) {

    DB::update('user_segment')
        ->set(['upload_ts' => time()])
        ->where('user_id', 'IN', $uploaded_ids)
        ->execute();
}

print( PHP_EOL . count($uploaded_ids) . " contacts uploaded" . PHP_EOL);