<?php

ini_set('display_errors', 'On'); // сообщения с ошибками будут показываться
error_reporting(E_ALL); // E_ALL - отображаем ВСЕ ошибки

if ($_SERVER['HTTP_HOST'] == 'photo.uralweb.info' || $_SERVER['HTTP_HOST'] == 'yapdomik.uralweb.info' || $_SERVER['HTTP_HOST'] == 'a2.uralweb.info' 
        || $_SERVER['HTTP_HOST'] == 'adomik.uralweb.info'
        || $_SERVER['HTTP_HOST'] == 'adomik.dev.uralweb.info'
) {
    date_default_timezone_set("Asia/Omsk");
} else {
    date_default_timezone_set("Asia/Yekaterinburg");
}

define('IN_NYOS_PROJECT', true);

// ini_set("max_execution_time", 120);
ini_set("max_execution_time", 59);

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
//\f\pa($_REQUEST,null,null,'$_REQUEST');

try {

    /**
     * версия с эксклюзивной базой данных (старая версия)
     */
    if (1 == 2 && isset($_REQUEST['action']) && $_REQUEST['action'] == 'get_now_domen_timer') {


        if (isset($_REQUEST['w']{2}) && isset($_REQUEST['h']{2})) {
            // $_SERVER["HTTP_USER_AGENT"]

            $temp_var_name = 'run_site_time__' . $_SERVER['HTTP_HOST'] . '_' . $_REQUEST['w'] . '_' . $_REQUEST['h'];
            $temp_var = \f\Cash::getVar($temp_var_name);

            if ($temp_var == false) {

                $txt = 'старт сайта таймера на устройстве.'
                        . PHP_EOL . $_SERVER["HTTP_USER_AGENT"]
                        . PHP_EOL . 'разрешение - w:' . $_REQUEST['w'] . ' h:' . $_REQUEST['h']
                ;
                \f\Cash::setVar($temp_var_name, date('Y-m-d H:i'), 60 * 60 * 3);
            } else {

                $txt = 'работает норм, запущен в ' . $temp_var;
                \f\Cash::setVar($temp_var_name, $temp_var, 60 * 60 * 3);
            }


            \nyos\Msg::sendTelegramm($txt, null, 1);

            if (isset($vv['admin_ajax_job'])) {
                foreach ($vv['admin_ajax_job'] as $k => $v) {
                    \nyos\Msg::sendTelegramm($txt, $v);
                    //\Nyos\NyosMsg::sendTelegramm('Вход в управление ' . PHP_EOL . PHP_EOL . $e, $k );
                }
            }
        }




        /*
          {% set sp_all = api__importexport__getData( 'adomik.uralweb.info', 'sale_point' ) %}
          {#{ pa(sp_all) }#}
         */
        //$sps = \Nyos\api\ImportExport::getLocalDump('adomik.uralweb.info', 'sale_point');
// \f\pa($sps, 2, '', 'sps');

        /*
          {% set sp_link = api__importexport__getData( 'adomik.uralweb.info', '074.time_expectations_links_to_sp' ) %}
          {{ pa(sp_link) }}
         */
        $link = \Nyos\api\ImportExport::getLocalDump('adomik.uralweb.info', '074.time_expectations_links_to_sp');
        // \f\pa($link, 2, '', 'link');

        $now_sp_timer = null;

        if (!empty($link['data']))
            foreach ($link['data'] as $k => $v) {
                if ($v['status'] == 'show') {
                    for ($i = 1; $i <= 3; $i++) {
                        //echo '<br/>' . ( $v['dop']['site1'] ?? '' );
                        if (isset($v['dop']['site' . $i]) && $v['dop']['site' . $i] == $_SERVER['HTTP_HOST']) {
                            //if (isset($v['dop']['site' . $i]) && $v['dop']['site' . $i] == 'tt3.timer.uralweb.info' ) {
                            $now_sp_timer = $v['dop']['id_timeserver'];
                            //\f\pa($v);
                            break;
                        }
                    }

                    //$links[$sps['data'][$v['dop']['sale_point']]['dop']['head_translit']] = $v;
                }
            }

        // $now_sp_timer = 3;

        if ($now_sp_timer === null) {
            \f\end2('error', false);
        }

        //\f\pa($now_sp_timer);

        $e = \Nyos\api\JobExpectation::getExpectationLastOne($now_sp_timer);
        // \f\pa( $e,'','','$e' );
        //\f\pa( $_REQUEST );

        if (isset($e['data']['timer']) && is_numeric($e['data']['timer'])) {

            \f\end2('ok', true, [
                'time' => $e['data']['timer'],
                'line' => __LINE__,
            ]);
        } elseif (isset($e[$now_sp_timer][1]['value'])) {

            sleep(1);

            \f\end2('ok', true, [
                'time' => $e[$now_sp_timer][1]['value'],
                'line' => __LINE__,
            ]);
        }
        //
        else {
            \f\end2('error', false, [
                'line' => __LINE__
            ]);
        }
    }

    //
    elseif (isset($_REQUEST['action']) && $_REQUEST['action'] == 'remove_hand_time_edit') {

        //if ( isset($_REQUEST['sp']{0}) && !empty($_REQUEST['s']) && !empty($_REQUEST['date_month']) && \Nyos\Nyos::checkSecret($_REQUEST['s'], $_REQUEST['sp'] . $_REQUEST['date_month']) !== false ) {
        // if ( 1 == 1 ) {
        if( isset($_REQUEST['sp']{0}) && !empty($_REQUEST['s']) && !empty($_REQUEST['date_month']) && \Nyos\Nyos::checkSecret($_REQUEST['s'], $_REQUEST['sp'] . $_REQUEST['date_month'])) {

            ob_start('ob_gzhandler');

            $res = \Nyos\api\JobExpectation::remove_hand_time_edit($db, $_REQUEST['sp'], $_REQUEST['date_month']);
            // \f\pa($res);

            $r = ob_get_contents();
            ob_end_clean();

            \f\end2($res['html'] . ( $r ?? '' ), true);

        } else {

            \f\end2('что то пошло не так #' . __LINE__, false, $_REQUEST);
        }
    }



    /**
     * версия с эксклюзивной базой данных (старая версия)
     */ elseif (1 == 2 && isset($_REQUEST['action']) && $_REQUEST['action'] == 'load_timer_waiting') {

        throw new \Exception('Старый способ');

        $e = \Nyos\api\JobExpectation::getTimerExpectation($db, $_REQUEST['sp'], $_REQUEST['date_start'], isset($_REQUEST['date_fin']) ? $_REQUEST['date_fin'] : date('Y-m-d', $_SERVER['REQUEST_TIME'] + 3600 * 24));
        // $e['line'] = __LINE__;
        return \f\end2('получили данные по времени задержки', true, $e);
    }

    /**
     * получаем данные по времени ожидания по умолчанию и пишем в модуль
     * рабочая версия 190716
     */
    //
    elseif (isset($_REQUEST['action']) && $_REQUEST['action'] == 'load_default_data_from_server') {

        $time_def = \Nyos\api\JobExpectation::getExpectationFromServerDefaultTime($db);
        // \f\pa($time_def,'','','$time_def');
        die(\f\end2('получили данные по времени задержки', true, $time_def, 'json'));
    }

    /**
     * 
     */
    //
    elseif (isset($_REQUEST['action']) && $_REQUEST['action'] == 'load_data_from_server') {

        try {

            $for_info = '';

            //echo '<br/>#'.__LINE__.' '.__FILE__;
            // \f\pa($_REQUEST, '', '', 'request ' . __FILE__);
            //echo '<br/>#'.__LINE__.' '.__FILE__;
            // echo $_REQUEST['date_start'];
            // удаляем всё из таблицы
            if (isset($_REQUEST['all_delete']) && $_REQUEST['all_delete'] == 'da') {

                $ff = $db->prepare('DELETE FROM mitems WHERE module = :id ');
                $ff->execute(array(':id' => '074.time_expectations_list'));
                // echo '<br/>' . __FILE__ . ' ' . __LINE__;
            }

            // echo '<br/>' . __FILE__ . ' ' . __LINE__;
            // \Nyos\api\JobExpectation::$no_write_data = true;

            $ds = date('Y-m-d', isset($_REQUEST['date_start']{8}) ? strtotime($_REQUEST['date_start']) : $_SERVER['REQUEST_TIME'] - 3600 * 24 * 4);
            $df = date('Y-m-d', ( isset($_REQUEST['date_fin']{3}) ? strtotime($_REQUEST['date_fin']) : ( $_SERVER['REQUEST_TIME'] - 3600 * 24 )));

            // echo '<br/>' . $ds . ' - ' . $df;

            $e = \Nyos\api\JobExpectation::getExpectation($db, $ds, $df, ( $_REQUSET['sp'] ?? null));
            \f\pa($e, 2, '', 'Инфа из getExpectation');
            //exit;

            if ($e === false)
                echo 'нет инфы из базы';

            // \f\pa($e,2,'','список времени ожидания что получили с сервера');
//    echo '<hr>';
//    echo '<hr>';
            //\f\pa($e, 2, null, '$e');

            $adds_in_db = \Nyos\api\JobExpectation::saveData($db, $e, $ds, $df);

//    echo '<hr>';
//    echo '<hr>';

            if (1 == 1 && class_exists('\\Nyos\\Msg')) {

                if (!isset($vv['admin_ajax_job'])) {
                    require_once DR . '/sites/' . \Nyos\nyos::$folder_now . '/config.php';
                }

                // $sp = \Nyos\mod\items::getItemsSimple($db, 'sale_point');
                $sp = \Nyos\mod\items::getItemsSimple3($db, 'sale_point');
                //\f\pa($sp);

                $nn = 1;

                foreach ($adds_in_db as $date => $v1) {
                    foreach ($v1 as $sp1 => $v) {

                        if ($nn == 1) {
                            $txt = 'Обработали и добавили данные по времени ожидания и добавили: '  // . sizeof($in3);
                            ;
                            $nn = 2;
                        }

                        // \f\pa($v);
                        $txt .= PHP_EOL . $date . ' ' . ( $sp[$sp1]['head'] ?? 'sp:' . $sp1 )
                                . ' х:' . ( $v['cold'] ?? '-' )
                                . ' г:' . ( $v['hot'] ?? '-' )
                                . ' д:' . ( $v['delivery'] ?? '-' )
                        ;

                        // $v['date'] . ' - ' . $v['sp'] . ' - ' . $v['otdel'] . ' - ' . $v['minut'];
                    }
                }

                if ($nn == 1) {
                    $txt = 'Обработали данные по времени ожидания и новых не добавили, все данные есть';
                }

                \f\Cash::deleteKeyPoFilter(['TimerExpectation']);

                \nyos\Msg::sendTelegramm($txt, null, 1);

                if (isset($vv['admin_ajax_job'])) {
                    foreach ($vv['admin_ajax_job'] as $k => $v) {
                        \nyos\Msg::sendTelegramm($txt, $v);
                        //\Nyos\NyosMsg::sendTelegramm('Вход в управление ' . PHP_EOL . PHP_EOL . $e, $k );
                    }
                }
            }

            die(\f\end2('данные получены, записаны', true, $adds_in_db));
        } catch (\Exception $ex) {

            echo $text = '<pre>--- ' . __FILE__ . ' ' . __LINE__ . '-------'
            . PHP_EOL . $ex->getMessage() . ' #' . $ex->getCode()
            . PHP_EOL . $ex->getFile() . ' #' . $ex->getLine()
            . PHP_EOL . $ex->getTraceAsString()
            . '</pre>';

            if (class_exists('\nyos\Msg'))
                \nyos\Msg::sendTelegramm($text, null, 1);
        } catch (\Throwable $ex) {

            echo $text = '<pre>--- ' . __FILE__ . ' ' . __LINE__ . '-------'
            . PHP_EOL . $ex->getMessage() . ' #' . $ex->getCode()
            . PHP_EOL . $ex->getFile() . ' #' . $ex->getLine()
            . PHP_EOL . $ex->getTraceAsString()
            . '</pre>';

            if (class_exists('\nyos\Msg'))
                \nyos\Msg::sendTelegramm($text, null, 1);
        } finally {

            die('неописуемая ситуация #' . __LINE__);
        }
    }

    // новая версия от 2020-02-07 подгрузки времени ожидания, с помощью АПИ
    elseif (isset($_REQUEST['action']) && $_REQUEST['action'] == 'get_times_noajax') {

        $sps = \Nyos\mod\items::get($db, \Nyos\mod\JobDesc::$mod_sale_point);
        $sps_link_timeo = \Nyos\mod\items::get($db, \Nyos\mod\JobDesc::$mod_sp_link_timeo);

        // \f\pa($sps_link_timeo);

        $link_sp_timeosp = [];
        foreach ($sps_link_timeo as $k => $v) {
            // \f\pa($v);
            if (!empty($v['sale_point']) && !empty($v['id_timeserver']))
                $link_sp_timeosp[$v['sale_point']] = $v['id_timeserver'];
        }

        // \f\pa($link_sp_timeosp);

        \f\timer_start(3);

        $ar__sp_date_time = [];

        foreach ($sps as $k => $v) {

            // \f\pa($v);

            if (!isset($link_sp_timeosp[$v['id']]))
                continue;

            $q = ['sp' => $link_sp_timeosp[$v['id']],
                // 'date' => '2020-02-01',
                'date' => date('Y-m-d'),
                's' => md5($link_sp_timeosp[$v['id']] . 'time' . date('Y-m-d'))
            ];

            $res_ar = json_decode(file_get_contents('http://time-exp.uralweb.info/api.php?' . http_build_query($q)), true);
            // \f\pa($res_ar,2);

            if (!empty($res_ar['status']) && $res_ar['status'] == 'ok' && !empty($res_ar['data'])) {
                $ar__sp_date_time[$v['id']] = $res_ar['data'];
            }

            // echo \f\timer_stop(3,'str');
        }

        if (!empty($_REQUEST['show'])) {
            \f\pa($link_sp_timeosp, 2, '', '$link_sp_timeosp');
            \f\pa($ar__sp_date_time, 2, '', '$ar__sp_date_time');
        }

        \Nyos\mod\items::$join_where = ' INNER JOIN `mitems-dops` mid '
                . ' ON mid.id_item = mi.id '
                . ' AND mid.name = \'date\' '
                . ' AND mid.value_date >= :d '
        ;
        \Nyos\mod\items::$var_ar_for_1sql[':d'] = date('Y-m-01');
        $timeo = \Nyos\mod\items::get($db, \Nyos\mod\JobDesc::$mod_timeo);
        // \f\pa($timeo, 2, '', '$timeo');

        $timeo_all__sp_date = [];
        foreach ($timeo as $k => $v) {
            $timeo_all__sp_date[$v['sale_point']][$v['date']] = $v;
        }
        // \f\pa($timeo_all__sp_date, 2, '', '$timeo_all__sp_date');

        $type = ['cold', 'hot', 'delivery'];

        // новые полные записи
        $new_db = [];
        // изменять данные
        $edit_dops = [];

        foreach ($ar__sp_date_time as $sp1 => $dates) {
            foreach ($dates as $date1 => $v1) {

                // новые данные, в бд нет в загрузке есть 
                if (!isset($timeo_all__sp_date[$sp1][$date1])) {

                    $ss = [
                        'date' => $date1,
                        'sale_point' => $sp1,
                    ];

                    foreach ($type as $t) {
                        $ss[$t] = $v1[$t] ?? 0;
                    }

                    $new_db[] = $ss;
                    continue;
                }

                $now = $timeo_all__sp_date[$sp1][$date1];

                foreach ($type as $t) {
                    if (!isset($now[$t]) || ( isset($now[$t]) && $now[$t] != $v1[$t] )) {
                        $edit_dops[$now['id']][$t] = $v1[$t];
                    }
                }
            }
        }

        // новые полные записи
        // \f\pa($new_db,2,'','$new_db');
        \Nyos\mod\items::addNewSimples($db, \Nyos\mod\JobDesc::$mod_timeo, $new_db);
        // \f\pa($edit_dops,2,'','$edit_dops');
        \Nyos\mod\items::saveNewDop($db, $edit_dops);

        if (1 == 1 && class_exists('\\Nyos\\Msg')) {

            $e = 'Время ожидания'
                    . PHP_EOL
                    . 'Загрузили и записали '
                    . PHP_EOL
                    . '( точка / дата / горячий + холодный + доставка';

            foreach ($new_db as $v) {
                // \f\pa($v);
                $e .= PHP_EOL . $sps[$v['sale_point']]['head'] . '/' . $v['date'] . '/' . $v['cold'] . ' + ' . $v['hot'] . ' + ' . $v['delivery'];
            }

            \nyos\Msg::sendTelegramm($e, null, 2);
        }

        $r = ob_get_contents();
        ob_end_clean();

        \f\end2('ok' . $r, true, ['load_kolvo' => sizeof($new_db), 'in_db' => sizeof($new_db)]);
    }

    // старая версия подгрузки времени ожидания, подключаемся к базе и тащим
    elseif (isset($_REQUEST['action']) && $_REQUEST['action'] == 'get_times_noajax-old2020-02-07') {

        ob_start('ob_gzhandler');

        if (isset($_REQUEST['view']) && $_REQUEST['view'] == 'html') {

            echo '<h2>считаем время ожидания</h2>';
        }

        // удаляем всё из таблицы
//    $ff = $db->prepare('DELETE FROM mitems WHERE module = :id ');
//    $ff->execute(array(':id' => '074.time_expectations_list'));
        // echo '<br/>' . __FILE__ . ' ' . __LINE__;
        $sps = \Nyos\mod\items::getItemsSimple3($db, 'sale_point');

        /**
         * достаём связи : id sp на сервере - id sp на сайте
         * массив : id sp на сервере - id sp на сайте
         */
        $links = \Nyos\mod\items::getItemsSimple3($db, '074.time_expectations_links_to_sp');
        $links_sp_and_sp_serv = [];
        foreach ($links as $v11) {
            $links_sp_and_sp_serv[$v11['id_timeserver']] = $v11['sale_point'];
        }

        $date_start = isset($_REQUEST['date_start']) ? date('Y-m-d', strtotime($_REQUEST['date_start'])) : date('Y-m-d', $_SERVER['REQUEST_TIME'] - 3600 * 24 * 4);

        $new_data = \Nyos\api\JobExpectation::getExpectation($db, $date_start, ( $_REQUEST['date_finish'] ?? null), ( $_REQUEST['sp'] ?? null));

        if (isset($_REQUEST['view']) && $_REQUEST['view'] == 'html') {

//            foreach ($new_data as $date => $k) {
//                foreach ($k as $sp => $v) {
//                    foreach ($v as $ceh => $v1) {
//                        foreach ($v1 as $time => $var) {
//                            echo '<br/>'.$date.' '.$sp.' '.$ceh.' '.$time.' '.$var;
//                        }
//                    }
//                }
//            }

            \f\pa($new_data, 2, '', '$new_data результат выгрузки');
        }

        if (isset($_REQUEST['view']) && $_REQUEST['view'] == 'html') {
            \f\pa($new_data, 2, '', '$new_data');
        }


        \Nyos\mod\items::$join_where = ' INNER JOIN `mitems-dops` mid '
                . ' ON mid.id_item = mi.id '
                . ' AND mid.name = \'date\' '
                . ' AND mid.value_date >= \'' . $date_start . '\' '
        ;
        $list = \Nyos\mod\items::getItemsSimple3($db, '074.time_expectations_list');
        // \f\pa($list);

        $now_db_id = $now_db = [];

        foreach ($list as $v) {

            $now_db_id[$v['date']][$v['sale_point']] = $v['id'];
            $v1 = [];

            if (!empty($v['cold']))
                $v1['cold'] = $v['cold'];

            if (!empty($v['hot']))
                $v1['hot'] = $v['hot'];

            if (!empty($v['delivery']))
                $v1['delivery'] = $v['delivery'];

            $now_db[$v['date']][$v['sale_point']] = $v1;
        }

        $sql_to_delete = '';
        $sql_to_delete_vars = [];
        $nn = 0;
        $in_db = [];

        // \f\pa($now_db, 2, '', 'now_db');
        // \f\pa($in_db);
        // записываем результат
        if (isset($_REQUEST['resave_res'])) {

//        $date_start = isset($_REQUEST['date_start']) ? date('Y-m-d', strtotime($_REQUEST['date_start'])) : date('Y-m-d', $_SERVER['REQUEST_TIME'] - 3600 * 24 * 4);
//        $new_data = \Nyos\api\JobExpectation::getExpectation($db, $date_start, ( $_REQUEST['date_finish'] ?? null), ( $_REQUEST['sp'] ?? null));
            //\Nyos\mod\items::$show_sql = true;
            \Nyos\mod\items::$join_where = '';

            if (!empty($_REQUEST['date_start']) && !empty($_REQUEST['date_finish']) && $_REQUEST['date_start'] == $_REQUEST['date_finish']) {
                \Nyos\mod\items::$join_where .= ' INNER JOIN `mitems-dops` mid '
                        . ' ON mid.id_item = mi.id '
                        . ' AND mid.name = \'date\' '
                        . ' AND mid.value_date = :d ';
                \Nyos\mod\items::$var_ar_for_1sql[':d'] = date('Y-m-d', strtotime($_REQUEST['date_start']));
            }

            if (!empty($_REQUEST['sp'])) {
                \Nyos\mod\items::$join_where .= ' INNER JOIN `mitems-dops` mid2 '
                        . ' ON mid2.id_item = mi.id '
                        . ' AND mid2.name = \'sale_point\' '
                        . ' AND mid2.value = :sp ';
                \Nyos\mod\items::$var_ar_for_1sql[':sp'] = $_REQUEST['sp'];
            }

//            \Nyos\mod\items::$join_where = ' INNER JOIN `mitems-dops` mid '
//                    . ' ON mid.id_item = mi.id '
//                    . ' AND mid.name = \'date\' '
//                    . ' AND mid.value_date >= :ds '
//                    . ' AND mid.value_date <= :df '
//                    . ' INNER JOIN `mitems-dops` mid2 '
//                    . ' ON mid2.id_item = mi.id '
//                    . ' AND mid2.name = \'sale_point\' '
//                    . ' AND mid2.value = :sp '
//
//            ;
//            \Nyos\mod\items::$var_ar_for_1sql[':sp'] = $sp;
//            \Nyos\mod\items::$var_ar_for_1sql[':ds'] = $date_start;

            $l = \Nyos\mod\items::get($db, \Nyos\mod\JobDesc::$mod_timeo);

            if (isset($_REQUEST['view']) && $_REQUEST['view'] == 'html')
                \f\pa($l);

            if (!empty($_REQUEST['date_start']) && !empty($_REQUEST['date_finish']) && $_REQUEST['date_start'] == $_REQUEST['date_finish'] && !empty($_REQUEST['sp'])) {

                $new = [];

                foreach ($l as $k => $v) {

                    $otd = 'cold';
                    if (!empty($v[$otd]) && !empty($new_data[$_REQUEST['date_start']][$_REQUEST['sp']][$otd]) && $v[$otd] != $new_data[$_REQUEST['date_start']][$_REQUEST['sp']][$otd]) {
                        $new[$otd] = $new_data[$_REQUEST['date_start']][$_REQUEST['sp']][$otd];
                    }

                    $otd = 'hot';
                    if (!empty($v[$otd]) && !empty($new_data[$_REQUEST['date_start']][$_REQUEST['sp']][$otd]) && $v[$otd] != $new_data[$_REQUEST['date_start']][$_REQUEST['sp']][$otd]) {
                        $new[$otd] = $new_data[$_REQUEST['date_start']][$_REQUEST['sp']][$otd];
                    }

                    $otd = 'delivery';
                    if (!empty($v[$otd]) && !empty($new_data[$_REQUEST['date_start']][$_REQUEST['sp']][$otd]) && $v[$otd] != $new_data[$_REQUEST['date_start']][$_REQUEST['sp']][$otd]) {
                        $new[$otd] = $new_data[$_REQUEST['date_start']][$_REQUEST['sp']][$otd];
                    }
                }


                if (empty($new)) {
                    echo '<h3>нет новых значений</h3>';
                } else {
                    $save_new_dop = \Nyos\mod\items::saveNewDop($db, [$v['id'] => $new]);
                    //\f\pa($save_new_dop,'','','записали новые значения');
                    echo '<h3>записали новые значения</h3>';
                    \f\pa($new, '', '', 'new');
                }
            }
        }
        //
        else {

            foreach ($new_data as $date => $v1) {
                foreach ($v1 as $sp => $v) {

                    if (isset($now_db[$date][$sp]) && $now_db[$date][$sp] == $v) {
                        // echo '<br/>' . __LINE__ . ' ок';
                    } else {

                        // echo '<br/>' . __LINE__ . ' не ок';
                        // echo $now_db_id[$date][$sp];

                        if (!empty($now_db_id[$date][$sp])) {

                            // echo '<br/>' . __LINE__ . ' удаляем ' . $now_db_id[$date][$sp];
                            $sql_to_delete .= ( empty($sql_to_delete) ? '' : ' OR ' ) . ' `id` = :id' . $nn;

                            $sql_to_delete_vars[':id' . $nn] = $now_db_id[$date][$sp];
                            $nn++;
                        }

                        $v['date'] = $date;
                        $v['sale_point'] = $sp;
                        // \f\pa($v);

                        $in_db[] = $v;
                    }
                }
            }

            if (!empty($sql_to_delete)) {
                $sql = 'UPDATE mitems SET `status` = \'delete\' WHERE `module` = \'074.time_expectations_list\' AND ( ' . $sql_to_delete . ' ) ';
                $ff = $db->prepare($sql);
                // \f\pa($sql_to_delete_vars);
                $ff->execute($sql_to_delete_vars);
            }

            \Nyos\mod\items::addNewSimples($db, '074.time_expectations_list', $in_db);
        }

        // \f\end2('ok', true, $in3);


        if (1 == 1 && class_exists('\\Nyos\\Msg')) {

            if (!isset($vv['admin_auerific'])) {
                require_once DR . '/sites/' . \Nyos\nyos::$folder_now . '/config.php';
            }

            if (!empty($in_db)) {

                $e = 'Время ожидания'
                        . PHP_EOL
                        . 'Загрузили данные и записали в базу данных: ' . sizeof($in_db)
                        . PHP_EOL
                        . ' дата / горячий + холодный + доставка'
                ;

                foreach ($sps as $sp) {

                    if ($sp['head'] == 'default')
                        continue;

                    $e .= PHP_EOL . '-- ' . $sp['head'] . ' --';

                    foreach ($in_db as $v) {
                        if ($v['sale_point'] == $sp['id']) {
                            $e .= PHP_EOL . $v['date'] . ' / '
                                    . ( $v['hot'] ?? 'x' )
                                    . ' + '
                                    . ( $v['cold'] ?? 'x' )
                                    . ' + '
                                    . ( $v['delivery'] ?? 'x' )
                            ;
                        }
                    }
                }

                \nyos\Msg::sendTelegramm($e, null, 2);

//                if (isset($vv['admin_auerific'])) {
//                    foreach ($vv['admin_auerific'] as $k => $v) {
//                        \nyos\Msg::sendTelegramm($e, $v);
//                        //\Nyos\NyosMsg::sendTelegramm('Вход в управление ' . PHP_EOL . PHP_EOL . $e, $k );
//                    }
//                }
            }
            //
            else {

                $e = 'загрузка времени ожидания, новых записей нет ... обратите внимание';

                \nyos\Msg::sendTelegramm($e, null, 2);

//                if (isset($vv['admin_auerific'])) {
//                    foreach ($vv['admin_auerific'] as $k => $v) {
//                        \nyos\Msg::sendTelegramm($e, $v);
//                        //\Nyos\NyosMsg::sendTelegramm('Вход в управление ' . PHP_EOL . PHP_EOL . $e, $k );
//                    }
//                }
            }
        }

        $r = ob_get_contents();
        ob_end_clean();

        \f\end2('ok' . $r, true, ['load_kolvo' => $nn, 'in_db' => $in_db]);
    }

    /**
     * получаем список точек что в базе на удалённом сервере
     */
//
    elseif (isset($_REQUEST['action']) && $_REQUEST['action'] == 'get_times_tochki') {

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
    elseif (isset($_REQUEST['action']) && $_REQUEST['action'] == 'test_sql1') {

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
    if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'get_times') {

        throw new \Exception('Старый способ');

        // удаляем всё из таблицы
        if (isset($_REQUEST['all_delete']) && $_REQUEST['all_delete'] == 'da') {
//    $ff = $db->prepare('DELETE FROM mitems WHERE module = :id ');
//    $ff->execute(array(':id' => '074.time_expectations_list'));

            echo '<br/>удаляем все данные';

            $ff = $db->prepare('DELETE FROM sushi_time_waiting ');
            $ff->execute();
        }

        $e = \Nyos\api\JobExpectation::getExpectation($db, $_REQUEST['date'] ?? date('Y-m-d', $_SERVER['REQUEST_TIME'] - 3600 * 24 * 4));

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
        \nyos\Msg::sendTelegramm($e, null, 1);

    // die( \f\end2( $e, false ) );
    die(\f\end2('Произошла неописуемая ситуация #' . __LINE__, false));
}
