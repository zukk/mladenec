<?php

/**
 * Данные о последнем аплоаде в гр есть в таблице getresponse
 * Нам нужно отписать если кто вдруг отписался, и обновить тех у кого uploaded == NULL
 * по умолчанию обрабатывает 1000 человек
 * ключ --count дает число людей но не обрабатывает их
 */
require('../../../www/preload.php');

ob_end_flush();
flush();

$count = FALSE;
$limit = 1000;
if ( ! empty($argv[1])) {
    if ($argv[1] == '--count') $count = TRUE; // выдаем статистику по количеству
    if (ctype_digit($argv[1])) $limit = $argv[1];
}

// Unsubscribe
if ($count) {
    $unsubscribe = DB::select(DB::expr('count(*) as cnt'));
} else {
    $unsubscribe = DB::select('z_user.id', 'z_user.email');
}

$unsubscribe = $unsubscribe->from('z_user')
    ->join('getresponse')
        ->on('z_user.id','=','getresponse.user_id')
    ->where('z_user.sub','=',0);

if ($count) {

    echo 'unsubscribe '.$unsubscribe->execute()->get('cnt')."\n";

} else {

    $unsubscribe = $unsubscribe->limit($limit)
        ->execute()
        ->as_array('id', 'email');

    if ( ! empty($unsubscribe) && ! $count) {

        $gr = new GetResponse();
        foreach ($unsubscribe as $id => $email) {
            $resp = $gr->unsubscribe($email);
            if ($resp === TRUE) {
                $unsubscribed_ids[] = $id;
            }
        }

        if ( ! empty($unsubscribed_ids)) {

            DB::delete('getresponse')
                ->where('user_id', 'IN', $unsubscribed_ids)
                ->execute();

            echo count($unsubscribed_ids)." unsubscribed\n";
        }
    }
// \ Unsubscribe
    echo " unsubscribe finished\n";

}

// добавим новых или обновим тех у кого uploaded = null
if ($count) {

    $user_ids = DB::select( DB::expr('count(z_user.id) as cnt'));

} else {

    $user_ids = DB::select( 'z_user.id', 'z_user.name', 'z_user.email', 'user_segment.*')
        ->distinct(TRUE)
        ->limit($limit);
}

$user_ids = $user_ids
    ->from('z_user')
    ->join('getresponse', 'LEFT')
        ->on('getresponse.user_id', '=', 'z_user.id')
    ->join('user_segment')
        ->on('user_segment.user_id', '=', 'z_user.id')
    ->where('z_user.sub', '=', 1)
    ->where('z_user.email', 'LIKE', '%@%')
    ->where('getresponse.uploaded', 'IS', DB::expr('NULL'));

if ($count) {

    echo 'upload '.$user_ids->execute()->get('cnt')."\n";

} else {

    $user_ids = $user_ids
        ->order_by('z_user.id', 'DESC')
        ->execute()
        ->as_array('id');
}

if ($count) exit();

$uploaded_ids = [];
$gr = new GetResponse();

foreach ($user_ids as $row) {

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
        $row['childs'] = explode(',', $row['childs']);
        if(is_array($row['childs'])) {
            $customs[] = ['name' => 'childs',     'content' => count($row['childs'])];
            for($i=1;$i<=6;$i++) {
                if ( ! isset($row['childs'][$i-1])) break;
                $customs[] = ['name' => 'child_birth_'.$i,     'content' => $row['childs'][$i-1]];
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
    
    $resp = $gr->upload($row, $customs);
    if ($resp === TRUE) {
        $uploaded_ids[] = $row['id'];
    }
}

print( PHP_EOL . count($uploaded_ids) . " contacts uploaded" . PHP_EOL);