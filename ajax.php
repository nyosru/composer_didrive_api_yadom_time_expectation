<?php

ini_set('display_errors', 'On'); // сообщения с ошибками будут показываться
error_reporting(E_ALL); // E_ALL - отображаем ВСЕ ошибки

date_default_timezone_set("Asia/Yekaterinburg");
define('IN_NYOS_PROJECT', true);

ini_set("max_execution_time", 120);


header("Cache-Control: no-store, no-cache, must-revalidate");
header("Expires: " . date("r"));

require $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
require( $_SERVER['DOCUMENT_ROOT'] . '/all/ajax.start.php' );

foreach (\Nyos\Nyos::$menu as $k => $v) {
    if (isset($v['type_api']) && $v['type_api'] == 'time_expectation') {

        if (!empty($v['server_host']))
            \Nyos\api\JobExpectation::$sql_host = $v['server_host'];

        if (!empty($v['server_port']))
            \Nyos\api\JobExpectation::$sql_port = $v['server_port'];

        if (!empty($v['server_base']))
            \Nyos\api\JobExpectation::$sql_base = $v['server_base'];

        if (!empty($v['server_login']))
            \Nyos\api\JobExpectation::$sql_login = $v['server_login'];

        if (!empty($v['server_pass']))
            \Nyos\api\JobExpectation::$sql_pass = $v['server_pass'];

        break;
    }
}

//echo $_SERVER['HTTP_HOST'];
//\f\pa($_GET,null,null,'$_GET');

//
if (isset($_GET['action']) && $_GET['action'] == 'load_timer_waiting') {

    $e = \Nyos\api\JobExpectation::getTimerExpectation($db, $_GET['sp'], $_GET['date_start'], isset($_GET['date_fin']) ? $_GET['date_fin'] : date('Y-m-d', $_SERVER['REQUEST_TIME'] + 3600 * 24));
    // $e['line'] = __LINE__;
    return \f\end2('получили данные по времени задержки', true, $e);
    
}
/**
 * получаем данные по времени ожидания по умолчанию и пишем в модуль
 * рабочая версия 190716
 */
elseif (isset($_GET['action']) && $_GET['action'] == 'load_default_data_from_server') {

    $time_def = \Nyos\api\JobExpectation::getExpectationFromServerDefaultTime($db);
    // \f\pa($time_def,'','','$time_def');
     die( \f\end2('получили данные по времени задержки', true, $time_def, 'json' ) );
} 
//
elseif (isset($_GET['action']) && $_GET['action'] == 'load_data_from_server') {

    // удаляем всё из таблицы
    if (isset($_GET['all_delete']) && $_GET['all_delete'] == 'da') {
        $ff = $db->prepare('DELETE FROM mitems WHERE module = :id ');
        $ff->execute(array(':id' => '074.time_expectations_list'));
        // echo '<br/>' . __FILE__ . ' ' . __LINE__;
    }

    // echo '<br/>' . __FILE__ . ' ' . __LINE__;

    // \Nyos\api\JobExpectation::$no_write_data = true;
    $e = \Nyos\api\JobExpectation::getExpectation( $db, !empty( $_REQUEST['date_start'] ) ? $_REQUEST['date_start'] : null );
    
//    echo '<hr>';
//    echo '<hr>';
//
//    \f\pa($e, 2, null, '$e');
//
//    echo '<hr>';
//    echo '<hr>';

    if (1 == 1 && class_exists('\\Nyos\\Msg')) {

        if (!isset($vv['admin_auerific'])) {
            require_once DR . '/sites/' . \Nyos\nyos::$folder_now . '/config.php';
        }

        $sp = \Nyos\mod\items::getItems($db, \Nyos\nyos::$folder_now, 'sale_point', 'show', 500 );
        // \f\pa($sp);

        $txt = 'Обработали и добавили данные по времени ожидания: '  // . sizeof($in3);
        ;

        foreach ( $e['data'] as $k => $v) {
            // \f\pa($v);
            $txt .= PHP_EOL . $v['date'] . ' ' . $sp['data'][$v['sale_point']]['head'].' '.
                ( !empty($v['cold']) ? PHP_EOL.'Холодный цех: '.$v['cold'].' ' : '' ).
                ( !empty($v['hot']) ? PHP_EOL.'Горячий цех: '.$v['hot'].' ' : '' ).
                ( !empty($v['delivery']) ? PHP_EOL.'Доставка: '.$v['delivery'].' ' : '' )
                .PHP_EOL;
            
            // $v['date'] . ' - ' . $v['sp'] . ' - ' . $v['otdel'] . ' - ' . $v['minut'];
        }

        \nyos\Msg::sendTelegramm($txt, null, 1);

        if (isset($vv['admin_auerific'])) {
            foreach ($vv['admin_auerific'] as $k => $v) {
                \nyos\Msg::sendTelegramm($txt, $v);
                //\Nyos\NyosMsg::sendTelegramm('Вход в управление ' . PHP_EOL . PHP_EOL . $e, $k );
            }
        }

    }

    die( \f\end2( 'данные получены, записаны', true, $e['data'] ) );
    
} elseif (isset($_GET['action']) && $_GET['action'] == 'get_times_noajax') {

    // удаляем всё из таблицы
//    $ff = $db->prepare('DELETE FROM mitems WHERE module = :id ');
//    $ff->execute(array(':id' => '074.time_expectations_list'));

    echo '<br/>' . __FILE__ . ' ' . __LINE__;

    $e = \Nyos\api\JobExpectation::getExpectation($db, isset($_GET['date_start']) ? $_GET['date_start'] : date('Y-m-d', $_SERVER['REQUEST_TIME'] - 3600 * 24 * 4));

    \f\pa($e);

    echo '<br/>результат в аякс файле ';
    $list = \Nyos\mod\items::getItems($db, \Nyos\Nyos::$folder_now, '074.time_expectations_list', 'show');
    // \f\pa($list);

    /**
     * достаём связи : id sp на сервере - id sp на сайте
     */
    // echo '<br/>22результат в аякс файле ';
    $list2 = \Nyos\mod\items::getItems($db, \Nyos\Nyos::$folder_now, '074.time_expectations_links_to_sp', 'show');
    //\f\pa($list2);

    /**
     * массив : id sp на сервере - id sp на сайте
     */
    $links_sp_and_sp_serv = [];

    foreach ($list2['data'] as $k11 => $v11) {
        $links_sp_and_sp_serv[$v11['dop']['id_timeserver']] = $v11['dop']['sale_point'];
    }

    // \f\pa($links_sp_and_sp_serv);

    $list2 = [];
    foreach ($list['data'] as $k => $v) {
        $list2[$v['dop']['date'] . '--' . $v['dop']['sp'] . '--' . $v['dop']['otdel']] = 1;
    }

    $in3 = [];

    foreach ($e['data'] as $date => $v) {
        foreach ($v as $sp => $v1) {

            foreach ($v1 as $otd => $time) {

                if ($date == date('Y-m-d', $_SERVER['REQUEST_TIME']))
                    continue;

                // если нет такого
                if (!isset($list2[$date . '--' . $sp . '--' . $otd])) {

                    $in2 = array(
                        'date' => $date,
                        'sale_point' => $sp,
                        'sp_on_site' => ( $links_sp_and_sp_serv[$sp] ?? '' ),
                        'otdel' => $otd,
                        'minut' => $time
                    );

                    $in3[] = $in2;
                    \Nyos\mod\items::addNewSimple($db, '074.time_expectations_list', $in2);
                }
            }
        }
    }

    if (1 == 2 && class_exists('\\Nyos\\Msg')) {

        if (!isset($vv['admin_auerific'])) {
            require_once DR . '/sites/' . \Nyos\nyos::$folder_now . '/config.php';
        }

        $e = 'Подгружаем данные по времени ожидания, загружено новых записей (дата+точка+отдел+время ожидания): '  // . sizeof($in3);
        ;

        foreach ($in3 as $k => $v) {
            $e .= PHP_EOL . $v['date'] . ' - ' . $v['sp'] . ' - ' . $v['otdel'] . ' - ' . $v['minut'];
        }

        \nyos\Msg::sendTelegramm($e, null, 1);

        if (isset($vv['admin_auerific'])) {
            foreach ($vv['admin_auerific'] as $k => $v) {
                \nyos\Msg::sendTelegramm($e, $v);
                //\Nyos\NyosMsg::sendTelegramm('Вход в управление ' . PHP_EOL . PHP_EOL . $e, $k );
            }
        }
    }

    \f\end2('ok', true, $in3);
}

/**
 * получаем список точек что в базе на удалённом сервере
 */ 
//
elseif (isset($_GET['action']) && $_GET['action'] == 'get_times_tochki') {

    // удаляем всё из таблицы
//    $ff = $db->prepare('DELETE FROM mitems WHERE module = :id ');
//    $ff->execute(array(':id' => '074.time_expectations_list'));

    $connection = mysqli_connect(
            \Nyos\api\JobExpectation::$sql_host . (!empty(\Nyos\api\JobExpectation::$sql_port) ? ':' . \Nyos\api\JobExpectation::$sql_port : '' )
            , \Nyos\api\JobExpectation::$sql_login ?? ''
            , \Nyos\api\JobExpectation::$sql_pass ?? ''
            , \Nyos\api\JobExpectation::$sql_base ?? ''
    );

    $podr = mysqli_query($connection, 'select 
                *
            from 
                `location` 
            ;');

    while ($row = mysqli_fetch_assoc($podr)) {
        \f\pa($row);
    }

    \f\end2('ok', true, $in3);
} 
//
elseif (isset($_GET['action']) && $_GET['action'] == 'test_sql1') {

    // удаляем всё из таблицы
//    $ff = $db->prepare('DELETE FROM mitems WHERE module = :id ');
//    $ff->execute(array(':id' => '074.time_expectations_list'));

    $connection = mysqli_connect(
            \Nyos\api\JobExpectation::$sql_host . (!empty(\Nyos\api\JobExpectation::$sql_port) ? ':' . \Nyos\api\JobExpectation::$sql_port : '' )
            , \Nyos\api\JobExpectation::$sql_login ?? ''
            , \Nyos\api\JobExpectation::$sql_pass ?? ''
            , \Nyos\api\JobExpectation::$sql_base ?? ''
    );

    $podr = mysqli_query($connection, 'select 
                *
            from 
                `dbo.ItemSaleEvent` 
                
            LIMIT 10
            ;');

    while ($row = mysqli_fetch_assoc($podr)) {
        \f\pa($row);
    }

    \f\end2('ok', true, $in3);
}








// echo __FILE__.' '.__LINE__;
//require_once( DR.'/vendor/didrive/base/class/Nyos.php' );
//require_once( dirname(__FILE__).'/../class.php' );
//if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'scan_new_datafile') {
//
//    scanNewData($db);
//    //cron_scan_new_datafile();
//}
// проверяем секрет
if (
        (
        isset($_REQUEST['id']{0}) && isset($_REQUEST['s']{5}) &&
        \Nyos\nyos::checkSecret($_REQUEST['s'], $_REQUEST['id']) === true
        ) || (
        isset($_REQUEST['ids']{0}) && isset($_REQUEST['s']{5}) &&
        \Nyos\nyos::checkSecret($_REQUEST['s'], $_REQUEST['ids']) === true
        ) || (
        isset($_REQUEST['id2']{0}) && isset($_REQUEST['s2']{5}) &&
        \Nyos\nyos::checkSecret($_REQUEST['s2'], $_REQUEST['id2']) === true
        )
) {
    
}
//
else {

    $e = '';

    foreach ($_REQUEST as $k => $v) {
        $e .= '<Br/>' . $k . ' - ' . $v;
    }

    f\end2('Произошла неописуемая ситуация #' . __LINE__ . ' обратитесь к администратору ' . $e // . $_REQUEST['id'] . ' && ' . $_REQUEST['secret']
            , 'error');
}





/**
 * получаем время, либо с даты по текущее время, либо за последние 3 дня
 */
if (isset($_GET['action']) && $_GET['action'] == 'get_times') {


    // удаляем всё из таблицы
    if (isset($_GET['all_delete']) && $_GET['all_delete'] == 'da') {
//    $ff = $db->prepare('DELETE FROM mitems WHERE module = :id ');
//    $ff->execute(array(':id' => '074.time_expectations_list'));

        echo '<br/>удаляем все данные';

        $ff = $db->prepare('DELETE FROM sushi_time_waiting ');
        $ff->execute();
    }

    $e = \Nyos\api\JobExpectation::getExpectation($db, $_GET['date'] ?? date('Y-m-d', $_SERVER['REQUEST_TIME'] - 3600 * 24 * 4));

    return false;

    // echo '<br/>результат в аякс файле ';
    $list = \Nyos\mod\items::getItems($db, \Nyos\Nyos::$folder_now, '074.time_expectations_list', 'show');
    \f\pa($list);

    /**
     * достаём связи : id sp на сервере - id sp на сайте
     */
    // echo '<br/>22результат в аякс файле ';
    $list2 = \Nyos\mod\items::getItems($db, \Nyos\Nyos::$folder_now, '074.time_expectations_links_to_sp', 'show');
    //\f\pa($list2);

    /**
     * массив : id sp на сервере - id sp на сайте
     */
    $links_sp_and_sp_serv = [];

    foreach ($list2['data'] as $k11 => $v11) {
        $links_sp_and_sp_serv[$v11['dop']['id_timeserver']] = $v11['dop']['sale_point'];
    }

    // \f\pa($links_sp_and_sp_serv);

    $list2 = [];
    foreach ($list['data'] as $k => $v) {
        $list2[$v['dop']['date'] . '--' . ( $v['dop']['sp_on_serv'] ?? '' ) . '--' . $v['dop']['otdel']] = 1;
    }

    $in3 = [];

    foreach ($e['data'] as $date => $v) {
        foreach ($v as $sp => $v1) {

            foreach ($v1 as $otd => $time) {

                if ($date == date('Y-m-d', $_SERVER['REQUEST_TIME']))
                    continue;

                // если нет такого
                if (!isset($list2[$date . '--' . $sp . '--' . $otd])) {

                    $in2 = array(
                        'date' => $date,
                        'sp_on_serv' => $sp,
                        'sale_point' => ( $links_sp_and_sp_serv[$sp] ?? '' ),
                        'otdel' => $otd,
                        'minut' => $time
                    );

                    $in3[] = $in2;
                    \Nyos\mod\items::addNewSimple($db, '074.time_expectations_list', $in2);
                }
            }
        }
    }

    if (1 == 2 && class_exists('\\Nyos\\Msg')) {

        if (!isset($vv['admin_auerific'])) {
            require_once DR . '/sites/' . \Nyos\nyos::$folder_now . '/config.php';
        }

        $e = 'Подгружаем данные по времени ожидания, загружено новых записей (дата+точка+отдел+время ожидания): '  // . sizeof($in3);
        ;

        foreach ($in3 as $k => $v) {
            $e .= PHP_EOL . $v['date'] . ' - ' . $v['sp'] . ' - ' . $v['otdel'] . ' - ' . $v['minut'];
        }

        \nyos\Msg::sendTelegramm($e, null, 1);

        if (isset($vv['admin_auerific'])) {
            foreach ($vv['admin_auerific'] as $k => $v) {
                \nyos\Msg::sendTelegramm($e, $v);
                //\Nyos\NyosMsg::sendTelegramm('Вход в управление ' . PHP_EOL . PHP_EOL . $e, $k );
            }
        }
    }

    \f\end2('ok', true, $in3);
}



\f\end2('Произошла неописуемая ситуация #' . __LINE__ . ' обратитесь к администратору', 'error');

exit;
