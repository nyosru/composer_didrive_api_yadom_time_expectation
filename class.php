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

        echo 
                '<br/> - '.self::$sql_host
                .'<br/> - '.self::$sql_port
                .'<br/> - '.self::$sql_base
                .'<br/> - '.self::$sql_login
                .'<br/> - '.self::$sql_pass;
        
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
            ;');

        //while ( $row=mysqli_fetch_array($podr)) { 
        $return = [];

        while ($row = mysqli_fetch_assoc($podr)) {

            if (!isset($return[$row['date']][$row['sp']][$row['ceh']])) {
                $return[$row['date']][$row['sp']][$row['ceh']] = $row['srednee_value'];
            } else {
                $return[$row['date']][$row['sp']][$row['ceh']] = round(($return[$row['date']][$row['sp']][$row['ceh']] + $row['srednee_value']) / 2, 1);
            }
            // echo '<pre>';
            //$row['mod_time2'] = date('Y-d-m H:i:s',$row['mod_time']);
            // print_r($row);
            // echo '</pre>';
        }

        \f\pa($return);
    }

}
