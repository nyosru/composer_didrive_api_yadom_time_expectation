<?php

ini_set('display_errors', 'On'); // сообщения с ошибками будут показываться
error_reporting(E_ALL); // E_ALL - отображаем ВСЕ ошибки

if ($_SERVER['HTTP_HOST'] == 'photo.uralweb.info' || $_SERVER['HTTP_HOST'] == 'yapdomik.uralweb.info' || $_SERVER['HTTP_HOST'] == 'a2.uralweb.info' || $_SERVER['HTTP_HOST'] == 'adomik.uralweb.info' || $_SERVER['HTTP_HOST'] == 'adomik.dev.uralweb.info'
) {
    date_default_timezone_set("Asia/Omsk");
} else {
    date_default_timezone_set("Asia/Yekaterinburg");
}

define('IN_NYOS_PROJECT', true);

// ini_set("max_execution_time", 120);
//ini_set("max_execution_time", 59);
//
//header("Cache-Control: no-store, no-cache, must-revalidate");
//header("Expires: " . date("r"));

require $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
require( $_SERVER['DOCUMENT_ROOT'] . '/all/ajax.start.php' );



//echo $_SERVER['HTTP_HOST'];
//\f\pa($_REQUEST,null,null,'$_REQUEST');

try {

//    ob_start('ob_gzhandler');

    $sps = \Nyos\mod\items::get($db, \Nyos\mod\JobDesc::$mod_sale_point);
    $sps_link_timeo = \Nyos\mod\items::get($db, \Nyos\mod\JobDesc::$mod_sp_link_timeo);

    \f\timer_start(3);

    $msg = 'грузим время ожидания';


    foreach ($sps as $k => $v) {

        // \f\pa($v);
//        if (!isset($link_sp_timeosp[$v['id']]))
//            continue;


        $temp_var = 'last_run__ajax.load_and_save_timeo.php__3sp' . $v['id'];

        $last_run = \f\Cash::getVar($temp_var);

        if ( !empty($last_run)) {
            
        //$msg .= PHP_EOL . ' - - пропуск';
        // $msg .= PHP_EOL . $sps[$v['id']]['head'].' --';
            
        }else {
            
        $msg .= PHP_EOL . $sps[$v['id']]['head'];
        
            $u = [
                'sp' => $v['id'],
                // если пишем всё по новой
                // 'delete_old' => 'da',
                // пропустить отправку сообщения
                'skip_send_msg' => 'da',
                'date' => date('Y-m-d')
            ];

            // echo '<br/>+++'.$sps[$v['id']]['head'];

            $link = 'http://' . $_SERVER['HTTP_HOST'] . '/vendor/didrive_api/yadom_time_expectation/ajax.load_and_save_timeo.php?' . http_build_query($u);

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
                //\f\pa($result, '', '', 'result');
                $res = json_decode($result, true);
                // \f\pa(json_decode($result,true), '', '', 'result2');
                foreach ($res['new'] as $q => $w) {
                    $msg .= PHP_EOL . $w['date'] . ' / ' . ( $w['cold'] ?? '-' ) . ' ' . ( $w['hot'] ?? '-' ) . ' ' . ( $w['delivery'] ?? '-' );
                }

                curl_close($curl); //закрытие сеанса
                // \f\Cash::setVar($temp_var, 1, ( $time_expire ?? 60 * 60 * 1));
            }

            $ee = \f\timer_stop(3, 'ar');
            \f\pa($ee);

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
