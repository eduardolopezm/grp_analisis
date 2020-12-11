<?
define('DBHOST', 'localhost');// MySQL host address - localhost is usually fine
define('DBNAME', 'DatawareServillantas');// MySQL database name - must already exist
define('DBUSER', 'root');// MySQL username - must already exist
define('DBPASS', 'passw0rd');// MySQL password for above username

function db_connect()
{
 @$db_link = mysql_connect(DBHOST, DBUSER, DBPASS);
 if ($db_link) @mysql_select_db(DBNAME);
 if (mysql_error())
 {
  if (SHOW_MYSQL_ERRORS) echo "MySQL error: ". mysql_error(). " MySQL error no: ".mysql_errno();
  exit;
 }
 return $db_link;
}

$dbDataware=db_connect();
?>