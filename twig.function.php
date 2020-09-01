<?php

/**
  определение функций для TWIG
 */
//creatSecret
//echo __FILE__;
//die();

/**
 * получаем средние значения ожидания с указанной даты по дату в точке продаж
 */
$function = new Twig_SimpleFunction('ApiYadom_time_ex_get_timer_on_sp', function ( $db, string $sp_id, $date_start, $date_fin ) {

    // echo __LINE__;
    $return = \Nyos\api\JobExpectation::getTimerExpectation(  $db, $sp_id, $date_start, $date_fin );

    return $return;
});
$twig->addFunction($function);
