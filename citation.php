<?php
$type = isset($_GET['ou_genre'])? $_GET['ou_genre']: 'Book, Whole';
$type=urldecode($_GET['ou_genre']);
$author=urldecode($_GET['ou_aulast']);
$title=urldecode($_GET['ou_title']);
$isbn=urldecode($_GET['ou_isbn']);
$lang = isset($_GET['ou_lang'])? $_GET['ou_lang']: 'English';
if (isset($_GET['ou_pp']) ) {
  $citation['PP']=$_GET['ou_pp'];
  $citation['YR']=$_GET['ou_yr'];
  $citation['PB']=$_GET['ou_pb'];
}

$dest='http://www.refworks.com/express/ExpressImport.asp?vendor=cornell.edu&filter=RefWorks%20Tagged%20Format&encoding=65001';
$type = "RT  ".$type . "\r\n";
$citation['T1']=$title;
$citation['A1']=$author;
$citation['SN']=$isbn;
$citation['LA']=$lang;
$data=$type;
foreach ($citation as $tag => $value) {
  $data .= $tag .' ' .$value . "\r\n"; 
}
echo "<html>";
echo "<body onload='form1.submit();'>";
echo "<form name='form1' action='$dest' method='POST'>";
echo "<b>Submitting to RefWorks!</b>";
echo "<input type=submit value='Export to Refworks'/>\n";
echo "<input type=hidden name=ImportData value=\"$data\r\n\"/>\n";
echo "</form>";
echo "</body>";
echo "</html>";
exit (0);
?>
