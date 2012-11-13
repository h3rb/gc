<?php require_once($_SERVER['DOCUMENT_ROOT'].'/sites/GC/common/config.php'); require_once($_SERVER['DOCUMENT_ROOT'].'/sites/GC/common/db.php'); require_once($_SERVER['DOCUMENT_ROOT'].'/sites/GC/common/security.php'); $object=array(); $uses=array(); $_VAR=array(); ?><html>
<head>
</head>
<body>
Content formatted for the web.<br>
Audio Object: 
<?php  ?><?php $_VAR['height']="auto"; $_VAR['width']=300; $_VAR['source']="9JI00186XT|7sM81CF0BW"; $_VAR['name']="Intro|Mad Merv on Guitar";  ?><?php
 load_gc();
 $source=nq($_VAR['source']);

 $retreived=array();
 if ( strpos($source,"|")!=false ) {
  $sources=explode("|",$source);
  foreach ( $sources as $s ) {
   $r=find("File","HC",$s);
   if ( is_null($r) ) { echo '[[Audio]] Hash tag >' . $s . '< is invalid'; die; }
   $retreived[] = $r;
  }
 } else {
  $r=find("File","HC",$source);
  if ( is_null($r) ) { echo '[[Audio]] Hash tag >' . $source . '< is invalid'; die; }
  $retreived[]=$r;
 }

 $w=250;
 $h=170;
 $align="middle";

 if ( stripos(browser(),"firefox") ) $w=intval($w)+13;

 /* Generate a xspf temporary file based on the project's list */
 if ( count($retreived)<=0 ) return "";

 $total=0; 
 $counter=0; 
 foreach( $retreived as $res ) if ( !flag($res['flags'],FILE_DEPRECIATED) && $res['extension'] == "mp3" ) $total++;
 
 // Adjust length according to size of playlist.
 if ( stripos(nq($_VAR['height']),"auto") != false ) {
  $h = 30+$total*15;
 } else $h=intval(nq($_VAR['height']));

 if ( is_numeric( nq($_VAR['width']) ) ) $w=intval(nq($_VAR['width']));
 if ( $h<45/2 ) $h=45/2;
    
 // Produce configuration file for output
 $output = '<?xml version="1.0" encoding="UTF-8" ?><config>  
  <param name="height" value="' . $h .'" />
  <param name="width" value="' . $w . '" />
  <param name="bgcolor" value="cccc99" />
  <param name="bgcolor1" value="e79a2d" />
  <param name="bgcolor2" value="d38c29" />
  <param name="buttoncolor" value="dddddd" />
  <param name="buttonovercolor" value="f9bf37" />
  <param name="slidercolor1" value="0000dd" />
  <param name="slidercolor2" value="0000cc" />
  <param name="sliderovercolor" value="f9bf37" />
  <param name="textcolor" value="dddddd" />
  <param name="playlistcolor" value="999999" />
  <param name="currentmp3color" value="f9bf37" />
  <param name="scrollbarcolor" value="cccccc" />
  <param name="scrollbarovercolor" value="f9bf37" />
  <param name="showvolume" value="1" />
  <param name="showinfo" value="1" />
  <param name="mp3" value="';
  // Generate mp3 list
  $counter=0; 
  foreach( $retreived as $res ) if ( !flag($res['flags'],FILE_DEPRECIATED) && $res['extension'] == "mp3" ) {
   $counter++; 
   @chmod( $res['location'], 0777 );    
   $url = str_replace("/var/www","http://www.gudagi.com",$res['location']);	
   $info = $_SERVER['DOCUMENT_ROOT'];
   $output .= $url . ($counter < $total ? '|' : "");
  }
  $output .= '" /> <param name="title" value="';
 // Title the mp3s
 $counter=0;
 foreach( $retreived as $res ) if ( !flag($res['flags'],FILE_DEPRECIATED) && $res['extension'] == "mp3" ) {
  $name=array_pop($names);
  $output .= $name;
  $output .= ($counter < $total ? '|' : "");
 }

 $output = $output . '" /> </config>';
 if ( $counter == 0 ) return "";
 $hash = md5("1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz");
 $fhash = $_SERVER['DOCUMENT_ROOT'] . '/temp/' . $hash . '.xml';
 @file_put_contents($fhash,$output);
 echo '<object type="application/x-shockwave-flash" data="http://www.gudagi.com/mp3player/player_mp3_multi.swf" width="' . $w . '" height="' . $h .'">
 	 <param name="movie" value="mp3player/player_mp3_multi.swf" />
 	 <param name="wmode" value="transparent" />
	 <param name="FlashVars" value="configxml=http://www.gudagi.com/temp/' . $hash . '.xml"></object>';

 unload_gc();
?>

</body>
</html>
