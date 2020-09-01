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

try {

    $sps = \Nyos\mod\items::get($db, \Nyos\mod\JobDesc::$mod_sale_point, 'show', 'id_id');

    if (isset($_REQUEST['file__show']))
        \f\pa($sps, 2, '', '$sps');

    $msg = 'грузим время ожидания';

    foreach ($sps as $k => $v) {

        if( $v['head'] == 'default' )
            continue;
        
        if (isset($_REQUEST['file__show']))
            \f\pa([ $k, $v], 2);

        $last_run = false;

//        if (!empty($temp_var))
//            $last_run = \f\Cash::getVar($temp_var);

        if ($last_run !== false) {
            
        } else {

            // \f\pa( 'da' );

            // $msg .= PHP_EOL . $sps[$v['id']]['head'] ?? 'no head sp';
            $msg .= PHP_EOL . $v['head'] ?? 'no head sp';

            $file_request = [
                'sp' => $v['id'],
                // если пишем всё по новой
                // 'delete_old' => 'da',
                // пропустить отправку сообщения
                'skip_send_msg' => 'da',
                'date' => (
                !empty($_REQUEST['scan_date']) ?
                date('Y-m-d', strtotime($_REQUEST['scan_date'])) : date('Y-m-d', $_SERVER['REQUEST_TIME'] - 3600 * 24)
                )
            ];


            if (1 == 2) {

                $link = 'http://' . $_SERVER['HTTP_HOST'] . '/vendor/didrive_api/yadom_time_expectation/micro-service/timeo-load-save.php?' . http_build_query($file_request);
                \f\pa($link);
                if ($curl = curl_init()) { //инициализация сеанса
                    //указываем адрес страницы
                    curl_setopt($curl, CURLOPT_URL, $link);
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                    // curl_setopt ($curl, CURLOPT_POST, true);
                    // curl_setopt ($curl, CURLOPT_POSTFIELDS, "i=1");
                    curl_setopt($curl, CURLOPT_HEADER, 0);
                    $result = curl_exec($curl); //выполнение запроса

                    if (isset($_REQUEST['file__show']))
                        \f\pa($result, '', '', 'result');

                    $res = json_decode($result, true);

                    if (isset($_REQUEST['file__show']))
                        \f\pa($res, '', '', 'result2');

                    foreach ($res['new'] as $q => $w) {
                        $msg .= PHP_EOL . $w['date'] . ' / ' . ( $w['cold'] ?? '-' ) . ' ' . ( $w['hot'] ?? '-' ) . ' ' . ( $w['delivery'] ?? '-' );
                    }

                    curl_close($curl); //закрытие сеанса
                }
            }
            //
            else {
                $skip_start = true;
                $file_return = 'array';
                $res1 = require 'timeo-load-save.php';
                if (isset($_REQUEST['file__show']))
                    \f\pa($res1);
            }

            $ee = \f\timer_stop(3, 'ar');
            if (isset($_REQUEST['file__show']))
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

    \f\end2('ok' . ( $r ?? '' ), true);
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