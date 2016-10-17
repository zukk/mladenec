<?php

/**
 * Крон отсылает письмо с информацией об отзывах к товарам, неактивных 14 и более дней
 */

require('../../../www/preload.php');

$gr = ORM::factory('good_review')
    ->with('good')
    ->where('good.active', '!=', 0)
    ->where('good_review.active', '=', 0)
    ->where('time', '<=', time() - 14 * 24 * 3600) // 14 дней
    ->find_all()
    ->as_array();

$to = Conf::instance()->mail_review;
if ( ! empty($gr) && ! empty($to)) {
    Mail::htmlsend('reviews', array('gr' => $gr), $to, 'Информация об отзывах, не активных более 14 дней');
}