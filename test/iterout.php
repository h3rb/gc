<?php require_once($_SERVER['DOCUMENT_ROOT'].'/sites/GC/common/config.php'); require_once($_SERVER['DOCUMENT_ROOT'].'/sites/GC/common/db.php'); require_once($_SERVER['DOCUMENT_ROOT'].'/sites/GC/common/security.php'); $object=array(); $uses=array(); $_VAR=array(); ?>Topic page:

<table>
<?php $_VAR['length']=10;  $_CODE='
<tr><td>[name link="singletopic.html"] [posts]</td></tr>
<tr><td>[latest title]</td></tr>
<tr><td>[excerpt words=30 link="singlepost.html"]</td>/tr>'; ?><?php load_gc();
 echo '$_code='; var_dump($_CODE);
 echo '<br>$_VAR='; var_dump($_VAR);
unload_gc(); ?>

</table>

<table>
<?php $_VAR['length']=10; $_VAR['length']=10;  $_CODE='
<tr><td>[name link="singletopic.html"] [posts]</td></tr>
<tr><td>[latest title]</td></tr>
<tr><td>[excerpt words=30 link="singlepost.html"]</td>/tr>'; ?><?php load_gc();
 echo '$_code='; var_dump($_CODE);
 echo '<br>$_VAR='; var_dump($_VAR);
unload_gc(); ?>

</table>

<table>
<?php $_VAR['length']=10; $_VAR['length']=10; $_VAR['length']=10;  $_CODE='
<tr><td>[name link="singletopic.html"] [posts]</td></tr>
<tr><td>[latest title]</td></tr>
<tr><td>[excerpt words=30 link="singlepost.html"]</td></tr>
<tr><td>
 <table>
  [>topic posts length=10: <tr><td>[post subject]</td><td>[post date="d-m-Y"]</td></tr><]
 </table>
</td></tr>'; ?><?php load_gc();
 echo '$_code='; var_dump($_CODE);
 echo '<br>$_VAR='; var_dump($_VAR);
unload_gc(); ?>

</table>
