<?php
// Copyright 2007 Facebook Corp.  All Rights Reserved. 
// Copyright (c) 2010 Ganos LLC,  all rights reserved.
require_once 'http://gudagi.com/platform/facebook.php';
if ( !file_exists("facebook.keys") ) { } else {
 $keys = file_get_contents("facebook.keys");
 $keys=explode("\n", $keys);
 $appapikey = $keys[0];
 $appsecret = $keys[1];
 $facebook = new Facebook($appapikey, $appsecret);
 $user_id = $facebook->require_login();
}
?>
