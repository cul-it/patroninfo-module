<?php
$endpunct =' :/?!.,';
$type = isset($_GET['ou_genre'])? 'BOOK': 'BOOK';
$author=urldecode($_GET['ou_aulast']);
$title=urldecode($_GET['ou_title']);
$isbn=urldecode($_GET['ou_isbn']);
$lang = isset($_GET['ou_lang'])? $_GET['ou_lang']: 'English';
if (isset($_GET['ou_pp']) ) {
  $citation['CY']=trim($_GET['ou_pp'],$endpunct);
  $citation['PY']=$_GET['ou_yr'];
  $citation['PB']=trim($_GET['ou_pb'],$endpunct);
}

$type = "TY  - ".$type . "\r\n";
$citation['T1']=trim($title,$endpunct);
$citation['AU']=trim($author,$endpunct);
$citation['SN']=$isbn;
$citation['LA']=$lang;
$data=$type;
foreach ($citation as $tag => $value) {
  $data .= $tag .'  - ' .$value . "\r\n"; 
}
// mendeley does not like CN tag, so put it last.
if (isset($_GET['ou_callno']) ) {
  $value = $_GET['ou_callno'];
  $data .= 'C1'  .'  - ' .$value . "\r\n"; 
}
  $data .= "ER  - \r\n"; 
header("Content-Type: application/x-Research-Info-Systems");
header('Content-Disposition: attachment; filename="citation.ris"');
//header("Content-Type: plain/text\n\r");
echo $data;
exit (0);
?>
