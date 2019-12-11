<?php

/**
  класс модуля
 * */

namespace Nyos\api;

if (!defined('IN_NYOS_PROJECT'))
    throw new \Exception('Сработала защита от розовых хакеров, обратитесь к администрратору');

class JobExpectation {

    public static $sql_host = '';
    public static $sql_port = '';
    public static $sql_login = '';
    public static $sql_pass = '';
    public static $sql_base = '';
    public static $timer_last_cash_file = DR . '/0.temp/last_timer.json';

    /**
     * сохраняем список линков тп с тп с сервера
     * @var type 
     */
    public static $tmp_links_sp = [];

    /**
     * переменная для блокировки записи полученных данных
     * если тру - то блокируем / если фальсе - то не блокируем и записываем
     * @var type 
     */
    public static $no_write_data = false;

    /**
     * старая версия 190722
     * @param type $db
     */
    /*
      public static function creatTable($db) {


      $ff = $db->prepare('CREATE TABLE sushi_time_waiting (
      `id`   INTEGER       UNIQUE
      PRIMARY KEY AUTOINCREMENT
      NOT NULL,
      `folder` VARCHAR (50)  NOT NULL,

      `sp_on_server` INTEGER (10)  NOT NULL,
      `sp` INTEGER (10) DEFAULT NULL,

      `date` DATE NOT NULL,

      `hot` VARCHAR (10)  DEFAULT NULL,
      `cold` VARCHAR (10)  DEFAULT NULL,
      `delivery` VARCHAR (10)  DEFAULT NULL

      );');
      $ff->execute();

      //die('Созданы таблицы, перезагрузите страницу');
      \nyos\Msg::sendTelegramm('Создали таблицы для времени ожидания суши', null, 1);
      }
     */

    /**
     * получаем массив времени ожидания данных по точке продаж
     * старая версия 190722
     * @param type $db
     * @param string $sp_id
     * @param type $date_start
     * @param type $date_fin
     * @return type
     */
    /*
      public static function getTimerExpectation($db, string $sp_id, $date_start, $date_fin) {

      try {

      $sql = 'SELECT
      sp,
      date,
      hot,
      cold,
      delivery
      FROM
      `sushi_time_waiting`
      WHERE
      `sp` = :sp_id
      '
      . '
      AND `date` >= :date_start
      '
      . '
      AND date <= :date_fin
      '
      ;

      // echo '<pre>' . $sql . '</pre>';

      $ff = $db->prepare($sql);
      $db2 = array(
      ':sp_id' => $sp_id,
      ':date_start' => date('Y-m-d', strtotime($date_start)),
      ':date_fin' => date('Y-m-d', strtotime($date_fin)),
      );
      // \f\pa($db2);
      $ff->execute($db2);

      //$return = $ff->fetchAll();
      $return = [];
      while ($r = $ff->fetch()) {
      $return[$r['date']] = $r;
      }
      \f\pa($return);

      return $return;

      //        $return = [];
      //        while ($e = $ff->fetch()) {
      //            \f\pa($e);
      //            $return[$e['date'] ?? ''][$e['sp']][$e['otdel']] = $e['minut'];
      //        }
      //
      //        \f\pa($return);
      //        file_put_contents($cash_file, json_encode($return));
      } catch (\PDOException $ex) {

      //            echo '<pre>--- ' . __FILE__ . ' ' . __LINE__ . '-------'
      //            . PHP_EOL . $ex->getMessage() . ' #' . $ex->getCode()
      //            . PHP_EOL . $ex->getFile() . ' #' . $ex->getLine()
      //            . PHP_EOL . $ex->getTraceAsString()
      //            . '</pre>';

      if (strpos($ex->getMessage(), 'no such table') !== false) {
      self::creatTable($db);
      return array();
      }
      }

      //   return $return;
      }
     */

    /**
     * получаем время по цехам (в выбранном промежутке дат) из БД сайта
     * @param type $db
     * @param int $sp_id
     * @param type $date_start
     * @param type $date_fin
     * @return type
     */
    public static function getTimerExpectation($db, int $sp_id, $date_start, $date_fin) {
        //echo '<br/>== ' . $sp_id . ' , ' . $date_start . ' , ' . $date_fin;

        try {

//            \f\timer::start(121);
//            \f\CalcMemory::start(121);

            $cash_var = 'getTimerExpectation-' . $sp_id . '-' . $date_start . '-' . $date_fin;
            $e = \f\Cash::getVar($cash_var);
            //\f\pa($e);
            if (!empty($e)) {

//                echo '<br/>201: ' . \f\timer::stop('str', 121);
//                echo '<br/>211: ' . \f\CalcMemory::stop(121);

                return $e;
            }

            // \Nyos\mod\items::$show_sql = true;
            \Nyos\mod\items::$join_where = ' INNER JOIN `mitems-dops` md1 ON md1.id_item = mi.id AND md1.name = \'date\' AND md1.value_date >= :ds AND md1.value_date <= :df 
                    INNER JOIN `mitems-dops` md2 ON md2.id_item = mi.id AND md2.name = \'sale_point\' AND md2.value = :sp ';
            \Nyos\mod\items::$var_ar_for_1sql = [
                ':sp' => $sp_id,
                ':ds' => date('Y-m-d', strtotime($date_start)),
                ':df' => date('Y-m-d', strtotime($date_fin))
            ];
            $ret = \Nyos\mod\items::getItemsSimple3($db, '074.time_expectations_list');
            // \f\pa($ret, 5, '', 'ret');
            //$return = $ff->fetchAll();
            $return = [];
            foreach ($ret as $k => $v) {
                //\f\pa($v);
                $return[$v['date']] = $v;
            }
            // \f\pa($return);

            \f\Cash::setVar($cash_var, $return);

//            echo '<br/>20: ' . \f\timer::stop('str', 121);
//            echo '<br/>21: ' . \f\CalcMemory::stop(121);

            return $return;
        } catch (\PDOException $ex) {

//            echo '<pre>--- ' . __FILE__ . ' ' . __LINE__ . '-------'
//            . PHP_EOL . $ex->getMessage() . ' #' . $ex->getCode()
//            . PHP_EOL . $ex->getFile() . ' #' . $ex->getLine()
//            . PHP_EOL . $ex->getTraceAsString()
//            . '</pre>';

            if (strpos($ex->getMessage(), 'no such table') !== false) {
                self::creatTable($db);
                return array();
            }
        }

        //   return $return;
    }

    /**
     * сохраняем 
     * @param type $db
     * @param array $data
     * @param type $mod_time_expectation
     * @return type
     */
    public static function saveData($db, array $data, $mod_time_expectation = '074.time_expectations_list') {

        ksort($data);
        // \f\pa($data, 2, '', '$data 2222');

        $start_d = $fin_d = '';

        $res = \Nyos\mod\items::getItemsSimple($db, $mod_time_expectation);
        //\f\pa($res, 2, '', 'что уже в базе');
        $on_db = [];
        foreach ($res['data'] as $k => $v) {
            $v['dop']['id'] = $v['id'];
            $on_db[$v['dop']['date']][$v['dop']['sale_point']] = $v['dop'];
        }
        // \f\pa($on_db, 2, '', 'что уже в базе 2');

        $r = [];

        foreach ($data as $date => $v) {

            if (empty($start_d))
                $start_d = $date;

            $fin_d = $date;

            foreach ($v as $sp => $v1) {

                if (!isset($on_db[$date][$sp])) {

                    $in = array('date' => $date,
                        'sale_point' => $sp);

                    foreach ($v1 as $ceh => $times) {
                        $in[$ceh] = $times;
                    }

                    $r[] = $in;
                }
            }
        }

        //\f\pa($est, 2, '', 'что уже в базе');

        if (1 == 2) {
            // echo $start_d .' - '. $fin_d;
            // \f\pa($r, 2, '', '$r');

            $ff = $db->prepare('SELECT 
                mi.id
                ,
                mid.value_date
            FROM 
                mitems mi
                
            INNER JOIN `mitems-dops` mid ON mid.id_item = mi.id AND mid.name = \'date\' AND ( mid.value_date >= \'' . $start_d . '\' AND mid.value_date <= \'' . $fin_d . '\' )
                
            WHERE 
                mi.module = :mod
                AND mi.status = \'show\'
            ');
            $ff->execute(array(':mod' => $mod_time_expectation));

            $sq3 = '';
            //\f\pa( $ff->fetchAll() );
            while ($q = $ff->fetch()) {
                $sq3 .= (!empty($sq3) ? 'OR' : '' ) . ' `id` = \'' . $q['id'] . '\' ';
            }
            /**
             * удаляем старое
             */
            if (!empty($sq3)) {
                $sql = 'DELETE FROM mitems 
            WHERE 
                module = :mod '
                //.' AND ( ' . $sq3 . ' ) '
                ;
                //echo $sql;
                $ff = $db->prepare($sql);
                $ff->execute(array(':mod' => $mod_time_expectation));
            }
        }

        /**
         * добавляем новое
         */
        \Nyos\mod\items::addNewSimples($db, $mod_time_expectation, $r);
        // \f\pa($r, 2, '', '$r');

        return $r;
    }

    /**
     * получаем время ожидания по умолчанию для цехов и пишем в модуль что определили в переменной
     * если не определили в параметрах модуль а ноль .. то только массив возвращаем
     * @param type $db
     * @param type $mod_for_time_default
     * @return type
     */
    public static function getExpectationFromServerDefaultTime($db, $mod_for_time_default = '074.time_expectations_default') {

        $connection = mysqli_connect(
                self::$sql_host . (!empty(self::$sql_port) ? ':' . self::$sql_port : '' )
                , self::$sql_login ?? ''
                , self::$sql_pass ?? ''
                , self::$sql_base ?? ''
        );

        $podr = mysqli_query($connection, 'select '
                . ' * '
//                .' FROM_UNIXTIME( mod_time, \'%Y-%m-%d\' ) date,
//                loc_id sp,
//                dep_id ceh,
//                FROM_UNIXTIME( mod_time, \'%H\' ) hour,
//                round(AVG(dep_value),1) srednee_value
//                '
                . '
            from 
                `department` 
                '
//            ' WHERE
//                mod_time >= UNIX_TIMESTAMP(STR_TO_DATE(\'' . date('Y-m-d', strtotime($start_date)) . ' 00:00:01\', \'%Y-%m-%d %H:%i:%s\'))
//                '
//                .'
//            GROUP BY 
//                FROM_UNIXTIME( mod_time, \'%Y-%m-%d %H\' )
//                '
//                .'
//            ORDER BY 
//                mod_time ASC
//                '
//            .'
                . ' ;');

        $def = [];

        while ($row = mysqli_fetch_assoc($podr)) {

            // \f\pa( $row , 2 , null, 'значения по умолчанию если нет данных по средней цифре' );
            if (empty($mod_for_time_default)) {
                $def[$row['id']] = $row['d_default'];
            } else {
                $def[] = array('otdel' => $row['id'], 'default' => $row['d_default']);
            }
        }

        if (!empty($mod_for_time_default)) {

            // удаляем данные что есть
            \Nyos\mod\items::deleteItems($db, \Nyos\Nyos::$folder_now, $mod_for_time_default);
            // пишем новые данные что загрузили выше
            \Nyos\mod\items::addNewSimples($db, $mod_for_time_default, $def);
        }

        return \f\end3('окей загрузили данные по умолчанию для времени ожидания по цехам', true, $def);
    }

    /**
     * грузим среднее ожидания за промежуток дней от старт до сегодня
     * @param string $start_date
     */
    public static function getExpectationFromPeriod($db, $start_date, $date_finish) {

        return self::getExpectation($db, $start_date, $date_finish, '074.time_expectations_list', '074.time_expectations_links_to_sp');
    }

    public static function getLinksSpAndSpOnServ($db, $mod = '074.time_expectations_links_to_sp') {

        /**
         * если не пустой массив (ранее заполнили) то его и показываем
         */
        if (!empty(self::$tmp_links_sp))
            return self::$tmp_links_sp;

        /**
         * достаём связи : id sp на сервере - id sp на сайте
         * массив : id sp на сервере - id sp на сайте
         */
        // $list2 = \Nyos\mod\items::getItems($db, \Nyos\Nyos::$folder_now, $mod, 'show');
        $list2 = \Nyos\mod\items::getItemsSimple3($db, $mod);

        foreach ($list2 as $k11 => $v11) {
            // связка id На сервере и id на сайте
            self::$tmp_links_sp['links_sp_serv_and_sp'][$v11['id_timeserver']] = $v11['sale_point'];
            // связка id на сайте и id На сервере 
            self::$tmp_links_sp['links_sp_and_sp_serv'][$v11['sale_point']] = $v11['id_timeserver'];
        }
        // \f\pa($links_sp_and_sp_serv, 2, null, 'связь точек продаж с сервра времени с точками на сайте');

        return self::$tmp_links_sp;
    }

    /**
     * грузим среднее ожидания за промежуток дней от старт до сегодня
     * @param string $start_date
     */
    public static function getExpectation($db, $start_date = null, $date_finish = null, $sp_now = null, $mod_time_on_site = '074.time_expectations_list', $mod_link_sp_and_sp_on_server = '074.time_expectations_links_to_sp') {

        if (isset($_REQUEST['show_dop_info']))
            echo '<br/>' . $start_date . ' - ' . $date_finish . ' -- ' . $sp_now;

        // $links = getLinksSpAndSpOnServ( $db , $mod_link_sp_and_sp_on_server );
        $links = self::getLinksSpAndSpOnServ($db);

        if (isset($_REQUEST['show_dop_info'])) {
            \f\pa($links, 2, '', '$links');
        }

        // связка id На сервере и id на сайте
        // $links['links_sp_and_sp_serv']
        // связка id на сайте и id На сервере 
        // $links['links_sp_serv_and_sp']

        $connection = mysqli_connect(
                self::$sql_host . (!empty(self::$sql_port) ? ':' . self::$sql_port : '' )
                , self::$sql_login ?? ''
                , self::$sql_pass ?? ''
                , self::$sql_base ?? ''
        );

//        echo $start_date;
//        echo '<br/>';
//        echo date( 'y.m.d H:i ', strtotime($start_date) );
//        echo '<br/>';

        if (!empty($start_date) && !empty($date_finish) && $start_date == $date_finish) {

            $time_start = strtotime($start_date) + 3600 * 9;
            $time_fin = strtotime($start_date) + 3600 * 29;
        } else {
            $date_start_ok = date('Y-m-d', (!empty($start_date) ? strtotime($start_date) : $_SERVER['REQUEST_TIME'] - 3600 * 24 * 4));
            $date_fin_ok = (!empty($date_finish) ) ? date('Y-m-d', strtotime($date_finish)) : null;
        }
        // echo '<br/>2 - '.$date_fin_ok;
        //echo $links_sp_serv_and_sp[$_REQUEST['sp']];

        $sq2 = '';

        foreach ($links['links_sp_serv_and_sp'] as $id_sp_serv => $id_sp_local) {

            $sq2 .= (!empty($sq2) ? ' OR ' : '' ) . ' `loc_id` = \'' . $id_sp_local . '\' ';
        }

        $sql = 'select 
                FROM_UNIXTIME( mod_time, \'%Y-%m-%d\' ) date,
                loc_id sp,
                dep_id ceh,
                FROM_UNIXTIME( mod_time, \'%H\' ) hour,
                FROM_UNIXTIME( mod_time, \'%m\' ) min,
                mod_time,
                dep_value value
            from 
                `depTimeToday` 
            WHERE ';
        //. ' ( ' . $sq2 . ' ) '
        // .' mod_time >= UNIX_TIMESTAMP(STR_TO_DATE(\'' . $date_start_ok . ' 00:00:01\', \'%Y-%m-%d %H:%i:%s\'))  '
        //. ' AND '
        if (!empty($time_start) && !empty($time_fin)) {
            $sql .= ' mod_time >= ' . $time_start . ' AND mod_time <= ' . $time_fin . ' ';
        } else {
            $sql .= ' mod_time >= ' . ( strtotime($start_date) + 3600 * 9 )
                    . ( isset($date_fin_ok{9}) ? ' AND mod_time <= ' . strtotime($date_fin_ok) : '' );
        }

        if (!empty($sp_now) && isset($links['links_sp_and_sp_serv'][$sp_now])) {
            $sql .= ' AND loc_id <= ' . $links['links_sp_and_sp_serv'][$sp_now] . ' ';
        }



        $sql .= ' ORDER BY 
                loc_id ASC,
                mod_time ASC
            ;';

        if (isset($_REQUEST['show_dop_info'])) {
            echo '<pre>' . $sql . '</pre>';
        }

        $podr = mysqli_query($connection, $sql);

        $return3 = [];
        $return47 = [];

//        echo '<br/>'.$date_start_ok;
//        echo '<br/>'.date( 'y.m.d H:i', strtotime($date_start_ok)+3600*9 );

        if (mysqli_num_rows($podr) == 0) {
            // echo '<br/>строк ' . mysqli_num_rows($podr);
            return false;
        }

        while ($row = mysqli_fetch_assoc($podr)) {

            // \f\pa($row, 2, null, 'массив из базы сервера о времени ожидания');

            if (!isset($now_date)) {

                $now_date = $row['date'];
            } else if ((int) $row['hour'] >= 8) {

                $now_date = $row['date'];
            }

            if ($now_date == date('Y-m-d', $_SERVER['REQUEST_TIME']))
                continue;

            //$return2[$now_date][$row['sp']][$row['ceh']][(int) $row['hour']] = $row['srednee_value'];
            //$return3[$now_date][$row['sp']][$row['ceh']][date('H:i', $row['mod_time'])] = $row['value'];
            $return3[$now_date][$row['sp']][$row['ceh']][$row['mod_time']] = $row['value'];
        }

        if (isset($_REQUEST['show_dop_info'])) {
            \f\pa($return3, 2, '', '$return3');
        }

        foreach ($return3 as $kdate => $v) {

//            if ($kdate != '2019-07-21')
//                continue;

            $d_start = strtotime($kdate . ' 10:00:00');
            $d_fin = strtotime($kdate . ' 01:00:00') + 3600 * 24;

            //echo '<h3>+ ' . $kdate . '</h3>';
//            echo '<h5>+ ' . date( 'Y.m.d H:i',$d_start) . '</h5>';
//            echo '<h5>+ ' . date( 'Y.m.d H:i',$d_fin) . '</h5>';

            $periods = ($d_fin - $d_start) / (60 * 5);
//            echo '<hr>' . $periods;

            foreach ($v as $ksp => $v2) {

                if (!isset($links['links_sp_serv_and_sp'][$ksp]))
                    continue;

//                if ($ksp != 1)
//                    continue;
                // echo '<hr>sp ' . $ksp . '';

                foreach ($v2 as $kceh => $v3) {

                    //echo '<br/>( ' . $kceh . ' )';
//                    echo '<hr>';

                    ksort($v3);

//                    echo '<style> td{vertical-align:top;} </style><table><tr><td>';
//                    foreach ($v3 as $kt => $val) {
//                        echo '<br/>';
//                        echo '[' . $kt . '] ';
//                        echo date('y.m.d H:i', $kt) . ' ' . $val;
//                        // echo ' <abbr title="'.date('y.m.d H:i',$kt).'" >--'.date('y.m.d H:i',$kt).'+' . $val .'</abbr>';
//                    }


                    if ($kceh == 3) {
                        $val_now = 80;
                    } elseif ($kceh == 1 || $kceh == 2) {
                        $val_now = 10;
                    }

                    $return = [];
                    $times = [];
                    $times2 = [];

//                    echo '</td><td>';

                    for ($th = 0; $th <= $periods; $th++) {

                        $now_time = $d_start + 60 * 5 * $th;
                        $next_time = $now_time + 60 * 5;

                        foreach ($v3 as $kt => $val) {

                            if ($now_time <= $kt && $next_time > $kt) {
                                // echo '<br/>- '.date('y.m.d H:i', $now_time) . ' < ' . date('y.m.d H:i', $kt) . ' < ' . date('y.m.d H:i', $next_time) . ' ';
                                // echo '<br/>' . '['.$val.']';
                                $val_now = $val;
                                break;
                            }
                        }

//                         echo '<br/>' . date('d H:i', $now_time) . '=' . date('d H:i', $next_time);
//                         echo '[['.$val_now.']]';

                        $times2[] = $val_now;
                    }

                    $average = array_sum($times2) / count($times2);
                    // echo 'среднее ' . (int) $average;

                    if ($kceh == 1) {
                        $return47[$kdate][$links['links_sp_serv_and_sp'][$ksp]]['cold'] = (int) $average;
                    } elseif ($kceh == 2) {
                        $return47[$kdate][$links['links_sp_serv_and_sp'][$ksp]]['hot'] = (int) $average;
                    } elseif ($kceh == 3) {
                        $return47[$kdate][$links['links_sp_serv_and_sp'][$ksp]]['delivery'] = (int) $average;
                    }

                    // echo '<br/>';
                    // echo date( 'H:m', $min );
//                    echo '<hr>';
//                    echo '<hr>';
//                    break;
                }
            }
        }

        //\f\pa($return47, 2, null, '$return47 массив данных с сервера ');

        return $return47;
    }

    /**
     * получаем цифру последнюю по точке и цеху
     * @param int $id_sp_time
     * @param type $ceh
     * @return boolean
     */
    public static function getExpectationLastOne($id_sp_time = null, $ceh = 1) {

//        echo date('Y-m-d H:i', filectime(self::$timer_last_cash_file) );
//        echo '<br/>';
//        echo date('Y-m-d H:i', filemtime(self::$timer_last_cash_file) );
//        echo '<br/>';
//        echo date('Y-m-d H:i', $_SERVER['REQUEST_TIME'] );
//        echo '<br/>';
//        echo date('Y-m-d H:i', $_SERVER['REQUEST_TIME'] - 60*5 );
//        echo '<br/>';
        // if (file_exists(self::$timer_last_cash_file) && ( filectime(self::$timer_last_cash_file) > $_SERVER['REQUEST_TIME'] - 60 * 5 )) {
        if (file_exists(self::$timer_last_cash_file) && ( filectime(self::$timer_last_cash_file) > $_SERVER['REQUEST_TIME'] - 30 )) {

            $e = json_decode(file_get_contents(self::$timer_last_cash_file), true);
//            echo '<br/>'.__LINE__;
//            echo '<br/>'.$id_sp_time;
//            \f\pa($e,2,'','из дамп файла');

            if (isset($e[$id_sp_time][$ceh])) {

//                echo '<br/>'.__LINE__;
//                \f\pa($e);

                return \f\end3('ок из дампа', true, array('timer' => $e[$id_sp_time][$ceh]));
            }
        }

        //echo __LINE__;
//        echo '<hr>';
//        echo $_SERVER['REQUEST_TIME'];
//        echo '<hr>';

        $connection = mysqli_connect(
                self::$sql_host . (!empty(self::$sql_port) ? ':' . self::$sql_port : '' )
                , self::$sql_login ?? ''
                , self::$sql_pass ?? ''
                , self::$sql_base ?? ''
        );

        $sql = 'SELECT '
                . ' * '
//                .' , '
//                .' FROM_UNIXTIME( mod_time, \'%Y-%m-%d\' ) as date '
                . ' , '
                . ' FROM_UNIXTIME( mod_time, \'%Y-%m-%d %H:%m:00\' ) as dt '
                . ' , '
                . ' loc_id sp_time_id '
                . ' , '
                . ' dep_id ceh '
//                .' , '
//                .' FROM_UNIXTIME( mod_time, \'%H\' ) hour '
//                .' , '
//                .' FROM_UNIXTIME( mod_time, \'%m\' ) min '
                . ' , '
                . ' mod_time '
                . ' , '
                . ' dep_value value '
                . ' FROM '
                . ' `depTimeToday` '
                . ' WHERE '

//                .' id > 87306 '
//                .( date('H',$_SERVER['REQUEST_TIME']) > 5
                . ' mod_time > ' . strtotime(date('Y-m-d', $_SERVER['REQUEST_TIME'] - 3600 * 24) . ' 03:00:00')

//                loc_id = ' . $id_sp_time . ' 
//                    AND dep_id = ' . (int) $ceh 
//                . ' GROUP BY 
//                loc_id, 
//                dep_id
//            '
                . ' ORDER BY '
                . ' id DESC '
                // . ' FROM_UNIXTIME( mod_time, \'%Y-%m-%d %H %m\' ) DESC'
//                . 'dt DESC'
//                . ' , '
//                . ' loc_id ASC'
//                . ' , '
//                . 'dep_id ASC '
                . ' LIMIT 0,500 '
                . ' ;';

        if (isset($_REQUEST['show_dop_info']) || 1 == 2) {
            echo '<pre>' . $sql . '</pre>';
        }

        $podr = mysqli_query($connection, $sql);

//        echo '<Br/>2 -- ' . mysqli_error($podr);
//        echo '<Br/>3 -- ' . mysqli_errno($podr);

        $return3 = [];
        $return47 = [];

//        echo '<br/>'.$date_start_ok;
//        echo '<br/>'.date( 'y.m.d H:i', strtotime($date_start_ok)+3600*9 );

        if (mysqli_num_rows($podr) == 0) {
            // echo '<br/>строк ' . mysqli_num_rows($podr);
            return false;
        }

        $last_id = null;
        $return = [];

        $nn = 1;

//        echo '<table class=table >';

        while ($row = mysqli_fetch_assoc($podr)) {

            if (empty($last_id) && ( isset($last_id) && $last_id < $row['id'] )) {
                $last_id = $row['id'];
            }

//            if ($nn == 1) {
//                echo '<tr>';
//                foreach ($row as $k => $v) {
//                    echo '<td>' . $k . '</td>';
//                }
//                echo '</tr>';
//            }
//
//            echo '<tr>';
//            foreach ($row as $k => $v) {
//                echo '<td>' . $v . '</td>';
//            }
//            echo '</tr>';
//
//            $row['dt'] = date('Y-m-d H:i:s', $row['mod_time']);
//            
            if (!isset($return[$row['sp_time_id']][$row['ceh']]))
            //$return[$row['sp_time_id']][$row['ceh']] = $row;
                $return[$row['sp_time_id']][$row['ceh']] = $row['value'];

            $nn++;
        }

        // echo '</table>';
        // \f\pa($return, 2, null, 'массив из базы сервера о времени ожидания');
        // \f\pa($return[3][1], 2, null, 'массив из базы сервера о времени ожидания');

        file_put_contents(self::$timer_last_cash_file, json_encode($return));

        if (isset($return[$id_sp_time][$ceh])) {
            return \f\end3('ок получили', true, array('timer' => $return[$id_sp_time][$ceh]));
        }

        return $return;
    }

    public static function getExpectation_old190723($db, $start_date = null, $mod_time_on_site = '074.time_expectations_list', $mod_link_sp_and_sp_on_server = '074.time_expectations_links_to_sp', $date_finish = null) {

        $connection = mysqli_connect(
                self::$sql_host . (!empty(self::$sql_port) ? ':' . self::$sql_port : '' )
                , self::$sql_login ?? ''
                , self::$sql_pass ?? ''
                , self::$sql_base ?? ''
        );

        /**
         * достаём связи : id sp на сервере - id sp на сайте
         * массив : id sp на сервере - id sp на сайте
         */
        $list2 = \Nyos\mod\items::getItems($db, \Nyos\Nyos::$folder_now, $mod_link_sp_and_sp_on_server, 'show');
        //\f\pa($list2, 2, '', '$list2');
        $links_sp_and_sp_serv = [];
        foreach ($list2['data'] as $k11 => $v11) {
            /**
             * связка id На сервере и id на сайте
             */
            $links_sp_and_sp_serv[$v11['dop']['id_timeserver']] = $v11['dop']['sale_point'];
            /**
             * связка id на сайте и id На сервере 
             */
            $links_sp_serv_and_sp[$v11['dop']['sale_point']] = $v11['dop']['id_timeserver'];
        }
        // \f\pa($links_sp_and_sp_serv, 2, null, 'связь точек продаж с сервра времени с точками на сайте');

        $date_start_ok = date('Y-m-d', (!empty($start_date) ? strtotime($start_date) : $_SERVER['REQUEST_TIME'] - 3600 * 24 * 4));
        $date_fin_ok = (!empty($date_finish) ) ? date('Y-m-d', strtotime($date_finish) + 3600 * 24) : null;

        //echo $links_sp_serv_and_sp[$_REQUEST['sp']];

        $podr = mysqli_query($connection, 'select 
                FROM_UNIXTIME( mod_time, \'%Y-%m-%d\' ) date,
                loc_id sp,
                dep_id ceh,
                FROM_UNIXTIME( mod_time, \'%H\' ) hour,
                round(AVG(dep_value),1) srednee_value
            from 
                `depTimeToday` 
            WHERE
                mod_time >= UNIX_TIMESTAMP(STR_TO_DATE(\'' . $date_start_ok . ' 00:00:01\', \'%Y-%m-%d %H:%i:%s\')) '
                . (!empty($date_fin_ok) ? ' AND mod_time <= UNIX_TIMESTAMP(STR_TO_DATE(\'' . $date_fin_ok . ' 23:59:01\', \'%Y-%m-%d %H:%i:%s\')) ' : '' )
                . (!empty($_REQUEST['sp']) ? ' AND loc_id = \'' . $links_sp_serv_and_sp[$_REQUEST['sp']] . '\' ' : '' )
                . ' GROUP BY 
                FROM_UNIXTIME( mod_time, \'%Y-%m-%d %H\' )
            ORDER BY 
                mod_time ASC
            ;');

        $return2 = [];

        while ($row = mysqli_fetch_assoc($podr)) {

            //\f\pa( $row , 2 , null, 'массив из базы сервера о времени ожидания' );

            if (!isset($now_date)) {

                $now_date = $row['date'];
            } else if ((int) $row['hour'] >= 8) {

                $now_date = $row['date'];
            }

            if ($now_date == date('Y-m-d', $_SERVER['REQUEST_TIME']))
                continue;

            $return2[$now_date][$row['sp']][$row['ceh']][(int) $row['hour']] = $row['srednee_value'];
        }

        //\f\pa($return2, 2, null, ' массив данных с сервера ');


        $arr_srednee = [];

        foreach ($return2 as $date => $v) {
            foreach ($v as $sp => $v1) {


                if (!isset($links_sp_and_sp_serv[$sp]))
                    continue;

                foreach ($v1 as $ceh => $times) {
                    $arr_srednee[$date][$sp][$ceh] = (int) ( ( array_sum($times) / count(array_filter($times)) ) * 10 ) / 10;
                }
            }
        }

        //\f\pa($arr_srednee, 2, null, ' массив данных со средними значениями // дата - точка - цех - ср.время ');


        /**
         * вычисляем даты по которым уже есть данные
         */
        $list5 = \Nyos\mod\items::getItemsSimple($db, $mod_time_on_site);

        $dtu3 = strtotime($date_start_ok);

        $list_est_data = [];

        if (!empty($list5['data']))
            foreach ($list5['data'] as $k => $v) {
                if ($dtu3 <= strtotime($v['dop']['date'])) {
                    $list_est_data[$v['dop']['date']][$v['dop']['sale_point']] = 1;
                }
            }

        //\f\pa($list5, 2, '', '$list5 текущие данные в базе');
        // \f\pa($list_est_data, 2, '', '$list_now_data текущие данные в базе');
//        $links_sp_and_sp_serv = [];
//        foreach ($list2['data'] as $k11 => $v11) {
//            $links_sp_and_sp_serv[$v11['dop']['id_timeserver']] = $v11['dop']['sale_point'];
//        }


        /**
         * вычисляем среднее значение за день и добавляем номер точки продаж на сайте
         */
        $rows = $r = [];

        foreach ($arr_srednee as $date => $v) {
            $r['date'] = $date;

            foreach ($v as $sp => $v1) {

//                if (!isset($links_sp_and_sp_serv[$sp]))
//                    continue;

                $r['sale_point'] = $links_sp_and_sp_serv[$sp];
                $r['sp_on_serv'] = $sp;

                foreach ($v1 as $ceh => $times) {
                    // $aa = (int) ( array_sum($times) / sizeof($times) * 10) / 10;
                    // $re[$date][$sp][$ceh] = $aa;

                    if ($ceh == 1) {
                        $r['cold'] = $times;
                    } elseif ($ceh == 2) {
                        $r['hot'] = $times;
                    } elseif ($ceh == 3) {
                        $r['delivery'] = $times;
                    }
                }

                // \f\db\db2_insert($db, 'sushi_time_waiting', $r );
                // если нет таких данных, то пишем в массив для записи
                if (!isset($list_est_data[$r['date']][$r['sale_point']])) {
                    //\f\pa($r);
                    $rows[] = $r;
                }

                $r = array('date' => $date);
            }


            $r = [];
        }

        // \f\pa($rows, 2, null, '$rows');


        /**
         * добавляем данные на сайт
         */
        // \f\db\sql_insert_mnogo($db, 'sushi_time_waiting', $rows, array( 'folder' => \Nyos\Nyos::$folder_now ) );
        if (self::$no_write_data === true) {
            echo 'Запись данных отменена';
        } else {

            if (sizeof($rows) > 0)
                \Nyos\mod\items::addNewSimples($db, $mod_time_on_site, $rows);

            self::$no_write_data = false;
        }

        return \f\end3('ok', true, $rows);
    }

}
