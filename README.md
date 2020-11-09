# didrive_api_yadom_time_expectation
работа со временем ожидания


-------- в мемкеш сохраняем ---------
массивы данных по выборкам (кеш на 4 часа)
getTimerExpectation-' . $sp_id . '-' . $date_start . '-' . $date_fin

--- запись того что сайт запущен и работает ---
$temp_var_name = 'run_site_time__'.$_SERVER['HTTP_HOST'] . '_' . $_REQUEST['w'] . '_' . $_REQUEST['h'];
