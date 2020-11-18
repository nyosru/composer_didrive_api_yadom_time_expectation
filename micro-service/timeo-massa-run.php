<?php

/**
 * для сканирования 1 даты добавте ?scan_date=2020-05-31
 */
ini_set('display_errors', 'On'); // сообщения с ошибками будут показываться
error_reporting(E_ALL); // E_ALL - отображаем ВСЕ ошибки

if ($_SERVER['HTTP_HOST'] == 'photo.uralweb.info' || $_SERVER['HTTP_HOST'] == 'yapdomik.uralweb.info' || $_SERVER['HTTP_HOST'] == 'a2.uralweb.info' || $_SERVER['HTTP_HOST'] == 'adomik.uralweb.info' || $_SERVER['HTTP_HOST'] == 'adomik.dev.uralweb.info'
) {
    date_default_timezone_set("Asia/Omsk");
} else {
    date_default_timezone_set("Asia/Yekaterinburg");
}

if (isset($skip_start) && $skip_start === true) {
    
} else {
    require_once '0start.php';
    $skip_start = false;
}



//define('IN_NYOS_PROJECT', true);
//
//// ini_set("max_execution_time", 120);
////ini_set("max_execution_time", 59);
////header("Cache-Control: no-store, no-cache, must-revalidate");
////header("Expires: " . date("r"));
//
//require $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
//require( $_SERVER['DOCUMENT_ROOT'] . '/all/ajax.start.php' );
//echo $_SERVER['HTTP_HOST'];
//\f\pa($_REQUEST,null,null,'$_REQUEST');

try {

    $sps = \Nyos\mod\items::get($db, \Nyos\mod\JobDesc::$mod_sale_point, 'show', 'id_id' );

    if (strpos($_SERVER['HTTP_HOST'], 'dev') !== false)
        \f\pa($sps, 2, '', '$sps');

//    if (1 == 2) {
////    ob_start('ob_gzhandler');
//        echo '<table><tr><td valign="top" >';
////echo '111111';
////    flush();
//        // \f\timer_start(231);
//        \Nyos\mod\items::$cancel_cash = true;
//        \Nyos\mod\items::$timer_show = true;
//        $sps = \Nyos\mod\items::get($db, \Nyos\mod\JobDesc::$mod_sale_point);
//        // echo '<br/>1 ' . \f\timer_stop(231);
//        \f\pa($sps);
////
////    echo '</td><td valign="top" >';
////flush();
////    \f\timer_start(23);
////    \Nyos\mod\items::$cancel_cash = true;
////    $sps = \Nyos\mod\items::get2($db, \Nyos\mod\JobDesc::$mod_sale_point,'show','sort_asc');
////    echo '<br/>2 ' . \f\timer_stop(23);
////    \f\pa($sps);
////    echo '</td><td valign="top" >';
//// flush();
////    \f\timer_start(237);
////    
////    \Nyos\mod\items::$cancel_cash = true;
////    $sps = \Nyos\mod\items::get3($db, \Nyos\mod\JobDesc::$mod_sale_point,'show','sort_asc');
////    \f\pa($sps,2,'','sps');
////    
////    echo '<br/>2 ' . \f\timer_stop(237);
//        echo '</td></tr></table>';
//        exit;
//    }
//
//    $sps_link_timeo = \Nyos\mod\items::get($db, \Nyos\mod\JobDesc::$mod_sp_link_timeo);
//    \f\pa($sps_link_timeo, 2, '', '$sps_link_timeo');
//
//    \f\timer_start(3);
//    
//
//if ( !isset($_REQUEST['scan_date']) && round(date('d', strtotime($_SERVER['REQUEST_TIME'])),0) <= 2 ) {
//    
//    echo '<br/>#'.__LINE__;
//    
//    foreach ($sps as $k => $v) {
//    $u = [
//        'sp' => $v['id'],
//        // если пишем всё по новой
//        // 'delete_old' => 'da',
//        // пропустить отправку сообщения
//        'scan_date' => date('Y-d-m', strtotime($_SERVER['REQUEST_TIME'] . ' -1 day')),
//        'skip_send_msg' => 'da',
//        'date' => (!empty($_REQUEST['scan_date']) ? date('Y-m-d', strtotime($_REQUEST['scan_date'])) : date('Y-m-d') )
//    ];
//
//    // echo '<br/>+++'.$sps[$v['id']]['head'];
//    $we = file_get_contents('http://' . $_SERVER['HTTP_HOST'] . '/vendor/didrive_api/yadom_time_expectation/ajax.load_and_save_timeo.php?' . http_build_query($u));
//    \f\pa($we);
//    $u = [
//        'sp' => $v['id'],
//        // если пишем всё по новой
//        // 'delete_old' => 'da',
//        // пропустить отправку сообщения
//        'scan_date' => date('Y-d-m', strtotime($_SERVER['REQUEST_TIME'] . ' -2 day')),
//        'skip_send_msg' => 'da',
//        'date' => (!empty($_REQUEST['scan_date']) ? date('Y-m-d', strtotime($_REQUEST['scan_date'])) : date('Y-m-d') )
//    ];
//
//    // echo '<br/>+++'.$sps[$v['id']]['head'];
//    $we = file_get_contents('http://' . $_SERVER['HTTP_HOST'] . '/vendor/didrive_api/yadom_time_expectation/ajax.load_and_save_timeo.php?' . http_build_query($u));
//    \f\pa($we);
//    }
//    
//}
//
//
//    








    $msg = 'грузим время ожидания';

    foreach ($sps as $k => $v) {

        // \f\pa($v);
//        if (!isset($link_sp_timeosp[$v['id']]))
//            continue;
        // $temp_var = 'last_run__ajax.load_and_save_timeo.php__3sp' . $v['id'];
        $last_run = false;

//        if (!empty($temp_var))
//            $last_run = \f\Cash::getVar($temp_var);

        if ($last_run !== false) {

            //$msg .= PHP_EOL . ' - - пропуск';
            // $msg .= PHP_EOL . $sps[$v['id']]['head'].' --';
        } else {

            $msg .= PHP_EOL . $sps[$v['id']]['head'];

            $u = [
                'sp' => $v['id'],
                // если пишем всё по новой
                // 'delete_old' => 'da',
                
                // показ инфы
                'show' => 'table',
                
                // пропустить отправку сообщения
                'skip_send_msg' => 'da',
                'date' => (!empty($_REQUEST['scan_date']) ? date('Y-m-d', strtotime($_REQUEST['scan_date'])) : date('Y-m-d') )
            ];

            // \f\pa($u);
            
            // echo '<br/>+++'.$sps[$v['id']]['head'];
            //$link = 'http://' . $_SERVER['HTTP_HOST'] . '/vendor/didrive_api/yadom_time_expectation/ajax.load_and_save_timeo.php?' . http_build_query($u);
            $link = 'http://' . $_SERVER['HTTP_HOST'] . '/vendor/didrive_api/yadom_time_expectation/micro-service/timeo-load-save.php?' . http_build_query($u);

// action=calc_mont_sp&sp=3051&2return=html-small';

            if ($curl = curl_init()) { //инициализация сеанса
// $curl
// curl_setopt($curl, CURLOPT_URL, 'http://webcodius.ru/'); //указываем адрес страницы
//указываем адрес страницы
                
                curl_setopt($curl, CURLOPT_URL, $link);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                
// curl_setopt ($curl, CURLOPT_POST, true);
// curl_setopt ($curl, CURLOPT_POSTFIELDS, "i=1");
                
                curl_setopt($curl, CURLOPT_HEADER, 0);
                $result = curl_exec($curl); //выполнение запроса
                // \f\pa( json_decode($result,true), '', '', 'result');

                if (strpos($_SERVER['HTTP_HOST'], 'dev') !== false)
                    \f\pa($result, '', '', 'result');

                $res = json_decode($result, true);
                // \f\pa(json_decode($result,true), '', '', 'result2');

                if (strpos($_SERVER['HTTP_HOST'], 'dev') !== false)
                    \f\pa($res, '', '', 'result2');


                if (!empty($res['new']))
                    foreach ($res['new'] as $q => $w) {
                        $msg .= PHP_EOL . $w['date'] . ' / ' . ( $w['cold'] ?? '-' ) . ' ' . ( $w['hot'] ?? '-' ) . ' ' . ( $w['delivery'] ?? '-' );
                    }

                curl_close($curl); //закрытие сеанса
                // \f\Cash::setVar($temp_var, 1, ( $time_expire ?? 60 * 60 * 1));
            }

            $ee = \f\timer_stop(3, 'ar');
            if (strpos($_SERVER['HTTP_HOST'], 'dev') !== false)
                \f\pa($ee);

            if (!empty($temp_var))
                \f\Cash::setVar($temp_var, 123, 60 * 60);

            if ($ee['sec'] > 25)
                break;
        }
    }

    if (1 == 1 && class_exists('\\Nyos\\Msg')) {
        \nyos\Msg::sendTelegramm($msg, null, 2);
    }

//    $r = ob_get_contents();
//    ob_end_clean();

    \f\end2('ok' . ( $r ?? '' ), true);
    // \f\end2('ok' . $r, true, ['load_kolvo' => sizeof($new_db ?? []), 'in_db' => sizeof($new_db ?? [])]);
}
//
catch (\Exception $ex) {

    $e = 'ошибка в запросе ' . $_SERVER['REQUEST_URI'] . PHP_EOL
            . '<pre>--- ' . __FILE__ . ' ' . __LINE__ . '-------'
            . PHP_EOL . $ex->getMessage() . ' #' . $ex->getCode()
            . PHP_EOL . $ex->getFile() . ' #' . $ex->getLine()
            . PHP_EOL . $ex->getTraceAsString()
            . '</pre>';

    if (class_exists('\nyos\Msg'))
        \nyos\Msg::sendTelegramm($e, null, 2);

    // die( \f\end2( $e, false ) );
    die(\f\end2('Произошла неописуемая ситуация #' . __LINE__, false));
}
