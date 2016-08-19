<?php
$renewsession = $_COOKIE[$_COOKIE['renewsession']];
$cdir = getcwd();
$boodir = '/var/www/apache2-default/webvision';
chdir($boodir);
set_include_path(get_include_path() . PATH_SEPARATOR . $boodir);
include_once("./includes/bootstrap.inc");
drupal_bootstrap(DRUPAL_BOOTSTRAP_SESSION);
chdir($cdir);
$sess = sess_read($renewsession); 
session_decode($sess);
$var =  $_SESSION['all_json'];
foreach ($var->items as $item) {
print_r($item);
}
include_once("NCIPc.php");
$sn = $_POST['sn'];
$bc = $_POST['bc'];
$inid = $_POST['inid'];
$netid = $_POST['netid'];


$auths = array('illiad' => array($netid,$netid,$netid), 'voyager' => array($bc,$sn,$inid));

$errors = '';

$renewablec = $_POST['item_cindex'];
$cancelc = $_POST['item_crindex'];
for($i=0;$i<$renewablec;$i++) {
    if ($_POST['item_'.$i.'_renew'])  {
        $val = explode(":",$_POST['item_'.$i.'_renew']);
        $sys = $val[0] ;
        $requested [$sys][] = $val[1]; 
    }
}
if ($renewablec > 0 && $requested )  $r = join(',',$requested);
for($i=0;$i<$cancelc;$i++) {
    if ($_POST['item_'.$i.'_cancel']) { 
       $val = explode(":",$_POST['item_'.$i.'_cancel']);
       $sys = $val[0] ;
       $canceled [$sys][] = $val[1]; 
    }
}

if (count($requested)) {
foreach ($requested as $sysa => $ids) {
  if (!isset($nca[$sysa])) {
      $nca[$sysa] = new NCIPc($sysa);
  }
  $nc = $nca[$sysa];
  $nc->set_trace(1);
  $resp = $nc->authenticate($auths[$sysa][0],$auths[$sysa][1]);
  $result = $resp->xpath('//Problem');
  if (isset($result[0])) {
     $errors .=  
     print_r( '0',TRUE). '|'.
     print_r( strval($result[0]->ErrorCode),TRUE). '|'.
     print_r( strval($result[0]->ErrorMessage),TRUE).'*';
   }   else {
  }

  if (count($ids)) {
  foreach( $ids as $it) { 
   $resp = $nc->renewitem($it,$auths[$sysa][2],'12/29/2039');
   $result = $resp->xpath('//Problem');
   if (isset($result[0])) {
      $errors .=  
      print_r( $it, TRUE). '|'.
      print_r( strval($result[0]->ErrorCode), TRUE). '|'.
      print_r( strval($result[0]->ErrorMessage),TRUE).'*';
    } else {
    // whatever 
   }
  }
  }
}
}

if (count($canceled)) {
foreach ($canceled as $sysa => $ids) {
  if (!isset($nca[$sysa])) {
      $nca[$sysa] = new NCIPc($sysa);
  }
  $nc = $nca[$sysa];
  $nc->set_trace(1);
  $resp = $nc->authenticate($auths[$sysa][0],$auths[$sysa][1]);
  $result = $resp->xpath('//Problem');
  if (isset($result[0])) {
     $errors .=  
     print_r( '0',TRUE). '|'.
     print_r( strval($result[0]->ErrorCode),TRUE). '|'.
     print_r( strval($result[0]->ErrorMessage),TRUE).'*';
   }   else {
  }
  if (count($ids)) {
  foreach( $ids as $it) { 
   //$resp = $nc->renewitem($it,$auths[$sysa][2],'12/29/2039');
   $resp = $nc->cancelrequestitem($it,$auths[$sysa][2]);
   $result = $resp->xpath('//Problem');
   if (isset($result[0])) {
      $errors .=  
      print_r( $it, TRUE). '|'.
      print_r( strval($result[0]->ErrorCode), TRUE). '|'.
      print_r( strval($result[0]->ErrorMessage),TRUE).'*';
    } else {
    // whatever 
   }
  }
  }
}
}


if ($errors) {
   setcookie('pimessage', $errors, 0, '/', '.cornell.edu');
   //setcookie("pimessage", $errors);
 //header("Location: ". $_SERVER['HTTP_REFERER']);
 //exit(0);
}

 header("Location: ". $_SERVER['HTTP_REFERER']);
 exit(0);

