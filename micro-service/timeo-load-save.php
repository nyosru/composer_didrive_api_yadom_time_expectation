<?php

ini_set('display_errors', 'On'); // сообщения с ошибками будут показываться
error_reporting(E_ALL); // E_ALL - отображаем ВСЕ ошибки
//
//if ($_SERVER['HTTP_HOST'] == 'photo.uralweb.info' || $_SERVER['HTTP_HOST'] == 'yapdomik.uralweb.info' || $_SERVER['HTTP_HOST'] == 'a2.uralweb.info' || $_SERVER['HTTP_HOST'] == 'adomik.uralweb.info' || $_SERVER['HTTP_HOST'] == 'adomik.dev.uralweb.info'
//) {
//    date_default_timezone_set("Asia/Omsk");
//} else {
//    date_default_timezone_set("Asia/Yekaterinburg");
//}
//
//define('IN_NYOS_PROJECT', true);
//
//// ini_set("max_execution_time", 120);
//ini_set("max_execution_time", 59);
//
//header("Cache-Control: no-store, no-cache, must-revalidate");
//header("Expires: " . date("r"));
//
//require $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
//require( $_SERVER['DOCUMENT_ROOT'] . '/all/ajax.start.php' );
//


if (isset($skip_start) && $skip_start === true) {
    
} else {
    require_once '0start.php';
    $skip_start = false;
}





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
    if (strpos($_SERVER['HTTP_HOST'], 'dev') !== false)
        \f\pa($date_start, '', '', 'date_start');

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
            'date' => $date_start,
            's' => md5($link_sp_timeosp[$v['id']] . 'time' . $date_start)
        ];

        $uri = 'http://time-exp.uralweb.info/api.php?' . http_build_query($q);
        // \f\pa($uri,'','','uri');
        $res_ar0 = json_decode(file_get_contents($uri), true);
        // \f\pa($res_ar0, 2, '', 'данные с дата сервера res_ar0');
        $res_ar = [];
        foreach ($res_ar0['data'] as $k3 => $v3) {

            if (!isset($v3['hot']))
                $v3['hot'] = 0;
            if (!isset($v3['cold']))
                $v3['cold'] = 0;
            if (!isset($v3['delivery']))
                $v3['delivery'] = 0;

            $res_ar['data'][$k3] = $v3;
        }
        if (strpos($_SERVER['HTTP_HOST'], 'dev') !== false)
            \f\pa($res_ar, 2, '', 'данные с дата сервера res_ar');
        // die;

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
//        \Nyos\mod\items::$join_where = ' INNER JOIN `mitems-dops` mid '
//                . ' ON mid.id_item = mi.id '
//                . ' AND mid.name = \'date\' '
//                . ' AND mid.value_date >= :d '
//                . ' AND mid.value_date <= :d2 '
//                . ' INNER JOIN `mitems-dops` mid2 '
//                . ' ON mid2.id_item = mi.id '
//                . ' AND mid2.name = \'sale_point\' '
//                . ' AND mid2.value = :sp '
//        ;
//        \Nyos\mod\items::$var_ar_for_1sql[':d'] = $date_start;
//        \Nyos\mod\items::$var_ar_for_1sql[':d2'] = $date_finish;
//        \Nyos\mod\items::$var_ar_for_1sql[':sp'] = $_REQUEST['sp'];



        \Nyos\mod\items::$between['date'] = [$date_start, $date_finish];
        \Nyos\mod\items::$search['sale_point'] = $_REQUEST['sp'];
        $timeo = \Nyos\mod\items::get($db, \Nyos\mod\JobDesc::$mod_timeo);

        if (strpos($_SERVER['HTTP_HOST'], 'dev') !== false)
            \f\pa($timeo, 2, '', '$timeo в базе');
//        $timeo0 = [];
//
//        foreach ($timeo as $k5 => $v5) {
//            $timeo0[$v5['date']] = $v5;
//        }
//        \f\pa($timeo0, 2, '', '$timeo0 в базе');

        $time_in_db = [];

        /**
         * массив дат которые стираем и пишем новое значение
         */
        $clear_date = [];

        foreach ($timeo as $k => $v) {

            if (isset($ntime[$v['date']]))
                $clear_date[$v['date']] = 1;

            $time_in_db[$v['date']] = $v;
        }

        if (isset($_REQUEST['show']) && $_REQUEST['show'] == 'table') {
            \f\pa($time_in_db, 2, '', 'время из базы $time_in_db');
            \f\pa($clear_date, 2, '', 'трём даты так как две записи');
        }

        /**
         * массив допоов которые трём перед добавлением
         */
        $ar_clear_dops = [];

        /**
         * добавляем данные
         */
        $ar_adds = [];

        $add_d = [];

        for ($n = 0; $n <= 32; $n++) {

            $now_date = date('Y-m-d', strtotime($date_start . ' +' . $n . ' day'));

            if ($now_date > $date_finish)
                break;

            if ($now_date >= $now_real_date)
                break;

            if (strpos($_SERVER['HTTP_HOST'], 'dev') !== false)
                echo '<br/>date - ' . $now_date;

            if (isset($clear_date[$now_date])) {
                if (strpos($_SERVER['HTTP_HOST'], 'dev') !== false)
                    echo '<br/>эту дату трем ' . $now_date;
                $ar_clear_dops[] = ['sale_point' => $_REQUEST['sp'], 'date' => $now_date];
            }

//            if (isset($time_in_db[$now_date]))
//                \f\pa($time_in_db[$now_date], '', '');
//            if (isset($res_ar['data'][$now_date]))
//                \f\pa($res_ar['data'][$now_date], '', '');


            if (isset($res_ar['data'][$now_date]) && !isset($time_in_db[$now_date])) {
                if (strpos($_SERVER['HTTP_HOST'], 'dev') !== false)
                    echo '<br/>записываем новые данные';

                if (isset($_REQUEST['show']) && $_REQUEST['show'] == 'table') {

                    echo '<table class=table ><tr><td>';
                    \f\pa(( $time_in_db[$now_date] ?? []), '', '');
                    echo '</td><td>';
                    \f\pa(( $res_ar['data'][$now_date] ?? []), '', '');
                    echo '</td></tr></table>';
                }

                $ar_adds[] = [
                    'date' => $now_date,
                    'sale_point' => $_REQUEST['sp'],
                    'cold' => $res_ar['data'][$now_date]['cold']
                    , 'hot' => $res_ar['data'][$now_date]['hot']
                    , 'delivery' => $res_ar['data'][$now_date]['delivery']
                ];
            } elseif (
                    isset($res_ar['data'][$now_date]) && isset($time_in_db[$now_date]) && (
                    $res_ar['data'][$now_date]['cold'] != $time_in_db[$now_date]['cold'] || $res_ar['data'][$now_date]['hot'] != $time_in_db[$now_date]['hot'] || $res_ar['data'][$now_date]['delivery'] != $time_in_db[$now_date]['delivery']
                    )
            ) {
                if (strpos($_SERVER['HTTP_HOST'], 'dev') !== false)
                    echo '<br/>данные есть и не сходятся';

                if (isset($_REQUEST['show']) && $_REQUEST['show'] == 'table') {
                    echo '<table class=table ><tr><td>';
                    \f\pa(( $time_in_db[$now_date] ?? []), '', '');
                    echo '</td><td>';
                    \f\pa(( $res_ar['data'][$now_date] ?? []), '', '');
                    echo '</td></tr></table>';
                }

                $ar_clear_dops[] = ['sale_point' => $_REQUEST['sp'], 'date' => $now_date];

//                $ar_adds[] = [
//                    'date' => $now_date,
//                    'sale_point' => $_REQUEST['sp'],
//                    'cold' => $res_ar['data'][$now_date]['cold']
//                    , 'hot' => $res_ar['data'][$now_date]['hot']
//                    , 'delivery' => $res_ar['data'][$now_date]['delivery']
//                ];
            } else {
                echo '<br/>всё норм';
            }

            continue;








            $check_data_date = false;

            foreach ($timeo as $k1 => $v1) {
                if ($v1['date'] == $now_date) {

                    $check_data_date = true;

                    if (strpos($_SERVER['HTTP_HOST'], 'dev') !== false)
                        echo '<br/><hr><hr>уже есть';

                    if (strpos($_SERVER['HTTP_HOST'], 'dev') !== false) {
                        \f\pa($v1, '', '', 'что в базе');
                        \f\pa($res_ar['data'][$now_date], '', '', 'что загрузили с сервера');
                    }

                    if (
                            ( ( $v1['cold'] ?? 0 ) != ( $res_ar['data'][$now_date]['cold'] ?? 0 ) ) ||
                            ( ( $v1['hot'] ?? 0 ) != ( $res_ar['data'][$now_date]['hot'] ?? 0 ) ) ||
                            ( ( $v1['delivery'] ?? 0 ) != ( $res_ar['data'][$now_date]['delivery'] ?? 0 ) )
                    ) {

                        echo '<br/>пишем данные, расходятся';

                        $ar_adds[] = [
                            'date' => $now_date,
                            'info' => 'данные не сходятся',
                            'sale_point' => $_REQUEST['sp'],
                            'cold' => ( $v1['cold'] ?? 0 )
                            , 'hot' => ( $v1['hot'] ?? 0 )
                            , 'delivery' => ( $v1['delivery'] ?? 0 )
                        ];
                    }

                    continue;
                }
            }

            if (!empty($res_ar['data'][$now_date]['cold']) || !empty($res_ar['data'][$now_date]['hot']) || !empty($res_ar['data'][$now_date]['delivery'])) {
                if ($check_data_date === false) {
                    $ar_adds[] = [
                        'date' => $now_date,
                        'sale_point' => $_REQUEST['sp'],
                        'cold' => ( $v1['cold'] ?? 0 )
                        , 'hot' => ( $v1['hot'] ?? 0 )
                        , 'delivery' => ( $v1['delivery'] ?? 0 )
                    ];
                }
            }
        }

        // \f\pa($clear_date,2,'','$clear_date');

        if (strpos($_SERVER['HTTP_HOST'], 'dev') !== false)
            \f\pa($ar_adds, 2, '', '$ar_adds');

        // \Nyos\mod\items::addNewSimples($db, \Nyos\mod\JobDesc::$mod_timeo, $ar_adds);

        if (!empty($ar_adds))
            \Nyos\mod\items::adds($db, \Nyos\mod\JobDesc::$mod_timeo, $ar_adds);

        // \f\pa($ar_clear_dops, 2, '', '$ar_clear_dops');
        // \Nyos\mod\items::deleteFromDopsMany($db, \Nyos\mod\JobDesc::$mod_timeo, $ar_clear_dops);
        \f\Cash::deleteKeyPoFilter([\Nyos\mod\JobDesc::$mod_timeo]);

//        die();
//
//        $indb__sp_date_ceh_time = [];
//        foreach ($timeo as $k => $v) {
//
//            $indb__sp_date_ceh_time[$v['sale_point']][$v['date']]['id'] = $v['id'];
//
//            foreach ($type as $type1) {
//                if (isset($v[$type1]))
//                    $indb__sp_date_ceh_time[$v['sale_point']][$v['date']][$type1] = $v[$type1];
//            }
//        }
//        \f\pa($indb__sp_date_ceh_time, 2, '', '$indb__sp_date_ceh_time');
//
//        // foreach ($res_ar['data'] as $date => $ar) {
//        foreach ($res_ar['data'] as $date => $ar) {
//            
//        }

        if (1 == 2)
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
//            if( !isset($indb__sp_date_ceh_time[$v['sale_point']][$now_date]) )
//                continue;

                $ar = $indb__sp_date_ceh_time[$v['sale_point']][$now_date] ?? [];

                if (isset($ar['id'])) {
                    $now_id = $ar['id'];
                    unset($ar['id']);
                }

                if ($res_ar['data'][$now_date] != $ar || isset($_REQUEST['delete_old'])) {

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

    //\f\pa($new_db,'','','$new_db0');

    if (!empty($new_db)) {

        // стираем все даты+сп что пишем по новой
        if (1 == 1) {

            $datas = [];
            foreach ($new_db as $k => $v) {
                $datas[] = ['sale_point' => $v['sale_point'], 'date' => $v['date']];
            }

            if (!empty($datas)) {
                \Nyos\mod\items::deleteFromDopsMany($db, \Nyos\mod\JobDesc::$mod_timeo, $datas);
            }
        }

        if (isset($_REQUEST['show'])) {
            if (strpos($_SERVER['HTTP_HOST'], 'dev') !== false)
                echo '<br/>обновили дней:' . sizeof($new_db);
            //\f\pa($new_db, 2, '', '$new_db');
        }

        // \f\pa($new_db,'','','$new_db');
        $we = \Nyos\mod\items::addNewSimples($db, \Nyos\mod\JobDesc::$mod_timeo, $new_db);
        // \f\pa($we,'','','$we');
    } else {
        if (isset($_REQUEST['show']))
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
        if (strpos($_SERVER['HTTP_HOST'], 'dev') !== false)
            \f\pa($new_db, 2, '', '$new_db');
        \Nyos\mod\items::addNewSimples($db, \Nyos\mod\JobDesc::$mod_timeo, $new_db);
        if (strpos($_SERVER['HTTP_HOST'], 'dev') !== false)
            \f\pa($edit_dops, 2, '', '$edit_dops');
        \Nyos\mod\items::saveNewDop($db, $edit_dops);
    }


    if (1 == 1 && !isset($_REQUEST['skip_send_msg']) && class_exists('\\Nyos\\Msg')) {

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

    if (isset($_REQUEST['show']))
        echo $r;

    \f\end2('ok' . ( $r ?? '--' ), true, [
        'load_kolvo' => sizeof($new_db ?? [])
        ,
        'in_db' => sizeof($new_db ?? [])
        ,
        'new' => ( $new_db ?? [] )
        ,
        'request' => ( $_REQUEST ?? [] )
    ]);
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
