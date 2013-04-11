<?php
	$ch = curl_init();
	
	curl_setopt($ch, CURLOPT_URL, 'http://services.vrt.be/traffic/events');
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/vnd.traffic.vrt.be.events_1.0+json', 'Content-Type: application/vnd.traffic.vrt.be.events_1.0+json'));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	
	$rawData = curl_exec($ch);
	
	if(curl_exec($ch) == false) {
		echo curl_error($ch);
	}
	else {
		$obj=json_decode($rawData);
		//haal het aantal events op
		$AantalEvents = count($obj->events);
		
		//schrijf de huidige events in de database
		include('db_connectie.php');
		$dbconnection = mysql_connect($dbhost, $dbuser, $dbpass) or die('Fout tijdens verbinden met de database');
		mysql_select_db($dbname, $dbconnection);
		
		$query = "INSERT INTO cachedates2 (issueDate) VALUES ('".$obj->issueDate."') ;";
		echo "ISSUEDATA OPSLAAN:<br/><pre>$query</pre><hr/>";
		mysql_query($query);
		
		$query = "SELECT id FROM cachedates2 ORDER BY id DESC LIMIT 0,1;";
		echo "ID TERUGHALEN:<br/><pre>$query</pre><hr/>";
		$idArray = mysql_fetch_assoc(mysql_query($query));
		$id = $idArray['id'];
		echo "ID:<br/><pre>$id</pre><hr/>DATA TOEVOEGEN MET ID $id:<br/>";
		
		$i=0;
		for($i=0; $i<$AantalEvents; $i++) {
			$query = "INSERT INTO cachedata2 (id, type, road, roadFrom, roadTo, details, routeIn, routeFrom, routeTo, segment, text) VALUES (".
						$id.", '".
						$obj->events[$i]->type."', '".
						$obj->events[$i]->route->road."', '".
						$obj->events[$i]->route->roadFrom."', '".
						$obj->events[$i]->route->roadTo."', '".
						$obj->events[$i]->route->details."', '".
						$obj->events[$i]->route->in."', '".
						$obj->events[$i]->route->from->city."', '".
						$obj->events[$i]->route->to->city."', '".
						$obj->events[$i]->segment."', '".
						$obj->events[$i]->text."');";
			echo "<pre>$query</pre>";
			mysql_query($query);
		}
		
		//verwijder oude caches
		$id = $id-1;
		$query = "DELETE FROM cachedata2 WHERE id='".$id."';";
		echo "<hr/>OUDE DATA VERWIJDEREN MET ID $id:<pre>$query";
		mysql_query($query);
		$query = "DELETE FROM cachedates2 WHERE id='".$id."';";
		echo "<br/>$query</pre>";
		mysql_query($query);
	}		
	curl_close($ch);
?>


<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>VRT Cachemachine</title>
</head>

<body>
</body>
</html>