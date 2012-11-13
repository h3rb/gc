<?php
 $data=$_SERVER['HTTP_USER_AGENT']."\n".$_SERVER['HTTP_ACCEPT']; 
 $filename='device_info';
 $tmp_path = dirname($filename).'/'.microtime(true);
 if(!is_file($tmp_path)) touch($tmp_path);
 file_put_contents($tmp_path, $data);
 exec("mv -f $tmp_path $filename");
 exec("/gc/gc_si/gc_si ".$source." ".$devinfo." > ".$resultfile);
 $result=file_get_contents($resultfile);
 header( "Location: ".$result );
?>
