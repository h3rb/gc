<?php
 require_once( "db.php" );
 function depipe($s) {
    if ( strlen($s) < 1 || empty($s) ) return array();
    $a=array();
    $s=explode("|",$s);
    if ( !is_array($s) || count($s) < 1 ) return array();
    $k=null;
    foreach ( $s as $item ) if ( $k===null ) $k=$item; else { $a[$k]=$item; $k=null; }
    return $a;
 }
 function enpipe($a) {
    $pipe="";
    $c=count($a);
    $i=0;
    if ( $c>0 ) foreach ( $a as $k=>$v ) { $i++;
        $pipe .= $k . '|' . $v . ( $i!=$c ? '|' : '' );
    }
    return $pipe;
 }
 //Get the current time.
 //define(NOW,strtotime('now'),TRUE);
 global $NOW;$NOW=strtotime('now');
 global $basepath, $urlbase;
 global $project;
 global $HC;
 // Check for the path.  A script run must check for placement in the owner's project folder.
 $path=explode("/",$absolute_path);
 $HC=$path[4];
 if ( $HC === false ) { echo 'This file is not on a Gudagi server.'; die(); }
 if ( is_null($project=find( "Projects", "HC", $HC )) ) { echo 'The page does not have permission to do this.'; die(); }
 unset($project['twitter_p']);
 unset($next);
 unset($path);
 unset($HC);
 global $owner;$owner=find("Contact","HC",$project['r_Owner']);
 global $store;$store=find("Store","r_Project",$project['HC']);
 global $expired;$expired=false;
 global $user;$user=NULL;
 global $username;$username="";
 // Create the global $user
 if ( isset($_COOKIE) && isset($_COOKIE['username']) && isset($_COOKIE['session']) ) {
  $username = $_COOKIE['username'];
  $sid      = $_COOKIE['session'];
  $expired = false;
  if ( is_null($username)  || is_null($sid) ) $expired = true;
  else {
   $u =find_auth( $username );
   $user=find( "Contact", "HC", $u['r_Contact'] );
   unset($user['W9path']);
   unset($user['ID1path']);
   unset($user['ID2path']);
  }
  unset($sid);
 }
 // dump instance files
    function fpca($filename, $content) {
      $temp = tempnam(dirname(__FILE__)."/cache", 'temp');
      if (!($f = @fopen($temp, 'wb'))) {
        $temp = dirname(__FILE__)."/cache"
                . DIRECTORY_SEPARATOR
                . uniqid('temp');
        if (!($f = @fopen($temp, 'wb'))) {
            trigger_error("file_put_contents_atomic() : error writing temporary file '$temp'", E_USER_WARNING);
            return false;
        }
      }
      @fwrite($f, $content);
      @fclose($f);
      if (!@rename($temp, $filename)) {
           @unlink($filename);
           @rename($temp, $filename);
      }
      @chmod($filename, 0777);
      return true;
    }
 $dir=str_replace("/","_",$absolute_path);
 fpca( "/instances/".$dir.'.user', enpipe($user) );
 fpca( "/instances/".$dir.'.owner', enpipe($owner) );
 fpca( "/instances/".$dir.'.project', enpipe($project) );
 fpca( "/instances/".$dir.'.store', enpipe($store) );
 unset($dir);
 // function
 function load_gc() {
  global $absolute_path,$user,$owner,$project,$store;
  $dir=str_replace("/","_",$absolute_path);
  $user=depipe(file_get_contents("/instances/".$dir.'.user')); 
  $owner=depipe(file_get_contents("/instances/".$dir.'.owner'));
  $project=depipe(file_get_contents("/instances/".$dir.'.project')); 
  $store=depipe(file_get_contents("/instances/".$dir.'.store'));
 }
 function unload_gc() { unset($GLOBALS['user']); unset($GLOBALS['owner']); unset($GLOBALS['project']); unset($GLOBALS['$store']); }
 unload_gc();
 // Functions
 function redirect( $url, $delay = 0 ) {
  if ( $delay == 0 ) echo '<script type="text/javascript"> window.location = "' . $url . '"; </script>';
  else echo '<script type="text/javascript"> function delayer(){ window.location = ' . "'"
   . $url . "'; } setTimeout('delayer()', '" . $delay . "'" . '); </script>';
 }
 function browser() {
  return ( isset( $_SERVER['HTTP_USER_AGENT'] ) ) ? strtolower( $_SERVER['HTTP_USER_AGENT'] ) : '';
 }
 function isIE( ) {
  if (stristr(browser(), "msie")) return true;
  else return false;
 }
 global $referrer;$referrer=$_SERVER['HTTP_USER_AGENT'];
 $s=explode('/',$referrer);
 global $domain;$domain=$s[1];
 $s=explode('/',abpath);
 global $live= ( strpos($s[5],"live")!=false ? true : false );
?>
