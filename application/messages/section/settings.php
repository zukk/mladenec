<?php
return array(
    'x' => array(
        Model_Section::SHOW_OUT_OF_STOCK             => 'По умолчанию показывать все товары',
        Model_Section::HIDE_OUT_OF_STOCK             => 'По умолчанию показывать товары в наличии',
        Model_Section::HIDE_OUT_OF_STOCK_STRICTLY    => 'Показывать только товары в наличии',
    ),
    'm' => array(
        0 => 'Показывать товары строками',
        1 => 'Показывать товары плашками',
    ),
    'buy' => array(
        Model_Section::BUY_BUTTON_INCDEC => 'С выбором количества',
        Model_Section::BUY_BUTTON_SIMPLE => 'Простой &laquo;В корзину&raquo;'
    ),
    'new' => array(
        0 => 'Не показывать',
        1 => 'Показывать'
    ),
    'per_page' => array(
        12,
        24,
        48
    ),
    'sort' => array(
        'rating'    => 'По популярности',
        'name'      => 'По названию',
        'price'     => 'По цене (по возрастанию)',
        'pricedesc'     => 'По цене (по убыванию)',
        'new'     => 'По новизне',
    ),
    'sub' => array(
        Model_Section::SUB_NO        => 'нет',
        Model_Section::SUB_BRAND     => 'Показывать бренды',
        Model_Section::SUB_FILTER    => 'Показывать фильтр',
    ),
    'list' => array(
        Model_Section::LIST_GOODS    => 'Показывать товары',
        Model_Section::LIST_FILTER   => 'Показывать фильтр',
        Model_Section::LIST_TEXT     => 'Показывать текст',
    ),
    'listroot' => array(
        Model_Section::LIST_GOODS    => 'Показывать бренды',
        Model_Section::LIST_TEXT     => 'Показывать текст',
    ),
);