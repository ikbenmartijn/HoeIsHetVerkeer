<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Hoe is het verkeer: Twitter Mention Zoeker</title>
</head>
<body></body>
</html>

<?php
	include('db_connectie.php');
	$dbconnection = mysql_connect($dbhost, $dbuser, $dbpass) or die('Fout tijdens verbinden met de database');
	mysql_select_db($dbname, $dbconnection);
	
	$querystring="SELECT id FROM incoming ORDER BY id DESC LIMIT 0,1"; 
	$query=mysql_query($querystring);
	$row=mysql_fetch_array($query);
	
	if($row && $row['id']!="0") {
		echo "Beginnen van de lus.<hr/>Recentste mention id in database: "; echo $row['id']; echo "<hr/>";
		HaalMentionsSinds($row['id']); //154855174106644480); als je de app herstart dan id van starttweet invullen
	}
	else {
		echo "<strong><font color=red>Ongeldige data.</font></strong>";
		echo "<hr/>Einde van de lus.";
	}
	
	mysql_close($dbconnection);
?>

<?php
function HaalMentionsSinds($sinceID) {
	require_once('twitteroauth/twitteroauth.php');
	
	echo "Authenticatiefase<br/>";
	
	define("CONSUMER_KEY", "RcYSKp48299J2GS5SxHww");
	define("CONSUMER_SECRET", "hAgwnh5AGjC5BmYFxBKK6OICiUH0wygGl8gLD9js");
	define("OAUTH_TOKEN", "454756155-hZ0CMdWaTuFZLnDxfQ2R94Ixt7DX3R72D2bL2XQx");
	define("OAUTH_SECRET", "UIxBjGVyRlAAygir5NzuO6COq1iwQf1QJ8n0GzKnv10");
	
	$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, OAUTH_TOKEN, OAUTH_SECRET);
	$content = $connection->get('account/verify_credentials');
	
	if($content->id) {
		echo "Authenticatie geslaagd. Aangemeld met ID: ".$content->id.".<br/>";
		echo "Laatste tweet opgehaald: $sinceID <br/>";
		echo "Eventuele nieuwe tweets aan het ophalen.<br/>";
		//haal de mentions op sinds de laatste tweet in de db
		$content = $connection->get('statuses/mentions', array('since_id' => $sinceID,'trim_user' => false,'include_entities' => true));
		echo "<font color=blue><strong>".count($content)." nieuwe tweets sinds vorige lus.</strong></font><br/>";
		
		if(!count($content)==0) {
			SchrijfNaarDatabase($content);
		}
		else {
			echo "<strong><font color=red>Geen data om te schrijven.</font><hr/>Einde van de lus</strong><hr/>";
		}
	}
	else {
		echo "<strong><font color=red>Authenticatie mislukt.</font><hr/>Einde van de lus.</strong>";	
	}
}

function SchrijfNaarDatabase($content) {
	//doe de lus voor elke tweet ($item) die binnengekomen is in het verzoek ($content)
	foreach ($content as $item) {
		if(!$item->id=="0") {
			echo "<hr/>";
			echo "Data van Twitter is geldig. Proberen te schrijven in database.<br/>";
			
			$query = "INSERT INTO incoming (id, timestamp, username, entities, tweet) VALUES ('".$item->id."','".$item->created_at."','@".$item->user->screen_name."','".serialize($item->entities->hashtags)."','".$item->text."');";
			
			echo "Query: ". $query."<br/>";
			
			$count=0;
			while(!mysql_query($query)) {
				$count++;
				echo "Schrijfpoging $count<br/>";
			}
			
			echo "<strong><font color=green>Schrijven succesvol.</font></strong> Volgende tweet.</br>";
		}
		else {
			echo "<strong><font color=red>Ongeldige data.</font></strong> Volgende tweet.<br/>";
		}
			
	}
	echo "<hr/><strong>Einde van de lus.</strong><hr/>";
}
?>