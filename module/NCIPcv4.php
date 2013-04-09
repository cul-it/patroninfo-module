<?php
//session_start();
include_once('log.php');
include_once('NCIPvx.php');
include_once('ill.inc');
// client for ncip protocol
// by no means does this support the complete protocol.
// just the items needed for specific ILS functions, by filling xml templates.
// by means of the version parameter, we could support different template names.
// The version of the ncip interface actually uses
// direct vxws calls to voyager to 
// renew, and to cancel.
class NCIPcv4 extends NCIPvx {
 const  authtmp = 'templatesv2/AuthenticateUser.xml';
 const  authresptmp = 'templatesv2/AuthenticateUserResponse.xml';
 const  luutmp = 'templatesv2/LookupUser.xml';
 const  luitmp = 'templatesv2/LookupItem.xml';
 const  lubtmp = 'templatesv2/LookupItemBibId.xml';
 const  luvtmp = 'templatesv2/LookupVersion.xml';
 const  rqitmp = 'templatesv2/RequestItem.xml';
 const  crqtmp = 'templatesv2/CancelRequestItem.xml';
 const  rcitmp = 'templatesv2/RecallItem.xml';
 const  crctmp = 'templatesv2/CancelRecallItem.xml';
 const  rentmp = 'templatesv2/RenewItem.xml';
 const  xluutmp = 'templatesv2/XCLookupUser.xml';
 const  xgatmp = 'templatesv2/XCGetAvailability.xml';

 const  stale_seconds = 1800;
 //const  hdr  = '<!DOCTYPE NCIPMessage PUBLIC "-//NISO//NCIP DTD Version 1//EN" "http://xml.coverpages.org/NCIP-v10a-DTD.txt">'; 
 const  hdr  = '';
 private $userid = ''; 
 private $username = ''; 
 private $password = ''; 
 private $system = ''; 
 private $authenticated = 0; 
 private $ch; 
 private $version = 1;
 private $trace = 0;
 private $logger;
 private $ncips;
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

// no need to authenticate for ncip v2 server -- just store values.
 public function authenticate($user,$password = NULL,$uid = NULL,$pid = NULL) {
  if ($this->get_trace()) $this->general("Current authenticated flag:" . $this->authenticated);
  if (!empty($pid) ){
    $this->pid = $pid; 
    $this->userid = $pid; 
  }
  if (empty($password) ){
  $this->userid = $user; 
  } else { 
   $this->username = $user; 
   $this->password = $password; 
   $this->userid = $uid; 
  }
 $doc = simplexml_load_string("<NCIPMessage/>");
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
      $this->general( "message:" . $doc);
  }
  return $doc;
 }

 public function lookupitem($id) {
    global $luidom;
    $xml      = clone ($this->luidom);
    $xml->LookupItem->ItemId[0]->IdentifierValue[0] = $id; 
    $request =  NCIPcv2::hdr.$xml->asXML();
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
    $request =  NCIPcv2::hdr.$xml->asXML();
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
    $request =  NCIPcv2::hdr.$xml->asXML();
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
    global $luudom;
    $xml      = clone ($this->luudom);
    print("LOOKUPUSER\n");
    $xml->LookupUser->AuthenticationInput[0]->AuthenticationInputData = $user; 
    $xml->LookupUser->AuthenticationInput[1]->AuthenticationInputData = $password; 
    $request =  NCIPcv2::hdr.$xml->asXML();
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

 public function requestitem($id,$loc,$date,$uid = NULL) {
    global $rqidom;
    $xml      = clone ($this->rqidom);
    $xml->RequestItem->UniqueUserId[0]->UserIdentifierValue[0] = $uid; 
    $xml->RequestItem->UniqueItemId[0]->ItemIdentifierValue[0] = $id; 
    $xml->RequestItem->ShippingInformation[0]->PhysicalAddress[0] = $loc; 
    $xml->RequestItem->PickupExpiryDate[0] = $date; 
    $request =  NCIPcv2::hdr.$xml->asXML();
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
    $request =  NCIPcv2::hdr.$xml->asXML();
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

 public function cancelrecallitem($id,$uid=NULL, $tid=NULL,$type='R') {
 return  $this->cancelrequestitem($id,$uid,$tid,$type);
 }
 public function cancelrequestitem($id,$uid=NULL, $tid=NULL,$type=NULL) {
    $uid = $this->username;
    $request = _ill() . "/illcancel.cgi?netid=$uid&iid=$id";
    if ($this->trace) $this->general($request);
    $buf2 = file_get_contents($request);
    if ($this->get_trace()) $this->general($buf2);
    $doc = json_decode($buf2);
    if (empty($doc)) {
       $doc = new SimpleXmlElement(
         "<Problem><ErrorCode>500</ErrorCode><ErrorMessage>Communication Error</ErrorMessage></Problem>", LIBXML_NOCDATA);
       return $doc;
    }
    if (empty($doc->error) ) {
       $doc = new SimpleXmlElement("<NCIPMessage/>");
    }
    if ($doc->error) {
       $error = $doc->error;
       $errortext = $doc->error;
       $doc = new SimpleXmlElement(
         "<Problem><ErrorCode>$error</ErrorCode><ErrorMessage>$errortext</ErrorMessage></Problem>", LIBXML_NOCDATA);
    }
    if ($this->get_trace()) $this->general(print_r($doc,TRUE));
    return $doc;
 } 

 public function recallitem($id,$loc,$date,$uid=NULL) {
    global $rcidom;
    $xml      = clone ($this->rcidom);
    $xml->RecallItem->UniqueItemId[0]->ItemIdentifierValue[0] = $id; 
    $xml->RecallItem->UniqueUserId[0]->UserIdentifierValue[0] = $uid; 
    $xml->RecallItem->DesiredDueDate[0] = $date; 
    $xml->RecallItem->ShippingInformation[0]->PhysicalAddress[0] = $loc; 
    $request =  NCIPcv2::hdr.$xml->asXML();
    if ($this->trace) $this->general($request);
    $this->ch_init($request);
    $buf2 = curl_exec ($this->ch); // execute the curl command
    print $buf2;
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

 private function xcancelrecallitem($id,$uid = NULL) {
    global $crcdom;
    $xml      = clone ($this->crcdom);
    $xml->CancelRecallItem->ItemId[0]->ItemIdentifierValue[0] = $id; 
    $xml->CancelRecallItem->AuthenticationInput[0]->AuthenticationInputData = $this->username; 
    $xml->CancelRecallItem->AuthenticationInput[1]->AuthenticationInputData = $this->password; 
    $request =  NCIPcv2::hdr.$xml->asXML();
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

// renewitem modified to directly use voyager services.
 public function renewitem($id,$uid=NULL,$date=NULL) {
    $uid = $this->username;
    $request = _ill() . "/illrenew.cgi?netid=$uid&iid=$id";
    if ($this->trace) $this->general($request);
    $buf2 = file_get_contents($request);
    if ($this->get_trace()) $this->general($buf2);
    $doc = json_decode($buf2);
    dump_var($doc);
    if (empty($doc)) {
       $doc = new SimpleXmlElement(
         "<Problem><ErrorCode>500</ErrorCode><ErrorMessage>Communication Error</ErrorMessage></Problem>", LIBXML_NOCDATA);
       return $doc;
    }
    if (empty($doc->error) ) {
       $doc = new SimpleXmlElement("<NCIPMessage/>");
    }
    if ($doc->error) {
       $error = $doc->error;
       $errortext = $doc->error;
       $doc = new SimpleXmlElement(
         "<Problem><ErrorCode>$error</ErrorCode><ErrorMessage>$errortext</ErrorMessage></Problem>", LIBXML_NOCDATA);
    }
    if ($this->get_trace()) $this->general(print_r($doc,TRUE));
    return $doc;
 }

 public function lookupversion() {
    global $luvdom;
    $xml      = clone ($this->luvdom);
    $request =  NCIPcv2::hdr.$xml->asXML();
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
    $filename = "/tmp/cookieFileName-".$this->system."-".$this->username;
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
 public function loadAll() { 
 $this->authdom=  simplexml_load_file(ncipcv2::authtmp);
 $this->authrespdom =  simplexml_load_file(ncipcv2::authresptmp);
 $this->crqdom =  simplexml_load_file(ncipcv2::crqtmp);
 $this->rendom =  simplexml_load_file(ncipcv2::rentmp);
 // these will fail.
 $this->luudom =  simplexml_load_file(ncipcv2::luutmp);
 $this->xluudom =  simplexml_load_file(ncipcv2::xluutmp);
 $this->luidom =  simplexml_load_file(ncipcv2::luitmp);
 $this->lubdom =  simplexml_load_file(ncipcv2::lubtmp);
 $this->luvdom =  simplexml_load_file(ncipcv2::luvtmp);
 $this->rqidom =  simplexml_load_file(ncipcv2::rqitmp);
 $this->rcidom =  simplexml_load_file(ncipcv2::rcitmp);
 $this->crcdom =  simplexml_load_file(ncipcv2::crctmp);
 $this->xgadom =  simplexml_load_file(ncipcv2::xgatmp);
 }
};
