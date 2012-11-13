<?php require('../../../config.php'); require_once('../../../library.php'); require_once('../../../visual.php'); require_once('../../../ret.php'); require_once('../../../system/helpers/array_helper.php'); require_once('../../../system/helpers/html_helper.php'); require_once('../../../GC/common/db.php'); require_once('../../../GC/common/security.php'); $user = check_cookie( ); if ( $expired ) { $user = null; $owner=false; } if ( $expired || $user == null ) { redirect("relog_form.php"); die(); } $object=array(); ?><html>
<head>
<title>just html</html>
</head>
<body>
some html no more no less
</body>
</html>
