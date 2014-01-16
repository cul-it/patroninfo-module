<?php
define('DRUPAL_ROOT', $_SERVER['DOCUMENT_ROOT']);
$base_url = 'http://'.$_SERVER['HTTP_HOST'];
require_once DRUPAL_ROOT . '/includes/bootstrap.inc';
drupal_bootstrap(DRUPAL_BOOTSTRAP_SESSION);
$cdir = getcwd();
$data = '';
$dest = 'http://www.refworks.com/express/ExpressImport.asp?vendor=cornell.edu&filter=RefWorks%20Tagged%20Format&encoding=65001';
$nciptypes['voyager'] = 'NCIPcv3';
$ncipservers['voyager'] = 'http://es287-dev.library.cornell.edu:8080/voyager/NCIPResponder';

$nciptypes['illiad'] = 'NCIPcv4';
# this is a noop -- not used.
$ncipservers['illiad'] = 'http://catalog-test.library.cornell.edu:8080/illiadncip/ncipToolkit';

$var = $_SESSION['all_json'];
$requestc = 0;
$renewablec = $_POST['item_cindex'];
$cancelc = $_POST['item_crindex'];
$post = $_POST;
if (isset($_POST['cbottond'])) {
  $renewablec = 1;
  $_POST['item_0_renew'] = 'voyager:all';
}

chdir($cdir);

if (isset($_POST['cbottono'])) {
  header("Location: /index");
  unset($_COOKIE['verify_netid']);
  setcookie('verify_netid', "invalid", REQUEST_TIME - 3600 * 25, '/', '.cornell.edu');
  unset($_COOKIE['netid']);
  setcookie('netid', "invalid", REQUEST_TIME - 3600 * 25, '/', '.cornell.edu');
  unset($_COOKIE['cuwltgttime']);
  setcookie('cuwltgttime', "invalid", REQUEST_TIME - 3600 * 25, '/', '.cornell.edu');
  unset($_COOKIE['CUWALastWeblogin']);
  setcookie('CUWALastWeblogin', "invalid", REQUEST_TIME - 3600 * 25, '/', '.cornell.edu');
  unset($_COOKIE['cuweblogin2']);
  setcookie('cuweblogin2', "", REQUEST_TIME - 3600 * 25, '/', '.cornell.edu');
  unset($_COOKIE['__utma']);
  setcookie('__utma', "", REQUEST_TIME - 3600 * 25, '/', '.cornell.edu');
  unset($_COOKIE['__utmb']);
  setcookie('__utmb', "", REQUEST_TIME - 3600 * 25, '/', '.cornell.edu');
  unset($_COOKIE['__utmc']);
  setcookie('__utmc', "", REQUEST_TIME - 3600 * 25, '/', '.cornell.edu');
  exit(0);
}

for ($i = 0; $i < $renewablec; $i++) {
  if (isset($_POST['item_' . $i . '_renew'])) {
    $val = explode(":", $_POST['item_' . $i . '_renew']);
    $sys = $val[0];
    $requested[$sys][] = $val[1];
    $requestc++;
  }
}
if (isset($_POST['cbotton'])) {
  foreach ($var->items as $item) {
    if (isset($requested[$item->system]) && in_array($item->iid, $requested[$item->system])) {
      $data .= citethem($item);
    }
  }
  sendthem($dest, $data);
  exit(0);
}
include_once $cdir . '/' . "NCIPc.php";
include_once $cdir . '/' . "NCIPcv1.php";
include_once $cdir . '/' . "NCIPcv2.php";
include_once $cdir .  '/' . "NCIPcv3.php";
include_once $cdir . '/' . "NCIPcv4.php";
$pid = $_POST['pid'];
$sn = $_POST['sn'];
$bc = $_POST['bc'];
$inid = $_POST['inid'];
$netid = $_POST['netid'];

$_SESSION['renew_user'] = $_POST['netid'];
//$handle = fopen("/tmp/" . $renewsessionname . ".txt", "w+");
//fwrite($handle, $netid);
//fclose($handle);
$auths = array(
  'illiad' => array($netid, $netid, $netid, $netid),
  'voyager' => array($bc, $sn, $inid, $pid),
);
$nca = array();
$canceled = array();
$canceledt = array();
$canceledtt = array();
$requested = array();

$errors = '';

//if ($renewablec > 0 && $requestc>0 )  $r = implode(',',$requested);
for ($i = 0; $i < $cancelc; $i++) {
  if (isset($_POST['item_' . $i . '_cancel'])) {
    $val = explode(":", $_POST['item_' . $i . '_cancel']);
    $sys = $val[0];
    $canceled[$sys][] = $val[1];
    if ($sys != 'illiad') {
      $canceledt[$sys][] = $val[3];
      $canceledtt[$sys][] = $val[4];
    }
    else {
      $canceledt[$sys][] = $val[1];
      $canceledtt[$sys][] = 'R';
    }
  }
}

for ($i = 0; $i < $renewablec; $i++) {
  if (isset($_POST['item_' . $i . '_renew'])) {
    $val = explode(":", $_POST['item_' . $i . '_renew']);
    $sys = $val[0];
    $requested[$sys][] = $val[1];
  }
}


renews($requested, $auths);
cancels($canceled, $auths, $canceledt, $canceledtt);

if ($errors) {
  //setcookie('pimessage', $errors, 0, '/', $_SERVER['SERVER_NAME']);
}
else {
  //setcookie('pimessage', '', 0, '/', $_SERVER['SERVER_NAME']);
}
setcookie('pimessage', '', 0, '/', $_SERVER['SERVER_NAME']);
setcookie('renewuser', $netid, 0, '/', $_SERVER['SERVER_NAME']);
header("Location: " . $_SERVER['HTTP_REFERER']);
exit(0);

function citethem($item) {
  $type = isset($item->ou_genre) ? $item->ou_genre : 'Book, Whole';
  $author = isset($item->ou_aulast) ? $item->ou_aulast : (isset($item->au) ? $item->au : "");
  $title = isset($item->ou_title) ? $item->ou_title : (isset($item->title) ? $item->title : "");
  $isbn = isset($item->ou_isbn) ? $item->ou_isbn : (isset($item->isbn) ? $item->isbn : "");
  $lang = isset($item->ou_lang) ? $item->ou_lang : (isset($item->lang) ? $item->lang : "English");
  if (isset($item->ou_pp)) {
    $citation['PP'] = $item->ou_pp;
    $citation['YR'] = $item->ou_yr;
    $citation['PB'] = $item->ou_pb;
  }

  $type = "\r\nRT  " . $type . "\r\n";
  $citation['T1'] = $title;
  $citation['A1'] = $author;
  $citation['SN'] = $isbn;
  $citation['LA'] = $lang;
  $data = $type;
  foreach ($citation as $tag => $value) {
    $data .= $tag . ' ' . $value . "\r\n";
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


function renews($requested, $auths) {
  global $nca;
  global $nciptypes;
  global $ncipservers;
  global $errors;

  if (count($requested)) {
    foreach ($requested as $sysa => $ids) {
      if (!isset($nca[$sysa])) {
        $classname = $nciptypes[$sysa];
        $ncp = $ncipservers[$sysa];
        $nca[$sysa] = new $classname($sysa, $ncp);
      }
      $nc = $nca[$sysa];
      $nc->set_trace(1);
      $nc->set_system($sysa);
      //$resp = $nc->lookupuser($auths[$sysa][0],$auths[$sysa][1],$auths[$sysa][2],$auths[$sysa][3]);
      // print_r($resp);
    }
  }

  if (count($requested)) {
    foreach ($requested as $sysa => $ids) {
      if (!isset($nca[$sysa])) {
        $classname = $nciptypes[$sysa];
        $ncp = $ncipservers[$sysa];
        $nca[$sysa] = new $classname($sysa, $ncp);
      }
      $nc = $nca[$sysa];
      $nc->set_trace(1);
      $nc->set_system($sysa);
      $resp = $nc->authenticate($auths[$sysa][0], $auths[$sysa][1], $auths[$sysa][2], $auths[$sysa][3]);
      $result = $resp->xpath('//Problem');
      if (isset($result[0])) {
        $errors .= print_r('0', TRUE) . '|' . print_r(strval($result[0]->ErrorCode), TRUE) . '|' . print_r(strval($result[0]->ErrorMessage), TRUE) . '*';
      }
      else {
      }

      if (count($ids)) {
        foreach ($ids as $it) {
          $resp = $nc->renewitem($it, $auths[$sysa][2], '12/29/2039');
          $result = $resp->xpath('//Problem');
          if (isset($result[0])) {
            $errors .= print_r($it, TRUE) . '|' . print_r(strval($result[0]->ErrorCode), TRUE) . '|' . print_r(strval($result[0]->ErrorMessage), TRUE) . '*';
          }
          else {
            // whatever
          }
        }
      }
    }
  }
}

function cancels($canceled, $auths, $canceledt, $canceledtt) {
  global $nca;
  global $nciptypes;
  global $ncipservers;
  global $errors;

  if (count($canceled)) {
    foreach ($canceled as $sysa => $ids) {
      if (!isset($nca[$sysa])) {
        $classname = $nciptypes[$sysa];
        $ncp = $ncipservers[$sysa];
        $nca[$sysa] = new $classname($sysa, $ncp);
      }
      $nc = $nca[$sysa];
      $nc->set_system($sysa);
      $nc->set_trace(1);
      $resp = $nc->authenticate($auths[$sysa][0], $auths[$sysa][1], $auths[$sysa][2], $auths[$sysa][3]);
      $result = $resp->xpath('//Problem');
      if (isset($result[0])) {
        $errors .= print_r('0', TRUE) . '|' . print_r(strval($result[0]->ErrorCode), TRUE) . '|' . print_r(strval($result[0]->ErrorMessage), TRUE) . '*';
      }
      else {
      }
      if (count($ids)) {
        $cid = 0;
        foreach ($ids as $it) {
          if ($canceledtt[$sysa][$cid] == 'R') {
            $resp = $nc->cancelrecallitem($it, $auths[$sysa][2], $canceledt[$sysa][$cid], $canceledtt[$sysa][$cid]);
          }
          else {
            $resp = $nc->cancelrequestitem($it, $auths[$sysa][2], $canceledt[$sysa][$cid], $canceledtt[$sysa][$cid]);
          }
          $cid++;
          $result = $resp->xpath('//Problem');
          var_dump($result);
          if (isset($result[0])) {
            $errors .= print_r($it, TRUE) . '|' . print_r(strval($result[0]->ErrorCode), TRUE) . '|' . print_r(strval($result[0]->ErrorMessage), TRUE) . '*';
          }
          else {
            // whatever
          }
        }
      }
    }
  }
}
?>
