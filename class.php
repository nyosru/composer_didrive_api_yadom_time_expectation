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
                    .'
                    AND `date` >= :date_start 
                    '
                    .'
                    AND date <= :date_fin 
                    '
                    ;

            // echo '<pre>' . $sql . '</pre>';

            $ff = $db->prepare($sql);
            $db2 = array(
                ':sp_id' => $sp_id,
                ':date_start' => date('Y-m-d', strtotime($date_start)) ,
                ':date_fin' => date('Y-m-d', strtotime($date_fin)),
            );
            // \f\pa($db2);
            $ff->execute($db2);

            //$return = $ff->fetchAll();
            $return = [];
            while( $r = $ff->fetch() ){
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
     * грузим среднее ожидания за промежуток дней от старт до сегодня
     * @param string $start_date
     */
    public static function getExpectation( $db, string $start_date) {

        $connection = mysqli_connect(
                self::$sql_host . (!empty(self::$sql_port) ? ':' . self::$sql_port : '' )
                , self::$sql_login ?? ''
                , self::$sql_pass ?? ''
                , self::$sql_base ?? ''
        );

        $podr = mysqli_query($connection, 'select 
                loc_id sp,
                dep_id ceh,
                round(AVG(dep_value),1) srednee_value,
                FROM_UNIXTIME( mod_time, \'%Y-%m-%d\' ) date,
                FROM_UNIXTIME( mod_time, \'%H\' ) hour
            from 
                `depTimeToday` 
            WHERE
                mod_time >= UNIX_TIMESTAMP(STR_TO_DATE(\'' . date('Y-m-d', strtotime($start_date)) . ' 00:00:01\', \'%Y-%m-%d %H:%i:%s\'))
            GROUP BY 
                FROM_UNIXTIME( mod_time, \'%Y-%m-%d %H\' )
            ORDER BY 
                mod_time ASC
            ;');

        $return2 = [];

        while ($row = mysqli_fetch_assoc($podr)) {

            if (!isset($now_date)) {

                $now_date = $row['date'];
            } else if ((int) $row['hour'] >= 8) {

                $now_date = $row['date'];
            }

            if ($now_date == date('Y-m-d', $_SERVER['REQUEST_TIME']))
                continue;

            $return2[$now_date][$row['sp']][$row['ceh']][$row['hour']] = $row['srednee_value'];
        }

        $re = [];

        foreach ($return2 as $date => $v) {
            foreach ($v as $sp => $v1) {
                foreach ($v1 as $ceh => $times) {
                    $aa = (int) ( array_sum($times) / sizeof($times) * 10);
                    $re[$date][$sp][$ceh] = $aa / 10;
                }
            }
        }



        /**
         * достаём связи : id sp на сервере - id sp на сайте
         * массив : id sp на сервере - id sp на сайте
         */
        // echo '<br/>22результат в аякс файле ';
        $list2 = \Nyos\mod\items::getItems($db, \Nyos\Nyos::$folder_now, '074.time_expectations_links_to_sp', 'show');
        $links_sp_and_sp_serv = [];
        foreach ($list2['data'] as $k11 => $v11) {
            $links_sp_and_sp_serv[$v11['dop']['id_timeserver']] = $v11['dop']['sale_point'];
        }
        // \f\pa($links_sp_and_sp_serv);

        $r = [];

        foreach ($re as $date => $v) {
            $r['date'] = $date;

            foreach ($v as $sp => $v1) {

                if (!isset($links_sp_and_sp_serv[$sp]))
                    continue;
                    
                    $r['sp'] = $links_sp_and_sp_serv[$sp];
                    $r['sp_on_server'] = $sp;
                
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
                
                $rows[] = $r;
                $r = array( 'date' => $date );
            }
            
            
            $r = [];
        }

        // \f\pa($rows,2);
        // \f\db\sql_insert_mnogo($db, 'sushi_time_waiting', $rows, array( 'folder' => \Nyos\Nyos::$folder_now ) );
        \Nyos\mod\items::addNewSimples( $db, '074.time_expectations_list', $rows);

        return \f\end3('ok', true, $re);
    }

}
