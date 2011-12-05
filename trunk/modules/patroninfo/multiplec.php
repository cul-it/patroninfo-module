<?php
  $list = "";
  $dest='http://www.refworks.com/express/ExpressImport.asp?vendor=cornell.edu&filter=RefWorks%20Tagged%20Format&encoding=65001';
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
  $sn = $_POST['sn'];
  $bc = $_POST['bc'];
  $inid = $_POST['inid'];
  $netid = $_POST['netid'];
  $citeall = $_POST['citeall'];
  $renewablec = $_POST['item_cindex'];
  $cancelc = $_POST['item_crindex'];
   for($i=0;$i<$renewablec;$i++) {
    if ($_POST['item_'.$i.'_renew'])  {
        $val = explode(":",$_POST['item_'.$i.'_renew']);
        $sys = $val[0] ;
        $requested [$sys][] = $val[1]; 
    }
   }
   for($i=0;$i<$cancelc;$i++) {
    if ($_POST['item_'.$i.'_cancel']) { 
       $val = explode(":",$_POST['item_'.$i.'_cancel']);
       $sys = $val[0] ;
       $requested [$sys][] = $val[1]; 
    }
   }
  if (count($requested) || $citeall=='yes') {
   foreach ($var->items as $item) {
    if ((isset($requested[$item->system]) && in_array(isset($item->iid)? $item->iid:$item->ii, $requested[$item->system]))|| $citeall=='yes'){
      if (!isset($item->ou_title) ) $item->ou_title = $item->title;
      $text .= __patroninfo_citation($item); 
    }
   }
  }
 print_r(__patroninfo_refform($text,$dest)); 

 exit(0);

function __patroninfo_citation(&$item) {
  if ($item->system !='illiad') {
    $genre='Book, Whole';
  } else {
    $genre = 
    $item->ou_genre=='book'?
       "Book, Whole":"Article";
  }
// type has to be first so treat it special.
  $type = "\n\rRT  ".$genre . "\r\n";
  $citation['A1']=$item->ou_aulast;
  $citation['T1']=$item->ou_title;
  $citation['SN']=$item->ou_isbn;
  $citation['LA']='English';

  if (isset ($item->ou_pp)) {
  $citation['PB']=$item->ou_pb;
  $citation['PP']=$item->ou_pp;
  $citation['YR']=$item->ou_yr;
  }

  $data=$type;
  foreach ($citation as $tag => $value) {
  $data .= $tag .' ' .$value . "\r\n"; 
  }
return $data;
}

function  __patroninfo_refform($data,$dest) {
  global $list;
  return "<html>".
   "<body zzzonload='form1.submit();'>".
   "Saving titles" . $list . 
   "<form name='form1' action='$dest' method='POST'>".
   "<b>Submitting to RefWorks!</b>".
   "<input type=submit value='Export to Refworks'/>\n".
   "<input type=hidden name=ImportData value=\"$data\r\n\"/>\n".
   "</form>".
   "</body>".
   "</html>";
}
