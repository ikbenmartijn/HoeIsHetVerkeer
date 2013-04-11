<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Hoe is het verkeer: Twitter Antwoorder</title>
</head>
<body></body>
</html>

<?php
	echo "Beginnen van de antwoordlus<hr/>";
	
	include('db_connectie.php');
	$dbconnection = mysql_connect($dbhost, $dbuser, $dbpass) or die('Fout tijdens verbinden met de database');
	mysql_select_db($dbname, $dbconnection);
	//haal alle niet beantwoorde tweets op, de laatste eerst
	$querystring="SELECT id,username,antwoord FROM incoming WHERE beantwoord=1 AND verzonden=0 ORDER BY id ASC"; 
	$query=mysql_query($querystring);
	//$testrow = mysql_fetch_array($query);
	
	echo "Eventuele onverstuurde antwoorden ophalen, starten met de oudste.<br/>";
	
	if(isset($query)) {
		$twitterconnectie = AuthenticeerBijTwitter();
		
		echo "Onverstuurde antwoorden gevonden. Overgaan naar verstuurlus.<hr/>";
		
		$number=0;
		while($row = mysql_fetch_array($query)){
			$number++;
			echo "Onverstuurd antwoord in database $number<br/>";
			BeantwoordMention($row['id'], $row['username'], $row['antwoord'], $twitterconnectie);
		}
		
		echo "<strong>Alle onverstuurde antwoorden zijn doorlopen.</font><hr/>Einde van de lus.</strong>";
	}
	else {
		echo "<hr/><strong><font color=red>Geen onverstuurde antwoorden.</font><hr/>Einde van de lus.</strong>";
	}
	
	mysql_close($dbconnection);
?>

<?php
function AuthenticeerBijTwitter() {
	require_once('twitteroauth/twitteroauth.php');
	
	echo "<hr/>";
	echo "Authenticatiefase<br/>";
	
	define("CONSUMER_KEY", "<REDACTZA>");
	define("CONSUMER_SECRET", "<REDACTZA>");
	define("OAUTH_TOKEN", "<REDACTZA>");
	define("OAUTH_SECRET", "<REDACTZA>");
	 
	$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, OAUTH_TOKEN, OAUTH_SECRET);
	$content = $connection->get('account/verify_credentials');
	
	if($content->id) {
		echo "Authenticatie geslaagd. Aangemeld met ID: ".$content->id.".<br/>";
		return $connection;
	}
	else {
		return null;	
	}
}

function BeantwoordMention($mentionID, $username, $antwoord, $twitterconnectie) {
	echo "Versturen van antwoord op tweet $mentionID van $username.<br/>";
	
	//$new_status = "$username Ik heb je goed verstaan maar ik kan nog niks echt zeggen. ($mentionID)";
	$new_status = $username." ".$antwoord;
	echo "<strong>Tweet: </strong> ".$new_status;
	$content = $twitterconnectie->post('statuses/update', array('status' => $new_status,'trim_user' => false,'include_entities' => true,'in_reply_to_status_id' => $mentionID));
	
	if($content->id) {
		echo "<font color=green><strong>Antwoord is verstuurd.</strong></font><br/>";
		NoteerAntwoordInDatabase($mentionID,1);
	}
	else {
		echo "<font color=red><strong>Antwoord is niet verstuurd.</strong></font><br/>";
		echo "<pre>".$content->error."</pre>";
		echo "<hr/>";
		NoteerAntwoordInDatabase($mentionID,9);
	}
}

function NoteerAntwoordInDatabase($mentionID,$code) {
		$query = "UPDATE incoming SET verzonden=$code WHERE id=$mentionID;";
		
		$count=0;
		while(!mysql_query($query)) {
			$count++;
			echo "Antwoord noteren in database ($count).<br/>";
		}
		
		echo "<strong><font color=green>Noteren succesvol.</font></strong>";
		echo "<hr/>";
}
?>