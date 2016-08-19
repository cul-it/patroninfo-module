<?php
// some urls are used to POST, or DELETE,
// and it is important to NOT let redirect happen from http to https
function _vxws() {
$vxwsbase="https://catalog.library.cornell.edu";
$vxwsport="";
$vxws="$vxwsbase$vxwsport";
return $vxws;
}
function _vxwsdbid() {
$vxwsdbid = '1@CORNELLDB20021226150546';
return $vxwsdbid;
}
?>
