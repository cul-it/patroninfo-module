<?php
$type = isset($_GET['ou_genre'])? 'BOOK': 'BOOK';
$author=urldecode($_GET['ou_aulast']);
$title=urldecode($_GET['ou_title']);
$isbn=urldecode($_GET['ou_isbn']);
$lang = isset($_GET['ou_lang'])? $_GET['ou_lang']: 'English';
if (isset($_GET['ou_pp']) ) {
  $citation['CY']=$_GET['ou_pp'];
  $citation['Y1']=$_GET['ou_yr'];
  $citation['PB']=$_GET['ou_pb'];
}

$dest='http://www.refworks.com/express/ExpressImport.asp?vendor=cornell.edu&filter=RIS%20Format&encoding=65001';
$type = "TY  - ".$type . "\r\n";
$citation['T1']=$title;
$citation['A1']=$author;
$citation['SN']=$isbn;
$citation['LA']=$lang;
$data=$type;
foreach ($citation as $tag => $value) {
  $data .= $tag .'  - ' .$value . "\r\n"; 
}
  $data .= "ER  - \r\n"; 
header("Content-Type: application/x-Research-Info-Systems\n\r");
//header("Content-Type: plain/text\n\r");
echo "\n\r\n\r";
echo $data;
exit (0);
?>
