<?php
//session_start();
include_once('log.php');
include_once('NCIPvx.php');
// client for ncip protocol
// by no means does this support the complete protocol.
// just the items needed for specific ILS functions, by filling xml templates.
// by means of the version parameter, we could support different template names.
class NCIPcv1 extends NCIPvx {
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
 //const  illiad_ncipserver = 'http://dss-es287linux.library.cornell.edu:8180/NCIPToolkit/ncipToolkit';
 //const  voyager_ncipserver = 'http://dss-es287linux.library.cornell.edu:8180/voyagerncip/ncipToolkit';
// const  illiad_ncipserver = 'http://test-www.library.cornell.edu:8080/illiadncip/ncipToolkit';
// const  illiad_ncipserver = 'http://catalog-test.library.cornell.edu:8080/illiadncip/ncipToolkit';
//const  voyager_ncipserver = 'http://test-www.library.cornell.edu:8080/voyagerncip/ncipToolkit';
// const  voyager_ncipserver = 'http://catalog-test.library.cornell.edu:8080/voyagerncip/ncipToolkit';
const  voyager_ncipserver = 'http://catalog.library.cornell.edu:8080/voyagerncip/ncipToolkit';
const  illiad_ncipserver = 'http://catalog.library.cornell.edu:8080/illiadncip/ncipToolkit';

 const  stale_seconds = 1800;
 const  hdr  = '<!DOCTYPE NCIPMessage PUBLIC "-//NISO//NCIP DTD Version 1//EN" "http://xml.coverpages.org/NCIP-v10a-DTD.txt">'; 
 private $userid = '';  // userid is 'instituition id'
 private $username = ''; 
 private $password = ''; 
 private $pid = '';     // pid is patron id -- voyager patron id number. 
 private $authenticated = 0;
 private $ch; 
 private $version = 1;
 private $trace = 0;
 private $logger;
 private $ncips;
 private $system;

 private $authdom;
 private  $authrespdom;
 private  $luudom ;
 private  $xluudom;
 private  $luidom ;
 private  $lubdom ;
 private  $luvdom ;
 private  $rqidom ;
 private  $crqdom ;
 private  $rcidom ;
 private $rendom ;
 private $crcdom ;
 private $xgadom ;

 public function __construct($s,$ncp) {
 $this->logger = new log(); 
 $this->ncips = $ncp;
 $this->system = $s;
 $this->loadAll(); 
}

 public function __destruct() {; }
 public function get_authenticated()   { 
    					   return $_SESSION['authenticated'];
			                   return $this->authenticated; 
                                       }
 public function get_userid()   { return $this->userid; }
 public function set_userid($userid) { 
   $old = $this->userid;
   $this->userid = $userid;
   return $old; 
}

 public function get_password() { return $this->password; }
 public function set_password($password) { 
   $old = $this->password;
   $this->password = $password;
   return $old; 
}

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

 public function get_system() { return $this->system; }
 public function set_system($system) { 
   $old = $this->system;
   $this->system = $system;
   return $old; 
 }

 public function authenticate($user,$password = NULL,$uid = NULL,$pid = NULL) {
    global $authdom;
    global $authrespdom;
  if ($this->get_trace()) $this->general("Current authenticated flag:" . $this->authenticated);
  if (empty($password) ){
  $this->userid = $user; 
  } else { 
   $this->username = $user; 
   $this->password = $password; 
  }
  $dumxml      = clone ($this->authrespdom);
  /* this does not really work, and I do not know why */
  $dumxml->AuthenticateUserResponse->UniqueUserId[0]->UserIdentifierValue[0] = $this->userid;
  $xml      = clone ($this->authdom);
  $xml->AuthenticateUser->AuthenticationInput[0]->AuthenticationInputData[0] = $user;
  $xml->AuthenticateUser->AuthenticationInput[1]->AuthenticationInputData[0] = $password;
  $request =  'NCIP='.NCIPcv1::hdr.$xml->asXML();
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
    $xml      = clone ($this->luidom);
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
    $xml      = clone ($this->lubdom);
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
    $xml      = clone ($this->xluudom);
    $xml->XCLookupUser->UniqueUserId[0]->UserIdentifierValue[0] = $id; 
    $request =  'NCIP='.NCIPcv1::hdr.$xml->asXML();
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

 public function lookupuser($user,$password = NULL,$uid = NULL,$pid = NULL) {
  return $this->xclookupuser($uid);
 }

 private function dummy_lookupuser($user,$password = NULL,$uid = NULL,$pid = NULL) {
    global $luudom;
    $xml      = clone ($this->luudom);
    $xml->LookupUser->UniqueUserId[0]->UserIdentifierValue[0] = $uid; 
    $request =  'NCIP='.NCIPcv1::hdr.$xml->asXML();
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
    $xml      = clone ($this->rqidom);
    $xml->RequestItem->UniqueUserId[0]->UserIdentifierValue[0] = $uid; 
    $xml->RequestItem->UniqueItemId[0]->ItemIdentifierValue[0] = $id; 
    $xml->RequestItem->ShippingInformation[0]->PhysicalAddress[0] = $loc; 
    $xml->RequestItem->PickupExpiryDate[0] = $date; 
    $request =  'NCIP='.NCIPcv1::hdr.$xml->asXML();
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
    $xml      = clone ($this->xgadom);
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

 public function cancelrequestitem($id,$uid = NULL,$tid=NULL,$type=NULL) {
    $xml      = clone ($this->crqdom);
    $xml->CancelRequestItem->UniqueUserId[0]->UserIdentifierValue[0] = $uid; 
    $xml->CancelRequestItem->UniqueItemId[0]->ItemIdentifierValue[0] = $id; 
    $request =  'NCIP='.NCIPcv1::hdr.$xml->asXML();
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
    $xml      = clone ($this->rcidom);
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
    $xml      = clone ($this->crcdom);
    //$xml->CancelRecallItem->UniqueUserId[0]->UserIdentifierValue[0] = $uid; 
    $xml->CancelRecallItem->UniqueItemId[0]->ItemIdentifierValue[0] = $id; 
    $request =  'NCIP='.NCIPcv1::hdr.$xml->asXML();
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
    $xml      = clone ($this->rendom);
    $xml->RenewItem->UniqueItemId[0]->ItemIdentifierValue[0] = $id; 
    $xml->RenewItem->UniqueUserId[0]->UserIdentifierValue[0] = $uid; 
    $xml->RenewItem->DesiredDateForReturn[0] = $date; 
    $request =  'NCIP='.NCIPcv1::hdr.$xml->asXML();
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
    $xml      = clone ($this->luvdom);
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
           if (($nw - $mtimef) > ncipcv1::stale_seconds) {
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
 public function loadAll() { 
 $this->authdom=  simplexml_load_file(NCIPcv1::authtmp);
 $this->authrespdom =  simplexml_load_file(NCIPcv1::authresptmp);
 $this->luudom =  simplexml_load_file(NCIPcv1::luutmp);
 $this->xluudom =  simplexml_load_file(NCIPcv1::xluutmp);
 $this->luidom =  simplexml_load_file(NCIPcv1::luitmp);
 $this->lubdom =  simplexml_load_file(NCIPcv1::lubtmp);
 $this->luvdom =  simplexml_load_file(NCIPcv1::luvtmp);
 $this->rqidom =  simplexml_load_file(NCIPcv1::rqitmp);
 $this->crqdom =  simplexml_load_file(NCIPcv1::crqtmp);
 $this->rcidom =  simplexml_load_file(NCIPcv1::rcitmp);
 $this->rendom =  simplexml_load_file(NCIPcv1::rentmp);
 $this->crcdom =  simplexml_load_file(NCIPcv1::crctmp);
 $this->xgadom =  simplexml_load_file(NCIPcv1::xgatmp);
}
};
