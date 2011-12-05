<?php
$data = '';
$dest='http://www.refworks.com/express/ExpressImport.asp?vendor=cornell.edu&filter=RefWorks%20Tagged%20Format&encoding=65001';
$renewsession = $_COOKIE[$_COOKIE['renewsession']];
$cdir = getcwd();
//$boodir = '/var/www/html';
//$boodir = '/var/www/apache2-default/webvision';
$boodir = '/webvision-dev/apache2/htdocs/webvision';
$boodir = '/library_www/beta/htdocs';
chdir($boodir);
set_include_path(get_include_path() . PATH_SEPARATOR . $boodir);
include_once("./includes/bootstrap.inc");
drupal_bootstrap(DRUPAL_BOOTSTRAP_SESSION);
chdir($cdir);
$sess = sess_read($renewsession); 
session_decode($sess);
$var =  $_SESSION['all_json'];
$requestc = 0;
$renewablec = $_POST['item_cindex'];
$cancelc = $_POST['item_crindex'];
for($i=0;$i<$renewablec;$i++) {
    if (isset($_POST['item_'.$i.'_renew']))  {
        $val = explode(":",$_POST['item_'.$i.'_renew']);
        $sys = $val[0] ;
        $requested [$sys][] = $val[1]; 
	$requestc++;
    }
}
if (isset($_POST['cbotton'])) {
  foreach ($var->items as $item) {
    if (isset($requested[$item->system]) && in_array($item->iid,$requested[$item->system]))
      $data .= citethem($item);
  }
  sendthem($dest,$data);
  exit(0);
}
include_once("NCIPc.php");
$sn = $_POST['sn'];
$bc = $_POST['bc'];
$inid = $_POST['inid'];
$netid = $_POST['netid'];


$auths = array('illiad' => array($netid,$netid,$netid), 'voyager' => array($bc,$sn,$inid));

$errors = '';
//if ($renewablec > 0 && $requestc>0 )  $r = implode(',',$requested);
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

function citethem($item) {
$type = isset($item->ou_genre)? $item->ou_genre: 'Book, Whole';
$author = isset($item->ou_aulast)? $item->ou_aulast: ( isset($item->au) ?$item->au : "" );
$title = isset($item->ou_title)? $item->ou_title: ( isset($item->title) ?$item->title : "" );
$isbn = isset($item->ou_isbn)? $item->ou_isbn: ( isset($item->isbn) ?$item->isbn : "" );
$lang = isset($item->ou_lang)? $item->ou_lang: ( isset($item->lang) ?$item->lang : "English" );
if (isset($item->ou_pp) ) {
  $citation['PP']=$item->ou_pp;
  $citation['YR']=$item->ou_yr;
  $citation['PB']=$item->ou_pb;
}

$type = "\r\nRT  ".$type . "\r\n";
$citation['T1']=$title;
$citation['A1']=$author;
$citation['SN']=$isbn;
$citation['LA']=$lang;
$data=$type;
foreach ($citation as $tag => $value) {
  $data .= $tag .' ' .$value . "\r\n";
}
return $data;
}

function sendthem($dest, $data) {
echo "<html>";
echo "<body onload='form1.submit();'>";
echo "<form name='form1' action='$dest' method='POST'>";
echo "<b>Submitting to RefWorks! This will only take moments...</b>";
echo "<input type=submit value='Export to Refworks'/>\n";
echo "<input type=hidden name=ImportData value=\"$data\r\n\"/>\n";
echo "</form>";
echo "</body>";
echo "</html>";
}
