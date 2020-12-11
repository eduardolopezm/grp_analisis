<?php

/* $Revision: 1.7 $ */
// User configurable variables
// ---------------------------------------------------

// DefaultLanguage to use for the login screen and the setup of new users - the users language selection will override
$DefaultLanguage = 'sp_MX';

// Whether to display the demo login and password or not on the login screen
$allow_demo_mode = false;

// version
$Version = '4.1.0';

// Connection information for the database
// $host is the computer ip address or name where the database is located
// assuming that the web server is also the sql server
$host = 'localhost';

// assuming that the web server is also the sql server
$dbType = 'mysqli';
// assuming that the web server is also the sql server
$dbuser = 'root';
// assuming that the web server is also the sql server
$dbpassword = 'p0rtali70s'; // 'Elc4N742!';//

$dbpass = $dbpassword;
// MYSQL Socket
//$dbsocket = '/home/mysql/mysql.sock';
 $dbsocket = '';
// The timezone of the business - this allows the possibility of having;
putenv ( 'America/Chicago' );

$getdir = explode ( '.', ($_SERVER ['HTTP_HOST']) );
$getdir1 = $getdir [0];

if ($getdir1 == 'erp') {
	$AllowCompanySelectionBox = false;
	setcookie ( "defaultempresa", 'erpgubernamental');
} else {
	$AllowCompanySelectionBox = false;
	setcookie ( "defaultempresa", $getdir1 );
}
$DefaultCompany = 'erpgubernamental_DES';
$SessionLifeTime = 7200;
$MaximumExecutionTime = 120;
$CryptFunction = 'sha1';
$DefaultClock = 12;
$rootpath = dirname ( $_SERVER ['PHP_SELF'] );

if (isset ( $DirectoryLevelsDeep )) {
	for($i = 0; $i < $DirectoryLevelsDeep; $i ++) {
		$rootpath = substr ( $rootpath, 0, strrpos ( $rootpath, '/' ) );
	}
}
if ($rootpath == '/' or $rootpath == '\\') {
	;
	$rootpath = '';
}
?>