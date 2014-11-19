<?php
class Logs extends CI_Model {

  function __construct() {
   parent::__construct();
  }

  /* 
  when some one log in
  [Timestamp] IP sahil@brightpod.com LOGIN
  */
  public function createLoginLog($login_log_array = NULL) {

    if(!isset($login_log_array) OR count($login_log_array) != 3)
    return false;

    $this->load->helper('date');
    $now = time();
    $timestamp = unix_to_human($now, TRUE);
    $ip_address = $login_log_array[0];
    $loggedin_user = $login_log_array[1];
    $method = $login_log_array[2];

    $admin_log_file = $_SERVER['DOCUMENT_ROOT'].'/logs/admin-log.log';

    $log =  "\n[$timestamp] {$ip_address} {$loggedin_user} {$method}\n";
 
    file_put_contents($admin_log_file, $log, FILE_APPEND) OR exit('Cannot append rule to admin-log.log');

  }

  /*
  * create log when db operation performed [Timestamp] sahil@brightpod.com REMOVED CLIENT <client-name>
  */
  public function createDbLog($db_log_array = NULL) {

    if(!isset($db_log_array) OR count($db_log_array) != 4)
    return false;

    $this->load->helper('date');
    $now = time();
    $timestamp = unix_to_human($now, TRUE);
    $ip_address = $db_log_array[0];
    $loggedin_user = $db_log_array[1];
    $operation = $db_log_array[2];
    $client_name = $db_log_array[3];

    $admin_log_file = $_SERVER['DOCUMENT_ROOT'].'/logs/admin-log.log';

    $log =  "\n[$timestamp] {$ip_address} {$loggedin_user} {$operation} <{$client_name}>\n";
 
    file_put_contents($admin_log_file, $log, FILE_APPEND) OR exit('Cannot append rule to admin-log.log');

  }

}