<?php
include('db_connectie.php');
$dbconnection = mysql_connect($dbhost, $dbuser, $dbpass) or die('Fout tijdens verbinden met de database');
	mysql_select_db($dbname, $dbconnection);

$sql = "SELECT count(id),road FROM cachedata2 WHERE type='speed_traps'OR type='incident' GROUP BY road;";
$res = mysql_query($sql);

$arr = mysql_fetch_array($res);

print_r($arr);
?>