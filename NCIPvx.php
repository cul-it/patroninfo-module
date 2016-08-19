<?php
abstract class NCIPvx
{
// methods to send corresponding messages.
abstract  public function authenticate($user,$password = NULL,$uid = NULL,$pid = NULL) ;
abstract  public function lookupitem($id) ;
abstract  public function lookupitembibid($id) ;
abstract  public function xclookupuser($id) ;
abstract  public function lookupuser($user,$password = NULL,$uid = NULL,$pid = NULL) ;
abstract  public function requestitem($id,$loc,$date,$uid = NULL) ;
abstract  public function xcgetavailability($id) ;
abstract  public function cancelrequestitem($id,$uid = NULL,$tid=NULL,$type=NULL);
abstract  public function recallitem($id,$loc,$date,$uid=NULL) ;
abstract  public function cancelrecallitem($id,$uid = NULL) ;
abstract  public function renewitem($id,$uid=NULL,$date=NULL) ;
abstract  public function lookupversion() ;
// methods to manage common data. 
abstract  public function get_authenticated()   ; 
abstract  public function get_userid()  ; 
abstract  public function set_userid($userid); 
abstract  public function get_password(); 
abstract  public function set_password($password); 
abstract  public function get_version(); 
abstract  public function set_version($version) ; 
abstract  public function get_trace()  ; 
abstract  public function set_trace($trace) ; 
abstract  public function get_username(); 
abstract  public function set_username($username) ; 
abstract  public function get_ncips()   ; 
abstract  public function set_ncips($v) ; 
abstract  public function set_system($v) ; 
abstract  public function get_system() ; 
abstract  public function loadAll() ;
}

