<?php require_once('../../../GC/common/config.php'); require_once('../../../GC/common/db.php'); require_once('../../../GC/common/security.php'); $object=array(); $uses=array(); ?><html>
<head>
<title>test case</title>
</head>
<body>
<?php  ?><?php $_VAR['class']="someclass";  ?><?php
 $test=find("test_table","x",1);
 echo $test['z'];
 echo 'Object: ';
 var_dump( $object );
 echo '<br>$_VAR=';
 var_dump( $_VAR );
 echo '<br>$_GET=';
 var_dump( $_GET );
// $product=find("Product","HC",("###1###"));
// echo $product['HC'];
?>

</body>
</html>
