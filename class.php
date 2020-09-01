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

    /**
     * переменная для блокировки записи полученных данных
     * если тру - то блокируем / если фальсе - то не блокируем и записываем
     * @var type 
     */
    public static $no_write_data = false;

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

    /**
     * получаем массив времени ожидания данных по точке продаж
     * @param type $db
     * @param string $sp_id
     * @param type $date_start
     * @param type $date_fin
     * @return type
     */
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
            // \f\pa($return);

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
    public static function getExpectation($db, $start_date = null, $mod_time_on_site = '074.time_expectations_list', $mod_link_sp_and_sp_on_server = '074.time_expectations_links_to_sp') {

        $connection = mysqli_connect(
                self::$sql_host . (!empty(self::$sql_port) ? ':' . self::$sql_port : '' )
                , self::$sql_login ?? ''
                , self::$sql_pass ?? ''
                , self::$sql_base ?? ''
        );


        $date_start_ok = date('Y-m-d', (!empty($start_date) ? strtotime($start_date) : $_SERVER['REQUEST_TIME'] - 3600 * 24 * 4));

        $podr = mysqli_query($connection, 'select 
                FROM_UNIXTIME( mod_time, \'%Y-%m-%d\' ) date,
                loc_id sp,
                dep_id ceh,
                FROM_UNIXTIME( mod_time, \'%H\' ) hour,
                round(AVG(dep_value),1) srednee_value
            from 
                `depTimeToday` 
            WHERE
                mod_time >= UNIX_TIMESTAMP(STR_TO_DATE(\'' . $date_start_ok . ' 00:00:01\', \'%Y-%m-%d %H:%i:%s\'))
            GROUP BY 
                FROM_UNIXTIME( mod_time, \'%Y-%m-%d %H\' )
            ORDER BY 
                mod_time ASC
            ;');

        $return2 = [];

        while ($row = mysqli_fetch_assoc($podr)) {

            // \f\pa( $row , 2 , null, 'массив из базы сервера о времени ожидания' );

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

        /**
         * достаём связи : id sp на сервере - id sp на сайте
         * массив : id sp на сервере - id sp на сайте
         */
        $list2 = \Nyos\mod\items::getItems($db, \Nyos\Nyos::$folder_now, $mod_link_sp_and_sp_on_server, 'show');
        $links_sp_and_sp_serv = [];
        foreach ($list2['data'] as $k11 => $v11) {
            $links_sp_and_sp_serv[$v11['dop']['id_timeserver']] = $v11['dop']['sale_point'];
        }
        // \f\pa($links_sp_and_sp_serv, 2, null, 'связь точек продаж с сервра времени с точками на сайте');

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

        // \f\pa($arr_srednee, 2, null, ' массив данных со средними значениями // дата - точка - цех - ср.время ');
        

/**
 * вычисляем даты по которым уже есть данные
 */
        $list5 = \Nyos\mod\items::getItemsSimple($db, $mod_time_on_site);

        $dtu3 = strtotime($date_start_ok);
        
        $list_est_data = [];
        
        foreach( $list5['data'] as $k => $v ){
            if( $dtu3 <= strtotime($v['dop']['date']) ){
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
                if( !isset($list_est_data[$r['date']][$r['sale_point']]) ){
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

            if( sizeof($rows) > 0 )
            \Nyos\mod\items::addNewSimples($db, $mod_time_on_site, $rows);
            
            self::$no_write_data = false;
        }

        return \f\end3('ok', true, $rows);
    }

}
