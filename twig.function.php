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

    //echo __FILE__.' ['.__LINE__.']';
    //$return = \Nyos\mod\items::getItemsSimple($db, '074.time_expectations_list');

    //    // $return = \Nyos\api\JobExpectation::getTimerExpectation(  $db, $sp_id, $date_start, $date_fin );
    return \Nyos\api\JobExpectation::getTimerExpectation(  $db, $sp_id, $date_start, $date_fin );
//    $return = \Nyos\api\JobExpectation::getTimerExpectation(  $db, $sp_id, $date_start, $date_fin );
    //\f\pa($return,2,'','$return');
    
//    $return = \Nyos\mod\items::::$sql_itemsdop2_add_where(
//            ' INNER '
//            );
    
    //\f\pa($return,2,'','$return');
//    return $return;
});
$twig->addFunction($function);
