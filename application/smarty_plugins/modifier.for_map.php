<?php
/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     modifier.for_map.php
 * Type:     modifier
 * Name:     for_static
 * Purpose:  prepare polygon for yandex map / static api (must have 100 points max)
 * -------------------------------------------------------------
 */
function smarty_modifier_for_map($poly, $use_static = FALSE)
{
    $poly = explode(',', $poly); // получим точки многоугольника

    if ($use_static) {
        $points = count($poly); // сколько точек?
        if ($points > 100) {
            $remove = $points - 100; // сколько точек убираем
            $ratio = $points / $remove;
            for($i = 1; $i <= $remove; $i++) { // выпиливаем точки
                $index = floor($i * $ratio) - 1;
                if ($index == 0) $index += 1; // первую точку не трогаем
                if ($index == $points - 1) $index -=1; // и последнюю тоже
                unset($poly[$index]);
            }
        }

        return str_replace(' ', ',', implode(',', $poly));

    } else {

        return '['.preg_replace('~([0-9\.]+) ([0-9\.]+)~', '$2,$1', implode('],[', $poly)).']'; // и переставить местами координаты
    }
}