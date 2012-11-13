<?php
function detect() {
/// GC Smart Index (c) 2010-2011 Gudagi
// MobileESP (c) 2010 Anthony Hand - Apache License 2.0
class uagent_info {
 var $useragent = "";
 var $httpaccept = "";
 var $true = 1;
 var $false = 0;
 var $isIphone = 0; 
 var $isTierIphone = 0; 
 var $isTierRichCss = 0;
 var $isTierGenericMobile = 0;
 var $engineWebKit = 'webkit';
 var $deviceAndroid = 'android';
 var $deviceIphone = 'iphone';
 var $deviceIpod = 'ipod';
 var $deviceIpad = 'ipad';
 var $deviceMacPpc = 'macintosh';
 var $deviceNuvifone = 'nuvifone';
 var $deviceSymbian = 'symbian';
 var $deviceS60 = 'series60';
 var $deviceS70 = 'series70';
 var $deviceS80 = 'series80';
 var $deviceS90 = 'series90';
 var $deviceWinPhone7 = 'windows phone os 7'; 
 var $deviceWinMob = 'windows ce';
 var $deviceWindows = 'windows'; 
 var $deviceIeMob = 'iemobile';
 var $devicePpc = 'ppc';
 var $enginePie = 'wm5 pie';
 var $deviceBB = 'blackberry';   
 var $vndRIM = 'vnd.rim';
 var $deviceBBStorm = 'blackberry95';  
 var $deviceBBBold = 'blackberry97'; 
 var $deviceBBTour = 'blackberry96'; 
 var $deviceBBCurve = 'blackberry89'; 
 var $deviceBBTorch = 'blackberry 98'; 
 var $devicePalm = 'palm';
 var $deviceWebOS = 'webos'; 
 var $engineBlazer = 'blazer'; 
 var $engineXiino = 'xiino';
 var $deviceKindle = 'kindle';
 var $vndwap = 'vnd.wap';
 var $wml = 'wml';   
 var $deviceBrew = 'brew';
 var $deviceDanger = 'danger';
 var $deviceHiptop = 'hiptop';
 var $devicePlaystation = 'playstation';
 var $deviceNintendoDs = 'nitro';
 var $deviceNintendo = 'nintendo';
 var $deviceWii = 'wii';
 var $deviceXbox = 'xbox';
 var $deviceArchos = 'archos';
 var $engineOpera = 'opera';
 var $engineNetfront = 'netfront';
 var $engineUpBrowser = 'up.browser';
 var $engineOpenWeb = 'openweb';
 var $deviceMidp = 'midp';
 var $uplink = 'up.link';
 var $engineTelecaQ = 'teleca q';
 var $devicePda = 'pda';
 var $mini = 'mini';
 var $mobile = 'mobile';
 var $mobi = 'mobi';
 var $maemo = 'maemo';
 var $maemoTablet = 'tablet';
 var $linux = 'linux';
 var $qtembedded = 'qt embedded';
 var $mylocom2 = 'com2';
 var $manuSonyEricsson = "sonyericsson";
 var $manuericsson = "ericsson";
 var $manuSamsung1 = "sec-sgh";
 var $manuSony = "sony";
 var $manuHtc = "htc";
 var $svcDocomo = "docomo";
 var $svcKddi = "kddi";
 var $svcVodafone = "vodafone";
 var $disUpdate = "update";
 function uagent_info() { $this->useragent = strtolower($_SERVER['HTTP_USER_AGENT']); $this->httpaccept = strtolower($_SERVER['HTTP_ACCEPT']); }
 function InitDeviceScan() { global $isIphone, $isTierIphone, $isTierRichCss, $isTierGenericMobile; $this->isIphone = $this->DetectIphoneOrIpod(); $this->isTierIphone = $this->DetectTierIphone(); $this->isTierRichCss = $this->DetectTierRichCss(); $this->isTierGenericMobile = $this->DetectTierOtherPhones(); }
 function Get_Uagent() { return $this->useragent; }
 function Get_HttpAccept() { return $this->httpaccept; }
 function DetectIphone() { if (stripos($this->useragent, $this->deviceIphone) > -1) { if ($this->DetectIpad() == $this->true || $this->DetectIpod() == $this->true) { return $this->false; } else return $this->true; } else return $this->false; }
 function DetectIpod() { if (stripos($this->useragent, $this->deviceIpod) > -1) return $this->true; else return $this->false; }
 function DetectIpad() { if (stripos($this->useragent, $this->deviceIpad) > -1 && $this->DetectWebkit() == $this->true) return $this->true; else return $this->false; }
 function DetectIphoneOrIpod() { if (stripos($this->useragent, $this->deviceIphone) > -1 || stripos($this->useragent, $this->deviceIpod) > -1) return $this->true; else return $this->false; }
 function DetectAndroid() { if (stripos($this->useragent, $this->deviceAndroid) > -1) return $this->true; else return $this->false; }
 function DetectAndroidWebKit() { if ($this->DetectAndroid() == $this->true) { if ($this->DetectWebkit() == $this->true) { return $this->true; } else return $this->false; } else return $this->false; }
 function DetectWebkit() { if (stripos($this->useragent, $this->engineWebKit) > -1) return $this->true; else return $this->false; }
 function DetectS60OssBrowser() { if ($this->DetectWebkit() == $this->true) { if (stripos($this->useragent, $this->deviceSymbian) > -1 || stripos($this->useragent, $this->deviceS60) > -1) { return $this->true; } else return $this->false; } else return $this->false; }
 function DetectSymbianOS() { if (stripos($this->useragent, $this->deviceSymbian) > -1 || stripos($this->useragent, $this->deviceS60) > -1 || stripos($this->useragent, $this->deviceS70) > -1 || stripos($this->useragent, $this->deviceS80) > -1 || stripos($this->useragent, $this->deviceS90) > -1) return $this->true; else return $this->false; }
 function DetectWindowsPhone7() { if (stripos($this->useragent, $this->deviceWinPhone7) > -1) return $this->true; else return $this->false; }
 function DetectWindowsMobile() { if ($this->DetectWindowsPhone7() == $this->true) return $this->false; if (stripos($this->useragent, $this->deviceWinMob) > -1 || stripos($this->useragent, $this->deviceIeMob) > -1 || stripos($this->useragent, $this->enginePie) > -1) return $this->true; if (stripos($this->useragent, $this->devicePpc) > -1  && !(stripos($this->useragent, $this->deviceMacPpc) > 1)) return $this->true; if (stripos($this->useragent, $this->manuHtc) > -1 && stripos($this->useragent, $this->deviceWindows) > -1) return $this->true; if ($this->DetectWapWml() == $this->true && stripos($this->useragent, $this->deviceWindows) > -1) return $this->true; else return $this->false; }
 function DetectBlackBerry() { if (stripos($this->useragent, $this->deviceBB) > -1) return $this->true; if (stripos($this->httpaccept, $this->vndRIM) > -1) return $this->true; else return $this->false; }
 function DetectBlackBerryWebKit() { if ((stripos($this->useragent, $this->deviceBB) > -1) && (stripos($this->useragent, $this->engineWebKit) > -1)) { return $this->true; } else return $this->false; }
 function DetectBlackBerryTouch() { if ((stripos($this->useragent, $this->deviceBBStorm) > -1) || (stripos($this->useragent, $this->deviceBBTorch) > -1)) return $this->true; else return $this->false; }
 function DetectBlackBerryHigh() { if ($this->DetectBlackBerryWebKit() == $this->true) return $this->false; if ($this->DetectBlackBerry() == $this->true) { if (($this->DetectBlackBerryTouch() == $this->true) || stripos($this->useragent, $this->deviceBBBold) > -1 || stripos($this->useragent, $this->deviceBBTour) > -1 || stripos($this->useragent, $this->deviceBBCurve) > -1) { return $this->true; } else return $this->false; } else return $this->false; }
 function DetectBlackBerryLow() { if ($this->DetectBlackBerry() == $this->true) { if ($this->DetectBlackBerryHigh() == $this->true) return $this->false; else return $this->true; } else return $this->false; }
 function DetectPalmOS() { if (stripos($this->useragent, $this->devicePalm) > -1 || stripos($this->useragent, $this->engineBlazer) > -1 || stripos($this->useragent, $this->engineXiino) > -1) { if ($this->DetectPalmWebOS() == $this->true) return $this->false; else return $this->true; } else return $this->false; }
 function DetectPalmWebOS() { if (stripos($this->useragent, $this->deviceWebOS) > -1) return $this->true; else return $this->false; }
 function DetectGarminNuvifone() { if (stripos($this->useragent, $this->deviceNuvifone) > -1) return $this->true; else return $this->false; }
 function DetectSmartphone() { if ($this->DetectIphoneOrIpod() == $this->true) return $this->true; if ($this->DetectS60OssBrowser() == $this->true) return $this->true; if ($this->DetectSymbianOS() == $this->true) return $this->true; if ($this->DetectAndroid() == $this->true) return $this->true; if ($this->DetectWindowsMobile() == $this->true) return $this->true; if ($this->DetectWindowsPhone7() == $this->true) return $this->true; if ($this->DetectBlackBerry() == $this->true) return $this->true; if ($this->DetectPalmWebOS() == $this->true) return $this->true; if ($this->DetectPalmOS() == $this->true) return $this->true; if ($this->DetectGarminNuvifone() == $this->true) return $this->true; else return $this->false; }
 function DetectBrewDevice() { if (stripos($this->useragent, $this->deviceBrew) > -1) return $this->true; else return $this->false; }
 function DetectDangerHiptop() { if (stripos($this->useragent, $this->deviceDanger) > -1 || stripos($this->useragent, $this->deviceHiptop) > -1) return $this->true; else return $this->false; }
 function DetectOperaMobile() { if (stripos($this->useragent, $this->engineOpera) > -1) { if ((stripos($this->useragent, $this->mini) > -1) || (stripos($this->useragent, $this->mobi) > -1)) return $this->true; else return $this->false; } else return $this->false; }
 function DetectWapWml() { if (stripos($this->httpaccept, $this->vndwap) > -1 || stripos($this->httpaccept, $this->wml) > -1) return $this->true; else return $this->false; }
 function DetectKindle() { if (stripos($this->useragent, $this->deviceKindle) > -1) return $this->true; else return $this->false; }
 function DetectMobileQuick() { if ($this->DetectiPad() == $this->true) return $this->false; if ($this->DetectSmartphone() == $this->true) return $this->true; if ($this->DetectWapWml() == $this->true) return $this->true; if ($this->DetectBrewDevice() == $this->true) return $this->true; if ($this->DetectOperaMobile() == $this->true) return $this->true; if (stripos($this->useragent, $this->engineNetfront) > -1) return $this->true; if (stripos($this->useragent, $this->engineUpBrowser) > -1) return $this->true; if (stripos($this->useragent, $this->engineOpenWeb) > -1) return $this->true; if ($this->DetectDangerHiptop() == $this->true) return $this->true; if ($this->DetectMidpCapable() == $this->true) return $this->true; if ($this->DetectMaemoTablet() == $this->true) return $this->true; if ($this->DetectArchos() == $this->true) return $this->true; if ((stripos($this->useragent, $this->devicePda) > -1) && (stripos($this->useragent, $this->disUpdate) < 0)) return $this->true; if (stripos($this->useragent, $this->mobile) > -1) return $this->true; else return $this->false; }
 function DetectSonyPlaystation() { if (stripos($this->useragent, $this->devicePlaystation) > -1) return $this->true; else return $this->false; }
 function DetectNintendo() { if (stripos($this->useragent, $this->deviceNintendo) > -1 || stripos($this->useragent, $this->deviceWii) > -1 || stripos($this->useragent, $this->deviceNintendoDs) > -1) return $this->true; else return $this->false; }
 function DetectXbox() { if (stripos($this->useragent, $this->deviceXbox) > -1) return $this->true; else return $this->false; }
 function DetectGameConsole() { if ($this->DetectSonyPlaystation() == $this->true) return $this->true; else if ($this->DetectNintendo() == $this->true) return $this->true; else if ($this->DetectXbox() == $this->true) return $this->true; else return $this->false; }
 function DetectMidpCapable() { if (stripos($this->useragent, $this->deviceMidp) > -1 || stripos($this->httpaccept, $this->deviceMidp) > -1) return $this->true; else return $this->false; }
 function DetectMaemoTablet() { if (stripos($this->useragent, $this->maemo) > -1) return $this->true; if (stripos($this->useragent, $this->maemoTablet) > -1 && stripos($this->useragent, $this->linux) > -1) return $this->true; else return $this->false; }
 function DetectArchos() { if (stripos($this->useragent, $this->deviceArchos) > -1) return $this->true; else return $this->false; }
 function DetectSonyMylo() { if (stripos($this->useragent, $this->manuSony) > -1) { if ((stripos($this->useragent, $this->qtembedded) > -1) || (stripos($this->useragent, $this->mylocom2) > -1)) { return $this->true; } else return $this->false; } else return $this->false; }
 function DetectMobileLong() { if ($this->DetectMobileQuick() == $this->true) return $this->true; if ($this->DetectGameConsole() == $this->true) return $this->true; if ($this->DetectSonyMylo() == $this->true) return $this->true; if (stripos($this->useragent, $this->uplink) > -1) return $this->true; if (stripos($this->useragent, $this->manuSonyEricsson) > -1) return $this->true; if (stripos($this->useragent, $this->manuericsson) > -1) return $this->true; if (stripos($this->useragent, $this->manuSamsung1) > -1) return $this->true; if (stripos($this->useragent, $this->svcDocomo) > -1) return $this->true; if (stripos($this->useragent, $this->svcKddi) > -1) return $this->true; if (stripos($this->useragent, $this->svcVodafone) > -1) return $this->true; else return $this->false; }
 function DetectTierIphone() { if ($this->DetectIphoneOrIpod() == $this->true) return $this->true; if ($this->DetectAndroid() == $this->true) return $this->true; if ($this->DetectAndroidWebKit() == $this->true) return $this->true; if ($this->DetectWindowsPhone7() == $this->true) return $this->true; if ($this->DetectBlackBerryWebKit() == $this->true) return $this->true; if ($this->DetectPalmWebOS() == $this->true) return $this->true; if ($this->DetectGarminNuvifone() == $this->true) return $this->true; if ($this->DetectMaemoTablet() == $this->true) return $this->true; else return $this->false; }
 function DetectTierRichCss() { if ($this->DetectMobileQuick() == $this->true) { if ($this->DetectTierIphone() == $this->true) return $this->false; if ($this->DetectWebkit() == $this->true) return $this->true; if ($this->DetectS60OssBrowser() == $this->true) return $this->true; if ($this->DetectBlackBerryHigh() == $this->true) return $this->true; if ($this->DetectWindowsMobile() == $this->true) return $this->true; if (stripos($this->useragent, $this->engineTelecaQ) > -1) return $this->true; else return $this->false; } else return $this->false; }
 function DetectTierOtherPhones() { if ($this->DetectMobileLong() == $this->true) { if ($this->DetectTierIphone() == $this->true) return $this->false; if ($this->DetectTierRichCss() == $this->true) return $this->false; else return $this->true; } else return $this->false; }
}
function browser_info($agent=null) {
  $known = array('msie', 'firefox', 'safari', 'webkit', 'opera', 'netscape', 'konqueror', 'gecko');
  $agent = strtolower($agent ? $agent : $_SERVER['HTTP_USER_AGENT']);
  $pattern = '#(?<browser>' . join('|', $known) .
    ')[/ ]+(?<version>[0-9]+(?:\.[0-9]+)?)#';
  if (!preg_match_all($pattern, $agent, $matches)) return array();
  $i = count($matches['browser'])-1;
  return array($matches['browser'][$i] => $matches['version'][$i]);
}
function supports( $word ) {
$devs=array(
0=>"ilife",
1=>"iphone",
2=>"ipad",
3=>"ipod",
4=>"blackberry",
5=>"blackberrykit",
6=>"blackberrytouch",
7=>"symbian",
8=>"palm",
9=>"palmweb",
10=>"winmobile",
11=>"winphone7",
12=>"operamobile",
13=>"garmin",
14=>"brew",
15=>"hiptop",
16=>"android",
17=>"androidkit",
18=>"ie",
19=>"msie",
20=>"explorer",
21=>"iexplore",
22=>"firefox",
23=>"ff",
24=>"gecko",
25=>"mozilla",
26=>"moz",
27=>"safari",
28=>"seamonkeys",
29=>"seamonkey",
30=>"maxthon",
31=>"flock",
32=>"chromium",
33=>"avant",
34=>"orca",
35=>"opera",
36=>"s60",
37=>"chrome",
38=>"deepnet",
39=>"xbox",
40=>"playstation",
41=>"nintendo",
42=>"kindle",
43=>"maemo",
44=>"mylo",
45=>"appletier",
46=>"richcss",
47=>"mobile",
48=>"desktop",
49=>"console",
50=>"wap",
51=>"tablet",
52=>"smartphone",
53=>"mobi",
54=>"wml",
55=>"konqueror",
56=>"omniweb"
);
 $i=0;
 foreach ( $devs as $de ) {
  if ( stripos($word,$de) ) return $i;
  $i++;
 }
 return false;
}
function deop($op){$ops=array( ">"=>1, "<"=>2, ">="=>3, "<="=>4, "=="=>5, "="=>5 );return $ops[$op];}
function v($n,$o,$v){$n=floatval($n);$v=floatval($v);switch($o){case false:break;case 1:if($n>$v)return true;case 2:if($n<$v)return true;case 3:if($n>=$v)return true;case 4:if($n<=$v)return true;case 5:if($n==$v)return true;}return false;} 
function go_($g,$o=false,$n=0,$v=99999){ if ( $o==false || v($n,$o,$v) ) {header("Location: ".$g); die();} }
function route( $d, $a, $v, $s, $g, $o, $n ) {
 switch ( $s ) {
 case 0: if ( $d->DetectIphoneorIpod() ) go_($g); break;
 case 1: if ( $d->DetectIphone() ) go_($g); break;
 case 2: if ( $d->DetectIpad() ) go_($g); break;
 case 3: if ( $d->DetectIpod() ) go_($g); break;
 case 4: if ( $d->DetectBlackBerry() ) go_($g); break;
 case 5: if ( $d->DetectBlackBerryWebKit() ) go_($g); break;
 case 6: if ( $d->DetectBlackBerryTouch() ) go_($g); break;
 case 7: if ( $d->DetectSymbianOS() ) go_($g); break;
 case 8: if ( $d->DetectPalmOS() ) go_($g); break;
 case 9: if ( $d->DetectPalmWebOS() ) go_($g); break;
 case 10: if ( $d->DetectWindowsMobile() ) go_($g); break;
 case 11: if ( $d->DetectWindowsPhone7() ) go_($g); break;
 case 12: if ( $d->DetectOperaMobile() ) go_($g); break;
 case 13: if ( $d->DetectGarminNuvifone() ) go_($g); break;
 case 14: if ( $d->DetectSmartPhone() ) go_($g); break;
 case 15: if ( $d->DetectBrewDevice() ) go_($g); break;
 case 16: if ( $d->DetectDangerHiptop() ) go_($g); break;
 case 17: if ( $d->DetectAndroid() ) go_($g); break;
 case 18: if ( $d->DetectAndroidWebKit() ) go_($g); break;
 case 20:
 case 21: if ( $a['browser'] == "msie" ) go_($g,$o,$v); break;
 case 22:
 case 23:
 case 24:
 case 25:
 case 26: if ( $a == "firefox" || $a['browser'] == "mozilla" || $a['browser'] == "gecko" ) go_($g,$o,$n,$v); break;
 case 27: if ( $a == "safari" ) go_($g,$o,$n,$v); break;
 case 28:
 case 29: if ( $a == "seamonkey" ) go_($g,$o,$n,$v); break;
 case 30: if ( $a == "maxthon" ) go_($g,$o,$n,$v); break;
 case 31:
 case 32: if ( $a == "chromium" || $a['browser'] == "flock" ) go_($g,$o,$n,$v); break;
 case 33: if ( $a == "avant" ) go_($g,$o,$n,$v); break;
 case 34: if ( $a == "orca" ) go_($g,$o,$n,$v); break;
 case 35: if ( $a == "opera" ) go_($g,$o,$n,$v); break;
 case 36: if ( $d->DetectS60OssBrowser() ) go_($g); break;
 case 37: if ( $a == "chrome" ) go_($g,$o,$n,$v); break;
 case 38: if ( $a == "deepnet" ) go_($g,$o,$n,$v); break;
 case 39: if ( $d->DetectXbox() ) go_($g); break;
 case 40: if ( $d->DetectSonyPlaystation() ) go_($g); break;
 case 41: if ( $d->DetectNintendo() ) go_($g); break;
 case 42: if ( $d->DetectKindle() ) go_($g); break;
 case 43: if ( $d->MaemoTablet() ) go_($g); break;
 case 44: if ( $d->DetectSonyMylo() ) go_($g); break;
 case 45: if ( $d->DetectTierIphone() ) go_($g); break;
 case 46: if ( $d->DetectRichCss() ) go_($g); break;
 case 47: if ( $d->DetectMobileLong() ) go_($g); break;
 case 48: if ( $a == "msie" || $a == "opera" || $a == "chrome" || $a == "firefox" && !$d->DetectMobileQuick() ) go_($g); break;
 case 49: if ( $d->DetectGameConsole() ) go_($g); break;
 case 50: if ( $d->DetectWapWml() ) go_($g); break;
 case 51: if ( $d->DetectIPad() || $d->DetectAndroid() || $d->DetectMaemoTablet() || $d->DetectSonyMylo() ) go_($g); break;
 case 52: if ( $d->DetectSmartphone() ) go_($g); break;
 case 53: if ( $d->DetectMobileLong() ) go_($g); break;
 case 54: if ( $d->DetectWapWml() ) go_($g); break;
 case 55: if ( $a == "konqueror" ) go_($g,$o,$n,$v); break;
 }
}
$ua = browser_info();
foreach ( $ua as $k=>$v ) { $device=$k; $version=$v; }
$d=new uagent_info();
$index=file_get_contents("index.gc");
$segments=explode("go",$index);
$wantsflash=false;
$wantsunity=false;
$linecount=0;
$goes=array();
foreach ( $segments as $seg ) {
 $lines=explode("\n",$seg);
 foreach ( $lines as $line ) {
  if ( $s=stripos($line,"\"") ) {
   $e=stripos(substr($line,$s,strlen($line)),"\"");
   if ( !$e ) $e=strlen($line);
   $path=str_replace("\"","",trim(substr($line,$s,$e)));
   $goes[]=$path;
  }
 }
}
$m=0;
$dev=array();
$ver=array();
$ops=array();
foreach ( $segments as $seg ) {
 $lines=explode("\n",$seg);
 $n=0;
 foreach ( $lines as $line ) {
  $linecount++;
  $s=false;
  if ( substr(trim($line),0,1) == "#" ) continue;
  if ( stripos($line,"\"") ) continue;
  $len=strlen($line);
  $parse=str_split(trim($line));
  $p=0; $q=0;
  $one=""; $two=""; $three="";
  while ( $parse[$p]==" " ) $p++;
  while ( $parse[$p]!="<" && $parse[$p]!="=" && $parse[$p]!=" " && $p<$len ) $one.=$parse[$p++];
  while ( $parse[$p]==" " ) $p++;
  while ( ($parse[$p]=="<" || $parse[$p]=="=") && $p<$len ) $two.=$parse[$p++];
  while ( $parse[$p]==" " ) $p++;
  while ( (is_numeric($parse[$p]) || $parse[$p]==".") && $p<$len ) $three.=$parse[$p++];
  if ( strlen($one)>0 ) {
   $dev[$m][$n]=supports($one);
   $ops[$m][$n]=deop($two);
   $ver[$m][$n++]=floatval($three);
  }
 }
 $m++;
}
foreach( $goes as $go )
foreach ( $dev as $de )
foreach ( $ops as $op )
foreach ( $ver as $ve ) route( $d, $device, $version, $de, $go, $op, $ve );
if ( $wantsflash ) {
}
if ( $wantsunity ) {
}
go_($goes[count($goes)-1]);
}detect();
?>
