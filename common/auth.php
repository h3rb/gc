<?php
  define('home_dir',"/var/www/");
  define('home_dir_noslash',"/var/www");
  define('USE_SQLITE',1);
  
   require('config.php');
   $err_file = "auth.php";
 
if ( !defined('USE_SQLITE') ) {
   // Connect to the database
   $db = mysql_connect( host,  db_user, db_pass );    
   mysql_select_db( db_name );
} 

function escape_string( $s, $db=NULL ) {
 if ( defined('USE_SQLITE') ) return addslashes($s);
 return mysql_real_escape_string( $s );
}
   
//define( 'USE_SQLITE', 1 );

// Get the current time.
//define(NOW,strtotime('now'),TRUE);
$NOW=strtotime('now');


        // bitvector mathematics
    
    function flag( $bitvector, $flag ) {
      $bit = intval($bitvector);
      $flag = intval($flag);
	    if ( $bitvector & $flag ) return true;
	    return false;
    }
    
    function on( $bit, $flag ) {
      $bit = (int) $bit;
      $flag = (int) $flag;
	    return $bit & $flag;
    }
    
    function off( $bit, $flag ) {
      $bit = (int) $bit;
      $flag = (int) $flag;
	    return $bit & ~($flag);
    }
    
    function bittoggle( $bit, $flag ) {
      $bit = (int) $bit;
      $flag = (int) $flag;
	    return $bit ^ (1 << $flag);
    }
   
// Browser detection algorithms

   function browser( ) {
	   return ( isset( $_SERVER['HTTP_USER_AGENT'] ) ) ? strtolower( $_SERVER['HTTP_USER_AGENT'] ) : '';
   }   
   
   function isIE( ) {
      if (stristr(browser(), "msie")) return true;
      else return false;
    }
    // User and database authentication, generic queries

 
 
 // Converts a mysql resource to a numerically array       
function table_to_array($res) {
    if ( is_array($res) ) return $res;
	if ( is_null($res) ) return array();
    $tab=array();
    $i=0;
    if ( not_null($res) )
    while ( $r=mysql_fetch_assoc($res) ) $tab[$i++]=$r;
    return $tab;
}
    
        // Adds ` ticks to a list of fields seperated by , commas (fix by RainCT)
        // see also: adq, sq, msq
        function adt( $strlist ) {
                $stra = explode(',', str_replace('`', '', $strlist));
                if ( count($stra) == 1 ) return '`'.$stra[0].'`';
                foreach ( $stra AS $key => $value) {
                        $stra[$key] = ' `'.$value.'`';
                }
                return implode(',', $stra);
        }
    
        // Adds ' single quote to a list of fields seperated by , commas (fix by RainCT)
        // see also: adt, sq, msq
        function adq( $strlist ) {
                $stra = explode(',', str_replace("'", '', $strlist));
                if ( count($stra) == 1 ) return "'".$stra[0]."'";
                foreach ( $stra AS $key => $value) {
                        $stra[$key] = " '".$value."'";
                }
                return implode(',', $stra);
        }
	
	// Slash quotes: fixes \" and \' to be " and ' (the sourceforge bug)
        // see also: adt, adq, msq
	function sq( $str ) {
	    $str = str_replace("\\'", "'", $str);
	    $str = str_replace("\\\"", '"', $str);
	    return $str;
	}
	
	// Make slash quotes: fixes " and ' to be \" and \' (the sourceforge bug)
        // see also: adt, sq, adq
	function msq( $str ) {
	    $str = str_replace("'", "\\'", $str);
	    $str = str_replace("\"", '\\\"', $str);
	    return $str;
	}
	
if ( !defined('USE_SQLITE') ) {
	
    // Find authentication by username
    function find_auth( $un ) {
	    
	    $query = "SELECT * FROM auth WHERE `username` = '" . $un . "';";
	    $res = mysql_query($query) or err(mysql_error(),$query);
	    if ( mysql_num_rows($res) > 0 ) return mysql_fetch_assoc($res);
	    return NULL;
    }    

    // Find a user by hash code
    function find_user( $hc ) {
	    return find_id( "Contact", $hc );
    }
    
    // Find associated Auth data by a Contact hash code
    function find_uid( $hc ) {
        return find( "auth", "r_Contact", $hc );
    }
    
    
    /*
    function find_session( $un ) {
	    
	    $query = "SELECT * FROM Contact WHERE username = ('" . $un . "');";
	    $res = mysql_query($query) or die(mysql_error());
	    if ( mysql_num_rows($res) > 0 ) return mysql_fetch_assoc($res);
	    return NULL;
    }
    */
    
    function get_new_id($table){
      $select = 'select max(`HC`) +1as `HC` from `'.$table.'`  where `HC` != <some big id>';
      $query = mysql_query($select);
      $obj = mysql_fetch_object($query);
      return $obj->id;
    }
    
    function show_last_autoincrement($table) {
     $q = "SELECT LAST_INSERT_ID() FROM $table";
     return mysql_num_rows(mysql_query($q));
    }
    
    // Save an object into the database, create new by default.
    // Returns mysql_insert_id(); only relevant for auto increment keys; will not be accurate if you use it on
    // a table without an auto-incrementing primary key
    function insert( $table, $field, $value ) {
        $query = "INSERT INTO " . $table . "( " . adt($field) . ") VALUES (" . adq($value) . ");";
        $res = mysql_query($query) or err(mysql_error(),$query);
	return $res;
    }
    
    // Updates an object in the database by HC
    function set( $table, $id, $field, $value ) {
	$query = "UPDATE " . $table . " SET `" . $field . "`='" . $value . "' WHERE HC = '" . $id . "';";
	$res = mysql_query($query) or err(mysql_error(),$query);
        return $res;
    }
    
    // Stores the right now time
    function now( $table, $id, $field ) {
	$query = "UPDATE " . $table . " SET `" . $field . "`=NOW() WHERE HC = '" . $id . "';";
	$res = mysql_query($query) or err(mysql_error(),$query);
        return $res;        
    }
    
    // Bitvector activation
    function activate( $table, $id, $field, $value ) {
        $query = "SELECT * FROM " . $table . " WHERE (HC = '" . $id ."');";
	$res = mysql_query($query) or err(mysql_error(),$query);   
        $row = mysql_fetch_assoc( $res );
	if ( flag($row[$field], $value) ) return;
        $flag = $row[$field] | $value;
        set($table,$id,$field,$flag);
    }
    
    function delete( $table, $id, $value, $sort_or_limit="" ) {
 	$query = "DELETE FROM " . $table . " WHERE `" . $id . "` = '" . $value . "' " . $sort_or_limit . ";";
	$res = mysql_query($query) or err(mysql_error(),$query);
    }
    
    // Bitvector deactivation
    function deactivate( $table, $id, $field, $value ) {
        $query = "SELECT * FROM " . $table . " WHERE HC = '" . $id ."';";
	$res = mysql_query($query) or err(mysql_error(),$query); 
        $row = mysql_fetch_assoc( $res );
	if ( !flag($row[$field], $value) ) return;
        $flag = $row[$field] & ~($value);
        set($table,$id,$field,$flag);        
    }
    
    // Bitvector toggle
    function toggle( $table, $id, $field, $value ) {
        $query = "SELECT * FROM `" . $table . "` WHERE HC = '" . $id ."';";
	$res = mysql_query($query) or err(mysql_error(),$query); 
        $row = mysql_fetch_assoc( $res );
	if ( flag($row[$field], $value) ) deactivate($table,$id,$field,$value);
        else activate($table,$id,$field,$value);
    }
    
    // Bitvector value request
    function flag_value( $table, $id, $field ) {
        $query = "SELECT * FROM `" . $table . "` WHERE HC = '" . $id ."';";
	$res = mysql_query($query) or err(mysql_error(),$query); 
        $row = mysql_fetch_assoc( $res );
        $flag = $row[$field];
        return ( $flag );       
    }
    
    // Bitvector value assertion
    function has( $table, $id, $field, $value ) {
        $query = "SELECT * FROM `" . $table . "` WHERE HC = '" . $id ."';";
	$res = mysql_query($query) or err(mysql_error(),$query); 
        $row = mysql_fetch_assoc( $res );
        $flag = $row[$field];
        return ( $flag & $value );
    }
    
    // Updates multiple columns
    function multiupdate( $table, $hc, $field_value ) {
        $query = "UPDATE `" . $table . "` SET " . $field_value . " WHERE HC = '" . $hc . "';";
    }
    
    // Adds a field to a table
    function add_field( $table, $field, $type ) {
        $query = "ALTER TABLE `" . $table . "` ADD `" . $field . "` " . $type . ";";
        $res = mysql_query($query) or err(mysql_error(),$query);
        return $res;
    }
    
    // Finds the first item in the table with an id and a value
    function find( $table, $id, $value, $other="" ) {
 	    $query = "SELECT * FROM " . $table . " WHERE `" . $id . "` = '" . $value . "' " . $other . ";";
	    $res = mysql_query($query) or err(mysql_error(),$query);
	    if ( mysql_num_rows($res) > 0 ) return mysql_fetch_assoc($res);
	    return NULL;       
    }

    
    // Finds items in the table with an id and a value
    function find_like( $table, $id, $value, $sort_or_limit="" ) {
 	    $query = "SELECT * FROM " . $table . " WHERE `" . $id . "` = '" . $value . "' " . $sort_or_limit . ";";
	    $res = mysql_query($query) or err(mysql_error(),$query);
	    if ( mysql_num_rows($res) > 0 ) return ($res);
	    return NULL;       
    }


    // Finds items in the table not matching an id and a value
    function find_not_like( $table, $id, $value, $sort_or_limit="" ) {
 	    $query = "SELECT * FROM " . $table . " WHERE `" . $id . "` <> '" . $value . "' " . $sort_or_limit . ";";
	    $res = mysql_query($query) or err(mysql_error(),$query);
	    if ( mysql_num_rows($res) > 0 ) return ($res);
	    return NULL;       
    }
    
    
    // Finds all items in the table (future plateau danger zone)
    function find_all( $table, $sort_or_limit="" ) {
 	    $query = "SELECT * FROM " . $table . " " . $sort_or_limit . ";";
	    $res = mysql_query($query) or err(mysql_error(),$query);
	    if ( mysql_num_rows($res) > 0 ) return ($res);
	    return NULL;
    }


    function find_id( $table, $id ) {
	    $query = "SELECT * FROM " . $table . " WHERE HC = '" . $id . "';";
	    $res = mysql_query($query) or err(mysql_error(),$query);
	    if ( mysql_num_rows($res) > 0 ) return mysql_fetch_assoc($res);
	    return NULL;
    }
   
    // finds all related records with a specific table, field and value
    function get_related( $table, $id, $val ) {
        $query = "SELECT * FROM " . $table . " WHERE " . $id . " = '" . $val ."';";
	    $res = mysql_query($query) or err(mysql_error(),$query);
        if ( mysql_num_rows($res) > 0 ) return $res;
        return NULL;        
    }
    
    // return a certain number of sorted records from a particular table
    function find_sorted( $table, $order_by, $limit, $asc_desc="DESC" ) {
      $query = "SELECT * FROM " . $table . " ORDER BY " . $order_by . " " . $asc_desc . " LIMIT " . $limit . ';';
          $res = mysql_query($query) or err(mysql_error(),$query);
      return $res;
    }
	
	
    
    function get_contact( $un ) {
	    $query = "SELECT * FROM Contacts WHERE HC = '" . $un . "';";
	    $res = mysql_query($query) or err(mysql_error(),$query);
	    if ( mysql_num_rows($res) > 0 ) return mysql_fetch_assoc($res);
	    return NULL;
    }
	
    
    function get_session( $sid ) {
	    $sid      = $_COOKIE['session'];
	    $username = $_COOKIE['username'];
	    
	    $expired = false;
	    if ( is_null($sid) || is_null($username) ) {
		    $expired = true;
		    return NULL;
	    }	    
	    
	    $user = find_user( $username);
	    
	    if ( $user == NULL ) return NULL;
	    
	    $query = "SELECT * FROM Session WHERE HC = '" . $sid . "'; ";
	    $session = mysql_query($query) or NULL;
	    
	    return $session;
    }	
	
	

  function project_member( $project, $user ) {
    $member=false;
    $query = "SELECT * FROM `Project_Users` WHERE ((`r_Project` = '" . $project['HC'] . "') AND (`r_User` = '" . $user['HC'] . "'));";
    $users = mysql_query($query) or err(mysql_error(),$query);
        while($row=mysql_fetch_array($users)) if ( !flag($row['flags'],PROJECT_USER_BAN)
						&& !flag($row['flags'],PROJECT_USER_QUIT)
					        && !flag($row['flags'],PROJECT_USER_REMOVED) ) $member=true;
    return ($member or $project['r_Creator'] == $user['HC']);
  }

  function project_member_noerr( $project, $user ) {
    $member=false;
    $query = "SELECT * FROM `Project_Users` WHERE ((`r_Project` = '" . $project['HC'] . "') AND (`r_User` = '" . $user['HC'] . "'));";
    $users = mysql_query($query);
    if ( !is_null($users) ) {
        while($row=mysql_fetch_array($users)) if ( !flag($row['flags'],PROJECT_USER_BAN)
						&& !flag($row['flags'],PROJECT_USER_QUIT)
					        && !flag($row['flags'],PROJECT_USER_REMOVED) )  $member=true;
        return ($member or $project['r_Creator'] == $user['HC']);
    }
    return false;
  }
  
  function project_owner( $user, $project, $creator="" ) {
        if ( ($project == NULL) ) { redirect("projects.php?r=n"); die(); }   
        if ( $creator=="" ) $creator = find_id("Contact",$project['r_Creator']);        
        if ( ($user != NULL) ) {        
        return ($creator['HC'] == $user['HC']);
        }
	return false;
  }
  
	
} // end !defined(USE_SQLITE)
else {

    function smash_tick( $ticked ) {
	 return str_replace( "`","", $ticked );
	}

    // Find authentication by username
    function find_auth( $un ) {
	  $query = "SELECT * FROM Auth WHERE username='" . $un . "';";
	  try {
	    $db = new PDO("sqlite:/sqlite/gudadb/Auth.db" );
	    $q=$db->prepare($query);
            if ( is_null($q) || $q===false ) { echo 'Bad query: ' . $query; die(); }
            $q->execute(); $res=$q->fetchAll();
      } catch ( PDOException $e ) { echo $e->getMessage(); }
	    if ( count($res) > 0 ) return array_pop($res);
	    return NULL;
    }    

    // Find a user by hash code
    function find_user( $hc ) {
	    return find_id( "Contact", $hc );
    }
    
    // Find associated Auth data by a Contact hash code
    function find_uid( $hc ) {
        return find( "Auth", "r_Contact", $hc );
    }  
    
    // Save an object into the database, create new by default.
    // Returns mysql_insert_id(); only relevant for auto increment keys; will not be accurate if you use it on
    // a table without an auto-incrementing primary key
    function insert( $table, $field, $value ) {
	    $table=ucfirst($table);
        $query = "INSERT INTO " . $table . "( " . ($field) . ") VALUES (" . adq($value) . ");";
		try {
	    $db = new PDO("sqlite:/sqlite/gudadb/".($table).".db" );
	   $q=$db->prepare($query); 
           if ( is_null($q) || $q===false ) { echo 'Bad query: ' . $query; die(); }
           $q->execute(); $res=$q->fetchAll(); 
		} catch ( PDOException $e ) { echo $e->getMessage(); }
        return $res;
    }
    
    // Updates an object in the database by HC
    function set( $table, $id, $field, $value ) {
      	$query = "UPDATE " . $table . " SET " . $field . "='" . $value . "' WHERE HC = '" . $id . "';";
	    $table=ucfirst($table);
		try {
	    $db = new PDO("sqlite:/sqlite/gudadb/".($table).".db" );
	   $q=$db->prepare($query); 
           if ( is_null($q) || $q===false ) { echo 'Bad query: ' . $query; die(); }
           $q->execute(); $res=$q->fetchAll(); 
		} catch ( PDOException $e ) { echo $e->getMessage(); }
        return $res;
    }
    
    // Stores the right now time
    function now( $table, $id, $field ) {
	    $table=ucfirst($table);
	    $query = "UPDATE " . $table . " SET " . $field . "=datetime('now') WHERE HC = '" . $id . "';";
		try {
	    $db = new PDO("sqlite:/sqlite/gudadb/".($table).".db" );
	   $q=$db->prepare($query);
           if ( is_null($q) || $q===false ) { echo 'Bad query: ' . $query; die(); }
           $q->execute(); $res=$q->fetchAll();
		} catch ( PDOException $e ) { echo $e->getMessage(); }
       return $res;        
    }
    
    // Bitvector activation
    function activate( $table, $id, $field, $value ) {
	    $table=ucfirst($table);
        $query = "SELECT * FROM " . $table . " WHERE (HC = '" . $id ."');";
		try {
	    $db = new PDO("sqlite:/sqlite/gudadb/".($table).".db" );
	   $q=$db->prepare($query); 
           if ( is_null($q) || $q===false ) { echo 'Bad query: ' . $query; die(); }
           $q->execute(); $res=$q->fetchAll(); 
		} catch ( PDOException $e ) { echo $e->getMessage(); }
        $row = array_pop( $res );
	if ( flag($row[$field], $value) ) return;
        $flag = $row[$field] | $value;
        set($table,$id,$field,$flag);
    }
    
    function delete( $table, $id, $value, $sort_or_limit="" ) {
	    $table=ucfirst($table);
      	$query = "DELETE FROM " . $table . " WHERE " . $id . " = '" . $value . "' " . smash_tick($sort_or_limit) . ";";
		try {
	    $db = new PDO("sqlite:/sqlite/gudadb/".($table).".db" );		
	   $q=$db->prepare($query);
           if ( is_null($q) || $q===false ) { echo 'Bad query: ' . $query; die(); }
           $q->execute(); $res=$q->fetchAll(); 
		} catch ( PDOException $e ) { echo $e->getMessage(); }
    }
    
    // Bitvector deactivation
    function deactivate( $table, $id, $field, $value ) {
	    $table=ucfirst($table);
        $query = "SELECT * FROM " . $table . " WHERE HC = '" . $id ."';";
		try {
	    $db = new PDO("sqlite:/sqlite/gudadb/".($table).".db" );
	   $q=$db->prepare($query);
           if ( is_null($q) || $q===false ) { echo 'Bad query: ' . $query; die(); }
           $q->execute(); $res=$q->fetchAll(); 
		} catch ( PDOException $e ) { echo $e->getMessage(); }
        $row = array_pop( $res );
	if ( !flag($row[$field], $value) ) return;
        $flag = $row[$field] & ~($value);
        set($table,$id,$field,$flag);        
    }
    
    // Bitvector toggle
    function toggle( $table, $id, $field, $value ) {
	    $table=ucfirst($table);
        $query = "SELECT * FROM " . $table . " WHERE HC = '" . $id ."';";
		try {
	    $db = new PDO("sqlite:/sqlite/gudadb/".($table).".db" );
	   $q=$db->prepare($query); 
           if ( is_null($q) || $q===false ) { echo 'Bad query: ' . $query; die(); }
           $q->execute(); $res=$q->fetchAll(); } catch ( PDOException $e ) { echo $e->getMessage(); }
        $row = array_pop( $res );
	if ( flag($row[$field], $value) ) deactivate($table,$id,$field,$value);
        else activate($table,$id,$field,$value);
    }
    
    // Bitvector value request
    function flag_value( $table, $id, $field ) {
	    $table=ucfirst($table);
        $query = "SELECT * FROM " . $table . " WHERE HC = '" . $id ."';";
		try {
	    $db = new PDO("sqlite:/sqlite/gudadb/".($table).".db" );
	   $q=$db->prepare($query);
           if ( is_null($q) || $q===false ) { echo 'Bad query: ' . $query; die(); }
           $q->execute(); $res=$q->fetchAll(); 
		} catch ( PDOException $e ) { echo $e->getMessage(); }
        $row = array_pop( $res );
        $flag = $row[$field];
        return ( $flag );       
    }
    
    // Bitvector value assertion
    function has( $table, $id, $field, $value ) {
        $query = "SELECT * FROM " . $table . " WHERE HC = '" . $id ."';";
	    $table=ucfirst($table);
		try {
	    $db = new PDO("sqlite:/sqlite/gudadb/".($table).".db" );
	   $q=$db->prepare($query);
           if ( is_null($q) || $q===false ) { echo 'Bad query: ' . $query; die(); }
           $q->execute(); $res=$q->fetchAll(); } catch ( PDOException $e ) { echo $e->getMessage(); }
        $row = array_pop( $res );
        $flag = $row[$field];
        return ( $flag & $value );
    }
    
    // Updates multiple columns
    function multiupdate( $table, $hc, $field_value ) {
	    $table=ucfirst($table);
	    $db = new PDO("sqlite:/sqlite/gudadb/".($table).".db" );
        $query = "UPDATE " . $table . " SET " . $field_value . " WHERE HC = '" . $hc . "';";
    }
    
    // Adds a field to a table
    function add_field( $table, $field, $type ) {
	    $table=ucfirst($table);
        $query = "ALTER TABLE " . $table . " ADD COLUMN " . $field . " " . $type . ";";
		try {
	    $db = new PDO("sqlite:/sqlite/gudadb/".($table).".db" );
	   $q=$db->prepare($query);
           if ( is_null($q) || $q===false ) { echo 'Bad query: ' . $query; die(); }
           $q->execute(); $res=$q->fetchAll(); 
		} catch ( PDOException $e ) { echo $e->getMessage(); }
        return $res;
    }
    
    // Finds the first item in the table with an id and a value
    function find( $table, $id, $value, $other="" ) {
	    $table=ucfirst($table);
 	    $query = "SELECT * FROM " . $table . " WHERE " . $id . " = '" . $value . "' " . $other . ";";
		try {
	    $db = new PDO("sqlite:/sqlite/gudadb/".($table).".db" );
	   $q=$db->prepare($query); 
           if ( is_null($q) || $q===false ) { echo 'Bad query: ' . $query; die(); }
           $q->execute(); $res=$q->fetchAll(); 
		} catch ( PDOException $e ) { echo $e->getMessage(); }
	    if ( count($res) > 0 ) return array_pop($res);
	    return NULL;       
    }

    
    // Finds items in the table with an id and a value
    function find_like( $table, $id, $value, $sort_or_limit="" ) {
	    $table=ucfirst($table);
 	    $query = "SELECT * FROM " . $table . " WHERE " . $id . "= '" . $value . "' " . smash_tick($sort_or_limit) . ";";
		try {
	    $db = new PDO("sqlite:/sqlite/gudadb/".($table).".db" );
	   $q=$db->prepare($query);
           if ( is_null($q) || $q===false ) { echo 'Bad query: ' . $query; die(); }
           $q->execute(); $res=$q->fetchAll(); 
		} catch ( PDOException $e ) { echo $e->getMessage(); }
	    if ( count($res) > 0 ) return ($res);
	    return NULL;       
    }


    // Finds items in the table not matching an id and a value
    function find_not_like( $table, $id, $value, $sort_or_limit="" ) {
	    $table=ucfirst($table);
 	    $query = "SELECT * FROM " . $table . " WHERE " . $id . " <> '" . $value . "' " . smash_tick($sort_or_limit) . ";";
		try {
	    $db = new PDO("sqlite:/sqlite/gudadb/".($table).".db" );
	   $q=$db->prepare($query);
           if ( is_null($q) || $q===false ) { echo 'Bad query: ' . $query; die(); }
           $q->execute(); $res=$q->fetchAll(); 
		} catch ( PDOException $e ) { echo $e->getMessage(); }
	    if ( count($res) > 0 ) return ($res);
	    return NULL;       
    }
    
    
    // Finds all items in the table (future plateau danger zone)
    function find_all( $table, $sort_or_limit="" ) {
	    $table=ucfirst($table);
 	    $query = "SELECT * FROM " . $table . (strlen($sort_or_limit)>0 ? " ". smash_tick($sort_or_limit) : "" ) . ";"; 
		try {
	    $db = new PDO("sqlite:/sqlite/gudadb/".($table).".db" );
	   $q=$db->prepare($query); 
           if ( is_null($q) || $q===false ) { echo 'Bad query: ' . $query; die(); }
           $q->execute(); $res=$q->fetchAll(); 
		} catch ( PDOException $e ) { echo $e->getMessage(); }
	    if ( count($res) > 0 ) return ($res);
	    return NULL;
    }


    function find_id( $table, $id ) {
	    $table=ucfirst($table);
	    $query = "SELECT * FROM " . $table . " WHERE HC = '" . $id . "';";
		try {
	    $db = new PDO("sqlite:/sqlite/gudadb/".($table).".db" );
	   $q=$db->prepare($query);
           if ( is_null($q) || $q===false ) { echo 'Bad query: ' . $query; die(); }
           $q->execute(); $res=$q->fetchAll(); 
		} catch ( PDOException $e ) { echo $e->getMessage(); }
	    if ( count($res) > 0 ) return array_pop($res);
	    return NULL;
    }
   
    // finds all related records with a specific table, field and value
    function get_related( $table, $id, $val ) {
	    $table=ucfirst($table);
        $query = "SELECT * FROM " . $table . " WHERE " . $id . " = '" . $val ."';";
		try {
	    $db = new PDO("sqlite:/sqlite/gudadb/".($table).".db" );
	   $q=$db->prepare($query);
           if ( is_null($q) || $q===false ) { echo 'Bad query: ' . $query; die(); }
           $q->execute(); $res=$q->fetchAll();
		} catch ( PDOException $e ) { echo $e->getMessage(); }
	    if ( count($res) > 0 ) return sqlite_fetch_all($res);
        return NULL;        
    }
    
    // return a certain number of sorted records from a particular table
    function find_sorted( $table, $order_by, $limit, $asc_desc="DESC" ) {
	    $table=ucfirst($table);
        $query = "SELECT * FROM " . $table . " ORDER BY " . $order_by . " " . $asc_desc . " LIMIT " . $limit . ';';
		try {
	    $db = new PDO("sqlite:/sqlite/gudadb/".($table).".db" );
	   $q=$db->prepare($query);
           if ( is_null($q) || $q===false ) { echo 'Bad query: ' . $query; die(); }
           $q->execute(); $res=$q->fetchAll(); 
		} catch ( PDOException $e ) { echo $e->getMessage(); }
      return $res;
    }
	
	
    
    function get_contact( $un ) {
	    $table=ucfirst($table);
		try {
	    $db = new PDO("sqlite:/sqlite/gudadb/".($table).".db" );
	    $query = "SELECT * FROM Contacts WHERE HC = '" . $un . "';";
	   $q=$db->prepare($query);
           if ( is_null($q) || $q===false ) { echo 'Bad query: ' . $query; die(); }
           $q->execute(); $res=$q->fetchAll(); } catch ( PDOException $e ) { echo $e->getMessage(); }
	    if ( count($res) > 0 ) return ($res);
	    return NULL;
    }

    
    function get_session( $sid ) {
	    $sid      = $_COOKIE['session'];
	    $username = $_COOKIE['username'];
	    
	    $expired = false;
	    if ( is_null($sid) || is_null($username) ) {
		    $expired = true;
		    return NULL;
	    }	    
	    
	    $user = find_user( $username);
	    
	    if ( $user == NULL ) return NULL;
	    $table="Session";
		try {
	    $db = new PDO("sqlite:/sqlite/gudadb/".($table).".db" );
	    $query = "SELECT * FROM Session WHERE HC = '" . $sid . "'; ";
	    $res = $db->query($query);
		} catch (PDOException $e) { echo $e->getMessage(); }
	    if ( count($res)>0 ) return ($res);
	    return NULL;
    }
	
	

  function project_member( $project, $user ) {
    $member=false;
    $query = "SELECT * FROM Project_Users WHERE ((r_Project = '" . $project['HC'] . "') AND (r_User = '" . $user['HC'] . "'));";
	try { $db=new PDO("sqlite:/sqlite/gudadb/Project_Users.db");
	$users=$db->query($query);
	} catch( PDOException $e ) { echo $e->getMessage(); }
    foreach( $users as $row ) if ( !flag($row['flags'],PROJECT_USER_BAN)
						&& !flag($row['flags'],PROJECT_USER_QUIT)
					        && !flag($row['flags'],PROJECT_USER_REMOVED) ) $member=true;
    return ($member or $project['r_Creator'] == $user['HC']);
  }

  function project_member_noerr( $project, $user ) {
    $member=false;
    $query = "SELECT * FROM Project_Users WHERE ((r_Project = '" . $project['HC'] . "') AND (r_User= '" . $user['HC'] . "'));";
	try { $db=new PDO("sqlite:/sqlite/gudadb/Project_Users.db");
	$users=$db->query($query);
	} catch( PDOException $e ) { echo $e->getMessage(); }
    foreach( $users as $row ) if ( !flag($row['flags'],PROJECT_USER_BAN)
						&& !flag($row['flags'],PROJECT_USER_QUIT)
					        && !flag($row['flags'],PROJECT_USER_REMOVED) )  $member=true;
        return ($member or $project['r_Creator'] == $user['HC']);
    }
  
  function project_owner( $user, $project, $creator="" ) {
        if ( ($project == NULL) ) { redirect("projects.php?r=n"); die(); }   
        if ( $creator=="" ) $creator = find_id("Contact",$project['r_Creator']);        
        if ( ($user != NULL) ) {        
        return ($creator['HC'] == $user['HC']);
        }
	return false;
  }
  
	
}
    
        
    function check_cookie( ) {
      if ( !isset($_COOKIE) || !isset($_COOKIE['username']) || !isset($_COOKIE['session']) ) return NULL;
      
	    $username = escape_string( $_COOKIE['username'] );
	    $sid      = $_COOKIE['session'];
	    
	    $expired = false;
	    if ( is_null($username)  || is_null($sid) ) {
		    $expired = true;
		    return NULL;
	    }
	    
	    $user =find_auth( $username );
            return find_user( $user['r_Contact'] );
    }
    
    function cook( $v, $s ) {
        return setcookie( $v, $s, time()+timeout, '/' );//, domain, false, true );
    } 
    
    function uncook( $v ) {
        unset($_COOKIE[$v]);
        return setcookie( $v, "", mktime(12,0,0,1, 1, 1970) );//, '/', domain, false, true );      
    }
    
    function clear_cookie( ) {
       if ( !uncook( "username" ) ) echo 'failed to remove cookie'; //, "", time()-3600, '/' );
       uncook( "session"  ); //,  "", time()-3600, '/' );
    }    
    
    function show_cookie( ) {
        echo var_dump($_COOKIE);
	$sid = $_COOKIE['session'];
	$username = $_COOKIE['username'];
	echo $username . '<br>' . $sid;
    }

    
        // Report all PHP/MySQL interface errors
    if ( !isset($err_file) ) $err_file = "unknown";
    function err( $die, $query="" ) {
        global $err_file;
        if ( isset($err_file) && strlen($err_file) > 0 ) echo 'File: ' . $err_file . '<br>';
        if ( strlen($query) > 0    ) echo 'Query:<br>' . $query . '<br>'; 
        die($die);
    }
    //file_put_contents() will cause concurrency problems - that is, it doesn't write files atomically (in a single operation), which sometimes means that one php script will be able to, for example, read a file before another script is done writing that file completely.
    //The following function was derived from a function in Smarty (http://smarty.php.net) which uses rename() to replace the file - rename() is atomic on Linux.
    //On Windows, rename() is not currently atomic, but should be in the next release. Until then, this function, if used on Windows, will fall back on unlink() and rename(), which is still not atomic...
  
    define("FILE_PUT_CONTENTS_ATOMIC_TEMP", dirname(__FILE__)."/cache");
    define("FILE_PUT_CONTENTS_ATOMIC_MODE", 0777);    

    function file_put_contents_atomic($filename, $content) {  
      $temp = tempnam(FILE_PUT_CONTENTS_ATOMIC_TEMP, 'temp');
      if (!($f = @fopen($temp, 'wb'))) {
        $temp = FILE_PUT_CONTENTS_ATOMIC_TEMP
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
      @chmod($filename, FILE_PUT_CONTENTS_ATOMIC_MODE);   
      return true;   
    }

    // Generates a unique 32 bit string based on previous executions
    // URL-safe hashing only letters Aa-Zz and 0-9
    // Optional parameters: define a set, for multiple exclusive hash sets,
    //                      define a hash length, defaulting to 254 chars    
    // 1.55409285284366e+60 unique values
    function hash_code( $codeset = "1", $hashlength = 254 ) {
             $fn = "hashes/Hashes_" . $codeset . ".txt";
             
        if ( file_exists($fn) )
        $previous = file_get_contents($fn);
        else $previous = "";
        $hashcodes = explode("\n",$previous);

        $found = 1;
        while ( $found > 0 ) {
         // generate a new hash
         $newcode = "";
         for ( $x = 0; $x < $hashlength; $x++ ) {
             if ( rand(0,1) == 1 ) {
                          $newcode = $newcode . chr(rand(48,57));
             } else
             if ( rand(0,1) == 1 ) {
                          $newcode = $newcode . chr(rand(65,90));
             } else       $newcode = $newcode . chr(rand(97,122));
          }
          $found = 0; // check for duplicates, each must be unique
          $array_length = count($hashcodes);
          for ( $y = 0; $y < $array_length; $y++ ) {
             if ( strcmp( $hashcodes[$y], $newcode ) == 0 ) $found++;
          }
	  if ( is_numeric($newcode) ) $found++; // can't be a number
        } 
        $hashcodes[] = $newcode; 
        file_put_contents_atomic($fn,implode("\n",$hashcodes));
        return $newcode;
    }
    
    
    function hash_temp( $hashlength = 254 ) {

         $newcode = "";
         for ( $x = 0; $x < $hashlength; $x++ ) {
             if ( rand(0,1) == 1 ) {
                          $newcode = $newcode . chr(rand(48,57));
             } else
             if ( rand(0,1) == 1 ) {
                          $newcode = $newcode . chr(rand(65,90));
             } else       $newcode = $newcode . chr(rand(97,122));
          }
	  
        $hashcodes[] = $newcode; 
        return $newcode;
    }
    
    function js( $code ) {
      echo '<script type="text/javascript"> ' . $code . " </script>";
    }
  

// Record events of significance in the timeline, where $message is a message containing <v1-4> to replace marked up tags
// denoting parameters $value1-4
function history( $message, $value1="", $value2="", $value3="", $value4="" ) {
         $e = hash_code( "History", 8  );
         $message = str_replace( "<v1>", $value1, $message );
         $message = str_replace( "<v2>", $value2, $message );
         $message = str_replace( "<v3>", $value3, $message );
         $message = str_replace( "<v4>", $value4, $message );
	 
	 insert("History","HC",$e);
	 set("History",$e,"message",$message);
	 set("History",$e,"value1",$value1);
	 set("History",$e,"value2",$value2);
	 set("History",$e,"value3",$value3);
	 set("History",$e,"value4",$value4);
	 now("History",$e,"moment");
	 
	 return $e;
}

   // Drops zero length strings from a string array, returns new array
   function dropzerolen( $strarray ) {
        $c = count($strarray); $x=0;
	for ( $i=0; $i<$c; $i++ ) if ( strlen($strarray[$i]) > 0 ) $newarray[$x++] = $strarray[$i];
	return $newarray;
   }

  // Returns a link to the user
  function user_name( $user_id ) {
    $auth = find("auth","r_Contact",$user_id);
    if ( !$auth ) $user_name = "nobody";
    else $user_name = $auth["username"];
    return "<a href=\"profile.php?id=" . $auth['r_Contact'] . "\">" . $user_name . "</a>";
  }
  
  // Returns a link to the user
  function file_link( $file ) {
    if ( !$file ) return;
    return "<a href=\"view.php?file=" . $file["HC"] . "\">" . $file["filename"] . "</a>";
  }

	function redirect( $url, $delay = 0 ) {
		if ( $delay == 0 ) 
		   	 echo '<script type="text/javascript"> window.location = "' . $url . '"; </script>';
		else echo '<script type="text/javascript"> function delayer(){ window.location = ' . "'"
		        . $url . "'; } setTimeout('delayer()', '" . $delay . "'" . '); </script>';
	}
	
function hash_key( $length=255 ) { return "HC VARCHAR(" . $length . ")"; }
function hash_ref( $name, $length=255 ) { return $name . " VARCHAR(" . $length . ")"; }

if(!function_exists('mime_content_type')) {

    function mime_content_type($filename) {

        $mime_types = array(

            'txt' => 'text/plain',
            'htm' => 'text/html',
            'html' => 'text/html',
            'php' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'swf' => 'application/x-shockwave-flash',
            'flv' => 'video/x-flv',

            // images
            'png' => 'image/png',
            'jpe' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'ico' => 'image/vnd.microsoft.icon',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'svg' => 'image/svg+xml',
            'svgz' => 'image/svg+xml',

            // archives
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'exe' => 'application/x-msdownload',
            'msi' => 'application/x-msdownload',
            'cab' => 'application/vnd.ms-cab-compressed',

            // audio/video
            'mp3' => 'audio/mpeg',
            'qt' => 'video/quicktime',
            'mov' => 'video/quicktime',

            // adobe
            'pdf' => 'application/pdf',
            'psd' => 'image/vnd.adobe.photoshop',
            'ai' => 'application/postscript',
            'eps' => 'application/postscript',
            'ps' => 'application/postscript',

            // ms office
            'doc' => 'application/msword',
            'rtf' => 'application/rtf',
            'xls' => 'application/vnd.ms-excel',
            'ppt' => 'application/vnd.ms-powerpoint',

            // open office
            'odt' => 'application/vnd.oasis.opendocument.text',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
        );

        $ext = strtolower(array_pop(explode('.',$filename)));
        if (array_key_exists($ext, $mime_types)) {
            return $mime_types[$ext];
        }
        elseif (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME);
            $mimetype = finfo_file($finfo, $filename);
            finfo_close($finfo);
            return $mimetype;
        }
        else {
            return 'application/octet-stream';
        }
    }
}


   // Snippet that grabs a user's session
   //echo $_COOKIES;
   if ( isset($_COOKIE['username'])
     && isset($_COOKIE['session']) ) {
	   $user = find_user( $_COOKIE['username'] );
	   $session = find_id( 'Session', $_COOKIE['session'] );
	   $contact = find_id( 'Contact', $user['HC'] );
   } else { $user=NULL; $session=NULL; }
   
    $user=NULL;
    $session=NULL;
    $expired=false;

   $err_file = "not auth.php";
?>
