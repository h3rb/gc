<?php define(b, '/sqlite/gudadb/');
function find($t,$i,$v,$o=''){$t=ucfirst($t);$q="SELECT * FROM $t WHERE $i='$v' ".str_replace("`","",$o).';';try{$d=new PDO('sqlite:/sqlite/gudadb/'.$t.'.db');$r=$d->prepare($q);if(is_null($r)||$r===false){echo 'Bad query: '.$q;die;}$r->execute();$r=$r->fetchAll();}catch(PDOException $e){echo $e->getMessage();}if(count($r)>0)return array_pop($r);return NULL;}
function find_like($t,$i,$v,$s=''){$t=ucfirst($t);$q="SELECT * FROM $t WHERE $i='$v' ".str_replace("`","",$s).';';try{$d=new PDO('sqlite:/sqlite/gudadb/'.$t.'.db');$r=$d->prepare($query);if(is_null($r)||$q===false){echo 'Bad query: '.$q;die;}$r->execute();$r=$r->fetchAll();}catch(PDOException $e){echo $e->getMessage();}if(count($r)>0)return($r);return NULL;}
function find_auth($u){return find("Auth","username",$u);}
function guv($n) { if ( isset($_POST[$n]) ) return $_POST[$n]; if ( isset($_GET[$n]) ) return $_GET[$n]; return false; }
$GLOBALS["report_interval"] = 1800;
$GLOBALS["min_interval"] = 300;
$GLOBALS["maxpeers"] = 50;
$GLOBALS["dynamic_torrents"] = false;
$GLOBALS["NAT"] = false;
$GLOBALS["persist"] = false;
$GLOBALS["ip_override"] = false;
$GLOBALS["countbytes"] = true;
$GLOBALS["peercaching"] = true;
?>
