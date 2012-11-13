<?php

include '/var/www/sites/GC/common/db.php';

$_CODE='
<tr><td>[name link="singletopic.html"] [posts]</td></tr>
<tr><td>[latest title]</td></tr>
<tr><td>[excerpt words=30 link="singlepost.html"]</td></tr>
<tr><td>
 <table>
  [>topic posts length=10: <tr><td>[post subject]</td><td>[post date="d-m-Y"]</td></tr><]
 </table>
</td></tr>';

function each_arg($c) {
 $c=str_split($c);
 $a=array();
 $a['o']=array();
 $a['t']=array();
 $acc="";
 foreach( $c as $ch ) {
  if ( $col===true ) { if ( $ch=='"' ) $col=false; $acc.=$ch; }
  if ( $ch==' ' ) {
   if ( strpos($acc,'=')===false ) $a["o"][]=$acc;
   else $a["t"][]=explode('=',str_replace('"','',$acc));
   $acc="";
  } else $acc.=$ch;
 }
 if ( strlen(trim($acc)) > 0 ) {
  if ( strpos($acc,'=')===false ) $a["o"][]=$acc;
  else $a["t"][]=explode('=',str_replace('"','',$acc));
  $acc="";
 }
 return $a;
}

function arify($c) {
 $a=array(); $c=str_split($c); $len=strlen($c); $acc=""; $i=0; $col=false; $o=0; $last=0; $mo=0;
 foreach( $c as $ch ) {
  if ( $col===true ) { if ( $ch=='"' ) $col=false; $acc.=$ch; }
  else switch ( $ch ) {
   case '"': if ( $col===false ) { $col=true; $acc.=$ch; }  break;
   case '>': if ( $last == '[' ) { $o++; if ( $mo<$o ) $mo=$o; } else $acc.=$ch;  break;
   case ':': { $a[]=array( "o,".$o=>each_arg($acc) ); $acc=''; } break;
   case '[': { $a[]=array( "h,".$o  =>$acc ); $acc=''; } break;
   case ']': if ( $last == '<' ) { $acc=substr($acc,0,strlen($arr)-1);
  $a[]=array( "h,".$o=>$acc ); $acc=""; $o--; } 
  else { $a[]=array("g,".$o=>each_arg($acc) ); $acc='';} break;
    default: $acc.=$ch; break;
  }
  $last=$ch;
 }
 if ( strlen($acc)>0 ) { $a[]=array("h,".$o => $acc ); }
 if ( $o > 0 ) echo 'GC Warning: Unmatched [> <]';
 $a["max"]=$mo;
 return $a;
}

function ttovar($a) {
 $b=array();
 foreach ( $a as $t ) {
  $b[$t[0]]=str_replace("'","\\'",$t[1]);
 }
 return $b;
}

global $langpath; $langpath='/gc/language/';
function fbm($f) {
 return trim(str_replace('/',' ',str_replace('iterate.gc','',str_replace($langpath,'',$f))));
}
function gcb_exec($o,$v,$t) {
 $f=$langpath.$o.'/'.implode('/',$v['o']).'/iterate.gc';
 if ( !file_exists($f) ) { return 'Warning: ['. fbm($f) . ']: not a valid GC iterator'; }
 $c=file_get_contents($f);
 $v=' $_VAR=depipe(str_replace("\\\\\'","\'",urldecode(\''.urlencode(enpipe(ttovar($v))).'\'))); ';
 ob_start(); $out=eval($v.$c); $out = ob_get_contents(); ob_end_clean();
 return $out;
}

function gcb_sub($v,$t,$r) {
 $f=$langpath.implode('/',$v['o']).'/iterate.gc';
 if ( !file_exists($f) ) { return 'Warning: ['. fbm($f) . ']: not a valid GC iterator'; }
 $c=file_get_contents($f);
 $v=' $_VAR=depipe(str_replace("\\\\\'","\'",urldecode(\''.urlencode(enpipe(ttovar($v))).'\'))); ';
 $r=' $_OBJ=depipe(urldecode(\''.urlencode(enpipe($r)).'\');';
 ob_start(); $out=eval($v.$r.$c); $out = ob_get_contents(); ob_end_clean();
 return $out;
}

function iter() {
 $out="";
 $b=array();
 $n=func_num_args();
 for ( $i=0; $i<$n; $i++ ) $b[]=func_get_arg($i);
 $obj=array_shift($b);
 $a=arify(array_shift($b));
 $nests=$a["max"];
 unset($a["max"]); 
 $top=array_shift($b);
// if ( is_null($top) ) return false;
 $sub=array();
 $sub['table']=array();
 $sub['ref']=array();
 for ( $j=0; $j<$nests; $j++ ) {
  $sub['table'][]=array_shift($b);
  $sub['ref'][]=array_shift($b);
 }
 $i=0;
 foreach ( $top as $t ) {
  foreach ( $a as $g ) {
   foreach ( $g as $k=>$v ) {
    $k=explode(',',$k);
    if ( intval($k[1])!=0 && $k[0]=='o' ) {
     $out.='#!#!#'.$k[1].'#!#!#';
    } else
    if ( intval($k[1])==0 ) {
     switch ( $k[0] ) {
      case 'h': $out.=$v; break;
      case 'g': $out.=gcb_exec($obj,$v,$t); break;
     }
    }
   }
  }
  foreach ( $sub['table'] as $tab ) {
   foreach ( $sub['ref'] as $ref ) {
    $i++;
    $out_o="";
    $res=find_like($tab,$ref,$t['HC']);
    if ( is_array($res) && count($res)>0 ) foreach ( $res as $r ) {
     foreach ( $a as $g ) {
      foreach ( $g as $k=>$v ) {
       $k=explode(',',$k);
       if ( intval($k[1]) == $i ) {
        switch ( $k[0] ) {
         case 'h': $out_o.=$v; break;
         case 'g': $out_o.=gcb_sub($v,$t,$r); break;
        }
       }
      }
     }
    }
    $out=str_replace('#!#!#'.$i.'#!#!#',$out_o,$out);
    $i--;
   }
  }
 }
 return $out;
}

var_dump($r=iter("Blog/topics",$_CODE,find_like("Topic","r_Blog","AU6MB7z3hNZ355F59QaJ"),"Post","r_Topic"));
//echo eval($r);
?>
