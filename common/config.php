<?php
if ( !isset($config_php) ) { $config_php=1;

// Configuration file

//site   ,"http://www.supertogether.com");  // no ending slash
//define(site, 'http://www.yourbusinesshost.net');
//define(domain,"www.yourbusinesshost.net");
define(site,'http://www.gudagi.com/');
define(site_,'http://www.gudagi.com');
define(domain,'.gudagi.com');
define(host, "localhost");
define(db_name, "dbname");
//db_name,"thebooks");
define(db_pass, "password");
define(db_user, "username");

define(timeout, 96000);       // number of seconds until cookie expires
// chosen db-related data structure sizes

define(biggest_db_storage , "bigint");
define(biggest_db_key     , "bigint");

define(db_key , "ID BIGINT NOT NULL AUTO_INCREMENT, PRIMARY KEY(ID)");

define(flag_size           , "bigint");

// chosen text and message limits to varchar


define(big_text_size       , 2048); define(bts, big_text_size);          // bts
define(medium_message_size , 1024); define(mms, medium_message_size);    // mms
define(small_message_size  , 512);  define(sms, small_message_size);     // sms
define(tiny_message_size   , 255);  define(tms, tiny_message_size);      // tms
define(tiny_token_size     , 128);  define(tts, tiny_token_size);        // tts

// flags for status etc

// Contact.flags
define(CONTACT_BANNED   , 1);
define(CONTACT_MANAGER  , 2);
define(CONTACT_NOCHAT   , 4);
define(CONTACT_W9FILED  , 8);   // W9/I9 is filed
define(CONTACT_VERIFIED , 16);  // SSN verified
define(CONTACT_ADMIN    , 32);
define(CONTACT_ADMIN_ASST, 64);

define(CONTACT_SALES_AGENT, 128);
define(CONTACT_SERVICE,     256);
define(CONTACT_SHOPKEEPER,  512);
define(CONTACT_SUPPLIER,   1024);

define(CONTACT_SUPPRESS_DESIGNER_QUESTION, 2048);
define(CONTACT_SUPPRESS_SALES_AGENT_QUESTION, 4096);
define(CONTACT_SUPPRESS_STORE_QUESTION, 8192);

// Project.flags
define(PROJECT_WRITTEN    , 1);
define(PROJECT_AUDIO      , 2);
define(PROJECT_MULTIMEDIA , 4);
define(PROJECT_GAME       , 8);
define(PROJECT_VIDEO      , 16);
//PROJECT_ 32, 64

define(PROJECT_FUNDING , 128);  // Project desires external funding
define(PROJECT_OPEN    , 256);  // Project is open to new members
define(PROJECT_INVITE  , 512);  // Contribution to this project is invite only
define(PROJECT_PRIVATE , 1024); // The project is private, invite only, unlisted

define(PROJECT_CONSENSUS , 2048);  // Project is administered by concensus
define(PROJECT_ANONYMOUS , 4096);  // Project is open to anonymous contribution
define(PROJECT_DERIVABLE , 8192);  // Project can generate open derivatives
define(PROJECT_ROYALTY   , 16384); // Project can generate royalty-based derivatives

define(PROJECT_PROFIT             , 32768);  // Project is for profit

define(PROJECT_ROYALTY_CONCENSUS  , 65536);  //  profits calculated by concensus "vote share"
define(PROJECT_ROYALTY_PERCENTAGE , 131072); //  profits calculated by percentage "participant share"
define(PROJECT_ROYALTY_EQUAL      , 262144); //  profits calculated by equal percentage
// if none of these bits are set, project is administered

define(PROJECT_BROADCAST   , 524288); // Project is broadcast or streamed publically
define(PROJECT_AUTOVERSION , 1048576); // Project's version is managed by the site

define(PROJECT_DERIVE_SAME , 2097152); // Project's derivatives must use the same license
define(PROJECT_NON_COMMERCIAL , 4194304); // Project's derivatives cannot be for-profit, or have fees charged for distribution

define(PROJECT_CLOSED , 8388608);  // Project is read only: a final version, possibly downloadable if not made private

define(PROJECT_CONFIRM_PUBLISH, 16777216);  // Packages are not published until an owner or admin clicks "publish"
define(PROJECT_PUBLIC_FILES, 33554432); // The individual files for this project are available to the public.

define(PROJECT_SUPPRESS_VIDEO_PLAYER, 67108864);   // Video player suppression
define(PROJECT_SUPPRESS_AUDIO_PLAYER, 134217728);  // Audio player suppression
define(PROJECT_SUPPRESS_IMAGE_VIEWER, 268435456);  // Image viewer suppression
define(PROJECT_SUPPRESS_SWF_PLAYER, 536870912);    // SWF Player suppression
define(PROJECT_SHOW_MEDIA_GALLERY, 1073741824);    // Use the Media Gallery instead

define(PROJECT_NOT_EMBEDDABLE, 2147483648);    // Use the Media Gallery instead

// Project.stages
define(PROJECT_ALPHA  , 1);
define(PROJECT_BETA   , 2);
define(PROJECT_MATURE , 3);

// Categories
define(CAT_MUSIC , 1);
define(CAT_FILM , 2);
define(CAT_PLAY , 4);
define(CAT_RESEARCH , 8);
define(CAT_GAME , 16);
define(CAT_INVENTION , 32);
define(CAT_SOFTWARE , 64);
define(CAT_WRITING , 128);
define(CAT_ART , 256);
define(CAT_FASHION , 512);
define(CAT_BUSINESS_PLAN , 1024);
define(CAT_HOMEWORK , 2048);

// File.flags
define(FILE_RESTRICTED,1);   // Restricts download access, set when a file
define(FILE_LOCKED,2);       // Restricts access from all users
define(FILE_DOCUMENT,4);     // Used for a versioned document
define(FILE_DEPRECIATED,8);  // File for internal use only for project members, not for download
define(FILE_CHECKED_OUT,16); // File for internal use only for project members, not for download
define(FILE_DOWNLOAD,32);    // File is available for download as-is
define(FILE_HIDDEN,64);      // File is excluded from project page media players

// Package.flags
define(PACKAGE_RESTRICTED,1);  // Restricts download access
define(PACKAGE_DEPRECIATED,8);    // File for internal use only for project members, not for public download

define(PACKAGE_PUBLISHED,16); // Package is available for public download

// Folder.flags
define(FOLDER_RESTRICTED,1);  // Restricts download access
define(FOLDER_DEPRECIATED,8);    // File for internal use only for project members, not for public download

// User.flags
define(USER_BANNED    , 1);
define(USER_ADMIN     , 2);
define(USER_MODERATOR , 4);

// User preferences flags
define(PREF_EXPERT_EDITOR , 1);       // Use complex MCE support

// Role.flags
define(ROLE_CRITIC    , 1);
define(ROLE_AUTHOR    , 2);
define(ROLE_INVESTOR  , 4);
define(ROLE_TALENT    , 8);

// Secretarial_Action.type
define(SA_W9,1);//Secretarial action required on w9

// Defines a relationship role
function role( $value ) {
 if ( flag( $value, ROLE_CRITIC ) )   return "Critic";
 if ( flag( $value, ROLE_AUTHOR ) )   return "Author";
 if ( flag( $value, ROLE_INVESTOR ) ) return "Investor";
 if ( flag( $value, ROLE_TALENT ) )   return "Talent";
}


define(PROJECT_USER_APPROVED, 1);  // approved invite request
define(PROJECT_USER_QUIT,     2);  // quit the project
define(PROJECT_USER_BAN,      4);  // user has been banned from this project
define(PROJECT_USER_REMOVED,  8);  // user has been banned from this project

// Media.flags
define(MEDIA_RATED_X , 1);  // Adults only, sexually or graphically explicit
define(MEDIA_RATED_R , 2);  // Adults only
define(MEDIA_RATED_T , 4);  // For Teens and up!
define(MEDIA_RATED_K , 8);  // For Kids!
define(MEDIA_UNRATED , 16); // media has not yet been rated

// Media.flags (deletion flags)
define(MEDIA_COPYRIGHT_VIOLATION , 32); // media is illegally copied
define(MEDIA_MALICIOUS , 64);  // for viruses, trojan horses, spyware
define(MEDIA_IMMORAL , 128);   // for child porn

// Version control system flags
define(VERSION_REVERT , 1);
define(VERSION_DERIVATION , 2);

// Store.flags
define(STORE_SUPPLIER, 1);
define(STORE_RETAIL, 2);
define(STORE_SERVICE, 4);

// Product.flags

define(PRODUCT_RESELLABLE,1);
define(PRODUCT_DRYICE,2);

/* Tracker Configuration
 *
 *  This file provides configuration informatino for
 *  the tracker. The user-editable variables are at the top. It is
 *  recommended that you do not change the database settings (at the bottom)
 *  unless your database settings actually change, or this is your
 *  initial installation.
 */

// Maximum reannounce interval, in seconds. 1800 == 30 minutes
$GLOBALS["report_interval"] = 1800;

// Minimum reannounce interval. Optional. Also in seconds.
// 300 == 5 minutes
$GLOBALS["min_interval"] = 300;

// Number of peers to send in one request.
// Some logic will break if you set this to more than 300, so please
// don't do that. 100 is the most you should set anyway.
$GLOBALS["maxpeers"] = 50;

// If set to true, then the tracker will accept any and all
// torrents given to it. Not recommended, but available if you need it.
$GLOBALS["dynamic_torrents"] = false;

// If set to true, NAT checking will be performed.
// This may cause trouble with some providers, so it's
// off by default. And paranoid people with paranoid firewalls.
$GLOBALS["NAT"] = false;

// Persistent connections: true or false.
// Check with your webmaster to see if you're allowed to use these.
// Highly recommended, especially for higher loads, but generally
// not allowed unless it's a dedicated machine.
$GLOBALS["persist"] = false;

// Allow users to override ip= ?
// Enable this if you know people have a legit reason to use
// this function. Leave disabled otherwise.
$GLOBALS["ip_override"] = false;

// For heavily loaded trackers, set this to false. It will stop count the number
// of downloaded bytes and the speed of the torrent, but will significantly reduce
// the load.
$GLOBALS["countbytes"] = true;

// Table caches!
// Lowers the load on all systems, but takes up more disk space.
// You win some, you lose some. But since the load is the big problem,
// grab this.
//
// Warning! Enable this BEFORE making torrents, or else run makecache.php
// immediately, or else you'll be in deep trouble. The tables will lose
// sync and the database will be in a somewhat "stale" state.
$GLOBALS["peercaching"] = true;


/////////// End of User Configuration ///////////

// These are usually filled in by install.php. 
// But if it fails to make a config.php for itself,
// you'll have to set these.

$dbhost = host;
$dbuser = db_user;
$dbpass = db_pass;
$database = db_name;
}
?>
