<?php defined('SYSPATH') or die('No direct script access.');

return array(
    Model_Event::T_GOOD_ADD         => 'Создание карточки товара',
    Model_Event::T_GOOD_INSTOCK     => 'Появился в наличии на складе',
    Model_Event::T_GOOD_OUTSTOCK    => 'Закончился на складе',
    Model_Event::T_GOOD_APPEAR      => 'Впервые поступил в продажу на сайте',
    Model_Event::T_GOOD_SHOW        => 'ВКЛючено отображение на сайте',
    Model_Event::T_GOOD_HIDE        => 'ОТКЛючено отображение на сайте (скрыт)',
    
    Model_Event::T_ACTION_ACTIVE    => 'Акция активна (ВКЛючилась)',
    Model_Event::T_ACTION_UNACTIVE  => 'Акция неактивна (ОТКЛючилась)',
);