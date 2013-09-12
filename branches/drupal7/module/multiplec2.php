<?php
$list = "";
$dest = 'http://www.refworks.com/express/ExpressImport.asp?vendor=cornell.edu&filter=RefWorks%20Tagged%20Format&encoding=65001';
$renewsession = $_COOKIE[$_COOKIE['renewsession']];
$cdir = getcwd();
$boodir = '/libweb/sites/www.library.cornell.edu/htdocs';
if (is_dir('/libweb/sites/wwwdev.library.cornell.edu/htdocs')) {
  $boodir = '/libweb/sites/wwwdev.library.cornell.edu/htdocs';
}
if (is_dir('/libweb/sites/www.test2.library.cornell.edu/htdocs')) {
  $boodir = '/libweb/sites/www.test2.library.cornell.edu/htdocs';
}
chdir($boodir);
set_include_path(get_include_path() . PATH_SEPARATOR . $boodir);
include_once DRUPAL_ROOT . "/includes/bootstrap.inc";
if (1) {
  include_once DRUPAL_ROOT . "/includes/session.inc";
  // parts of drupal_bootstrap
  drupal_unset_globals();
  // Start a page timer:
  timer_start('page');
  // Initialize the configuration
  conf_init();
  // Initialize the default database.
  require_once DRUPAL_ROOT . '/includes/database.inc';
  db_set_active();
  // Allow specifying alternate lock implementations in settings.php, like
  // those using APC or memcached.
  require_once DRUPAL_ROOT . '/' . variable_get('lock_inc', './includes/lock.inc');
  lock_init();


  session_set_save_handler('sess_open', 'sess_close', 'sess_read', 'sess_write', 'sess_destroy_sid', 'sess_gc');
  session_name($_COOKIE['renewsession']);
  session_start();
  //exit(0);
}
if (0) {
  session_name($_COOKIE['renewsession']);
  drupal_bootstrap(DRUPAL_BOOTSTRAP_SESSION);
}

chdir($cdir);
//$sess = sess_read($renewsession);
//session_decode($sess);
$var = $_SESSION['all_json'];
$sn = $_POST['sn'];
$bc = $_POST['bc'];
$howmany = 0;
$inid = $_POST['inid'];
$netid = $_POST['netid'];
$citeall = $_POST['citeall'];
$renewablec = $_POST['item_cindex'];
$cancelc = $_POST['item_crindex'];
for ($i = 0; $i < $renewablec; $i++) {
  if ($_POST['item_' . $i . '_renew']) {
    $val = explode(":", $_POST['item_' . $i . '_renew']);
    $sys = $val[0];
    $requested[$sys][] = $val[1];
    $howmany++;
  }
}
for ($i = 0; $i < $cancelc; $i++) {
  if ($_POST['item_' . $i . '_cancel']) {
    $val = explode(":", $_POST['item_' . $i . '_cancel']);
    $sys = $val[0];
    $requested[$sys][] = $val[1];
  }
}
if (count($requested) || $citeall == 'yes') {
  foreach ($var->items as $item) {
    if ((isset($requested[$item->system]) && in_array(isset($item->iid) ? $item->iid : $item->ii, $requested[$item->system])) || $citeall == 'yes') {
      if (!isset($item->ou_title)) {
        $item->ou_title = $item->title;
      }
      $text .= __patroninfo_citation($item);
    }
  }
}
$extra .= "<br><b>session_name:" . session_name() . "</b>\n";
$extra .= "<br><b>session_id:" . session_id() . "</b>\n";
$extra .= "<b>renewsession:$renewsession</b>\n";
$extra .= "<b>sess:$sess</b>\n";
$extra .= "_SESSION:$_SESSION</b>\n";
print_r(__patroninfo_refform2($text, $dest, $citeall == 'yes' ? $renewablec : $howmany));

exit(0);

function __patroninfo_citation(&$item) {
  if ($item->system != 'illiad') {
    $genre = 'Book, Whole';
  }
  else {
    $genre = $item->ou_genre == 'book' ? "Book, Whole" : "Article";
  }
  // type has to be first so treat it special.
  $type = "\n\rRT  " . $genre . "\r\n";
  $citation['A1'] = $item->ou_aulast;
  $citation['T1'] = $item->ou_title;
  $citation['SN'] = $item->ou_isbn;
  $citation['LA'] = 'English';

  if (isset($item->ou_pp)) {
    $citation['PB'] = $item->ou_pb;
    $citation['PP'] = $item->ou_pp;
    $citation['YR'] = $item->ou_yr;
  }

  $data = $type;
  foreach ($citation as $tag => $value) {
    $data .= $tag . ' ' . $value . "\r\n";
  }
  return $data;
}

function __patroninfo_refform2($data, $dest, $renewablec) {
  global $list;
  global $extra;
  //$d2 = "http://wwwdev.library.cornell.edu/sites/all/modules/patroninfo/multiplec.php";
  $d2 = $dest;
  $fm = "<html>" . "<body onload='form1.submit();'>" .
  //"Saving info" . $data .
  "<form name='form1' action='$d2' method='POST'>" . "<b>Submitting $renewablec records to RefWorks!</b>" . "<input type=submit value='Export to Refworks'/>\n" . "<input type=hidden name=ImportData value=\"$data\r\n\"/>\n";
  $fm .= "<input type=hidden name=item_cindex value='$renewablec' />" . "</form>" . "<br/>" . "</body>" . "</html>";
  return $fm;
}
