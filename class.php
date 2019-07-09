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
     * грузим среднее ожидания за промежуток дней от старт до сегодня
     * @param string $start_date
     */
    public static function getExpectation(string $start_date) {

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
                 
                    $aa = (int) ( array_sum($times)/sizeof($times) * 10);
                    $re[$date][$sp][$ceh] = $aa / 10;
           
                }
            }
        }

        return \f\end3('ok', true, $re);
    }

}
