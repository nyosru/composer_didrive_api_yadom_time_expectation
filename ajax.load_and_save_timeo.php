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

    ob_start('ob_gzhandler');

    $type = ['cold', 'hot', 'delivery'];

    // \f\pa($_REQUEST, '', '', '$_REQUEST');

    $date_start = date('Y-m-01', strtotime($_REQUEST['date']));
    $date_finish = date('Y-m-d', strtotime($date_start . ' +1 month -1 day'));

    // чистим кеш
    \f\Cash::deleteKeyPoFilter(['time_expectations', 'ds' . $date_start]);




    $sps = \Nyos\mod\items::get($db, \Nyos\mod\JobDesc::$mod_sale_point);
    $sps_link_timeo = \Nyos\mod\items::get($db, \Nyos\mod\JobDesc::$mod_sp_link_timeo);

    // \f\pa($sps_link_timeo);

    $link_sp_timeosp = [];
    foreach ($sps_link_timeo as $k => $v) {

        if (isset($_REQUEST['sp']) && $v['sale_point'] != $_REQUEST['sp'])
            continue;

        // \f\pa($v);
        if (!empty($v['sale_point']) && !empty($v['id_timeserver']))
            $link_sp_timeosp[$v['sale_point']] = $v['id_timeserver'];
    }

    // \f\pa($link_sp_timeosp);

    \f\timer_start(3);









    $ar__sp_date_time = [];

    /**
     * масив id что стираем
     */
    $clear_id_timeo = [];

    /**
     * текущая реальная дата
     */
    $now_real_date = date('Y-m-d');






    foreach ($sps as $k => $v) {

        // \f\pa($v);

        if (!isset($link_sp_timeosp[$v['id']]))
            continue;

        $q = ['sp' => $link_sp_timeosp[$v['id']],
            // 'date' => '2020-02-01',
            'date' => date('Y-m-d'),
            's' => md5($link_sp_timeosp[$v['id']] . 'time' . date('Y-m-d'))
        ];

        $uri = 'http://time-exp.uralweb.info/api.php?' . http_build_query($q);
        // \f\pa($uri,'','','uri');
        $res_ar = json_decode(file_get_contents($uri), true);
        // \f\pa($res_ar, 2, '', 'res_ar');

        if (isset($_REQUEST['show']) && $_REQUEST['show'] == 'table') {
            if (isset($res_ar['data'])) {
                echo 'загрузили данные с сервера времени ожидания'
                . '<table class="table" ><thead><tr><th>дата</th><th>холодный</th><th>горячий</th><th>доставка</th></tr></thead><tbody>';
                foreach ($res_ar['data'] as $date => $m) {
                    echo '<tr><td>' . $date . '</td><td>' . ( $m['cold'] ?? '-' ) . '</td><td>' . ( $m['hot'] ?? '-' ) . '</td><td>' . ( $m['delivery'] ?? '-' ) . '</td></tr>';
                }
                echo '</tbody></table>';
            }
        }

//        if ($res_ar['status'] == 'ok')
//            $ar__sp_date_time[$v['id']] = $res_ar['data'];
//        if ( 1 == 1 or ( !empty($res_ar['status']) && $res_ar['status'] == 'ok' && !empty($res_ar['data']) ) ) {
//            $ar__sp_date_time[$v['id']] = $res_ar['data'];
//        }
        // echo \f\timer_stop(3,'str');
//        if (!empty($_REQUEST['show'])) {
//            \f\pa($link_sp_timeosp, 2, '', '$link_sp_timeosp');
//            \f\pa($ar__sp_date_time, 2, '', '$ar__sp_date_time');
//        }

        \Nyos\mod\items::$join_where = ' INNER JOIN `mitems-dops` mid '
                . ' ON mid.id_item = mi.id '
                . ' AND mid.name = \'date\' '
                . ' AND mid.value_date >= :d '
                . ' AND mid.value_date <= :d2 '
                . ' INNER JOIN `mitems-dops` mid2 '
                . ' ON mid2.id_item = mi.id '
                . ' AND mid2.name = \'sale_point\' '
                . ' AND mid2.value = :sp '
        ;
        \Nyos\mod\items::$var_ar_for_1sql[':d'] = $date_start;
        \Nyos\mod\items::$var_ar_for_1sql[':d2'] = $date_finish;
        \Nyos\mod\items::$var_ar_for_1sql[':sp'] = $_REQUEST['sp'];

        $timeo = \Nyos\mod\items::get($db, \Nyos\mod\JobDesc::$mod_timeo);
        // \f\pa($timeo, 2, '', '$timeo в базе');

        $indb__sp_date_ceh_time = [];
        foreach ($timeo as $k => $v) {

            $indb__sp_date_ceh_time[$v['sale_point']][$v['date']]['id'] = $v['id'];

            foreach ($type as $type1) {
                if (isset($v[$type1]))
                    $indb__sp_date_ceh_time[$v['sale_point']][$v['date']][$type1] = $v[$type1];
            }
        }

//        \f\pa($indb__sp_date_ceh_time, 2, '', '$indb__sp_date_ceh_time');
//
//        // foreach ($res_ar['data'] as $date => $ar) {
//        foreach ($res_ar['data'] as $date => $ar) {
//            
//        }

        for ($n = 0; $n <= 32; $n++) {

            $now_date = date('Y-m-d', strtotime($date_start . ' +' . $n . ' day'));

            if ($now_date >= $now_real_date)
                continue;

            // \f\pa($now_date);
            // \f\pa($indb__sp_date_ceh_time[$v['sale_point']][$now_date], '', '', '$indb__sp_date_ceh_time');

            foreach ($type as $type1) {
                if (!isset($res_ar['data'][$now_date][$type1])) {
                    $res_ar['data'][$now_date][$type1] = 0;
                }
            }
            // \f\pa($res_ar['data'][$now_date], '', '', '$res_ar');

            $ar = $indb__sp_date_ceh_time[$v['sale_point']][$now_date];

            if (isset($ar['id'])) {
                $now_id = $ar['id'];
                unset($ar['id']);
            }

            if ($res_ar['data'][$now_date] != $ar || isset($_REQUEST['delete_old']) ) {

                $clear_id_timeo[$now_id] = 1;

                if (isset($_REQUEST['show']))
                    echo '<br/>#' . __LINE__ . ' (' . $sps[$v['sale_point']]['head'] . ') значения за ' . $now_date . ' не сходятся, запишем новые значения';

                $ss = [
                    'date' => $now_date,
                    'sale_point' => $v['sale_point'],
                ];

                foreach ($type as $t) {
                    $ss[$t] = $res_ar['data'][$now_date][$t] ?? 0;
                }

                $new_db[] = $ss;
                continue;
            }
//            else {
//                echo '#' . __LINE__ . ' сходится';
//            }
//            echo '<hr>';
//            echo '<hr>';
        }
    }

// стираем старые id что заменяем новыми
    if (1 == 2) {
        // \f\pa($clear_id_timeo, 2, '', '$clear_id_timeo');

        $sql2 = '';
        $sql_in = [];
        $n7 = 1;

        foreach ($clear_id_timeo as $k => $v) {
            $n7++;
            $sql2 .= (!empty($sql2) ? ' OR ' : '' ) . '`id` = :id' . $n7;
            $sql_in[':id' . $n7] = $k;
        }

        if (!empty($sql2)) {
            $sql = 'UPDATE `mitems` SET `status` = \'delete\' WHERE ' . $sql2;

            if (isset($_REQUEST['show'])) {
                \f\pa($sql);
                \f\pa($sql_in);
            }

            $ff = $db->prepare($sql);
            $ff->execute($sql_in);
        }
    }


    if (!empty($new_db)) {

        // стираем все даты+сп что пишем по новой
        if (1 == 1) {

            $datas = [];
            foreach ($new_db as $k => $v) {
                $datas[] = ['sale_point' => $v['sale_point'], 'date' => $v['date']];
            }

            if (!empty($datas))
                \Nyos\mod\items::deleteItems2($db, \Nyos\mod\JobDesc::$mod_timeo, $datas);
        }

        if (isset($_REQUEST['show'])) {
            echo '<br/>обновили дней:' . sizeof($new_db);
            //\f\pa($new_db, 2, '', '$new_db');
        }

        \Nyos\mod\items::addNewSimples($db, \Nyos\mod\JobDesc::$mod_timeo, $new_db);
    } else {
        if( isset($_REQUEST['show']) )
        echo '<br/>всё норм, ничего не добавили, не обновили';
    }


    if (1 == 2) {

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

                    if (!isset($now[$t]) || ( isset($now[$t]) && !empty($v1[$t]) && $now[$t] != $v1[$t] )) {
                        $edit_dops[$now['id']][$t] = $v1[$t];
                    }
                }
            }
        }

        // новые полные записи
        \f\pa($new_db, 2, '', '$new_db');
        \Nyos\mod\items::addNewSimples($db, \Nyos\mod\JobDesc::$mod_timeo, $new_db);
        \f\pa($edit_dops, 2, '', '$edit_dops');
        \Nyos\mod\items::saveNewDop($db, $edit_dops);
    }

    if (1 == 1 && class_exists('\\Nyos\\Msg')) {

        $e = 'Время ожидания'
                . PHP_EOL
                . 'Загрузили и записали '
                . PHP_EOL
                . '( точка / дата / холодный + горячий + доставка';

        if (!empty($new_db)) {
            foreach ($new_db as $v) {
                // \f\pa($v);
                $e .= PHP_EOL . $sps[$v['sale_point']]['head'] . '/' . $v['date'] . '/' . $v['cold'] . ' + ' . $v['hot'] . ' + ' . $v['delivery'];
            }
        } else {
            $e .= PHP_EOL . 'всё норм, ничего не добавили, не обновили';
        }

        \nyos\Msg::sendTelegramm($e, null, 2);
    }

    $r = ob_get_contents();
    ob_end_clean();

    // чистим кеш
    \f\Cash::deleteKeyPoFilter(['time_expectations', 'ds' . $date_start]);

    \f\end2('ok' . $r, true, ['load_kolvo' => sizeof($new_db ?? []), 'in_db' => sizeof($new_db ?? [])]);
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
