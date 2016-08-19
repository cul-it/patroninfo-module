<?php
//session_start();
include_once('log.php');
// client for ncip protocol
// by no means does this support the complete protocol.
// just the items needed for specific ILS functions, by filling xml templates.
// by means of the version parameter, we could support different template names.
class NCIPc {
 const  authtmp = 'templates/AuthenticateUser.xml';
 const  authresptmp = 'templates/AuthenticateUserResponse.xml';
 const  luutmp = 'templates/LookupUser.xml';
 const  luitmp = 'templates/LookupItem.xml';
 const  lubtmp = 'templates/LookupItemBibId.xml';
 const  luvtmp = 'templates/LookupVersion.xml';
 const  rqitmp = 'templates/RequestItem.xml';
 const  crqtmp = 'templates/CancelRequestItem.xml';
 const  rcitmp = 'templates/RecallItem.xml';
 const  crctmp = 'templates/CancelRecallItem.xml';
 const  rentmp = 'templates/RenewItem.xml';
 const  xluutmp = 'templates/XCLookupUser.xml';
 const  xgatmp = 'templates/XCGetAvailability.xml';
 //const  ncipserver = 'http://dss-es287linux.library.cornell.edu:8180/NCIPToolkit/ncipToolkit';
 const  ncipserver = 'http://dss-es287linux.library.cornell.edu:8180/voyagerncip/ncipToolkit';
 //const  illiad_ncipserver = 'http://dss-es287linux.library.cornell.edu:8180/NCIPToolkit/ncipToolkit';
 //const  voyager_ncipserver = 'http://dss-es287linux.library.cornell.edu:8180/voyagerncip/ncipToolkit';
// const  illiad_ncipserver = 'http://test-www.library.cornell.edu:8080/illiadncip/ncipToolkit';
// const  illiad_ncipserver = 'http://catalog-test.library.cornell.edu:8080/illiadncip/ncipToolkit';
//const  voyager_ncipserver = 'http://test-www.library.cornell.edu:8080/voyagerncip/ncipToolkit';
// const  voyager_ncipserver = 'http://catalog-test.library.cornell.edu:8080/voyagerncip/ncipToolkit';
const  voyager_ncipserver = 'http://catalog-test.library.cornell.edu:8080/voyagerncip/ncipToolkit';
const  illiad_ncipserver = 'http://catalog.library.cornell.edu:8080/illiadncip/ncipToolkit';

 const  stale_seconds = 1800;
 const  hdr  = '<!DOCTYPE NCIPMessage PUBLIC "-//NISO//NCIP DTD Version 1//EN" "http://xml.coverpages.org/NCIP-v10a-DTD.txt">'; 
 private $userid = ''; 
 private $username = ''; 
 private $password = ''; 
 private $authenticated = 0; 
 private $ch; 
 private $version = 1;
 private $trace = 0;
 private $logger;
 private $ncips;

 public function __construct($s) {
 $this->logger = new log(); 
   $this->ncips = NCIPC::ncipserver;
 if ($s == "illiad") { 
   $this->ncips = NCIPC::illiad_ncipserver;
 }
 if ($s == "voyager") { 
   $this->ncips = NCIPC::voyager_ncipserver;
 }

 }

 public function __destruct() {; }
 public function get_authenticated()   { 
    					   return $_SESSION['authenticated'];
			                   return $this->authenticated; 
                                       }
 public function get_userid()   { return $this->userid; }
 public function get_password() { return $this->password; }

 public function get_version() { return $this->version; }
 public function set_version($version) { 
   $old = $this->version;
   $this->version = $version;
   return $old; 
 }

 public function get_trace()   { return $this->trace; }
 public function set_trace($trace) { 
   $old = $this->trace;
   $this->trace = $trace;
   return $old; 
 }

 public function get_username() { return $this->username; }
 public function set_username($username) { 
   $old = $this->username;
   $this->username = $username;
   return $old; 
 }

 public function get_ncips()   { return $this->ncips; }
 public function set_ncips($v) { 
   $old = $this->ncips;
   $this->ncips = $v;
   return $old; 
 }

 public function authenticate($user,$password = NULL) {
    global $authdom;
    global $authrespdom;
  if ($this->get_trace()) $this->general("Current authenticated flag:" . $this->authenticated);
  if (empty($password) ){
  $this->userid = $user; 
  } else { 
   $this->username = $user; 
   $this->password = $password; 
  }
  $dumxml      = clone ($authrespdom);
  /* this does not really work, and I do not know why */
  $dumxml->AuthenticateUserResponse->UniqueUserId[0]->UserIdentifierValue[0] = $this->userid;
  $xml      = clone ($authdom);
  $xml->AuthenticateUser->AuthenticationInput[0]->AuthenticationInputData[0] = $user;
  $xml->AuthenticateUser->AuthenticationInput[1]->AuthenticationInputData[0] = $password;
  $request =  'NCIP='.NCIPc::hdr.$xml->asXML();
  if ($this->trace) $this->general($request);
  $this->ch_init($request);
  if ($this->authenticated) { 
      return $dumxml; 
  }
  $buf2 = curl_exec ($this->ch); // execute the curl command
  try {
  $doc = new SimpleXmlElement($buf2, LIBXML_NOCDATA);
    } 
    catch (Exception $e) {
       $doc = new SimpleXmlElement(
         "<Problem><ErrorCode>500</ErrorCode><ErrorMessage>Communication Error</ErrorMessage></Problem>", LIBXML_NOCDATA);
    }
  $result = $doc->xpath('//Problem');
  if (isset($result[0])) {
    $this->authenticated = 0; 
    $_SESSION['authenticated'] = 0;
  } else  { 
    $_SESSION['authenticated'] = 1;
    $this->authenticated = 1; 
  }
  if ($this->trace) {
      $this->general( "Authenticated:" . $this->authenticated);
      $this->general( "message:" . $buf2);
  } 
  return $doc;
 }

 public function lookupitem($id) {
    global $luidom;
    $xml      = clone ($luidom);
    $xml->LookupItem->UniqueItemId[0]->ItemIdentifierValue[0] = $id; 
    $request =  'NCIP='.NCIPc::hdr.$xml->asXML();
    if ($this->get_trace()) $this->general($request);
    $this->ch_init($request);
    $buf2 = curl_exec ($this->ch); // execute the curl command
    if ($this->get_trace()) $this->general($buf2);
    try {
    $doc = new SimpleXmlElement($buf2, LIBXML_NOCDATA);
    } 
    catch (Exception $e) {
       $doc = new SimpleXmlElement(
         "<Problem><ErrorCode>500</ErrorCode><ErrorMessage>Communication Error</ErrorMessage></Problem>", LIBXML_NOCDATA);
    }
    return $doc;
 }

 public function lookupitembibid($id) {
    global $lubdom;
    $xml      = clone ($lubdom);
    $xml->LookupItem->VisibleItemId[0]->VisibleItemIdentifier[0] = $id; 
    $request =  'NCIP='.NCIPc::hdr.$xml->asXML();
    if ($this->trace) $this->general($request);
    $this->ch_init($request);
    $buf2 = curl_exec ($this->ch); // execute the curl command
    if ($this->get_trace()) $this->general($buf2);
    try {
    $doc = new SimpleXmlElement($buf2, LIBXML_NOCDATA);
    } 
    catch (Exception $e) {
       $doc = new SimpleXmlElement(
         "<Problem><ErrorCode>500</ErrorCode><ErrorMessage>Communication Error</ErrorMessage></Problem>", LIBXML_NOCDATA);
    }
    return $doc;
 }

 public function xclookupuser($id) {
    global $xluudom;
    $xml      = clone ($xluudom);
    $xml->XCLookupUser->UniqueUserId[0]->UserIdentifierValue[0] = $id; 
    $request =  'NCIP='.NCIPc::hdr.$xml->asXML();
    if ($this->trace) $this->general($request);
    $this->ch_init($request);
    $buf2 = curl_exec ($this->ch); // execute the curl command
    if ($this->get_trace()) $this->general($buf2);
    try {
    $doc = new SimpleXmlElement($buf2, LIBXML_NOCDATA);
    } 
    catch (Exception $e) {
       $doc = new SimpleXmlElement(
         "<Problem><ErrorCode>500</ErrorCode><ErrorMessage>Communication Error</ErrorMessage></Problem>", LIBXML_NOCDATA);
    }
    return $doc;
 }

 public function lookupuser($id) {
    global $luudom;
    $xml      = clone ($luudom);
    $xml->LookupUser->UniqueUserId[0]->UserIdentifierValue[0] = $id; 
    $request =  'NCIP='.NCIPc::hdr.$xml->asXML();
    if ($this->trace) $this->general($request);
    $this->ch_init($request);
    $buf2 = curl_exec ($this->ch); // execute the curl command
    if ($this->get_trace()) $this->general($buf2);
    try {
    $doc = new SimpleXmlElement($buf2, LIBXML_NOCDATA);
    } 
    catch (Exception $e) {
       $doc = new SimpleXmlElement(
         "<Problem><ErrorCode>500</ErrorCode><ErrorMessage>Communication Error</ErrorMessage></Problem>", LIBXML_NOCDATA);
    }
    return $doc;
 }

 public function requestitem($id,$loc,$date,$uid = NULL) {
    global $rqidom;
    $xml      = clone ($rqidom);
    $xml->RequestItem->UniqueUserId[0]->UserIdentifierValue[0] = $uid; 
    $xml->RequestItem->UniqueItemId[0]->ItemIdentifierValue[0] = $id; 
    $xml->RequestItem->ShippingInformation[0]->PhysicalAddress[0] = $loc; 
    $xml->RequestItem->PickupExpiryDate[0] = $date; 
    $request =  'NCIP='.NCIPc::hdr.$xml->asXML();
    if ($this->trace) $this->general($request);
    $this->ch_init($request);
    $buf2 = curl_exec ($this->ch); // execute the curl command
    if ($this->get_trace()) $this->general($buf2);
    try {
    $doc = new SimpleXmlElement($buf2, LIBXML_NOCDATA);
    } 
    catch (Exception $e) {
       $doc = new SimpleXmlElement(
         "<Problem><ErrorCode>500</ErrorCode><ErrorMessage>Communication Error</ErrorMessage></Problem>", LIBXML_NOCDATA);
    }
    return $doc;
 }

 public function xcgetavailability($id) {
    global $xgadom;
    $xml      = clone ($xgadom);
    $xml->XCGetAvailability->UniqueItemId[0]->ItemIdentifierValue[0] = $id; 
    $request =  'NCIP='.NCIPc::hdr.$xml->asXML();
    if ($this->trace) $this->general($request);
    $this->ch_init($request);
    $buf2 = curl_exec ($this->ch); // execute the curl command
    if ($this->get_trace()) $this->general($buf2);
    try {
    $doc = new SimpleXmlElement($buf2, LIBXML_NOCDATA);
    } 
    catch (Exception $e) {
       $doc = new SimpleXmlElement(
         "<Problem><ErrorCode>500</ErrorCode><ErrorMessage>Communication Error</ErrorMessage></Problem>", LIBXML_NOCDATA);
    }
    return $doc;
 }

 public function cancelrequestitem($id,$uid = NULL) {
    global $crqdom;
    $xml      = clone ($crqdom);
    $xml->CancelRequestItem->UniqueUserId[0]->UserIdentifierValue[0] = $uid; 
    $xml->CancelRequestItem->UniqueItemId[0]->ItemIdentifierValue[0] = $id; 
    $request =  'NCIP='.NCIPc::hdr.$xml->asXML();
    if ($this->trace) $this->general($request);
    $this->ch_init($request);
    $buf2 = curl_exec ($this->ch); // execute the curl command
    if ($this->get_trace()) $this->general($buf2);
    try {
    $doc = new SimpleXmlElement($buf2, LIBXML_NOCDATA);
    } 
    catch (Exception $e) {
       $doc = new SimpleXmlElement(
         "<Problem><ErrorCode>500</ErrorCode><ErrorMessage>Communication Error</ErrorMessage></Problem>", LIBXML_NOCDATA);
    }
    return $doc;
 } 

 public function recallitem($id,$loc,$date,$uid=NULL) {
    global $rcidom;
    $xml      = clone ($rcidom);
    $xml->RecallItem->UniqueItemId[0]->ItemIdentifierValue[0] = $id; 
    $xml->RecallItem->UniqueUserId[0]->UserIdentifierValue[0] = $uid; 
    $xml->RecallItem->DesiredDueDate[0] = $date; 
    $xml->RecallItem->ShippingInformation[0]->PhysicalAddress[0] = $loc; 
    $request =  'NCIP='.NCIPc::hdr.$xml->asXML();
    if ($this->trace) $this->general($request);
    $this->ch_init($request);
    $buf2 = curl_exec ($this->ch); // execute the curl command
    if ($this->get_trace()) $this->general($buf2);
    try {
    $doc = new SimpleXmlElement($buf2, LIBXML_NOCDATA);
    } 
    catch (Exception $e) {
       $doc = new SimpleXmlElement(
         "<Problem><ErrorCode>500</ErrorCode><ErrorMessage>Communication Error</ErrorMessage></Problem>", LIBXML_NOCDATA);
    }
    return $doc;
 }

 public function cancelrecallitem($id,$uid = NULL) {
    global $crcdom;
    $xml      = clone ($crcdom);
    $xml->CancelRecallItem->UniqueUserId[0]->UserIdentifierValue[0] = $uid; 
    $xml->CancelRecallItem->UniqueItemId[0]->ItemIdentifierValue[0] = $id; 
    $request =  'NCIP='.NCIPc::hdr.$xml->asXML();
    if ($this->trace) $this->general($request);
    $this->ch_init($request);
    $buf2 = curl_exec ($this->ch); // execute the curl command
    if ($this->get_trace()) $this->general($buf2);
    try {
       $doc = new SimpleXmlElement($buf2, LIBXML_NOCDATA);
    } 
    catch (Exception $e) {
       $doc = new SimpleXmlElement(
         "<Problem><ErrorCode>500</ErrorCode><ErrorMessage>Communication Error</ErrorMessage></Problem>", LIBXML_NOCDATA);
    }
    return $doc;
 } 

 public function renewitem($id,$uid=NULL,$date=NULL) {
    global $rendom;
    $xml      = clone ($rendom);
    $xml->RenewItem->UniqueItemId[0]->ItemIdentifierValue[0] = $id; 
    $xml->RenewItem->UniqueUserId[0]->UserIdentifierValue[0] = $uid; 
    $xml->RenewItem->DesiredDateForReturn[0] = $date; 
    $request =  'NCIP='.NCIPc::hdr.$xml->asXML();
    if ($this->trace) $this->general($request);
    $this->ch_init($request);
    $buf2 = curl_exec ($this->ch); // execute the curl command
    if ($this->get_trace()) $this->general($buf2);
    try {
    $doc = new SimpleXmlElement($buf2, LIBXML_NOCDATA);
    } 
    catch (Exception $e) {
    $doc = new SimpleXmlElement(
         "<Problem><ErrorCode>500</ErrorCode><ErrorMessage>Communication Error</ErrorMessage></Problem>", LIBXML_NOCDATA);
    }
    return $doc;
 }

 public function lookupversion() {
    global $luvdom;
    $xml      = clone ($luvdom);
    $request =  'NCIP='.NCIPc::hdr.$xml->asXML();
    if ($this->trace) $this->general($request);
    $this->ch_init($request);
    $buf2 = curl_exec ($this->ch); // execute the curl command
    if ($this->get_trace()) $this->general($buf2);
    try {
    $doc = new SimpleXmlElement($buf2, LIBXML_NOCDATA);
    } 
    catch (Exception $e) {
       $doc = new SimpleXmlElement(
         "<Problem><ErrorCode>500</ErrorCode><ErrorMessage>Communication Error</ErrorMessage></Problem>", LIBXML_NOCDATA);
    }
    return $doc;
 }

 private function ch_init($request) {
    if (empty($this->ch) ) $this->ch = curl_init();
    $filename = "/tmp/cookieFileName-".$this->username;
    if (file_exists($filename)) { 
           $stat = stat($filename);
           $mtimef =$stat['mtime'];
           $nw = time();
           if ($this->trace) $this->general ("File age:" . $nw - $mtimef);
           if (($nw - $mtimef) > ncipc::stale_seconds) {
             if ($this->trace) $this->general( "Truncating file:" . $filename);
             $handle = fopen($filename, 'w+');
             ftruncate($handle, 0);
             fclose($handle); 
             $this ->authenticated=0;
             $_SESSION['authenticated'] = 0;
           } else {
             $_SESSION['authenticated'] = 1;
             $this ->authenticated=1;
           }
    } else {
             $_SESSION['authenticated'] = 0;
             $this ->authenticated=0;
    }
    if($this->trace) { 
       curl_setopt($this->ch, CURLOPT_VERBOSE, 1);
    }
    // it seems like the cookies won't be read from this file on the first request unless I provide both these options.
    // i am not sure I believe this, but cookies were not reused unless I provided both options.
    curl_setopt($this->ch, CURLOPT_COOKIEJAR, $filename);
    curl_setopt($this->ch, CURLOPT_COOKIEFILE, $filename);
    curl_setopt($this->ch, CURLOPT_URL,$this->ncips);
    curl_setopt($this->ch, CURLOPT_POST, 1);
    curl_setopt($this->ch, CURLOPT_POSTFIELDS, $request);
    curl_setopt($this->ch, CURLOPT_RETURNTRANSFER,1);
    if ($this->trace) $this->general($this->ch);
    if ($this->trace) $this->general ("cookie jar is ". $filename);
    if ($this->trace) $this->general("authenticated=". $this->authenticated);
 }
 private function general($msg) { 
   $this->logger->user($_SERVER['REMOTE_ADDR'] ,$this->username,$msg);
 }
};
 $authdom=  simplexml_load_file(ncipc::authtmp);
 $authrespdom =  simplexml_load_file(ncipc::authresptmp);
 $luudom =  simplexml_load_file(ncipc::luutmp);
 $xluudom =  simplexml_load_file(ncipc::xluutmp);
 $luidom =  simplexml_load_file(ncipc::luitmp);
 $lubdom =  simplexml_load_file(ncipc::lubtmp);
 $luvdom =  simplexml_load_file(ncipc::luvtmp);
 $rqidom =  simplexml_load_file(ncipc::rqitmp);
 $crqdom =  simplexml_load_file(ncipc::crqtmp);
 $rcidom =  simplexml_load_file(ncipc::rcitmp);
 $rendom =  simplexml_load_file(ncipc::rentmp);
 $crcdom =  simplexml_load_file(ncipc::crctmp);
 $xgadom =  simplexml_load_file(ncipc::xgatmp);
