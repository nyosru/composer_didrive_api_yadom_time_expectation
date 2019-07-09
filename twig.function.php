<?php



/**
определение функций для TWIG
 */
//creatSecret

//echo __FILE__;
//die();

/**
 * получаем средние значения ожидания с указанной даты
 */
$function = new Twig_SimpleFunction('ApiYadom_time_ex_get_timer_on_sp', function ( $db, string $sp_id, $date_start, $date_fin, $mod_link_time_to_sp = '074.time_expectations_links_to_sp', $mod_data_timer = '074.time_expectations_list' ) {
    
            $ff = $db->prepare('SELECT 
                    mi.id
                FROM 
                    mitems mi
                    
                INNER JOIN `mitems` mi2 ON  mi2.module = :mod_link_time_to_sp AND mi2.status = \'show\'
                INNER JOIN `mitems-dops` mid ON  mid.dops :mod_link_time_to_sp  mid. id_timeserver
                
                WHERE
                    mi.module = :mod_data_timer AND
                    mi.status = \'show\' 
                ');

            $ff->execute(array(
                // ':id_user' => 'f34d6d84-5ecb-4a40-9b03-71d03cb730cb',
//                ':mod_data_timer' => $mod_data_timer,
//                ':mod_link_time_to_sp' => $mod_link_time_to_sp,
//                ':date_start' => ' date(\'' . date('Y-m-d', strtotime( $date_start ) ) .'\') ',
//                ':date_fin' => ' date(\'' . date('Y-m-d', strtotime( $date_fin ) ) .'\') ',
                    // ':date' => ' date(\'' . date('Y-m-d', $_SERVER['REQUEST_TIME'] - 3600*24*3 ) .'\') ',
                    // ':dates' => $start_date //date( 'Y-m-d', ($_SERVER['REQUEST_TIME'] - 3600 * 24 * 14 ) )
            ));
            //$e3 = $ff->fetchAll();

            $sql2 = '';
            while ($e = $ff->fetch()) {
                echo '<br/>123123';
            }
    
    return \Nyos\Nyos::creatSecret($text);
});
$twig->addFunction($function);
