<?php

ini_set('display_errors', 'On'); // сообщения с ошибками будут показываться
error_reporting(E_ALL); // E_ALL - отображаем ВСЕ ошибки

date_default_timezone_set("Asia/Yekaterinburg");
define('IN_NYOS_PROJECT', true);

require $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
require( $_SERVER['DOCUMENT_ROOT'] . '/all/ajax.start.php' );


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


foreach (\Nyos\Nyos::$menu as $k => $v) {
    if ( isset($v['type_api']) && $v['type_api'] == 'time_expectation') {

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


if ( isset($_GET['action']) && $_GET['action'] == 'get_times' ) {

    $e = \Nyos\api\JobExpectation::getExpectation( $_GET['date'] ?? date('Y-m-d',$_SERVER['REQUEST_TIME']-3600*24*4) );
    \f\pa($e);

    \f\end2('ok');
    
}




//require_once( $_SERVER['DOCUMENT_ROOT'] . '/0.all/sql.start.php');
//require( $_SERVER['DOCUMENT_ROOT'] . '/0.site/0.cfg.start.php');
//require( $_SERVER['DOCUMENT_ROOT'] . DS . '0.all' . DS . 'class' . DS . 'mysql.php' );
//require( $_SERVER['DOCUMENT_ROOT'] . DS . '0.all' . DS . 'db.connector.php' );
// добавляем смену сотруднику
//if (isset($_POST['action']) && ( $_POST['action'] == 'delete_smena' || $_POST['action'] == 'delete_comment') ) {
//
//    // require_once DR . '/all/ajax.start.php';
//
//    $ff = $db->prepare('UPDATE `mitems` SET `status` = \'hide\' WHERE `id` = :id ');
//    $ff->execute(array(':id' => (int) $_POST['id2']));
//
//    \f\end2('удалено');
//}
////
//elseif (isset($_POST['action']) && $_POST['action'] == 'recover_smena') {
//
//    require_once DR . '/all/ajax.start.php';
//
//    $ff = $db->prepare('UPDATE `mitems` SET `status` = \'show\' WHERE `id` = :id ');
//    $ff->execute(array(':id' => (int) $_POST['id2']));
//
//    \f\end2('смена восстановлена');
//}
////
//elseif (
//        isset($_POST['action']) && ( 
//        $_POST['action'] == 'add_new_smena' || 
//        $_POST['action'] == 'add_comment' || 
//        $_POST['action'] == 'confirm_smena' || 
//        $_POST['action'] == 'goto_other_sp'
//        )
//) {
//    // action=add_new_smena
//
//    try {
//
//        //require_once DR . '/all/ajax.start.php';
//
//        // action=add_new_smena
//        // \f\pa($_POST);
//        // [date] => 2019-06-27
//        // [toform_sp] => 2611
//        // [action] => goto_other_sp
//        // [id2] => 10    
//        // [jobman] => 1886        
//        /**
//         * отправляем сотрудника на другую точку
//         */
//        if ($_POST['action'] == 'goto_other_sp') {
//
////            if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php'))
////                require $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
////
////            if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/vendor/didrive_mod/items/class.php'))
////                require ($_SERVER['DOCUMENT_ROOT'] . '/vendor/didrive_mod/items/class.php');
//
//            // если старт часов меньше часов сдачи
//            if (strtotime($_REQUEST['start_time']) > strtotime($_REQUEST['fin_time'])) {
//                //$b .= '<br/>'.__LINE__;
//                $start_time = strtotime($_REQUEST['date'] . ' ' . $_REQUEST['start_time']);
//                $fin_time = strtotime($_REQUEST['date'] . ' ' . $_REQUEST['fin_time']) + 3600 * 24;
//            }
//            // если старт часов больше часов сдачи
//            else {
//                //$b .= '<br/>'.__LINE__;
//                $start_time = strtotime($_REQUEST['date'] . ' ' . $_REQUEST['start_time']);
//                $fin_time = strtotime($_REQUEST['date'] . ' ' . $_REQUEST['fin_time']);
//            }
//
//            \Nyos\mod\items::addNew($db, $vv['folder'], \Nyos\nyos::$menu['050.chekin_checkout'], array(
//                'head' => rand(100, 100000),
//                'jobman' => $_REQUEST['jobman'],
//                'sale_point' => $_REQUEST['salepoint'],
//                'start' => date('Y-m-d H:i', $start_time),
//                'fin' => date('Y-m-d H:i', $fin_time)
//            ));
//
//            \f\end2('<div>'
//                    . '<nobr><b class="warn" >смена добавлена</b>'
//                    . '<br/>'
//                    . date('d.m.y H:i', $start_time) . ' - ' . date('d.m.y H:i', $fin_time)
//                    . '</nobr>'
//                    . '</div>', true);
//        }
//        //
//        elseif ($_POST['action'] == 'add_new_smena') {
//
////            if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php'))
////                require $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
////
////            if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/vendor/didrive_mod/items/class.php'))
////                require ($_SERVER['DOCUMENT_ROOT'] . '/vendor/didrive_mod/items/class.php');
//
//            // если старт часов меньше часов сдачи
//            if (strtotime($_REQUEST['start_time']) > strtotime($_REQUEST['fin_time'])) {
//                //$b .= '<br/>'.__LINE__;
//                $start_time = strtotime($_REQUEST['date'] . ' ' . $_REQUEST['start_time']);
//                $fin_time = strtotime($_REQUEST['date'] . ' ' . $_REQUEST['fin_time']) + 3600 * 24;
//            }
//            // если старт часов больше часов сдачи
//            else {
//                //$b .= '<br/>'.__LINE__;
//                $start_time = strtotime($_REQUEST['date'] . ' ' . $_REQUEST['start_time']);
//                $fin_time = strtotime($_REQUEST['date'] . ' ' . $_REQUEST['fin_time']);
//            }
//
//            $indb = array(
//                'head' => rand(100, 100000),
//                'jobman' => $_REQUEST['jobman'],
//                'sale_point' => $_REQUEST['salepoint'],
//                'start' => date('Y-m-d H:i', $start_time),
//                'fin' => date('Y-m-d H:i', $fin_time),
//                'hour_on_job_calc' => \Nyos\mod\IikoChecks::calculateHoursInRange( $start_time , $fin_time ),
//                'who_add_item' => 'admin',
//                'who_add_item_id' => $_SESSION['now_user_di']['id'] ?? '',
//                'ocenka' => $_REQUEST['ocenka']
//            );
//
//
//            
//            \Nyos\mod\items::addNew($db, $vv['folder'], \Nyos\nyos::$menu['050.chekin_checkout'], $indb);
//
//            \f\end2('<div>'
//                    . '<nobr><b class="warn" >смена добавлена</b>'
//                    . '<br/>'
//                    . date('d.m.y H:i', $start_time) . ' - ' . date('d.m.y H:i', $fin_time)
//                    . '</nobr>'
//                    . '</div>', true);
//        }
//        elseif ($_POST['action'] == 'add_comment') {
//
//            $indb = $_REQUEST;
//
////array(
////                // 'head' => rand(100, 100000),
////                'jobman' => $_REQUEST['jobman'],
////                'sale_point' => $_REQUEST['salepoint'],
////                'start' => date('Y-m-d H:i', $start_time),
////                'fin' => date('Y-m-d H:i', $fin_time)
////            )
//
//            //\f\pa( $indb );
//            \Nyos\mod\items::addNew($db, $vv['folder'], \Nyos\nyos::$menu['073.comments'], $indb );
//
//            \f\end2('<div style="background-color: gray; padding:5px;" >'
//                    . '<b class="warn" >добавили комментарий</b>'
//                    . '<br/>'
//                    . $_REQUEST['comment']
//                    . '</div>', true);
//        }
//        //
//        elseif ($_POST['action'] == 'confirm_smena') {
//
////        if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php'))
////            require $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
//
//            $ff = $db->prepare('DELETE FROM `mitems-dops` WHERE `id_item` = :id AND `name` = \'pay_check\' ;');
//            $ff->execute(array(':id' => (int) $_POST['id2']));
//
//            $ff = $db->prepare('INSERT INTO `mitems-dops` ( `id_item`, `name`, `value` ) values ( :id, \'pay_check\', \'yes\' ) ');
//            $ff->execute(array(':id' => (int) $_POST['id2']));
//
//            \f\end2('<div>'
//                    . '<nobr>'
//                    . '<b class="warn" >отправлено на оплату</b>'
//                    . '</nobr>'
//                    . '</div>', true);
//        }
//        //
//        elseif ($_POST['action'] == 'edit_items_dop') {
//
////        if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php'))
////            require $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
//
//            $ff = $db->prepare('DELETE FROM `mitems-dops` WHERE `id_item` = :id AND `name` = \'pay_check\' ;');
//            $ff->execute(array(':id' => (int) $_POST['id2']));
//
//            $ff = $db->prepare('INSERT INTO `mitems-dops` ( `id_item`, `name`, `value` ) values ( :id, \'pay_check\', \'yes\' ) ');
//            $ff->execute(array(':id' => (int) $_POST['id2']));
//
//            \f\end2('<div>'
//                    . '<nobr>'
//                    . '<b class="warn" >отправлено на оплату</b>'
//                    . '</nobr>'
//                    . '</div>', true);
//        }
//    } 
//    //
//    catch (\Exception $ex) {
//
//        $e = '<pre>--- ' . __FILE__ . ' ' . __LINE__ . '-------'
//                . PHP_EOL . $ex->getMessage() . ' #' . $ex->getCode()
//                . PHP_EOL . $ex->getFile() . ' #' . $ex->getLine()
//                . PHP_EOL . $ex->getTraceAsString()
//                . '</pre>';
//
//        \f\end2($e, true);
//    } catch (\PDOException $ex) {
//
//        $e = '<pre>--- ' . __FILE__ . ' ' . __LINE__ . '-------'
//                . PHP_EOL . $ex->getMessage() . ' #' . $ex->getCode()
//                . PHP_EOL . $ex->getFile() . ' #' . $ex->getLine()
//                . PHP_EOL . $ex->getTraceAsString()
//                . '</pre>';
//
//        \f\end2($e, true);
//    }
//}
//
////
//elseif (isset($_POST['action']) && $_POST['action'] == 'add_new_minus') {
//    // action=add_new_smena
//
//    try {
//
////        require_once DR . '/all/ajax.start.php';
////
////        if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php'))
////            require $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
////
////        if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/vendor/didrive_mod/items/class.php'))
////            require ($_SERVER['DOCUMENT_ROOT'] . '/vendor/didrive_mod/items/class.php');
//
//        \Nyos\mod\items::addNew($db, $vv['folder'], \Nyos\nyos::$menu['072.vzuscaniya'], array(
//            // 'head' => rand(100, 100000),
//            'date_now' => date('Y-m-d', strtotime($_REQUEST['date'])),
//            'jobman' => $_REQUEST['jobman'],
//            'sale_point' => $_REQUEST['salepoint'],
//            'summa' => $_REQUEST['summa'],
//            'text' => $_REQUEST['text']
//        ));
//
//
////        if (date('Y-m-d', $start_time) == date('Y-m-d', $fin_time)) {
////            $dd = true;
////        } else {
////            $dd = false;
////        }
////        $r = ob_get_contents();
////        ob_end_clean();
//
//
//        \f\end2('<div>'
//                . '<nobr><b class="warn" >взыскание добавлено</b>'
//                . '<br/>'
//                . $_REQUEST['summa']
//                . '<br/>'
//                . '<small>' . $_REQUEST['text'] . '</small>'
////                . (
////                $dd === true ?
////                        '<br/>с ' . date('H:i', $start_time) . ' - ' . date('H:i', $fin_time) : '<br/>с ' . date('Y-m-d H:i:s', $start_time) . '<br/>по ' . date('Y-m-d H:i:s', $fin_time)
////                )
//                // .'окей '.$b
////                . '</br>'
////                . $b
////                . '</br>'
////                . $r
//                . '</nobr>'
//                . '</div>', true);
//    } 
//    catch (\Exception $ex) {
//
//        $e = '<pre>--- ' . __FILE__ . ' ' . __LINE__ . '-------'
//                . PHP_EOL . $ex->getMessage() . ' #' . $ex->getCode()
//                . PHP_EOL . $ex->getFile() . ' #' . $ex->getLine()
//                . PHP_EOL . $ex->getTraceAsString()
//                . '</pre>';
//
//        \f\end2($e, true);
//    } catch (\PDOException $ex) {
//
//        $e = '<pre>--- ' . __FILE__ . ' ' . __LINE__ . '-------'
//                . PHP_EOL . $ex->getMessage() . ' #' . $ex->getCode()
//                . PHP_EOL . $ex->getFile() . ' #' . $ex->getLine()
//                . PHP_EOL . $ex->getTraceAsString()
//                . '</pre>';
//
//        \f\end2($e, true);
//    }
//}
////
//elseif (isset($_POST['action']) && $_POST['action'] == 'add_new_plus') {
//    // action=add_new_smena
//
//    try {
//
//        //require_once DR . '/all/ajax.start.php';
//
////        if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php'))
////            require $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
////
////        if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/vendor/didrive_mod/items/class.php'))
////            require ($_SERVER['DOCUMENT_ROOT'] . '/vendor/didrive_mod/items/class.php');
//
//        \Nyos\mod\items::addNew($db, $vv['folder'], \Nyos\nyos::$menu['072.plus'], array(
//            // 'head' => rand(100, 100000),
//            'date_now' => date('Y-m-d', strtotime($_REQUEST['date'])),
//            'jobman' => $_REQUEST['jobman'],
//            'sale_point' => $_REQUEST['salepoint'],
//            'summa' => $_REQUEST['summa'],
//            'text' => $_REQUEST['text']
//        ));
//
//
////        if (date('Y-m-d', $start_time) == date('Y-m-d', $fin_time)) {
////            $dd = true;
////        } else {
////            $dd = false;
////        }
////        $r = ob_get_contents();
////        ob_end_clean();
//
//
//        \f\end2('<div>'
//                . '<nobr><b class="warn" >премия добавлена'
//                . '<br/>'
//                . $_REQUEST['summa']
//                . '<br/>'
//                . '<small>' . $_REQUEST['text'] . '</small>'
//                . '</b>'
////                . (
////                $dd === true ?
////                        '<br/>с ' . date('H:i', $start_time) . ' - ' . date('H:i', $fin_time) : '<br/>с ' . date('Y-m-d H:i:s', $start_time) . '<br/>по ' . date('Y-m-d H:i:s', $fin_time)
////                )
//                // .'окей '.$b
////                . '</br>'
////                . $b
////                . '</br>'
////                . $r
//                . '</nobr>'
//                . '</div>', true);
//    } 
//    catch (\Exception $ex) {
//
//        $e = '<pre>--- ' . __FILE__ . ' ' . __LINE__ . '-------'
//                . PHP_EOL . $ex->getMessage() . ' #' . $ex->getCode()
//                . PHP_EOL . $ex->getFile() . ' #' . $ex->getLine()
//                . PHP_EOL . $ex->getTraceAsString()
//                . '</pre>';
//
//        \f\end2($e, true);
//    } catch (\PDOException $ex) {
//
//        $e = '<pre>--- ' . __FILE__ . ' ' . __LINE__ . '-------'
//                . PHP_EOL . $ex->getMessage() . ' #' . $ex->getCode()
//                . PHP_EOL . $ex->getFile() . ' #' . $ex->getLine()
//                . PHP_EOL . $ex->getTraceAsString()
//                . '</pre>';
//
//        \f\end2($e, true);
//    }
//}
/////
//elseif (isset($_POST['action']) && $_POST['action'] == 'show_info_strings') {
//
////    require_once DR . '/all/ajax.start.php';
////
////    if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php'))
////        require $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
////
////    if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/all/exception.nyosex'))
////        require $_SERVER['DOCUMENT_ROOT'] . '/all/exception.nyosex';
//
//    // require_once DR.'/vendor/didrive_mod/items/class.php';
//    // \Nyos\mod\items::getItems( $db, $folder )
//    // echo DR ;
//    $loader = new Twig_Loader_Filesystem(dirname(__FILE__) . '/tpl.ajax/');
//
//// инициализируем Twig
//    $twig = new Twig_Environment($loader, array(
//        'cache' => $_SERVER['DOCUMENT_ROOT'] . '/templates_c',
//        'auto_reload' => true
//            //'cache' => false,
//            // 'debug' => true
//    ));
//
//    if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/all/twig.function.php'))
//        require ($_SERVER['DOCUMENT_ROOT'] . '/all/twig.function.php');
//
//    if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/vendor/didrive_mod/items/class.php'))
//        require ($_SERVER['DOCUMENT_ROOT'] . '/vendor/didrive_mod/items/class.php');
//
//    if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/vendor/didrive_mod/items/1/twig.function.php'))
//        require ($_SERVER['DOCUMENT_ROOT'] . '/vendor/didrive_mod/items/1/twig.function.php');
//
////    \Nyos\Mod\Items::getItems($db, $folder, $module, $stat, $limit);
//
//    $vv['get'] = $_GET;
//
//    $ttwig = $twig->loadTemplate('show_table.htm');
//    echo $ttwig->render($vv);
//
//    $r = ob_get_contents();
//    ob_end_clean();
//
//    // die($r);
//
//
//    \f\end2('окей', true, array('data' => $r));
//}

f\end2('Произошла неописуемая ситуация #' . __LINE__ . ' обратитесь к администратору', 'error');

exit;
