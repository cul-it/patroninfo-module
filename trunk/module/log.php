<?php
//For break use "\n" instead '\n'

Class log {
  //
  const USER_ERROR_DIR = '/tmp/Site_User_errors.log';
  const GENERAL_ERROR_DIR = '/tmp/Site_General_errors.log';

  /*
   User Errors...
  */
    public function user($ip,$msg,$username)
    {
    $date = date('d.m.Y h:i:s');
    $log = $ip . "\t" .$date."\t".$username."\t".$msg."\n";
    error_log($log, 3, self::USER_ERROR_DIR);
    }
    /*
   General Errors...
  */
    public function general($ip,$msg)
    {
    $date = date('d.m.Y h:i:s');
    $log = $ip . "\t" .$date."\t\t".$msg."\n";
    error_log($log, 3, self::GENERAL_ERROR_DIR);
    }

}
//$log = new log();
//$log->user($msg,$username); //use for user errors
//$log->general($msg); //use for general errors
